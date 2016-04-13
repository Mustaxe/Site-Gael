<?php


use app\model\dao\CasesDao; /**APAGAR**/
use app\model\Cases; /**APAGAR**/
use app\model\Clientes;
use app\model\Pastas;
use app\model\Arquivos;
use app\model\Categorias;
use app\model\Projetos;
use app\model\Arquivo;

$cases = new Cases(array(), $app->db); /**APAGAR**/
$clientes = new Clientes(array(), $app->db);
$pastas = new Pastas(array(), $app->db);
$arquivos = new Arquivos(array(), $app->db);
$categorias = new Categorias(array(), $app->db);
$projetos = new Projetos(array(), $app->db);


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
* 
* Listagem de clientes
* 
*/
$app->get('/admin/clientes', function () use ($app, $clientes, $projetos) {   
	

	$q = "
		SELECT 
			id AS Id,
			empresa AS Empresa,
			nome AS Nome,
			telefones AS Telefone,
			email AS Email,
			usuario AS Usuario,
			ativo AS Ativo
		FROM
			tbl_clientes
		WHERE
			status = 1";

	$res   = $clientes->Query($q);

	$colunas = array_keys($res->res[0]);

	$app->render('admin/clientes/listagem.html.twig', array('clientes' => $res->res, 'colunas' => $colunas));
})->name('listagem_clientes');



/**
*
* 
* 
* Adicionar cliente
*
*/
$app->map('/admin/cliente/novo', function () use ($app, $clientes, $pastas, $categorias, $arquivos, $URL_UPLOAD){
	$params = $app->request;	

	if ($params->isPost())
	{
		/**
		*
		* POST: Adiciona cliente
		*		
		*
		*/		


		$_nome = trim($params->post('nome'));
		$_empresa = trim($params->post('empresa'));		
		$_telefone = trim($params->post('telefone'));
		$_email = trim($params->post('email'));


		/**
		*
		* Verifica se o nome de usuário já existe no Banco de Dados
		*
		*/
		$_usuario = trim($params->post('usuario'));
		$q = "
			SELECT count(*) AS count FROM tbl_clientes WHERE usuario = '" . $_usuario . "'";

		$res = $clientes->Query($q);
		if($res->res[0]['count'] > 0)
		{				
			$app->flash('error', 'Nome de usuário "' . $_usuario . '" já existe no Banco de Dados');
			$app->redirect($app->urlfor('novo_cliente'));
			return;
		}


		/**
		*
		* Verifica se url amigavel já existe no Banco de Dados
		*
		*/
		$_url = trim($params->post('url'));
		$q = "
			SELECT count(*) AS count FROM tbl_clientes WHERE url = '" . $_url . "'";

		$res = $clientes->Query($q);
		if($res->res[0]['count'] > 0)
		{				
			$app->flash('error', 'URL amigável "' . $_url . '" já existe no Banco de Dados');
			$app->redirect($app->urlfor('novo_cliente'));
			return;				
		}



		/**
		*
		* Criptografia MD5 para senha
		*
		*/
		$_senha = md5($params->post('senha'));
		$_ativo = $params->post('ativo');


		$clientes->nome = $_nome;
		$clientes->empresa = $_empresa;
		$clientes->url = $_url;
		$clientes->telefones = $_telefone;
		$clientes->email = $_email;
		$clientes->usuario = $_usuario;
		$clientes->senha = $_senha;
		
		$clientes->ativo = $_ativo;
		$clientes->status = $_ativo;


		$res = $clientes->create();
		if($res->cod == 200)
		{
			/**
			*
			* Obtem o ID do cliente inserido
			*
			*/
			$clienteId = $app->db->lastInsertId();


			/**
			*
			* Cria pasta default para o cliente
			*
			*/
			$pastas->id_cliente = $clienteId;
			$pastas->nome = 'Default';

			$resPasta = $pastas->create();
			if(!($resPasta->cod == 200))
			{
				$app->flash('error', 'Falha ao tentar inserir Pasta de Trabalho no Banco de Dados');
				$app->redirect($app->urlfor('novo_cliente'));
			}
		}
		else
		{
			$app->flash('error', 'Falha ao tentar inserir Usuário no Banco de Dados');
			$app->redirect($app->urlfor('novo_cliente'));
		}
	


		if ($res->cod == 200)
		{
			$app->flash('notice', 'Operação realizado com sucesso ' . $res->res['id']);
		}
		else
		{
			$app->flash('error', 'Falha ao tentar realizar a operação');
		}

		
		$app->redirect($app->urlFor('listagem_clientes'));
		$app->render('admin/clientes/novo.html.twig');
		
	}
	else
	{

		/**
		*
		* GET: Novo cliente
		*
		*/

		$app->render('admin/clientes/novo.html.twig', array());
	}
})->via("POST", "GET")->name('novo_cliente');



