<?php

namespace CNESIntegration\Services;

use CNESIntegration\Connection\Conn;
use CNESIntegration\Connection\ConnMapa;
use CNESIntegration\Repositories\Mapa\AgentRepository;
use CNESIntegration\Repositories\Mapa\SpaceRepository;
use CNESIntegration\Repositories\ProfissionalDWRepository;
use MapasCulturais\App;

class ProfissionalService
{
    public function atualizaProfissionais()
    {
        $start = microtime(true);

        $conn = new Conn();
        $conMapa = $conn->getInstance(Conn::DATABASE_MAPA);

        $app = App::i();

        $userAdmin = $app->repo('User')->findOneBy(['email' => 'desenvolvimento@esp.ce.gov.br']);
        $userCnes = $app->repo('User')->findOneBy(['email' => 'cnes@esp.ce.gov.br']);

        $app->user = $userAdmin;
        $app->auth->authenticatedUser = $userAdmin;

        $profissionalRepository = new ProfissionalDWRepository();
        $cnsS = $profissionalRepository->getAllCnsDistinctProfissionais();

        $agentRepository = new AgentRepository();
        $spaceRepository = new SpaceRepository();

        $i = 1;
        foreach ($cnsS as $cns_) {
            $nome =  $cns_['nome'];
            $cns = (int) $cns_['cns'];
            $data = date('Y-m-d H:i:s');
            $descricao = "CNS: {$cns}";

            $agentMeta = $agentRepository->agentMetaPorCns($cns);

            // se existir o agente, então deve existir a rotina de atualização do profissional
            if ($agentMeta) {
                $agentId = $agentMeta->object_id;

                // retorna as relações com espaços do agente
                $relations = $agentRepository->relationsPorAgent($agentId);

                foreach ($relations as $relation) {
                    // realiza a limpeza dos relacionamentos dos agentes com os espaços
                    if ($agentRepository->deleteRelation($relation->id)) {
                        $app->log->debug("Removendo vinculos do agente {$agentId}");
                        $this->logMsg("Removendo vinculos do agente {$agentId}");
                    }
                }
            } else {
                $agent = [];
                $agent['userCnesId'] = $userCnes->id;
                $agent['nome'] = $nome;
                $agent['data'] = $data;
                $agent['descricao'] = $descricao;

                $agentId = $agentRepository->novoAgent($agent);

                if ($agentId) {
                    $agentMeta = [];
                    $agentMeta['agentId'] = $agentId;
                    $agentMeta['cns'] = $cns;
                    $agentRepository->novoAgentMeta($agentMeta);
                }
            }

            $vinculos = $profissionalRepository->getVinculos($cns);
            foreach ($vinculos as $vinculo_) {
                $space = null;

                $cns = (int) $vinculo_['cns'];
                $cbo = $vinculo_['cbo'] . ' - ' . $vinculo_['descricao_cbo'];
                $cnes = $vinculo_['cnes'];
                $nome = $vinculo_['nome'];

                // $metadata = (array)$vinculo_;
                unset($vinculo_['sexo']);
                unset($vinculo_['cnpj']);
                $jsonVinculo = json_encode($vinculo_);

                $spaceMeta = $spaceRepository->spaceMetaPorCnes($cnes);

                if ($spaceMeta) {
                    $space = $spaceRepository->spacePorId($spaceMeta->object_id);
                }

                // se existir o agente, então deve existir a rotina de atualização do profissional
                if ($agentId) {
                    if (!is_null($space)) {
                        $agentRelation = [];
                        $agentRelation['agentId'] = $agentId;
                        $agentRelation['spaceId'] = $space->id;
                        $agentRelation['cbo'] = $cbo;
                        $agentRelation['data'] = $data;
                        $agentRelation['jsonVinculo'] = $jsonVinculo;

                        $agentRepository->novoAgentRelation($agentRelation);

                        $app->log->debug("Adiciona vinculo EXISTENTE do agent {$agentId} e vinculando ao espaço {$space->id} com CBO: {$cbo}" . PHP_EOL);
                        $this->logMsg("Adiciona vinculo EXISTENTE do agent {$agentId} e vinculando ao espaço {$space->id} com CBO: {$cbo}" . PHP_EOL);
                    }
                } 
            }

            $time_elapsed_secs = microtime(true) - $start;

            $app->log->debug("------------------------------" . $time_elapsed_secs . "------------------------------------" . PHP_EOL);
            $this->logMsg("------------------------------" . $time_elapsed_secs . "------------------------------------" . PHP_EOL);
            $app->log->debug("Linha: " . $i++ . PHP_EOL);
        }
    }

    function logMsg($msg)
    {

        $file = '/var/www/html/protected/application/plugins/CNESIntegration/Logs/logs.txt';
        $date = date('Y-m-d H:i:s');
        $current = file_get_contents($file);
        $current .= "{$date}: {$msg}\n";
        file_put_contents($file, $current);
    }
}
