<?php

namespace app\models;

use app\extensions\traits\Singleton;
use yii\base\Model;

class Users extends Model
{
    use Singleton;

    private const AVAILABLE_USERS = [
        ['login' => 'admin', 'apiPassword' => 'gGy73pWMg55VhdtaYHh83eW3x'],
        ['login' => 'manager', 'apiPassword' => 'UaVDABC84BVj8r5frAxwX8vDX'],
    ];

    public function getUserByLogin(string $login): ?array
    {
        $userPosition = array_search($login, array_column(self::AVAILABLE_USERS, 'login'), true);

        return false === $userPosition ? null : self::AVAILABLE_USERS[$userPosition];
    }
}