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
 *   description="Operações Admin. Projetos",
 *   produces="['application/json']"
 * )
 */

use app\model\Projetos;
use app\model\Log;

// modelos usados
$projetos  = new Projetos(array(), $app->db);

/**
 *
 * @SWG\Api(
 *   path="/admin/projetos/{chave}",
 *   description="Editar dados de projeto",
 *   @SWG\Operation(method="PATCH", summary="Editar projeto", type="void", nickname="editaProjeto",
 *      @SWG\Parameters(
 *          @SWG\Parameter(
 *              name="chave",
 *              description="chave do usuário",
 *              paramType="path",
 *              required=true,
 *              type="string"
 *          ),
 *          @SWG\Parameter(
 *              name="email",
 *              description="E-mail",
 *              required=false,
 *              type="string",
 *              paramType="form"
 *          ),
 *          @SWG\Parameter(
 *              name="endereco",
 *              description="Endereco",
 *              required=false,
 *              type="string",
 *              paramType="form"
 *          ),
 *          @SWG\Parameter(
 *              name="video",
 *              description="Link do embed do youtube/vimeo",
 *              required=false,
 *              type="string",
 *              paramType="form"
 *          ),
 *          @SWG\Parameter(
 *              name="fone",
 *              description="Telefone",
 *              required=false,
 *              type="string",
 *              paramType="form"
 *          )
 *      ),
 *      @SWG\ResponseMessage(code=500, message="Problema ao salvar projeto")
 *   )
 * )
 */
$app->patch('/admin/projetos/:chave', function ($chave) use ($app, $projetos) {
    $params = $app->request;

    $projeto = $projetos->findOne(
        array('projeto' => 'Gael'),
        'projeto = :projeto'
    );

    $email    = trim($params->params('email'));
    $fone     = trim($params->params('fone'));
    $endereco = trim($params->params('endereco'));
    $video    = trim($params->params('video'));

    // usada verificação de undefined também, pois é o valor retornado pela API da documentação
    if(!empty($email)  && $email !== 'undefined')       $projetos->contato_email    = $email;
    if(!empty($fone) && $fone !== 'undefined')          $projetos->contato_fone     = $fone;
    if(!empty($endereco)  && $endereco !== 'undefined') $projetos->contato_endereco = $endereco;
    if(!empty($video)  && $video !== 'undefined')       $projetos->video            = $video;

    $res = $projetos->save(array('id' => $projeto->res['id']), 'id = :id');

    echo json_encode($res);
});
