<?php

namespace app\assets;

use yii\web\AssetBundle;
use yii\web\View;

class AngularAsset extends AssetBundle {

    public $sourcePath = '@bower';
    
    public $js = [
        'angular/angular.js',
        'angular-route/angular-route.min.js',
        'angular-ui-router/release/angular-ui-router.min.js',
        'angular-resource/angular-resource.min.js',
    ];
    public $jsOptions = [
        'position' => View::POS_HEAD,
    ];



}
