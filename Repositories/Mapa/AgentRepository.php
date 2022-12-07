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

    public function agentMetaPorCnsCpf($cns, $cpf)
    {
        $sth = $this->connection->prepare("SELECT object_id FROM agent_meta WHERE value = '{$cns}' or value = '{$cpf}'");
        $sth->execute();
        return $sth->fetch(\PDO::FETCH_OBJ);
    }

    public function existeDocumentoNoAgentMeta($cpf)
    {
        $sth = $this->connection->prepare("SELECT object_id FROM public.agent_meta WHERE value = '{$cpf}'");
        $sth->execute();
        return $sth->fetch(\PDO::FETCH_OBJ);
    }

    public function existeCNSNoAgentMeta($cns)
    {
        $sth = $this->connection->prepare("SELECT object_id FROM public.agent_meta WHERE value = '{$cns}'");
        $sth->execute();
        return $sth->fetch(\PDO::FETCH_OBJ);
    }

    public function relationsPorAgent($agentId)
    {
        $sth = $this->connection->prepare(
            "SELECT id FROM agent_relation 
                    WHERE agent_id = {$agentId} 
                    AND object_type = 'MapasCulturais\Entities\Space' and type <> 'group-admin'");
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

        $agentMeta = $this->connection->exec("INSERT INTO public.agent_meta (object_id, key, value, id) VALUES ({$agentId}, 'cns', '{$cns}', (select nextval('agent_meta_id_seq')))");
        $this->connection->exec("SELECT setval('agent_meta_id_seq', COALESCE((SELECT MAX(id)+1 FROM public.agent_meta), 1), false)");

        return $agentMeta;
    }

    public function inserirDocumentoNoAgentMeta($agentMeta, $cpf)
    {
        $object_id = $agentMeta->object_id;
        $documento = $cpf;

        $agentMeta = $this->connection->exec("INSERT INTO public.agent_meta (object_id, key, value, id) VALUES ({$object_id}, 'documento', '{$documento}', (select nextval('agent_meta_id_seq')))");
        $this->connection->exec("SELECT setval('agent_meta_id_seq', COALESCE((SELECT MAX(id)+1 FROM public.agent_meta), 1), false)");

        return $agentMeta;

    }

    public function inserirCNSNoAgentMeta($agentMeta, $cns)
    {
        $object_id = $agentMeta->object_id;
        $cns = $cns;

        $agentMeta = $this->connection->exec("INSERT INTO public.agent_meta (object_id, key, value, id) VALUES ({$object_id}, 'cns', '{$cns}', (select nextval('agent_meta_id_seq')))");
        $this->connection->exec("SELECT setval('agent_meta_id_seq', COALESCE((SELECT MAX(id)+1 FROM public.agent_meta), 1), false)");

        return $agentMeta;

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

    public function salvarTermsSaude($agentId)
    {
        $saudeid = 250;
        $sth = $this->connection->prepare("SELECT object_id FROM public.term_relation WHERE term_id = {$saudeid} AND object_type = 'MapasCulturais\Entities\Agent' AND object_id = {$agentId}");
        $sth->execute();
        $object_id = $sth->fetchColumn();

        if (!$object_id) {
            $sqlInsertMeta = "INSERT INTO public.term_relation (term_id, object_type, object_id, id) VALUES (
                                                                $saudeid, 
                                                                'MapasCulturais\Entities\Agent', 
                                                                $agentId,  
                                                                (SELECT MAX(id)+1 FROM public.term_relation)
                                                    )";
            $this->connection->exec($sqlInsertMeta);
            $this->connection->exec("SELECT setval('term_relation_id_seq', COALESCE((SELECT MAX(id)+1 FROM public.term_relation), 1), false);");
        }
    }
}
