<?php

namespace tests\unit\models;

use app\models\Users;
use Codeception\Test\Unit;

class UsersTest extends Unit
{
    public function testGetUserByLogin()
    {
        expect_that($user = Users::getInstance()->getUserByLogin('admin'));
        expect($user['login'])->equals('admin');

        expect_not(is_null($user));
    }
}
