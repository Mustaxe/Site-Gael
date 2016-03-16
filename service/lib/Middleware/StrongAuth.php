<?php
/**
 * Strong Authentication
 *
 * Use this middleware with your Slim Framework application
 * to require HTTP basic auth for all routes.
 *
 * @author Andrew Smith <a.smith@silentworks.co.uk>
 * @version 1.0
 * @copyright 2012 Andrew Smith
 *
 * USAGE
 * 
 * $app = new \Slim\Slim();
 * $app->add(new \Slim\Extras\Middleware\StrongAuth(array('provider' => 'PDO', 'pdo' => new PDO('sqlite:memory'))));
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
namespace lib\Middleware;

use lib\exception\HttpForbiddenException;

class StrongAuth extends \Slim\Middleware
{
    /**
     * @var array
     */
    protected $settings = array(
        'login.url' => '/',
        'auth.type' => 'http',
        'realm'     => 'Protected Area',
    );

    /**
     * Construtor
     *
     * @param  array  $config   Configuration for Strong and Login Details
     * @param  \lib\Authentication\Authentication $strong
     *
     * @return  void
     */
    public function __construct(array $config = array(), \lib\Authentication\Authentication $strong = null)
    {
        $this->config = array_merge($this->settings, $config);
        $this->auth = (!empty($strong)) ? $strong : \lib\Authentication\Authentication::factory($this->config);
    }

    /**
     * Call
     *
     * @return void
     */
    public function call()
    {
        $req = $this->app->request();

        switch ($this->config['auth.type']) {
            case 'chave':
                $this->formAuthKey($this->auth, $req);
                break;
            case 'sessao':
                $this->formAuthSession($this->auth, $req);
                break;
            default:
                $this->httpAuth($this->auth, $req);
                break;
        }
    }

    /**
     * Autenticação utilizando formulário (POST) com chave
     *
     * @param \Strong\Strong $auth
     * @param object $req
     */
    private function formAuthKey($auth, $req)
    {
        $app       = $this->app;
        $config    = $this->config;
        $ipUsuario = $this->getIpUsuario();

        // anterior: slim.before.router
        // todo: refatoração - remover código duplicado - suporte ao $this em closures a partir PHP 5.4
        $this->app->hook('slim.before.dispatch', function () use ($app, $auth, $req, $config, $ipUsuario) {

            // verifica se o ip do usuário requisitando a API é o mesmo que o servidor, ou outro IP
            // autorizado, definido na configuração 'ip.servidor'
			
			/* if ( !in_array($ipUsuario, $app->config('ip.servidor')) ) {
				throw new HttpForbiddenException();
			} */

            //$secured_urls = isset($config['security.urls']) && is_array($config['security.urls']) ? $config['security.urls'] : array();
            $secured_urls = $app->rotaCrud->findAll();
            if ($secured_urls->cod == 404){
                $secured_urls = array();
            } else {
                foreach ($secured_urls->res as $surl) {
                    $patternAsRegex = $surl['nome'];
                    // todo: adicionar / na frente se nao existir
                    if (substr($surl['nome'], -1) === '/') {
                        $patternAsRegex = $patternAsRegex . '?';
                    }
                    $patternAsRegex = '@^\\' . $patternAsRegex . '(/.*)?$@';

                    $urlPattern             = $app->router()->getCurrentRoute()->getPattern();
                    $pathAtualSemParametros = $urlPattern;

                    // retirar os parâmetros da URL
                    if (strpos($urlPattern, ':') !== false) {
                        list($pathAtualSemParametros) = explode(":", $urlPattern);
                        $pathAtualSemParametros = rtrim($pathAtualSemParametros, "/");
                    }

                    if (preg_match($patternAsRegex, $pathAtualSemParametros)) {
                        $ch = $app->router()->getCurrentRoute()->getParams();
                        if (!isset($ch['chave'])) {
                            header("Content-Type: application/json");
                            header(':', true, 401);
                            echo json_encode(array('cod' => 401, 'res' => 'Usuário não autenticado'));
                            exit;
                        }

                        $chave = $app->router()->getCurrentRoute()->getParam('chave');

                        if (!$auth->loggedIn($chave)) {
                            if ($req->getPath() !== $config['login.url']) {
                                // $app->redirect($app->request->getRootUri() . '/naoaut');

                                header("Content-Type: application/json");
                                header(':', true, 401);

                                echo json_encode(array('cod'=>401, 'res'=>'Usuário não autenticado'));
                                exit;
                            }
                        }

                        if ($auth->usuarioPertenceGrupo($pathAtualSemParametros, $chave) === false) {
                            throw new HttpForbiddenException();
                        }

                        $app->container->singleton('usuarioLogado', function () use ($app, $auth, $chave) {
                            return $auth->getUser($chave);
                        });
                    }
                }
            }
        });

        $this->next->call();
    }


    /**
     * Autenticação utilizando formulário (POST) com sessão
     *
     * @param \Strong\Strong $auth
     * @param object $req
     */
    private function formAuthSession($auth, $req)
    {
        $app       = $this->app;
        $config    = $this->config;
        $ipUsuario = $this->getIpUsuario();

        $this->app->hook('slim.before.dispatch', function () use ($app, $auth, $req, $config, $ipUsuario) {

            // verifica se o ip do usuário requisitando a API é o mesmo que o servidor, ou outro IP
            // autorizado, definido na configuração 'ip.servidor'
            /*if ( !in_array($ipUsuario, $app->config('ip.servidor')) ) {
                throw new HttpForbiddenException();
            }*/

            $secured_urls = $app->rotaCrud->findAll();

            if ($secured_urls->cod == 404){
                $secured_urls = array();
            } else {
                foreach ($secured_urls->res as $surl) {
                    $patternAsRegex = $surl['nome'];

                    if (substr($surl['nome'], -1) === '/') {
                        $patternAsRegex = $patternAsRegex . '?';
                    }
                    $patternAsRegex = '@^\\' . $patternAsRegex . '(/.*)?$@';

                    $urlPattern             = $app->router()->getCurrentRoute()->getPattern();
                    $pathAtualSemParametros = $urlPattern;

                    // retirar os parâmetros da URL
                    if (strpos($urlPattern, ':') !== false) {
                        list($pathAtualSemParametros) = explode(":", $urlPattern);
                        $pathAtualSemParametros = rtrim($pathAtualSemParametros, "/");
                    }

                    if (preg_match($patternAsRegex, $pathAtualSemParametros)) {
                        if (!$auth->loggedIn()) {
                            if ($req->getPath() !== $config['login.url']) {
                                $app->redirect($app->request->getUrl() . $app->request->getRootUri() . $config['login.url']);
                            }
                        }

                        if ($auth->usuarioPertenceGrupo($pathAtualSemParametros) === false) {
                            if (strpos($app->request()->getResourceUri(), '/admin') !== false) {
                                $app->render('admin/erros/403.html.twig');

                                exit;
                            }
                            throw new HttpForbiddenException();
                        }

                        // adiciona user logado na view para a interface administrativa
                        $app->view()->appendData(
                                array( 'usuarioAtual' => $auth->getUser() )
                           );

                        $app->container->singleton('usuarioLogado', function () use ($app, $auth) {
                            return $auth->getUser();
                        });
                    }
                }
            }
        });

        $this->next->call();
    }

    /**
     * HTTPAuth based authentication
     *
     * This method will check the HTTP request headers for previous authentication. If
     * the request has already authenticated, the next middleware is called. Otherwise,
     * a 401 Authentication Required response is returned to the client.
     *
     * @param \Strong\Strong $auth
     * @param object $req
     */
    private function httpAuth($auth, $req)
    {
        $res      = $this->app->response();
        $authUser = $req->headers('PHP_AUTH_USER');
        $authPass = $req->headers('PHP_AUTH_PW');

        if ($authUser && $authPass && $auth->login($authUser, $authPass)) {
            $this->next->call();
        } else {
            $res->status(401);
            $res->header('WWW-Authenticate', sprintf('Basic realm="%s"', $this->config['realm']));
        }
    }

    /**
     * Retorna o IP do usuário que está
     * requisitando a API
     *
     * @return $ip IP do usuário
     */
    private function getIpUsuario()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }
}
