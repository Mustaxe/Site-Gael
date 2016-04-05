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

use app\model\dao\CasesDao;
use app\model\dao\DestaquesDao;
use app\model\Destaques;
use app\model\Projetos;
use app\model\Arquivo;

$destaques = new Destaques(array(), $app->db);
$projetos = new Projetos(array(), $app->db);
$arquivos = new Arquivo(array(), $app->db);


/**
*
* TODO_CONFIG: Config de path para upload
*
*/
$_URL_UPLOAD = array(
    'localhost' => '/git/site_gael/Site-Gael/service/web/uploads/',
    'localhost:8080' => '/git/site_gael/Site-Gael/service/web/uploads/',
    'homologacao.gael.ag' => '/service/web/uploads/',
    'gael.ag' => '/service/web/uploads/'
);
$URL_UPLOAD = $_URL_UPLOAD[$_SERVER['HTTP_HOST']];

$_URL_UPLOAD_MOBILE = array(
    'localhost' => '/git/site_gael/Site-Gael/service/web/uploads/',
    'localhost:8080' => '/git/site_gael/Site-Gael/service/web/uploads/',
    'homologacao.gael.ag' => '/service/web/uploads/',
    'gael.ag' => '/service/web/uploads/'
);
$URL_UPLOAD_MOBILE = $_URL_UPLOAD_MOBILE[$_SERVER['HTTP_HOST']];




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
$app->map('/admin/destaques/novo', function () use ($app, $destaques, $projetos, $arquivos, $URL_UPLOAD, $URL_UPLOAD_MOBILE) {

    $params = $app->request;

    if ($params->isPost())
    {
        /**
        * Lang
        */
        $lang = trim($params->post('lang'));
        if($lang == 'pt' || $lang == 'en')
        {
            $destaques->lang = $lang;
        }
        else
        {
            /**
            * Lang default
            */
            $destaques->lang = $lang;
        }

        $destaques->titulo = $params->post('titulo');

        $destaques->link = $params->post('link');

        $Imagem = isset($_FILES['imagem']) ? $_FILES['imagem'] : '';
        if (empty($Imagem) || $Imagem == 'undefined')
        {
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

		$destaques->caseid = $params->post('caseid');

        $res = $destaques->create();

        if ($res->cod == 200) {

			/***************** Redimensionamento de imagens para Mobile *******************/
            
			$baseData = $arquivos->findById($ThumbUpload->res['id'], array("id", "nome", "extensao"));
			$base = new Imagick($_SERVER['DOCUMENT_ROOT'] . $URL_UPLOAD . $baseData->res['extensao'] . '/' . $baseData->res['nome']);

			$maskData = $arquivos->findById($ImagemUpload->res['id'], array("id", "nome", "extensao"));
			$mask = new Imagick($_SERVER['DOCUMENT_ROOT'] . $URL_UPLOAD . $maskData->res['extensao'] . '/' . $maskData->res['nome']);

            $d = $base->getImageGeometry();
            $w = ($d['width'] * 0.7);

            $maskWidth = $mask->getImageWidth();
            $maskHeight = $mask->getImageHeight();

            $baseWidth = $base->getImageWidth();
	        $baseHeight = $base->getImageHeight();

            $left = ($maskWidth - $baseWidth + 100);
            $top = ($maskHeight - $baseHeight + 100);

            $base->resizeImage($w,0,Imagick::FILTER_LANCZOS,1);
            //$base->writeImage($_SERVER['DOCUMENT_ROOT'] . '/service/web/uploads/mobile/' . substr($maskData->res['nome'], 0, strlen($maskData->res['nome']) -4) . '-teste.jpg');

			$mask->compositeImage($base, Imagick::COMPOSITE_DEFAULT, $left, $top, Imagick::CHANNEL_ALPHA);

			$mask->writeImage($_SERVER['DOCUMENT_ROOT'] . $URL_UPLOAD_MOBILE . substr($maskData->res['nome'], 0, strlen($maskData->res['nome']) -4) . '-1920.jpg');

			$mask = new Imagick($_SERVER['DOCUMENT_ROOT'] . $URL_UPLOAD_MOBILE . substr($maskData->res['nome'], 0, strlen($maskData->res['nome']) -4) . '-1920.jpg');
			$mask->setImageCompression(imagick::COMPRESSION_JPEG);
			$mask->setImageCompressionQuality(70);
			$mask->resizeImage(1024,0,Imagick::FILTER_LANCZOS,1);
			$mask->writeImage($_SERVER['DOCUMENT_ROOT'] . $URL_UPLOAD_MOBILE . substr($maskData->res['nome'], 0, strlen($maskData->res['nome']) -4) . '-1024.jpg');

			$mask = new Imagick($_SERVER['DOCUMENT_ROOT'] . $URL_UPLOAD_MOBILE . substr($maskData->res['nome'], 0, strlen($maskData->res['nome']) -4) . '-1024.jpg');
			$mask->setImageCompression(imagick::COMPRESSION_JPEG);
			$mask->setImageCompressionQuality(70);
			$mask->resizeImage(640,0,Imagick::FILTER_LANCZOS,1);
			$mask->writeImage($_SERVER['DOCUMENT_ROOT'] . $URL_UPLOAD_MOBILE . substr($maskData->res['nome'], 0, strlen($maskData->res['nome']) -4) . '-640.jpg');
            
            
			/***************** Redimensionamento de imagens para Mobile *******************/

		    $app->flash('notice', 'Informação adicionada com sucesso');

        } else {
            $app->flash('error', 'Não foi possível adicionar a informação.');

        }
        $app->flashKeep();

        $app->redirect($app->urlFor('listagem_destaques'));

    } else {
		// Pega cases para enviar ao template e montar select com ID e descricao
		$cases = new CasesDao($app);
		$res   = $cases->getCasesComArquivosListagem();

		$colunas = array_keys($res->res[0]);

	}

	$app->render('admin/destaques/novo.html.twig', array('cases'=>$res->res, 'colunas'=>$colunas));

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


    /**
    * Lang
    */
    $lang = $params->post('lang');
    if($lang == 'pt' || $lang == 'en')
    {
        $destaques->lang = $lang;
    }
    else
    {
        /**
        * Lang default
        */
        $destaques->lang = 'pt';
    }


	$destaques->titulo = $params->post('titulo');

	$destaques->link = $params->post('link');

	$destaques->ativo = $params->post('ativo');

	$destaques->caseid = $params->post('caseid');

	$destaques->ativo = $params->post('ativo');

    if(strlen($_FILES['imagem']['name']) > 0) {
        $uploader = $app->uploader;
        $uploader->setArquivo('imagem');
        $uploader->setTipo('destaques');

        $ImagemUpload = $uploader->salva();

        $destaques->imagem = $ImagemUpload->res['id'];
    }

    if(strlen($_FILES['thumb']['name']) > 0) {
        $uploader = $app->uploader;
        $uploader->setArquivo('thumb');
        $uploader->setTipo('destaques');

        $ThumbUpload = $uploader->salva();

        $destaques->thumb = $ThumbUpload->res['id'];
    }

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
    $destaques = new DestaquesDao($app);
    $res   = $destaques->getDestaquesComArquivosListagem();

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
    $destaques = new DestaquesDao($app);
    $res   = $destaques->getDestaquesComArquivosUnico($id);

    if ($res->cod == 404)
    {
        $app->notFound();
    }
    else
    {
		// Pega cases para enviar ao template e montar select com ID e descricao
		$cases = new CasesDao($app);
		$resCases   = $cases->getCasesComArquivosListagem();

		$colunas = array_keys($resCases->res[0]);

        $app->render('admin/destaques/editar.html.twig', array('destaques' => $res->res, 'cases' => $resCases->res, 'colunas' => $colunas));
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


$app->get('/admin/merge-destaques', function ($id) use ($app, $destaques, $arquivos, $URL_UPLOAD, $URL_UPLOAD_MOBILE) {
    

	$R = $destaques->findAll();

	for($i=0; $i < sizeof($R->res); $i++) {

		$baseData = $arquivos->findById($R->res[$i]['thumb'], array("id", "nome", "extensao"));
		$base = new Imagick($_SERVER['DOCUMENT_ROOT'] . $URL_UPLOAD . $baseData->res['extensao'] . '/' . $baseData->res['nome']);

		$maskData = $arquivos->findById($R->res[$i]['imagem'], array("id", "nome", "extensao"));
		$mask = new Imagick($_SERVER['DOCUMENT_ROOT'] . $URL_UPLOAD . $maskData->res['extensao'] . '/' . $maskData->res['nome']);

        $d = $base->getImageGeometry();
        $w = ($d['width'] * 0.7);

        $maskWidth = $mask->getImageWidth();
        $maskHeight = $mask->getImageHeight();

        $baseWidth = $base->getImageWidth();
        $baseHeight = $base->getImageHeight();

        $left = ($maskWidth - $baseWidth + 100);
        $top = ($maskHeight - $baseHeight + 100);

        $base->resizeImage($w,0,Imagick::FILTER_LANCZOS,1);

		$mask->compositeImage($base, Imagick::COMPOSITE_DEFAULT, $left, $top, Imagick::CHANNEL_ALPHA);

		$mask->writeImage($_SERVER['DOCUMENT_ROOT'] . $URL_UPLOAD_MOBILE . substr($maskData->res['nome'], 0, strlen($maskData->res['nome']) -4) . '-1920.jpg');

		$mask = new Imagick($_SERVER['DOCUMENT_ROOT'] . $URL_UPLOAD_MOBILE . substr($maskData->res['nome'], 0, strlen($maskData->res['nome']) -4) . '-1920.jpg');

		$mask->resizeImage(1024,0,Imagick::FILTER_LANCZOS,1);
		$mask->setImageCompression(imagick::COMPRESSION_JPEG);
		$mask->setImageCompressionQuality(70);
		$mask->writeImage($_SERVER['DOCUMENT_ROOT'] . $URL_UPLOAD_MOBILE . substr($maskData->res['nome'], 0, strlen($maskData->res['nome']) -4) . '-1024.jpg');

		$mask = new Imagick($_SERVER['DOCUMENT_ROOT'] . $URL_UPLOAD_MOBILE . substr($maskData->res['nome'], 0, strlen($maskData->res['nome']) -4) . '-1024.jpg');
		$mask->setImageCompression(imagick::COMPRESSION_JPEG);
		$mask->setImageCompressionQuality(70);
		$mask->resizeImage(640,0,Imagick::FILTER_LANCZOS,1);
		$mask->writeImage($_SERVER['DOCUMENT_ROOT'] . $URL_UPLOAD_MOBILE . substr($maskData->res['nome'], 0, strlen($maskData->res['nome']) -4) . '-640.jpg');

	}

});
