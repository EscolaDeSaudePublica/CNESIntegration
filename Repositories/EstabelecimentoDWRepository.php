<?php

namespace CNESIntegration\Repositories;

use CNESIntegration\Connection\Conn;

use MapasCulturais\App;

class EstabelecimentoDWRepository
{

    private $connection;

    public function __construct()
    {
        $conn = new Conn();
        $this->connection = $conn->getInstance(Conn::DATABASE_DW);
    }

    public function getEstabelecimentosByCNES($cnes)
    {
        $sql = "SELECT * FROM estabelecimentos WHERE co_cnes=?";

        $sth = $this->connection->prepare($sql);
        $sth->execute([$cnes]);
        return $sth->fetch(\PDO::FETCH_ASSOC);
    }

    public function getAllEstabelecimentos()
    {
        $sql = "SELECT distinct competencia, co_cnes, no_razao_social, no_fantasia, no_logradouro, nu_endereco, no_complemento, no_bairro, co_cep, dt_atualizacao, 
        nu_latitude, nu_longitude, tp_unidade, description, atende_sus, co_municipio_gestor, municipio, nu_telefone 
        FROM estabelecimentos LIMIT 50";

        $sth = $this->connection->prepare($sql);
        $sth->execute();
        return $sth->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getServicosPorEstabelecimento($cnes)
    {
        $sql = "SELECT ds_servico_especializado FROM estabelecimentos WHERE co_cnes = :cnes";

        $sth = $this->connection->prepare($sql);
        $sth->execute([
            ":cnes" => $cnes
        ]);
        return $sth->fetchAll(\PDO::FETCH_ASSOC);
    }
}