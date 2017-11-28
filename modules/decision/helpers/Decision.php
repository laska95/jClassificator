<?php

namespace app\modules\decision\helpers;

use \app\modules\decision\models\FrequencyLang;
use \app\modules\decision\models\FrequencyProjectLang;

class Decision {
    /* availabilityDescription */

    const AD_GOOD = 1;
    const AD_EMPTY = 2;
    const AD_BAD = 3;
    const AD_ONLY_URL = 4;

    public static function getAD_Labels() {
        return[
            self::AD_GOOD => 'Задача містить коректний опис',
            self::AD_EMPTY => 'Задача не описана',
            self::AD_BAD => 'Задача описана не коректно',
            self::AD_ONLY_URL => 'Опис містить тільки url посилання'
        ];
    }

    public static function textQuality($text, $lang, $project = NULL, $user) {

        //таблиця із словником

        if ($lang == 'project') {
            $f_text = FrequencyProjectLang::canculateFrequency($text, 1);
            $f_abc = FrequencyProjectLang::getFrequencyLangN($project, $user, 1);
        } else {
            $f_text = FrequencyLang::canculateFrequency($text, 1);
            $f_abc = FrequencyLang::getFrequencyLangN($lang, 1);
        }

        if (!$f_abc) {
            return FALSE;
        }

        $d0 = self::qDif($f_abc, []);
        $d1 = self::qDif($f_text, $f_abc);
        $d1_norm = 100 - self::normDef($d0, $d1);

        $word_cout = preg_match_all('/\w/u', $text);
        $no_word_cout = preg_match_all('/\W/u', $text);
        $no_word_cout2 = preg_match_all('/\W{2,}/u', $text);

        if ($d1_norm > 0) {
            $d1_norm *= (1 - ($no_word_cout + $no_word_cout2) / $word_cout);
        }

        if ($d1_norm > 68) {
            $text = 'Висова збіжність';
        } elseif ($d1_norm > 32) {
            $text = 'Нормальна збіжність';
        } elseif ($d1_norm > 0) {
            $text = 'Погана збіжність';
        } else {
            $text = 'Тексти не підлягають порівнянню';
        }

        return [
            'value' => $d1_norm,
            'text' => $text
        ];
    }

    public static function availabilityDescription($issue, $user) {
        preg_match('/(^[[:upper:]]+\-)/U', $issue['key'], $project_key);
        if (isset($project_key[0]) && is_string($project_key[0])) {
            $project_key = substr($project_key[0], 0, -1);
        }

        $urls = [];
        $url_count = preg_match_all('/(https?:\/\/([\w\.-]+\/?)+)/', $issue['description'], $urls);
        $clean_url_desc = preg_replace('/(https?:\/\/([\w\.-]+\/?)+)/', '', $issue['description']);
        $clean_noabc_desc = preg_replace('/[\W_]/u', '', $clean_url_desc);

        if (!$clean_noabc_desc && $url_count == 0) {
            return [
                'value' => self::AD_EMPTY,
                'text' => self::getAD_Labels()[self::AD_EMPTY]
            ];
        }

        $clean_summary = preg_replace('/[\W_]/u', '', $issue['summary']);
        if ($url_count > 0 && strlen($clean_noabc_desc) < strlen($clean_summary)) {
            return [
                'value' => self::AD_ONLY_URL,
                'text' => self::getAD_Labels()[self::AD_ONLY_URL],
                'url' => $urls
            ];
        }

        //чи заголовок не дублює опис?        
        $f_summary = FrequencyProjectLang::canculateFrequency($clean_summary, 1);
        $f_description = FrequencyProjectLang::canculateFrequency($issue['description'], 1);

        $d0 = self::qDif($f_description, []);
        $d1 = self::qDif($f_description, $f_summary);
        $d1_norm = 100 - self::normDef($d0, $d1);
        $quality = ($d1_norm < 50) ? TRUE : FALSE;

        //чи опис нормально написаний?
        $quality2 = self::textQuality($clean_url_desc, 'project', $project_key, $user);
        if ($quality2 !== FALSE) {
            $quality *= ($quality2['value'] > 32) ? TRUE : FALSE;
        }

        if ($quality) {
            return [
                'value' => self::AD_GOOD,
                'text' => self::getAD_Labels()[self::AD_GOOD],
                'quality_summary' => $d1_norm ?? NULL,
                'quality_description' => $quality2 ?? NULL,
            ];
        } else {
            return [
                'value' => self::AD_BAD,
                'text' => self::getAD_Labels()[self::AD_BAD],
                'quality_summary' => $d1_norm ?? NULL,
                'quality_description' => $quality2 ?? NULL,
            ];
        }
    }

