<?php

namespace app\modules\decision\controllers;

use \app\modules\decision\models\FrequencyLang;

class DefaultController extends \yii\base\Controller {

    public function actionIndex() {
           
        return $this->render('null');
    }
    
    public function actionUaAbc() {
        $this->layout = '@app/views/layouts/main.php'; 
        FrequencyLang::deleteAll(['<', 'frequency', 0.01]);
        
        $text_model = new \app\modules\decision\models\BigText();
        
        if (\Yii::$app->request->isPost && $text_model->load(\Yii::$app->request->post())){
            FrequencyLang::createNew($text_model->text);
        }
        
        return $this->render('ua_abc');
    }
}