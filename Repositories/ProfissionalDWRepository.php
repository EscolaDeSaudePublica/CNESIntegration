<?php

namespace CNESIntegration\Repositories;

use CNESIntegration\Connection\Conn;

class ProfissionalDWRepository
{
    private $connection;

    public function __construct()
    {
        $conn = new Conn();
        $this->connection = $conn->getInstance(Conn::DATABASE_DW);
    }

    public function getVinculos($filter)
    {
        $sql = "SELECT cns, cbo, descricao_cbo, cnes, nome, sexo, cnpj FROM cnesprofissionais WHERE cns=?";

        $sth = $this->connection->prepare($sql );
        $sth->execute([$filter]);
        return $sth->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getAllCnsDistinctProfissionais()
    {
        $sql = "SELECT DISTINCT cns, nome FROM cnesprofissionais";

        $sth = $this->connection->prepare($sql);
        $sth->execute();
        return $sth->fetchAll();
    }
}
