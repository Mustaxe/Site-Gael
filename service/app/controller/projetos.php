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
 *   resourcePath="/projetos",
 *   description="projetos do Projeto",
 *   produces="['application/json']"
 * )
 */

use app\model\Projetos;

$projetos  = new Projetos(array(), $app->db);

/**
 *
 * @SWG\Api(
 *   path="/projetos",
 *   description="Listagem dados do projeto",
 *   @SWG\Operation(method="GET", summary="Listagem dados projeto", type="string", nickname="listagemProjeto")
 * )
 */
$app->get('/projetos', function () use ($app, $projetos) {
    $projeto = $projetos->findOne(
         array('projeto' => 'Gael'),
        'projeto = :projeto'
    );
    // obs: para trazer somente determinados campos, passar como segundo argumento no findOne, por ex., para retornar somente os três campos seguintes: array("contato_email" => 1, "contato_fone" => 1, "contato_endereco" => 1)

    echo json_encode($projeto);
});
