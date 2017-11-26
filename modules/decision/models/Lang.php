<?php

namespace app\modules\decision\models;

/** @property integer $id */
/** @property string $code */
/** @property string $name */
class Lang extends \yii\db\ActiveRecord{
   
    public function rules() {
        return [
            [['code', 'name'], 'string', 'max' => 100]
        ];
    }
    
}
