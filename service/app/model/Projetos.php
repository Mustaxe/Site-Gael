<?php

namespace app\model;

use lib\Db\Crud as Crud;

class Projetos Extends Crud
{
    /**
     * @var string $table Nome da tabela
     */
    protected $table = 'tbl_projetos';
    
    /**
     * @var string $pk Chave primária da tabela
     */
    protected $pk    = 'id';
}
