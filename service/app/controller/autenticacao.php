<?php
/**
 * @package
 * @since  Mon, 28 Apr 14 18:51:46 -0300
 * @category
 * @subpackage
 *
 * @SWG\Resource(
 *   apiVersion="1.0.0",
 *   swaggerVersion="1.2",
 *   basePath="http://localhost/admin",
 *   resourcePath="/admin",
 *   description="Operações de Autenticação",
 *   produces="['text/html']"
 * )
 */

use lib\Authentication\Authentication;
use app\model\Clientes;


$clientes = new Clientes(array(), $app->db);



/**
 *
 * @SWG\Api(
 *   path="/admin/login",
 *   description="Login",
 *   @SWG\Operation(method="POST", summary="Login", type="void", nickname="loginForm",
 *      @SWG\Parameters(
 *          @SWG\Parameter(
 *              name="usuario",
 *              description="Usuário",
 *              paramType="form",
 *              required=true,
 *              type="string"
 *          ), 
 *          @SWG\Parameter(
 *              name="senha",
 *              description="Senha",
 *              paramType="form",
 *              required=true,
 *              type="string"
 *          ) 
 *      ),
 *      @SWG\ResponseMessage(code=500, message="Problema ao efetuar login") 
 *   )
 * )
 */
$app->map('/admin/login', function () use ($app) {
    $params  = $app->request;
    $error = '';

    if ($app->request->isPost()) {
        $usuario = $params->post('usuario');
        $senha = $params->post('senha');

        $res = $app->auth->login($usuario, $senha);

        if ($res) {
            //$app->redirect($app->urlFor('home'));
			$app->redirect($app->urlFor('listagem_cases'));
        }

        $error = 'Não foi possível efetuar o login.';
    }

    $app->render('admin/base/login.html.twig', array('url' =>'login', 'error'=>$error));

})->via("POST", "GET")->name('login');


/**
 *
 * @SWG\Api(
 *   path="/admin/logout",
 *   description="Logout",
 *   @SWG\Operation(method="POST", summary="Logout", type="void", nickname="logoutForm",
 *      @SWG\ResponseMessage(code=500, message="Problema ao efetuar logout") 
 *   )
 * )
 */
$app->post('/admin/logout', function () use ($app){

    $app->response->headers->set('Content-Type', 'application/json;charset=utf-8');
    $res = $app->auth->logout();

    if ($res) {
        echo json_encode(array('cod' => 200));
        // $app->redirect($app->urlFor('login'));
    } else {
        echo json_encode(array('cod' => 500));
    }

    // $app->flash('error', 'Ocorreu um problema ao efetuar logout');
})->name('logout');


/**
*
*
* Login de clientes
*
*/
$app->map('/cliente/login', function () use ($app, $clientes) {

    
    $params  = $app->request;
    $error = '';

    if ($app->request->isPost())
    {
        /**
        *
        * Processo de login
        *        
        */

        $usuario = $params->post('usuario');
        $senha = $params->post('senha');

        if(!empty($usuario) && !empty($senha))
        {

            /**
            *
            * Verifica se usuario e senha são válidos e se está ativo
            *
            */
            $q = "SELECT * FROM tbl_clientes WHERE usuario = '" . $usuario . "' AND senha = '" . md5($senha) . "' AND ativo = 1 AND status = 1";

            $resCliente = $clientes->Query($q);
            
            if($resCliente->cod == 200)
            {
                /**
                *
                * IMPORTANT: Variaveis de sessão que representa o cliente logado
                *
                *
                */

                $cliente = $resCliente->res[0];

                $_SESSION['X_CLIENTE_SESSION_KEY'] = md5($_SERVER["REMOTE_ADDR"]);
                $_SESSION['X_CLIENTE_ID'] = $cliente['id'];
                $_SESSION['X_CLIENTE_NOME'] = $cliente['nome'];
                $_SESSION['X_CLIENTE_EMPRESA'] = $cliente['empresa'];
                $_SESSION['X_CLIENTE_LOGO'] = $cliente['logo'];
                $_SESSION['X_CLIENTE_URL'] = $cliente['url'];
                $_SESSION['X_CLIENTE_TELEFONES'] = $cliente['telefones'];
                $_SESSION['X_CLIENTE_EMAIL'] = $cliente['email'];
                $_SESSION['X_CLIENTE_USUARIO'] = $cliente['usuario'];


                /**
                *
                * IMPORTANT: Apaga a veriavel de SESSAO X_SLUG
                *
                */
                unset($_SESSION['X_SLUG']);
                unset($_SESSION['X_EMPRESA']);

                       

                /**
                *
                * Redireciona para a home
                *
                */            
                $app->redirect($app->urlFor('cliente_pastas'));
            }
            else
            {
                $error = 'Usuário e senha inválidos';
            }
        }
        else
        {
            $error = 'Usuário e senha inválidos';
        }

        $app->response->headers->set('Content-Type', 'text/html;charset=utf-8');
        $app->render('cliente/base/login.html.twig', array('url' => 'login', 'empresa' => $_SESSION['X_EMPRESA'], 'error' => $error));
    }
    else
    {

        /**
        *
        * IMPORTANT: Se o slug não estiver definido, então veio diretamente para essa url, vamos redirecionar para 404
        *    
        *
        */
        if(!isset($_SESSION['X_SLUG']) || !isset($_SESSION['X_EMPRESA']))
        {        
            $app->response->headers->set('Content-Type', 'text/html;charset=utf-8');
            $app->render('cliente/erros/404.html.twig');
            exit;
        }


        $app->response->headers->set('Content-Type', 'text/html;charset=utf-8');
        $app->render('cliente/base/login.html.twig', array('url' => 'login', 'empresa' => $_SESSION['X_EMPRESA'], 'error' => $error));
    }
})->via("POST", "GET")->name('cliente_login');


/**
 *
 * @SWG\Api(
 *   path="/admin/logout",
 *   description="Logout",
 *   @SWG\Operation(method="POST", summary="Logout", type="void", nickname="logoutForm",
 *      @SWG\ResponseMessage(code=500, message="Problema ao efetuar logout") 
 *   )
 * )
 */
$app->post('/cliente/logout', function () use ($app){

    


    unset($_SESSION['X_CLIENTE_SESSION_KEY']);
    unset($_SESSION['X_CLIENTE_ID']);
    unset($_SESSION['X_CLIENTE_NOME']);
    unset($_SESSION['X_CLIENTE_EMPRESA']);
    unset($_SESSION['X_CLIENTE_LOGO']);
    unset($_SESSION['X_CLIENTE_URL']);
    unset($_SESSION['X_CLIENTE_TELEFONES']);
    unset($_SESSION['X_CLIENTE_EMAIL']);
    unset($_SESSION['X_CLIENTE_USUARIO']);


    echo '{"cod": 200}';
    $app->response->headers->set('Content-Type', 'application/json;charset=utf-8');

})->name('cliente_logout');