    public static function getPriorityClustering($issue) {
        $hp = 2; //високий пріоритет 
        //2017-11-27T18:20:07
        if ($issue['duedate'] !== null) {
            $duedate = date_create_from_format('Y-m-dTG:i:s', $issue['duedate']);
            $timeLeft = $issue['remainingEstimateSeconds'];
            if (time() + abs($timeLeft) >= $duedate) {
                return $hp;
            }
        }

        return $issue['priority_id'];
    }

    public static function getAllLinks($issue_arr) {
        $ret = [];

        foreach ($issue_arr as $one) {
            $urls = [];
            preg_match_all('/(https?:\/\/([\w\.-]+\/?)+)/', $one['description'], $urls);
            foreach ($urls[0] as $u) {
                $ret[] = $u;
            }
        }
        $ret = array_unique($ret);
        sort($ret);
        return $ret;
    }

    public static function qDif($v1, $v2) {
        $s2 = 0; //сума різниці квадратів

        foreach ($v1 as $c => $f) {
            if (isset($v2[$c])) {
                $s2 += ($v1[$c] - $v2[$c]) * ($v1[$c] - $v2[$c]);
                unset($v1[$c]);
                unset($v2[$c]);
            } else {
                $s2 += $v1[$c] * $v1[$c];
                unset($v1[$c]);
            }
        }

        foreach ($v2 as $c => $f) {
            $s2 += $v2[$c] * $v2[$c];
        }

        return sqrt($s2);
    }

    private static function normDef($d0, $d1) {

        return ($d1 * 100) / $d0;
    }

    
    /**
     * @param array $p масив з векторами, які треба кластеризувати
     * @return array масив з кластерами
     */
    public static function clustering($w) {

        $full_graph = self::fullGraph($w);
        $tree = self::tree_prime($full_graph, $w);

        $clusters = self::getClusters($tree);
        $ww = self::fullGraph($w);
        $F0 = self::getTestF($w);
        for ($i = 0; $i < count($ww)/2; $i++) {
            $is_ok = true;
            foreach ($clusters as $one) {
                
                $F = self::getF($one, $w, $ww, $tree);
                if ($F < $F0) {
                    $is_ok = FALSE;
                }
            }

            if (!$is_ok) {
                $tree = self::divTree($tree, $one, $F0, $w, $ww);
                $clusters = self::getClusters($tree);
            } else {
                break;
            }
        }

        return $clusters;
    }

    private static function divTree($tree, $cluster) {
        try {
            $max = self::max2($tree, $cluster);
        } catch (\Exception $ex) {
            var_dump('+++++++++++++++++++++++++++');
            var_dump($tree);
            var_dump($cluster)
            ;
            die();
        }

        foreach ($tree as $key1 => $w1) {
            if (in_array($key1, $cluster)) {
                foreach ($w1 as $key2 => $v) {
                    if ($v == $max) {
                        unset($tree[$key1][$key2]);
                        unset($tree[$key2][$key1]);
                        break(2);
                    }
                }
            }
        }

        return $tree;
    }

    private static function getClusters($tree) {
        $clusters = [];

        foreach ($tree as $key1 => $w1) {
            $g = [$key1];

            foreach ($w1 as $key2 => $v) {
                if ($v > 0) {
                    $g[] = $key2;
                }
            }

            $ci = [];
            foreach ($g as $gkey) {
                $gi_arr = self::getClusterIndexs($clusters, $gkey);
                foreach ($gi_arr as $one_g){
                    $ci[] = $one_g;
                }
            }


            if (empty($ci)) {
                //new cluster
                $clusters[] = $g;
            } elseif (count($ci) == 1) {
                //old cluster
                $ci = $ci[0];
                $clusters[$ci] = array_merge($clusters[$ci], $g);
            } else {
                foreach ($ci as  $c){
                    $g = array_merge($g, $clusters[$c]); 
                }
                
                foreach ($ci as  $c){
                    unset($clusters[$c]);
                }
                
                 $clusters[] = $g;
            }
            
        }

        foreach ($clusters as $ci => $one) {
            $clusters[$ci] = array_unique($one);
        }

        return $clusters;
    }

