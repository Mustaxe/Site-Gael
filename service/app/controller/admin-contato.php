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
$app->post('/admin/contato/:id', function ($id) use ($app, $contato, $projetos) {
    $params  = $app->request;

    $res = $contato->findById($id);

    if ($res->cod == 404) {
        $app->notFound();
        exit;
    }

    /**
    *
    * IMPORTANT: Separador de informações ";"    
    *
    */

    /**
    * Emails
    */
    $email1 = trim($params->post('email1'));
    $email2 = trim($params->post('email2'));

    $contato_email = $email1 . ';' . $email2;
    $contato->contato_email = $contato_email;


    /**
    * Fones
    */
    $fone1 = trim($params->post('fone1'));
    $fone2 = trim($params->post('fone2'));

    $contato_fone = $fone1 . ';' . $fone2;
    $contato->contato_fone = $contato_fone;


    /**
    * Enderecos
    */
    $endereco1 = trim($params->post('endereco1'));
    $endereco2 = trim($params->post('endereco2'));

    $contato_endereco = $endereco1 . ';' . $endereco2;
    $contato->contato_endereco = $contato_endereco;


    /*
    $contato_email = trim($params->post('contato_email'));
    if (!empty($contato_email) && $contato_email !== 'undefined')
    {
        $contato->contato_email = $contato_email;
    }

    $contato_fone = trim($params->post('contato_fone'));
    if (!empty($contato_fone) && $contato_fone !== 'undefined')
    {
        $contato->contato_fone = $contato_fone;
    }

    $contato_endereco = trim($params->post('contato_endereco'));
    if (!empty($contato_endereco) && $contato_endereco !== 'undefined')
    {
        $contato->contato_endereco = $contato_endereco;
    }
    */

    $video = trim($params->post('video'));
    if (!empty($video) && $video !== 'undefined')
    {
        $contato->video = $video;
    }

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
    
	//$colunas = array('ID', 'Email de contato', 'Fone de contato', 'Endereço', 'Projeto', 'Ativo', 'Vídeo');
	//$colunas_key = array_keys($res->res[0]);    
    
    $colunas = array('ID', 'Projeto', 'Vídeo', 'Ativo'); 
    $colunas_key = array('id', 'projeto', 'video', 'status');
	
    $app->render('admin/contato/listagem.html.twig', array('contato' => $res->res, 'colunas' => $colunas, 'colunas_key' => $colunas_key));
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

    if ($res->cod == 404)
    {
        $app->notFound();
    }
    else
    {       

        $emails = explode(';', $res->res['contato_email']);
        $res->res['email1'] = isset($emails[0]) ? $emails[0] : "";
        $res->res['email2'] = isset($emails[1]) ? $emails[1] : "";

        $fones = explode(';', $res->res['contato_fone']);
        $res->res['fone1'] = isset($fones[0]) ? $fones[0] : "";
        $res->res['fone2'] = isset($fones[1]) ? $fones[1] : "";

        $enderecos = explode(';', $res->res['contato_endereco']);
        $res->res['endereco1'] = isset($enderecos[0]) ? $enderecos[0] : "";
        $res->res['endereco2'] = isset($enderecos[1]) ? $enderecos[1] : "";

        $app->render('admin/contato/editar.html.twig', array('contato'=>$res->res));
    }

})->name('busca_contato');


