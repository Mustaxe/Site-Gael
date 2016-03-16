<?php

namespace lib\Exception;

use lib\Exception\AuthenticationException;

/**
 * Exceção HTTP 401
 */
class HttpNotAuthenticatedException extends AuthenticationException
{
    /**
     * Construtor
     *
     * @param string    $message  Mensagem da exceção
     * @param int       $code     Código da exceção
     * @param Exception $previous Exceção anterior
     */
    public function __construct($message = 'Usuário não autenticado', $code = 401, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
