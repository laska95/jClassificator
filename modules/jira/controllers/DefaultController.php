<?php

namespace app\modules\jira\controllers;

class DefaultController extends \yii\base\Controller {

    public function actionIndex() {
        return $this->render('index');
    }

}