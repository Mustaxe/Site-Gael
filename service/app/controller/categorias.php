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

    $query = "
    	SELECT 
    		D.id, D.nome, D.tipo
		FROM 
			tbl_cases C
		INNER JOIN
			tbl_categorias D ON FIND_IN_SET(D.id, C.categorias)
		WHERE
			D.ativo = 1 AND D.status = 1
		GROUP BY 
			D.id
		ORDER BY 
			D.nome";	

	$R = $categorias->Query($query);	

    echo json_encode($R);
});


$app->get('/categorias/:ativo/:lang', function ($ativo, $lang) use ($app, $categorias, $projetos) {	

	/**
	*
	* Tipo 'E' = Empresa
	* - Busca todas as empresas independentimento do idioma 
	*
	*
	*/

	$query = "
		SELECT 
			D.id, D.nome, D.tipo, D.lang
		FROM
			tbl_cases C
		INNER JOIN
			tbl_categorias D ON FIND_IN_SET(D.id, C.categorias)
		WHERE 
			D.ativo = 1 AND D.status = 1 AND (D.lang = '" . $lang . "' OR (D.lang = 'pt' AND D.tipo = 'E')) 
		GROUP BY 
			D.id
		ORDER BY 
			D.nome"; 	

	$R = $categorias->Query($query);
    echo json_encode($R);
});
