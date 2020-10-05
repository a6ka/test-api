<?php

namespace app\extensions;

use Yii;
use app\extensions\traits\Singleton;
use yii\web\HttpException;

class AppIO
{
    use Singleton;

    const CODE_ERROR_UNKNOWN = 500;
    const CODE_ERROR_NOT_IMPLEMENTED = 501;
    const CODE_ERROR_AUTHORIZATION = 401;
    const CODE_ERROR_FORBIDDEN = 403;
    const CODE_ERROR_INVALID_DATA = 412;
    const CODE_ERROR_NOT_FOUND = 404;
    const CODE_ERROR_METHOD_NOT_ALLOWED = 405;
    const CODE_ERROR_BAD_REQUEST = 400;
    const CODE_ERROR_TOO_MANY_REQUESTS = 429;
    const CODE_OK = 200;
    const CODE_CREATED = 201;
    const CODE_ACCEPTED = 202;
    const CODE_ALREADY_REPORTED = 208;

    const STATUS_OK = 1;
    const STATUS_ERROR = 0;

    /**
     * @var array
     */
    public static $availableCodes = [
        self::CODE_ERROR_AUTHORIZATION,
        self::CODE_ERROR_FORBIDDEN,
        self::CODE_ERROR_INVALID_DATA,
        self::CODE_ERROR_NOT_FOUND,
        self::CODE_ERROR_UNKNOWN,
        self::CODE_ERROR_NOT_IMPLEMENTED,
        self::CODE_OK,
        self::CODE_ERROR_BAD_REQUEST,
        self::CODE_ERROR_METHOD_NOT_ALLOWED,
        self::CODE_CREATED,
        self::CODE_ALREADY_REPORTED,
        self::CODE_ACCEPTED,
        self::CODE_ERROR_TOO_MANY_REQUESTS,
    ];

    public static $errorCodes = [
        self::CODE_ERROR_AUTHORIZATION,
        self::CODE_ERROR_FORBIDDEN,
        self::CODE_ERROR_INVALID_DATA,
        self::CODE_ERROR_NOT_FOUND,
        self::CODE_ERROR_UNKNOWN,
        self::CODE_ERROR_BAD_REQUEST,
        self::CODE_ERROR_METHOD_NOT_ALLOWED,
        self::CODE_ERROR_NOT_IMPLEMENTED,
        self::CODE_ERROR_TOO_MANY_REQUESTS,
    ];

    private static $errorMessages = [
        self::CODE_ERROR_UNKNOWN => 'Something went wrong :('
    ];

    private $startTime;

    public function initInstance()
    {
        $this->startTime = microtime(true);
    }

    /**
     * @return $this
     */
    public function setAppResponse()
    {
        Yii::$app->setComponents(
            [
                'response' => [
                    'format'        => yii\web\Response::FORMAT_JSON,
                    'charset'       => 'UTF-8',
                    'class'         => 'yii\web\Response',
                    'on beforeSend' => function ($event) {
                        $response = $event->sender;

                        /** @var HttpException $exception */
                        $exception = Yii::$app->errorHandler->exception;

                        $code = null !== $exception
                            ? ($exception->statusCode ?? $exception->getCode())
                            : Yii::$app->response->getStatusCode();

                        $code                 = in_array((int)$code, self::$availableCodes, true) ? (int)$code : self::CODE_ERROR_UNKNOWN;
                        $response->statusCode = $code;
                        $status               = in_array($code, self::$errorCodes, true) ? self::STATUS_ERROR : self::STATUS_OK;

                        if ((!YII_ENV_DEV || $code !== 500) && (null !== $exception)) {
                            $data = [
                                'code'            => $code,
                                'status'          => $status,
                                'message'         => self::$errorMessages[$code] ?? $exception->getMessage(),
                                'data'            => [],
                                'timestamp'       => time(),
                                'processing_time' => $this->getProcessingTime(),
                            ];
                        } else {
                            $data = [
                                'code'            => $code,
                                'status'          => $status,
                                'message'         => 'OK',
                                'data'            => $response->data ?? [],
                                'timestamp'       => time(),
                                'processing_time' => $this->getProcessingTime(),
                            ];
                        }

                        $response->data = $data;
                    },
                ],

            ]
        );

        return $this;
    }

    /**
     * @param int $precision
     *
     * @return float
     */
    private function getProcessingTime(int $precision = 4)
    {
        return round(microtime(true) - $this->startTime, $precision);
    }
}
