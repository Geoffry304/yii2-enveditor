<?php

/* @var $this yii\web\View */
/* @var $model app\models\BerekeningWaardes */
?>
<div class="env-view">
    <table class="table table-striped">
        <tr>
            <th><?= Yii::t('app', 'Key') ?></th>
            <th><?= Yii::t('app', 'Backup Value') ?></th>
            <th><?= Yii::t('app', 'Backup Comment') ?></th>
        </tr>
        <?php foreach ($keys as $key => $values): ?>
            <tr>
                <td><?= $key ?></td>
                <td><?php if ($values['backupValue'] == $values['nowValue']) {
                        echo $values['backupValue'];
                    } else {
                        echo "<span style='color:red; cursor:pointer;' title='Current: ".$values['nowValue']."'>" . (($values['backupValue']) ? $values['backupValue'] : "NOT SET") . "</span>";
                    } ?>
                </td>
                <td><?php if ($values['backupComment'] == $values['nowComment']) {
                        echo $values['backupComment'];
                    } else {
                        echo "<span style='color:red; cursor:pointer;' title='Current: ".$values['nowComment']."'>" . (($values['backupComment']) ? $values['backupComment'] : "NOT SET") . "</span>";
                    } ?>
                </td>

            </tr>
        <?php endforeach; ?>
    </table>

</div>