    private static function getClusterIndexs($clusters, $key) {
        $i = [];
        foreach ($clusters as $ci => $one) {
            if (in_array($key, $one)) {
                $i[] = $ci;
            }
        }
        return $i;
    }

    /**
     * @return array матриця із різницями довжин між усіма векторами
     */
    private static function fullGraph($w, $def = null) {
        $ww = [];
        $keys = array_keys($w);

        foreach ($keys as $k1) {
            $ww[$k1] = [];
            foreach ($keys as $k2) {
                $ww[$k1][$k2] = $def;
            }
        }

        if ($def === null) {
            foreach ($ww as $key1 => $one_ww) {
                foreach ($one_ww as $key2 => $v2) {
                    $d = self::qDif($w[$key1], $w[$key2]);
                    $ww[$key1][$key2] = $d;
                }
            }
        }

        return $ww;
    }

    /**
     * @param type $w - вектор ознак
     * @param array $ww - fullGraph
     * @return array просте дерево
     */
    private static function tree_prime($ww, $w) {
        $ww_tree = self::fullGraph($w, 0);
        $no_in_tree = array_keys($w);
        $in_tree = [$no_in_tree[0]];
        unset($no_in_tree[0]);
        $i = 0;
        while (count($no_in_tree) > 0) {
            $ww0 = self::fullGraph($w, 0);
            foreach ($in_tree as $key_in_tree) {
                foreach ($no_in_tree as $key_no_in_tree) {
                    $ww0[$key_in_tree][$key_no_in_tree] = $ww[$key_in_tree][$key_no_in_tree];
                }
            }

            $w_min = self::min2($ww0);
            foreach ($in_tree as $key_in_tree) {
                foreach ($no_in_tree as $key_no_in_tree) {
                    if ($ww0[$key_in_tree][$key_no_in_tree] == $w_min) {
                        $in_tree[] = $key_no_in_tree;
                        unset($no_in_tree[array_search($key_no_in_tree, $no_in_tree)]);
                        $ww_tree[$key_in_tree][$key_no_in_tree] = $w_min;
                        $ww_tree[$key_no_in_tree][$key_in_tree] = $w_min;
                        break (2);
                    }
                }
            }

            if ($i > 100) {
                break;
            }
        }
        return $ww_tree;
    }

    private static function getTestF($w) {
        //http://www.tsi.lv/sites/default/files/editor/science/Research_journals/Tr_Tel/2003/V1/yatskiv_gousarova.pdf

        $n = count($w);
        $p = count(array_shift($w));
        $z = 0; //0.5

        return 1 - 2 / ($n * $p);
    }

    private static function getF($cluster, $w, $ww, $tree) {

        $w1 = self::getDW2($w, $ww, array_keys($w));

        //тестове розбиття
        $tree_test = self::divTree($tree, $cluster);
        $clusters_text = self::getClusters($tree_test);
        $w2 = [];
        foreach ($clusters_text as $c) {
            $w2[] = self::getDW2($w, $ww, $c);
        }

        return array_sum($w2) / ($w1);
    }

    private static function getDW2($w, $ww, $elem) {
        $sum = 0;
        foreach ($elem as $key_from) {
            foreach ($elem as $key_to) {
                try {
                    $v = $ww[$key_from][$key_to];
                    if ($v > 0) {
                        $sum += $v * $v;
                    }
                } catch (\Exception $ex) {
                    var_dump("EX 410");
                    var_dump($key_from, $key_to);
                    var_dump($ww);
                    die();
                }
            }
        }
        return $sum / 2;
    }

    private static function max2($ww, $cluster = null) {

        $cluster = ($cluster === NULL) ? array_keys($ww) : $cluster;

        $all = [];
        foreach ($ww as $k1 => $w) {
            if (in_array($k1, $cluster)) {
                foreach ($w as $k2 => $v) {
                    $all[] = $v;
                }
            }
        }
        return max($all) ?? -1;
    }

    private static function min2($ww, $start = 0) {
        $all = [];
        foreach ($ww as $w) {
            foreach ($w as $v) {
                if ($v > $start) {
                    $all[] = $v;
                }
            }
        }
        return min($all);
    }

}
