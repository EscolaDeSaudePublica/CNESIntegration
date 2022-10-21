<?php

namespace CNESIntegration\Repositories\Mapa;

use CNESIntegration\Connection\Conn;

class AgentRepository
{
    private $connection;

    public function __construct()
    {
        $conn = new Conn();
        $this->connection = $conn->getInstance(Conn::DATABASE_MAPA);
    }

    public function agentMetaPorCns($cns)
    {
        $sth = $this->connection->prepare("SELECT object_id FROM agent_meta WHERE value = '{$cns}'");
        $sth->execute();
        return $sth->fetch(\PDO::FETCH_OBJ);
    }

    public function relationsPorAgent($agentId)
    {
        $sth = $this->connection->prepare(
            "SELECT id FROM agent_relation 
                    WHERE agent_id = {$agentId} 
                    AND object_type = 'MapasCulturais\Entities\Space'");
        $sth->execute();
        return $sth->fetchAll(\PDO::FETCH_OBJ);
    }

    public function deleteRelation($id)
    {
        $stmt = $this->connection->prepare("DELETE FROM agent_relation WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function novoAgent($agent)
    {
        $userCnesId = $agent['userCnesId'];
        $nome = $agent['nome'];
        $data = $agent['data'];
        $descricao = $agent['descricao'];

        $this->connection->exec("INSERT INTO public.agent (user_id, type, name,  create_timestamp, status, is_verified, public_location, update_timestamp, short_description) 
                    VALUES ({$userCnesId}, 1, '{$nome}', '{$data}', '1', 'FALSE', 'TRUE', '{$data}', '{$descricao}')");
        return $this->connection->lastInsertId();
    }

    public function novoAgentMeta($agentMeta)
    {
        $agentId = $agentMeta['agentId'];
        $cns = $agentMeta['cns'];

        return $this->connection->exec("INSERT INTO public.agent_meta (object_id, key, value, id) VALUES ({$agentId}, 'cns', '{$cns}', (SELECT MAX(id)+1 FROM public.agent_meta))");
    }

    public function novoAgentRelation($agentRelation)
    {
        $agentId = $agentRelation['agentId'];
        $spaceId = $agentRelation['spaceId'];
        $cbo = $agentRelation['cbo'];
        $data = $agentRelation['data'];
        $jsonVinculo = $agentRelation['jsonVinculo'];

        $this->connection->exec("INSERT INTO public.agent_relation (agent_id, object_type, object_id, type, has_control, create_timestamp, status, metadata) 
                        VALUES ({$agentId}, 'MapasCulturais\Entities\Space', '{$spaceId}', '{$cbo}', 'FALSE', '{$data}', 1, '{$jsonVinculo}')");
    }
}
