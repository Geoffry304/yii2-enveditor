<?php

use yii\helpers\Html;
use kartik\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\BerekeningWaardes */
/* @var $form yii\widgets\ActiveForm */


?>

<div class="env-form">

    <?php //$form = ActiveForm::begin(['type' => ActiveForm::TYPE_HORIZONTAL, 'options' => ['id' => 'berekenings-form', 'class' => 'disable-submit-buttons']]); ?>
    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <?php if ($model->isNewRecord): ?>
        <div class="col-md-12">
            <?= $form->field($model, 'name')->textInput()->label("Key") ?>
        </div>
        <?php else: ?>
        <div class="col-md-12">
            <label>Key</label> : <?= $model->name;?>
        </div>
        <?php endif; ?>
        <div class="col-md-12">
            <?= $form->field($model, 'value')->textInput() ?>
        </div>
        <div class="col-md-12">
            <?= $form->field($model, 'comment')->textInput() ?>
        </div>

    </div>


    <?php if (!Yii::$app->request->isAjax) { ?>
        <div class="form-group">
            <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>
    <?php } ?>

    <?php ActiveForm::end(); ?>

</div>
