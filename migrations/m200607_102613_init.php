<?php

use yii\db\Migration;

/**
 * Class m200607_102613_init
 */
class m200607_102613_init extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(
            'key_storage',
            [
                'id'        => $this->primaryKey()->unsigned(),
                'firstKey'  => $this->string(32)->notNull(),
                'secondKey' => $this->string(32)->notNull(),
                'createdAt' => $this->dateTime()->notNull()
            ],
            'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB'
        );
        $this->createIndex('ks_keys', 'key_storage', ['firstKey', 'secondKey']);
        $this->createIndex('ks_created', 'key_storage', ['createdAt']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200607_102613_init cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200607_102613_init cannot be reverted.\n";

        return false;
    }
    */
}
