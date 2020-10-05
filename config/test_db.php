<?php
$db = require __DIR__ . '/db.php';
// test database! Important not to run tests on production or development databases
$db['dsn'] = 'mysql:host=172.19.0.102;dbname=testdb';

return $db;
