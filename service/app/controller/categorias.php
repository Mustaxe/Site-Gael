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
 *   resourcePath="/categorias",
 *   description="Operações Categorias",
 *   produces="['application/json']"
 * )
 */

use app\model\Categorias;
use app\model\Projetos;
use app\model\Log;

$projetos   = new Projetos(array(), $app->db);
$categorias = new Categorias(array(), $app->db);

/**
 *
 * @SWG\Api(
 *   path="/categorias/{ativo}",
 *   description="Listagem de categorias",
 *   @SWG\Operation(method="GET", summary="Listagem de categorias", type="string", nickname="categorias",
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
$app->get('/categorias/:ativo', function ($ativo) use ($app, $categorias, $projetos) {
    //$R = $categorias->find(array(), 'ativo = 1', array("id", "nome", "tipo"), array("nome"), array(), '');
	$R = $categorias->Query("SELECT D.id, D.nome, D.tipo
								FROM tbl_cases C
								INNER JOIN tbl_categorias D ON FIND_IN_SET(D.id, C.categorias)
								WHERE D.ativo = 1 AND D.status = 1
								GROUP BY D.id
								ORDER BY D.nome");
    echo json_encode($R);
});
