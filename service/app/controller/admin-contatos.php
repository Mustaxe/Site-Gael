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
 *   description="Operações Admin. Contatos",
 *   produces="['application/json']"
 * )
 */

use app\model\Contatos;
use app\model\Projetos;

// modelos usados
$contatos  = new Contatos(array(), $app->db);
$projetos  = new Projetos(array(), $app->db);

/**
 *
 * @SWG\Api(
 *   path="/admin/contatos/{chave}",
 *   description="Listagem de contatos",
 *   @SWG\Operation(method="GET", summary="Listagem de contatos", type="string", nickname="listagemContatos",
 *      @SWG\Parameter(
 *          name="chave",
 *          description="chave do usuario",
 *          paramType="path",
 *          required=true,
 *          type="string"
 *      )
 *   )
 * )
 */
$app->get('/admin/contatos/:chave', function ($chave) use ($app, $contatos, $projetos) {
    $res = $contatos->findAll();

    echo json_encode($res);
});

/**
 *
 * @SWG\Api(
 *   path="/admin/contatos/{chave}/{id}",
 *   description="Remover um contato",
 *   @SWG\Operation(method="DELETE", summary="Remove um contato pelo id", type="string", nickname="removeContato",
 *      @SWG\Parameter(
 *          name="chave",
 *          description="chave do usuário",
 *          paramType="path",
 *          required=true,
 *          type="string"
 *      ),
 *      @SWG\Parameter(
 *          name="id",
 *          description="id do contato que será removido",
 *          paramType="path",
 *          required=true,
 *          type="string"
 *      ),
 *      @SWG\ResponseMessage(code=404, message="Contato não encontrado"),
 *      @SWG\ResponseMessage(code=400, message="Id informado é inválido")
 *   )
 * )
 */
$app->delete('/admin/contatos/:chave/:id', function ($chave, $id) use ($app, $contatos){
    $res = $contatos->delete(array('id' => $id), 'id = :id');

    echo json_encode($res);
});
