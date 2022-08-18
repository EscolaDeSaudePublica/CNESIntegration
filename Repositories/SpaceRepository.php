<?php

namespace CNESIntegration\Repositories;

use CNESIntegration\Connection\Conn;
use MapasCulturais\App;

class SpaceRepository
{

    public function getEstabelecimentosByCNES($cnes)
    {
        $connection = Conn::getConnection();
        $sql = "SELECT * FROM estabelecimentos WHERE co_cnes=?";

        $sth = $connection->prepare($sql );
        $sth->execute([$cnes]);
        $result = $sth->fetch(\PDO::FETCH_ASSOC);

        return $result;
    }

    public function getAllEstabelecimentos()
    {
        $connection = Conn::getConnection();
        $sql = "SELECT distinct competencia, co_cnes, no_razao_social, no_fantasia, no_logradouro, nu_endereco, no_complemento, no_bairro, co_cep, dt_atualizacao, 
        nu_latitude, nu_longitude, tp_unidade, description, atende_sus, co_municipio_gestor, municipio, nu_telefone 
        FROM estabelecimentos WHERE co_cnes='2497654'";

        $sth = $connection->prepare($sql);
        $sth->execute();
        $result = $sth->fetchAll(\PDO::FETCH_ASSOC);

        return $result;
    }

    public function getServicosPorEstabelecimento($cnes)
    {
        $connection = Conn::getConnection();
        $sql = "SELECT ds_servico_especializado FROM estabelecimentos WHERE co_cnes = :cnes";

        $sth = $connection->prepare($sql);
        $sth->execute([
            ":cnes" => $cnes
        ]);
        $result = $sth->fetchAll(\PDO::FETCH_ASSOC);

        return $result;
    }
}