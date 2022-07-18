<?php

namespace CNESIntegration\Repositories;

use CNESIntegration\Connection\Conn;

class ProfissionalRepository extends Repository
{
    public function getVinculos($filter = [], $options = [])
    {
        $manager = Conn::getManager();
        $query = new \MongoDB\Driver\Query($filter, $options);

        $result = $manager->executeQuery($this->database .'.'. $this->collection, $query);

        return $result;
    }

    public function getAllCnsDistinctProfissionais()
    {
        $manager = Conn::getManager();

        $command = new \MongoDB\Driver\Command([
            'distinct' => $this->collection,
            'key' => 'CNS'
        ]);

        $cursor = $manager->executeCommand($this->database, $command);
        return current($cursor->toArray())->values;
    }
}