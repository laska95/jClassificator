<?php


namespace app\modules\jira\models;

/** @property integer $id */
/** @property string $code */
/** @property string $jira_url */
class Project extends \yii\db\ActiveRecord{
    
    public function rules() {
        return [
            [['jira_url', 'key'], 'string'],
            [['jira_url', 'key'], 'required'],
        ];
    }

    
}
