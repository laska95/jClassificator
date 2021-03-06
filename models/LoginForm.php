<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 *
 * @property User|null $user This property is read-only.
 *
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;
    public $jiraUrl;

    private $_user = NULL;
    
    public function rules()
    {
        return [
            [['username', 'password', 'jiraUrl'], 'required'],
            [['jiraUrl'], 'url'],
            ['rememberMe', 'boolean'],
            ['password', 'validatePassword'],
        ];
    }

    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {

            $user = $this->getUser();
            
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect username or password.');
            }
        }
    }

    public function login()
    {
        if ($this->validate()) {
            
            $user = $this->getUser();
            
            if ($user->isNewRecord){
                $this->createNewUser();
            }
            
            //генерує нові ключі доступу
            $user->selfLogin($this->password);          
            return Yii::$app->user->login($user, $this->rememberMe ? 3600*24*30 : 0);
        }
        return false;
    }


    public function getUser()
    {
        if ($this->_user === NULL){

            $user = User::findByUsername($this->username);

            if (!$user){
                $user = new User();
                if (filter_var($this->username, FILTER_VALIDATE_EMAIL)){
                    $user->email = $this->username;
                } else {
                    $user->username = $this->username;
                }
                $user->jiraUrl = $this->jiraUrl;
            }
            
            $this->_user = $user;
        }
        return $this->_user;
    }
    
    public function createNewUser(){
        $provider = \app\modules\jira\providers\JiraProvider::getInstance();
        $res = $provider->getSelf2($this->username, $this->password, $this->jiraUrl);
        
        
        
        $data = $res->response;
        
        if (!$data['name']){
            var_dump($data);
            die();
        }
        
        $user = User::findByUsername($data['emailAddress']) 
                ?? User::findByUsername($data['name']) 
                ?? new User();
        
        $user->username = $data['name'];
        $user->fullName = $data['displayName'];
        $user->email = $data['emailAddress'];
        $user->jiraUrl = $this->jiraUrl;
        $user->save();
        $this->_user = $user;
        return $user;
    }
}
