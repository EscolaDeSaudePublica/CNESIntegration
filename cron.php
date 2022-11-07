<?php

namespace CNESIntegration;

require '/var/www/html/protected/application/bootstrap.php';

use MapasCulturais\App;

$app = App::i();

$app->createAndSendMailMessage([
    'from' => $app->config['mailer.from'],
    'to' => 'victor.magalhaesp@gmail.com',
    'subject' => 'MIGRAÇÃO CNES - INICIANDO',
    'body' => 'MIGRAÇÃO CNES - INICIANDO'
]);

$plugins = $app->getPlugins();
$cnesIntegration = $plugins['CNESIntegration'];
$cnesIntegration->runMigrationCNES();

$app->createAndSendMailMessage([
    'from' => $app->config['mailer.from'],
    'to' => 'victor.magalhaesp@gmail.com',
    'subject' => 'MIGRAÇÃO CNES - FINALIZADO',
    'body' => 'MIGRAÇÃO CNES - FINALIZADO'
]);
