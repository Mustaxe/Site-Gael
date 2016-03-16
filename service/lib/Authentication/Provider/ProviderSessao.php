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

abstract class ProviderSessao
{
    /**
     * @var array
     */
    protected $config;

    /**
     * Inicializa o provider
     *
     * @param array $config
     */
    public function __construct(array $config) {
        // Carrega sessão
        if(session_id() === "") {
            session_start();
        }

        // Save the config in global variable
        $this->config = $config;
    }

    /**
     * Verifica se usuário está logado com base no provider
     *
     * @return boolean
     */
    abstract public function loggedIn();

    /**
     * Autentica usuário com base no username ou email
     * e senha
     *
     * @param string $usernameOrEmail
     * @param string $password
     *
     * @return boolean
     */
    abstract public function login($usernameOrEmail, $password);

    /**
     * Logout do usuário
     *
     * @return boolean
     */
    abstract public function logout();

    /**
     * Busca informações do usuário
     *
     * @return array|null
     */
    abstract function getUser();

    /**
     * Completa login armazenando detalhes do usuário na sessão
     *
     * @return string
     */
    protected function completeLogin($user) {
        $_SESSION['auth_user'] = $user;

        return 'Autenticação realizada com sucesso';
    }

    /**
     * Verifica se usuário tem acesso a determinada rota
     *
     * @param string $rota
     *
     * @return boolean
     */
    abstract public function usuarioPertenceGrupo($rota);
}
