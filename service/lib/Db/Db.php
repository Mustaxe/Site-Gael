<?php

namespace lib\Db;

use lib\Log\DbWriter;
use \PDO;

class Db
{
    /**
     * @var object O objeto PDO
     */
    public $pdo;

    /**
     * @var object Objeto PDO statement
     */
    private $sQuery;

    /**
     * @var array  Configurações do banco de dados
     */
    private $settings;

    /**
     * @var bool  Se está conectado ao banco de dados
     */
    private $bConnected = false;

    /**
     * @var object Objeto para log de exceções
     */
    private $log;

    /**
     * @var array Os parâmetros da query SQL
     */
    private $parameters;

    /**
     * @var bool Se executou query com sucesso
     */
    private $success;

    /**
    *   Construtor
    *
    *   @param array $config Configurações da aplicação
    */
    public function __construct(array $config, $log, $app)
    {
        $this->log        = $log;
        $this->app        = $app;
        $this->parameters = array();

        $this->connect($config);
    }

    /**
     *   Conecta ao banco de dados
     */
    private function connect(array $config)
    {
        $dsn = 'mysql:dbname='.$config['dbmysql']['database'].';host='.$config['dbmysql']['host'].'';
        try {
            $this->pdo = new PDO($dsn, $config['dbmysql']['username'], $config['dbmysql']['password'], array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
            
            // Para permitir o log de qualquer exceção quando ocorrer um erro fatal
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // usar prepared statements reais
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            $this->bConnected = true;
        } catch (\PDOException $e) {
            //echo $this->exceptionLog('Erro de conexão ao banco de dados em ' . $e->getFile() . ' na linha ' . $e->getLine() . ': ' . $e->getMessage());
            echo json_encode(array('cod'=>500, 'res'=>'Problema ao conectar ao banco de dados. '.$e->getMessage()));

            exit;
        }
    }

    /*
     *   Fecha a conexão com o PDO
     */
    public function closeConnection()
    {
        /* 
         *   Seta o objeto PDO como null para fechar a conexão
         *   http://www.php.net/manual/en/pdo.connections.php
         */
        $this->pdo = null;
    }

    /**
     *   Todo método que precisa executar uma query SQL utiliza este método
     */
    private function init($query,$parameters = "",$fetchmode = PDO::FETCH_ASSOC,$class='')
    {
        // Conecta ao banco de dados
        if (!$this->bConnected) { $this->connect(); }
        try {
            // Prepara a query
            $this->sQuery = $this->pdo->prepare($query);
            
            // Adiciona parâmetros ao array $parameters
            $this->bindMore($parameters);

            // Bind dos parâmetros
            if (!empty($this->parameters)) {
                foreach($this->parameters as $param) {
                    $parameters = explode("\x7F",$param);
                    $this->sQuery->bindParam($parameters[0],$parameters[1]);
                }
            }

            if(!empty($class)){
                $this->sQuery->setFetchMode($fetchmode, $class);
            }

            $this->success   = $this->sQuery->execute();
        } catch(\PDOException $e) {
            // echo $this->exceptionLog($e->getMessage(), $query );
            return $this->app->error($e); 
        }

        $this->parameters = array();
    }

   /**
    *   @void 
    *
    *   Adiciona o parâmetro ao array de parâmetros
    *
    *   @param string $para
    *   @param string $value
    */  
    public function bind($para, $value)
    {   
        $this->parameters[sizeof($this->parameters)] = ":" . $para . "\x7F" . $value;
    }

   /**
    *   @void
    *
    *   Adiciona mais parâmetros no array de parâmetros
    *
    *   @param array $parray
    */  
    public function bindMore($parray)
    {
        if(empty($this->parameters) && is_array($parray)) {
            $columns = array_keys($parray);
            foreach($columns as $i => &$column) {
                $this->bind($column, $parray[$column]);
            }
        }
    }

    /**
     *   Se query SQL contém um SELECT ele retorna um array contendo todos os resultados
     *   Se statement SQL é DELETE, INSERT, ou UPDATE retorna o número de linhas afetadas
     *
     *   @param  string $query
     *   @param  array  $params
     *   @param  int    $fetchmode
     *
     *   @return mixed
     */
    public function query($query, $params = null, $fetchmode = PDO::FETCH_ASSOC, $class='')
    {
        $query = trim($query);

        $this->init($query,$params,$fetchmode,$class);

        $R = (object) array();

        // As seis primeiras letras do statement sql: insert, select, etc...
        $statement = strtolower(substr($query, 0 , 6));

        if($this->sQuery->rowCount() == 0) {
            $R->cod = 404;
            $R->qtd = $this->sQuery->rowCount();
            $R->res = "Nenhum resultado encontrado ou atualizado.";
        } else {
            $R->cod = 200;
            $R->qtd = $this->sQuery->rowCount();
            if ($statement === 'select') {
                $R->res = $this->sQuery->fetchAll($fetchmode);
            }
            elseif ( $statement === 'insert' ||  $statement === 'update' || $statement === 'delete' ) {
                $R->res = $this->sQuery->rowCount();
            }
        }

        return $R;
    }

    /**
     *  Retorna o último id inserido
     *
     *  @return string
     */
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }

    /**
     *   Retorna um array que representa uma coluna do result set
     *
     *   @param  string $query
     *   @param  array  $params
     *
     *   @return array
     */
    public function column($query, $params = null)
    {
        $this->init($query, $params);
        $Columns = $this->sQuery->fetchAll(PDO::FETCH_NUM);
        
        $column = null;

        foreach($Columns as $cells) {
            $column[] = $cells[0];
        }

        return $column;
    }

    /**
     *   Retorna um array que representa um único registro
     *
     *   @param  string $query
     *   @param  array  $params
     *   @param  int    $fetchmode
     *
     *   @return array
     */  
    public function row($query, $params = null, $fetchmode = PDO::FETCH_ASSOC, $class='')
    {
        $this->init($query, $params, $fetchmode, $class);

        $R = (object) array();

        // As seis primeiras letras do statement sql: insert, select, etc...
        $statement = strtolower(substr($query, 0 , 6));

        if($this->sQuery->rowCount() == 0) {
            $R->cod = 404;
            $R->qtd = $this->sQuery->rowCount();
            $R->res = "Nenhum resultado encontrado ou atualizado.";
        } else {
            $R->cod = 200;
            $R->qtd = $this->sQuery->rowCount();
            $R->res = $this->sQuery->fetch($fetchmode);
        }

        return $R;
    }

   /**
    *   Retorna o valor de uma única columa/campo
    *
    *   @param  string $query
    *   @param  array  $params
    *
    *   @return string
    */
    public function single($query, $params = null)
    {
        $this->init($query, $params);
        return $this->sQuery->fetchColumn();
    }

   /**
    * Escreve no log e retorna a execeção
    *
    * @param  string $msg
    * @param  string $sql
    *
    * @return string
    */
    private function exceptionLog($msg , $sql = "")
    {
        $exception  = 'Exceção encontrada. <br />';
        $exception .= $msg;
        $exception .= ". Favor verificar o erro no arquivo de log.";

        if(!empty($sql)) {
            $exception .= "\r\nRaw SQL : "  . $sql;
        }

        return $exception;
    }

    public function getDatabaseType()
    {
        return 'mysql';
    }

    public function getCrudClass()
    {
        return 'lib\Db\CrudMysql';
    }

    public function getConnection()
    {
        return $this->pdo;
    }
}
