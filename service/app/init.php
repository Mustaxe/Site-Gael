<?php
setlocale(LC_ALL, "pt_BR.utf-8");
date_default_timezone_set("Brazil/East");
define('ROOT', dirname(__DIR__));

use app\bootstrap\SlimBootstrap;
use app\bootstrap\Services;
use Slim\Slim;

require_once(__DIR__.'/../vendor/autoload.php');

if (!defined('SLIM_ENVIRONMENT')) {
    $mode = getenv('SLIM_ENVIRONMENT') ? getenv('SLIM_ENVIRONMENT') : 'prod';
    define('SLIM_ENVIRONMENT', $mode);
}

$configFiles = sprintf(
    '%s/app/config/*{slim,%s}.php', 
    ROOT,
    SLIM_ENVIRONMENT
);

$configSlim = array();
foreach(glob($configFiles,GLOB_BRACE) as $cfg) {
    $var = require_once($cfg);
    $configSlim = array_merge($configSlim, $var);
}

$app = new Slim($configSlim['slim']);
$app->setName('Inkuba');

$services  = new Services($configSlim, $app);
$bootstrap = new SlimBootstrap($app, $services, $configSlim);
$app       = $bootstrap->bootstrap();

/**
 * Inclui os controllers
 */
foreach(glob(ROOT.'/app/controller/*.php') as $router) {
    include $router;
}

$app->get('/', function () use ($app) {
    echo 'inicio';
});

$app->get('/admin', function () use ($app) {
    $app->redirect($app->urlFor('home'));
});

$app->run();
