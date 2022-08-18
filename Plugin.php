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

        $this->registerMetadataEstabelecimentos();
    }

    private function registerMetadataEstabelecimentos()
    {
        $this->registerSpaceMetadata('En_CEP', [
            'label' => i::__('CEP'),
            'type' => 'string',
        ]);
        $this->registerSpaceMetadata('En_Nome_Logradouro', [
            'label' => i::__('Logradouro'),
            'type' => 'string',
        ]);
        $this->registerSpaceMetadata('En_Num', [
            'label' => i::__('Número'),
            'type' => 'string',
        ]);
        $this->registerSpaceMetadata('En_Bairro', [
            'label' => i::__('Bairro'),
            'type' => 'string',
        ]);
        $this->registerSpaceMetadata('En_Estado', [
            'label' => i::__('Estado'),
            'type' => 'string',
        ]);
        $this->registerSpaceMetadata('instituicao_cnes', [
            'label' => i::__('CNES'),
            'type' => 'string',
        ]);
        $this->registerSpaceMetadata('instituicao_cnes_data_atualizacao', [
            'label' => i::__('Data de atualização'),
            'type' => 'string',
        ]);
        $this->registerSpaceMetadata('instituicao_cnes_competencia', [
            'label' => i::__('Competência'),
            'type' => 'string',
        ]);
        $this->registerSpaceMetadata('telefonePublico', [
            'label' => i::__('Telefone Público'),
            'type' => 'string',
        ]);
        $this->registerSpaceMetadata('instituicao_pertence_sus', [
            'label' => i::__('Pertence ao SUS?'),
            'type' => 'string',
        ]);
    }
}