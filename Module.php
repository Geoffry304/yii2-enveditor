<?php

namespace geoffry304\enveditor;

use Yii;
use yii\web\NotFoundHttpException;

/**
 * customer module definition class
 */
class Module extends \yii\base\Module{

    public $controllerNamespace = 'geoffry304\yii2-enveditor\controllers';

    public $allowedIds = null;

    public function init()
    {
       $ids = str_split($this->allowedIds);
       if (!in_array(Yii::$app->user->id,$ids)){
           throw new NotFoundHttpException('The requested page does not exist.');
       }
        parent::init();
    }

}
