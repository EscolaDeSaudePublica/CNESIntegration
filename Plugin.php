<?php

namespace CNESIntegration;

ini_set('MAX_EXECUTION_TIME', '-1');
ini_set('MAX_INPUT_TIME', '-1');
ini_set('DEFAULT_SOCKET_TIMEOUT', '-1');

ini_set('display_errors', true);
error_reporting(E_ALL);

use MapasCulturais\App;
use MapasCulturais\i;

class Plugin extends \MapasCulturais\Plugin 
{
    public function _init() 
    {
        
    }

    public function register()
    {
        $app = App::i();
        $app->registerController('cnes-integration', Controllers\CNESIntegration::class);

        $this->registerAgentMetadata('cns', [
            'label' => i::__('CNS'),
            'type' => 'string'
        ]);
    }
}