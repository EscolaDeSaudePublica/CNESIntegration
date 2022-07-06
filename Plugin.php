<?php

namespace CNESIntegration;

use MapasCulturais\App;

class Plugin extends \MapasCulturais\Plugin 
{
    public function _init() 
    {
        
    }

    public function register()
    {
        $app = App::i();
        $app->registerController('cnes-integration', Controllers\CNESIntegration::class);
    }
}