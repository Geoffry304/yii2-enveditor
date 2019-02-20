<?php

use yii\helpers\Html;
use yii\helpers\Url; ?>


<div class="panel panel-default">
    <div class="panel-heading">
        <h2 class="panel-title"><?= Yii::t('app', 'Save your current .env file'); ?></h2>
    </div>
    <div class="panel-body">
        <a href="default/backup-create" class="btn btn-primary" role="modal-remote">
            <?= Yii::t('app', 'Create Backup'); ?>
        </a>
        <a href="default/download" class="btn btn-primary" data-pjax="0">
            <?= Yii::t('app', 'Download Current .env'); ?>
        </a>
    </div>
</div>
<?php
$gridColumn = [
    [
        'class' => 'kartik\grid\SerialColumn'
    ],
    'filename',
    'created_at',
    [
        'class' => 'kartik\grid\ActionColumn',
        'template' => '{backup-view} {backup-restore} {backup-download} {backup-delete}',
        'buttons' => [
            'backup-view' => function ($url, $model) {
                return Html::a('<span class="fa fa-eye"></span>',
                    Url::to(["default/backup-view", "file" => $model['filename']]), [
                        'title' => \Yii::t('app', 'View'),
                        'role' => 'modal-remote',
                    ]);
            },
            'backup-restore' => function ($url, $model) {
                return Html::a('<span class="fa fa-refresh"></span>',
                    Url::to(["default/backup-restore", "file" => $model['filename']]), [
                        'title' => \Yii::t('app', 'Restore'),
                        'role' => 'modal-remote',
                        'data-confirm' => false, 'data-method' => false, // for overide yii data api
                        'data-request-method' => 'post',
                        'data-toggle' => 'tooltip',
                        'data-confirm-title' => 'Are you sure?',
                        'data-confirm-message' => 'Are you sure want to restore to this backup?'
                    ]);
            },
            'backup-download' => function ($url, $model) {
                return Html::a('<span class="fa fa-cloud-download"></span>',
                    Url::to(["default/backup-download", "file" => $model['filename']]), [
                        'title' => \Yii::t('app', 'Download'),
                        'data-pjax' => 0
//                        'role' => 'modal-remote',
                    ]);
            },
            'backup-delete' => function ($url, $model) {
                return Html::a('<span class="fa fa-trash-o"></span>',
                    Url::to(["default/backup-delete", "file" => $model['filename']]), [
                        'title' => \Yii::t('app', 'Delete'),
                        'role' => 'modal-remote',
                        'data-confirm' => false, 'data-method' => false, // for overide yii data api
                        'data-request-method' => 'post',
                        'data-toggle' => 'tooltip',
                        'data-confirm-title' => 'Are you sure?',
                        'data-confirm-message' => 'Are you sure want to delete this item'
                    ]);
            },
        ],
    ]
];

echo \kartik\grid\GridView::widget([
    'id' => 'crud-datatable-backups',
    'dataProvider' => $backupProvider,
    'columns' => $gridColumn,
    'pjax' => true,
    'panel' => [
        'type' => \kartik\grid\GridView::TYPE_DEFAULT,
        'heading' => Html::encode("Your available backups"),
        'before' => Yii::t('app', 'Here you can restore one of your available backups.') .
            '<br><span style="color:red;">' . Yii::t('app', 'This overwrites your active .env! Be sure to backup your currently active .env-file!') . '</span>',
    ],
    'export' => false,
    // your toolbar can include the additional full export menu
    'toolbar' => [
    ],
]);
?>
