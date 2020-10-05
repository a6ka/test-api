<?php


namespace app\controllers;


use Yii;
use yii\web\Controller;
use yii\web\Response;

class SiteController extends Controller
{
    public function actionIndex()
    {
        Yii::$app->getResponse()->format = Response::FORMAT_JSON;
        return [':)'];
    }
}