<?php

namespace CNESIntegration\Repositories;

use CNESIntegration\Connection\Conn;
use MapasCulturais\App;

class SpaceRepository extends Repository
{
    public function getSpacesMetaByCNES($cnes)
    {
        $app = App::i();
        // Busca o espaço para adicionar um novo relation com o agent
        $query = $app->em->createQuery("SELECT s FROM MapasCulturais\Entities\SpaceMeta s WHERE s.value LIKE :value");
        $query->setParameters([
            "value" => "%{$cnes}%"
        ]);

        return $query->getOneOrNullResult();
    }
}