<?php

namespace CNESIntegration\Services;

use CNESIntegration\Repositories\SpaceRepository;
use MapasCulturais\App;
use MapasCulturais\Types\GeoPoint;
class SpaceService
{

    public function atualizarSpaces()
    {
        ini_set('display_errors', true);
        error_reporting(E_ALL);

        $app = App::i();

        $userAdmin = $app->repo('User')->find(8);

        $app->user = $userAdmin;
        $app->auth->authenticatedUser = $userAdmin;

        $spaceRepository = new SpaceRepository();
        // retorna uma lista com todos os cnes da base do CNES
        $cnes = $spaceRepository->getAllEstabelecimentos();
        
        foreach ($cnes as $cnes_) 
        {
            // retorna todos os dados da view estabelecimentos de um determinado cnes 
            // $resultCnes = $spaceRepository->getEstabelecimentosByCNES(2497654);
            $spaceMeta = $app->repo('SpaceMeta')->findOneBy(['value' => $cnes_['co_cnes']]);
            
            if ($spaceMeta) {

                try {
                    $nomeFantasia = $cnes_["no_razao_social"]; 
                    $location = '(' . $cnes_["nu_longitude"] . ', ' . $cnes_["nu_latitude"] . ')';
                    $geo = new GeoPoint($cnes_["nu_latitude"], $cnes_["nu_longitude"]);
                    if ($cnes_["nu_longitude"] == null || $cnes_["nu_longitude"] == 'nan') {
                        $geo = new GeoPoint(0, 0);
                    }
                   
    
                    $codigoCnes = $cnes_["co_cnes"];
                    $dataAtualizacao = $cnes_["dt_atualizacao"];
                    $tipoUnidade = $cnes_['description'];
    
                    $telefone = 'Não informado';
                    if (@isset($cnes_["nu_telefone"])) {
                        $telefone = $cnes_["nu_telefone"];
                    }

                    $percenteAoSus = $cnes_['atende_sus'];    
                    //$servicos = $cnes_["ds_servico_especializado"];
                   
                    $conn = $app->em->getConnection();
                    $idTipo = $this->retornaIdTipoEstabelecimentoPorNome($conn, $tipoUnidade);
                    if ($idTipo == null || $idTipo == '') {
                        echo $tipoUnidade . PHP_EOL;
                    }
                    
                    $data = date('Y-m-d H:i:s');
                    $idAgenteResponsavel = 8; 
                    
                    
                    $space = $spaceMeta->owner;
                    //$space->location = $location;
                    //$space->_geo_location = $geo ;
                    $space->setLocation($geo);
                    
                    $space->name = 'aaaaa';
                    $space->short_description = $nomeFantasia;
                    $space->long_description = $nomeFantasia;
                    $space->create_timestamp = $data;
                    $space->status = 1;
                    $space->is_verified = 'FALSE';
                    $space->public = 'FALSE';
                    $space->agent_id = $idAgenteResponsavel;
                    $space->type = $idTipo;
                   
                    //$space->setMetadata('teste', 'teste');
                    $space->save(true);


                    // if (isset($servicos)) {
                                                   
                    //     $servicosString = $this->adicionarAcentos($servicos);
    
                    //     $space->setMetadata('instituicao_servicos', $servicosString);
                    // }
                    
                    // $space->setMetadata( 'En_CEP', $cnes_["co_cep"]); 
                    // $space->setMetadata('En_Nome_Logradouro', $resultCnes["no_logradouro"]);
                    // $space->setMetadata('En_Num', $resultCnes["nu_endereco"]);
                    // $space->setMetadata('En_Bairro', $resultCnes["no_bairro"]);
                    // $space->setMetadata('En_Municipio', $resultCnes["municipio"]);
                    // $space->setMetadata('En_Estado', 'CE');
                    // $space->setMetadata('instituicao_cnes', $resultCnes["co_cnes"]);
                    // $space->setMetadata('instituicao_cnes_data_atualizacao', $dataAtualizacao);
                    // $space->setMetadata('instituicao_cnes_competencia', date('m/Y'));
                    // $space->setMetadata('instituicao_tipos_unidades', $this->adicionarAcentos($tipoUnidade));
                    // $space->setMetadata('telefonePublico', $resultCnes["nu_telefone"]);
                    // $space->setMetadata('instituicao_pertence_sus', $percenteAoSus);
    
                    // $this->salvarSelos($conn, $idSpace, $idAgenteResponsavel);
    
    
                } catch (Exception $e) {
                    print_r($e);
                    continue;
                }
                echo "atualizado";
            }

        }
        
    }

