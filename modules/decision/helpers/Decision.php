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
//        var_dump($issue);
        preg_match('/(^[[:upper:]]+\-)/U', $issue['key'], $project_key);
        if (isset($project_key[0]) && is_string($project_key[0])) {
            $project_key = substr($project_key[0], 0, -1);
        }

        $urls = [];
        $url_count = preg_match_all('/(https?:\/\/[\w\.-]+)\/?/', $issue['description'], $urls);
        $clean_url_desc = preg_replace('/(https?:\/\/[\w\.-]+)\/?/', '', $issue['description']);
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

    private static function qDif($v1, $v2) {
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

}
