<?php

namespace app\models\activerecord;

use yii\db\ActiveRecord;

class KeyStorage extends ActiveRecord
{
    public static function tableName()
    {
        return 'key_storage';
    }

    public function rules()
    {
        return [
            [
                [
                    'firstKey',
                    'secondKey',
                    'createdAt',
                ],
                'safe'
            ]
        ];
    }
}