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

class MongoDbSessao extends ProviderSessao
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
        return (isset($_SESSION['auth_user']) && !empty($_SESSION['auth_user']));
    }

    /**
     * Busca informações do usuário
     *
     * @return array|null
     */
    public function getUser()
    {
        if (isset($_SESSION['auth_user']['id'])) {
            $res  = $this->usuarios->findOne(array('id' => $_SESSION['auth_user']['id'], 'status' => 1));
            $user = (object) $res;

            return $user;
        }

        return null;
    }

    /**
     * Verifica se usuário tem acesso a determinada rota
     *
     * @param string $rota
     *
     * @return boolean
     */
    public function usuarioPertenceGrupo($rota)
    {
        // localizar id da rota
        $objRota = $this->rotas->findOne(array("nome" => $rota, 'status' => 1));

        $cursor  = $this->usuarios->find(array("grupos" => $objRota['_id'], "id" => $_SESSION['auth_user']['id'], 'status' => 1));

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
            return $this->completeLogin($user);
        }

        return false;
    }

    /**
     * Login e armazena detalhes do usuário na sessão
     *
     * @param string $chave
     *
     * @return string
     */
    protected function completeLogin($user)
    {
        $userInfo = array(
            'id'        => $user->id,
            'email'     => $user->email,
            'logged_in' => true
        );

        return parent::completeLogin($userInfo);
    }

    /**
     * Realiza logout removendo os valores da sessão ou
     * removendo a sessão completamente
     *
     * @param boolean $destroy
     *
     * @return boolean
     */
    public function logout($destroy = false)
    {
        if ($destroy === true) {
            // Remove a sessão completamente
            session_destroy();
        } else {
            // Remove da sessão as informações do usuário
            $_SESSION['auth_user'] = array();
        }

        // Double check
        return !$this->loggedIn();
    }
}