/**
* 
* 
* 
* Obtem cliente
* 
*/
$app->get('/admin/cliente/:id', function ($id) use ($app, $clientes, $pastas, $arquivos) {
	
	$q = "
		SELECT * FROM tbl_clientes WHERE id = " . $id;

	$res   = $clientes->Query($q);
	if ($res->cod == 404)
	{
		$app->notFound();
	}
	else
	{


		$_cliente = $res->res[0];



		/**
		*
		* Obtemos todas as pastas referente ao cliente
		*
		*/
		$q = "
			SELECT * FROM tbl_pastas WHERE id_cliente = " . $_cliente['id'] . " AND status = 1";		

		$_pastas = $pastas->Query($q);
		if($_pastas->cod == 200)
		{
			$_pastas = $_pastas->res;			

			/**
			*
			* Obtemos todos os arquivos
			*
			*/
			$_arquivos = array();

			for($i = 0; $i < count($_pastas); $i++)
			{
				$q = "
					SELECT * FROM tbl_arquivos WHERE id_pasta = " . $_pastas[$i]['id'] . " AND status = 1";
				$_arquivosToMerge = $arquivos->Query($q);

				array_merge($_arquivos, $_arquivosToMerge->res);
			}

		}
	   

		$app->render('admin/clientes/editar.html.twig', array('cliente' => $_cliente, 'pastas' => $_pastas, 'arquivos' => $_arquivos));
	}
})->name('obtem_cliente');



/**
*
*
* Editar cliente
*
*/
$app->post('/admin/cliente/editar/:clienteId', function ($clienteId) use ($app, $clientes, $URL_UPLOAD) {
	
	$params  = $app->request;


	/**
	* Obtem o cliente
	*/
	$resCliente = $clientes->findById($clienteId);	
	if ($resCliente->cod == 404)
	{
		var_dump($resCliente);
		return;
		$app->notFound();
		exit;
	}




	$_cliente = $resCliente->res;

	/**
	* Propriedades do formulário
	*/
	$_nome = trim($params->post('nome'));
	$_empresa = trim($params->post('empresa'));		
	$_telefone = trim($params->post('telefone'));
	$_email = trim($params->post('email'));
	$_ativo = trim($params->post('ativo'));
	$_alterarSenha = trim($params->post('alterarSenha'));



	/**
	*
	* Verifica se o nome de usuário já existe no Banco de Dados
	*
	*/
	$_usuario = trim($params->post('usuario'));
	$q = "
		SELECT id, count(*) AS count FROM tbl_clientes WHERE usuario = '" . $_usuario . "'";

	$res = $clientes->Query($q);
	if($res->res[0]['count'] > 0)
	{
		/**
		* Se o ID não for igual ao do cliente que está sendo editado, então o nome de usuario já existe
		*/
		if(!($res->res[0]['id'] == $_cliente['id']))
		{
			$app->flash('error', 'Nome de usuário "' . $_usuario . '" já existe no Banco de Dados');
			$app->redirect($app->urlfor('obtem_cliente', array('id' => $clienteId)));
			return;
		}
	}



	/**
	*
	* Verifica se url amigavel já existe no Banco de Dados
	*
	*/
	$_url = trim($params->post('url'));
	$q = "
		SELECT id, count(*) AS count FROM tbl_clientes WHERE url = '" . $_url . "'";

	$res = $clientes->Query($q);
	if($res->res[0]['count'] > 0)
	{	
		/**
		* Se o ID não for igual ao do cliente que está sendo editado, então o nome de usuario já existe
		*/
		if(!($res->res[0]['id'] == $_cliente['id']))
		{
			$app->flash('error', 'URL amigável "' . $_url . '" já existe no Banco de Dados');
			$app->redirect($app->urlfor('obtem_cliente', array('id' => $clienteId)));
			return;	
		}					
	}



	/**
	*
	* Verifica se é para alterar a senha
	* - A senha deve conter no minimo 6 caracteres
	*
	*/
	$_senha = trim($params->post('senha'));
	if(!empty($_alterarSenha))
	{
		if(!isset($_senha[5]))
		{
			$app->flash('error', 'A senha deve conter no mínimo 6 caracteres');
			$app->redirect($app->urlfor('obtem_cliente', array('id' => $clienteId)));
			return;	
		}

		$clientes->senha = md5($_senha);
	}
	else
	{
		$clientes->senha = $_cliente['senha'];
	}

	/**
	* Persistencia
	*/

	$clientes->nome = $_nome;
	$clientes->empresa = $_empresa;
	$clientes->url = $_url;
	$clientes->telefones = $_telefone;
	$clientes->email = $_email;
	$clientes->usuario = $_usuario;
	$clientes->ativo = $_ativo;
	$clientes->status = $_ativo;



	$res = $clientes->save(array('id' => $clienteId), 'id = :id');
	if($res->cod == 200)
	{
		$app->flash('notice', 'Informações atualizada com sucesso');
		$app->redirect($app->urlfor('obtem_cliente', array('id' => $clienteId)));
		return;
	}


	$app->flash('error', 'Nenhuma informação foi alterada.');
	$app->redirect($app->urlFor('obtem_cliente', array('id' => $clienteId)));
})->name('edita_cliente');



