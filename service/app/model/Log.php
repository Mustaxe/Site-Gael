<?php

namespace app\model;

use lib\Db\Crud;

/**
 * Log 
 */
class Log Extends Crud
{
    /**
     * @var string $table Nome da tabela
     */
    protected $table = 'tbl_log';
    
    /**
     * @var string $pk Chave primária da tabela
     */
    protected $pk    = 'id';

    protected $db;

    protected $variables;

    public function __construct($variables, $db)
    {
        $this->db = $db;
        $this->variables  = $variables;

        parent::__construct($variables, $db);
    }
}
