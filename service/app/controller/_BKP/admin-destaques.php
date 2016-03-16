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
 *   description="Operações Admin. Destaques",
 *   produces="['application/json']"
 * )
 */

use app\model\Destaques;
use app\model\Projetos;

$destaques = new Destaques(array(), $app->db);
$projetos = new Projetos(array(), $app->db);

/**
 *
 * @SWG\Api(
 *   path="/admin/destaques/novo",
 *   description="Cadastrar destaque",
 *   @SWG\Operation(method="POST", summary="Cadastrar destaque", type="void", nickname="cadastrarDestaques",
 *      @SWG\Parameters(
 *          @SWG\Parameter(
 *              name="titulo",
 *              description="Título",
 *              paramType="form",
 *              required=true,
 *              type="string"
 *          ), 
 *          @SWG\Parameter(
 *              name="link",
 *              description="Link",
 *              paramType="form",
 *              required=false,
 *              type="string"
 *          ), 
 *          @SWG\Parameter(
 *              name="imagem",
 *              description="Imagem",
 *              paramType="body",
 *              required=true,
 *              type="File"
 *          ), 
 *          @SWG\Parameter(
 *              name="thumb",
 *              description="Hover",
 *              paramType="body",
 *              required=true,
 *              type="File"
 *          ), 
 *          @SWG\Parameter(
 *              name="ativo",
 *              description="Ativo/Inativo",
 *              paramType="form",
 *              required=true,
 *              type="string"
 *          ) 
 *      ),
 *      @SWG\ResponseMessage(code=500, message="Problema ao salvar case") 
 *   )
 * )
 */
$app->map('/admin/destaques/novo', function () use ($app, $destaques, $projetos){
    $params = $app->request;

    if ($params->isPost()) {
 
        $destaques->titulo = $params->post('titulo');
 
        $destaques->link = $params->post('link');
 
        $Imagem = isset($_FILES['imagem']) ? $_FILES['imagem'] : '';
        if (empty($Imagem) || $Imagem == 'undefined') {
            $app->flash('error', 'O arquivo não foi fornecido.');

            return;
        }

        $uploader = $app->uploader;
        $uploader->setArquivo('imagem');
        $uploader->setTipo('destaques');

        $ImagemUpload = $uploader->salva();

        $destaques->imagem = $ImagemUpload->res['id'];
 
        $Thumb = isset($_FILES['thumb']) ? $_FILES['thumb'] : '';
        if (empty($Thumb) || $Thumb == 'undefined') {
            $app->flash('error', 'O arquivo não foi fornecido.');

            return;
        }

        $uploader = $app->uploader;
        $uploader->setArquivo('thumb');
        $uploader->setTipo('destaques');

        $ThumbUpload = $uploader->salva();

        $destaques->thumb = $ThumbUpload->res['id'];
 
        $destaques->ativo = $params->post('ativo');

        $res = $destaques->create();

        if ($res->cod == 200) {
            $app->flash('notice', 'Informação adicionada com sucesso');
        }else{
            $app->flash('error', 'Não foi possível adicionar a informação.');
        }
        $app->flashKeep();

        $app->redirect($app->urlFor('listagem_destaques'));
    }

    $app->render('admin/destaques/novo.html.twig');
})->via("POST", "GET")->name('adiciona_destaques');

/**
 *
 * @SWG\Api(
 *   path="/admin/destaques/{id}",
 *   description="Editar destaque",
 *   @SWG\Operation(method="POST", summary="Editar destaque", type="void", nickname="cadastrarDestaques",
 *      @SWG\Parameters(
 *          @SWG\Parameter(
 *              name="id",
 *              description="Id",
 *              paramType="path",
 *              required=true,
 *              type="string"
 *          ), 
 *          @SWG\Parameter(
 *              name="titulo",
 *              description="Título",
 *              paramType="form",
 *              required=true,
 *              type="string"
 *          ), 
 *          @SWG\Parameter(
 *              name="link",
 *              description="Link",
 *              paramType="form",
 *              required=false,
 *              type="string"
 *          ), 
 *          @SWG\Parameter(
 *              name="imagem",
 *              description="BG",
 *              paramType="body",
 *              required=true,
 *              type="File"
 *          ), 
 *          @SWG\Parameter(
 *              name="thumb",
 *              description="Hover",
 *              paramType="body",
 *              required=true,
 *              type="File"
 *          ), 
 *          @SWG\Parameter(
 *              name="ativo",
 *              description="Ativo/Inativo",
 *              paramType="form",
 *              required=true,
 *              type="string"
 *          ) 
 *      ),
 *      @SWG\ResponseMessage(code=500, message="Problema ao editar destaque") 
 *   )
 * )
 */
