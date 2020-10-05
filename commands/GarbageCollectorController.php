<?php

namespace app\commands;

use app\models\activerecord\KeyStorage;
use app\models\KeyStorages;
use Exception;
use Throwable;
use Yii;
use yii\console\Controller;
use yii\console\widgets\Table;
use yii\db\Query;
use yii\db\Transaction;
use yii\helpers\Console;

class GarbageCollectorController extends Controller
{
    public const PREVIEW_ROW_COUNT = 5;
    public const LIMIT_SIZE = 10000;
    public const CHUNK_SIZE = 1000;

    public function actionIndex()
    {
        Console::clearScreen();
        echo $this->ansiFormat("HELL Cleaner", Console::BOLD, Console::FG_RED) . PHP_EOL;
        echo $this->ansiFormat("WARNING! ", Console::BOLD, Console::FG_RED)
             . "This operation is irreversible!" . PHP_EOL;

        /**
         * Вводим количество дней
         */
        echo "Provide dates range your data will be saved for. All data prior to this dates range will be deleted: ";
        $input = Console::stdin();
        if (!$this->validateDayInterval($input)) {
            echo "Incorrect dates range. БУГАГАГАГГА" . PHP_EOL;
            Yii::$app->end();
        }
        $input = (int)$input;

        $currentDate = date('Y-m-d');
        $newDate     = date('Y-m-d', strtotime('-' . $input . ' day', strtotime($currentDate)));

        /**
         * Подтверждаем корректность введенной даты
         */
        if (!$this->confirm("All data prior to " . $this->ansiFormat($newDate, Console::BOLD, Console::FG_YELLOW) . " will be deleted. Continue?")) {
            echo "You're so boring... :(" . PHP_EOL;
            Yii::$app->end();
        }

        /**
         * Готовим превью записей к удалению. Если записей нет, то удалять нечего и прекращаем скрипт
         */
        $lastRows = $this->getLastRows($newDate);
        if (0 === count($lastRows)) {
            echo "Nothing to delete :(" . PHP_EOL;
            Yii::$app->end();
        }

        /**
         * Превью: количество записей и примеры
         */
        echo 'Rows example:' . PHP_EOL;

        echo Table::widget([
            'headers' => ['id', 'firstKey', 'secondKey', 'createdAt'],
            'rows'    => $lastRows,
        ]);

        /**
         * Подтверждение на старт
         */
        if (!$this->confirm("Are you sure you want to continue?")) {
            Yii::$app->end();
        }
        if (!$this->confirm("Are you " . $this->ansiFormat("REALLY", Console::BOLD) . " sure you want to continue?")) {
            Yii::$app->end();
        }
        echo "Oh... OK..." . PHP_EOL;

        $this->renderTimer();

        /**
         * Chunk delete with progress bar
         */
        $this->deleteProcess($newDate);

        /**
         * Удаление завершено, прощаемся
         */
        echo PHP_EOL . "Deletion complete! The gates of hell are closing. " . PHP_EOL;
        echo $this->ansiFormat("      ,  ,  , , ,", Console::FG_CYAN) .
             "
     <(__)> | | |
     | \/ | \_|_/
     \^  ^/   |
     /\--/\  /|   Come back again!
    /  \/  \/ |" . PHP_EOL;
        echo PHP_EOL;
    }

    private function renderTimer()
    {

        try {
            $time = random_int(10, 15);
        } catch (Exception $e) {
            $time = 666;
        }
        echo "The gates of hell will open after $time seconds. Press \"Ctrl+C \" to leave them closed." . PHP_EOL;
        Console::hideCursor();
        for ($i = $time; $i >= 0; $i--) {
            Console::clearLine();
            Console::moveCursorTo(1);

            echo "Start in: " . $this->ansiFormat($i, $this->getSecondColor($i)) . " sec.";
            sleep(1);
        }
        echo PHP_EOL;
    }

    private function getSecondColor(int $second): int
    {
        $colorMap = [
            0  => Console::FG_RED,
            5  => Console::FG_YELLOW,
            10 => Console::FG_GREEN,
        ];

        $result = Console::FG_GREY;

        foreach ($colorMap as $minValue => $color) {
            if ($second >= $minValue) {
                $result = $color;
            } else {
                break;
            }
        }

        return $result;
    }

    private function validateDayInterval($interval): bool
    {
        $result = true;
        if ($result && !is_numeric($interval)) {
            $result = false;
        }

        if ($result && (int)$interval <= 0) {
            $result = false;
        }

        return $result;
    }

    private function getLastRows($lastDate): array
    {
        $query = new Query();
        $query
            ->select(['id', 'firstKey', 'secondKey', 'createdAt'])
            ->from(KeyStorage::tableName())
            ->where(['<', 'createdAt', $lastDate])
            ->limit(self::PREVIEW_ROW_COUNT);

        return $query->all();
    }

    /**
     * Удаление через PRIMARY KEY
     *
     * @param string $lastDate
     */
    private function deleteProcess(string $lastDate): void
    {
        $deletedRowsCount = 0;
        $query            = $this->prepareSelectQuery($lastDate);

        while (true) {
            $ids = $query->column();
            //Нечего удалять - стопаем цикл
            if (empty($ids)) {
                break;
            }
            $ids = array_chunk($ids, self::CHUNK_SIZE);
            foreach ($ids as $chunk) {
                /** @var Transaction $transaction */
                $transaction = Yii::$app->getDb()->beginTransaction();
                try {
                    $deletedCount = KeyStorages::getInstance()->deleteByIds($chunk);
                    $transaction->commit();
                    $deletedRowsCount += $deletedCount;
                    $this->showDeleteCount($deletedRowsCount);
                } catch (Throwable $exception) {
                    $transaction->rollBack();
                }
            }
        }
    }

    private function showDeleteCount(int $deletedRowsCount): void
    {
        Console::clearLine();
        Console::moveCursorTo(1);

        echo "Row deleted: " . $deletedRowsCount;
    }

    /**
     * @param string $lastDate
     *
     * @return Query
     */
    private function prepareSelectQuery(string $lastDate): Query
    {
        $query = new Query();
        $query
            ->select('id')
            ->from(KeyStorage::tableName())
            ->where(['<', 'createdAt', $lastDate])
            ->limit(self::LIMIT_SIZE);

        $possibleKeys = $this->getPossibleKeys($query->createCommand()->getRawSql());
        $betterKey    = reset($possibleKeys);
        if (!empty($betterKey)) {
            $query->from(KeyStorage::tableName() . ' FORCE INDEX (' . $betterKey . ')');
        }

        return $query;
    }

    private function getPossibleKeys(string $sql): array
    {
        $sql          = 'EXPLAIN ' . $sql;
        $explain      = Yii::$app->getDb()->createCommand($sql)->queryOne();
        $possibleKeys = $explain['possible_keys'] ?? '';

        return explode(',', $possibleKeys) ?: [];
    }
}