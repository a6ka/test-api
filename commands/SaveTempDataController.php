<?php

namespace app\commands;

use app\dto\KeyStorageDto;
use app\models\KeyStorages;
use app\models\TempStorage;
use Exception;
use Generator;
use Throwable;
use yii\console\Controller;

class SaveTempDataController extends Controller
{
    private const MAX_INSERT_SIZE = 500;

    public function actionIndex()
    {
        foreach ($this->generateRows() as $rows) {
            if (empty($rows)) {
                break;
            }

            try {
                $saved = KeyStorages::getInstance()->setMultipleData($rows);
            } catch (Throwable $exception) {
                $saved = false;
            }

            $tempStorage = new TempStorage();
            if ($saved) {
                $tempStorage->crearBackup();
            }
        }
    }

    public function actionRestoreBackup()
    {
        $tempStorage = new TempStorage();
        $tempStorage->restoreFromBackup();
    }

    /**
     * @param int $count
     *
     * @return Generator|null
     * @throws Exception
     */
    private function generateRows(): ?Generator
    {
        $tempStorage = new TempStorage();
        while (true) {
            $rows = [];
            for ($k = 0; $k < self::MAX_INSERT_SIZE; $k++) {
                $data = $tempStorage->getRecord();
                if (null === $data) {
                    break;
                }
                $rows []= new KeyStorageDto($data['firstKey'], $data['secondKey'], $data['createdAt']);
            }
            yield $rows;
        }
    }
}