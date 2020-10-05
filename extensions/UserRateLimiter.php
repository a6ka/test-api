<?php

namespace app\extensions;


use Yii;
use yii\filters\RateLimitInterface;
use yii\redis\Connection;

class UserRateLimiter implements RateLimitInterface
{
    public const MAX_REQUESTS = 10;
    public const PERIOD_IN_SECONDS = 60;

    public function getRateLimit($request, $action)
    {
        return [self::MAX_REQUESTS, self::PERIOD_IN_SECONDS];
    }

    public function loadAllowance($request, $action)
    {
        $login = $request->get('login');
        $allowance = $this->getStorage()->get($this->generateRedisKey($login));
        $allowance ??= self::MAX_REQUESTS;

        return [(int)$allowance, time()];
    }

    public function saveAllowance($request, $action, $allowance, $timestamp)
    {
        $login = $request->get('login');
        $this->getStorage()->set($this->generateRedisKey($login), $allowance, 'EX', self::PERIOD_IN_SECONDS);
    }

    private function generateRedisKey(string $login): string
    {
        return 'api::rateLimiter::' . $login;
    }

    private function getStorage(): Connection
    {
        return Yii::$app->redis;
    }
}