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
 *   description="Operações Admin. Usuário",
 *   produces="['application/json']"
 * )
 */

use app\model\Usuario;
use app\model\Projetos;

// modelos usados
$usuario  = new Usuario(array(), $app->db);
$projetos  = new Projetos(array(), $app->db);

/**
 *
 * @SWG\Api(
 *   path="/admin/usuario/{id}",
 *   description="Dados do usuário",
 *   @SWG\Operation(method="GET", summary="Dados do usuário", type="array", nickname="dadosUsuario",
 *          @SWG\Parameter(
 *              name="id",
 *              description="id do usuário",
 *              required=true,
 *              type="string",
 *              paramType="path"
 *          )
 *       ),
 *      @SWG\ResponseMessage(code=500, message="Problema ao pegar os dados do usuário")
 *   )
 * )
 */
$app->get('/admin/usuario/:id', function ($id) use ($app, $usuario) {
    $res = $usuario->findById($id);

    if ($res->cod == 404) {
        $app->notFound();
    }else{
        $app->render('admin/usuario/editar.html.twig', array('usuario'=>$res->res));
    }

    return json_encode($res);
})->name('busca_usuario');

/**
 *
 * @SWG\Api(
 *   path="/admin/usuario/{id}",
 *   description="Editar dados do usuário",
 *   @SWG\Operation(method="POST", summary="Editar usuário", type="void", nickname="editaUsuario",
 *      @SWG\Parameters(
 *          @SWG\Parameter(
 *              name="id",
 *              description="id do usuário",
 *              paramType="path",
 *              required=true,
 *              type="string"
 *          ),
 *          @SWG\Parameter(
 *              name="nome",
 *              description="Nome",
 *              required=false,
 *              type="string",
 *              paramType="form"
 *          ),
 *          @SWG\Parameter(
 *              name="email",
 *              description="E-mail",
 *              required=false,
 *              type="string",
 *              paramType="form"
 *          ),
 *          @SWG\Parameter(
 *              name="senha",
 *              description="Senha",
 *              required=false,
 *              type="string",
 *              paramType="form"
 *          ),
  *          @SWG\Parameter(
 *              name="confirma_senha",
 *              description="Confirmação Senha",
 *              required=false,
 *              type="string",
 *              paramType="form"
 *          )
 *      ),
 *      @SWG\ResponseMessage(code=500, message="Problema ao editar usuário")
 *   )
 * )
 */
$app->post('/admin/usuario/:id', function ($id) use ($app, $usuario) {
    $params = $app->request;

    $res = $usuario->findById($id);

    if ($res->cod == 404) {
        $app->notFound();
        exit;
    }

    $nome   = trim($params->post('nome'));
    $email  = trim($params->post('email'));
    $senha  = trim($params->post('senha'));
    $confirmacaoSenha = trim($params->post('confirma_senha'));

    if (!empty($nome) && $nome !== 'undefined'){
        $usuario->nome  = $nome;
    }

    if (!empty($email) && $email !== 'undefined'){
        $usuario->email          = $email;
        $usuario->emailCanonical = strtolower($email);
    }

    if (!empty($senha) && $senha !== 'undefined'){
        if ($senha !== $confirmacaoSenha ) {
            $app->response->setStatus(400);

            $app->render('/admin/usuario/editar.html.twig', array('usuario' => $usuario, 'error' => 'As senhas não conferem.'));

            return;
        } 

        $usuario->hashSenha = $app->auth->hashPassword($senha);
    }

    $res = $usuario->save(array('id' => $id), 'id = :id');

    if ($res->cod == 200) {
        $app->flash('notice', 'Informação atualizada com sucesso');
        $app->redirect($app->urlFor('busca_usuario', array('id' => $id)));
    }

    $app->flash('error', 'Não foi possível atualizar a(s) informação(ões).');
    $app->redirect($app->urlFor('busca_usuario', array('id' => $id)));
})->name('edita_usuario');

/**
 *
 * @SWG\Api(
 *   path="/admin/esqueci-senha",
 *   description="Enviar email com link para trocar senha",
 *   @SWG\Operation(method="POST", summary="Enviar e-mail para trocar senha", type="void", nickname="esqueciSenha",
 *      @SWG\Parameters(
 *          @SWG\Parameter(
 *              name="email",
 *              description="Email para recuperar a senha",
 *              required=true,
 *              type="string",
 *              paramType="form"
 *          )
 *       ),
 *      @SWG\ResponseMessage(code=404, message="O e-mail informado não consta no sistema."),
 *      @SWG\ResponseMessage(code=400, message="Não foi possível salvar o token do usuário.")
 *   )
 * )
 */
