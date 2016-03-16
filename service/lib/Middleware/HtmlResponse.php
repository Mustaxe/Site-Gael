<?php
/**
 * HTML Response
 *
 * Usar para retornar resposta da interface 
 * administrativa em HTML
 *
 */
namespace lib\Middleware;

class HtmlResponse extends \Slim\Middleware
{
    /**
     * Chamada do middleware
     *
     * @return void
     */
    public function call() 
    {
        $this->app->hook('slim.before', array($this, 'check'));

        // Chama o próximo middleware
        $this->next->call();
    }

    /**
     * Verifica se contém '/admin' na URL
     * então retorna a resposta em HTML
     *
     * @return void
     */
    public function check()
    {
        $path = $this->app->request()->getPathInfo();

        if (preg_match('@^\\/admin(/.*)?$@', $path)) {
            $this->app->response->headers->set('Content-Type', 'text/html;charset=utf-8');
        }
    }
}
