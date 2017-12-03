<?php

namespace app\modules\decision\controllers;

use \app\modules\jira\models\Issue;
use \app\modules\decision\models\FrequencyProjectLang;
use \app\modules\jira\providers\JiraProvider;
use \app\modules\decision\helpers\Parser;
use \app\modules\decision\helpers\Word;
use \app\modules\decision\helpers\Decision;

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
            $issues_description[] = $post['text'] ?? '';

            //задачі задані як масив ключів

            $provider = JiraProvider::getInstance();
            $issue_keys = array_filter($post['issue_key_arr'], function ($one) use ($project_key) {
                return !empty($one) && preg_match("/^({$project_key}-)/u", $one);
            });

            $jql = Issue::getJQuery(['key__in' => $issue_keys]);
            $issues = $provider->getIssueList($jql, ['description'], 0, 100);
            if (isset($issues->getResponse()['issues'])) {
                foreach ($issues->getResponse()['issues'] as $one) {
                    $issues_description[] = $one['fields']['description'];
                }
            }


            //JQL
            $jql = $post['jql'];
            $issues = $provider->getIssueList($jql, ['description'], 0, 100);
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
                $ret[$one['key']] = \app\modules\decision\helpers\Decision::textQuality(
                                $one['description'], $lang, $prj, $this->_user);
            }
            $provider = JiraProvider::getInstance();
            $issue_key_arr = array_filter($post['issue_key_arr']);
            if ($issue_key_arr) {
                $jql = Issue::getJQuery(['key__in' => $issue_key_arr]);
                $issues = $provider->getIssueList($jql, ['description'], 0, 100);
                if (isset($issues->getResponse()['issues'])) {
                    foreach ($issues->getResponse()['issues'] as $one) {
                        $ret[$one['key']] = \app\modules\decision\helpers\Decision::textQuality(
                                        $one['fields']['description'], $lang, $prj, $this->_user);
                    }
                }
            }

            //JQL
            if ($post['jql']) {
                $jql = $post['jql'];
                $issues = $provider->getIssueList($jql, ['description'], 0, 100);
                if (isset($issues->getResponse()['issues'])) {
                    foreach ($issues->getResponse()['issues'] as $one) {
                        $ret[$one['key']] = \app\modules\decision\helpers\Decision::textQuality(
                                        $one['fields']['description'], $lang, $prj, $this->_user);
                    }
                }
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
            if (isset($post['issue_arr'])) {
                foreach ($post['issue_arr'] as $key => $one) {
                    $issues[$one['key']] = $one;
                }
            }
            $provider = JiraProvider::getInstance();

            $issue_key_arr = (isset($post['issue_key_arr'])) ? array_filter($post['issue_key_arr']) : NULL;
            if ($issue_key_arr) {
                $jql = Issue::getJQuery(['key__in' => $post["issue_key_arr"]]);
                $jiraIssues = $provider->getIssueList($jql, ['description', 'summary'], 0, 100);
                if (isset($jiraIssues->getResponse()['issues'])) {
                    foreach ($jiraIssues->getResponse()['issues'] as $one) {
                        $issues[$one['key']] = [
                            'key' => $one['key'],
                            'summary' => $one['fields']['summary'],
                            'description' => $one['fields']['description']
                        ];
                    }
                }
            }

            //JQL
            if ($post['jql']) {
                $jql = $post['jql'];
                $jiraIssues = $provider->getIssueList($jql, ['description', 'summary'], 0, 500);
                $providerRet = $jiraIssues->getResponse();
                if (isset($providerRet['issues'])) {
                    foreach ($providerRet['issues'] as $one) {
                        $issues[$one['key']] = [
                            'key' => $one['key'],
                            'summary' => $one['fields']['summary'],
                            'description' => $one['fields']['description']
                        ];
                    }
                }
            }

            $ret = [];
            foreach ($issues as $key => $one) {
                $ret[$key] = \app\modules\decision\helpers\Decision::availabilityDescription($one, $this->_user);
            }

            $ret0 = [
                Decision::AD_GOOD => [
                    'class' => [
                        'id' => Decision::AD_GOOD,
                        'label' => Decision::getAD_Labels()[Decision::AD_GOOD]
                    ],
                    'items' => [],
                    'datails' => []
                ],
                Decision::AD_EMPTY => [
                    'class' => [
                        'id' => Decision::AD_EMPTY,
                        'label' => Decision::getAD_Labels()[Decision::AD_EMPTY]
                    ],
                    'items' => [],
                    'datails' => []
                ],
                Decision::AD_BAD => [
                    'class' => [
                        'id' => Decision::AD_BAD,
                        'label' => Decision::getAD_Labels()[Decision::AD_BAD]
                    ],
                    'items' => [],
                    'datails' => []
                ],
                Decision::AD_ONLY_URL => [
                    'class' => [
                        'id' => Decision::AD_ONLY_URL,
                        'label' => Decision::getAD_Labels()[Decision::AD_ONLY_URL]
                    ],
                    'items' => [],
                    'datails' => []
                ]
            ];

            foreach ($ret as $issue_key => $val) {
                $ret0[$val['value']]['items'][] = $issue_key;
                $q = Decision::textQuality(
                                $issues[$issue_key]['description'], $lang, $prj, $this->_user);
                $ret0[$val['value']]['datails'][$issue_key] = [
                    'quality' => $q
                ];
            }

            return $ret0;
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
                    'class' => [
                        'id' => $one['id'],
                        'label' => $this->getPriorityLabel($one['id'])
                    ],
                    'items' => []
                ];
            }

            if (isset($post['issue_arr'])) {
                foreach ($post['issue_arr'] as $one) {
                    $c = \app\modules\decision\helpers\Decision::getPriorityClustering($one);
                    $ret[$c]['items'][] = $one['key'];
                }
            }

            if (isset($post["issue_key_arr"])) {
                $jql = Issue::getJQuery(['key__in' => $post["issue_key_arr"]]);
                $jiraIssues = $provider->getIssueList($jql, ['duedate', 'timetracking', 'priority'], 0, 100);
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
            }


            return $ret;
        }
    }

    private function getPriorityLabel($pi) {
        if ($pi == '1') {
            return "Дуже високий, блокуючий";
        } elseif ($pi == '2') {
            return "Високий";
        } elseif ($pi == '3') {
            return "Нормальний";
        } elseif ($pi == '4') {
            return "Низький";
        } elseif ($pi == '5') {
            return "Дуже низький";
        } else {
            return '---';
        }
    }

    public function actionLinksClustering() {
        if (\Yii::$app->request->isPost) {
            $post = \Yii::$app->request->post();

            $issues = isset($post['issue_arr']) ? $post['issue_arr'] : [];
            $provider = JiraProvider::getInstance();

            $issue_key_arr = (isset($post["issue_key_arr"])) ? array_filter($post["issue_key_arr"]) : Null;
            if ($issue_key_arr) {
                $jql = Issue::getJQuery(['key__in' => $issue_key_arr]);
                $jiraIssues = $provider->getIssueList($jql, ['description'], 0, 100);
                if (isset($jiraIssues->getResponse()['issues'])) {
                    foreach ($jiraIssues->getResponse()['issues'] as $one) {
                        $issues[] = [
                            'key' => $one['key'],
                            'description' => $one['fields']['description'],
                        ];
                    }
                }
            }

            if ($post['jql']) {
                $jql = $post['jql'];
                $jiraIssues = $provider->getIssueList($jql, ['description'], 0, 100);
                if (isset($jiraIssues->getResponse()['issues'])) {
                    foreach ($jiraIssues->getResponse()['issues'] as $one) {
                        $issues[] = [
                            'key' => $one['key'],
                            'description' => $one['fields']['description'],
                        ];
                    }
                }
            }

            $urls = \app\modules\decision\helpers\Decision::getAllLinks($issues);
            $ret = [];
            $ret[] = [
                'class' => [
                    'url' => null,
                    'label' => '(без посилань)'
                ],
                'items' => []
            ];

            foreach ($urls as $u) {
                $l = '';
                preg_match('(https?:\/\/([\w\.-]+\/?))', $u, $l);
                $ret[] = [
                    'class' => [
                        'url' => $u,
                        'label' => $l[0] ?? '---'
                    ],
                    'items' => []
                ];
            }

            foreach ($issues as $one) {

                $set = false;

                foreach ($ret as $i => $ret_one) {

                    if ($i == 0) {
                        continue;
                    }

                    $n = preg_match('#(' . preg_quote($ret_one['class']['url']) . ')#', $one['description']);
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

            $issues = isset($post['issue_arr']) ? $post['issue_arr'] : [];
            $provider = JiraProvider::getInstance();

            $issue_key_arr = (isset($post['issue_key_arr'])) ? array_filter($post['issue_key_arr']) : NULL;
            if ($issue_key_arr) {
                $jql = Issue::getJQuery(['key__in' => $post["issue_key_arr"]]);
                $jiraIssues = $provider->getIssueList($jql, ['description', 'summary'], 0, 20);
                if (isset($jiraIssues->getResponse()['issues'])) {
                    foreach ($jiraIssues->getResponse()['issues'] as $one) {
                        $issues[] = [
                            'key' => $one['key'],
                            'summary' => $one['fields']['summary'],
                            'description' => $one['fields']['description'],
                        ];
                    }
                }
            }

            if (isset($post['jql'])) {
                $jql = $post['jql'];
                $jiraIssues = $provider->getIssueList($jql, ['description', 'summary'], 0, 20);
                if (isset($jiraIssues->getResponse()['issues'])) {
                    foreach ($jiraIssues->getResponse()['issues'] as $one) {
                        $issues[] = [
                            'key' => $one['key'],
                            'summary' => $one['fields']['summary'],
                            'description' => $one['fields']['description'],
                        ];
                    }
                }
            }

            $ret0 = [];

            foreach ($issues as $one) {
                $ret0[$one['key']] = Decision::findLike($one, $issues);
                ;
            }

            $clusters = [];
            foreach ($ret0 as $key => $like_elem) {
                $key_group = [];
                foreach ($like_elem as $like_key => $like_v) {
                    if ($like_v > 0) {
                        $key_group[] = $like_key;
                    }
                }

                $ci = [];
                foreach ($key_group as $one_key) {
                    $ci_new = Decision::getClusterIndexs($clusters, $one_key);
                    foreach ($ci_new as $cin){
                        $ci[] = $cin;
                    }
                }

                if (empty($ci)) {
                    $clusters[] = $key_group;
                } elseif (count($ci) == 1) {
                    //one old cluster
                    try {
                        foreach ($key_group as $ok) {
                            $clusters[$ci[0]][] = $ok;
                        }
                        $clusters[$ci[0]] = array_unique($clusters[$ci[0]]);
                    } catch (\Exception $ex) {
                        var_dump($ex->getMessage());
                        var_dump($ci);
                        var_dump($clusters);
                        var_dump($key_group);
                        die;
                    }
                } else {
                    $clusters[] = $key_group;
                    $marge_cluster = $key_group;
                    foreach ($ci as $c) {
                        foreach ($clusters[$c] as $one_val) {
                            $marge_cluster[] = $one_val;
                        }
                    }

                    foreach ($ci as $c) {
                        unset($clusters[$c]);
                    }

                    $clusters[] = array_unique($marge_cluster);
                }
            }


            $ret = [];

            foreach ($clusters as $i => $r_one) {
                $ret[] = [
                    'class' => [
                        'id' => $i
                    ],
                    'items' => $r_one
                ];
            }

            return $ret;
        }
    }

    public function actionTextClustering2() {

        if (\Yii::$app->request->isPost) {

            $post = \Yii::$app->request->post();

            $issues = isset($post['issue_arr']) ? $post['issue_arr'] : [];
            $provider = JiraProvider::getInstance();

            $issue_key_arr = (isset($post['issue_key_arr'])) ? array_filter($post['issue_key_arr']) : NULL;
            if ($issue_key_arr) {
                $jql = Issue::getJQuery(['key__in' => $post["issue_key_arr"]]);
                $jiraIssues = $provider->getIssueList($jql, ['description', 'summary'], 0, 20);
                if (isset($jiraIssues->getResponse()['issues'])) {
                    foreach ($jiraIssues->getResponse()['issues'] as $one) {
                        $issues[] = [
                            'key' => $one['key'],
                            'summary' => $one['fields']['summary'],
                            'description' => $one['fields']['description'],
                        ];
                    }
                }
            }

            if (isset($post['jql'])) {
                $jql = $post['jql'];
                $jiraIssues = $provider->getIssueList($jql, ['description', 'summary'], 0, 20);
                if (isset($jiraIssues->getResponse()['issues'])) {
                    foreach ($jiraIssues->getResponse()['issues'] as $one) {
                        $issues[] = [
                            'key' => $one['key'],
                            'summary' => $one['fields']['summary'],
                            'description' => $one['fields']['description'],
                        ];
                    }
                }
            }

            //clean issues
            $clean_issues_text = [];
            $text_mm = [];
            foreach ($issues as $one) {
                $text0 = ($one['description'] ?? '') . ' ' . ($one['summary'] ?? '') . ' ' . ($one['summary'] ?? '');
                $text0 = preg_replace('#\W#u', ' ', $text0);
                $text0 = preg_replace('#( {2,})#u', ' ', $text0);
                $clean_issues_text[$one['key']] = $text0;
            }

            foreach ($clean_issues_text as $key1 => $text1) {
                $text_mm[$key1] = [];
                foreach ($clean_issues_text as $key2 => $text2) {
                    $text_mm[$key1][$key2] = Decision::dif2FText($text1, $text2);
                }
            }



            $vv_min = [];
            $vv_max = [];
            foreach ($text_mm as $key => $v) {
                foreach ($v as $i => $one_v) {
                    //min
                    if (!isset($vv_min[$i])) {
                        $vv_min[$i] = ($one_v < 0) ? $one_v : 0;
                    } elseif ($vv_min[$i] > $one_v) {
                        $vv_min[$i] = $one_v;
                    }
                }
            }

            foreach ($text_mm as $key => $v) {
                foreach ($v as $i => $v_one) {
                    $text_mm[$key][$i] -= $vv_min[$i];
                }
            }

            //to [0:100]
            $vv_max = [];
            foreach ($text_mm as $key => $v) {
                foreach ($v as $i => $one_v) {
                    //min
                    if (!isset($vv_max[$i])) {
                        $vv_max[$i] = $one_v;
                    } elseif ($vv_max[$i] < $one_v) {
                        $vv_max[$i] = $one_v;
                    }
                }
            }
//нормалізація координат
            foreach ($text_mm as $key => $v) {
                foreach ($v as $i => $v_one) {
                    $text_mm[$key][$i] = 100 * $text_mm[$key][$i] / $vv_max[$i];
                }
            }

//            return $text_mm;
            //видалення не значущих координат

            $r0 = Decision::clustering2($text_mm);
            $ret = [];

            foreach ($r0 as $i => $r_one) {
                $ret[] = [
                    'class' => [
                        'id' => $i
                    ],
                    'items' => $r_one
                ];
            }

            return $ret;
        }
    }

    public function actionTextClusteringOld() {

        if (\Yii::$app->request->isPost) {

            $post = \Yii::$app->request->post();

            $issues = isset($post['issue_arr']) ? $post['issue_arr'] : [];
            $provider = JiraProvider::getInstance();

            $issue_key_arr = (isset($post['issue_key_arr'])) ? array_filter($post['issue_key_arr']) : NULL;
            if ($issue_key_arr) {
                $jql = Issue::getJQuery(['key__in' => $post["issue_key_arr"]]);
                $jiraIssues = $provider->getIssueList($jql, ['description', 'summary'], 0, 20);
                if (isset($jiraIssues->getResponse()['issues'])) {
                    foreach ($jiraIssues->getResponse()['issues'] as $one) {
                        $issues[] = [
                            'key' => $one['key'],
                            'summary' => $one['fields']['summary'],
                            'description' => $one['fields']['description'],
                        ];
                    }
                }
            }

            if (isset($post['jql'])) {
                $jql = $post['jql'];
                $jiraIssues = $provider->getIssueList($jql, ['description', 'summary'], 0, 20);
                if (isset($jiraIssues->getResponse()['issues'])) {
                    foreach ($jiraIssues->getResponse()['issues'] as $one) {
                        $issues[] = [
                            'key' => $one['key'],
                            'summary' => $one['fields']['summary'],
                            'description' => $one['fields']['description'],
                        ];
                    }
                }
            }


            $all_keys = [];
            $all_text = '';

            $all_keys = Decision::getKeyWords($issues);
            $clean_issues = [];
            $w = [];

            foreach ($issues as $one) {
                $title = $one['summary'];
                $title = preg_replace('#\W#u', ' ', $title);
                $title = preg_replace('#( {2,})#u', ' ', $title);

                $text = $one['description'];
                $text = preg_replace('#\W#u', ' ', $text);
                $text = preg_replace('#( {2,})#u', ' ', $text);

                $all_text .= ' ' . $title . ' ' . $text;
                $clean_issues[$one['key']] = $title . ' ' . $title . ' ' . $title . ' ' . $text;
            }

            foreach ($clean_issues as $issue_key => $issue_text) {
                $w[$issue_key] = [];
                foreach ($all_keys as $key_kode) {
                    $n = preg_match_all("#{$key_kode}#", $issue_text);
                    $w[$issue_key][$key_kode] = $n;
                }
            }

            $d_max = [];
            $d_sum = [];
            $d_dif = [];
            foreach ($w as $issue_key => $old_w) {
                foreach ($old_w as $i => $n) {

                    if (!isset($d_max[$i])) {
                        $d_max[$i] = $n;
                        $d_sum[$i] = $n;
                    } else {
                        if ($d_max[$i] < $n) {
                            $d_max[$i] = $n;
                        }
                        $d_max[$i] += $n;
                    }
                }
            }

            foreach ($d_max as $i => $val) {
                $d_dif[$i] = $val - $d_sum[$i] / count($w);
            }

            //нормалізація координат
            foreach ($w as $issue_key => $old_w) {
                foreach ($old_w as $i => $n) {
                    $w[$issue_key][$i] = ($d_max[$i]) ? ($n * 100 / $d_max[$i]) : 0;
                }
            }

            //видалення не значущих координат
            $d_dif_max = max($d_dif);
            foreach ($d_max as $i => $v) {
                if ($v < 0.32 * $d_dif_max || $v > 0.68 * $d_dif_max) {
                    foreach ($w as $issue_key => $old_w) {
                        unset($w[$issue_key][$i]);
                    }
                }
            }

            $r0 = Decision::clustering($w);
            $ret = [];

            foreach ($r0 as $i => $r_one) {
                $ret[] = [
                    'class' => [
                        'id' => $i
                    ],
                    'items' => $r_one
                ];
            }

            return $ret;
        }
    }

    public function actionFindLike() {

        if (\Yii::$app->request->isPost) {

            $post = \Yii::$app->request->post();

            $issues = isset($post['issue_arr']) ? $post['issue_arr'] : [];
            $provider = JiraProvider::getInstance();

            $issue_key_arr = (isset($post['issue_key_arr'])) ? array_filter($post['issue_key_arr']) : NULL;
            if ($issue_key_arr) {
                $jql = Issue::getJQuery(['key__in' => $post["issue_key_arr"]]);
                $jiraIssues = $provider->getIssueList($jql, ['description', 'summary'], 0, 20);
                if (isset($jiraIssues->getResponse()['issues'])) {
                    foreach ($jiraIssues->getResponse()['issues'] as $one) {
                        $issues[] = [
                            'key' => $one['key'],
                            'summary' => $one['fields']['summary'],
                            'description' => $one['fields']['description'],
                        ];
                    }
                }
            }

            if (isset($post['jql'])) {
                $jql = $post['jql'];
                $jiraIssues = $provider->getIssueList($jql, ['description', 'summary'], 0, 20);
                if (isset($jiraIssues->getResponse()['issues'])) {
                    foreach ($jiraIssues->getResponse()['issues'] as $one) {
                        $issues[] = [
                            'key' => $one['key'],
                            'summary' => $one['fields']['summary'],
                            'description' => $one['fields']['description'],
                        ];
                    }
                }
            }

//$issues - масив з подібними задачами


            if (isset($post['issue_like']['key'])) {
                $jis = $provider->getIssue($post['issue_like']['key'], 'description, summary')->getResponse();
                $like_issue = [
                    'key' => $jis['key'],
                    'description' => $jis['fields']['description'],
                    'summary' => $jis['fields']['summary'],
                ];
            } else {
                $like_issue = $post['issue_like'];
            }
            $ret = Decision::findLike($like_issue, $issues);

            return $ret;
        }
    }

}
