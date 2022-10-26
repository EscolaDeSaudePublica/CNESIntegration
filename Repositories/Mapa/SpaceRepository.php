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

    public function salvarSelo($spaceId, $agentId)
    {
        $dataHora = date('Y-m-d H:i:s');

        $sealId = 1;

        $sqlInsertSeal = "INSERT INTO public.seal_relation 
                    (id, seal_id, object_id, create_timestamp, status, object_type, agent_id, validate_date, renovation_request) 
                    VALUES ((SELECT MAX(id)+1 FROM public.seal_relation) , {$sealId}, '" . $spaceId . "', '{$dataHora}' , '1' , 'MapasCulturais\Entities\Space' , {$agentId}, '2029-12-08 00:00:00' , true)";
        return $this->connection->exec($sqlInsertSeal);
    }

}
