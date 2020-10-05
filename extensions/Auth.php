<?php

namespace app\extensions;

use app\models\Users;
use Throwable;

class Auth
{
    public const PARAM_LOGIN = 'login';
    public const PARAM_TOKEN = 'apiPassword';

    /**
     * @var string System API apiId
     */
    private string $apiLogin;
    /**
     * @var string API password
     */
    private string $apiPassword;

    /**
     * Export API auth
     *
     * @param string $apiLogin
     * @param string $apiPassword
     */
    public function __construct(string $apiLogin, string $apiPassword)
    {
        $this->apiLogin    = $apiLogin;
        $this->apiPassword = $apiPassword;
    }

    /**
     * @return bool
     */
    public function checkAuth(): bool
    {
        try {
            $user = Users::getInstance()->getUserByLogin($this->apiLogin);
        } catch (Throwable $exception) {
            return false;
        }

        return !empty($user['apiPassword']) && $user['apiPassword'] === $this->apiPassword;
    }
}