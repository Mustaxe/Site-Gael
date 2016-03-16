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
 *   description="Operações Admin. Cases",
 *   produces="['application/json']"
 * )
 */

use app\model\Cases;
use app\model\Projetos;

$cases = new Cases(array(), $app->db);
$projetos = new Projetos(array(), $app->db);

/**
 *
 * @SWG\Api(
 *   path="/admin/cases/novo",
 *   description="Cadastrar cases",
 *   @SWG\Operation(method="POST", summary="Cadastrar cases", type="void", nickname="cadastrarCases",
 *      @SWG\Parameters(
 *          @SWG\Parameter(
 *              name="titulo",
 *              description="Título",
 *              paramType="form",
 *              required=true,
 *              type="string"
 *          ), 
 *          @SWG\Parameter(
 *              name="descricao",
 *              description="Subtítulo",
 *              paramType="form",
 *              required=true,
 *              type="string"
 *          ), 
 *          @SWG\Parameter(
 *              name="texto",
 *              description="Texto",
 *              paramType="form",
 *              required=true,
 *              type="string"
 *          ), 
 *          @SWG\Parameter(
 *              name="imagem_thumb",
 *              description="Imagem Thumb",
 *              paramType="body",
 *              required=true,
 *              type="File"
 *          ), 
 *          @SWG\Parameter(
 *              name="imagem_thumb_over",
 *              description="Imagem Thumb Hover",
 *              paramType="body",
 *              required=true,
 *              type="File"
 *          ), 
 *          @SWG\Parameter(
 *              name="imagem_integra",
 *              description="Imagem na íntegra",
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
$app->map('/admin/cases/novo', function () use ($app, $cases, $projetos){
    $params = $app->request;

    if ($params->isPost()) {
 
        $cases->titulo = $params->post('titulo');
 
        $cases->descricao = $params->post('descricao');
 
        $cases->texto = $params->post('texto');
 
        $ImagemThumb = isset($_FILES['imagem_thumb']) ? $_FILES['imagem_thumb'] : '';
        if (empty($ImagemThumb) || $ImagemThumb == 'undefined') {
            $app->flash('error', 'O arquivo não foi fornecido.');

            return;
        }

        $uploader = $app->uploader;
        $uploader->setArquivo('imagem_thumb');
        $uploader->setTipo('cases');
        $ImagemThumbUpload = $uploader->salva();
        $cases->imagem_thumb = $ImagemThumbUpload->res['id'];
 
        $ImagemThumbOver = isset($_FILES['imagem_thumb_over']) ? $_FILES['imagem_thumb_over'] : '';
        if (empty($ImagemThumbOver) || $ImagemThumbOver == 'undefined') {
            $app->flash('error', 'O arquivo não foi fornecido.');

            return;
        }

		
        $uploader = $app->uploader;
        $uploader->setArquivo('imagem_thumb_over');
        $uploader->setTipo('cases');
        $ImagemThumbOverUpload = $uploader->salva();
        $cases->imagem_thumb_over = $ImagemThumbOverUpload->res['id'];
 
/*         $ImagemIntegra = isset($_FILES['imagem_integra1']) ? $_FILES['imagem_integra'1] : '';
        if (empty($ImagemIntegra) || $ImagemIntegra == 'undefined') {
            $app->flash('error', 'O arquivo não foi fornecido.');

            return;
        } */

        $uploader = $app->uploader;
        $uploader->setArquivo('imagem_integra1');
		$uploader->setTipo('cases');
        $ImagemIntegraUpload = $uploader->salva();
        $cases->imagem_integra1 = $ImagemIntegraUpload->res['id'];
		
		$uploader = $app->uploader;
		$uploader->setArquivo('imagem_integra2');
		$uploader->setTipo('cases');
        $ImagemIntegraUpload = $uploader->salva();
        $cases->imagem_integra2 = $ImagemIntegraUpload->res['id'];
		
		$uploader = $app->uploader;
		$uploader->setArquivo('imagem_integra3');
		$uploader->setTipo('cases');
        $ImagemIntegraUpload = $uploader->salva();
        $cases->imagem_integra3 = $ImagemIntegraUpload->res['id'];
		
		$uploader = $app->uploader;
		$uploader->setArquivo('imagem_integra4');
		$uploader->setTipo('cases');
        $ImagemIntegraUpload = $uploader->salva();
        $cases->imagem_integra4 = $ImagemIntegraUpload->res['id'];
		
		$uploader = $app->uploader;
		$uploader->setArquivo('imagem_integra5');
		$uploader->setTipo('cases');
        $ImagemIntegraUpload = $uploader->salva();
        $cases->imagem_integra5 = $ImagemIntegraUpload->res['id'];

        $cases->ativo = $params->post('ativo');

        $res = $cases->create();

        if ($res->cod == 200) {
            $app->flash('notice', 'Informação adicionada com sucesso');
        }else{
            $app->flash('error', 'Não foi possível adicionar a informação.');
        }
        $app->flashKeep();

        $app->redirect($app->urlFor('listagem_cases'));
    }

    $app->render('admin/cases/novo.html.twig');
})->via("POST", "GET")->name('adiciona_cases');

