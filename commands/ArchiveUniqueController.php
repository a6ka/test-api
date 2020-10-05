<?php


namespace app\commands;


use Yii;
use yii\console\Controller;

class ArchiveUniqueController extends Controller
{
    private const TIMEOUT = 300;

    public function actionIndex()
    {
        $db = Yii::$app->getDb();
        $connectionSettings = [
            'dbName' => $this->getDsnAttribute('dbname', $db->dsn),
            'dbUser' => $db->username,
            'dbHost' => $this->getDsnAttribute('host', $db->dsn),
            'dbPassword' => $db->password,
        ];

        $sourceTable = 'key_storage';
        $destinationTable = 'key_storage_unique';

        $ptCommand = "timeout " . self::TIMEOUT . " pt-archiver --ask-pass".
                     " --source D=" . $connectionSettings['dbName'] . ",t=". $sourceTable . ",h=". $connectionSettings['dbHost'] ." --user " . $connectionSettings['dbUser'] . " --password " . $connectionSettings['dbPassword'] . " --no-check-charset --dest t=". $destinationTable . " --limit 10000 --txn-size 1000 --bulk-delete --where 1=1 --ignore";

        exec($ptCommand, $output, $code);

        return (bool) $code;
    }

    private function getDsnAttribute($name, $dsn)
    {
        if (preg_match('/' . $name . '=([^;]*)/', $dsn, $match)) {
            return $match[1];
        }

        return null;
    }
}