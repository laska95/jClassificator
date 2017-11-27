<?php


namespace app\modules\decision\models;

/** @property integer $id */
/** @property string $code */
/** @property string $jira_url */
class Project extends \yii\db\ActiveRecord{
    
    public function rules() {
        return [
            [['jira_url', 'code'], 'string'],
            [['jira_url', 'code'], 'required'],
        ];
    }

    
}
