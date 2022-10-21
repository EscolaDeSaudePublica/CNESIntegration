<?php

namespace CNESIntegration\Controllers;

ini_set('display_errors', true);
ini_set('max_execution_time', -1);
error_reporting(E_ALL);

use CNESIntegration\Connection\Conn;
use CNESIntegration\Services\ProfissionalService;
use CNESIntegration\Services\EstabelecimentoService;
use MapasCulturais\App;
use MapasCulturais\Exceptions\PermissionDenied;

class CNESIntegration extends \MapasCulturais\Controller
{
    public function profissionais()
    {
        $app = App::i();

        if ($app->user->is('guest')) $app->auth->requireAuthentication();

        if ($app->user->email == 'desenvolvimento@esp.ce.gov.br'){
            $profissionalService = new ProfissionalService();
            $profissionalService->atualizaProfissionais();
        } else {
            throw new PermissionDenied($app->user, $this, '@control');
        }

    }

    public function GET_estabelecimentos()
    {
        $app = App::i();

        if ($app->user->is('guest')) $app->auth->requireAuthentication();

        if ($app->user->email == 'desenvolvimento@esp.ce.gov.br'){
            $spaceService = new EstabelecimentoService();
            $spaceService->atualizarSpaces();

            $this->profissionais();
        } else {
            throw new PermissionDenied($app->user, $this, '@control');
        }

    }
}
