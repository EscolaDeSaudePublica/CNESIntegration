<?php

namespace CNESIntegration\Connection;

use PDO;

define('DRIVE', env('CNES_DW_DB_DRIVE'));
define('HOST', env('CNES_DW_DB_HOST'));
define('PORT', env('CNES_DW_DB_PORT'));
define('DBNAME', env('CNES_DW_DB_NAME'));
define('USERNAME', env('CNES_DW_DB_USERNAME'));
define('PASSWORD', env('CNES_DW_DB_PASSWORD'));

class Conn
{
    private static $pdo;

    private function __construct()
    {
        //  
    }

    public static function getInstance()
    {
        if (!isset(self::$pdo)) {
            try {
                $opcoes = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);
                self::$pdo = new PDO(DRIVE . ":host=" . HOST . "; port=" . PORT . "; dbname=" . DBNAME . ";", USERNAME, PASSWORD, $opcoes);
            } catch (\PDOException $e) {
                print "Erro: " . $e->getMessage();
            }
        }
        return self::$pdo;
    }
}