/**
*
* 
*  Desabilita cliente
* 
*/
$app->get('/admin/cliente/apagar/:id', function ($id) use ($app, $cases, $projetos) {
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
})->name('apagar_cliente');



/**
* 
* 
* Adiciona pasta
* 
*/
$app->post('/admin/cliente/pasta/:clienteId', function ($clienteId) use ($app, $cases){

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


/**
*
* Apaga pasta
* 
*/
$app->delete('/admin/cliente/pasta/:clienteId/:id', function ($clienteId, $id) use ($app, $cases){

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


/**
*
* 
* 
* Adiciona arquivo
* 
*/
$app->post('/admin/cliente/arquivo/:clienteId', function ($clienteId) use ($app, $clientes, $pastas, $arquivos, $URL_UPLOAD) {

	/**
	*
	* Adiciona o arquivo
	* 
	* - Necessário renomear o arquivo para não haver problemas de acentuações, o novo nome será baseado no time()
	* - Importante manter o nome original do arquivo salvo na base para podermos mostrar na listagem
	*
	*/



	$message = "";

	
	if(isset($_FILES['arquivo']) && !empty($_FILES['arquivo']['name']))
	{
		/**
		* Nome original
		*/
		$nomeOriginal = $_FILES['arquivo']['name'];

		/**
		* Extensão
		*/
		$extensao = end(explode('.', $nomeOriginal));

		/**
		* Novo nome
		*/
		$novoNome = time() . '.' . $extensao;


		/**
		* Verifica se o diretorio já existe, se não existir então criamos		
		*/
		$diretorio = $_SERVER['DOCUMENT_ROOT'] . $URL_UPLOAD . 'clientes/' . $extensao;
		if(!file_exists($diretorio))
		{
			mkdir($diretorio);
		}

		/**
		* Copia a imagem para a pasta
		*/
		$urlToCopy = $diretorio . '/' . $novoNome;
		if(copy($_FILES['arquivo']['tmp_name'], $urlToCopy))
		{
			/**
			* Obtemos o cliente
			*/
			$resCliente = $clientes->findById($clienteId);
			if($resCliente->cod != 200)
			{
				echo '{"status": false, "message": "Cliente não encontrado"}';
				$app->response->headers->set('Content-Type', 'application/json;charset=utf-8');
				return;
			}

			$cliente = $resCliente->res;


			/**
			*
			* Obtemos a pasta padrão do cliente
			* - Futuramento será evoluido para inserir um arquivo em uma determinada pasta, que será passada como parametro
			*
			*/
			$q = "SELECT * FROM tbl_pastas WHERE id_cliente = " . $clienteId . " ORDER BY id ASC LIMIT 1";
			$resPasta = $pastas->Query($q);
			if($resPasta->cod != 200)
			{
				echo '{"status": false, "message": "Pasta de trabalho padrão não foi encontrada"}';
				$app->response->headers->set('Content-Type', 'application/json;charset=utf-8');
				return;
			}

			$pasta = $resPasta->res[0];		



			/**
			*
			* Salva referencia no Banco de Dados
			*
			*/			
			$arquivos->id_pasta = $pasta['id'];
			$arquivos->nome = $novoNome;
			$arquivos->nomeOriginal = $nomeOriginal;
			$arquivos->extensao = $extensao;			
			$arquivos->create();

			echo '{"status" : true, "message": "Arquivo inserido com sucesso"}';
		}
		else
		{
			echo '{"status" : false, "message": "Falha ao copiar arquivo"}';
		}
	}
	else
	{
		echo '{"status" : false, "message": "Arquivo inválido"}';
	}

	$app->response->headers->set('Content-Type', 'application/json;charset=utf-8');	
});


/**
*
* 
* Listagem de arquivos
* 
*/
$app->get('/admin/cliente/arquivo/:clienteId', function ($clienteId) use ($app, $clientes, $pastas, $arquivos, $URL_UPLOAD) {
	
	
	/**
	* Obtemos o cliente
	*/
	$resCliente = $clientes->findById($clienteId);
	if($resCliente->cod != 200)
	{
		echo '{"status": false, "message": "Cliente não encontrado"}';
		$app->response->headers->set('Content-Type', 'application/json;charset=utf-8');
		return;
	}

	$cliente = $resCliente->res;


	/**
	*
	* Obtemos a pasta padrão do cliente
	* - Futuramento será evoluido para inserir um arquivo em uma determinada pasta, que será passada como parametro
	*
	*/
	$q = "SELECT * FROM tbl_pastas WHERE id_cliente = " . $clienteId . " ORDER BY id ASC LIMIT 1";
	$resPasta = $pastas->Query($q);
	if($resPasta->cod != 200)
	{
		echo '{"status": false, "message": "Pasta de trabalho padrão não foi encontrada"}';
		$app->response->headers->set('Content-Type', 'application/json;charset=utf-8');
		return;
	}

	$pasta = $resPasta->res[0];


	/**
	*
	* Obtemos os arquivos referentes a pasta
	* 
	* - Importante: Montar a URL do arquivo aqui
	*
	*/
	$_arquivos = array();
	$q = "
		SELECT * FROM tbl_arquivos WHERE id_pasta = " . $pasta['id'] . " AND status = 1 AND ativo = 1 ORDER BY criacao DESC";

	$resArquivo = $pastas->Query($q);
	if($resArquivo->cod == 200)
	{
		$_arquivos = $resArquivo->res;
		for($i = 0; $i < count($_arquivos); $i++)
		{
			$_arquivos[$i]['url'] = '/web/uploads/clientes/' . $_arquivos[$i]['extensao'] . '/' . $_arquivos[$i]['nome'];
		}
	}

	echo '{"status" : true, "arquivos": ' . json_encode($_arquivos). '}';
	$app->response->headers->set('Content-Type', 'application/json;charset=utf-8');	
});


/**
* 
* Apaga arquivo
* 
*/
$app->get('/admin/cliente/arquivo/apagar/:arquivoId', function ($arquivoId) use ($app, $arquivos){		
	$q = "UPDATE tbl_arquivos SET status = 0, ativo = 0 WHERE id = " . $arquivoId;
	$resArquivo = $arquivos->Query($q);

	if($resArquivo->cod == 200)
	{
		echo  '{ "status": true, "message": "Arquivo excluído com sucesso"}';
	}
	else
	{
		echo  '{ "status": false, "message": "Falha na exclusão do arquivo"}';
	}
	$app->response->headers->set('Content-Type', 'application/json;charset=utf-8');	
});

