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
 *   description="Operações Admin. Home",
 *   produces="['application/json']"
 * )
 */

use app\model\Cases;
use app\model\Projetos;

$cases = new Cases(array(), $app->db);
$projetos = new Projetos(array(), $app->db);


/**
 *
 * @SWG\Api(
 *   path="/admin/home",
 *   description="Home",
 *   @SWG\Operation(method="GET", summary="Home", type="void", nickname="listagemCases",
 *      @SWG\ResponseMessage(code=500, message="Problema ao acessar home") 
 *   )
 * )
 */
$app->get('/admin/home', function () use ($app, $cases, $projetos){

    //$app->render('admin/cases/listagem.html.twig', array());
	$app->redirect('/service/admin/cases');
})->name('home');


