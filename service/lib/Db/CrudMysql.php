<?php 
namespace lib\Db;

use lib\Db\Db;

class CrudMysql implements CrudInterface{

    private $db;

    private $variables;

    private $table;

    private $pk;

    public function __construct($data = array(), Db $db, $table, $pk)
    {
        $this->db        = $db;
        $this->variables = $data;
        $this->pk        = $pk;
        $this->table     = $table;
    }

    public function setVariables($name, $value)
    {
        $this->variables[$name] = $value;
    }

    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * IMPORTANTE: Se houver um campo na condição igual ao campo sendo atualizado
     * usar um nome diferente do campo no array criteria para não ocorrer 
     * problema no bind das variáveis
     *
     **/
    public function save($criteria = array(), $condition = null)
    {
        if (empty($criteria)) {
            $condition = $this->pk . '= :' . $this->pk;
            $this->variables[$this->pk] = (empty($this->variables[$this->pk])) ? '0' : $this->variables[$this->pk];
        }

        $fieldsvals = '';
        $columns    = array_keys($this->variables);

        foreach ($columns as $column) {
            if ($column !== $this->pk)
            $fieldsvals .= $column . ' = :'. $column . ',';
        }

        $fieldsvals = substr_replace($fieldsvals , '', -1);

        if (count($columns) > 0) {
            $sql = 'UPDATE ' . $this->table .  ' SET ' . $fieldsvals . ' WHERE ' . $condition . ' AND STATUS = 1';
            return $this->db->query($sql, $this->variables + $criteria);
        }

        // todo: $this->dispatcher->dispatch('on.save', new SaveEvent($sql));
    }

    public function create()
    {
        $bindings       = $this->variables;

        if (!empty($bindings)) {
            $fields     =  array_keys($bindings);
            $fieldsvals =  array(implode(',',$fields),':' . implode(',:',$fields));
            $sql        = 'INSERT INTO '.$this->table.' ('.$fieldsvals[0].') VALUES ('.$fieldsvals[1].')';
        } else {
            $sql        = 'INSERT INTO '.$this->table.' () VALUES ()';
        }

        return $this->db->query($sql,$bindings);
    }

    public function delete($criteria = array(), $whereCond = NULL)
    {
        $res      = (object) array();
        $res->qtd = 0;
        $R        = (object) array();

        if (!empty($criteria)) {
            // $sql = "DELETE FROM " . $this->table . " WHERE " . $this->pk . "= :" . $this->pk. " LIMIT 1" ;
            $sql = 'UPDATE ' . $this->table . ' SET STATUS = 0 WHERE ' . $whereCond;
            $res = $this->db->query($sql, $criteria);
        }
 
        if ($res->qtd == 0) {
            $R->cod = 404;
            $R->qtd =  $res->qtd;
            $R->res = 'Nenhum resultado encontrado.';
        } else {
            $R->cod = 200;
            $R->qtd = $res->qtd;
            $R->res = 'Informação removida com sucesso';
        }

        return $R; 
    }

    private function converteCamposParaString($campos)
    {
        if (!empty($campos)) {
            //$json   = json_encode(array_keys($campos));
			$json   = json_encode($campos);
            $campos = str_replace(array('[', ']', ':', '"'), array('', '', '=', ''), $json);
        }

        return (empty($campos)) ? ('*') : ($campos);
    }


    public function findById($id = '', $campos = array())
    {
        $campos = $this->converteCamposParaString($campos);
        $R      = null;
        
        $id     = (empty($this->variables[$this->pk])) ? ($id) : ($this->variables[$this->pk]);

        if (!empty($id)) {
            $sql = 'SELECT '. $campos .' FROM ' . $this->table .' WHERE ' . $this->pk . '= :' . $this->pk . ' AND STATUS = 1 LIMIT 1';
            $R   = $this->db->row($sql,array($this->pk=>$id));
        }

        return $R; 
    }

