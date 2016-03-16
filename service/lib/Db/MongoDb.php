<?php

namespace lib\Db;

use lib\Log\DateTimeFileWriter;
use \MongoClient;

class MongoDb
{
    /**
     * @var array  Configurações do Banco de Dados
     */
    private $config;

    /**
     * @var object Banco de dados do MongoDb selecionado
     */
    private $db;

    /**
     * @var bool  Se está conectado ao banco de dados
     */
    private $bConnected;

    /**
     * @var object Object Log das exceções
     */
    private $log;

    public function __construct(array $config, $log)
    {
        $this->log = $log;
        $this->connect($config);
    }

    /**
     *   Conecta ao banco de dados
     */
    private function connect(array $config)
    {
        $dsn = false;

        if (isset($config['dbmongo']['username'])) {
            $dsn = 'mongodb://'.$config['dbmongo']['username'].':'.$config['dbmongo']['password'].'@'.$config['dbmongo']['host'];
        }

        try{
            if ($dsn) {
                $mongo = new MongoClient($dsn);
            }else{
                $mongo = new MongoClient();
            }
            $this->db = $mongo->$config['dbmongo']['database'];

            $this->bConnected = true;
        }catch (\MongoConnectionException $e) {
            //$this->log->write('Erro de conexão ao banco de dados MongoDb em ' . $e->getFile() . ' na linha ' . $e->getLine() . ': ' . $e->getMessage());
            echo json_encode(array('cod'=>500, 'res'=>'Problema ao conectar ao banco de dados. '.$e->getMessage()));

            exit;
        }
    }

    public function database()
    {
        return $this->db;
    }
    
    public function getDatabaseType()
    {
        return 'mongo';
    }

    public function getCrudClass()
    {
        return 'lib\Db\CrudMongo';
    }
}
