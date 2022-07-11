<?php

namespace CNESIntegration;

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