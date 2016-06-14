<?php
/**
 * @package
 * @since  Tue, 29 Apr 14 16:45:02 -0300
 * @category
 * @subpackage
 *
 * @SWG\Resource(
 *   apiVersion="1.0.0",
 *   swaggerVersion="1.2",
 *   basePath="http://localhost/admin",
 *   resourcePath="/admin",
 *   description="Operações Admin. Cases",
 *   produces="['application/json']"
 * )
 */

use app\model\Clientes;
use app\model\Pastas;
use app\model\Arquivos;


$clientes = new Clientes(array(), $app->db);
$pastas = new Pastas(array(), $app->db);
$arquivos = new Arquivos(array(), $app->db);


/**
*
* TODO_CONFIG: Config de path para upload
*
*/
$_URL_UPLOAD = array(
    'localhost' => '/git/site_gael/Site-Gael/service/web/uploads/',
    'localhost:8080' => '/git/site_gael/Site-Gael/service/web/uploads/',
    'homologacao.gael.ag' => '/service/web/uploads/',
    'www.gael.ag' => '/service/web/uploads/',
    'gael.ag' => '/service/web/uploads/'
);
$URL_UPLOAD = $_URL_UPLOAD[$_SERVER['HTTP_HOST']];


/**
*
*
* Listagem de pastas
*
*/
$app->get('/cliente/pastas', function () use ($app, $clientes, $pastas, $arquivos, $URL_UPLOAD) {
    

    /**
    *
    * IMPORTANT: Verifica se o usuario está logado 
    *
    */
    if( !isset($_SESSION['X_CLIENTE_SESSION_KEY']) || !($_SESSION['X_CLIENTE_SESSION_KEY'] == md5($_SERVER["REMOTE_ADDR"])) )
    {
        $app->response->headers->set('Content-Type', 'text/html;charset=utf-8');
        $app->render('cliente/erros/404.html.twig');
        exit;
    }




    $usuario = array();
    $usuario['id'] = $_SESSION['X_CLIENTE_ID'];
    $usuario['nome'] = $_SESSION['X_CLIENTE_NOME'];
    $usuario['empresa'] = $_SESSION['X_CLIENTE_EMPRESA'];
    $usuario['logo'] = $_SESSION['X_CLIENTE_LOGO'];
    $usuario['url'] = $_SESSION['X_CLIENTE_URL'];
    $usuario['telefones'] = $_SESSION['X_CLIENTE_TELEFONES'];
    $usuario['email'] = $_SESSION['X_CLIENTE_EMAIL'];
    $usuario['usuario'] = $_SESSION['X_CLIENTE_USUARIO'];


    /**
    *
    * Obtem as pastas e arquivos referente ao cliente
    * 
    *
    */   

    $_arquivos = array();


    $q = "SELECT * FROM tbl_pastas WHERE id_cliente = " . $usuario['id'] . ' AND ativo = 1 AND status = 1 ORDER BY criacao DESC';
    $resPastas = $pastas->Query($q);
    if($resPastas->cod == 200)
    {
        /**
        *
        * Obtem os arquivos de cada pasta
        *
        */
        foreach($resPastas->res as $pasta)
        {

            $q = "SELECT * FROM tbl_arquivos WHERE id_pasta = " . $pasta['id'] . ' AND ativo = 1 AND status = 1 ORDER BY criacao DESC';

            $resArquivo = $arquivos->Query($q);
            if($resArquivo->cod == 200)
            {
                foreach($resArquivo->res as $item)
                {
                    /**
                    * IMPORTANT: Formata url
                    */
                    $item['url'] = '/web/uploads/clientes/' . $item['extensao'] . '/' . $item['nome'];
                    $_arquivos[] = $item;
                }
            }
        }
    }    


    $app->response->headers->set('Content-Type', 'text/html;charset=utf-8');
    $app->render('cliente/home/index.html.twig', array('usuario' => $usuario, 'arquivos' => $_arquivos));
})->name('cliente_pastas');



