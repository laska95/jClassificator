<?php

namespace app\modules\jira\models;

class FullResponse extends \yii\base\Object{
    
    private $_json_request; //json string
    private $_code;         //integer 
    
    public function __construct($ch = NULL) {
        if ($ch){
            $this->_json_request = curl_exec($ch);
            $this->_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
        }
    }

    public function getResponse(){
        return json_decode($this->_json_request, TRUE);
    }
    
    public function getRawResponse(){
        return $this->_json_request;
    }

    public function getCode(){
        return $this->_code;
    }
    
}
