<?php
/**
 * Strong Authentication Library
 *
 * User authentication and authorization library
 *
 * @license     MIT Licence
 * @category    Libraries
 * @author      Andrew Smith
 * @link        http://www.silentworks.co.uk
 * @copyright   Copyright (c) 2012, Andrew Smith.
 * @version     1.0.0
 */

namespace lib\Authentication\Provider;

use \lib\exception\GeracaoChaveException;

class MongoDbChave extends ProviderChave
{
    /**
     * @var array
     */
    protected $settings = array(
        'dsn'    => '',
        'dbuser' => null,
        'dbpass' => null,
    );

    protected $fb = null;

    /**
     * @var $usuarios Coleção usuários
     */
    private $usuarios;

    /**
     * Inicializa a conexão com o MongoDb
     * e adiciona configurações default
     *
     * @param array $config 
     */
    public function __construct($config, $fb)
    {
        parent::__construct($config);
        $this->config = array_merge($this->settings, $this->config);

        if (!isset($this->config['db']) || !($this->config['db'] instanceof \lib\Db\MongoDb)) {
            throw new \InvalidArgumentException('Deve ser informado um objeto MongoDb válido');
        }

        $this->db          = $this->config['db']->database();
        $this->usuarios    = $this->db->tbl_usuario;
        $this->rotas       = $this->db->tbl_rota;
        $this->fb          = $fb;
    }

    /**
     * Verificação de login do usuário
     *
     * @param string $chave
     *
     * @return boolean
     */
    public function loggedIn($chave)
    {
        return $this->validaChave($chave);
    }

    /**
     * Verifica se chave está associada ao usuário
     *
     * @param string $chave
     *
     * @return boolean
     */
    public function validaChave($chave)
    {
        $user     = $this->usuarios->findOne(array('chave' => $chave, 'status' => 1));

        if ($user) {
            return !(parent::chaveExpirou($user['chaveTempo']));
        }

        return false;
    } 

    /**
     * Busca informações do usuário pela chave
     *
     * @return array|null
     */
    public function getUser($chave)
    {
        $res  = $this->usuarios->findOne(array('chave' => $chave, 'status' => 1));
        $user = (object) $res;

        return $user;
    }

    /**
     * Verifica se usuário tem acesso a determinada rota
     *
     * @param string $rota
     * @param string $chave
     *
     * @return boolean
     */
    public function usuarioPertenceGrupo($rota, $chave)
    {
        // localizar id da rota
        $objRota = $this->rotas->findOne(array("nome" => $rota, 'status' => 1));

        $cursor  = $this->usuarios->find(array("grupos" => $objRota['_id'], "chave" => $chave, 'status' => 1));

        $usuarios = array();
        foreach ($cursor as $usuario) {
            $usuarios[] = $usuario;
        }

        $res  = (count($usuarios) > 0) ? true : false;

        return $res;
    }

    /**
     * Autenticar usuário utilizando
     * usuário e senha
     *
     * @param string $usernameOrEmail
     * @param string $password
     *
     * @return boolean
     */
    public function login($usernameOrEmail = null, $password = null)
    {
        if (! is_object($usernameOrEmail)) {
            $res   = $this->usuarios->findOne(array('email' => $usernameOrEmail, 'status' => 1));
            $user  = (object) $res;
        }

        if (is_object($user) && ($user->email === $usernameOrEmail) && crypt($password, $user->hashSenha) === $user->hashSenha) {
            // gera chave e também o timestamp em segundos
            $chave          = parent::geraChave();
            $timestampAtual = (int) gmdate('U');

            $res = $this->usuarios->update(array("_id" => $user->_id), array('$set'=>array("chave"=>$chave, "chaveTempo"=>$timestampAtual)));

            if ($res) {
                return $this->completeLogin($chave);
            }

            throw new GeracaoChaveException();
        }

        return false;
    }

    /**
     * Completa o login retornando a chave
     *
     * @param string $chave
     *
     * @return string
     */
    protected function completeLogin($chave)
    {
        return $chave;
    }

    /**
     * Realiza logout zerando a chave associada
     * ao usuario no banco de dados
     *
     * @param string $chave
     *
     * @return boolean
     */
    public function logout($chave)
    {
        $res = $this->usuarios->update(array("chave" => $chave), array('$set'=>array("chave"=>null, "chaveTempo"=>null)));

        // Double check
        return !$this->loggedIn($chave);
    }
}
