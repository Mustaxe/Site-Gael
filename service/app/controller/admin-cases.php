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

use app\model\dao\CasesDao;
use app\model\Cases;
use app\model\Categorias;
use app\model\Projetos;
use app\model\Arquivo;

$cases = new Cases(array(), $app->db);
$categorias = new Categorias(array(), $app->db);
$projetos = new Projetos(array(), $app->db);
$arquivos = new Arquivo(array(), $app->db);

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
$app->map('/admin/cases/novo', function () use ($app, $cases, $projetos, $categorias, $arquivos){
    $params = $app->request;

    if ($params->isPost()) {


    	/**
    	* Idioma
    	*/
    	$lang = $params->post('lang');
    	if($lang == 'pt' || $lang == 'en')
    	{
    		$cases->lang = $lang;
    	}
    	else
    	{
    		/**
    		* Idioma padrão
    		*/
    		$cases->lang = 'pt';
    	}

        $cases->titulo = $params->post('titulo');

        $cases->descricao = $params->post('descricao');

        $cases->texto = $params->post('editor_content');

		// Converte array para lista
		$categorias = $params->post('categorias');
		$categorias = implode(",", $categorias);
		$cases->categorias = $categorias;

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

		$_FILES = $cases->reArrayFiles($_FILES['imagem_integra']);
		
		$casesImagens = '';
		for($i=0; $i < sizeof($_FILES); $i++) {

			$uploader = $app->uploader;
			$uploader->setTipo('cases');

			$url = '';
			if($_POST['url_video'][$i] != '') {
				$url = $_POST['url_video'][$i];
			}

			$uploader->setUrl($url);
			$uploader->setArquivo($i);
			$ImagemIntegraUpload = $uploader->salva();
			

			/***************** Redimensionamento de imagens para Mobile *******************/
			
            /**
            *
            * TODO_CONFIG: Config de path para upload
            *
            */
            //$_path = '/git/site_gael/Site-Gael/service/web/uploads/';
            $_path = '/service/web/uploads/';


			$baseData = $arquivos->findById($ImagemIntegraUpload->res['id'], array("id", "nome", "extensao"));
			
			$base = new Imagick($_SERVER['DOCUMENT_ROOT'] . $_path . $baseData->res['extensao'] . '/' . $baseData->res['nome']);
			$base->setImageCompression(Imagick::COMPRESSION_JPEG); 
			$base->setImageCompressionQuality(70);
			$base->resizeImage(1024,0,Imagick::FILTER_LANCZOS,1);
			$base->writeImage($_SERVER['DOCUMENT_ROOT'] . $_path . $baseData->res['extensao'] . '/' . substr($baseData->res['nome'], 0, strlen($baseData->res['nome']) -4) . '-1024.jpg');
			
			$base = new Imagick($_SERVER['DOCUMENT_ROOT'] . $_path . $baseData->res['extensao'] . '/' . $baseData->res['nome']);
			$base->setImageCompression(Imagick::COMPRESSION_JPEG); 
			$base->setImageCompressionQuality(70);
			$base->resizeImage(640,0,Imagick::FILTER_LANCZOS,1);
			$base->writeImage($_SERVER['DOCUMENT_ROOT'] . $_path . $baseData->res['extensao'] . '/' . substr($baseData->res['nome'], 0, strlen($baseData->res['nome']) -4) . '-640.jpg');
			
			/***************** Redimensionamento de imagens para Mobile *******************/
			
			$casesImagens .= $ImagemIntegraUpload->res['id'] . ',';
		}
		$cases->imagens = substr($casesImagens, 0, (strlen($casesImagens)-1));



		// COMENTADOS ABAIXO - QUANTIDADE DE UPLOADS FIXOS

		/* 		
		if(strlen($_FILES['imagem_integra1']['name']) > 0) {
			$uploader = $app->uploader;
			$uploader->setArquivo('imagem_integra1');
			$uploader->setTipo('cases');
			$ImagemIntegraUpload = $uploader->salva();
			$cases->imagem_integra1 = $ImagemIntegraUpload->res['id'];
		}

		if(strlen($_FILES['imagem_integra2']['name']) > 0) {
			$uploader = $app->uploader;
			$uploader->setArquivo('imagem_integra2');
			$uploader->setTipo('cases');
			$ImagemIntegraUpload = $uploader->salva();
			$cases->imagem_integra2 = $ImagemIntegraUpload->res['id'];
		}

		if(strlen($_FILES['imagem_integra3']['name']) > 0) {
			$uploader = $app->uploader;
			$uploader->setArquivo('imagem_integra3');
			$uploader->setTipo('cases');
			$ImagemIntegraUpload = $uploader->salva();
			$cases->imagem_integra3 = $ImagemIntegraUpload->res['id'];
		}

		if(strlen($_FILES['imagem_integra4']['name']) > 0) {
			$uploader = $app->uploader;
			$uploader->setArquivo('imagem_integra4');
			$uploader->setTipo('cases');
			$ImagemIntegraUpload = $uploader->salva();
			$cases->imagem_integra4 = $ImagemIntegraUpload->res['id'];
		}

		if(strlen($_FILES['imagem_integra5']['name']) > 0) {
			$uploader = $app->uploader;
			$uploader->setArquivo('imagem_integra5');
			$uploader->setTipo('cases');
			$ImagemIntegraUpload = $uploader->salva();
			$cases->imagem_integra5 = $ImagemIntegraUpload->res['id'];
		}
		*/

        $cases->ativo = $params->post('ativo');


        /**
        * Obtem a maior ordem
        */
		$ordem = $cases->Query("SELECT ordem FROM tbl_cases WHERE status = 1 AND ativo = 1 ORDER BY ordem DESC LIMIT 1");

		
		// Se for uma nova ordem, pega última posição existente e soma 1
		if($params->post('ordem') == 0)
		{
			$cases->ordem = ($ordem->res[0]['ordem'] + 1);
		}
		else
		{
			$cases->Query("UPDATE tbl_cases SET ordem = (ordem + 1) WHERE ordem >= " . $params->post('ordem') . " AND ativo = 1 AND status = 1");
			$cases->ordem = $params->post('ordem');
		}

        $res = $cases->create();

        if ($res->cod == 200)
        {
            $app->flash('notice', 'Informação adicionada com sucesso');
        }
        else
        {
            $app->flash('error', 'Não foi possível adicionar a informação.');
        }
        $app->flashKeep();

        $app->redirect($app->urlFor('listagem_cases'));
		$app->render('admin/cases/novo.html.twig');

    } else {

		$res = $categorias->findAll();
		$resOrdem = $cases->Query("SELECT CONCAT(ordem, ' - ', descricao) AS ordemTitulo, ordem FROM tbl_cases WHERE status = 1 AND ativo = 1 ORDER BY ordem");
	    $app->render('admin/cases/novo.html.twig', array('categorias'=>$res->res, 'ordenacao'=>$resOrdem->res));
	}
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
$app->post('/admin/cases/:id', function ($id) use ($app, $cases, $projetos, $arquivos){
    $params  = $app->request;

    $res = $cases->findById($id);
	
    if ($res->cod == 404) {
        $app->notFound();
        exit;
    }

	// Converte array para lista
	$categorias = $params->post('categorias');
	$categorias = implode(",", $categorias);
	$cases->categorias = $categorias;


	/**
    * Lang
    */    
    $lang = trim($params->post('lang'));
    if ($lang == 'pt' || $lang == 'en')
    {
        $cases->lang = $lang;
    }
    else 
    {
        /**
        * Lang padrão do sistema
        */
        $cases->lang = 'pt';
    }


	$cases->texto = $params->post('editor_content');
	
	$cases->ordem = $params->post('ordem');

    $titulo = trim($params->post('titulo'));
    if(!empty($titulo) && $titulo !== 'undefined')
    {
    	$cases->titulo = $titulo;
	}

    $descricao = trim($params->post('descricao'));
    if(!empty($descricao) && $descricao !== 'undefined')
    {
    	$cases->descricao = $descricao;
    }


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
	/* if(strlen($_FILES['imagem_integra1']['name']) > 0) {
        $uploader = $app->uploader;
        $uploader->setArquivo('imagem_integra1');
        $uploader->setTipo('cases');
        $ImagemIntegraUpload = $uploader->salva();
        $cases->imagem_integra1 = $ImagemIntegraUpload->res['id'];
    } */

    //$ImagemIntegra = isset($_FILES['imagem_integra2']) ? $_FILES['imagem_integra2'] : '';
    //if (!empty($ImagemIntegra) && $ImagemIntegra !== 'undefined') {
	/* if(strlen($_FILES['imagem_integra2']['name']) > 0) {
        $uploader = $app->uploader;
        $uploader->setArquivo('imagem_integra2');
        $uploader->setTipo('cases');
        $ImagemIntegraUpload = $uploader->salva();
        $cases->imagem_integra2 = $ImagemIntegraUpload->res['id'];
    } */

    //$ImagemIntegra = isset($_FILES['imagem_integra3']) ? $_FILES['imagem_integra3'] : '';
    //if (!empty($ImagemIntegra) && $ImagemIntegra !== 'undefined') {
	/* if(strlen($_FILES['imagem_integra3']['name']) > 0) {
        $uploader = $app->uploader;
        $uploader->setArquivo('imagem_integra3');
        $uploader->setTipo('cases');
        $ImagemIntegraUpload = $uploader->salva();
        $cases->imagem_integra3 = $ImagemIntegraUpload->res['id'];
    } */

    //$ImagemIntegra = isset($_FILES['imagem_integra4']) ? $_FILES['imagem_integra4'] : '';
    //if (!empty($ImagemIntegra) && $ImagemIntegra !== 'undefined') {
	/* if(strlen($_FILES['imagem_integra4']['name']) > 0) {
        $uploader = $app->uploader;
        $uploader->setArquivo('imagem_integra4');
        $uploader->setTipo('cases');
        $ImagemIntegraUpload = $uploader->salva();
        $cases->imagem_integra4 = $ImagemIntegraUpload->res['id'];
    } */

    //$ImagemIntegra = isset($_FILES['imagem_integra5']) ? $_FILES['imagem_integra5'] : '';
    //if (!empty($ImagemIntegra) && $ImagemIntegra !== 'undefined') {
	/* if(strlen($_FILES['imagem_integra5']['name']) > 0) {
        $uploader = $app->uploader;
        $uploader->setArquivo('imagem_integra5');
        $uploader->setTipo('cases');
        $ImagemIntegraUpload = $uploader->salva();
        $cases->imagem_integra5 = $ImagemIntegraUpload->res['id'];
    } */


    /**
    *
    * TODO_CONFIG: Config de path para upload
    *
    */
    //$_path = '/git/site_gael/Site-Gael/service/web/uploads/';
    $_path = '/service/web/uploads/';

	$_FILES = $cases->reArrayFiles($_FILES['imagem_integra']);

	$casesImagens = '';
	for($i=0; $i < sizeof($_FILES); $i++) {
		if($_FILES[$i]['name'] != '') {
			$uploader = $app->uploader;
			$uploader->setTipo('cases');

			$url = '';
			if($_POST['url_video'][$i] != '') {
				$url = $_POST['url_video'][$i];
			}

			$uploader->setUrl($url);
			$uploader->setArquivo($i);
			$ImagemIntegraUpload = $uploader->salva();
			
			/***************** Redimensionamento de imagens para Mobile *******************/			

			$baseData = $arquivos->findById($ImagemIntegraUpload->res['id'], array("id", "nome", "extensao"));
			
			$base = new Imagick($_SERVER['DOCUMENT_ROOT'] . $_path . $baseData->res['extensao'] . '/' . $baseData->res['nome']);
			$base->setImageCompression(imagick::COMPRESSION_JPEG); 
			$base->setImageCompressionQuality(70);
			$base->resizeImage(1024,0,Imagick::FILTER_LANCZOS,1);
			$base->writeImage($_SERVER['DOCUMENT_ROOT'] . $_path . $baseData->res['extensao'] . '/' . substr($baseData->res['nome'], 0, strlen($baseData->res['nome']) -4) . '-1024.jpg');
			
			$base = new Imagick($_SERVER['DOCUMENT_ROOT'] . $_path . $baseData->res['extensao'] . '/' . $baseData->res['nome']);
			$base->setImageCompression(imagick::COMPRESSION_JPEG); 
			$base->setImageCompressionQuality(70);
			$base->resizeImage(640,0,Imagick::FILTER_LANCZOS,1);
			$base->writeImage($_SERVER['DOCUMENT_ROOT'] . $_path . $baseData->res['extensao'] . '/' . substr($baseData->res['nome'], 0, strlen($baseData->res['nome']) -4) . '-640.jpg');
			
			/***************** Redimensionamento de imagens para Mobile *******************/
			
			$casesImagens .= $ImagemIntegraUpload->res['id'] . ',';
		}
	}

	if($params->post('imagens_atuais') != '' && $casesImagens != '') {
		$cases->imagens = $params->post('imagens_atuais') . ',' . substr($casesImagens, 0, (strlen($casesImagens)-1));
	}else if( $casesImagens != '') {
        $cases->imagens = substr($casesImagens, 0, (strlen($casesImagens)-1));
	}

	
    $ativo = trim($params->post('ativo'));
	
    if ($ativo == '0') {
		// Subtrai 1 em todas as ordens dos cases maiores que o id
		$cases->Query("UPDATE tbl_cases SET ordem = (ordem - 1) WHERE ordem > " . $res->res['ordem'] . " AND ativo = 1 AND status = 1");
		$cases->ordem = 0;
	}
	
	if($ativo == 1) {
		// Se a posição escolhida for 2 e a atual for 1, inverte as posições
		if($res->res['ordem'] == 1) {
			$cases->Query("UPDATE tbl_cases SET ordem = 1 WHERE ordem = 2 AND ativo = 1 AND status = 1");
			$cases->ordem = 2;
			
		// Senão, soma 1 na ordem maior que a ordem selecionada
		} else {
			// Pega ultimo case
			$ordemUltimo = $cases->Query("SELECT ordem
											FROM tbl_cases
											WHERE status = 1 AND ativo = 1
											ORDER BY ordem DESC LIMIT 1");
			
			if($params->post('ordem') == 0) {
				$cases->ordem = ($ordemUltimo->res[0]['ordem'] + 1);
			
			} else {
				if($ordemUltimo->res[0]['ordem'] == $params->post('ordem')) {
					$cases->Query("UPDATE tbl_cases SET ordem = (ordem + 1) WHERE ordem = " . $params->post('ordem') . " AND ativo = 1 AND status = 1");
					$cases->ordem = $params->post('ordem');

				} else {
					$ordemAtual = $res->res['ordem'];

					if($ordemAtual > $params->post('ordem')) {
						//$cases->Query("UPDATE tbl_cases SET ordem = (ordem + 1) WHERE ordem >= " . $params->post('ordem') . " AND ativo = 1 AND status = 1 AND id <> " . $id);				
						$cases->Query("UPDATE tbl_cases SET ordem = (ordem + 1) WHERE ordem >= " . $params->post('ordem') . " AND ordem < " . $ordemAtual . " AND ativo = 1 AND status = 1");
					
					} else {
						$cases->Query("UPDATE tbl_cases SET ordem = (ordem - 1) WHERE ordem > " . $ordemAtual . " AND ordem <= " . $params->post('ordem') . " AND ativo = 1 AND status = 1");
					
					}
				
				}
			}
		}
		
	}
	
	$cases->ativo = $ativo;
	
    $vars = $cases->getVariables();

    if (empty($vars)) {
        $app->flash('error', 'Nenhuma informação foi salva.');
        $app->redirect($app->urlFor('busca_cases', array('id' => $id)));
    }
	
	// Salva case com os novos dados, inclusive ordem.
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
$app->delete('/admin/cases/:id', function ($id) use ($app, $cases, $projetos) {
    $res = $cases->delete(array('id' => $id), 'id = :id');

    if ($res) {

		// Ordem atual do case
		$ordemAtual = $cases->Query("SELECT ordem
										FROM tbl_cases
										WHERE id = " . $id);
	
		// Subtrai 1 em todas as ordens dos cases maiores que o id
		$cases->Query("UPDATE tbl_cases SET ordem = (ordem - 1) WHERE ordem > " . $ordemAtual->res[0]['ordem'] . " AND ativo = 1 AND status = 1");
		
        $app->flash('notice', 'Informação excluída com sucesso');
		
    }else{
        $app->flash('error', 'Não foi possível excluir a informação.');
		
    }
	
    $app->flashKeep();

    $app->redirect($app->urlFor('listagem_cases'));
})->conditions(array('id' => '\d+'));


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
$app->get('/admin/cases', function () use ($app, $cases, $projetos) {
    $cases = new CasesDao($app);
    $res   = $cases->getCasesComArquivosListagem();

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
$app->get('/admin/cases/:id', function ($id) use ($app, $cases, $projetos, $categorias) {
    
    $casesDao = new CasesDao($app);
    $res   = $casesDao->getCasesComArquivosUnico($id);

    if ($res->cod == 404)
    {
        $app->notFound();
    }
    else
    {
		$resCat = $categorias->findAll();

		$query = "
			SELECT 
				CONCAT(ordem, ' - ', titulo , ' | ' , descricao) AS ordemTitulo,
				ordem
			FROM
				tbl_cases
			WHERE
				status = 1 AND ativo = 1
			ORDER BY
				ordem";

		$resOrdem = $cases->Query($query);

        $app->render('admin/cases/editar.html.twig', array('cases'=>$res->res, 'categorias'=>$resCat->res, 'ordenacao'=>$resOrdem->res));
    }
})->name('busca_cases');


/**
 *
 * @SWG\Api(
 *   path="/admin/cases/imagem/{caseid}/{id}",
 *   description="Remover imagem de cases",
 *   @SWG\Operation(method="DELETE", summary="Remover imagem de cases", type="void", nickname="removerImagemCases",
 *      @SWG\ResponseMessage(code=500, message="Problema ao remover imagem de cases")
 *   )
 * )
 */
$app->delete('/admin/cases/imagem/:caseid/:id', function ($caseid, $id) use ($app, $cases){

    //$cases->find(array('idimg' => $id), $campo.' = :idimg');
    //$cases->$campo = null;

    //$res = $cases->save(array('idimg' => $id), $campo.' = :idimg');

    //if ($res->cod == 200) {

	$R = $cases->Query("UPDATE tbl_arquivo
					SET status = 0
					WHERE id = " . $id);

	if($R->cod == 200) {

		$cases->Query("UPDATE tbl_cases
						SET imagens = REPLACE(imagens, CONCAT(IF(LOCATE(',',imagens) > 0, ',', ''), " . $id . "), '')
						WHERE id = " . $caseid);

		$R = array('cod' => 200, 'msg' => 'Exclusão realizada com sucesso');

	} else {
		$R = array('cod' => 500, 'msg' => 'Ocorreu problema na exclusão');

	}

    $app->response->headers->set('Content-Type', 'application/json;charset=utf-8');
    echo json_encode($R);
});


$app->get('/admin/merge-cases', function ($id) use ($app, $cases, $arquivos) {
	
    /**
    *
    * TODO_CONFIG: Config de path para upload
    *
    */
    //$_path = '/git/site_gael/Site-Gael/service/web/uploads/';
    $_path = '/service/web/uploads/';

	$R = $cases->findAll();
	
	for($i=0; $i < sizeof($R->res); $i++) {
		
		echo $R->res[$i]['id'] . '-';
		
		$IMG = explode(",", $R->res[$i]['imagens']);
		
		//echo sizeof($IMG) . '-';
		
		if($R->res[$i]['imagens'] != '' && sizeof($IMG) > 0) {
		
			//$IMG = explode(",", $R->res[$i]['imagens']);

			for($ii = 0; $ii < sizeof($IMG); $ii++) {

				$baseData = $arquivos->findById($IMG[$ii], array("id", "nome", "extensao"));
				
				if($baseData->cod == 200) {
				
					$base = new Imagick($_SERVER['DOCUMENT_ROOT'] . $_path . $baseData->res['extensao'] . '/' . $baseData->res['nome']);
					$base->setImageCompression(imagick::COMPRESSION_JPEG); 
					$base->setImageCompressionQuality(70);
					$base->resizeImage(1024,0,Imagick::FILTER_LANCZOS,1);
					$base->writeImage($_SERVER['DOCUMENT_ROOT'] . $_path . $baseData->res['extensao'] . '/' . substr($baseData->res['nome'], 0, strlen($baseData->res['nome']) -4) . '-1024.jpg');
					
					$base = new Imagick($_SERVER['DOCUMENT_ROOT'] . $_path . $baseData->res['extensao'] . '/' . $baseData->res['nome']);
					$base->setImageCompression(imagick::COMPRESSION_JPEG); 
					$base->setImageCompressionQuality(70);
					$base->resizeImage(640,0,Imagick::FILTER_LANCZOS,1);
					$base->writeImage($_SERVER['DOCUMENT_ROOT'] . $_path . $baseData->res['extensao'] . '/' . substr($baseData->res['nome'], 0, strlen($baseData->res['nome']) -4) . '-640.jpg');
				}
					
			}
		}
	}
});	