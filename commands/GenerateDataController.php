<?php

namespace app\commands;

use app\models\activerecord\KeyStorage;
use Exception;
use Generator;
use Yii;
use yii\base\ExitException;
use yii\console\Controller;
use yii\helpers\Console;

class GenerateDataController extends Controller
{
    private const MAX_INSERT_SIZE = 500;

    /**
     * @throws ExitException
     * @throws \yii\db\Exception
     * @throws Exception
     */
    public function actionIndex()
    {
        echo "Enter row count: ";
        $count = Console::stdin();
        if (!$this->validateCount($count)) {
            echo "Incorrect value" . PHP_EOL;
            Yii::$app->end();
        }
        $count = (int)$count;

        $start = 0;
        Console::startProgress($start, $count);
        $firstIteration = true;
        foreach ($this->generateRows($count) as $rows) {
            $start += count($rows);
            Yii::$app->getDb()->createCommand()
                ->batchInsert(KeyStorage::tableName(), ['firstKey', 'secondKey', 'createdAt'], $rows)
                ->execute();

            $this->showMemoryUsage($firstIteration);
            $firstIteration = false;

            Console::updateProgress($start, $count);
        }
        Console::endProgress();
    }

    private function validateCount($count): bool
    {
        $result = true;
        if ($result && !is_numeric($count)) {
            $result = false;
        }

        if ($result && (int)$count <= 0) {
            $result = false;
        }

        return $result;
    }

    /**
     * @param int $count
     *
     * @return Generator|null
     * @throws Exception
     */
    private function generateRows(int $count): ?Generator
    {
        while ($count > 0) {
            $rows = [];
            $size = $count <= self::MAX_INSERT_SIZE ? $count : self::MAX_INSERT_SIZE;
            for ($k = 0; $k < $size; $k++) {
                $rows []= [
                    'firstKey'  => $this->generateRandomString(),
                    'secondKey' => $this->generateRandomString(),
                    'createdAt' => $this->generateRandomDate(),
                ];
            }
            $count -= $size;
            yield $rows;
        }
    }

    private function generateRandomString(): string
    {
        return md5(mt_rand());
    }

    /**
     * @return string
     * @throws Exception
     */
    private function generateRandomDate(): string
    {
        $startPeriod   = '2019-01-01 00:00:00';
        $endPeriod     = date("Y-m-d H:i:s");
        $randTimeStamp = random_int(strtotime($startPeriod), strtotime($endPeriod));

        return date("Y-m-d H:i:s", $randTimeStamp);
    }

    private function showMemoryUsage(bool $firstIteration = true): void
    {
        if (!$firstIteration) {
            Console::moveCursorPrevLine(1);
        }
        Console::clearLine();
        Console::moveCursorTo(1);
        echo "Memory usage: " . round(memory_get_peak_usage()/1024/1024, 2) . " MB" . PHP_EOL;
    }
}