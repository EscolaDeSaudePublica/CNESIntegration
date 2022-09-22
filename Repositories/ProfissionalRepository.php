<?php

namespace CNESIntegration\Repositories;

use CNESIntegration\Connection\Conn;

class ProfissionalRepository
{
    public function getVinculos($filter)
    {
        $connection = Conn::getInstance();
        $sql = "SELECT * FROM cnesprofissionais WHERE cns=?";

        $sth = $connection->prepare($sql );
        $sth->execute([$filter]);
        $result = $sth->fetchAll(\PDO::FETCH_ASSOC);

        return $result;
    }

    public function getAllCnsDistinctProfissionais()
    {
        $connection = Conn::getInstance();
        $sql = "SELECT DISTINCT cns FROM cnesprofissionais";

        $sth = $connection->prepare($sql);
        $sth->execute();
        $result = $sth->fetchAll();

        return $result;
    }

}
