<?php

namespace app\modules\decision\controllers;

use \app\modules\jira\models\Issue;
use \app\modules\decision\models\FrequencyProjectLang;
use \app\modules\jira\providers\JiraProvider;

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
        if (\Yii::$app->request->isGet){
            return FrequencyProjectLang::getFrequencyLangN($project_key, $this->_user);
        }
        
        if (\Yii::$app->request->isPost){
            $post = \Yii::$app->request->post();
            $issues_description = [];   
                        
            //задачі описані вручну
            foreach ($post['issue_arr'] as $one_issue){
                $issues_description[] = $one_issue['description'];
            }
            
            //задачі задані як масив ключів

            $provider = JiraProvider::getInstance();
            $issue_keys = array_filter($post['issue_key_arr'], function ($one) use ($project_key){
                return !empty($one) && preg_match("/^({$project_key}-)/u", $one);
            });
                        
            $jql = Issue::getJQuery(['key__in' => $issue_keys]);
                        var_dump($jql);
            $issues = $provider->getIssueList($jql, ['description']);
            foreach ($issues->getResponse()['issues'] as $one){
                $issues_description[] =  $one['fields']['description'];
            }
            
            return ($issues_description);
            //TO DO: з $issues_description повинно створювати словник 
        }
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
