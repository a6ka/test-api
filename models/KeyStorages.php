<?php

namespace app\models;

use app\dto\KeyStorageDto;
use app\extensions\traits\Cache;
use app\extensions\traits\Singleton;
use app\models\activerecord\KeyStorage;
use Yii;
use yii\base\Model;
use yii\db\Exception;
use yii\db\Query;

class KeyStorages extends Model
{
    use Singleton;
    use Cache;

    private const CACHE_KEY = 'keyStorageData';

    /**
     * @param KeyStorageDto $dto
     *
     * @return bool
     * @throws Exception
     */
    public function setData(KeyStorageDto $dto): bool
    {
        $insert = [
            'firstKey'  => $dto->getFirstKey(),
            'secondKey' => $dto->getSecondKey(),
            'createdAt' => $dto->getCreatedAt(),
        ];

        $cnt     = Yii::$app->getDb()->createCommand()->insert(KeyStorage::tableName(), $insert)->execute();
        $success = (bool)$cnt;
        /**
         * Refresh cache
         */
        if ($success) {
            $this->invalidateCache($dto);
        }

        return $success;
    }

    /**
     * @param KeyStorageDto[] $data
     *
     * @return bool
     * @throws Exception
     */
    public function setMultipleData(array $data): bool
    {
        $insert = [];
        foreach ($data as $row) {
            $insert [] = [
                $row->getFirstKey(),
                $row->getSecondKey(),
                $row->getCreatedAt(),
            ];
        }
        $cnt = Yii::$app->getDb()
            ->createCommand()
            ->batchInsert(
                KeyStorage::tableName(),
                ['firstKey', 'secondKey', 'createdAt'],
                $insert
            )
            ->execute();

        $success = (bool)$cnt;
        /**
         * Refresh cache
         */
        if ($success) {
            foreach ($data as $dto) {
                $this->invalidateCache($dto);
            }
        }

        return $success;
    }

    /**
     * @param string $firstKey
     * @param string $secondKey
     *
     * @return array
     */
    public function getData(string $firstKey, string $secondKey): array
    {
        $query = new Query();
        $query
            ->select('*')
            ->from(KeyStorage::tableName())
            ->where([
                'firstKey'  => $firstKey,
                'secondKey' => $secondKey,
            ]);

        return $query->all();
    }

    /**
     * @param array $ids
     *
     * @return int
     * @throws Exception
     */
    public function deleteByIds(array $ids)
    {
        return Yii::$app->getDb()->createCommand()->delete(KeyStorage::tableName(), ['id' => $ids])->execute();
    }

    private function invalidateCache(KeyStorageDto $dto): void
    {
        $this->cache(1, true, true)->getData($dto->getFirstKey(), $dto->getSecondKey());
    }
}