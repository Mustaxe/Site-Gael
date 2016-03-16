<?php

namespace lib\Exception;

use lib\Exception\AuthenticationException;

/**
 * Exceção na geração de chave de autenticação
 */
class GeracaoChaveException extends AuthenticationException
{
    /**
     * Construtor
     *
     * @param string    $message  Mensagem da exceção
     * @param int       $code     Código da exceção
     * @param Exception $previous Exceção anterior
     */
    public function __construct($message = 'Ocorreu um problema na geração da chave de autenticação', $code = 401, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
