<?php

namespace CNESIntegration\Services;

use CNESIntegration\Repositories\ProfissionalRepository;
use MapasCulturais\App;

class ProfissionalService 
{
    public function atualizaProfissionais()
    {
        $app = App::i();

        $user = $app->repo('User')->find(8);

        $app->user = $user;
        $app->auth->authenticatedUser = $user;


        $filter = [
            'CNS' => 980016297407245
        ];

        /**
         * retorna dados do mongodb
         */
        $ProfissionalRepository = new ProfissionalRepository();
        $profissionais = $ProfissionalRepository->getProfissionais($filter);

        $i = 1;
        foreach ($profissionais as $profissional) {
            $cns = (int) $profissional->CNS;
            $cbo = $profissional->CBO . ' - ' . $profissional->{'DESCRICAO CBO'};
            $cnes = $profissional->CNES;
            
            // busca no banco do mapa se o CNS está cadastrado, ou seja, se o profissional já foi migrado anteriomente
            $agentMeta = $app->repo('AgentMeta')->findOneBy(['value' => $cns]);

            // se existir o agente, então deve sexistir a rotina de atualização do profissional
            if ($agentMeta) {
                $agent = $agentMeta->owner;

                // retorna as relações com espaços do agente
                $conn = $app->em->getConnection();
                $relations = $conn->fetchAll("SELECT * FROM agent_relation WHERE agent_id = {$agent->id} AND object_type = 'MapasCulturais\Entities\Space'");
                foreach ($relations as $relation) {
                    // realiza a limpeza dos relacionamentos dos agentes com os espaços
                    $relation_ = $app->repo('AgentRelation')->find($relation['id']);
                    $relation_->delete(true);
                }
           
                // Busca o espaço para adicionar um novo relation com o agent
                $spaceMeta = $app->repo('SpaceMeta')->findOneBy(['value' => $cnes]);
                if ($spaceMeta) {
                    $space = $spaceMeta->owner;

                    // adiciona o relacionamento do espaço com o agente retornado do mongodb, concatenando com o CBO (id do cbo + descrição do cbo)
                    $space->createAgentRelation($agent, $cbo);     
                } else {
                    // TODO: Caso o espaço não exista, deve ser adicionado o espaço e em seguida continua o fluxo
                }
            } else {
                // TODO: se não existir o agente, então deve existir a rotina de cadastro do profissional
                echo 'NÃO Existe cns no mapa, deve INSERIR: ' . $cns . '<br>';
            }
            

            /**
             * executa apenas 1 vez dentro do foreach
             */
            if ($i++ == 1) {
                die;
            }
        } 
    }
}