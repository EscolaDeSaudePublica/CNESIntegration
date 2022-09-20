<?php

namespace CNESIntegration\Controllers;

ini_set('display_errors', true);
ini_set('max_execution_time', -1);
error_reporting(E_ALL);

use CNESIntegration\Connection\Conn;
use CNESIntegration\Services\ProfissionalService;
use CNESIntegration\Services\SpaceService;
use MapasCulturais\App;
use MapasCulturais\Exceptions\PermissionDenied;

class CNESIntegration extends \MapasCulturais\Controller
{
    public function GET_profissionais()
    {
        Conn::getConnection();

        $profissionalService = new ProfissionalService();
        $profissionalService->atualizaProfissionais();
    }

    public function GET_estabelecimentos()
    {     
        $app = App::i();

        if ($app->user->is('guest')) $app->auth->requireAuthentication();

        if ($app->user->email == 'desenvolvimento@esp.ce.gov.br'){
            $spaceService = new SpaceService();
            $spaceService->atualizarSpaces();
        } else {
            throw new PermissionDenied($app->user, $this, '@control');
        }
        
    }
}
