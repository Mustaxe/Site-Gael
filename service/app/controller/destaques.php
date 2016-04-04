<?php
/**
 * @package
 * @category
 * @subpackage
 *
 * @SWG\Resource(
 *   apiVersion="1.0.0",
 *   swaggerVersion="1.2",
 *   basePath="http://localhost/service",
 *   resourcePath="/destaques",
 *   description="Operações Cliente",
 *   produces="['application/json']"
 * )
 */

use app\model\dao\DestaquesDao;
use app\model\Destaques;
use app\model\Projetos;

$destaques = new Destaques(array(), $app->db);
$projetos  = new Projetos(array(), $app->db);

/**
 *
 * @SWG\Api(
 *   path="/destaques/{ativo}",
 *   description="Listagem de clientes",
 *   @SWG\Operation(method="GET", summary="Listagem de clientes", type="string", nickname="cliente",
 *      @SWG\Parameter(
 *          name="ativo",
 *          description="ativo",
 *          paramType="path",
 *          required=true,
 *          type="string"
 *      )
 *   )
 * )
 */
$app->get('/destaques/:ativo', function ($ativo) use ($app, $destaques, $projetos) {
	$destaques = new DestaquesDao($app);
    $res   = $destaques->getDestaquesComArquivos($ativo);
	
    echo json_encode($res);
});

$app->get('/destaques/:ativo/:lang', function ($ativo, $lang) use ($app, $destaques, $projetos) {
	$destaques = new DestaquesDao($app);
    $res   = $destaques->getDestaquesComArquivos($ativo, $lang);

    echo json_encode($res);
});

