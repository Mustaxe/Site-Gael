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
 *   resourcePath="/admin",
 *   description="Operações Admin. Grupos",
 *   produces="['application/json']"
 * )
 */

use app\model\Grupo;

// modelos usados
$grupos     = new Grupo(array(), $app->db);

/**
 *
 * @SWG\Api(
 *   path="/admin/grupos/{chave}",
 *   description="Listagem de grupos",
 *   @SWG\Operation(method="GET", summary="Listagem de grupos", type="string", nickname="listagemGrupos",
 *      @SWG\Parameter(
 *          name="chave",
 *          description="chave do usuário",
 *          paramType="path",
 *          required=true,
 *          type="string"
 *      )
 *   )
 * )
 */
$app->get('/admin/grupos/:chave', function ($chave) use ($app, $grupos) {
    $res     = $grupos->findAll();

    echo json_encode($res);
});

/**
 *
 * @SWG\Api(
 *   path="/admin/grupos/{chave}",
 *   description="Cadastrar grupo",
 *   @SWG\Operation(method="POST", summary="Cadastrar grupo", type="void", nickname="cadastraGrupo",
 *      @SWG\Parameters(
 *          @SWG\Parameter(
 *              name="chave",
 *              description="chave do usuário",
 *              paramType="path",
 *              required=true,
 *              type="string"
 *          ),
 *          @SWG\Parameter(
 *              name="grupo",
 *              description="Nome do grupo",
 *              required=true,
 *              type="string",
 *              paramType="form"
 *          ),
 *          @SWG\Parameter(
 *              name="rotas",
 *              description="Id das rotas pertencentes ao grupo (separadas por ,)",
 *              required=true,
 *              type="string",
 *              paramType="form"
 *          )
 *       ),
 *      @SWG\ResponseMessage(code=500, message="Problema ao salvar grupo")
 *   )
 * )
 */
$app->post('/admin/grupos/:chave', function ($chave) use ($app, $grupos) {
    $params = $app->request;

    $grupos->grupo = $params->post('grupo');
    $grupos->rotas = $params->post('rotas');

    $res = $grupos->create();

    echo json_encode($res);
});

/**
 *
 * @SWG\Api(
 *   path="/admin/grupos/{chave}/{id}",
 *   description="Editar dados do grupo",
 *   @SWG\Operation(method="PUT", summary="Editar grupo", type="void", nickname="editaGrupo",
 *      @SWG\Parameters(
 *          @SWG\Parameter(
 *              name="chave",
 *              description="chave do usuário",
 *              paramType="path",
 *              required=true,
 *              type="string"
 *          ),
 *          @SWG\Parameter(
 *              name="id",
 *              description="id do grupo",
 *              paramType="path",
 *              required=true,
 *              type="string"
 *          ),
 *          @SWG\Parameter(
 *              name="grupo",
 *              description="Nome do grupo",
 *              required=true,
 *              type="string",
 *              paramType="form"
 *          ),
 *          @SWG\Parameter(
 *              name="rotas",
 *              description="Id das rotas pertencentes ao grupo (separadas por ,)",
 *              required=true,
 *              type="string",
 *              paramType="form"
 *          )
 *      ),
 *      @SWG\ResponseMessage(code=500, message="Problema ao editar grupo")
 *   )
 * )
 */
$app->put('/admin/grupos/:chave/:id', function ($chave, $id) use ($app, $grupos) {
    $params = $app->request;

    $grupo   = trim($params->put('grupo'));
    $rotas   = trim($params->put('rotas'));

    // usada verificação de undefined também, pois é o valor retornado pela API da documentação
    if (!empty($grupo)  && $grupo !== 'undefined')  $grupos->grupo  = $grupo;
    if (!empty($rotas)  && $rotas !== 'undefined')  $grupos->rotas  = $rotas;

    $res = $grupos->save(array('id' => $id), 'id = :id');

    echo json_encode($res);
});

/**
 *
 * @SWG\Api(
 *   path="/admin/grupos/{chave}/{id}",
 *   description="Remover um grupo",
 *   @SWG\Operation(method="DELETE", summary="Remove um grupo pelo id", type="string", nickname="removeGrupo",
 *      @SWG\Parameter(
 *          name="chave",
 *          description="chave do usuário",
 *          paramType="path",
 *          required=true,
 *          type="string"
 *      ),
 *      @SWG\Parameter(
 *          name="id",
 *          description="id do grupo",
 *          paramType="path",
 *          required=true,
 *          type="string"
 *      ),
 *      @SWG\ResponseMessage(code=404, message="Grupo não encontrado"),
 *      @SWG\ResponseMessage(code=400, message="Id informado é inválido")
 *   )
 * )
 */
$app->delete('/admin/grupos/:chave/:id', function ($chave, $id) use ($app, $grupos){
    $res = $grupos->delete(array('id' => $id), 'id = :id');

    echo json_encode($res);
});