$app->post('/cliente/arquivo', function () use ($app, $clientes, $pastas, $arquivos, $URL_UPLOAD) {


    /**
    *
    * IMPORTANT: Verifica se o usuario está logado 
    *
    */
    if( !isset($_SESSION['X_CLIENTE_SESSION_KEY']) || !($_SESSION['X_CLIENTE_SESSION_KEY'] == md5($_SERVER["REMOTE_ADDR"])) )
    {
        $app->response->headers->set('Content-Type', 'text/html;charset=utf-8');
        $app->render('cliente/erros/404.html.twig');
        exit;
    }


    $usuario = array();
    $usuario['id'] = $_SESSION['X_CLIENTE_ID'];
    $usuario['nome'] = $_SESSION['X_CLIENTE_NOME'];
    $usuario['empresa'] = $_SESSION['X_CLIENTE_EMPRESA'];
    $usuario['logo'] = $_SESSION['X_CLIENTE_LOGO'];
    $usuario['url'] = $_SESSION['X_CLIENTE_URL'];
    $usuario['telefones'] = $_SESSION['X_CLIENTE_TELEFONES'];
    $usuario['email'] = $_SESSION['X_CLIENTE_EMAIL'];
    $usuario['usuario'] = $_SESSION['X_CLIENTE_USUARIO'];


    /**
    *
    * Adiciona o arquivo
    * 
    * - Necessário renomear o arquivo para não haver problemas de acentuações, o novo nome será baseado no time()
    * - Importante manter o nome original do arquivo salvo na base para podermos mostrar na listagem
    *
    */


    $message = "";    
    if(isset($_FILES['arquivo']) && !empty($_FILES['arquivo']['name']))
    {
        /**
        * Nome original
        */
        $nomeOriginal = $_FILES['arquivo']['name'];

        /**
        * Extensão
        */
        $extensao = end(explode('.', $nomeOriginal));

        /**
        * Novo nome
        */
        $novoNome = time() . '.' . $extensao;


        /**
        * Verifica se o diretorio já existe, se não existir então criamos       
        */
        $diretorio = $_SERVER['DOCUMENT_ROOT'] . $URL_UPLOAD . 'clientes/' . $extensao;
        if(!file_exists($diretorio))
        {
            mkdir($diretorio);
        }

        /**
        * Copia a imagem para a pasta
        */
        $urlToCopy = $diretorio . '/' . $novoNome;
        if(copy($_FILES['arquivo']['tmp_name'], $urlToCopy))
        {
            /**
            * Obtemos o cliente
            */
            $resCliente = $clientes->findById($usuario['id']);
            if($resCliente->cod != 200)
            {
                echo '{"status": false, "message": "Cliente não encontrado"}';
                $app->response->headers->set('Content-Type', 'application/json;charset=utf-8');
                return;
            }

            $cliente = $resCliente->res;


            /**
            *
            * Obtemos a pasta padrão do cliente
            * - Futuramento será evoluido para inserir um arquivo em uma determinada pasta, que será passada como parametro
            *
            */
            $q = "SELECT * FROM tbl_pastas WHERE id_cliente = " . $usuario['id'] . " ORDER BY id ASC LIMIT 1";
            $resPasta = $pastas->Query($q);
            if($resPasta->cod != 200)
            {
                echo '{"status": false, "message": "Pasta de trabalho padrão não foi encontrada"}';
                $app->response->headers->set('Content-Type', 'application/json;charset=utf-8');
                return;
            }

            $pasta = $resPasta->res[0];     



            /**
            *
            * Salva referencia no Banco de Dados
            *
            */          
            $arquivos->id_pasta = $pasta['id'];
            $arquivos->nome = $novoNome;
            $arquivos->nomeOriginal = $nomeOriginal;
            $arquivos->extensao = $extensao;            
            $arquivos->create();

            echo '{"status" : true, "message": "Arquivo inserido com sucesso"}';
        }
        else
        {
            echo '{"status" : false, "message": "Falha ao copiar arquivo"}';
        }
    }
    else
    {
        echo '{"status" : false, "message": "Arquivo inválido"}';
    }

    $app->response->headers->set('Content-Type', 'application/json;charset=utf-8');

})->name('cliente_arquivo');