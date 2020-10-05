<?php

namespace app\controllers;

use app\dto\KeyStorageDto;
use app\extensions\AppIO;
use app\extensions\UserRateLimiter;
use app\forms\KeyStorageForm;
use app\extensions\Auth;
use app\models\KeyStorages;
use app\models\TempStorage;
use Yii;
use yii\base\Action;
use yii\filters\RateLimiter;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\Response;

class ApiController extends Controller
{
    /**
     * @param Action $action
     *
     * @return bool
     * @throws HttpException
     * @throws BadRequestHttpException
     */
    public function beforeAction($action)
    {
        AppIO::getInstance()->setAppResponse();
        if (!Yii::$app->request->getIsPost()) {
            throw new HttpException(405, '405x1 Method Not Allowed. This url can only handle the following request methods: POST.');
        }
        $this->checkUser();
        Yii::$app->getResponse()->format = Response::FORMAT_JSON;

        return parent::beforeAction($action);
    }

    public function behaviors()
    {
        return [
            'rateLimiter' => [
                'class' => RateLimiter::class,
                'user'  => new UserRateLimiter()
            ],
        ];
    }

    /**
     * @return array
     * @throws HttpException
     */
    public function actionSetData()
    {
        $dto = $this->prepareData();

        $tempStorage = new TempStorage();
        $tempStorage->addNewRecord($dto);

        return ['saved' => true];
    }

    /**
     * @return array
     * @throws HttpException
     */
    public function actionGetData()
    {
        $dto = $this->prepareData();

        return KeyStorages::getInstance()->cache(300)->getData($dto->getFirstKey(), $dto->getSecondKey());

    }

    /**
     * @throws HttpException
     */
    private function checkUser(): void
    {
        $apiLogin    = trim(Yii::$app->request->get(Auth::PARAM_LOGIN, ''));
        $apiPassword = trim(Yii::$app->request->get(Auth::PARAM_TOKEN, ''));
        $auth        = new Auth($apiLogin, $apiPassword);

        if (!$auth->checkAuth()) {
            throw new HttpException(401, 'Unknown user');
        }
    }

    /**
     * @return KeyStorageDto
     * @throws HttpException
     */
    private function prepareData(): KeyStorageDto
    {
        $form = new KeyStorageForm();
        $form->load(Yii::$app->getRequest()->get(), '');

        if (!$form->validate()) {
            $errors = $form->getFirstErrors();
            throw new HttpException(412, $this->prepareErrorMessage($errors));
        }

        return new KeyStorageDto($form->firstKey, $form->secondKey);
    }

    /**
     * @param array $errors
     *
     * @return string
     */
    private function prepareErrorMessage(array $errors): string
    {
        $errorMessage = '';

        foreach ($errors as $key => $error) {
            $errorMessage .= $key . ':' . $error . '; ';
        }

        return substr(trim($errorMessage),0,-1);
    }
}