<?php
/**
 * Script para criação do banco de dados e coleções da aplicação.
 *
 **/

namespace lib\Maiden;

use lib\Log\DateTimeFileWriter as Logger;
use \PDO as PDO;

/**
 * Criar banco de dados e coleções
 *
 */
class CriarBancoDeDadosMongo {

    /**
     * Log
     *
     * @var \lib\Log\DateTimeFileWriter
     */
    protected $logger;

    /**
     * String de conexão ao banco de dados 
     *
     * @var string
     */
    protected $dsn;

    /**
     * Nome usuário para autenticar
     *
     * @var string
     */
    protected $username;

    /**
     * Senha para autenticar
     *
     * @var string
     */
    protected $password;

    public function __construct($settings)
    {
        $logWriter       = new Logger();
        $this->logger    = new \Slim\Log($logWriter);
        $this->settings  = $settings; 
    }

    /**
     * Criar o banco de dados e documentos
     *
     * @return null
     */
    public function criar()
    {
        /* 
         * Conecta ao MongoDb
         *
         */
        $dsn = false;

        if (isset($this->settings['dbmongo']['username'])) {
            $dsn = 'mongodb://'.$this->settings['dbmongo']['username'].':'.$this->settings['dbmongo']['password'].'@'.$this->settings['dbmongo']['host'];
        }

        try {
            $mongo = ($dsn) ? (new \MongoClient($dsn)) : (new \MongoClient());
        } catch (\MongoConnectionException $e) {
            $this->logger->error('Erro ao conectar ao MongoDb: ' . $e->getMessage());
            die('Erro ao conectar ao MongoDb: ' . $e->getMessage()).'\n\n';
        }

        /*
         * Verifica se o banco de dados já existe
         *
         */
        $database = $this->settings['dbmongo']['database'];

        $execReturn = $mongo->selectDB('local')->execute('db.getMongo().getDBNames()');
        $dbListagem = $execReturn['retval'];

        $dbExiste = (in_array($database, $dbListagem));

        /* 
         * Se o BD não existir
         *
         */
        if (!$dbExiste) {
            /* 
             * Cria o banco de dados 
             *
             */
            $this->db = $mongo->$database;

            echo "Criando o banco de dados ...\n\n";
            $lista = $this->db->listCollections();
            foreach ($lista as $collection) {
                echo "$collection </br>";
            }
            echo "Banco de dados $database criado.\n\n";
            $this->logger->info("Banco de dados $database criado.");

            /* 
             * Seleciona o banco de dados 
             *
             */
            echo "Banco de dados $database selecionado.\n\n";

            /* 
             * Cria as coleções de documentos
             *
             */
            echo "Criando coleções ...\n\n";

            $rotas = $this->db->selectCollection("tbl_rota");
            $rotas->insert(array('nome' => '/admin', 'status' => 1));
            $rotas->insert(array('nome' => '/mongo', 'status' => 1));
            $rotas->insert(array('nome' => '/cliente', 'status' => 1));
            $rotas->insert(array('nome' => '/teste', 'status' => 1));

            $rota1 = $rotas->findOne(array('nome' => '/admin'));
            $rota2 = $rotas->findOne(array('nome' => '/cliente'));

            $rotas->ensureIndex(array("usuarios" => 1));

            $clientes = $this->db->selectCollection("tbl_cliente");
            $clientes->insert(array('nome' => 'João', 'sobrenome' =>'Silva', 'sexo'=>'M', 'idade'=>19, 'status' => 1));
            $clientes->insert(array('nome' => 'Nikola', 'sobrenome' =>'Tesla', 'sexo'=>'M', 'idade'=>32, 'status' => 1));
            $clientes->insert(array('nome' => 'Maria', 'sobrenome' =>'Souza', 'sexo'=>'F', 'idade'=>21, 'status' => 1));
            $clientes->insert(array('nome' => 'Ana', 'sobrenome' =>'Costa', 'sexo'=>'F', 'idade'=>19, 'status' => 1));
            $clientes->insert(array('nome' => 'Pablo', 'sobrenome' =>'Picasso', 'sexo'=>'M', 'idade'=>50, 'status' => 1));

            $arquivos = $this->db->selectCollection("tbl_arquivo"); 
            $arquivos->insert(array('nome' => 'f40ffdfddc21b633502d5e272a5510237bd26faa.gif', 'checksum' => 'f51776f2ba5892d4bc9e1694d9cdd32226f5c888', 'modificado'=>new \MongoDate(), 'tamanho'=>305945, 'extensao'=>'gif', 'tipo'=>'teste', 'nomeOriginal'=>'teste.gif', 'status' => 1));

            $usuarios = $this->db->selectCollection("tbl_usuario"); 
            $usuarios->insert(array('email' => 'user@example.com', 'emailCanonical' => 'user@example.com', 'hashSenha' => '$2a$08$92112bbfc198068d81c0fuc.BMKjMsrIQZj8csXeC.FEmlxb1SqGy', 'grupos'=>array($rota1['_id'], $rota2['_id']), 'ultimoLogin'=>new \MongoDate(), 'nome'=>'Teste', 'status' => 1));
            /*"grupos": [
                ObjectId("4e54ed9f48dc5922c0094a42"),
                ObjectId("4e54ed9f48dc5922c0094a41")
              ]*/

            $usuario1 = $usuarios->findOne(array('email' => 'user@example.com'));

            $usuarios->ensureIndex(array("rotas" => 1));

            $dados = array('$set' => array("usuarios" => array($usuario1['_id'])));
            $rotas->update(array('nome'=>'/admin'), $dados);
            $rotas->update(array('nome'=>'/cliente'), $dados);

            $this->logger->info("Coleções criadas.");
            echo("Coleções criadas.");
        } else {
            echo "O banco de dados já existe, se deseja recriar as coleções, favor excluir o banco de dados e executar novamente este comando.";
            $this->logger->info("Banco de dados $database já existe, não foram criadas as coleções.");
        }
    }
}
