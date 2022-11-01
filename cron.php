<?php

namespace CNESIntegration;

require '/var/www/html/protected/application/bootstrap.php';

use MapasCulturais\App;

$app = App::i();

$plugins = $app->getPlugins();
$cnesIntegration = $plugins['CNESIntegration'];
$cnesIntegration->runMigrationCNES();
