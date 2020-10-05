<?php

namespace app\tests\api;

use ApiTester;

class ApiCest
{
    public function badMethod(ApiTester $i)
    {
        $i->sendGET('/api/get-data');
        $i->seeResponseCodeIs(405);
        $i->seeResponseIsJson();

        $i->sendGET('/api/set-data');
        $i->seeResponseCodeIs(405);
        $i->seeResponseIsJson();
    }

    public function invalidCredentials(ApiTester $i)
    {
        $i->sendPOST('/api/get-data', [
            'login' => 'unknown_login',
            'apiPassword' => 'unknown_password',
        ]);
        $i->seeResponseCodeIs(401);
        $i->seeResponseIsJson();
    }

    public function setValidData(ApiTester $i)
    {
        $i->sendPOST('/api/set-data', [
            'login' => 'admin',
            'apiPassword' => 'gGy73pWMg55VhdtaYHh83eW3x',
            'firstKey' => '241acec7dcf97ac590222595a4e1374c',
            'secondKey' => '012629531f170a6a77004dafc7f5061a',
        ]);
        $i->seeResponseCodeIs(200);
        $i->seeResponseIsJson();
        $i->seeResponseJsonMatchesJsonPath('$.data.saved');
    }

    public function getValidData(ApiTester $i)
    {
        $i->sendPOST('/api/get-data', [
            'login' => 'admin',
            'apiPassword' => 'gGy73pWMg55VhdtaYHh83eW3x',
            'firstKey' => '241acec7dcf97ac590222595a4e1374c',
            'secondKey' => '012629531f170a6a77004dafc7f5061a',
        ]);
        $i->seeResponseCodeIs(200);
        $i->seeResponseIsJson();
        $i->seeResponseJsonMatchesJsonPath('$.data[*].id');
    }

    public function getInvalidData(ApiTester $i)
    {
        $i->sendPOST('/api/get-data', [
            'login' => 'admin',
            'apiPassword' => 'gGy73pWMg55VhdtaYHh83eW3x',
            'firstKey' => 'unknown',
            'secondKey' => 'unknown',
        ]);
        $i->seeResponseCodeIs(200);
        $i->seeResponseIsJson();
        $i->dontSeeResponseJsonMatchesJsonPath('$.data[*].id');
    }

    public function invalidSearchParams(ApiTester $i)
    {
        $i->sendPOST('/api/get-data', [
            'login' => 'admin',
            'apiPassword' => 'gGy73pWMg55VhdtaYHh83eW3x',
        ]);
        $i->seeResponseCodeIs(412);
        $i->seeResponseIsJson();
    }
}