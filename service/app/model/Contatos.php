<?php

namespace app\model;

use lib\Db\Crud as Crud;

class Contatos Extends Crud
{
    /**
     * @var string $table Nome da tabela
     */
    protected $table = 'tbl_contatos';
    
    /**
     * @var string $pk Chave primária da tabela
     */
    protected $pk    = 'id';
}
