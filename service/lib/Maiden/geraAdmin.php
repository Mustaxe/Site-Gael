<?php

/**
 * Gera controladores e templates administrativos
 * padrão conforme configurações definidas no
 * arquivo app/config/admin.php
 *
 **/

namespace lib\Maiden;

use lib\Log\DateTimeFileWriter as Logger;
use \PDO as PDO;
use ICanBoogie\Inflector;

/**
 * Gerar interface administrativa
 *
 */
class geraAdmin {

    /**
     * Log
     *
     * @var \lib\Log\DateTimeFileWriter
     */
    protected $logger;

    public function __construct($configAdmin, $configApp)
    {
        $logWriter       = new Logger();
        $this->logger    = new \Slim\Log($logWriter);
        $this->config    = $configAdmin;
        $this->configApp = $configApp;
    }

    /**
     * Gera os controladores e templates
     *
     * @return null
     */
    public function gerar()
    {
        $dados = $this->config;
        // array('classname' => 'teste', 'methods' => array(array('name'=>'gera', 'description'=>'geracao teste', 'parameters' =>array('a', 'b'))));
        $closuresAutenticacao = array();
        $tipoAutenticacao     = $this->configApp['slim']['auth.type'];
        $inflector            = Inflector::get();

        $loader = new \Twig_Loader_Filesystem('templates');
        $twig   = new \Twig_Environment($loader, array());

        foreach ($dados['controladores'] as $controlador) {
            $nomeControlador = 'admin-'.$controlador['nome'].'.php';

            $modelCaminhos        = array();
            $modelClasses         = array();
            $closures             = array();

            // diretório para os templates do controlador
            $caminhoTemplatesRaiz = 'templates/admin/';
            $caminhoTemplates     = $caminhoTemplatesRaiz.$controlador['nome'];

            if (!is_dir($caminhoTemplates)) {
                mkdir($caminhoTemplates);
            }

            foreach ($controlador['models'] as $model) {
                $modelCaminhos[] = 'use app\model\\'.ucfirst($model).';';
                $modelClasses[]  = '$'.strtolower($model).' = new '.ucfirst($model).'(array(), $app->db);';
            }

            // renderiza os dados das closures
            foreach ($controlador['closures'] as $closure) {
                $nickname       = (isset($closure['nickname'])) ? isset($closure['nickname']) : $this->gerarNicknameDoc($closure['metodo'], $controlador['model']);

                $temPost = false;

                $acao = isset($closure['acao']) ? ($closure['acao']) : ('');

                $metodoDoc = is_array($closure['metodo']) ? ($closure['metodo'][0]) : ($closure['metodo']);

                if (is_array($closure['metodo'])) {
                    $temPost = in_array('post', $closure['metodo']);
                }

                // verifica se tem response messages para a geração da doc., senão adiciona default:
                if (!isset($closure['response_messages'])) {
                    $closure['response_messages'] = array(
                            array('code'    => '500',
                                  'message' => 'Ocorreu um problema',
                              )
                        );
                }

                $v = explode('/', $closure['url']);
                $nomeClosure = $v[2];

                if ($closure['metodo'] == 'get' && $acao == 'todos') {
                    // definir o nome da rota, para usar com redirects para a listagem:
                    $closure['nome_rota'] = 'listagem_'.$controlador['nome'];
                }

                if (($closure['metodo'] == 'post' || $temPost ) && $acao == 'criar') {
                    $closure['nome_rota'] = 'adiciona_'.$controlador['nome'];
                }

                if ($closure['metodo'] == 'post' && $acao == 'editar') {
                    $closure['nome_rota'] = 'edita_'.$controlador['nome'];
                }

                if ($closure['metodo'] == 'get' && $acao == 'unico') {
                    $closure['nome_rota'] = 'busca_'.$controlador['nome'];
                }

                if ($closure['url'] == '/admin/esqueci-senha') {
                    $closure['nome_rota'] = 'esqueci_senha';
                }

                $closures[] = $twig->render(
                    'gerador/closure.php.twig',
                    array(
                        'closure'          => $closure,
                        'urlDoc'           => $this->converteParamUrl($closure['url']),
                        'nickname'         => $nickname,
                        'model'            => $controlador['model'],
                        'projeto'          => $dados['projeto'],
                        'tipoAutenticacao' => $tipoAutenticacao,
                        'inflector'        => $inflector,
                        'nomeControlador'  => $controlador['nome'],
                        'metodoDoc'        => $metodoDoc,
                    )
                    /*
                        array(
                            'metodo'      => trim($closure['name']),
                            'description' => wordwrap($closure['description'], 80, "\n * "),
                            'params'      => $closure['parameters']
                        )
                    */
                );

                // gerar templates

                // template para tabelas
                if ($closure['metodo'] == 'get' && $acao == 'todos') {
                    $titulo = isset($closure['template']['titulo']) ? ($closure['template']['titulo']) : ('Listagem de '.ucfirst($controlador['nome']));
                    $conteudoTemplate = $twig->render(
                        'gerador/table.php.twig',
                        array(
                            'model'       => $controlador['model'],
                            'titulo'      => $titulo,
                            'nome'        => $controlador['nome'],
                        )
                    );

                    file_put_contents($caminhoTemplates.'/listagem.html.twig', $conteudoTemplate);
                }

                // template para adição
                if (($closure['metodo'] == 'post' || $temPost ) && $acao == 'criar') {
                    $titulo = isset($closure['template']['titulo']) ? ($closure['template']['titulo']) : ('Adicionar '.ucfirst($controlador['nome']));
                    $conteudoTemplate = $twig->render(
                        'gerador/novo.php.twig',
                        array(
                            'model'       => $controlador['model'],
                            'titulo'      => $titulo,
                            'nome'        => $controlador['nome'],
                            'campos'      => $closure['params_request'],
                            'nomeClosure' => $nomeClosure,
                        )
                    );

                    file_put_contents($caminhoTemplates.'/novo.html.twig', $conteudoTemplate);
                }

                // template para edição
                if ($closure['metodo'] == 'post' && $acao == 'editar') {
                    $titulo = isset($closure['template']['titulo']) ? ($closure['template']['titulo']) : ('Editar '.ucfirst($controlador['nome']));
                    $conteudoTemplate = $twig->render(
                        'gerador/editar.php.twig',
                        array(
                            'model'       => $controlador['model'],
                            'titulo'      => $titulo,
                            'nome'        => $controlador['nome'],
                            'campos'      => $closure['params_request'],
                            'nomeClosure' => $nomeClosure,
                        )
                    ); 

                    file_put_contents($caminhoTemplates.'/editar.html.twig', $conteudoTemplate);
                }

                // template para a home (dashboard)
                if ($controlador['nome'] == 'home') {
                    $conteudoTemplate = $twig->render(
                        'gerador/home.php.twig',
                        array(
                            'model'       => $controlador['model'],
                            'titulo'      => $closure['template']['titulo'],
                            'nome'        => $controlador['nome'],
                        )
                    ); 

                    file_put_contents($caminhoTemplates.'/index.html.twig', $conteudoTemplate);
                }
            }

            // renderiza os controladores e inclui as closures
            $conteudoControladorString = $twig->render(
                'gerador/controlador.php.twig',
                array(
                    'controlador' => array(
                        'nome'            => trim($nomeControlador),
                        'basePath'        => $dados['basePath'],
                        'descricao'       => 'Operações Admin. '.ucfirst($controlador['nome']),
                        'formatoResposta' => "['application/json']",
                        'gerado'          => date(DATE_RFC822),
                        'modelCaminhos'   => $modelCaminhos,
                        'modelClasses'    => $modelClasses,
                        'closures'        => $closures,
                    )
                )
            );

            file_put_contents('./app/controller/'.$nomeControlador, $conteudoControladorString);

        }

        foreach ($dados['controlador_aut']['closures'] as $closure) {
            $nickname       = (isset($closure['nickname'])) ? isset($closure['nickname']) : $this->gerarNicknameDoc($closure['metodo'], $controlador['model']);

            $metodoDoc = is_array($closure['metodo']) ? ($closure['metodo'][0]) : ($closure['metodo']);

            if ($closure['url'] == '/admin/login') {
                $closure['nome_rota'] = 'login';
            }

            if ($closure['url'] == '/admin/logout') {
                $closure['nome_rota'] = 'logout';
            }

            $closuresAutenticacao[] = $twig->render(
                'gerador/closureAutenticacao.php.twig',
                array(
                    'closure'          => $closure,
                    'urlDoc'           => $this->converteParamUrl($closure['url']),
                    'nickname'         => $nickname,
                    'tipoAutenticacao' => $tipoAutenticacao,
                    'metodoDoc'        => $metodoDoc,
                )
                /*
                    array(
                        'metodo'      => trim($closure['name']),
                        'description' => wordwrap($closure['description'], 80, "\n * "),
                        'params'      => $closure['parameters']
                    )
                */
            );
        }

        // renderiza o controlador da autenticação
        //  'formatoResposta' => "['application/json']",
        $conteudoControladorAutString = $twig->render(
            'gerador/controladorAutenticacao.php.twig',
            array(
                'controlador' => array(
                    'nome'            => 'autenticacao',
                    'basePath'        => $dados['basePath'],
                    'descricao'       => 'Operações de Autenticação',
                    'formatoResposta' => "['text/html']",
                    'gerado'          => date(DATE_RFC822),
                    'closures'        => $closuresAutenticacao,
                )
            )
        );

        file_put_contents('./app/controller/autenticacao.php', $conteudoControladorAutString);

        // template de login
        $conteudoTemplateLogin = $twig->render(
            'gerador/login.php.twig',
            array(
                'titulo'      => 'Login',
            )
        );

        file_put_contents($caminhoTemplatesRaiz.'base/login.html.twig', $conteudoTemplateLogin);

        // templates globais
        $conteudoMain = $twig->render(
            'gerador/base/main.php.twig',
            array(
                'projeto'    => $dados['projeto'],
            )
        );

        file_put_contents($caminhoTemplatesRaiz.'base/main.html.twig', $conteudoMain);

        /*$conteudoSidebar = $twig->render(
            'gerador/base/sidebar.php.twig',
            array(
            )
        );

        file_put_contents($caminhoTemplatesRaiz.'base/sidebar.html.twig', $conteudoSidebar);*/

        // templates:

        // render method data
        /*foreach($result['methods'] as $method) {
            $name     = trim($method['name']);
            $template = $twig->render(
                'generator/template.php.twig',
                array(
                    'method' => array(
                        'name'        => $name,
                        'description' => wordwrap($method['description'], 80, "\n * "),
                        'params'      => $method['parameters']
                    )
                )
            );

            file_put_contents('templates/'.$name . '.html.twig', $template);
        }*/

        echo 'Arquivos da interface administrativa gerados com sucesso.';
    }

    private function converteParamUrl($url)
    {
        $parts = explode('/', $url);

        while (list($k, $part) = each($parts)) {
            if (strpos($part, ':') !== false){
                $url  = str_replace($part, strtr($part, ':', '{') . '}', $url);
            }
        }

        return $url;
    }

    private function gerarNicknameDoc($metodo, $model)
    {
        if(is_array($metodo)) $metodo = $metodo[0];
        $prefixo = array('post'=>'cadastrar', 'delete'=>'remover', 'put'=>'put', 'patch'=>'patch', 'get'=>'listagem');
        return $prefixo[$metodo] . ucfirst($model);
    }
}