    private function adicionarAcentos($frase)
    {
        $arrayComAcento = ['ORGÃOS', 'CAPTAÇÃO', 'NOTIFICAÇÃO', 'PÚBLICA', 'LABORATÓRIO', 'GESTÃO', 'ATENÇÃO', 'BÁSICA', 'DOENÇA', 'CRÔNICA', 'FAMÍLIA',  'ESTRATÉGIA', 'COMUNITÁRIOS', 'LOGÍSTICA',  'IMUNOBIOLÓGICOS', 'REGULAÇÃO', 'AÇÕES', 'SERVIÇOS', 'SERVIÇO', 'HANSENÍASE', 'MÓVEL', 'URGÊNCIAS', 'DIAGNÓSTICO', 'LABORATÓRIO', 'CLÍNICO', 'DISPENSAÇÃO', 'ÓRTESES', 'PRÓTESES', 'REABILITAÇÃO', 'PRÁTICAS', 'URGÊNCIA', 'EMERGÊNCIA', 'VIGILÂNCIA', 'BIOLÓGICOS', 'FARMÁCIA', 'GRÁFICOS', 'DINÂMICOS', 'MÉTODOS', 'PATOLÓGICA', 'INTERMEDIÁRIOS', 'TORÁCICA', 'PRÉ-NATAL', 'IMUNIZAÇÃO', 'CONSULTÓRIO', 'VIOLÊNCIA', 'SITUAÇÃO', 'POPULAÇÕES', 'INDÍGENAS', 'ASSISTÊNCIA', 'COMISSÕES', 'COMITÊS', 'SAÚDE', 'BÁSICA'];

        $arraySemAcento = ['ORGAOS', 'CAPTACAO', 'NOTIFICACAO', 'PUBLICA', 'LABORATORIO', 'GESTAO', 'ATENCAO', 'BASICA', 'DOENCA', 'CRONICA', 'FAMILIA', 'ESTRATEGIA', 'COMUNITARIOS', 'LOGISTICA',  'IMUNOBIOLOGICOS', 'REGULACAO', 'ACOES', 'SERVICOS', 'SERVICO', 'HANSENIASE', 'MOVEL', 'URGENCIAS', 'DIAGNOSTICO', 'LABORATORIO', 'CLINICO', 'DISPENSACAO', 'ORTESES', 'PROTESES', 'REABILITACAO', 'PRATICAS', 'URGENCIA', 'EMERGENCIA', 'VIGILANCIA', 'BIOLOGICOS', 'FARMACIA', 'GRAFICOS', 'DINAMICOS', 'METODOS', 'PATOLOGICA', 'INTERMEDIARIOS', 'TORACICA', 'PRE-NATAL', 'IMUNIZACAO', 'CONSULTORIO', 'VIOLENCIA', 'SITUACAO', 'POPULACOES', 'INDIGENAS', 'ASSISTENCIA', 'COMISSOES', 'COMITES', 'SAUDE', 'BASICA'];

        return str_replace($arraySemAcento, $arrayComAcento, $frase);
    }

    private function salvarSelos($conMap, $idSpace, $idAgent)
    {

        $sql = "SELECT MAX(id)+1 FROM public.seal_relation";
        $maxSealRelation = $conMap->query($sql);
        $id = $maxSealRelation->fetchColumn();

        $id = !empty($id) ? $id : 1;

        $dataHora = date('Y-m-d H:i:s');
        $sqlInsertSeal = "INSERT INTO public.seal_relation 
                    (id, seal_id, object_id, create_timestamp, status, object_type, agent_id, validate_date, renovation_request) 
                    VALUES ({$id} ,'2', '" . $idSpace . "', '{$dataHora}' , '1' , 'MapasCulturais\Entities\Space' , {$idAgent},
                    '2029-12-08 00:00:00' , true)";
        $conMap->exec($sqlInsertSeal);
    }

    private function retornaIdTipoEstabelecimentoPorNome($conMap, $tipoNome)
    {
        $tipoNome = $this->adicionarAcentos($tipoNome);

        $sql = "SELECT id FROM public.term WHERE taxonomy='instituicao_tipos_unidades' AND term='{$tipoNome}'";
        $result = $conMap->query($sql);
        $id = $result->fetchColumn();
        return $id;
    }
}
