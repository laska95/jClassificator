<?php

namespace app\modules\decision\controllers;

use \app\modules\jira\models\Issue;
use \app\modules\decision\models\FrequencyProjectLang;
use \app\modules\jira\providers\JiraProvider;
use \app\modules\decision\helpers\Parser;
use \app\modules\decision\helpers\Word;

class FullApiController extends \yii\web\Controller {

    private $_user = NULL;

    public function beforeAction($action) {
        $this->_user = \Yii::$app->user->identity;

        if (!$this->_user) {
            $api_key = \Yii::$app->request->headers->get('agile-api-key-header');
            if (!$api_key) {
                throw new \yii\web\ForbiddenHttpException();
            }

            $this->_user = \app\models\User::findOne(['apiKey' => $api_key]);
            if (!$this->_user) {
                throw new \yii\web\ForbiddenHttpException();
            }
        }

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return parent::beforeAction($action);
    }

    public function actionProjectLang($project_key = NULL) {
        if (\Yii::$app->request->isGet) {
            return FrequencyProjectLang::getFrequencyLangN($project_key, $this->_user);
        }

        if (\Yii::$app->request->isPost) {
            $post = \Yii::$app->request->post();
            $project = \app\modules\jira\models\Project::findOne(['key' => $project_key, 'jira_url' => $this->_user->jiraUrl]);
            if (!$project) {
                $project = new \app\modules\jira\models\Project();
                $project->key = $project_key;
                $project->jira_url = $this->_user->jiraUrl;
                $project->save();
            }

            $issues_description = [];

            //задачі описані вручну
            foreach ($post['issue_arr'] as $one_issue) {
                $issues_description[] = $one_issue['description'];
            }

            //задачі задані як масив ключів

            $provider = JiraProvider::getInstance();
            $issue_keys = array_filter($post['issue_key_arr'], function ($one) use ($project_key) {
                return !empty($one) && preg_match("/^({$project_key}-)/u", $one);
            });

            $jql = Issue::getJQuery(['key__in' => $issue_keys]);
            $issues = $provider->getIssueList($jql, ['description']);
            if (isset($issues->getResponse()['issues'])) {
                foreach ($issues->getResponse()['issues'] as $one) {
                    $issues_description[] = $one['fields']['description'];
                }
            }
            $text = '';
            foreach ($issues_description as $one) {
                //видаляємо посилання
                $text .= preg_replace('/(https?:\/\/)([\w\.-]+)\/?/u', '', $one);
            }

            return FrequencyProjectLang::createNew($text, $project->id);
        }
    }

    public function actionTextQuality() {

        if (\Yii::$app->request->isPost) {
            $post = \Yii::$app->request->post();

            $ret = [];

            $lang = $post['lang_code'];
            $prj = $post['project_code'];

            $issues = $post['issue_arr'];
            foreach ($issues as $one) {
                $ret[] = \app\modules\decision\helpers\Decision::textQuality(
                                $one['description'], $lang, $prj, $this->_user);
            }

            return $ret;
        }
    }

    public function actionAvailabilityDescription() {
        if (\Yii::$app->request->isPost) {
            $post = \Yii::$app->request->post();

            $lang = $post['lang_code'];
            $prj = $post['project_code'];

            $issues = [];
            foreach ($post['issue_arr'] as $key => $one) {
                $one['key'] = $key;
                $issues[$key] = $one;
            }

            $provider = JiraProvider::getInstance();
            $jql = Issue::getJQuery(['key__in' => $post["issue_key_arr"]]);
            $jiraIssues = $provider->getIssueList($jql, ['description', 'summary']);
            if (isset($jiraIssues->getResponse()['issues'])) {
                foreach ($jiraIssues->getResponse()['issues'] as $one) {
                    $issues[$one['key']] = [
                        'key' => $one['key'],
                        'summary' => $one['fields']['summary'],
                        'description' => $one['fields']['description']
                    ];
                }
            }

            $ret = [];
            foreach ($issues as $key => $one) {
                $ret[$key] = \app\modules\decision\helpers\Decision::availabilityDescription($one, $this->_user);
            }

            return $ret;
        }
    }

    public function actionPriorityClustering() {
        if (\Yii::$app->request->isPost) {
            $post = \Yii::$app->request->post();

            $ret = [];
            $provider = JiraProvider::getInstance();
            $priorityList = $provider->getIssuePriority()->getResponse();
            foreach ($priorityList as $one) {
                $ret[$one['id']] = [
                    'class' => $one,
                    'items' => []
                ];
            }

            foreach ($post['issue_arr'] as $one) {
                $c = \app\modules\decision\helpers\Decision::getPriorityClustering($one);
                $ret[$c]['items'][] = $one['key'];
            }

            $jql = Issue::getJQuery(['key__in' => $post["issue_key_arr"]]);
            $jiraIssues = $provider->getIssueList($jql, ['duedate', 'timetracking', 'priority']);
            if (isset($jiraIssues->getResponse()['issues'])) {
                foreach ($jiraIssues->getResponse()['issues'] as $one) {

                    $duedate = $one['fields']['duedate'];

                    $one_arr = [
                        'key' => $one['key'],
                        'priority_id' => $one['fields']['priority']['id'],
                        'duedate' => $duedate ? substr($duedate, 0, -8) : null,
                        'remainingEstimateSeconds' => $one['fields']['timetracking']['remainingEstimateSeconds'] ?? null
                    ];

                    $c = \app\modules\decision\helpers\Decision::getPriorityClustering($one_arr);
                    $ret[$c]['items'][] = $one['key'];
                }
            }

            return $ret;
        }
    }

