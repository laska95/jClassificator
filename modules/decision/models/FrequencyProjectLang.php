<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\modules\decision\models;

/** @property integer $id */
/** @property string $code */
/** @property integer $project_id */
/** @property integer $l */

/** @property float $frequency */
class FrequencyProjectLang extends \yii\db\ActiveRecord {

    use FrequencyTrait;

    public function rules() {
        return [
            [['project_id', 'l'], 'integer'],
            [['code'], 'string', 'max' => 10],
            [['frequency'], 'double'],
            [['project_id', 'code', 'frequency'], 'required'],
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

    public static function createNew($text, $project_id) {
  
        foreach ([1, 5] as $n){
            $fr = self::canculateFrequency($text, $n);
              
            if ($n > 3){
                arsort($fr);
                $fr = array_slice($fr, 0, (int)(count($fr) * 0.32));
            }

            $old_fr  = self::find()->where([
                    'project_id' => $project_id ?? 0,
                ])->asArray()->all();
            $old_fr = \yii\helpers\ArrayHelper::map($old_fr, 'code', 'frequency') ?? [];
            
            $fr = self::sumFrequency($old_fr, $fr);
            
            self::deleteAll(['project_id' => $project_id ?? 0]);
            
            foreach ($fr as $c => $f){
                if ($f < 0.1/($n*10)){
                    continue;
                }
                
                $m = new FrequencyProjectLang();
                $m->code = $c;
                $m->frequency = $f;
                $m->project_id = $project_id;
                $m->save();
            }

        }
        
        $new_fr  = self::find()->where([
                'project_id' => $project_id ?? 0,
            ])->asArray()->all();
        return \yii\helpers\ArrayHelper::map($new_fr, 'code', 'frequency') ?? [];
    }

    public static function getFrequencyLangN($project_code, $user = NULL, $n = [1, 5, 7]) {

        $user = $user ?? \Yii::$app->user->identity;
        $project = \app\modules\jira\models\Project::findOne(['key' => $project_code, 'jira_url' => $user->jiraUrl]);

        $arr = self::find()->where([
                    'project_id' => $project->id ?? 0,
                    'l' => $n
                ])->asArray()->all();

        return \yii\helpers\ArrayHelper::map($arr, 'code', 'frequency') ?? [];
    }

}
