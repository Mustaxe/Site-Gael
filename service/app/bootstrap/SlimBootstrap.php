<?php

namespace app\bootstrap;

use app\bootstrap\Services;
use Slim\Log;
use Slim\Slim;
use lib\Middleware\StrongAuth;
use lib\Middleware\HtmlResponse;
use lib\Middleware\CsrfGuard;
use \PDO;

class SlimBootstrap
{
    /**
     * @var Slim
     */
    protected $app;

    /**
     * @var Services
     */
    protected $container;

    /**
     * @var array
     */
    protected $config;

    /**
     * Construtor
     *
     * @param Slim     $app        Instância da aplicação
     * @param Services $container  Container com os serviços
     * @param array    $config     Configuração da aplicação
     */
    public function __construct(Slim $app, Services $container, array $config)
    {
        $this->app       = $app;
        $this->container = $container;
        $this->config    = $config;
    }

    public function bootstrap()
    {
        $app       = $this->app;
        $container = $this->container;
        $config    = $this->config;

		
        /**
        *
        * Dados disponíveis globalmente para a visão somente para interface administrativa e tipo de autenticação sessão
        *        
        */
        if (strpos($app->request()->getResourceUri(), '/admin') !== false && $app->config('auth.type') == 'sessao')
        {
            $this->configureView($app, $container);
        }
        else if (strpos($app->request()->getResourceUri(), '/cliente') !== false) // != de FALSE = VERDADEIRO
        {            
            $this->configureView($app, $container);
        }

        $this->addDefaultHeaders($app);
        $this->addConfigAuthentication($app, $config);
        $this->configureErrors($app);
        $this->addMiddleware($app, $container, $config);

        return $app;
    }

    public function configureView(Slim $app, Services $container)
    {
        /*
         * Dados disponíveis globalmente para a visão
         */
        $menuTopo      = array();
        $resourceUri   = $app->request()->getResourceUri(); // $_SERVER['REQUEST_URI'];
        $rootUri       = $app->request()->getRootUri();
        $assetUri      = $app->request()->getUrl() . $rootUri;

        /**
        *
        * Define a baseUri, se é de cliente ou admin
        *
        */
        $arrUri = explode("/", $_SERVER['REQUEST_URI']);
        if(array_search('admin', $arrUri) !== FALSE)
        {
            $baseUri = $app->request()->getUrl() . $rootUri . '/admin'; 
        }
        else
        {
            $baseUri = $app->request()->getUrl() . $rootUri . '/cliente'; 
        }
               
        
        $usuarioLogado = $app->usuarioLogado;
        $usuario       = isset($usuarioLogado) ? ($usuarioLogado) : (null);
        $adminMenu     = $app->config('admin.menu');

        if (isset($adminMenu)) {
            foreach ($adminMenu as $menu) {
                // usa a última parte da URL como nome da página (usada para adicionar link ativo)
                $menuUrl = array_reverse(explode('/', $menu['url']));
                $menu['page'] = $menuUrl[0];
                $menuTopo[] = $menu;
            }
        }

        $app->view()->appendData(
            array( 'app'          => $app,
                   'rootUri'      => $rootUri,
                   'assetUri'     => $assetUri,
                   'resourceUri'  => $resourceUri,
                   'baseUri'      => $baseUri,
                   'usuarioAtual' => $usuario,
                   'adminMenu'    => $menuTopo,
        ));
    }

    /**
    *
    * Sobrescreve a baseUri disponicel na View
    *
    */
    public function setBaseUri($value) {
        $app->view()->appendData(array( 'baseUri' => $value));
    }

    public function configureErrors(Slim $app)
    {
        /**
         * Substitui a mensagem 404
         */
        $app->notFound(
            function () use ($app) {
                if (strpos($app->request()->getResourceUri(), '/admin') !== false && $app->config('auth.type') == 'sessao') {
                    $app->render('admin/erros/404.html.twig');

                    return;
                }

                echo json_encode(array('cod'=>404, 'res'=>'O recurso não foi encontrado'));
            }
        );

        $config = $this->config;

        /**
         * Tratamento das exceções e adiciona templates default, por exemplo, para um erro específico
         * e grava no banco de dados a exceção ocorrida
         */
        $app->error(
            function(\Exception $e) use ($app, $config) {
                $usuarioLogado = $app->usuarioLogado;
                $usuario       = isset($usuarioLogado) ? ($usuarioLogado) : (null);
                $msg           = 'Erro: '.$e->getMessage().' - Arquivo: '.$e->getFile().' - Linha: '.$e->getLine();
                $app->logdb->write($usuario, $msg, $config);

                if ($e instanceof \lib\Exception\HttpForbiddenException) {
                    if (strpos($app->request()->getResourceUri(), '/admin') !== false && $app->config('auth.type') == 'sessao') {
                        $app->render('admin/erros/403.html.twig');

                        return;
                    }

                    $app->response->setStatus(401);
                    echo json_encode(array('cod'=>401, 'res'=> $e->getMessage()));

                    return;
                }

                if ($e instanceof \PDOException) {
                    echo json_encode(array('cod'=>500, 'res'=> 'Ocorreu um erro no banco de dados. Verificar o log para detalhes.'));

                    return;
                } 

                if (strpos($app->request()->getResourceUri(), '/admin') !== false && $app->config('auth.type') == 'sessao') {
                    $app->render('admin/erros/500.html.twig');

                    return;
                }
                echo json_encode(array('cod'=>500, 'res'=> $e->getMessage()));
            }
        );
    }

    public function addDefaultHeaders(Slim $app)
    {
        $app->response->headers->set('Content-Type', 'application/json;charset=utf-8');
        $app->response->headers->set('Access-Control-Allow-Origin', '*');
    }

    public function addConfigAuthentication(Slim $app, array $config)
    {
        $configauth = array(
            'provider'        => $app->config('provider'),
            'db'              => $app->db,
            'auth.type'       => $app->config('auth.type'),
            'chave.expira'    => $app->config('chave.expira'),
            'login.url'       => $app->config('login.url'),
            'security.urls'   => $app->config('secured.urls'),
            'fb.scope'        => $config['facebook']['scope'],
            'fb.redirect.uri' => $config['facebook']['redirect.uri'],
        );

        $app->configauth = function () use ($configauth) {
            return $configauth;
        };
    }

    /**
     * Middlewares são aplicados globalmente e executados na ordem reversa da chamada.
     * São layers que executam uma lógica e podem parar o processamento ou chamar a próxima layer
     */
    public function addMiddleware(Slim $app, Services $container, array $config)
    {
        $app->add(new HtmlResponse());
        $app->add(new StrongAuth($app->configauth));
    }
}
