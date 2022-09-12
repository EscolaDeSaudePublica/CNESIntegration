<?php

namespace CNESIntegration\Services;

use CNESIntegration\Repositories\ProfissionalRepository;
use CNESIntegration\Repositories\SpaceRepository;
use MapasCulturais\App;

class ProfissionalService
{
    public function atualizaProfissionais()
    {

        ini_set('display_errors', true);
        error_reporting(E_ALL);

        $app = App::i();

        $userAdmin = $app->repo('User')->find(8);
        $userCnes = $app->repo('User')->find(2);

        $app->user = $userAdmin;
        $app->auth->authenticatedUser = $userAdmin;

        $profissionalRepository = new ProfissionalRepository();
        $cnsS = $profissionalRepository->getAllCnsDistinctProfissionais();

        //print_r($cnsS[0]['cns']);

        // $cnsS = [980016287995799];
        //$i = 0;
        foreach ($cnsS as $cns) {
            $cns = (int) $cns['cns'];

            // busca no banco do mapa se o CNS está cadastrado, ou seja, se o profissional já foi migrado anteriomente
            $agentMeta = $app->repo('AgentMeta')->findOneBy(['value' => $cns]);

            // se existir o agente, então deve existir a rotina de atualização do profissional
            if ($agentMeta) {
                $agent = $agentMeta->owner;

                // retorna as relações com espaços do agente
                $conn = $app->em->getConnection();
                $relations = $conn->fetchAll("
                    SELECT id FROM agent_relation 
                    WHERE agent_id = {$agent->id} 
                    AND object_type = 'MapasCulturais\Entities\Space'
                ");

                foreach ($relations as $relation) {
                    // realiza a limpeza dos relacionamentos dos agentes com os espaços
                    $relation_ = $app->repo('AgentRelation')->find($relation['id']);
                    $relation_->delete(true);
                    $msg = "Removendo vinculos do agente {$agent->id}<br>";
                    $app->log->debug($msg);
                    echo $msg;
                }
            }

            $app->log->debug(PHP_EOL . PHP_EOL);

            $vinculos = $profissionalRepository->getVinculos($cns);
            foreach ($vinculos as $vinculo_) {
                $cns = (int) $vinculo_['cns'];
                $cbo = $vinculo_['cbo']. ' - ' . $vinculo_['descricao_cbo'];
                $cnes = $vinculo_['cnes'];
                $nome = $vinculo_['nome'];

                // $metadata = (array)$vinculo_;
                unset($vinculo_['sexo']);
                unset($vinculo_['cnpj']);

                //$spaceRepository = new SpaceRepository();
                //$spaceMeta = $spaceRepository->getSpacesMetaByMapa($cnes);
                $spaceMeta = $app->repo('SpaceMeta')->findOneBy(['value' => $cnes]);
                

                if ($spaceMeta) {
                    $space = $spaceMeta->owner;
                }

                // se existir o agente, então deve existir a rotina de atualização do profissional
                if ($agentMeta) {

                    $agent = $agentMeta->owner;

                    // adiciona o relacionamento do espaço com o agente retornado do banco cnes,, concatenando com o CBO (id do cbo + descrição do cbo)
                    $spaceAgentRelation = new \MapasCulturais\Entities\SpaceAgentRelation();
                    $spaceAgentRelation->owner = $space;
                    $spaceAgentRelation->agent = $agent;
                    $spaceAgentRelation->group = $cbo;
                    $spaceAgentRelation->metadata = json_encode($vinculo_);
                    $spaceAgentRelation->save(true);

                    $msg = "Add vinculo existente do agent {$agent->id} e vinculando ao espaço {$space->id} com CBO: {$cbo}" . PHP_EOL . '<br>';
                    echo $msg;
                    $app->log->debug($msg);
                } else {
                    // TODO: se não existir o agente, então deve existir a rotina de cadastro do profissional
                    $agent = new \MapasCulturais\Entities\Agent;
                    $agent->user = $userCnes;
                    $agent->_type = 1;
                    $agent->name = $nome;
                    $agent->shortDescription = "CNS: {$cns}";
                    $agent->setMetadata('cns', $cns);
                    $agent->save(true);
                    echo "Novo agente {$agent->id}";

                    // adiciona o relacionamento do espaço com o agente retornado do banco cnes, concatenando com o CBO (id do cbo + descrição do cbo)
                    //$space->createAgentRelation($agent, $cbo);
                    $spaceAgentRelation = new \MapasCulturais\Entities\SpaceAgentRelation;
                    $spaceAgentRelation->owner = $space;
                    $spaceAgentRelation->agent = $agent;
                    $spaceAgentRelation->group = $cbo;
                    $spaceAgentRelation->metadata = json_encode($vinculo_);
                    $spaceAgentRelation->save(true);

                    $msg = "Add vinculo do novo agent {$agent->id} e vinculando ao espaço {$space->id} com CBO: {$cbo}<br>" . PHP_EOL . '<br>';
                    $app->log->debug($msg);
                    echo $msg;
                }
            }

        }
    }
}
