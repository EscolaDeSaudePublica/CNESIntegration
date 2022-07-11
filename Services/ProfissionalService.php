<?php

namespace CNESIntegration\Services;

use CNESIntegration\Repositories\ProfissionalRepository;
use CNESIntegration\Repositories\SpaceRepository;
use MapasCulturais\App;
use MapasCulturais\Entities\Agent;

class ProfissionalService
{
    private function unique_multidim_array($array, $key) {
        $temp_array = array();
        $i = 0;
        $key_array = array();

        foreach($array as $value) {
            $val = (array) $value;
            if (!in_array($val[$key], $key_array)) {
                $key_array[$i] = $val[$key];
                $temp_array[$i] = $val;
            }
            $i++;
        }
        return $temp_array;
    }

    public function atualizaProfissionais()
    {
        $app = App::i();

        $userAdmin = $app->repo('User')->find(8);
        $userCnes = $app->repo('User')->find(2);

        $app->user = $userAdmin;
        $app->auth->authenticatedUser = $userAdmin;


        $filter = [
            //'CNS' => 700507591592556
        ];
        $options = ['limit' => 500];

        /**
         * retorna dados do mongodb
         */
        $ProfissionalRepository = new ProfissionalRepository();
        $profissionais = $ProfissionalRepository->getProfissionais($filter, $options);
        $i = 1;

        $profi = $profissionais->toArray();
        $unicos = $this->unique_multidim_array($profi, 'CNS');

        foreach ($unicos as $profissional) {
            $cns = (int) $profissional['CNS'];
            $cnes = $profissional['CNES'];

            $spaceRepository = new SpaceRepository();
            $spaceMeta = $spaceRepository->getSpacesMetaByCNES($cnes);

            if ($spaceMeta) {
                $space = $spaceMeta->owner;
            } else {
                // TODO: Caso o espaço não exista, deve ser adicionado o espaço e em seguida continua o fluxo
            }

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
                    $app->log->debug("Removendo vinculos do agente {$agent->id}");
                }
            }

            if ($i++ == 500) {
                break;
            }
        }

        $app->log->debug(PHP_EOL.PHP_EOL);

        $i = 1;
        $profissionais_ = $ProfissionalRepository->getProfissionais($filter);
        foreach ($profissionais_ as $profissional_) {
            $cns = (int) $profissional_->CNS;
            $cbo = $profissional_->CBO . ' - ' . $profissional_->{'DESCRICAO CBO'};
            $cnes = $profissional_->CNES;
            $nome = $profissional_->NOME;

            $agentMeta = $app->repo('AgentMeta')->findOneBy(['value' => $cns]);

            $spaceRepository = new SpaceRepository();
            $spaceMeta = $spaceRepository->getSpacesMetaByCNES($cnes);

            if ($spaceMeta) {
                $space = $spaceMeta->owner;
            }

            // se existir o agente, então deve existir a rotina de atualização do profissional
            if ($agentMeta) {
                $agent = $agentMeta->owner;

                // adiciona o relacionamento do espaço com o agente retornado do mongodb, concatenando com o CBO (id do cbo + descrição do cbo)
                $space->createAgentRelation($agent, $cbo);
                $app->log->debug("Add vinculo existente do agent {$agent->id} e vinculando ao espaço {$space->id} com CBO: {$cbo}" . PHP_EOL);

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


                // adiciona o relacionamento do espaço com o agente retornado do mongodb, concatenando com o CBO (id do cbo + descrição do cbo)
                $space->createAgentRelation($agent, $cbo);
                $app->log->debug("Add vinculo do novo agent {$agent->id} e vinculando ao espaço {$space->id} com CBO: {$cbo}<br>" . PHP_EOL);
            }
            

            /**
             * executa apenas 1 vez dentro do foreach
             */
            if ($i++ == 5000) {
                break;
            }
        } 
    }
}