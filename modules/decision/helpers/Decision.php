<?php

namespace app\modules\decision\helpers;

use \app\modules\decision\models\FrequencyLang;

class Decision {
    
    public static function textQuality($text, $lang, $project = NULL){
        
        //таблиця із словником
        $f_class = \app\modules\decision\models\FrequencyLang::class;
                
        $f_text = FrequencyLang::canculateFrequency($text, 1);
        $f_abc = $f_class::getFrequencyLangN($lang, 1);

        $d0 = self::qDif($f_abc, []);
        $d1 = self::qDif($f_text, $f_abc);
        $d1_norm = 100 - self::normDef($d0, $d1);
              
        $word_cout = preg_match_all('/\w/u', $text);
        $no_word_cout = preg_match_all('/\W/u', $text);
        $no_word_cout2 = preg_match_all('/\W{2,}/u', $text);
        
        if ($d1_norm > 0){
            $d1_norm *= (1 - ($no_word_cout+$no_word_cout2)/$word_cout); 
        }
        
        if ($d1_norm > 68){
            $text = 'Висова збіжність';
        } elseif ($d1_norm > 32) {
            $text = 'Нормальна збіжність';
        } elseif($d1_norm > 0) {
            $text = 'Погана збіжність';
        } else {
            $text = 'Тексти не підлягають порівнянню';
        }  
        
        return [
            'value' => $d1_norm,
            'text' => $text
        ];
        
        
    }
    
    
    private static function qDif($v1, $v2){
        $s2 = 0; //сума різниці квадратів
        
        foreach ($v1 as $c => $f){
            if (isset($v2[$c])){
                $s2 += ($v1[$c] - $v2[$c]) * ($v1[$c] - $v2[$c]);
                unset($v1[$c]);
                unset($v2[$c]);
            } else {
                $s2 += $v1[$c] * $v1[$c];
                unset($v1[$c]);
            }
        }
        
        foreach ($v2 as $c => $f){
            $s2 += $v2[$c] * $v2[$c];
        }
        
        return sqrt($s2);
    }
    
    private static function normDef($d0, $d1){
        
        return ($d1 * 100) / $d0;
    }
}
