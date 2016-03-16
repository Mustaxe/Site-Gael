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
 *   description="Operações Admin. Contato",
 *   produces="['application/json']"
 * )
 */

use app\model\Contatos;
use app\model\Projetos;

//$contato = new Contatos(array(), $app->db);
//$projetos = new Projetos(array(), $app->db);
$contato = new Projetos(array(), $app->db);

/**
 *
 * @SWG\Api(
 *   path="/admin/contato/{id}",
 *   description="Editar contato",
 *   @SWG\Operation(method="POST", summary="Editar contato", type="void", nickname="cadastrarContato",
 *      @SWG\Parameters(
 *          @SWG\Parameter(
 *              name="id",
 *              description="Id",
 *              paramType="path",
 *              required=true,
 *              type="string"
 *          ), 
 *          @SWG\Parameter(
 *              name="contato_email",
 *              description="Email",
 *              paramType="form",
 *              required=true,
 *              type="string"
 *          ), 
 *          @SWG\Parameter(
 *              name="contato_fone",
 *              description="Telefone",
 *              paramType="form",
 *              required=true,
 *              type="string"
 *          ), 
 *          @SWG\Parameter(
 *              name="contato_endereco",
 *              description="Endereço",
 *              paramType="body",
 *              required=true,
 *              type="string"
 *          ), 
 *          @SWG\Parameter(
 *              name="video",
 *              description="Vídeo",
 *              paramType="body",
 *              required=true,
 *              type="string"
 *          ) 
 *      ),
 *      @SWG\ResponseMessage(code=500, message="Problema ao editar contato") 
 *   )
 * )
 */
$app->post('/admin/contato/:id', function ($id) use ($app, $contato, $projetos){
    $params  = $app->request;

    $res = $contato->findById($id);

    if ($res->cod == 404) {
        $app->notFound();
        exit;
    }

    $contato_email = trim($params->post('contato_email'));
    if (!empty($contato_email) && $contato_email !== 'undefined') $contato->contato_email = $contato_email;

    $contato_fone = trim($params->post('contato_fone'));
    if (!empty($contato_fone) && $contato_fone !== 'undefined') $contato->contato_fone = $contato_fone;

    $contato_endereco = trim($params->post('contato_endereco'));
    if (!empty($contato_endereco) && $contato_endereco !== 'undefined') $contato->contato_endereco = $contato_endereco;

    $video = trim($params->post('video'));
    if (!empty($video) && $video !== 'undefined') $contato->video = $video;


    $vars = $contato->getVariables();

    if (empty($vars)) {
        $app->flash('error', 'Nenhuma informação foi salva.');
        $app->redirect($app->urlFor('busca_contato', array('id' => $id)));
    }

    $res = $contato->save(array('id' => $id), 'id = :id');

    if ($res->cod == 200) {
        $app->flash('notice', 'Informação atualizada com sucesso');
        $app->redirect($app->urlFor('listagem_contato'));
    }

    $app->flash('error', 'Não foi possível atualizar a informação.');
    $app->redirect($app->urlFor('busca_contato', array('id' => $id)));
})->name('edita_contato');

/**
 *
 * @SWG\Api(
 *   path="/admin/contato",
 *   description="Listagem de contato",
 *   @SWG\Operation(method="GET", summary="Listagem de contato", type="void", nickname="listagemContato",
 *      @SWG\ResponseMessage(code=500, message="Problema ao salvar contato") 
 *   )
 * )
 */
$app->get('/admin/contato', function () use ($app, $contato, $projetos){
    $res = $contato->findAll();

	$colunas = array('ID', 'Email de contato', 'Fone de contato', 'Endereço', 'Projeto', 'Ativo', 'Vídeo');
	$colunas_key = array_keys($res->res[0]);
	
    $app->render('admin/contato/listagem.html.twig', array('contato'=>$res->res, 'colunas'=>$colunas, 'colunas_key'=>$colunas_key));
})->name('listagem_contato');

/**
 *
 * @SWG\Api(
 *   path="/admin/contato/{id}",
 *   description="Busca contato pelo id",
 *   @SWG\Operation(method="GET", summary="Busca contato pelo id", type="void", nickname="listagemContato",
 *      @SWG\Parameters(
 *          @SWG\Parameter(
 *              name="id",
 *              description="Id",
 *              paramType="form",
 *              required=true,
 *              type="string"
 *          ) 
 *      ),
 *      @SWG\ResponseMessage(code=500, message="Problema ao buscar contato") 
 *   )
 * )
 */
$app->get('/admin/contato/:id', function ($id) use ($app, $contato, $projetos){
    $res = $contato->findById($id);

    if ($res->cod == 404) {
        $app->notFound();
    }else{
        $app->render('admin/contato/editar.html.twig', array('contato'=>$res->res));
    }
})->name('busca_contato');


