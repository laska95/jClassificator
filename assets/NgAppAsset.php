<?php

/**
 * Created by PhpStorm.
 * User: MackRais
 * site: http://mackrais.com
 * email: mackraiscms@gmail.com
 */

namespace app\assets;

use yii\web\AssetBundle;
use yii\web\JqueryAsset;

class NgAppAsset extends AssetBundle {

    public $publishOptions = [
        'forceCopy' => false,
    ];
    public $css = [
        'css/site.css',
        'css/base.css'
    ];
    public $js = [
        'js/angular/plugins/ui-bootstrap-tpls-2.5.0.min.js',
        'js/angular/moment.js',
        'js/bootstrap-plugins/bootbox.js',
        'js/jquery.mCustomScrollbar.concat.min.js',
        'js/angular/base.js',
        'js/helpers.js',
        'js/angular/other-scripts.js',
        'js/angular/plugins/angular-base64.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
        'yii\jui\JuiAsset',
    ];
    public $jsOptions = array(
//        'position' => \yii\web\View::POS_HEAD
    );

    public function init() {
        $path = \Yii::getAlias('@app/web/js/angular/directives/*.js');
        foreach (glob($path) as $file) {
            $f = explode(\Yii::getAlias('@app') . '/web/', $file)[1];
            $this->js [] = $f;
        }

        $path = \Yii::getAlias('@app/web/js/angular/filters/*.js');
        foreach (glob($path) as $file) {
            $f = explode(\Yii::getAlias('@app') . '/web/', $file)[1];
            $this->js [] = $f;
        }

        $path = \Yii::getAlias('@app/web/js/angular/services/*.js');
        foreach (glob($path) as $file) {
            $f = explode(\Yii::getAlias('@app') . '/web/', $file)[1];
            $this->js [] = $f;
        }

        $path = \Yii::getAlias('@app/web/js/angular/factories/*.js');
        foreach (glob($path) as $file) {
            $f = explode(\Yii::getAlias('@app') . '/web/', $file)[1];
            $this->js [] = $f;
        }

        $this->js [] = 'js/angular/config.js';

        parent::init();
    }

}
