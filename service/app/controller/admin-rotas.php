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
 *   description="Operações Admin. Rota",
 *   produces="['application/json']"
 * )
 */

use app\model\Rota;

// modelos usados
$rotas     = new Rota(array(), $app->db);

/**
 *
 * @SWG\Api(
 *   path="/admin/rotas/{chave}",
 *   description="Listagem de rotas",
 *   @SWG\Operation(method="GET", summary="Listagem de rotas", type="string", nickname="listagemRotas",
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
$app->get('/admin/rotas/:chave', function ($chave) use ($app, $rotas) {
    $res     = $rotas->findAll();

    echo json_encode($res);
});

/**
 *
 * @SWG\Api(
 *   path="/admin/rotas/{chave}",
 *   description="Cadastrar rota",
 *   @SWG\Operation(method="POST", summary="Cadastrar rota", notes="As rotas devem iniciar com / (barra), por ex., /admin/destaques", type="void", nickname="cadastraRota",
 *      @SWG\Parameters(
 *          @SWG\Parameter(
 *              name="chave",
 *              description="chave do usuário",
 *              paramType="path",
 *              required=true,
 *              type="string"
 *          ),
 *          @SWG\Parameter(
 *              name="nome",
 *              description="Nome",
 *              required=true,
 *              type="string",
 *              paramType="form"
 *          )
 *       ),
 *      @SWG\ResponseMessage(code=500, message="Problema ao salvar rota")
 *   )
 * )
 */
$app->post('/admin/rotas/:chave', function ($chave) use ($app, $rotas) {
    $params = $app->request;

    $rotas->nome           = $params->post('nome');

    $res = $rotas->create();

    echo json_encode($res);
});

/**
 *
 * @SWG\Api(
 *   path="/admin/rotas/{chave}/{id}",
 *   description="Editar dados da rota",
 *   @SWG\Operation(method="PUT", summary="Editar rota", type="void", nickname="editaRota",
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
 *              description="id da rota",
 *              paramType="path",
 *              required=true,
 *              type="string"
 *          ),
 *          @SWG\Parameter(
 *              name="nome",
 *              description="Nome",
 *              required=false,
 *              type="string",
 *              paramType="form"
 *          )
 *      ),
 *      @SWG\ResponseMessage(code=500, message="Problema ao editar rota")
 *   )
 * )
 */
$app->put('/admin/rotas/:chave/:id', function ($chave, $id) use ($app, $rotas) {
    $params = $app->request;

    $nome   = trim($params->put('nome'));

    // usada verificação de undefined também, pois é o valor retornado pela API da documentação
    if (!empty($nome)  && $nome !== 'undefined')  $rotas->nome  = $nome;

    $res = $rotas->save(array('id' => $id), 'id = :id');

    echo json_encode($res);
});

/**
 *
 * @SWG\Api(
 *   path="/admin/rotas/{chave}/{id}",
 *   description="Remover uma rota",
 *   @SWG\Operation(method="DELETE", summary="Remove uma rota pelo id", type="string", nickname="removeRota",
 *      @SWG\Parameter(
 *          name="chave",
 *          description="chave do usuário",
 *          paramType="path",
 *          required=true,
 *          type="string"
 *      ),
 *      @SWG\Parameter(
 *          name="id",
 *          description="id da rota",
 *          paramType="path",
 *          required=true,
 *          type="string"
 *      ),
 *      @SWG\ResponseMessage(code=404, message="Rota não encontrada"),
 *      @SWG\ResponseMessage(code=400, message="Id informado é inválido")
 *   )
 * )
 */
$app->delete('/admin/rotas/:chave/:id', function ($chave, $id) use ($app, $rotas){
    $res = $rotas->delete(array('id' => $id), 'id = :id');

    echo json_encode($res);
});
