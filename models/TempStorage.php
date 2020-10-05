<?php


namespace app\models;


use app\dto\KeyStorageDto;
use Yii;
use yii\redis\Connection;

class TempStorage
{
    private Connection $connection;
    private const LIST_NAME = 'temp::keyStorage::unsavedRecords';
    private const BACKUP_LIST_NAME = 'temp::keyStorage::backupRecords';

    public function __construct()
    {
        /** @var Connection $connection */
        $connection       = Yii::$app->redis;
        $this->connection = $connection;
    }

    public function addNewRecord(KeyStorageDto $dto)
    {
        $data = [
            'firstKey' => $dto->getFirstKey(),
            'secondKey' => $dto->getSecondKey(),
            'createdAt' => $dto->getCreatedAt(),
        ];
        $data = json_encode($data);
        $this->connection->rpush(self::LIST_NAME, $data);
    }

    public function getRecord(): ?array
    {
        $data = $this->connection->lpop(self::LIST_NAME);
        $this->connection->rpush(self::BACKUP_LIST_NAME, $data);

        $data = json_decode($data, true);

        return is_array($data) ? $data : null;
    }

    public function crearBackup()
    {
        $this->connection->del(self::BACKUP_LIST_NAME);
    }

    public function restoreFromBackup()
    {
        while (true) {
            $data = $this->connection->lpop(self::BACKUP_LIST_NAME);
            if (empty($data)) {
                break;
            }
            $this->connection->rpush(self::LIST_NAME, $data);
        }
    }
}