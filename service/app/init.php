<?php

setlocale(LC_ALL, "pt_BR.utf-8");
date_default_timezone_set("Brazil/East");
define('ROOT', dirname(__DIR__));

use app\bootstrap\SlimBootstrap;
use app\bootstrap\Services;
use Slim\Slim;
use app\model\Clientes;

require_once(__DIR__.'/../vendor/autoload.php');

if (!defined('SLIM_ENVIRONMENT')) {
    $mode = getenv('SLIM_ENVIRONMENT') ? getenv('SLIM_ENVIRONMENT') : 'prod';
    define('SLIM_ENVIRONMENT', $mode);
}



/**
*
* Identifica se é ambiente Admin ou Cliente
*
*/
$arrUri = explode("/", $_SERVER['REQUEST_URI']);
if(array_search('admin', $arrUri) !== FALSE)
{    
    $isCliente = false;
}
else
{ 
    $isCliente = true;
}


/**
*
* Seleciona o arquivo de configuração correto para cada ambiente
*
*/
if($isCliente)
{
    $configFiles = sprintf('%s/app/config/*{cliente,%s}.php', ROOT, SLIM_ENVIRONMENT);
}
else
{  
    $configFiles = sprintf('%s/app/config/*{slim,%s}.php', ROOT, SLIM_ENVIRONMENT);
} 


/**
*
* Inclui arquivos de configurações
*
*/
$configSlim = array();
foreach(glob($configFiles, GLOB_BRACE) as $cfg) {
    $var = require_once($cfg);
    $configSlim = array_merge($configSlim, $var);
}


/**
*
* Identifica a URI para incluir as configs referente ao ambiente
*
* Main aplication
* 
*/
if($isCliente)
{
    $app = new Slim($configSlim['cliente']);
}
else
{
    $app = new Slim($configSlim['slim']);
} 

$app->setName('Inkuba');


$services  = new Services($configSlim, $app);
$bootstrap = new SlimBootstrap($app, $services, $configSlim);
$app       = $bootstrap->bootstrap();


/**
 * Inclui os controllers
 */
foreach(glob(ROOT . '/app/controller/*.php') as $router)
{
    include $router;
}


/**
*
* Rotas
*
*/
$app->get('/', function () use ($app) {
    echo 'inicio';
});

$app->get('/admin', function () use ($app) {
    $app->redirect($app->urlFor('home'));
});


/**
*
* Default cliente
*
*/
$app->get('/cliente', function () use ($app) {

    /**
    *
    * Se o usuario estiver logado redirecionamos ele para as pastas
    * - Se não estiver logado redirecionamos para 404
    *
    *
    *
    */

    /**
    *
    * IMPORTANT: Verifica se o usuario já está logado
    *
    */
    if(isset($_SESSION['X_CLIENTE_SESSION_KEY']) && ($_SESSION['X_CLIENTE_SESSION_KEY'] == md5($_SERVER["REMOTE_ADDR"])))
    {
        $app->redirect($app->urlFor('cliente_pastas'));
        return;
    }
    

    $app->response->headers->set('Content-Type', 'text/html;charset=utf-8');
    $app->render('cliente/erros/404.html.twig');
    exit;
});


/**
*
* Default cliente com slug
*
*/
$app->get('/cliente/:slug', function ($slug) use ($app) {

    /**
    *
    * IMPORTANT: Verifica se o cliente existe no Banco de Dados
    * - Se o slug do cliente não for encontrado, então redirecionamos ele para 404
    *
    * - Se o usuario estiver logado então redirecionamos ele para as pastas
    * - Se não estiver logado e existir o slug, então verificamos.
    *
    */

    /**
    *
    * IMPORTANT: Verifica se o usuario já está logado
    *
    */
    if(isset($_SESSION['X_CLIENTE_SESSION_KEY']) && ($_SESSION['X_CLIENTE_SESSION_KEY'] == md5($_SERVER["REMOTE_ADDR"])))
    {
        $app->redirect($app->urlFor('cliente_pastas'));
        return;
    }


    /**
    *
    * IMPORTANT: Verifica se o cliente existe no Banco de Dados
    *
    * IMPORTANT: Tomar cuidado com SQLInjection aqui.
    *
    */    
    if(!empty($slug))
    {
        $clientes = new Clientes(array(), $app->db);


        $_slug = trim($slug);
        $_slug = strtolower($_slug);

        $q = "SELECT * FROM tbl_clientes WHERE url = '" . $_slug . "'";

        $resCliente = $clientes->Query($q);
        if($resCliente->cod == 200) 
        {
            /**
            *
            * Existe!
            * 
            * - Criamos a variável de referencia ao slug            
            *
            * IMPORTANT: Variável de referencia ao slug para ser verificada na tela de login, é importante apagar a variavel após seu uso.
            *
            */

            $cliente = $resCliente->res[0];

            $_SESSION['X_SLUG'] = $cliente['url'];
            $_SESSION['X_EMPRESA'] = $cliente['empresa'];

            $app->redirect($app->urlFor('cliente_login'));
        }        
    }

    $app->response->headers->set('Content-Type', 'text/html;charset=utf-8');
    $app->render('cliente/erros/404.html.twig');
    exit;
});

$app->run();


