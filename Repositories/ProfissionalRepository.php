<?php

namespace CNESIntegration\Repositories;

use CNESIntegration\Connection\Conn;

class ProfissionalRepository extends Repository
{
    public function getProfissionais($filter = [], $options = [])
    {
        $manager = Conn::getManager();
        $query = new \MongoDB\Driver\Query($filter, $options);

        $result = $manager->executeQuery($this->database .'.'. $this->collection, $query);

        return $result;
    }
}