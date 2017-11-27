<?php

namespace app\modules\decision\controllers;

use \app\modules\jira\models\Issue;
use \app\modules\decision\models\FrequencyProjectLang;

class FullApiController extends \yii\web\Controller{
    
    private $_user = NULL;

    public function beforeAction($action) {   
        
        $api_key = \Yii::$app->request->headers->get('agile-api-key-header');
        
        if (!$api_key){
            throw new \yii\web\ForbiddenHttpException();
        }
//        
        $this->_user = \app\models\User::findOne(['apiKey' => $api_key]);
        if (!$this->_user){
            throw new \yii\web\ForbiddenHttpException();
        }
        
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return parent::beforeAction($action);
    }
    
    
    public function actionProjectLang($project_key = NULL){
        
        return FrequencyProjectLang::getFrequencyLangN($project_key, $this->_user);
        
    }

    

    public function actionTextQuality(){      
        
        if (\Yii::$app->request->isPost){
            $post = \Yii::$app->request->post();
            $model = new Issue();
            $model->description = $post['issue']['description'];     
            
            $lang = $post['property']['lang_code'];
            $prj = $post['property']['project_code'];
            
            return \app\modules\decision\helpers\Decision::textQuality($model->description, $lang, $prj);
            
        }
        
    }
    
}
