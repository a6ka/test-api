<?php

namespace app\tests\api;

use ApiTester;

class HomeCest
{
    public function mainPage(ApiTester $i)
    {
        $i->sendGET('/');
        $i->seeResponseCodeIs(200);
        $i->seeResponseIsJson();
    }

}