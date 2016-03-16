<?php

namespace lib\Db;

use lib\Db\CrudMongo;
use lib\Db\CrudMysql;

abstract class Crud
{
    protected $table;
    
    protected $pk = 'id';

    protected $db;

    protected $variables;

    protected $crud;

    public function __construct($variables, $db)
    {
        $this->variables  = $variables;
        $crudClass = $db->getCrudClass();
        $this->crud = new $crudClass($this->variables, $db, $this->table, $this->pk);
    }

    public function __set($name,$value)
    {
        if (strtolower($name) === $this->pk) {
            //$this->crud->variables[$this->pk] = $value;
            $this->crud->setVariables($this->pk, $value);
        } else {
            //$this->crud->variables[$name] = $value;
            $this->crud->setVariables($name, $value);
        }
    }

    public function __get($name)
    {
        /*if (is_array($this->variables)) {
            if (array_key_exists($name,$this->variables)) {
                return $this->variables[$name];
            }
        }*/

        if (is_array($this->crud->getVariables())) {
            if (array_key_exists($name,$this->crud->getVariables())) {
                return $this->crud->getVariables[$name];
            }
        }
    }

    public function getVariables()
    {
        return $this->crud->getVariables();
    }

    public function findAll()
    {
        return $this->crud->findAll();
    }

    public function findById($id = "", $campos = array())
    {
        return $this->crud->findById($id, $campos);
    }

    public function findQuery($sql)
    {
        return $this->crud->findQuery($sql);
    }
	
	
    /**
     * $whereCond e $join - são usados somente para o MySQL
     * se usar mongoDb passar como NULL esses parâmetros
     *
     **/
    public function find($criteria = array(), $whereCond = NULL, $campos = array(), $order = array(), $limit = array(), $join = NULL)
    {
        return $this->crud->find($criteria, $whereCond, $campos, $order, $limit, $join);
    }

    /**
     * $whereCond e $join - são usados somente para o MySQL
     * se usar mongoDb passar como NULL esses parâmetros
     *
     **/
    public function findOne($criteria = array(), $whereCond = NULL, $campos = array(), $join = NULL)
    {
        return $this->crud->findOne($criteria, $whereCond, $campos, $join);
    }

	public function Query($sql)
	{
		return $this->crud->Query($sql);
	}	

    public function save($criteria = array(), $condition = null)
    {
        return $this->crud->save($criteria, $condition);
    }

    public function create()
    {
        return $this->crud->create();
    }

    /**
     * $whereCond - usado somente para o MySQL
     * se usar mongoDb passar como NULL esse parâmetro
     *
     **/
    public function delete($criteria = array(), $whereCond = NULL)
    {
        return $this->crud->delete($criteria, $whereCond);
    }

    public function sum($field)
    {
        return $this->crud->sum($field);
    }
	
    public function reArrayFiles($field)
    {
        return $this->crud->reArrayFiles($field);
    }
	
}
