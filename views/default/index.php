<?php

use kartik\export\ExportMenu;
use kartik\tabs\TabsX;
use yii\bootstrap\Modal;
use yii\helpers\Html;
use yii\helpers\Url;
use johnitvn\ajaxcrud\CrudAsset;

CrudAsset::register($this);

$this->title = "Enveditor";
//echo "<pre>" . print_r(Yii::$app->env->getBackups(), true) . "</pre>";
$gridColumn = [

    'name',
    'value',
    'comment',
    [
        'class' => 'kartik\grid\ActionColumn',
        'template' => '{update} {delete}',
        'urlCreator' => function ($action, $model, $key, $index) {
            return Url::to(['/enveditor/default/' . $action, 'key' => $model["name"]]);
        },
        'updateOptions' => ['role' => 'modal-remote', 'title' => 'Update', 'data-toggle' => 'tooltip'],
        'deleteOptions' => ['role' => 'modal-remote', 'title' => 'Delete',
            'data-confirm' => false, 'data-method' => false, // for overide yii data api
            'data-request-method' => 'post',
            'data-toggle' => 'tooltip',
            'data-confirm-title' => 'Are you sure?',
            'data-confirm-message' => 'Are you sure want to delete this item'],
    ]
];

$overview = \kartik\grid\GridView::widget([
    'id' => 'crud-datatable',
    'dataProvider' => $dataProvider,
    'columns' => $gridColumn,
    'pjax' => true,
    'panel' => [
        'type' => \kartik\grid\GridView::TYPE_DEFAULT,
        'heading' => Html::encode("Your current .env-file"),
        'before' => Yii::t('app', 'Here you can see the content of your current active .env.'),
    ],
    'export' => false,
    // your toolbar can include the additional full export menu
    'toolbar' => [
//        ['content' =>
//            Html::a('<i class="fa fa-plus"></i>', ['create'],
//                ['role' => 'modal-remote', 'title' => 'Nieuwe parameter toevoegen', 'class' => 'btn btn-success'])
//        ],
    ],
]);

?>

<?php
$items = [
    [
        'label' => Yii::t('app', 'Overview'),
        'content' => $overview,
        'active' => true,
    ],
    [
        'label' => Yii::t('app', 'Add New'),
        'url' => ['create'],
        'linkOptions' => ['role' => 'modal-remote']
    ],
    [
        'label' => Yii::t('app', 'Backups'),
        'content' => $this->render('backup', ['backupProvider' => $backupProvider])
    ]
];

yii\widgets\Pjax::begin(['id' => "enveditor-reload-pjax", 'timeout' => "10000"]);

echo TabsX::widget([
    'items' => $items,
    'position' => TabsX::POS_ABOVE,
    'encodeLabels' => false
]);

yii\widgets\Pjax::end();

$this->registerJs('$(document).on("pjax:timeout", function(event) {
  // Prevent default timeout redirection behavior
  event.preventDefault()
});');
?>
<?php Modal::begin([
    "id" => "ajaxCrudModal",
    'size' => Modal::SIZE_LARGE,
    "footer" => "",// always need it for jquery plugin
]) ?>
<?php Modal::end(); ?>

