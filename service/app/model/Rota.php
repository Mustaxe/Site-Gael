<?php

namespace app\model;

use lib\Db\Crud as Crud;

/**
 * Esta tabela contém as urls que precisam de autenticação para serem acessadas
 * elas devem iniciar com '/' por exemplo, para tornar a url 'admin' segura 
 * adicionar no campo name desta tabela o valor '/admin' 
 */

class Rota Extends Crud
{
    /**
     * @var string $table Nome da tabela
     */
    protected $table = 'tbl_rota';
    
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
