<?php

namespace CNESIntegration\Repositories\Mapa;

use CNESIntegration\Connection\Conn;
use MapasCulturais\App;

class SpaceRepository
{
    private $connection;

    public function __construct()
    {
        $conn = new Conn();
        $this->connection = $conn->getInstance(Conn::DATABASE_MAPA);
    }

    public function spaceMetaPorCnes($cnes)
    {
        $sth = $this->connection->prepare("SELECT object_id FROM space_meta WHERE value = '{$cnes}'");
        $sth->execute();
        return $sth->fetch(\PDO::FETCH_OBJ);
    }

    public function spacePorId($id)
    {
        $sth = $this->connection->prepare("SELECT id FROM space WHERE id = '{$id}'");
        $sth->execute();
        return $sth->fetch(\PDO::FETCH_OBJ);
    }

    public function salvarSelo($spaceId, $agentId)
    {
        $dataHora = date('Y-m-d H:i:s');

        $sealId = 1;

        $sqlInsertSeal = "INSERT INTO public.seal_relation 
                    (id, seal_id, object_id, create_timestamp, status, object_type, agent_id, validate_date, renovation_request) 
                    VALUES ((SELECT MAX(id)+1 FROM public.seal_relation) , {$sealId}, '" . $spaceId . "', '{$dataHora}' , '1' , 'MapasCulturais\Entities\Space' , {$agentId}, '2029-12-08 00:00:00' , true)";

        $this->connection->exec($sqlInsertSeal);
        $this->connection->exec("SELECT setval('seal_relation_id_seq', COALESCE((SELECT MAX(id)+1 FROM public.seal_relation), 1), false);");
    }

    public function salvarTermsSaude($spaceId)
    {
        $saudeid = 250;
        $sth = $this->connection->prepare("SELECT object_id FROM public.term_relation WHERE term_id = {$saudeid} AND object_type = 'MapasCulturais\Entities\Space' AND object_id = {$spaceId}");
        $sth->execute();
        $object_id = $sth->fetchColumn();

        if (!$object_id) {
            $sqlInsertMeta = "INSERT INTO public.term_relation (term_id, object_type, object_id, id) VALUES (
                                                                $saudeid, 
                                                                'MapasCulturais\Entities\Space', 
                                                                $spaceId,  
                                                                (SELECT MAX(id)+1 FROM public.term_relation)
                                                    )";
            $this->connection->exec($sqlInsertMeta);
            $this->connection->exec("SELECT setval('term_relation_id_seq', COALESCE((SELECT MAX(id)+1 FROM public.term_relation), 1), false);");
        }
    }

    public function retornaIdTipoEstabelecimentoPorNome($tipoNome)
    {
        $app = App::i();
        $conn = $app->em->getConnection();
        $tipoNome = $this->adicionarAcentos($tipoNome);

        $sql = "SELECT id FROM public.term WHERE taxonomy='instituicao_tipos_unidades' AND term='{$tipoNome}'";
        $result = $conn->query($sql);
        $id = $result->fetchColumn();

        if (!empty($id)) {
            return $id;
        }

        $sql = "SELECT id FROM public.term WHERE taxonomy='instituicao_tipos_unidades' AND term='ESTABELECIMENTO DE SAÚDE'";
        $result = $conn->query($sql);
        $id = $result->fetchColumn();

        return $id;
    }

    public function retornaStringTipoEstabelecimentoPorNome($tipoNome)
    {
        $app = App::i();
        $conn = $app->em->getConnection();
        $tipoNome = $this->adicionarAcentos($tipoNome);

        $sql = "SELECT term FROM public.term WHERE taxonomy='instituicao_tipos_unidades' AND term='{$tipoNome}'";
        $result = $conn->query($sql);
        $term = $result->fetchColumn();

        if (!empty($term)) {
            return $term;
        }

        return 'ESTABELECIMENTO DE SAÚDE';
    }

    public function adicionarAcentos($frase)
    {
        $arrayComAcento = ['ORGÃOS', 'CAPTAÇÃO', 'NOTIFICAÇÃO', 'PÚBLICA', 'LABORATÓRIO', 'GESTÃO', 'ATENÇÃO', 'BÁSICA', 'DOENÇA', 'CRÔNICA', 'FAMÍLIA',  'ESTRATÉGIA', 'COMUNITÁRIOS', 'LOGÍSTICA',  'IMUNOBIOLÓGICOS', 'REGULAÇÃO', 'AÇÕES', 'SERVIÇOS', 'SERVIÇO', 'HANSENÍASE', 'MÓVEL', 'URGÊNCIAS', 'DIAGNÓSTICO', 'LABORATÓRIO', 'CLÍNICO', 'DISPENSAÇÃO', 'ÓRTESES', 'PRÓTESES', 'REABILITAÇÃO', 'PRÁTICAS', 'URGÊNCIA', 'EMERGÊNCIA', 'VIGILÂNCIA', 'BIOLÓGICOS', 'FARMÁCIA', 'GRÁFICOS', 'DINÂMICOS', 'MÉTODOS', 'PATOLÓGICA', 'INTERMEDIÁRIOS', 'TORÁCICA', 'PRÉ-NATAL', 'IMUNIZAÇÃO', 'CONSULTÓRIO', 'VIOLÊNCIA', 'SITUAÇÃO', 'POPULAÇÕES', 'INDÍGENAS', 'ASSISTÊNCIA', 'COMISSÕES', 'COMITÊS', 'SAÚDE', 'BÁSICA', 'ÁREA', 'PRÉ-HOSPITALAR', 'NÍVEL'];

        $arraySemAcento = ['ORGAOS', 'CAPTACAO', 'NOTIFICACAO', 'PUBLICA', 'LABORATORIO', 'GESTAO', 'ATENCAO', 'BASICA', 'DOENCA', 'CRONICA', 'FAMILIA', 'ESTRATEGIA', 'COMUNITARIOS', 'LOGISTICA',  'IMUNOBIOLOGICOS', 'REGULACAO', 'ACOES', 'SERVICOS', 'SERVICO', 'HANSENIASE', 'MOVEL', 'URGENCIAS', 'DIAGNOSTICO', 'LABORATORIO', 'CLINICO', 'DISPENSACAO', 'ORTESES', 'PROTESES', 'REABILITACAO', 'PRATICAS', 'URGENCIA', 'EMERGENCIA', 'VIGILANCIA', 'BIOLOGICOS', 'FARMACIA', 'GRAFICOS', 'DINAMICOS', 'METODOS', 'PATOLOGICA', 'INTERMEDIARIOS', 'TORACICA', 'PRE-NATAL', 'IMUNIZACAO', 'CONSULTORIO', 'VIOLENCIA', 'SITUACAO', 'POPULACOES', 'INDIGENAS', 'ASSISTENCIA', 'COMISSOES', 'COMITES', 'SAUDE', 'BASICA', 'AREA', 'PRE-HOSPITALAR', 'NIVEL'];

        return str_replace($arraySemAcento, $arrayComAcento, $frase);
    }

}
