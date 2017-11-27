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
class FrequencyLang extends \yii\db\ActiveRecord {

    use FrequencyTrait;

    public function rules() {
        return [
            [['lang_id', 'l'], 'integer'],
            [['code'], 'string', 'max' => 10],
            [['frequency'], 'double'],
            [['lang_id', 'code', 'frequency'], 'required'],
        ];
    }

    public function beforeSave($insert) {
        try {
            $this->l = iconv_strlen($this->code, 'UTF-8//TRANSLIT');
            return parent::beforeSave($insert);
        } catch (\Exception $ex) {
            return false;
        }
    }

    public static function createNew($text, $lang_id = 1) {
        FrequencyLang::deleteAll(['lang_id' => $lang_id]);

        foreach ([1, 2, 3] as $n) {
            $fr = self::canculateFrequency($text, $n);

            foreach ($fr as $c => $f) {
                if ($f < 0.1 / ($n * 10)) {
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

    public static function getFrequencyLangN($lang_code, $n, $code_arr = NULL) {

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
