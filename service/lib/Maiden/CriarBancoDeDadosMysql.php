<?php
/**
 * Script para criação do banco de dados e tabelas da aplicação.
 * As tabelas serão criadas a partir do arquivo tabelas.sql
 * dentro do diretório data/
 *
 **/

namespace lib\Maiden;

use lib\Log\DateTimeFileWriter as Logger;
use \PDO as PDO;

/**
 * Criar banco de dados e tabelas
 *
 */
class CriarBancoDeDadosMysql {

    /**
     * Log
     *
     * @var \lib\Log\DateTimeFileWriter
     */
    protected $logger;

    /**
     * String PDO de conexão ao banco de dados 
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
        $this->dsn       = "mysql:host=".$settings['dbmysql']['host'];
        $this->usuario   = $settings['dbmysql']['username'];
        $this->senha     = $settings['dbmysql']['password'];
        $this->nomeBanco = $settings['dbmysql']['database'];
    }

    /**
     * Executa os comandos SQL
     *
     * @param PDO    $pdo Objeto PDO
     * @param string $sql SQL para executar
     */
    protected function executa($pdo, $sql)
    {
        try {
            return $pdo->exec($sql);
        } catch (PDOException $e) {
            $this->logger->error('Erro: ' . $e->getMessage());
            die('Erro: ' . $e->getMessage().'\n\n');
        }
    }

    /**
     * Criar o banco de dados e tabelas
     *
     * @return null
     */
    public function criar()
    {
        /* 
         * Conecta ao MySQL
         *
         */
        try {
            $pdo = new PDO($this->dsn, $this->usuario, $this->senha);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            $this->logger->error('Erro ao conectar no ao MySQL: ' . $e->getMessage());
            die('Erro ao conectar no ao MySQL: ' . $e->getMessage()).'\n\n';
        }

        /* 
         * Verifica se o banco de dados já existe
         *
         */
        $sql = sprintf("SHOW DATABASES LIKE '%s'", $this->nomeBanco);
        $res = $pdo->query($sql);
        $bd  = $res->fetchColumn(0);

        /* 
         * Se o BD não existir
         *
         */
        if (!$bd) {
            /* 
             * Cria o banco de dados 
             *
             */
            echo "Criando o banco de dados ...\n\n";
            $sql = sprintf('CREATE DATABASE %s DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci', $this->nomeBanco);
            $this->executa($pdo, $sql);
            echo "Banco de dados $this->nomeBanco criado.\n\n";
            $this->logger->info("Banco de dados $this->nomeBanco criado.");

            /* 
             * Seleciona o banco de dados 
             *
             */
            $sql = sprintf('USE %s', $this->nomeBanco);
            $this->executa($pdo, $sql);
            echo "Banco de dados $this->nomeBanco selecionado.\n\n";

            /* 
             * Lê o arquivo tabelas.sql e cria (e popula) as tabelas 
             *
             */
            echo "Criando tabelas ...\n\n";

            $query_file = __DIR__.'/../../data/tabelas.sql';

            $fp    = fopen($query_file, 'r');
            $sql   = fread($fp, filesize($query_file));
            fclose($fp); 

            $this->executa($pdo, $sql);
            $this->logger->info("Tabelas criadas.");
            echo("Tabelas criadas.");
        } else {
            echo "O banco de dados já existe, se deseja recriar as tabelas, favor excluir o banco de dados e executar novamente este comando.";
            $this->logger->info("Banco de dados $this->nomeBanco já existe, não foram criadas as tabelas.");
        }

        $pdo = null;
    }
}
