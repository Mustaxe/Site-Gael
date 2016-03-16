<?php
namespace lib\Maiden;

use lib\Log\DateTimeFileWriter as Logger;
/**
 * Base class that all custom Maiden.php should extend.
 *
 * @author Paul Serby <paul.serby@clock.co.uk>
 * @copyright Clock Limited 2011
 * @license http://opensource.org/licenses/bsd-license.php New BSD License
 */
class MaidenDefault {

    /**
     * @var \lib\Log\DateTimeFileWriter
     */
    protected $logger;

    public function __construct()
    {
        $logWriter = new Logger();
        $this->logger = new \Slim\Log($logWriter);
        $this->init();
    }

    /**
     * Sobrescrever este método, ao invés do construtor, para adicionar configurações gerais
     */
    protected function init()
    {
    }

    protected function loadJson($filename)
    {
        $this->logger->debug("Carregando JSON de '$filename'");

        $file = __DIR__ .'/'. $filename;

        if (!file_exists($file)) {
            throw new \Exception("Erro ao carregar '{$filename}' - Arquivo não existe");
        }

        $return = json_decode(file_get_contents($file));

        switch (json_last_error()) {
            case JSON_ERROR_DEPTH:
                throw new \Exception("Parse de '{$filename}' - Maximum stack depth exceeded");
            break;
            case JSON_ERROR_CTRL_CHAR:
                throw new \Exception("Parse de '{$filename}' - Encontrado um caracter de controle inesperado");
            break;
            case JSON_ERROR_SYNTAX:
                throw new \Exception("Parse de '{$filename}' - Erro de sintaxe JSON");
            break;
            case JSON_ERROR_NONE:
                break;
        }

        return $return;
    }

    protected function exec($command, $failOnError = true, $returnOutput = false)
    {
        $this->logger->info("Executando: $command");
        $out = "";
        if ($returnOutput) {
            exec($command, $out, $return);
            $out = implode("\n", $out);
        } else {
            passthru($command, $return);
        }
        if ($failOnError && ($return !== 0)) {
            throw new \Exception("Problema na execução - código de retorno: $return");
        }
        return $out;
    }

    protected function fail($message, $exitCode = 1)
    {
        $this->logger->error("Falha: " . $message);
        exit($exitCode);
    }
}
