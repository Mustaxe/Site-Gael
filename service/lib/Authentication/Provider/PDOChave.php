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

class PDOChave extends ProviderChave
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
        $sql = "SELECT * FROM tbl_usuario WHERE chave = :chave and status = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':chave', $chave);
        $stmt->execute();

        $user = $stmt->fetch(\PDO::FETCH_OBJ);

        if ($user) {
            return !(parent::chaveExpirou($user->chaveTempo));
        }

        return false;
    }

    /**
     * Busca usuário pela chave
     * ou por dados da sessão
     *
     * @return Usuario | null
     */
    public function getUser($chave = null)
    {
        if (!empty($chave)) {
            $sql  = "SELECT * FROM tbl_usuario WHERE chave = :chave and status = 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':chave', $chave);
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
     * @param string $chave
     *
     * @return boolean
     */
    public function usuarioPertenceGrupo($rota, $chave = null)
    {
        $sql = '
            SELECT r.nome
            FROM tbl_rota r
            INNER JOIN tbl_usuario_grupo ug ON FIND_IN_SET(r.id, ug.rotas)
            INNER JOIN tbl_usuario u ON FIND_IN_SET(ug.id, u.grupos)
            WHERE u.chave = :chave and r.nome = :rota and r.status = 1 and u.status = 1 and ug.status = 1';

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindParam(':chave', $chave);
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
        if (! is_object($usernameOrEmail)) {
            $sql = "SELECT * FROM tbl_usuario WHERE email = :email and status = 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':email', $usernameOrEmail);
            $stmt->execute();

            $user = $stmt->fetch(\PDO::FETCH_OBJ);
        }

        if (is_object($user) && ($user->email === $usernameOrEmail) && crypt($password, $user->hashSenha) === $user->hashSenha) {
            // gera chave e grava timestamp em segundos
            $chave          = parent::geraChave();
            $timestampAtual = (int) gmdate('U');

            $stmt = $this->pdo->prepare("UPDATE tbl_usuario SET chave = :chave, chaveTempo = :chaveTempo WHERE id = :id"); 
            $stmt->bindParam(':chave', $chave);
            $stmt->bindParam(':chaveTempo', $timestampAtual);
            $stmt->bindParam(':id', $user->id);
            $res = $stmt->execute();

            if($res){
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
    public function logout($chave = null)
    {
        $stmt = $this->pdo->prepare("UPDATE tbl_usuario SET chave = :nova, chaveTempo = :chaveTempo WHERE chave = :chave"); 
        $stmt->bindValue(':nova', null);
        $stmt->bindValue(':chaveTempo', null);
        $stmt->bindValue(':chave', $chave);
        $res = $stmt->execute();

        // Double check
        return !$this->loggedIn($chave);
    }
}
