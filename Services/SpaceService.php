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
        $estabelecimentos = $spaceRepository->getAllEstabelecimentos();

        foreach ($estabelecimentos as $estabelecimento) {
            // retorna todos os dados da view estabelecimentos de um determinado cnes
            $spaceMeta = $app->repo('SpaceMeta')->findOneBy(['value' => $estabelecimento['co_cnes']]);

            if ($estabelecimento["nu_longitude"] == null || $estabelecimento["nu_longitude"] == 'nan') {
                $geo = new GeoPoint(0, 0);
            } else {
                $geo = new GeoPoint($estabelecimento["nu_longitude"], $estabelecimento["nu_latitude"]);
            }

            $nomeFantasia = $estabelecimento["no_fantasia"];
            $razaoSocial =  $estabelecimento["no_razao_social"];
            $tipoUnidade = $estabelecimento['description'];
            $telefone = $estabelecimento["nu_telefone"];
            $percenteAoSus = $estabelecimento['atende_sus'];


            $cep = $estabelecimento['co_cep'];
            $logradouro = $estabelecimento['no_logradouro'];
            $numero = $estabelecimento['nu_endereco'];
            $bairro = $estabelecimento['no_bairro'];
            $municipio = $estabelecimento['municipio'];
            $cnes = $estabelecimento['co_cnes'];
            $now = date('Y-m-d H:i:s');
            $dateTime = new \DateTime($now); 
        

            $competencia = substr_replace($estabelecimento['competencia'], '-', -2, -2);
            $competenciaArray = explode('-', $competencia);
            $competenciaData = $competenciaArray[1] . '/' . $competenciaArray[0];

            $servicosEstabelecimento = $spaceRepository->getServicosPorEstabelecimento($cnes);

            $servicosArray = [];
            foreach ($servicosEstabelecimento as $serv) {
                if (!empty($serv['ds_servico_especializado']) && $serv['ds_servico_especializado'] != 'nan') {
                    $servicosArray[] = $serv['ds_servico_especializado'];
                }
            }

            //$servicos = $cnes_["ds_servico_especializado"];

            if ($spaceMeta) {
                $space = $spaceMeta->owner;
            } else {
                $space = new \MapasCulturais\Entities\Space;
            }

            $idAgenteResponsavel = 8;
            $space->setLocation($geo);
            $space->name = $nomeFantasia;
            $space->shortDescription = 'CNES: ' . $cnes;
            $space->longDescription = $razaoSocial;
            $space->createTimestamp = $dateTime;
            $space->status = 1;
            // $space->is_verified = false;
            // $space->public = false;
            $space->agent_id = $idAgenteResponsavel;
            $space->type = $this->retornaIdTipoEstabelecimentoPorNome($tipoUnidade);

            if (!empty($cep)) {
                $space->setMetadata('En_CEP', $cep);
            }

            if (!empty($logradouro)) {
                $space->setMetadata('En_Nome_Logradouro', $logradouro);
            }

            if (!empty($numero)) {
                $space->setMetadata('En_Num', $numero);
            }

            if (!empty($bairro)) {
                $space->setMetadata('En_Bairro', $bairro);
            }

            if (!empty($municipio)) {
                $space->setMetadata('En_Municipio', $municipio);
            }
            $space->setMetadata('En_Estado', 'CE');

            if (!empty($cnes)) {
                $space->setMetadata('instituicao_cnes', $cnes);
            }

            $space->setMetadata('instituicao_cnes_data_atualizacao', $now);

            if (!empty($competenciaData)) {
                $space->setMetadata('instituicao_cnes_competencia', $competenciaData);
            }

            if (!empty($tipoUnidade)) {
                $space->setMetadata('instituicao_tipos_unidades', $tipoUnidade);
            }

            if (!empty($telefone)) {
                $space->setMetadata('telefonePublico', $telefone);
            }

            if (!empty($percenteAoSus) && $percenteAoSus != 'nan') {
                $space->setMetadata('instituicao_pertence_sus', $percenteAoSus);
            }

            if (is_array($servicosArray)) {
                $space->setMetadata('instituicao_servicos', implode(', ', $servicosArray));
            }

            $space->save(true);
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

    private function retornaIdTipoEstabelecimentoPorNome($tipoNome)
    {
        $app = App::i();
        $conn = $app->em->getConnection();
        $tipoNome = $this->adicionarAcentos($tipoNome);

        $sql = "SELECT id FROM public.term WHERE taxonomy='instituicao_tipos_unidades' AND term='{$tipoNome}'";
        $result = $conn->query($sql);
        $id = $result->fetchColumn();
        return $id;
    }
}
