<?php

use app\modules\decision\models\Lang;
use app\modules\decision\models\FrequencyLang;
use app\modules\decision\models\BigText;
use yii\bootstrap\ActiveForm;
?>

<?php 

$model = new BigText();

?>

<? $form = ActiveForm::begin(); ?>

<?= $form->field($model, 'text')->textarea(); ?>
<?= yii\bootstrap\Html::submitButton('Submit'); ?>

<? ActiveForm::end(); ?>