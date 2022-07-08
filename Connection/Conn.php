<?php

namespace CNESIntegration\Connection;

use MongoDB\Client;

class Conn
{
    public static function getManager()
    {
        try {
            return new \MongoDB\Driver\Manager(env('CNES_MONGO_DB_URI'), ['username' => env('CNES_MONGO_DB_USERNAME'), 'password' => env('CNES_MONGO_DB_PASSOWRD')]);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}