/**
 *
 * @SWG\Api(
 *   path="/admin/cases/{id}",
 *   description="Editar cases",
 *   @SWG\Operation(method="POST", summary="Editar cases", type="void", nickname="cadastrarCases",
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
 *              name="descricao",
 *              description="Subtítulo",
 *              paramType="form",
 *              required=true,
 *              type="string"
 *          ), 
 *          @SWG\Parameter(
 *              name="texto",
 *              description="Texto",
 *              paramType="form",
 *              required=true,
 *              type="string"
 *          ), 
 *          @SWG\Parameter(
 *              name="imagem_thumb",
 *              description="Imagem Thumb",
 *              paramType="body",
 *              required=true,
 *              type="File"
 *          ), 
 *          @SWG\Parameter(
 *              name="imagem_thumb_over",
 *              description="Imagem Thumb Over",
 *              paramType="body",
 *              required=true,
 *              type="File"
 *          ), 
 *          @SWG\Parameter(
 *              name="imagem_integra",
 *              description="Imagem na íntegra",
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
 *      @SWG\ResponseMessage(code=500, message="Problema ao editar case") 
 *   )
 * )
 */
$app->post('/admin/cases/:id', function ($id) use ($app, $cases, $projetos){
    $params  = $app->request;

    $res = $cases->findById($id);

    if ($res->cod == 404) {
        $app->notFound();
        exit;
    }
	
    $titulo = trim($params->post('titulo'));
    if (!empty($titulo) && $titulo !== 'undefined') $cases->titulo = $titulo;

    $descricao = trim($params->post('descricao'));
    if (!empty($descricao) && $descricao !== 'undefined') $cases->descricao = $descricao;

    $texto = trim($params->post('texto'));
    if (!empty($texto) && $texto !== 'undefined') $cases->texto = $texto;

    //$ImagemThumb = isset($_FILES['imagem_thumb']) ? $_FILES['imagem_thumb'] : '';
    //if (!empty($ImagemThumb) && $ImagemThumb !== 'undefined') {
	if(strlen($_FILES['imagem_thumb']['name']) > 0) {
        $uploader = $app->uploader;
        $uploader->setArquivo('imagem_thumb');
        $uploader->setTipo('cases');
        $ImagemThumbUpload = $uploader->salva();
        $cases->imagem_thumb = $ImagemThumbUpload->res['id'];
	}
	
    //$ImagemThumbOver = isset($_FILES['imagem_thumb_over']) ? $_FILES['imagem_thumb_over'] : '';
    //if (!empty($ImagemThumbOver) && $ImagemThumbOver !== 'undefined') {
	if(strlen($_FILES['imagem_thumb_over']['name']) > 0) {
        $uploader = $app->uploader;
        $uploader->setArquivo('imagem_thumb_over');
        $uploader->setTipo('cases');
        $ImagemThumbOverUpload = $uploader->salva();
        $cases->imagem_thumb_over = $ImagemThumbOverUpload->res['id'];
    }
	
    //$ImagemIntegra = isset($_FILES['imagem_integra1']) ? $_FILES['imagem_integra1'] : '';
    //if (!empty($ImagemIntegra) && $ImagemIntegra !== 'undefined') {
	if(strlen($_FILES['imagem_integra1']['name']) > 0) {
        $uploader = $app->uploader;
        $uploader->setArquivo('imagem_integra1');
        $uploader->setTipo('cases');
        $ImagemIntegraUpload = $uploader->salva();
        $cases->imagem_integra1 = $ImagemIntegraUpload->res['id'];
    }
	
    //$ImagemIntegra = isset($_FILES['imagem_integra2']) ? $_FILES['imagem_integra2'] : '';
    //if (!empty($ImagemIntegra) && $ImagemIntegra !== 'undefined') {
	if(strlen($_FILES['imagem_integra2']['name']) > 0) {
        $uploader = $app->uploader;
        $uploader->setArquivo('imagem_integra2');
        $uploader->setTipo('cases');
        $ImagemIntegraUpload = $uploader->salva();
        $cases->imagem_integra2 = $ImagemIntegraUpload->res['id'];
    }

    //$ImagemIntegra = isset($_FILES['imagem_integra3']) ? $_FILES['imagem_integra3'] : '';
    //if (!empty($ImagemIntegra) && $ImagemIntegra !== 'undefined') {
	if(strlen($_FILES['imagem_integra3']['name']) > 0) {
        $uploader = $app->uploader;
        $uploader->setArquivo('imagem_integra3');
        $uploader->setTipo('cases');
        $ImagemIntegraUpload = $uploader->salva();
        $cases->imagem_integra3 = $ImagemIntegraUpload->res['id'];
    }

    //$ImagemIntegra = isset($_FILES['imagem_integra4']) ? $_FILES['imagem_integra4'] : '';
    //if (!empty($ImagemIntegra) && $ImagemIntegra !== 'undefined') {
	if(strlen($_FILES['imagem_integra4']['name']) > 0) {
        $uploader = $app->uploader;
        $uploader->setArquivo('imagem_integra4');
        $uploader->setTipo('cases');
        $ImagemIntegraUpload = $uploader->salva();
        $cases->imagem_integra4 = $ImagemIntegraUpload->res['id'];
    }

    //$ImagemIntegra = isset($_FILES['imagem_integra5']) ? $_FILES['imagem_integra5'] : '';
    //if (!empty($ImagemIntegra) && $ImagemIntegra !== 'undefined') {
	if(strlen($_FILES['imagem_integra5']['name']) > 0) {
        $uploader = $app->uploader;
        $uploader->setArquivo('imagem_integra5');
        $uploader->setTipo('cases');
        $ImagemIntegraUpload = $uploader->salva();
        $cases->imagem_integra5 = $ImagemIntegraUpload->res['id'];
    }
	
	
    $ativo = trim($params->post('ativo'));
    if (($ativo == '1' || $ativo == '0') && $ativo !== 'undefined') $cases->ativo = $ativo;

    $vars = $cases->getVariables();

    if (empty($vars)) {
        $app->flash('error', 'Nenhuma informação foi salva.');
        $app->redirect($app->urlFor('busca_cases', array('id' => $id)));
    }

    $res = $cases->save(array('id' => $id), 'id = :id');

    if ($res->cod == 200) {
        $app->flash('notice', 'Informação atualizada com sucesso');
        $app->redirect($app->urlFor('listagem_cases'));
    }

    $app->flash('error', 'Não foi possível atualizar a informação.');
    $app->redirect($app->urlFor('busca_cases', array('id' => $id)));
})->name('edita_cases');

