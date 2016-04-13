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


/**
*
*
* Login de clietes
*
*/
$app->map('/cliente/dfasdf', function () use ($app, $cases, $projetos, $categorias, $arquivos, $URL_UPLOAD){

    
    $app->redirect($app->urlFor('cliente_login'));
    //$app->render('admin/cases/novo.html.twig');


    $params = $app->request;

    if ($params->isPost())
    {

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

		/**
        *
        * Converte array para lista
        *
        */
		$categorias = $params->post('categorias');
		$categorias = implode(",", $categorias);
		$cases->categorias = $categorias;

        $ImagemThumb = isset($_FILES['imagem_thumb']) ? $_FILES['imagem_thumb'] : '';
        if (empty($ImagemThumb) || $ImagemThumb == 'undefined')
        {
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

			$baseData = $arquivos->findById($ImagemIntegraUpload->res['id'], array("id", "nome", "extensao"));
			
			$base = new Imagick($_SERVER['DOCUMENT_ROOT'] . $URL_UPLOAD  . $baseData->res['extensao'] . '/' . $baseData->res['nome']);
			$base->setImageCompression(Imagick::COMPRESSION_JPEG); 
			$base->setImageCompressionQuality(70);
			$base->resizeImage(1024,0,Imagick::FILTER_LANCZOS,1);
			$base->writeImage($_SERVER['DOCUMENT_ROOT'] . $URL_UPLOAD  . $baseData->res['extensao'] . '/' . substr($baseData->res['nome'], 0, strlen($baseData->res['nome']) -4) . '-1024.jpg');
			
			$base = new Imagick($_SERVER['DOCUMENT_ROOT'] . $URL_UPLOAD  . $baseData->res['extensao'] . '/' . $baseData->res['nome']);
			$base->setImageCompression(Imagick::COMPRESSION_JPEG); 
			$base->setImageCompressionQuality(70);
			$base->resizeImage(640,0,Imagick::FILTER_LANCZOS,1);
			$base->writeImage($_SERVER['DOCUMENT_ROOT'] . $URL_UPLOAD  . $baseData->res['extensao'] . '/' . substr($baseData->res['nome'], 0, strlen($baseData->res['nome']) -4) . '-640.jpg');
			
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
        * Obtem a maior ordem (MAX)
        */
        $q = "
            SELECT 
                ordem
            FROM
                tbl_cases
            WHERE
                status = 1 AND ativo = 1 AND lang = '" . $lang . "'
            ORDER B
                ordem DESC
            LIMIT 1";

		$ordem = $cases->Query($q);

		
		/**
        *
        * Se for uma nova ordem, pega última posição existente e soma 1
        *
        */
		if($params->post('ordem') == 0)
		{
			$cases->ordem = ($ordem->res[0]['ordem'] + 1);
		}
		else
		{
            $q = "
                UPDATE
                    tbl_cases
                SET
                    ordem = (ordem + 1)
                WHERE
                    ordem >= " . $params->post('ordem') . " AND ativo = 1 AND status = 1 AND lang = '" . $lang . "'";

			$cases->Query($q);
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
    }
    else
    {

        /**
        *
        * GET
        *
        * Por default o idioma é PT
        *
        *
        */
        //$langDefault = 'pt';


        /**
        *
        * Obtemos todas as empresas independente de idioma
        *
        */
        /*
        $q = "
            SELECT 
                *
            FROM
                tbl_categorias
            WHERE
                status = 1 ORDER BY 2";
		$empresas = $categorias->Query($q);
        */


        /**
        *
        * Obtemos os todos os Jobs de de acordo com o idioma
        *
        */
        /*
        $q = "
            SELECT 
                *
            FROM
                tbl_categorias
            WHERE
                status = 1 AND lang = '" . $langDefault . "' ORDER BY 2";
        $jobs = $categorias->Query($q);
        */

        /*
        $q = "
            SELECT 
                CONCAT(ordem, ' - ', descricao) AS ordemTitulo,
                ordem 
            FROM
                tbl_cases
            WHERE
                status = 1 AND ativo = 1 AND lang = '" . $langDefault . "'
            ORDER BY
                ordem";
		$resOrdem = $cases->Query($q);
        */
        $app->response->headers->set('Content-Type', 'text/html;charset=utf-8');
	    $app->render('cliente/clientes/listagem.html.twig', array('empresas' => '', 'jobs' => '', 'ordenacao' => ''));
	}
})->via("GET")->name('xxxxxxxxxxxxxx');


/**
*
* Adiciona cliente
*
*/
$app->map('/cliente/novo', function ($categoria, $lang) use ($app, $cases, $projetos, $categorias, $arquivos) {

    $q = "
        SELECT 
            CONCAT(ordem, ' - ', descricao) AS ordemTitulo,
            ordem 
        FROM
            tbl_cases
        WHERE
            status = 1 AND ativo = 1 AND lang = '" . $lang . "'
        ORDER BY
            ordem";

    $resOrdem = $cases->Query($q);

    /**
    * Constroi o HTML
    */
    $html = '<option value="0">Novo</option>';
    foreach($resOrdem->res as $item) {
        $html .= '<option value="' . $item['ordem'] . '">' . $item['ordemTitulo'] . '</option>';
    }

    $app->response->headers->set('Content-Type', 'text/html;charset=utf-8');
    echo $html;    
})->via("GET")->name('cliente_novoxxx');