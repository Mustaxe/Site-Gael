<?php

namespace app\bootstrap;

use Slim\Slim;
use Slim\Middleware\SessionCookie;
use lib\Db\Db;
use lib\Db\MongoDb;
use lib\Db\CrudMongo;
use lib\Log\DbWriter;
use lib\Upload\Uploader;
use lib\Validacao\Validacao;
use lib\Authentication\Authentication;
use app\model\Log;
use app\model\Rota;
use app\model\Arquivo;
use app\model\Usuario;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Injeção de dependência dos serviços
 * que serão utilizados pela aplicação
 */
class Services
{
    /**
     * @var Slim
     */
    protected $app;

    /**
     * @var array Configurações das aplicação
     */
    protected $config;

    public function __construct(array $config, Slim $app)
    {
        $this->config = $config;
        $this->app    = $app;

        $this->configureContainer();
    }

    protected function configureContainer()
    {
        $this->config['baseUrl'] = null;

        $app    = $this->app;
        $config = $this->config;

        $this->app->container->singleton('logdb', function () use ($app) {
            return new DbWriter(array(), $app);
        });

        $this->app->container->singleton('db', function () use ($config, $app) {
            $db = ($config['database'] == 'mysql') ? (new Db($config, $app->logdb, $app)) : (new MongoDb($config, $app->logdb));

            return $db;
        });

        if(!empty($config['facebook']['appid'])){
            $this->app->container->singleton('facebook', function () use ($config, $app) {
                return new \Facebook(array('appId' => $config['facebook']['appid'], 'secret' => $config['facebook']['secret'], 'fileUpload' => $config['facebook']['file.upload']));;
            });
        }

        $this->app->container->singleton('auth', function () use ($app, $config) {
            $fb = null;
            if(!empty($config['facebook']['appid'])){
                $fb = $app->facebook; 
            }
            return Authentication::factory($app->configauth, $fb);
        });

        /* Eventos */

        $this->app->container->singleton('dispatcher', function () use ($app) {
            $dispatcher = new EventDispatcher();
            $dispatcher->addSubscriber($app->event_subscriber.salvar);

            return $dispatcher;
        });

        $this->app->container->singleton('event_subscriber.salvar', function () use ($app) {
            return new SalvarSubscriber($app);
        });

        $this->app->container->singleton('swiftmailer_transport', function () use ($app, $config) {
            $transport = \Swift_SmtpTransport::newInstance($config['mailer']['host'], $config['mailer']['port'], $config['mailer']['ssl'])
                             ->setUsername($config['mailer']['username'])
                             ->setPassword($config['mailer']['password']);
            return $transport;
        });

        $this->app->container->singleton('mailer', function () use ($app) {
            return new \Swift_Mailer($app->swiftmailer_transport);
        });

        $this->app->container->singleton('validacao', function () use ($app) {
            return new Validacao($app);
        });

        /* Objetos de acesso ao banco de dados */
        // todo: definir se já carregar todos aqui deixando no escopo da app ou no arquivo controller

        $this->app->container->singleton('logCrud', function () use ($app) {
            return new Log(array(), $app->db);
        });

        $this->app->container->singleton('usuarioCrud', function () use ($app) {
            return new Usuario(array(), $app->db);
        }); 

        $this->app->container->singleton('rotaCrud', function () use ($app) {
            return new Rota(array(), $app->db);
        });

        $this->app->container->singleton('arquivoCrud', function () use ($app) {
            return new Arquivo(array(), $app->db);
        });

        /* Serviços que dependem de classes do banco de dados */

        $this->app->container->singleton('uploader', function () use ($app, $config) {
            return new Uploader($app->arquivoCrud, $config, $app->logdb);
        });

        /* Funções gerais */

        $this->app->now = function () {
            return new \DateTime('now');
        };
    }
}
