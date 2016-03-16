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

class PDOSessao extends ProviderSessao
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
     * Inicializa conexão PDO e faz merge das configurações 
     * do usuário com as configurações default
     *
     * @param array $config
     */
    public function __construct($config, $fb)
    {
        parent::__construct($config);
        $this->config = array_merge($this->settings, $this->config);

        if (!isset($this->config['db']) || !($this->config['db']->getConnection() instanceof \PDO)) {
            throw new \InvalidArgumentException('Deve-se informar um objeto de conexão PDO válido.');
        }

        $this->pdo         = $this->config['db']->getConnection();
        $this->fb          = $fb;
    }

    /**
     * Verificação de login do usuário (sessão)
     *
     * @return boolean
     */
    public function loggedIn()
    {
        return (isset($_SESSION['auth_user']) && !empty($_SESSION['auth_user']));
    }

    /**
     * Busca usuário pelos
     * dados da sessão
     *
     * @return Usuario | null
     */
    public function getUser()
    {
        if (isset($_SESSION['auth_user']['id'])) {
            $sql  = "SELECT * FROM tbl_usuario WHERE id = :id and status = 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $_SESSION['auth_user']['id']);
            $stmt->execute();

            $user = $stmt->fetch(\PDO::FETCH_OBJ);

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
        $sql = '
            SELECT r.nome
            FROM tbl_rota r
            INNER JOIN tbl_usuario_grupo ug ON FIND_IN_SET(r.id, ug.rotas)
            INNER JOIN tbl_usuario u ON FIND_IN_SET(ug.id, u.grupos)
            WHERE u.id = :id and r.nome = :rota and r.status = 1 and u.status = 1 and ug.status = 1';
        $stmt = $this->pdo->prepare($sql);

        $stmt->bindParam(':id', $_SESSION['auth_user']['id']);
        $stmt->bindParam(':rota', $rota);
        $stmt->execute();

        $user =  $stmt->fetch();
		
        $res = $user ? true : false;

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
        if(! is_object($usernameOrEmail)) {
            $sql  = "SELECT * FROM tbl_usuario WHERE email = :email";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':email', $usernameOrEmail);
            $stmt->execute();

            $user = $stmt->fetch(\PDO::FETCH_OBJ);
        }

        if(is_object($user) && ($user->email === $usernameOrEmail) && crypt($password, $user->hashSenha) === $user->hashSenha) {
            return $this->completeLogin($user);
        }

        return false;
    }

    /**
     * Login e armazena detalhes do usuário na sessão
     *
     * @param object $user
     *
     * @return boolean
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
