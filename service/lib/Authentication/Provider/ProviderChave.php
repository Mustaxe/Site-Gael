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

use \RandomLib\Factory;
use \lib\Authentication\KeyGenerator;

abstract class ProviderChave
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var int Tempo limite, em segundos, para expirar a chave
     */
    private $tempoLimite;

    /**
     * Inicializa o provider
     *
     * @param array $config
     */
    public function __construct(array $config) {
        $this->config      = $config;
        $this->tempoLimite = $this->config['chave.expira'];
    }

    /**
     * Verifica se usuário está logado com base no provider
     *
     * @param string $chave
     *
     * @return boolean
     */
    abstract public function loggedIn($chave);

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
     * Realiza o logout do usuário
     *
     * @param string $chave
     *
     * @return boolean
     */
    abstract public function logout($chave = null);

    /**
     * Busca informações do usuário
     *
     * @return array|null
     */
    abstract public function getUser($chave);

    /**
     * Login e retorna a chave
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
     * Gera chave para usuário acessar a API
     *
     * @return string $chave Chave de autenticação
     */
    protected function geraChave()
    {
        $randomFactory = new Factory();
        $keyGenerator  = new KeyGenerator($randomFactory);

        // chave randômica de 40 caracteres
        $chave         = $keyGenerator->generateKey();

        return $chave;
    }

    /**
     * Verifica se a geração da chave passou do tempo limite
     * definido em configuração (default é um dia)
     *
     * @param  int     $timestampBd     GMT timestamp do BD
     *
     * @return boolean
     */
    protected function chaveExpirou($timestampBd)
    {
        $timestampAtual = (int) gmdate('U');

        if (abs($timestampBd - $timestampAtual) > $this->tempoLimite) {
            return true;
        }

        return false;
    }

    /**
     * Verifica se usuário tem acesso a determinada rota
     *
     * @param string $rota
     *
     * @return boolean
     */
    abstract public function usuarioPertenceGrupo($rota, $chave = null);
}