$app->post('/admin/destaques/:id', function ($id) use ($app, $destaques, $projetos){
    $params  = $app->request;

    $res = $destaques->findById($id);

    if ($res->cod == 404) {
        $app->notFound();
        exit;
    }

    $titulo = trim($params->post('titulo'));
    if (!empty($titulo) && $titulo !== 'undefined') $destaques->titulo = $titulo;

    $link = trim($params->post('link'));
    if (!empty($link) && $link !== 'undefined') $destaques->link = $link;

    $Imagem = isset($_FILES['imagem']) ? $_FILES['imagem'] : '';
    if (!empty($imagem) && $imagem !== 'undefined') {
        $uploader = $app->uploader;
        $uploader->setArquivo('imagem');
        $uploader->setTipo('destaques');

        $ImagemUpload = $uploader->salva();

        $destaques->imagem = $ImagemUpload->res['id'];
    }
    $Thumb = isset($_FILES['thumb']) ? $_FILES['thumb'] : '';
    if (!empty($thumb) && $thumb !== 'undefined') {
        $uploader = $app->uploader;
        $uploader->setArquivo('thumb');
        $uploader->setTipo('destaques');

        $ThumbUpload = $uploader->salva();

        $destaques->thumb = $ThumbUpload->res['id'];
    }
    $ativo = trim($params->post('ativo'));
    if (($ativo == '1' || $ativo == '0') && $ativo !== 'undefined') $destaques->ativo = $ativo;


    $vars = $destaques->getVariables();

    if (empty($vars)) {
        $app->flash('error', 'Nenhuma informação foi salva.');
        $app->redirect($app->urlFor('busca_destaques', array('id' => $id)));
    }

    $res = $destaques->save(array('id' => $id), 'id = :id');

    if ($res->cod == 200) {
        $app->flash('notice', 'Informação atualizada com sucesso');
        $app->redirect($app->urlFor('listagem_destaques'));
    }

    $app->flash('error', 'Não foi possível atualizar a informação.');
    $app->redirect($app->urlFor('busca_destaques', array('id' => $id)));
})->name('edita_destaques');

/**
 *
 * @SWG\Api(
 *   path="/admin/destaques",
 *   description="Listagem de destaques",
 *   @SWG\Operation(method="GET", summary="Listagem de destaques", type="void", nickname="listagemDestaques",
 *      @SWG\ResponseMessage(code=500, message="Problema ao acessar destaques") 
 *   )
 * )
 */
$app->get('/admin/destaques', function () use ($app, $destaques, $projetos){
    //$res = $destaques->findAll();
	$sql = 'SELECT D.id, D.titulo AS "Título", D.link AS "Link",
				CONCAT("/service/web/uploads/", A1.extensao, "/", A1.nome) AS "Thumb", 
				CONCAT("/service/web/uploads/", A2.extensao, "/", A2.nome) AS "Imagem", 
				D.ativo AS "Ativo" FROM tbl_destaques D
					LEFT OUTER JOIN tbl_arquivo A1 ON A1.id = D.thumb
					LEFT OUTER JOIN tbl_arquivo A2 ON A2.id = D.imagem
				WHERE D.status = 1 AND A1.status = 1 AND A2.status = 1';
	$res = $destaques->findQuery($sql);
	
    $colunas = array_keys($res->res[0]);

    $app->render('admin/destaques/listagem.html.twig', array('destaques'=>$res->res, 'colunas'=>$colunas));
})->name('listagem_destaques');

/**
 *
 * @SWG\Api(
 *   path="/admin/destaques/{id}",
 *   description="Busca destaque pelo id",
 *   @SWG\Operation(method="GET", summary="Busca destaque pelo id", type="void", nickname="listagemDestaques",
 *      @SWG\Parameters(
 *          @SWG\Parameter(
 *              name="id",
 *              description="Id",
 *              paramType="form",
 *              required=true,
 *              type="string"
 *          ) 
 *      ),
 *      @SWG\ResponseMessage(code=500, message="Problema ao buscar destaque") 
 *   )
 * )
 */
$app->get('/admin/destaques/:id', function ($id) use ($app, $destaques, $projetos){
    //$res = $destaques->findById($id);
	$sql = 'SELECT D.id, D.titulo AS "titulo", D.link AS "link",
				CONCAT("/service/web/uploads/", A1.extensao, "/", A1.nome) AS "thumb", 
				CONCAT("/service/web/uploads/", A2.extensao, "/", A2.nome) AS "imagem", 
				D.ativo AS "ativo" FROM tbl_destaques D
					LEFT OUTER JOIN tbl_arquivo A1 ON A1.id = D.thumb
					LEFT OUTER JOIN tbl_arquivo A2 ON A2.id = D.imagem
				WHERE D.status = 1 AND A1.status = 1 AND A2.status = 1 AND D.id = ' . $id;
	$res = $destaques->findQuery($sql);

    if ($res->cod == 404) {
        $app->notFound();
    }else{
        $app->render('admin/destaques/editar.html.twig', array('destaques'=>$res->res[0]));
    }
})->name('busca_destaques');

/**
 *
 * @SWG\Api(
 *   path="/admin/destaques/{id}",
 *   description="Remover destaque",
 *   @SWG\Operation(method="DELETE", summary="Remover destaque", type="void", nickname="removerDestaques",
 *      @SWG\ResponseMessage(code=500, message="Problema ao remover destaque") 
 *   )
 * )
 */
$app->delete('/admin/destaques/:id', function ($id) use ($app, $destaques){
    $res = $destaques->delete(array('id' => $id), 'id = :id');

    if ($res) {
        $app->flash('notice', 'Informação excluída com sucesso');
    }else{
        $app->flash('error', 'Não foi possível excluir a informação.');
    }
    $app->flashKeep();

    $app->redirect($app->urlFor('listagem_destaques'));
});


