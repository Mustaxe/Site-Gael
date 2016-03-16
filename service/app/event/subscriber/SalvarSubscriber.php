<?php

namespace app\event\subscriber;

use Slim\Slim;
use app\event\SaveEvent;
use lib\Log\DbWriter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SalvarSubscriber implements EventSubscriberInterface
{
    /**
     * @var DbWriter
     */
    protected $logger;

    protected $usuario;

    /**
     * Construtor
     *
     * @param Slim $app
     */
    public function __construct(Slim $app)
    {
        $this->logger  = $app->logdb;
        $this->usuario = isset($app->usuarioLogado) ? $app->usuarioLogado : null;
    }

    public static function getSubscribedEvents()
    {
        return array(
            'on.save'   => array('onSave', 0),
            'on.delete' => array('onDelete', 0),
        );
    }

    public function onSave(SalvarEvent $event)
    {
        $this->logger->write($this->usuario, $event->getSql);
    }

    public function onDelete(SalvarEvent $event)
    {
        // todo
    }
}