/**
 *
 * @SWG\Api(
 *   path="/admin/cases/{id}",
 *   description="Remover case",
 *   @SWG\Operation(method="DELETE", summary="Remover case", type="void", nickname="removerCases",
 *      @SWG\ResponseMessage(code=500, message="Problema ao remover case") 
 *   )
 * )
 */
$app->delete('/admin/cases/:id', function ($id) use ($app, $cases, $projetos){
    $res = $cases->delete(array('id' => $id), 'id = :id');

    if ($res) {
        $app->flash('notice', 'Informação excluída com sucesso');
    }else{
        $app->flash('error', 'Não foi possível excluir a informação.');
    }
    $app->flashKeep();

    $app->redirect($app->urlFor('listagem_cases'));
});

/**
 *
 * @SWG\Api(
 *   path="/admin/cases",
 *   description="Listagem de cases",
 *   @SWG\Operation(method="GET", summary="Listagem de cases", type="void", nickname="listagemCases",
 *      @SWG\ResponseMessage(code=500, message="Problema ao salvar case") 
 *   )
 * )
 */
$app->get('/admin/cases', function () use ($app, $cases, $projetos){
    //$res = $cases->findAll();
	$sql = 'SELECT C.id, C.titulo AS "Título", C.descricao AS "Descrição", C.texto AS "Texto", 
				CONCAT("/service/web/uploads/", A1.extensao, "/", A1.nome) AS "Thumb", 
				CONCAT("/service/web/uploads/", A2.extensao, "/", A2.nome) AS "ThumbHover", 
				C.ativo AS "Ativo" FROM tbl_cases C
					LEFT OUTER JOIN tbl_arquivo A1 ON A1.id = C.imagem_thumb
					LEFT OUTER JOIN tbl_arquivo A2 ON A2.id = C.imagem_thumb_over
					LEFT OUTER JOIN tbl_arquivo A3 ON A3.id = C.imagem_integra1
					LEFT OUTER JOIN tbl_arquivo A4 ON A4.id = C.imagem_integra2
					LEFT OUTER JOIN tbl_arquivo A5 ON A5.id = C.imagem_integra3
					LEFT OUTER JOIN tbl_arquivo A6 ON A6.id = C.imagem_integra4
					LEFT OUTER JOIN tbl_arquivo A7 ON A7.id = C.imagem_integra5
				WHERE C.status = 1 AND A1.status = 1 AND A2.status = 1 AND A3.status = 1';
	
	$res = $cases->findQuery($sql);
	
	$colunas = array_keys($res->res[0]);
	
    $app->render('admin/cases/listagem.html.twig', array('cases'=>$res->res, 'colunas'=>$colunas));
})->name('listagem_cases');

