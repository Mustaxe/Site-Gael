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
 *   resourcePath="/contatos",
 *   description="Operações Contatos",
 *   produces="['application/json']"
 * )
 */

use app\model\Contatos;
use app\model\Projetos;

$contatos = new Contatos(array(), $app->db);
$projetos = new Projetos(array(), $app->db);

/**
 *
 * @SWG\Api(
 *   path="/contatos",
 *   description="Enviar email de contato",
 *   @SWG\Operation(method="POST", summary="Enviar e-mail contato", type="void", nickname="enviaEmail",
 *      @SWG\Parameters(
 *          @SWG\Parameter(
 *              name="email",
 *              description="Email Remetente",
 *              required=true,
 *              type="string",
 *              paramType="form"
 *          ),
 *          @SWG\Parameter(
 *              name="nome",
 *              description="Nome Remetente",
 *              required=true,
 *              type="string",
 *              paramType="form"
 *          ),
 *          @SWG\Parameter(
 *              name="descricao",
 *              description="Descrição (Whats on your mind?)",
 *              required=true,
 *              type="string",
 *              paramType="form"
 *          )
 *       ),
 *      @SWG\ResponseMessage(code=500, message="Problema ao enviar e-mail contato")
 *   )
 * )
 */
$app->post('/contatos', function () use ($app, $contatos, $projetos) {
    $params = $app->request;
    $view   = $app->view();

    $projeto = $projetos->findOne(
        array('projeto' => 'Gael'),
        'projeto = :projeto'
    );


    // enviar e-mail

    // Setar dados para o e-mail usando: setData() ou appendData()
/*     $view->setData('fullname', $params->post('fullname'));
    $view->setData('email', $params->post('email'));
	$view->setData('mind', $params->post('mind'));

    $templateEmail = $view->render('contato.html.twig'); */

    $app->mailer->send(\Swift_Message::newInstance()
                        ->setSubject('Contato de gael.ag')
                        ->setFrom(array($params->post('email')))
                        ->setTo(array('rh@gael.ag'))
                        //->setTo(array($projeto->res['contato_email']))
                        //->setBcc(array($projeto->res['contato_email']))
                        //->setBody($templateEmail,'text/html'));
						->setBody("<table>
										<tr>
											<td>Nome:</td>
											<td>".$params->post('nome')."</td>
										</tr>
										<tr>
											<td>Email:</td>
											<td>".$params->post('email')."</td>
										</tr>
										<tr><td><br></td></tr>
										<tr>
											<td>Mensagem:</td>
											<td>".$params->post('descricao')."</td>
										</tr>										
								   </table>",'text/html'));

    // gravar no banco de dados
    $contatos->email       = $params->post('email');
    $contatos->nome        = $params->post('nome');
    $contatos->descricao   = $params->post('descricao');
    $contatos->data_envio  = date('Y-m-d H:i:s');

    $reg = $contatos->create();

    //$res = array('cod' => 200, 'res' => 'Agradecemos seu contato.');
    echo json_encode($reg);
});
