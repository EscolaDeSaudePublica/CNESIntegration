<?php

namespace CNESIntegration\Repositories\Mapa;

use CNESIntegration\Connection\Conn;

class SpaceRepository
{
    private $connection;

    public function __construct()
    {
        $conn = new Conn();
        $this->connection = $conn->getInstance(Conn::DATABASE_MAPA);
    }

    public function spaceMetaPorCnes($cnes)
    {
        $sth = $this->connection->prepare("SELECT object_id FROM space_meta WHERE value = '{$cnes}'");
        $sth->execute();
        return $sth->fetch(\PDO::FETCH_OBJ);
    }

    public function spacePorId($id)
    {
        $sth = $this->connection->prepare("SELECT id FROM space WHERE id = '{$id}'");
        $sth->execute();
        return $sth->fetch(\PDO::FETCH_OBJ);;
    }

}
