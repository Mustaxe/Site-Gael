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
 *   resourcePath="/cases",
 *   description="Operações Cases",
 *   produces="['application/json']"
 * )
 */

use app\model\dao\CasesDao;
use app\model\Cases;
use app\model\Projetos;
use app\model\Log;

$projetos  = new Projetos(array(), $app->db);
$cases     = new Cases(array(), $app->db);

/**
 *
 * @SWG\Api(
 *   path="/cases/{ativo}",
 *   description="Listagem de cases",
 *   @SWG\Operation(method="GET", summary="Listagem de cases", type="string", nickname="cases",
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
$app->get('/cases/:ativo', function ($ativo) use ($app, $cases, $projetos) {
    $cases = new CasesDao($app);
    $res   = $cases->getCasesComArquivos($ativo);

    echo json_encode($res);
});
