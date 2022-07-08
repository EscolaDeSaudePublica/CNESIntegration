<?php


namespace CNESIntegration\Controllers;

ini_set('display_errors', true);
error_reporting(E_ALL);

require_once PLUGINS_PATH . 'CNESIntegration/vendor/autoload.php';

use CNESIntegration\Services\ProfissionalService;

class CNESIntegration extends \MapasCulturais\Controller
{
    public function GET_profissionais()
    {
        $profissionalService = new ProfissionalService();
        $profissionalService->atualizaProfissionais();
    }
}