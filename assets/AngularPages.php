<?php

namespace app\assets;
use yii\web\View;

class AngularPages extends \yii\web\AssetBundle {

    public $js = [
    ];
    public $jsOptions = [
        'position' => View::POS_HEAD,
    ];

    public function init() {
        $path = \Yii::getAlias('@app/web/ng-pages/*/*.js');
        foreach (glob($path) as $file) {

            $f = explode(\Yii::getAlias('@app') . '/web/', $file)[1];

            $this->js [] = $f;
        }
        return parent::init();
    }

    public $depends = [
        '\app\assets\AngularAsset',
        '\app\assets\NgAppAsset',
    ];

}
