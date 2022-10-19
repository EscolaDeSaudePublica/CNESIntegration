<?php

namespace CNESIntegration\Services;

use CNESIntegration\Connection\Conn;
use CNESIntegration\Connection\ConnMapa;
use CNESIntegration\Repositories\ProfissionalDWRepository;
use MapasCulturais\App;

class ProfissionalService
{
    public function atualizaProfissionais()
    {
        $start = microtime(true);

        $conn = new Conn();
        $conMapa = $conn->getInstance(Conn::DATABASE_DW);

        $app = App::i();

        $userAdmin = $app->repo('User')->findOneBy(['email' => 'desenvolvimento@esp.ce.gov.br']);
        $userCnes = $app->repo('User')->findOneBy(['email' => 'cnes@esp.ce.gov.br']);

        $app->user = $userAdmin;
        $app->auth->authenticatedUser = $userAdmin;

        $profissionalRepository = new ProfissionalDWRepository();
        $cnsS = $profissionalRepository->getAllCnsDistinctProfissionais();

        $i = 1;
        foreach ($cnsS as $cns) {
            $cns = (int) $cns['cns'];
            $data = date('Y-m-d H:i:s');


            $sth = $conMapa->prepare("SELECT object_id FROM agent_meta WHERE value = '{$cns}'");
            $sth->execute();
            $agentMeta = $sth->fetch(\PDO::FETCH_OBJ);

            // se existir o agente, então deve existir a rotina de atualização do profissional
            if ($agentMeta) {
                $agentId = $agentMeta->object_id;

                // retorna as relações com espaços do agente
                $conn = $app->em->getConnection();
                $relations = $conn->fetchAll("
                    SELECT id FROM agent_relation 
                    WHERE agent_id = {$agentId} 
                    AND object_type = 'MapasCulturais\Entities\Space'
                ");

                foreach ($relations as $relation) {
                    // realiza a limpeza dos relacionamentos dos agentes com os espaços
                    $stmt = $conMapa->prepare("DELETE FROM agent_relation WHERE id = ?");
                    $stmt->execute([$relation['id']]);
                    $app->log->debug("Removendo vinculos do agente {$agentId}");
                }
            }

            $vinculos = $profissionalRepository->getVinculos($cns);
            foreach ($vinculos as $vinculo_) {
                $space = null;

                $cns = (int) $vinculo_['cns'];
                $cbo = $vinculo_['cbo']. ' - ' . $vinculo_['descricao_cbo'];
                $cnes = $vinculo_['cnes'];
                $nome = $vinculo_['nome'];

                // $metadata = (array)$vinculo_;
                unset($vinculo_['sexo']);
                unset($vinculo_['cnpj']);
                $jsonVinculo = json_encode($vinculo_);

                $sth = $conMapa->prepare("SELECT object_id FROM space_meta WHERE value = '{$cnes}'");
                $sth->execute();
                $spaceMeta = $sth->fetch(\PDO::FETCH_OBJ);

                if ($spaceMeta) {
                    $sth = $conMapa->prepare("SELECT id FROM space WHERE id = '{$spaceMeta->object_id}'");
                    $sth->execute();
                    $space = $sth->fetch(\PDO::FETCH_OBJ);;
                }

                // se existir o agente, então deve existir a rotina de atualização do profissional
                if ($agentMeta) {
                    if (!is_null($space)) {
                        $sth = $conMapa->prepare("SELECT id FROM agent WHERE id = '{$agentMeta->object_id}'");
                        $sth->execute();
                        $agent = $sth->fetch(\PDO::FETCH_OBJ);

                        $conMapa->query("INSERT INTO public.agent_relation (agent_id, object_type, object_id, type, has_control, create_timestamp, status, metadata) 
                        VALUES ({$agent->id}, 'MapasCulturais\Entities\Space', '{$space->id}', '{$cbo}', 'FALSE', '{$data}', 1, '{$jsonVinculo}')");

                        $app->log->debug("Adiciona vinculo EXISTENTE do agent {$agent->id} e vinculando ao espaço {$space->id} com CBO: {$cbo}" . PHP_EOL);
                    }
                } else {
                    $descricao = "CNS: {$cns}";

                    $conMapa->exec("INSERT INTO public.agent (user_id, type, name,  create_timestamp, status, is_verified, public_location, update_timestamp, short_description) 
            VALUES ({$userCnes->id}, 1, '{$nome}', '{$data}', '1', 'FALSE', 'TRUE', '{$data}', '{$descricao}')");
                    $idAgent = $conMapa->lastInsertId();

                    $conMapa->exec("INSERT INTO public.agent_meta (object_id, key, value, id) VALUES ({$idAgent}, 'cns', '{$cns}', (SELECT MAX(id)+1 FROM public.agent_meta))");

                    if (!is_null($space)) {
                        $conMapa->query("INSERT INTO public.agent_relation (agent_id, object_type, object_id, type, has_control, create_timestamp, status, metadata) 
                        VALUES ({$idAgent}, 'MapasCulturais\Entities\Space', '{$space->id}', '{$cbo}', 'FALSE', '{$data}', 1, '{$jsonVinculo}')");

                        $app->log->debug("Adiciona vinculo do NOVO agent {$idAgent} e vinculando ao espaço {$space->id} com CBO: {$cbo}<br>" . PHP_EOL);
                    }

                }
            }

            $time_elapsed_secs = microtime(true) - $start;

            $app->log->debug("------------------------------" . $time_elapsed_secs . "------------------------------------" . PHP_EOL);
            $app->log->debug("Linha: " . $i++ . PHP_EOL);
        }
    }


}