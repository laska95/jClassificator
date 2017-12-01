<?php

namespace app\models;

/*
 * Модель, що використовується для аунтифікації користувача в системі 
 * та на зовнішніх ресурсах
 */

use yii\db\ActiveRecord;
use app\modules\jira\providers\JiraProvider;
use Codeception\Util\HttpCode;


/** @property integer $id */
/** @property string $fullName */
/** @property string $username */
/** @property string $email */
/** @property string $authKey */
/** @property string $jiraAuthKey */
/** @property string $jiraUrl */
/** @property string $apiKey */
/** @property string $settings */
class User extends ActiveRecord implements \yii\web\IdentityInterface
{

    public static function tableName() {
        return 'user';
    }

    public function rules() {
        return [
            [['fullName', 'username'], 'string', 'max' => 200],
            [['email'], 'email'],
            [['jiraAuthKey', 'apiKey', 'jiraUrl', 'authKey'], 'string'],
            [['username'], 'required'],
            [['username', 'email'], 'unique']
        ];
    }

    /*========================= IdentityInterface ============================*/
    
    public static function findIdentity($id)
    {
        return  self::findOne(['id' => $id]) ?? null;
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return null;
    }

    /**
     * Шукає користувача за унікальним іменем (логіном) в БД
     *
     * @param string $username (login)
     * @return static|null
     */
    public static function findByUsername($username)
    {
        $ret = filter_var($username, FILTER_VALIDATE_EMAIL)
                ? self::findOne(['email' => $username])
                : self::findOne(['username' => $username]);
        return  $ret ?? null;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * Повертає ключ сесії. Якщо в БД такого нема, то генерує новий
     */
    public function getAuthKey()
    {       
        return $this->authKey ?? md5($this->id . $this->username . time());
    }

    public function validateAuthKey($authKey)
    {
        return $this->authKey === $authKey;
    }

    /**
     * Валідація паролю виконується в Jira
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        $provider = JiraProvider::getInstance();
        return $provider->validatePassword($this->email ?? $this->username, $password, $this->jiraUrl);
    }
    
    public function selfLogin($password){
        $provider = JiraProvider::getInstance();
        
        if ($this->jiraAuthKey){
            //видаляємо застарілу сесію
            $provider->deleteSession($this);
        }
        
        $res = $provider->createSession($this->email ?? $this->username, $password, $this);
                
        $this->authKey = $this->getAuthKey();   //новий ключ сесії
        $this->jiraAuthKey = ($res->code == HttpCode::OK) ? $res->rawResponse : NULL;
                
        $this->save();
    }

    public function selfLogout(){
        $provider = JiraProvider::getInstance();
        
        $provider->deleteSession($this);
        
        $this->authKey = NULL;
        $this->jiraAuthKey = NULL;
        $this->save();
    }
}