/**
 *
 * @SWG\Api(
 *   path="/admin/cases/{id}",
 *   description="Busca case pelo id",
 *   @SWG\Operation(method="GET", summary="Busca case pelo id", type="void", nickname="listagemCases",
 *      @SWG\Parameters(
 *          @SWG\Parameter(
 *              name="id",
 *              description="Id",
 *              paramType="form",
 *              required=true,
 *              type="string"
 *          ) 
 *      ),
 *      @SWG\ResponseMessage(code=500, message="Problema ao buscar case") 
 *   )
 * )
 */
$app->get('/admin/cases/:id', function ($id) use ($app, $cases, $projetos){
    //$res = $cases->findById($id);
	$sql = 'SELECT C.id, C.titulo AS "titulo", C.descricao AS "descricao", C.texto AS "texto", 
				CONCAT("/service/web/uploads/", A1.extensao, "/", A1.nome) AS "thumb", 
				CONCAT("/service/web/uploads/", A2.extensao, "/", A2.nome) AS "thumbHover", 
				CONCAT("/service/web/uploads/", A3.extensao, "/", A3.nome) AS "integra1", 
				CONCAT("/service/web/uploads/", A4.extensao, "/", A4.nome) AS "integra2", 
				CONCAT("/service/web/uploads/", A5.extensao, "/", A5.nome) AS "integra3", 
				CONCAT("/service/web/uploads/", A6.extensao, "/", A6.nome) AS "integra4", 
				CONCAT("/service/web/uploads/", A7.extensao, "/", A7.nome) AS "integra5", 
				C.ativo AS "ativo" FROM tbl_cases C
					LEFT OUTER JOIN tbl_arquivo A1 ON A1.id = C.imagem_thumb
					LEFT OUTER JOIN tbl_arquivo A2 ON A2.id = C.imagem_thumb_over
					LEFT OUTER JOIN tbl_arquivo A3 ON A3.id = C.imagem_integra1
					LEFT OUTER JOIN tbl_arquivo A4 ON A4.id = C.imagem_integra2
					LEFT OUTER JOIN tbl_arquivo A5 ON A5.id = C.imagem_integra3
					LEFT OUTER JOIN tbl_arquivo A6 ON A6.id = C.imagem_integra4
					LEFT OUTER JOIN tbl_arquivo A7 ON A7.id = C.imagem_integra5
				WHERE C.status = 1 AND A1.status = 1 AND A2.status = 1 AND A3.status = 1 AND C.id = ' . $id;
	$res = $cases->findQuery($sql);
	
    if ($res->cod == 404) {
        $app->notFound();
    }else{
        $app->render('admin/cases/editar.html.twig', array('cases'=>$res->res[0]));
    }
})->name('busca_cases');


