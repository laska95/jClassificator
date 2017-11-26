<?php

namespace app\modules\decision\models;

class BigText extends \yii\base\Model{
    
    public $text;
    
    public function rules(){
        return [
            [['text'], 'string'],
        ];
    }
    
}
