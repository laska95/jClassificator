<?php

namespace app\modules\decision\controllers;

use \app\modules\jira\models\Issue;

class FullApiController extends \app\components\FullApiController{
    
    public function actionTextQuality(){      
        
        if (\Yii::$app->request->isPost){
            $post = \Yii::$app->request->post();
            $model = new Issue();
            $model->description = $post['issue']['description'];     
            
            $lang = $post['property']['lang_code'];
            $prj = $post['property']['project_code'];
            
            return \app\modules\decision\helpers\Decision::textQuality($model->description, $lang, $prj);
            
        }
        
    }
    
}
