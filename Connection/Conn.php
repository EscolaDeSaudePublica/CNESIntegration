<?php

namespace CNESIntegration\Connection;

use PDO;

class Conn
{
    public static function getConnection()
    {
        try {
            $drive = env('CNES_DW_DB_DRIVE');
            $host = env('CNES_DW_DB_HOST');
            $port = env('CNES_DW_DB_PORT');
            $db = env('CNES_DW_DB_NAME');
            $username = env('CNES_DW_DB_USERNAME');
            $password = env('CNES_DW_DB_PASSWORD');
            
            $dns = "$drive:host=$host;port=$port;dbname=$db;";

	        $conn = new PDO($dns, $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            if ($conn) {
                echo "Connected to the dw database successfully!";
            }

            return $conn;
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}