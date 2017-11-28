<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\modules\jira\controllers;

use \app\modules\jira\providers\JiraProvider;
use \app\modules\jira\models\Issue;

/**
 * Description of FullApiController
 *
 * @author laska
 */
class FullApiController extends \yii\web\Controller{
    
    public function beforeAction($action) {   
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return parent::beforeAction($action);
    }
        
    public function actionGetSelf(){     
        $provider = JiraProvider::getInstance();
        $res = $provider->getSelf();
        $this->configureResponse($res);
        return $res->response;
    }
    
    public function actionGetProjectList(){
        
        $user_id = \Yii::$app->user->id;
        $cache = \Yii::$app->cache;
        $cache_key = 'GetProjectList' .$user_id;
        
        $val = $cache->get($cache_key);
        if ($val === false){
            $provider = JiraProvider::getInstance();
            $res = $provider->getProjectList();
            $this->configureResponse($res);
            $val = $res->response;
            $cache->set($res->response, 60*3);
        }
        
        return $val; 
        
    }
    
    public function actionGetIssueStatusList(){
        
        $user_id = \Yii::$app->user->id;
        $cache = \Yii::$app->cache;
        $cache_key = 'GetIssueList' .$user_id;
        
        $val = $cache->get($cache_key);
        if ($val === false){
            $provider = JiraProvider::getInstance();
            $res = $provider->getIssueStatusList();
            $this->configureResponse($res);
            $val = $res->response;
            $cache->set($res->response, 60*3);
        }
        
        return $val;    
    }
    
    public function actionGetIssueList(){
        $get = \Yii::$app->request->get();
        $jql = Issue::getJQuery($get);
        
        $user_id = \Yii::$app->user->id;
        $cache = \Yii::$app->cache;
        $cache_key = 'GetIssueList'. $user_id . json_encode($jql);
        
        $val = $cache->get($cache_key);
        if ($val === false){
            $fields = Issue::getLoadFields();
            $startAt = $get['startAt'] ?? 0;

            $provider = JiraProvider::getInstance();
            $res = $provider->getIssueList($jql, $fields, $startAt);
            $this->configureResponse($res);
            $val = $res->response;
            
            $cache->set($res->response, 60*3);
        } 

        return $val;
    }

    public function actionGetIssue($key){
        $provider = JiraProvider::getInstance();
        $fields = Issue::getLoadFields();
        $res = $provider->getIssue($key, $fields);
        $this->configureResponse($res);
        return $res->response;
//        $get = \Yii::$app->request->get();
//        $jql = \app\modules\jira\models\Issue::getJQuery($get);
//        $provider = JiraProvider::getInstance();
//        $res = $provider->getIssueList($jql, $get['page'] ?? 0, $get['maxCount'] ?? 50);
//        $this->configureResponse($res);
//        return $res->response;
    }

    public function actionGetIssuePriority(){        
        $user_id = \Yii::$app->user->id;
        $cache = \Yii::$app->cache;
        $cache_key = 'GetIssueList' .$user_id;
        
        $val = $cache->get($cache_key);
        if ($val === false){
            $provider = JiraProvider::getInstance();
            $res = $provider->getIssuePriority();
            $this->configureResponse($res);
            $val = $res->response;
            $cache->set($res->response, 60*3);
        }
        
        return $val;    
    }

    /**
     * Налаштовує відповідь від сервера, залежно від відповіді Jira 
     */
    private function configureResponse($fullResponse){
        
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON; 
        \Yii::$app->response->statusCode = $fullResponse->code;
        
    }
    
}
