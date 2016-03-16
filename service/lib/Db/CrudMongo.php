<?php 
namespace lib\Db;

use lib\Db\MongoDb;

class CrudMongo implements CrudInterface{

    private $db;

    private $variables;

    private $table;

    private $pk;

    private $rowCount = 0;

    private $status;

    public function __construct($data = array(), MongoDb $db, $table, $pk)
    {
        $this->db        = $db;
        $this->variables = $data;
        $this->pk        = $pk;
        $database        = $this->db->database();
        $this->table     = $database->$table;
        $this->status    = array('status' => 1);
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
     * O Save contempla 3 cenários:
     *  - Novo registro chamado com save($newValuesArray)
     *  - Atualiza um registro existente pelo id ($valuesArrayWithMongoID)
     *  - Atualiza um registro existente pelo valor($valuesArrayWithId, $criteria)
     */
    public function save($criteria = array(), $operador = null)
    {
        $value          = $this->variables;
        $id             = '';
        $whereCriteria  = $criteria;
        $sanitizedValue = $this->removeMongoId($value);
        $operador       = ($operador) ? ($operador) : '$set';

        // Passa um objeto existente que deseja atualizar pelo id
        if (!$this->isNewRecord($value, $criteria) && !$criteria) {
            if(isset($value["_id"])){
                $id = $value["_id"];
            } elseif(isset($value["id"])) {
                $id = $value["id"];
            }
            $whereCriteria = $this->createId($id);
        }

        if (empty($whereCriteria)) {
            $sanitizedValue = $this->insert($sanitizedValue);

            //$sanitizedValue['_id'] = $sanitizedValue['_id']->{'$id'};
            $whereCriteria = array('_id'=> $sanitizedValue['_id'] );
        } else {
            $whereCriteria = $whereCriteria + $this->status;
            $this->table->update($whereCriteria, array($operador => $sanitizedValue), array('upsert'=>false));
            //$sanitizedValue["_id"] = $id;
        }

        //return $sanitizedValue;
        return $this->query($whereCriteria);
    }

    public function unwrap($data)
    {
        $this->rowCount = 0;
        $output["results"] = array();

        foreach ($data as $result) {
            //if(isset($result["_id"])){
                foreach($result as $key => $value){
                    // converter ids do objeto mongoid para string
                    if(isset($result[$key]->{'$id'})){
                        $result[$key] = $result[$key]->{'$id'};
                    }
                    // converter datas do formato do mongo para o formato dd/mm/aaaa hh:mm:ss
                    if(isset($result[$key]->{'sec'})){
                        $result[$key] = date('d/m/Y H:i:s', $result[$key]->{'sec'});
                    }
                }
                $output['results'][] = $result; 
            //}
            $this->rowCount++;
        }

        return $output;
    }

    /**
     * Procura um único registro com a condição informada
     *
     * @param  array  $criteria Condição para pesquisa
     * @param  array  $campos   Campos para retornar na pesquisa
     *
     * @return object $R Resultado da pesquisa
     */
    public function row($criteria = array(), $campos = array())
    {
        $criteria = $criteria + $this->status;
        $data     = $this->table->findOne($criteria, $campos);
        $data     = $this->unwrap(array($data));
        $R        = (object) array();

        if (empty($data['results'])) {
            $R->cod = 404;
            $R->qtd = $this->rowCount;
            $R->res = "Nenhum resultado encontrado.";
        } else {
            $R->cod = 200;
            $R->qtd = $this->rowCount;
            $R->res = $data['results'][0];
        }

        return $R;
    }

    /**
     * Retorna documentos conforme condições informadas
     *
     * @param  array  $criteria Condição para pesquisa
     * @param  array  $campos   Campos para retornar na pesquisa
     * @param  array  $order    Campos para ordenação
     * @param  array  $limit    Quantidade de resultados retornados
     *
     * @return object $R Resultado da pesquisa
     */
    public function query($criteria = array(), $campos = array(), $order = array(), $limit = array())
    {
        $criteria = $criteria + $this->status;
        $cursor   = $this->table->find($criteria, $campos);
        if (!empty($order)) $cursor->sort($order);
        if (!empty($limit)) $cursor->limit($limit[0]);
        $data     = $this->unwrap($cursor);
        $R        = (object) array();

        if (empty($data['results'])) {
            $R->cod = 404;
            $R->qtd = $this->rowCount;
            $R->res = "Nenhum resultado encontrado.";
        } else {
            $R->cod = 200;
            $R->qtd = $this->rowCount;
            $R->res = $data['results'];
        }

        return $R;
    }

    private function insert($value)
    {
        $value = $value + $this->status;
        $this->table->insert($value);

        return $value;
    }

    private function update($criteria, $value, $operador = null)
    {
        $operador = ($operador) ? ($operador) : ('$set');

        $this->table->update($criteria, array($operador => $value), array('upsert' => false));

        return $this->query($criteria);
    }

    /**
     * Cria um MongoId para um array
     * 
     * @param  $id
     *
     * @return array MongoId
     */
    private function createId($id = null)
    {
        if (is_array($id)) {
            $id = (object) $id;
        }

        if (is_object($id) && isset($id->{'$id'})) {
            $id = $id->{'$id'};
        }

        return array("_id" => new \MongoId($id));
    }

    /**
     * Remove o MongoId do array informado
     */
    private function removeMongoId($value)
    {
        $sanitizedValue = $value;

        if (isset($sanitizedValue["_id"])) {
            unset($sanitizedValue["_id"]);
        }

        if (isset($sanitizedValue["id"])) {
            unset($sanitizedValue["id"]);
        }

        return $sanitizedValue;
    }

    /**
     * Procura por um _id no array para determinar
     * se é um novo registro ou não
     */
    private function isNewRecord($value, $criteria = null)
    {
        $id = '';
        if(isset($value["_id"])){
            $id = $value["_id"];
        } elseif(isset($value["id"])) {
            $id = $value["id"];
        }

        return !$criteria && (!isset($id) || $id == "");
    }

    public function findAll()
    {
        return $this->query();
    }

    public function findById($id = '', $campos = array())
    {
        $id = new \MongoId($id);

        return $this->row(array('_id'=>$id), $campos);
    }

    /**
     * Pesquisa documentos em uma coleção
     * conforme condições informadas
     *
     * Ordenação:
     *   - para ordem asc,  associar 1 ao campo:   array( 'created' => 1 )
     *   - para ordem desc, associar -1 ao campo: array( 'created' => -1 )
     *   - se for ordenar por mais de um campo, a ordem dos campos informada
     *     no array é levada em consideração
     *
     **/
    public function find($criteria = array(), $whereCond = NULL, $campos = array(), $order = array(), $limit = array(), $join = NULL)
    {
        return $this->query($criteria, $campos, $order, $limit);
    }

    public function findOne($criteria = array(), $whereCond = NULL, $campos = array(), $join = NULL)
    {
        return $this->row($criteria, $campos);
    }

    /*
     * Remove documentos conforme condições informadas
     */
    public function delete($criteria = array(), $whereCond = NULL)
    {
        $res      = array();
        $res['n'] = 0;
        $R        = (object) array();

        if (!empty($criteria)) {
            // não remove, altera status para 0
            // $res = $this->table->remove($criteria);

            $res = $this->table->update($criteria, array('$set'=>array('status'=>0)), array('upsert'=>false));
        }

        if ($res['n'] == 0) {
            $R->cod = 404;
            $R->qtd = $res['n'];
            $R->res = "Nenhum resultado encontrado.";
        } else {
            $R->cod = 200;
            $R->qtd = $res['n'];
            $R->res = "Informação removida com sucesso";
        }

        return $R;
    }

    public function create()
    {
        return $this->save();
    }
}
