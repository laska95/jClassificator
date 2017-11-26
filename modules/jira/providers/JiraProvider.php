<?php

namespace app\modules\jira\providers;

/*
 * Відповідає за інтеграцією з Jira 
 */

use Codeception\Util\HttpCode;
use \yii\web\HttpException;
use \app\modules\jira\models\FullResponse;
use \app\modules\jira\models\Issue;

class JiraProvider {
    
    /*=============================== Singleton ==============================*/
    
    private static $_instance = NULL;
    
    private function __construct() {}
    
    protected function __clone() {
        return NULL;
    }
    
    /**
     * @return JiraProvider
     */
    public static function getInstance(){
        
        if (self::$_instance === NULL){
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }
    
    /*================= Методи для аунтифікації користувача ==================*/
    
    public function validateTest($username, $password){
        
        $jiraUrl = \Yii::$app->params['jiraUrl'];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $jiraUrl . '/rest/api/2/myself');
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_USERPWD, "{$username}:{$password}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        
        $ret = new FullResponse($ch);
        
        curl_close($ch);
                
        return $ret;
    }
    
    /** 
     * Намагається авторизуватися в системі Jira використовуючи простий 
     * метод аунтифікації. Це єдиний метод, що не повертає FullResponse
     * 
     * @return bool TRUE - якщо авторизація пройшла успішно
     */
    public function validatePassword($username, $password){
        
        $jiraUrl = \Yii::$app->params['jiraUrl'];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $jiraUrl . '/rest/auth/1/session');
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_USERPWD, "{$username}:{$password}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        
        $result = curl_exec($ch);
        $result_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
        
        curl_close($ch);
                
        return ($result_code == HttpCode::OK) ? TRUE : FALSE;
    }
    
    /**
     * Створює нову сесію в Jira.
     * 
     * @return FullResponse , де відповідь - ключ сесії у випадку успіху 
     */
    public function createSession($username, $password){
         
        /* Приклад коректної відповіді (JSON)
         * "session": {
         *          "name":"JSESSIONID",
         *          "value":"6E3487971234567896704A9EB4AE501F"
	 *      },
         */
        
        $post_data = [
            'username' => $username,
            'password' => $password,
        ];
        
        $ch = $this->getBaseCurl('/rest/auth/1/session', [], 'POST', $post_data);
        $ret = new FullResponse($ch);
        curl_close($ch);       
        return $ret;
    }
    
    /**
     * Перевіряє, чи сесія поточного користувача досі дійсна
     * 
     * @return bool TRUE - якщо сесія ще дійсна
     */
    public function checkSession(){       
        $ch = $this->getBaseCurl('/rest/auth/1/sessionn');
        $ret = new FullResponse($ch);
        curl_close($ch);                  
        return ($ret->code == HttpCode::OK) ? TRUE : FALSE;
    }
    
    /** 
     * Видаляє сесію поточного користувача
     * 
     * @return FullResponse
     */
    public function deleteSession(){  
        $ch = $this->getBaseCurl('/rest/auth/1/session', [], 'DELETE');
        $ret = new FullResponse($ch);
        curl_close($ch);
        return $ret;
    }

    /**
     * @return FullResponse 
     */
    public function getProjectList(){
        $ch = $this->getBaseCurl('/rest/api/2/project');
        $ret = new FullResponse($ch);
        curl_close($ch);
        return $ret;
    }
    
    /**
     * @return FullResponse 
     */
    public function getIssueStatusList(){
        $ch = $this->getBaseCurl('/rest/api/2/status');
        $ret = new FullResponse($ch);
        curl_close($ch);
        return $ret;
    }
    
    /**
     * @return FullResponse 
     */
    public function getSelf2($username, $password){
        $jiraUrl = \Yii::$app->params['jiraUrl'];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $jiraUrl . '/rest/api/2/myself');
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_USERPWD, "{$username}:{$password}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        
        $ret = new FullResponse($ch);
        curl_close($ch);
                
        return $ret;
    }
    
    public function getSelf(){
//        $ch = $this->getBaseCurl('/rest/auth/1/session');
//        $ret = new FullResponse($ch);
//        curl_close($ch);
//              
//        return $ret;
        
        $post = [
            'jql' => 'status IN ("To Do") ORDER BY "created"'
        ];
        
        $ch = $this->getBaseCurl('/rest/api/2/search', [], 'POST', $post);
        $ret = new FullResponse($ch);
        curl_close($ch);
        return $ret;
    }
    
    public function getIssue($key, $fields = '*'){
        $ch = $this->getBaseCurl("/rest/api/2/issue/{$key}", [], 'GET', 
                ['fields' => is_array($fields) ? implode(',', $fields) : $fields]);
        $ret = new FullResponse($ch);
        curl_close($ch);
        return $ret;
    }

    public function getIssueList($jql, $fields = '*', $startAt = 0, $maxResults = 50){
        $post = [
            'jql' => $jql,
            'startAt' => $startAt,
            'maxResults' => $maxResults,
//            'fields' => $fields,
        ];
                
        $ch = $this->getBaseCurl('/rest/api/2/search', [], 'POST', $post);
        $ret = new FullResponse($ch);
        curl_close($ch);
        return $ret;    
    }

    /**
     * @return FullResponse 
     */
    public function getBecklog(){
        $post = [
            'jql' => 'status IN ("To Do")'
        ];
        
        $ch = $this->getBaseCurl('/rest/api/2/search', [], 'POST', $post);
        $ret = new FullResponse($ch);
        curl_close($ch);
        return $ret;
    }
    
    protected function generateSessionHeader($session){
        /* Приклад вхіних даних (JSON):
         * $session = {
         *          'name': "JSESSIONID",
         *          'value': "6E3487971234567896704A9EB4AE501F"
         *      }
         * 
         * https://developer.atlassian.com/jiradev/jira-apis/jira-rest-apis/jira-rest-api-tutorials/jira-rest-api-example-cookie-based-authentication
         */
        
        $session = json_decode($session, TRUE)['session'];       
        
        if (!isset($session['name']) || !isset($session['value'])){
            return FALSE;
        }
        
        return "cookie: {$session['name']}={$session['value']}";
    }
        
    private function getBaseCurl($restUrl, $headers = [], $type = 'GET', $data = NULL, $user = NULL){
        
        $jiraUrl = \Yii::$app->params['jiraUrl'];
        $user = $user ?? \Yii::$app->user->identity;
        $session_header = $this->generateSessionHeader($user->jiraAuthKey ?? NULL);
        $headers[] = $session_header;
        
        $url = $jiraUrl . $restUrl;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
        
        if (($type == 'POST') && $data){
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } 
        
        if (($type == 'GET') && $data){
            $query = http_build_query($data);
            $url.= "?{$query}";
        }
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        
        return $ch;
        
    }

}
