<?php

use yii\db\Migration;

/**
 * Class m200615_064216_unique
 */
class m200615_064216_unique extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(
            'key_storage_unique',
            [
                'id'        => $this->primaryKey()->unsigned(),
                'firstKey'  => $this->string(32)->notNull(),
                'secondKey' => $this->string(32)->notNull(),
                'createdAt' => $this->dateTime()->notNull()
            ],
            'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB'
        );
        $this->createIndex('ks_keys', 'key_storage_unique', ['firstKey', 'secondKey'], true);
        $this->createIndex('ks_created', 'key_storage_unique', ['createdAt']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200615_064216_test cannot be reverted.\n";

        return false;
    }
}
