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
 *   resourcePath="/categorias",
 *   description="Operações Admin. Categorias",
 *   produces="['application/json']"
 * )
 */

use app\model\Categorias;
use app\model\Projetos;

$categorias = new Categorias(array(), $app->db);
$projetos = new Projetos(array(), $app->db);

/**
 *
 * @SWG\Api(
 *   path="/admin/categorias/novo",
 *   description="Cadastrar categorias",
 *   @SWG\Operation(method="POST", summary="Cadastrar categorias", type="void", nickname="cadastrarCategorias",
 *      @SWG\Parameters(
 *          @SWG\Parameter(
 *              name="nome",
 *              description="Nome",
 *              paramType="form",
 *              required=true,
 *              type="string"
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
$app->map('/admin/categorias/novo', function () use ($app, $categorias, $projetos){
    $params = $app->request;

    if ($params->isPost()) {
 
        $categorias->nome = $params->post('nome');
		$categorias->status = 1;
        $categorias->lang = $params->post('lang');
        $categorias->ativo = $params->post('ativo');
		$categorias->tipo = $params->post('tipo');

        $res = $categorias->create();

        if ($res->cod == 200) {
            $app->flash('notice', 'Informação adicionada com sucesso');
        }else{
            $app->flash('error', 'Não foi possível adicionar a informação.');
        }
        $app->flashKeep();

        $app->redirect($app->urlFor('listagem_categorias'));
    }

    $app->render('admin/categorias/novo.html.twig');
})->via("POST", "GET")->name('adiciona_categorias');

/**
 *
 * @SWG\Api(
 *   path="/admin/categorias/{id}",
 *   description="Editar categorias",
 *   @SWG\Operation(method="POST", summary="Editar categorias", type="void", nickname="cadastrarCategorias",
 *      @SWG\Parameters(
 *          @SWG\Parameter(
 *              name="id",
 *              description="Id",
 *              paramType="path",
 *              required=true,
 *              type="string"
 *          ), 
 *          @SWG\Parameter(
 *              name="nome",
 *              description="Nome",
 *              paramType="form",
 *              required=true,
 *              type="string"
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
$app->post('/admin/categorias/:id', function ($id) use ($app, $categorias, $projetos){
    
    $params  = $app->request;

    $res = $categorias->findById($id);

    if ($res->cod == 404) {
        $app->notFound();
        exit;
    }
	
    $titulo = trim($params->post('nome'));
    if (!empty($titulo) && $titulo !== 'undefined')
    {
        $categorias->nome = $titulo;
    } 

    $ativo = trim($params->post('ativo'));
    if (($ativo == '1' || $ativo == '0') && $ativo !== 'undefined')
    {
        $categorias->ativo = $ativo;
    }


    /**
    * Lang
    */    
    $lang = trim($params->post('lang'));
    if ($lang == 'pt' || $lang == 'en')
    {
        $categorias->lang = $lang;
    }
    else 
    {
        /**
        * Lang padrão do sistema
        */
        $categorias->lang = 'pt';
    }



    $tipo = trim($params->post('tipo'));
    if (($tipo == 'E' || $tipo == 'J') && $tipo !== 'undefined')
    {
        $categorias->tipo = $tipo;
    } 
	
    $vars = $categorias->getVariables();

    if (empty($vars)) {
        $app->flash('error', 'Nenhuma informação foi salva.');
        $app->redirect($app->urlFor('busca_cases', array('id' => $id)));
    }

    $res = $categorias->save(array('id' => $id), 'id = :id');

    if ($res->cod == 200) {
        $app->flash('notice', 'Informação atualizada com sucesso');
        $app->redirect($app->urlFor('listagem_categorias'));
    }

    $app->flash('error', 'Não foi possível atualizar a informação.');
    $app->redirect($app->urlFor('busca_cases', array('id' => $id)));
})->name('edita_categorias');

/**
 *
 * @SWG\Api(
 *   path="/admin/categorias/{id}",
 *   description="Remover categorias",
 *   @SWG\Operation(method="DELETE", summary="Remover categoria", type="void", nickname="removerCategoria",
 *      @SWG\ResponseMessage(code=500, message="Problema ao remover case")
 *   )
 * )
 */
$app->delete('/admin/categorias/:id', function ($id) use ($app, $categorias, $projetos){
    $res = $categorias->delete(array('id' => $id), 'id = :id');

    if ($res) {
        $app->flash('notice', 'Informação excluída com sucesso');
    }else{
        $app->flash('error', 'Não foi possível excluir a informação.');
    }
    $app->flashKeep();

    $app->redirect($app->urlFor('listagem_categorias'));
})->conditions(array('id' => '\d+'));

/**
 *
 * @SWG\Api(
 *   path="/admin/categorias",
 *   description="Listagem de categorias",
 *   @SWG\Operation(method="GET", summary="Listagem de categorias", type="void", nickname="listagemCategorias",
 *      @SWG\ResponseMessage(code=500, message="Problema ao salvar categoria") 
 *   )
 * )
 */
$app->get('/admin/categorias', function () use ($app, $categorias, $projetos) {

    $q = "
        SELECT 
            id AS 'ID', 
            nome AS 'Nome', 
            IF(tipo = 'E', 'Empresa', 'Job') AS Tipo, 
            IF(lang = 'pt', 'Português', 'Inglês') AS 'Idioma',
            ativo AS 'Ativo'
        FROM
            tbl_categorias 
        WHERE
            status = 1
        ORDER BY 2, 4";

    $res = $categorias->findQuery($q);
										
	$colunas = array_keys($res->res[0]);
	
    $app->render('admin/categorias/listagem.html.twig', array('categorias'=>$res->res, 'colunas'=>$colunas));
})->name('listagem_categorias');

/**
 *
 * @SWG\Api(
 *   path="/admin/categorias/{id}",
 *   description="Busca categoria pelo id",
 *   @SWG\Operation(method="GET", summary="Busca categoria pelo id", type="void", nickname="listagemCategorias",
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
$app->get('/admin/categorias/:id', function ($id) use ($app, $categorias, $projetos){
    $res   = $categorias->findById($id);

    if ($res->cod == 404) {
        $app->notFound();
    }else{
        $app->render('admin/categorias/editar.html.twig', array('categorias'=>$res->res));
    }
})->name('busca_categorias');