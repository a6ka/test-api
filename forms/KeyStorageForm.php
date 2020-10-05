<?php

namespace app\forms;

use yii\base\Model;

class KeyStorageForm extends Model
{
    public $firstKey;
    public $secondKey;

    public function rules()
    {
        return [
            [['firstKey', 'secondKey'], 'required'],
            [['firstKey', 'secondKey'], 'string', 'max' => 32],
        ];
    }

    public function attributeLabels()
    {
        $attributes = array_keys($this->getAttributes());

        return array_combine($attributes, $attributes);
    }
}