<?php

namespace app\modules\jira;

class Module extends \yii\base\Module
{
    public function init()
    {
        parent::init();
        $this->layout = '@app/views/layouts/angular.php';
    }
}