$app->post('/admin/esqueci-senha', function () use ($app, $usuario, $projetos) {
    $params = $app->request;
    $view   = $app->view();
    $email  = $app->request->post('email');

    // verifica se e-mail está cadastrado
    $usuario = $usuario->findOne(
        array('email' => $email),
        'email = :email'
    );

    $projeto = $projetos->findOne(
         array('projeto' => 'Gael'),
        'projeto = :projeto'
    );

    if ($usuario->cod == 200) {
        $tokenConfirmacao = $app->auth->geraTokenConfirmacao();

        $usuario->tokenConfirmacao = $tokenConfirmacao;

        $res = $usuario->save(array('id' => $usuario->res['id']), 'id = :id');

        if ($res->cod == 200) {
            $urlTrocaSenha = $app->config('dominio.frontend') . '/admin/troca-senha/' . $tokenConfirmacao;

            $view->setData('link', $urlTrocaSenha);

            $templateEmail = $view->render('esqueci-senha.html.twig');

            $app->mailer->send(\Swift_Message::newInstance()
                                ->setSubject('[Gael] Esqueci a minha senha')
                                ->setFrom(array($projeto->res['contato_email']))
                                ->setTo(array($params->post('email')))
                                ->setBody($templateEmail,'text/html'));

            $res = array('res' => 'Favor verificar o seu e-mail.');
            echo json_encode($res);

            return;
        }

        $app->response->setStatus(400);

        echo json_encode(array(
            'res' => 'Não foi possível salvar o token do usuário.',
            'cod' => 400,
        ));

        return;
    }

    $app->response->setStatus(404);

    return json_encode(array(
        'res' => 'O e-mail informado não consta no sistema.',
        'cod' => 404,
    ));
});

/**
 *
 * @SWG\Api(
 *   path="/admin/troca-senha",
 *   description="Troca senha do usuário",
 *   @SWG\Operation(method="POST", summary="Trocar senha usuário", type="void", nickname="trocaSenhaUsuario",
 *      @SWG\Parameters(
 *          @SWG\Parameter(
 *              name="token",
 *              description="token de confirmação",
 *              paramType="form",
 *              required=true,
 *              type="string"
 *          ),
 *          @SWG\Parameter(
 *              name="senha",
 *              description="Senha",
 *              required=true,
 *              type="string",
 *              paramType="form"
 *          ),
 *          @SWG\Parameter(
 *              name="confirma-senha",
 *              description="Confirmação senha",
 *              required=true,
 *              type="string",
 *              paramType="form"
 *          )
 *       ),
 *      @SWG\ResponseMessage(code=500, message="Problema ao trocar senha do usuário"),
 *      @SWG\ResponseMessage(code=404, message="O usuário com token nnnn não foi encontrado."),
 *      @SWG\ResponseMessage(code=400, message="As senhas não conferem.")
 *   )
 * )
 */
$app->post('/admin/troca-senha', function () use ($app, $usuario) {
    $params = $app->request;

    $token            = trim($params->post('token'));
    $senha            = trim($params->post('senha'));
    $confirmacaoSenha = trim($params->post('confirma-senha'));

    // busca usuário pelo token
    $usuario = $usuario->findOne(
        array('token' => $token),
        'tokenConfirmacao = :token'
    );

    if ($usuario->cod == 404) {
        $app->response->setStatus(404);

        return json_encode(array(
            'res' => 'O usuário com token '.$token.' não foi encontrado.',
            'cod' => 404,
        ));

    }

    if ($senha !== $confirmacaoSenha ) {
        $app->response->setStatus(400);

        return json_encode(array(
            'res' => 'As senhas não conferem.',
            'cod' => 400,
        ));

    }

    $usuario->hashSenha        = $app->auth->hashPassword($senha);
    $usuario->tokenConfirmacao = NULL;

    $res = $usuario->save(array('token' => $token), 'tokenConfirmacao = :token');

    return json_encode($res);
})->name('troca-senha');
