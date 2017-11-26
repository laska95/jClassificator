<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\modules\decision\models;

/** @property integer $id */
/** @property string $code */
/** @property integer $lang_id */
/** @property integer $l */
/** @property float $frequency */
class FrequencyLang extends \yii\db\ActiveRecord{
    
    public static function tableName() {
        return 'frequency_lang';
    }

    public function rules() {
        return [
            [['lang_id', 'l'], 'integer'],
            [['code'], 'string', 'max' => 10],
            [['frequency'], 'double'],
            [['lang_id', 'code', 'frequency'], 'required'],
        ];
    }

    public function beforeSave($insert) {
        try{
            $this->l = iconv_strlen($this->code, 'UTF-8//TRANSLIT');
            return parent::beforeSave($insert);
        } catch (\Exception $ex) {
            return false;
        }
        
    }

    public static function createNew($text, $lang_id = 1){
        FrequencyLang::deleteAll(['lang_id' => $lang_id]);
                
        foreach ([1, 2, 3] as $n){
            $fr = self::canculateFrequency($text, $n);
                        
            foreach ($fr as $c => $f){
                if ($f < 0.1/($n*10)){
                    continue;
                }
                
                $m = new FrequencyLang();
                $m->code = $c;
                $m->frequency = $f;
                $m->lang_id = $lang_id;
                $m->save();
            }

        }
    }
    
    public static function canculateFrequency($text, $n = 1){
        $text = mb_strtolower($text);
        $codes = self::findAllCode($text, $n);
        $fr = [];
        foreach ($codes as $c){
            $fr[$c] = substr_count($text, $c);
        }
        $all_c = array_sum($fr);
        foreach ($codes as $c){
            $fr[$c] = $fr[$c] * 100 / $all_c;
        }
        
        return $fr;
    }


    public static function sumFrequency($fr1, $fr2){
        $fr_new = [];
        foreach ($fr1 as $c){
            if (isset($fr2[$c])){
                $fr_new[$c] = ($fr1[$c] + $fr2[$c]) / 2;
                unset($fr1[$c]);
                unset($fr2[$c]);
            } else {
                $fr_new[$c] = $fr1[$c] / 2;
                unset($fr1[$c]);
            }
        }
        
        foreach ($fr2 as $c){
            if (isset($fr1[$c])){
                $fr_new[$c] = ($fr1[$c] + $fr2[$c]) / 2;
                unset($fr1[$c]);
                unset($fr2[$c]);
            } else {
                $fr_new[$c] = $fr2[$c] / 2;
                unset($fr1[$c]);
            }
        }
        
        return $fr_new;
    }

    public static function findAllCode($text, $n){
        $abc = [];
        if ($n > 3){            
            $t = preg_match_all("/([^[:word:]])([[:word:]]{{$n},{$n}})/u", $text, $abs );
            $t = array_unique($abs[0]);
            foreach ($t as $one){
                $abc[] = substr($one, 1);
            }
        } else {
            $t = preg_match_all("/[[:word:]]{{$n},{$n}}/u", $text, $abs );
            $abc = array_unique($abs[0]);
        }
        
        return $abc;
    }
    
    public static function getFrequencyLangN($lang_code, $n, $code_arr = NULL){
        
        $lang = Lang::findOne(['code' => $lang_code]);
        
        if ($code_arr){
            $arr = self::find()->where([
                'lang_id' => $lang->id,
                'code' => $code_arr
            ])->asArray()->all();
        } else {
            $arr = self::find()->where([
                'lang_id' => $lang->id,
                'l' => $n
            ])->asArray()->all();
        }
        
        
        
       return \yii\helpers\ArrayHelper::map($arr, 'code', 'frequency');
    }
}
