<?php

namespace lib\Exception;

use lib\Exception\AuthenticationException;

/**
 * Exceção HTTP 403
 */
class HttpForbiddenException extends AuthenticationException
{
    /**
     * Construtor
     *
     * @param string    $message  Mensagem da exceção
     * @param int       $code     Código da exceção
     * @param Exception $previous Exceção anterior
     */
    public function __construct($message = 'Você não está autorizado a acessar esta página', $code = 403, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