    public function findOne($criteria = array(), $whereCond = NULL, $campos = array(), $join = NULL)
    {
        $campos     = $this->converteCamposParaString($campos);

        $sql = 'SELECT '. $campos .' FROM ' . $this->table;
        if (!is_null($join))  $sql .= ' '.$join;
        $sql .= ' WHERE ' . $whereCond . ' AND STATUS = 1';

        return $this->db->row($sql, $criteria);
    }

    /**
     * Monta select para pesquisa
     *
     * Exemplos:
     * $criteria  - array('nome' => '%an%', 'idade' => 21)
     * $whereCond - "nome like :nome AND idade = :idade"
     * $limit     - array(15, 30)
     * $join      - "LEFT JOIN tbl_dados ON tbl_pessoas.id = tbl_dados.id"
     *
     **/
    public function find($criteria = array(), $whereCond = NULL, $campos = array(), $order = array(), $limit = array(), $join = NULL)
    {
        $campos = $this->converteCamposParaString($campos);

        $sql = 'SELECT '. $campos .' FROM ' . $this->table;
        if (!is_null($join))  $sql .= ' '.$join;
        $sql .= ' WHERE ' . $whereCond . ' AND STATUS = 1';
        if (!empty($order)) {
            $fieldsvals = '';
            $columns    = array_keys($order);

            foreach ($columns as $column) {
                //if($column !== $this->pk)
                $fieldsvals .= $column . ' = :'. $column . ',';
            }

            $fieldsvals = substr_replace($fieldsvals , '', -1);

            $sql .= ' ORDER BY '.$fieldsvals;
            $criteria = $criteria + $order;
        }
        if (!empty($limit)) {
            $inicio = $limit[0];
            $fim    = isset($limit[1]) ? ', '.$limit[1] : '';
            $sql .= ' LIMIT ' . $inicio . $fim;
        }

        //var_dump($criteria); var_dump($sql); die;

        return $this->db->query($sql, $criteria);
    }

    public function findAll()
    {
        return $this->db->query('SELECT * FROM ' . $this->table . ' WHERE STATUS = 1 ORDER BY 2');
    }

    public function Query($sql)
    {
        return $this->db->query($sql);
    }
	
    public function findQuery($sql)
    {
        return $this->db->query($sql);
    }

    public function min($field)
    {
        if ($field)
        return $this->db->single('SELECT min(' . $field . ')' . ' FROM ' . $this->table . ' WHERE STATUS = 1');
    }

    public function max($field)
    {
        if ($field)
        return $this->db->single('SELECT max(' . $field . ')' . ' FROM ' . $this->table . ' WHERE STATUS = 1');
    }

    public function avg($field)
    {
        if ($field)
        return $this->db->single('SELECT avg(' . $field . ')' . ' FROM ' . $this->table . ' WHERE STATUS = 1');
    }

    public function sum($field)
    {
        $res = $this->getResultadoDefault();

        if ($field) {
            $soma = $this->db->single('SELECT sum(' . $field . ')' . ' FROM ' . $this->table . ' WHERE STATUS = 1');

            $res = $this->getResultado($soma);
        }

        return $res;
    }

    public function count($field)
    {
        if ($field)
        return $this->db->single('SELECT count(' . $field . ')' . ' FROM ' . $this->table . ' WHERE STATUS = 1');
    }

    public function getResultadoDefault()
    {
        $R = (object) array();

        $R->cod = 404;
        $R->qtd = 0;
        $R->res = 'Nenhum resultado encontrado ou atualizado.';

        return $R;
    }

    public function getResultado($var)
    {
        $R = (object) array();

        if ($var === false || $var == 0 ) {
            $R->cod = 404;
            $R->qtd = 0;
            $R->res = 'Nenhum resultado encontrado ou atualizado.';
        } else {
            $R->cod = 200;
            $R->qtd = 1;
            $R->res = $var;
        }

        return $R;
    }
	
	public function reArrayFiles(&$file_post) 
	{
		$file_ary = array();
		$file_count = count($file_post['name']);
		$file_keys = array_keys($file_post);

		for ($i=0; $i<$file_count; $i++) {
			foreach ($file_keys as $key) {
				$file_ary[$i][$key] = $file_post[$key][$i];
			}
		}

		return $file_ary;
	}	
}
