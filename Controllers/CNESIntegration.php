<?php

namespace CNESIntegration\Controllers;

ini_set('display_errors', true);
error_reporting(E_ALL);

use CNESIntegration\Connection\Conn;
use CNESIntegration\Services\ProfissionalService;
use CNESIntegration\Services\SpaceService;

class CNESIntegration extends \MapasCulturais\Controller
{
    public function GET_profissionais()
    {
        //Conn::getConnection();

        // $profissionalService = new ProfissionalService();
        // $profissionalService->atualizaProfissionais();
    }

    public function GET_estabelecimentos()
    {
        $spaceService = new SpaceService();
        $spaceService->atualizarSpaces();
    }
}
