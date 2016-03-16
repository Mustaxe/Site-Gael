<?php
namespace lib\Maiden;

use lib\Log\DateTimeFileWriter as Logger;

/*
 * Tarefas que podem ser executadas através da linha de comando.
 * Os métodos definidos como protected não serão listados.
 * 
 * Exemplo de uso (executar no diretório raiz do projeto):
 *  $ php app/inkuba instala
 *
 * Os parâmetros do método serão solicitados, caso existirem
 *
 */
class MaidenTasks extends MaidenDefault {

    protected $configuracoesSlim = null;

    protected $propriedades;

    protected $logger;

    protected function init()
    {
        define('ROOT', dirname(__DIR__).'/../');

        $this->propriedades      = $this->loadJson("properties.json");
        $this->configuracoesSlim = $this->buscaConfiguracoes();

        $logWriter = new Logger();
        $this->logger = new \Slim\Log($logWriter);
    }

    /**
     * Busca configurações da aplicação
     */
    protected function buscaConfiguracoes()
    {
        $configFiles = sprintf(
            '%s/app/config/*{slim}.php', 
            ROOT
        );

        $configSlim = array();
        foreach(glob($configFiles,GLOB_BRACE) as $cfg) {
            $var = require_once($cfg);
            $configSlim = array_merge($configSlim, $var);
        }

        return $configSlim;
    }


    /**
     * Deploy de um projeto para produção
     */
    public function deploy($nomeAmbiente, $versao)
    {
        // todo
        $this->geraDocs();
    }

    /**
     * Primeiro deploy de um projeto para produção
     * - criar diretório do projeto, clone do repositório, ...
     *
     */
    public function primeiroDeploy($nomeAmbiente, $versao)
    {
        // todo

        $this->configuraDiretorios($nomeAmbiente);
        $this->getAmbiente($nomeAmbiente);
        $this->geraDocs();

        $this->logger->info("Deploy completo");
    }

    /**
     * Instala o projeto no ambiente desenvolvimento
     */
    public function instala()
    {
        $env = 'dev';
        $configDev = sprintf(
            '%s/app/config/*{%s}.php', 
            ROOT,
            $env
        );
        $tes = glob($configDev,GLOB_BRACE);

        require_once($tes[0]);

        $this->logger->info("Instalando ambiente 'dev' para {$this->propriedades->application->name}");

        //$this->configuraDiretorios($nomeAmbiente);

        $this->criarBancoDeDados($settings);

        $this->logger->info('Instalação completa');
    }

    /**
     * Criar um nome de arquivo temporário
     */
    protected function getNomeArquivoTemporario()
    {
        return sys_get_temp_dir() . '/InkubaFP' .  md5(uniqid());
    }

    /**
     * Gerar documentação da API
     */
    public function geraDocs()
    {
        $this->logger->info('Gera documentação da API');
        $this->exec("php vendor/zircote/swagger-php/swagger.phar ./app/controller -o ./doc/api");
    }

    /**
     * Gerar controladores e templates para interface administrativa
     * conforme definições no arquivo app/config/admin.php
     */
    public function geraAdmin()
    {
        $cfg = glob(sprintf('%s/app/config/admin.php', ROOT), GLOB_BRACE);
        $configAdmin = require_once($cfg[0]);
        $configApp   = $this->configuracoesSlim;

        $this->logger->info('Gera interface administrativa');
        
        $classe       = '\lib\Maiden\geraAdmin';
        $admin = new $classe($configAdmin, $configApp);

        $admin->gerar();
    }

    /**
     * Criar BD e tabelas
     * Cria interface administrativa
     * Gera documentação da API
     */
    public function geraTudo()
    {
        $this->instala();
        $this->geraAdmin();
        $this->geraDocs();
    }

    /**
     * Recarrega o Apache no ambiente atual
     */
    public function recarregaApache()
    {
        $this->logger->info('Recarrega o servidor Apache');
        $this->exec("sudo invoke-rc.d apache2 reload");
    }

    /**
     * Reinicia o Apache no ambiente atual
     */
    public function reiniciaApache()
    {
        $this->logger->info("Reinicia o servidor Apache");
        $this->exec("sudo /etc/init.d/apache2 restart");
    }

    /**
     * Criar banco de dados e tabelas
     */
    protected function criarBancoDeDados($settings)
    {
        $this->logger->info("Criando banco de dados e tabelas");

        $classe       = '\lib\Maiden\CriarBancoDeDados'.ucfirst($this->configuracoesSlim['database']);
        $bancoDeDados = new $classe($settings);

        $bancoDeDados->criar();
    } 

    /**
     * Dump do banco de dados
     */
    public function getDumpBD($nomeAmbienteOrigem)
    {
        // todo
        $tempfile = $this->getNomeArquivoTemporario();

        $this->logger->info("Dump dos dados de '$nomeAmbienteOrigem' para '$tempfile'");
        // $this->exec("");

        return $tempfile;
    }

    /**
     * Retorna as propriedades para o ambiente informado
     */
    protected function getAmbiente($nomeAmbiente)
    {
        if (!isset($this->propriedades->{$nomeAmbiente})) {
            throw new \Exception("O ambiente '$nomeAmbiente' não existe");
        }

        return $this->propriedades->{$nomeAmbiente};
    }

    /**
     * Configura permissões para os diretórios 
     */
    public function configuraDiretorios($nomeAmbiente)
    {
        $this->logger->info("configura diretórios");
        $this->exec("sudo -u www-data sh -c 'umask 002; mkdir -p {$this->propriedades->{$nomeAmbiente}->logPath}'");
    }
}
