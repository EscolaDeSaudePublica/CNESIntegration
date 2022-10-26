<?php

namespace CNESIntegration\Services;

use CNESIntegration\Repositories\EstabelecimentoDWRepository;
use CNESIntegration\Repositories\Mapa\SpaceRepository;
use MapasCulturais\App;
use MapasCulturais\Types\GeoPoint;

class EstabelecimentoService
{

    public function atualizarSpaces()
    {
        $app = App::i();

        $start = microtime(true);

        $userAdmin = $app->repo('User')->findOneBy(['email' => 'desenvolvimento@esp.ce.gov.br']);
        $userCnes = $app->repo('User')->findOneBy(['email' => 'cnes@esp.ce.gov.br']);

        $app->user = $userAdmin;
        $app->auth->authenticatedUser = $userAdmin;

        $app->em->getConnection()->getConfiguration()->setSQLLogger(null);

        $estabelecimentoDWRepository = new EstabelecimentoDWRepository();
        // retorna uma lista com todos os cnes da base do CNES
        $estabelecimentos = $estabelecimentoDWRepository->getAllEstabelecimentos();

        $spacesNewsSeals = [];

        $cont = 1;
        foreach ($estabelecimentos as $estabelecimento) {
            // retorna todos os dados da view estabelecimentos de um determinado cnes
            $spaceMeta = $app->repo('SpaceMeta')->findOneBy(['value' => $estabelecimento['co_cnes']]);

            if ($estabelecimento["nu_longitude"] == null || $estabelecimento["nu_longitude"] == 'nan') {
                $geo = new GeoPoint(0.0, 0.0);
            } else {
                $geo = new GeoPoint(str_replace(",",".",$estabelecimento["nu_longitude"]), str_replace(",",".",$estabelecimento["nu_latitude"]));
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

            $servicosEstabelecimento = $estabelecimentoDWRepository->getServicosPorEstabelecimento($cnes);

            $servicosArray = [];
            foreach ($servicosEstabelecimento as $serv) {
                if (!empty($serv['ds_servico_especializado']) && $serv['ds_servico_especializado'] != 'nan') {
                    $servicosArray[] = $serv['ds_servico_especializado'];
                }
            }

            $tipoUnidadeComAcento = $this->adicionarAcentos($tipoUnidade);
            $term = $app->repo('Term')->findOneBy(['term' => $tipoUnidadeComAcento]);
            if (empty($term)) {
                $term = new \MapasCulturais\Entities\Term;
                $term->taxonomy = 'instituicao_tipos_unidades';
                $term->term = $tipoUnidadeComAcento;
                $term->save(true);
            }

            if ($spaceMeta) {
                $space = $spaceMeta->owner;
            } else {
                $space = new \MapasCulturais\Entities\Space;
            }
            
            
            $space->setLocation($geo);
            $space->name = $nomeFantasia;
            $space->shortDescription = 'CNES: ' . $cnes;
            $space->longDescription = $razaoSocial;
            $space->createTimestamp = $dateTime;
            $space->status = 1;
            $space->ownerId = $userCnes->id;
            $space->is_verified = false;
            $space->public = false;
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

            if (!empty($telefone) || $telefone != 'nan') {
                $space->setMetadata('telefonePublico', $telefone);
            }

            if (!empty($percenteAoSus) && $percenteAoSus != 'nan') {
                $space->setMetadata('instituicao_pertence_sus', $percenteAoSus);
            }

            if (is_array($servicosArray)) {
                $space->setMetadata('instituicao_servicos', implode(', ', $servicosArray));
            }

            $space->save(true);

            if (!$spaceMeta) {
                $spacesNewsSeals[] = $space->id;
            }

            $app->em->clear();
            $msg = "Salva/atualiza espaço com o CNES {$cnes} | Espaço: {$space->id}";
            $app->log->debug($msg);
            $this->logMsg( $msg );

            $cont++;

            $time_elapsed_secs = microtime(true) - $start;

            $app->log->debug("------------------------------" . $time_elapsed_secs . "------------------------------------");
            $app->log->debug("Linha: " . $cont);
            $this->logMsg("------------------------------" . $time_elapsed_secs . "------------------------------------");
            $this->logMsg("Linha: " . $cont);
        }

        $app->em->clear();

        $spaceRepository = new SpaceRepository();
        foreach ($spacesNewsSeals as $spaceId) {
            $spaceRepository->salvarSelo($spaceId, $userCnes->profile->id);
            $app->log->debug("Aplicando SELO para: " . $spaceId);
            $this->logMsg("Aplicando SELO para: " . $spaceId);
        }

        $msg = "¨¨\_(* _ *)_/¨¨ -  Processo de atualização dos espaços finalizado !  -  ¨¨\_(* _ *)_/¨¨";
        $this->logMsg( $msg );
        $app->log->debug($msg);
        $app->log->debug(print_r($spacesNewsSeals));
    }

    private function adicionarAcentos($frase)
    {
        $arrayComAcento = ['ORGÃOS', 'CAPTAÇÃO', 'NOTIFICAÇÃO', 'PÚBLICA', 'LABORATÓRIO', 'GESTÃO', 'ATENÇÃO', 'BÁSICA', 'DOENÇA', 'CRÔNICA', 'FAMÍLIA',  'ESTRATÉGIA', 'COMUNITÁRIOS', 'LOGÍSTICA',  'IMUNOBIOLÓGICOS', 'REGULAÇÃO', 'AÇÕES', 'SERVIÇOS', 'SERVIÇO', 'HANSENÍASE', 'MÓVEL', 'URGÊNCIAS', 'DIAGNÓSTICO', 'LABORATÓRIO', 'CLÍNICO', 'DISPENSAÇÃO', 'ÓRTESES', 'PRÓTESES', 'REABILITAÇÃO', 'PRÁTICAS', 'URGÊNCIA', 'EMERGÊNCIA', 'VIGILÂNCIA', 'BIOLÓGICOS', 'FARMÁCIA', 'GRÁFICOS', 'DINÂMICOS', 'MÉTODOS', 'PATOLÓGICA', 'INTERMEDIÁRIOS', 'TORÁCICA', 'PRÉ-NATAL', 'IMUNIZAÇÃO', 'CONSULTÓRIO', 'VIOLÊNCIA', 'SITUAÇÃO', 'POPULAÇÕES', 'INDÍGENAS', 'ASSISTÊNCIA', 'COMISSÕES', 'COMITÊS', 'SAÚDE', 'BÁSICA', 'ÁREA', 'PRÉ-HOSPITALAR', 'NÍVEL'];

        $arraySemAcento = ['ORGAOS', 'CAPTACAO', 'NOTIFICACAO', 'PUBLICA', 'LABORATORIO', 'GESTAO', 'ATENCAO', 'BASICA', 'DOENCA', 'CRONICA', 'FAMILIA', 'ESTRATEGIA', 'COMUNITARIOS', 'LOGISTICA',  'IMUNOBIOLOGICOS', 'REGULACAO', 'ACOES', 'SERVICOS', 'SERVICO', 'HANSENIASE', 'MOVEL', 'URGENCIAS', 'DIAGNOSTICO', 'LABORATORIO', 'CLINICO', 'DISPENSACAO', 'ORTESES', 'PROTESES', 'REABILITACAO', 'PRATICAS', 'URGENCIA', 'EMERGENCIA', 'VIGILANCIA', 'BIOLOGICOS', 'FARMACIA', 'GRAFICOS', 'DINAMICOS', 'METODOS', 'PATOLOGICA', 'INTERMEDIARIOS', 'TORACICA', 'PRE-NATAL', 'IMUNIZACAO', 'CONSULTORIO', 'VIOLENCIA', 'SITUACAO', 'POPULACOES', 'INDIGENAS', 'ASSISTENCIA', 'COMISSOES', 'COMITES', 'SAUDE', 'BASICA', 'AREA', 'PRE-HOSPITALAR', 'NIVEL'];

        return str_replace($arraySemAcento, $arrayComAcento, $frase);
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

    function logMsg( $msg ) {

        $file = '/var/www/html/protected/application/plugins/CNESIntegration/Logs/logs.txt';
        $date = date( 'Y-m-d H:i:s' );
        $current = file_get_contents($file);
        $current .= "{$date}: {$msg}\n";
        file_put_contents( $file, $current );
    }
}