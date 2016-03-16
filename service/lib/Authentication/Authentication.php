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

namespace lib\Authentication;

use lib\Authentication\Strong;

class Authentication extends Strong
{
    /**
     * @var array
     */
    protected $config = array(
        'name'     => 'default',
        'provider' => 'PDO',
    );

    /**
     * @const string
     */
    const VERSION = '1.0.0';

    /**
     * @var array[Strong]
     */
    protected static $apps = array();

    /**
     * @var Strong_Provider
     */
    protected $provider;

    /**
     * @var string Custo para geração do hash da senha
     */
    protected $custo = '08';

    /**
     * Factory method to call Strong and initalize
     *
     * @param array $config
     * @return Strong
     */
    public static function factory($config = array(), $fb = null)
    {
        return new self($config, $fb);
    }

    /**
     * Get an existing instance of Strong using a
     * static method
     *
     * @param string $name
     * @return Strong
     */
    public static function getInstance($name = 'default')
    {
        return self::$apps[$name];
    }

    /**
     * Instantiate Strong and provide config for your settings
     *
     * @param array $config
     */
    public function __construct($config = array(), $fb = null)
    {
        // Save the config in global variable
        $this->setConfig($config);

        if (!isset($config['auth.type'])) {
            throw new \InvalidArgumentException('O tipo de autenticação não foi informado na configuração.');
        }

        $this->tipoAutenticacao = $config['auth.type'];

        // Seta o nome da classe do provider
        $provider = '\\lib\\Authentication\\Provider\\' . $this->config['provider'] . ucfirst($this->tipoAutenticacao);

        if ( !class_exists($provider)) {
            throw new \Exception('O provider ' . $this->config['provider'] . ' não existe em ' . get_class($this));
        }

        // Carrega o provider
        $provider = new $provider($this->config, $fb);
        $instanciaProvider = '\lib\Authentication\Provider\Provider' . ucfirst($this->tipoAutenticacao);

        if ( !($provider instanceof $instanciaProvider) ) {
            throw new \Exception('O provider atual ' . $this->config['provider'] . ' não estende '.$instanciaProvider);
        }

        $this->provider = $provider;

        // Seta o nome da app
        if ( !isset(self::$apps[$this->config['name']]) ) {
            $this->setName($this->config['name']);
        }
    }

    /**
     * Verifica se usuário tem acesso a determinada rota
     *
     * @param string $rota
     *
     * @return boolean
     */
    public function usuarioPertenceGrupo($rota, $chave = null)
    {
        if ($this->tipoAutenticacao == 'sessao') {
            return $this->provider->usuarioPertenceGrupo($rota);
        }

        return $this->provider->usuarioPertenceGrupo($rota, $chave);
    }

    /**
     * Aplica hash na senha informada
     *
     * @param string $senha Senha sem criptografia
     *
     * @return string Hash da senha
     */
    public function hashPassword($password)
    {
        // return $this->provider->hashPassword($password);
        // mesma lógica para todos os providers, então é definida diretamente aqui. Se necessário mover a lógica para os providers e habilitar linha acima

        $salt = bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM)); // get 256 random bits in hex

        // outra opção de salt:
        // $salt = substr(str_replace('+', '.', base64_encode(call_user_func_array('pack', array_merge(array('H14N'), explode('.', uniqid('', true)))).pack('N2', mt_rand(), mt_rand()))), 0, 22);

        // Gera um hash utilizando bcrypt
        $hashSenha = crypt($password, '$2a$' . $this->custo . '$' . $salt . '$');

        return $hashSenha;
    }

    /**
     * Gera token de confirmação
     *
     * @return string $token Token de Confirmação
     */
    public function geraTokenConfirmacao()
    {
        //return $this->provider->geraTokenConfirmacao();
        // mesma lógica para todos os providers, então é definida diretamente aqui. Se necessário mover a lógica para os providers e habilitar linha acima

        $numeroRandomico = hash('sha256', uniqid(mt_rand(), true), true);

        return base_convert(bin2hex($numeroRandomico), 16, 36);
    }
}
