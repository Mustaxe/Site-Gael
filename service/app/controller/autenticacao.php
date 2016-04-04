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


 