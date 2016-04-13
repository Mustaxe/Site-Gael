<?php

namespace app\model;

use lib\Db\Crud as Crud;

class Pastas Extends Crud
{
    /**
     * @var string $table Nome da tabela
     */
    protected $table = 'tbl_pastas';
    
    /**
     * @var string $pk Chave primária da tabela
     */
    protected $pk    = 'id';
}