    public function actionLinksClustering() {
        if (\Yii::$app->request->isPost) {
            $post = \Yii::$app->request->post();

            $issues = $post['issue_arr'];
            $provider = JiraProvider::getInstance();

            $jql = Issue::getJQuery(['key__in' => $post["issue_key_arr"]]);
            $jiraIssues = $provider->getIssueList($jql, ['description']);
            if (isset($jiraIssues->getResponse()['issues'])) {
                foreach ($jiraIssues->getResponse()['issues'] as $one) {
                    $issues[] = [
                        'key' => $one['key'],
                        'description' => $one['fields']['description'],
                    ];
                }
            }

            $urls = \app\modules\decision\helpers\Decision::getAllLinks($issues);
            $ret = ['0' => ['url' => null, 'items' => []]];

            foreach ($urls as $u) {
                $ret[] = [
                    'url' => $u,
                    'items' => []
                ];
            }

            foreach ($issues as $one) {

                $set = false;

                foreach ($ret as $i => $ret_one) {

                    if ($i == 0) {
                        continue;
                    }

                    $n = preg_match('#(' . preg_quote($ret_one['url']) . ')#', $one['description']);
                    if ($n) {
                        $ret[$i]['items'][] = $one['key'];
                        $set = TRUE;
                    }
                }

                if ($set == FALSE) {
                    $ret[0]['items'][] = $one['key'];
                }
            }

            return $ret;
        }
    }

    public function actionTextClustering() {

        if (\Yii::$app->request->isPost) {
            
            $post = \Yii::$app->request->post();

            $issues = $post['issue_arr'];
            
            
            $all_text = '';
            
            foreach ($issues as $one){
                $t0 = $one['summary'] . ' ' .$one['summary'] .' '. $one['summary'];
                $t1 = $one['description'];
                $t0 .= ' ' . $t1;
                
                $t1 = preg_replace('#\W#u', ' ', $t0);
                $t1 = preg_replace('#( {2,})#u', ' ', $t1);
                
                $all_text .= $t1;
            }
            
            $f5 = \app\modules\decision\models\FrequencyLang::canculateFrequency($all_text, 5);
            asort($f5);
            $f5 = array_slice($f5, 0.68 * count($f5));  //вектор ключових слів
            $w = [];
            foreach ($issues as $one){
                $w[$one['key']] = [];
                $t0 = $one['summary'] . ' ' .$one['summary'] .' '. $one['summary'];
                $one_text = $t0 . ' '. $one['description'];
                foreach ($f5 as $f_code => $f_one){
                    $n = preg_match_all("#{$f_code}#", $one_text);
                    $w[$one['key']][$f_code] = $n;
                }
            }
            
            return self::tree($w);
        }
        
    }

    
    private function tree($w){
        $ww = [];
        $keys = array_keys($w);
                
        foreach ($keys as $k1){
            $ww[$k1] = [];
            foreach ($keys as $k2){
                $ww[$k1][$k2] = 0;
            }
        }

        $ww_tree = $ww;
        
        $all_d = [];
        foreach ($ww as $key1 => $one_ww){
            foreach ($one_ww as $key2 =>$v2){
                $d = \app\modules\decision\helpers\Decision::qDif($w[$key1], $w[$key2]);
                $ww[$key1][$key2] = $d;
                if ($d >0){
                    $all_d[] = $d;
                }
                
            }
        }
      
        $in_tree = [];
        $i = 0;
        while (count($in_tree) < count($keys)){
            $w_min = self::min2($ww);
            
            foreach ($ww as $k1 => $v1){
                foreach ($v1 as $k2 => $v){
                    if ($v == $w_min){
                        $in_tree[] = $k1;
                        $in_tree[] = $k2;
                        $in_tree = array_unique($in_tree);
                        $ww[$k1][$k2] = -1;
                        $ww[$k2][$k1] = -1;
                        $ww_tree[$k1][$k2] = $w_min;
                        $ww_tree[$k2][$k1] = $w_min;
                        break (2);
                    }
                }
            }
            
            $i++;
            if ($i > 100)                break;
        }
        
        return $ww_tree;
    }
    
    private static function max2($ww){
        $all = [];
        foreach ($ww as $w){
            foreach ($w as $v){
                $all[] = $v;
            }
        }
        return max($all);
    }
    
    private static function min2($ww){
        $all = [];
        foreach ($ww as $w){
            foreach ($w as $v){
                if ($v > 0){
                    $all[] = $v;
                }
                
            }
        }
        return min($all);
    }
}
