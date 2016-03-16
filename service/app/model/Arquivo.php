<?php

namespace app\model;

use lib\Db\Crud as Crud;

class Arquivo Extends Crud
{
    /**
     * @var string $table Nome da tabela
     */
    protected $table = 'tbl_arquivo';
    
    /**
     * @var string $pk Chave primária da tabela
     */
    protected $pk    = 'id';
}
