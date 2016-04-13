<?php

namespace app\model;

use lib\Db\Crud as Crud;

class Arquivos Extends Crud
{
    /**
     * @var string $table Nome da tabela
     */
    protected $table = 'tbl_arquivos';
    
    /**
     * @var string $pk Chave primária da tabela
     */
    protected $pk    = 'id';
}
