<?php

namespace CNESIntegration\Connection;

use PDO;

class ConnMapa
{
    private static $pdo;

    public static function getInstance()
    {
        $drive = 'pgsql';
        $host = 'db';
        $port = '5432';
        $db = env('DB_NAME');
        $user = env('POSTGRES_USER');
        $pass = env('POSTGRES_PASSWORD');

        if (!isset(self::$pdo)) {
            try {
                self::$pdo = new PDO($drive . ":host=" . $host . "; port=" . $port . "; dbname=" . $db . ";", $user, $pass, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
            } catch (\PDOException $e) {
                print "Erro: " . $e->getMessage();
            }
        }
        return self::$pdo;
    }
}
