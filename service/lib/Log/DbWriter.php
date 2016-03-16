<?php
/**
 * Gravar Log no Banco de Dados
 *
 * Use para gravar mensagens de de exceções
 * no banco de dados
 *
 */

namespace lib\Log;

use lib\Db\Db;
use app\model\Log;
use app\model\Usuario;

class DbWriter
{
    /**
     * @var Db
     */
    protected $db;

    /**
     * @var array
     */
    protected $settings;

    /**
     * Construtor
     *
     * @param   array $settings
     * @param   Slim    $app
     *
     * @return  void
     */
    public function __construct($settings = array(), $app)
    {
        $this->settings = $settings;
        $this->app      = $app;
    }

    /**
     * Grava log no banco de dados
     *
     * @param   $user
     * @param   string   $msg
     *
     * @return  void
     */
    public function write($user, $msg, $config)
    {
       $db  = new Db($config, null, null);
       $log = new Log(array(), $db);

       $log->user_id = null;
       $log->msg     = $msg;
       $log->data    = date('Y-m-d H:i:s');
       
       if ( is_object($user) && !($user instanceof Usuario)) {
           $log->user_id = $user->id;
       }

       if ($user instanceof Usuario) {
           $log->user_id = $user->getId();
       }

       $res = $log->Create();
    }
}
