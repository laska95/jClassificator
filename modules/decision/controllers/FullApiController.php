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
            
            $all_keys = [];
            $all_text = '';
            
            foreach ($issues as $one){
                $f_one = [];
                $t0 = $one['summary'] . ' ' .$one['summary'] .' '. $one['summary'];
                $t1 = $one['description'];
                $t0 .= ' ' . $t1;

                $t1 = preg_replace('#\W#u', ' ', $t0);
                $t1 = preg_replace('#( {2,})#u', ' ', $t1);
                
                $f5_one = \app\modules\decision\models\FrequencyLang::canculateFrequency($t1, 5);
                asort($f5_one); 
                $f5_one = array_slice($f5_one, 0.32 * count($f5_one), 0.68 * count($f5_one));
                $all_keys = array_merge($all_keys, array_keys($f5_one));

                $all_text .= $t1;
            }
            
            $w = [];
            foreach ($issues as $one){
                $w[$one['key']] = [];
                $t0 = $one['summary'] . ' ' .$one['summary'] .' '. $one['summary'];
                $one_text = $t0 . ' '. $one['description'];
                foreach ($all_keys as $f_code){
                    $n = preg_match_all("#{$f_code}#", $one_text);
                    $w[$one['key']][$f_code] = $n;
                }
            }

            return \app\modules\decision\helpers\Decision::clustering($w);
        }
        
    }


}
