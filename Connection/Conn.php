<?php

namespace CNESIntegration\Connection;

use PDO;

class Conn
{
    private $pdo;
    const DATABASE_DW = 'dw';
    const DATABASE_MAPA = 'mapa';

    public function getInstance($database)
    {

        if ($database == self::DATABASE_DW) {
            $drive = env('CNES_DW_DB_DRIVE');
            $host = env('CNES_DW_DB_HOST');
            $port = env('CNES_DW_DB_PORT');
            $db = env('CNES_DW_DB_NAME');
            $user = env('CNES_DW_DB_USERNAME');
            $pass = env('CNES_DW_DB_PASSWORD');
        } else {
            $drive = 'pgsql';
            $host = 'db';
            $port = '5432';
            $db = env('DB_NAME');
            $user = env('DB_USER');
            $pass = env('DB_PASS');
        }

        try {
            $this->pdo = new PDO($drive . ":host=" . $host . "; port=" . $port . "; dbname=" . $db . ";", $user, $pass, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        } catch (\PDOException $e) {
            print "Erro: " . $e->getMessage();
        }

        return $this->pdo;
    }
}
