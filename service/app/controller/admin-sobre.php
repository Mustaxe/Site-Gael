<?php
/**
 *
 * @package
 * @since  Tue, 29 Apr 14 16:45:02 -0300
 * @category
 * @subpackage
 *
 */


/**
*
* TODO_CONFIG: Config de path para upload
*
*/
$_URL_UPLOAD = array(
    'localhost' => '/git/site_gael/Site-Gael/service/web/uploads/',
    'localhost:8080' => '/git/site_gael/Site-Gael/service/web/uploads/',
    'homologacao.gael.ag' => '/service/web/uploads/',
    'www.gael.ag' => '/service/web/uploads/',
    'gael.ag' => '/service/web/uploads/'
);
$URL_UPLOAD = $_URL_UPLOAD[$_SERVER['HTTP_HOST']];



$app->post('/admin/sobre', function () use ($app, $URL_UPLOAD) {
    
    /**
    *
    * Manipula as informações da area Sobre
    *   - Os dados serão armazenados em um arquivo JSON
    *
    * Estrutura do Aruivo
    * 
    *   pt =>
    *       titulo =>
    *       subtitulo => 
    *       texto =>
    *       arquivo => 
    *   en =>
    *       titulo =>
    *       subtitulo =>
    *       texto =>
    *       arquivo =>
    *
    *
    */


    $params  = $app->request;


    /**
    * Lang
    */
    $_lang = $params->post('lang');



    /**
    *
    * Obtem o arquivo JSON com as informações "Sobre"    
    *
    */
    $json = file_get_contents($_SERVER['DOCUMENT_ROOT'] . $URL_UPLOAD . 'sobre/sobre.json');
    

    
    $json = (array) json_decode($json);

   
    /**
    * Titulo
    */
    $_titulo = trim($params->post('titulo'));
    if(empty($_titulo))
    {
        /**
        * Se estiver vazio pegamos o valor atual
        */
        $_titulo = $json[$_lang]->titulo;
    }
    else
    {
        $_titulo = addslashes($_titulo);
    }


    /**
    * Subtitulo
    */
    $_subtitulo = $params->post('subtitulo');
    if(empty($_subtitulo))
    {
        /**
        * Se estiver vazio pegamos o valor atual
        */
        $_subtitulo = $json[$_lang]->subtitulo;
    }
    else
    {
        $_subtitulo = addslashes($_subtitulo);
    }
    
    
    /**
    * Texto
    */
    $_texto = $params->post('editor');
    if(empty($_texto))
    {
        /**
        * Se estiver vazio pegamos o valor atual
        */
        $_texto = str_replace(array("\n","\r","\t"), "", $json[$_lang]->texto);
    }
    else
    {
        $_texto = str_replace(array("\n","\r","\t"), "", $_texto);
        $_texto = addslashes($_texto);
    }


    /**
    * Arquivo
    */
    $_arquivo = '';
    if($params->post('isRemoveFile') == 1)
    {
        /**
        *
        * Removemos o arquivo
        *
        */
    }
    else
    {
        if(!empty($_FILES['arquivo']['name']))
        {
            /**
            * Apenas arquivo PDF
            */
            if($_FILES['arquivo']['type'] == 'application/pdf')
            {

                if(!copy($_FILES['arquivo']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . $URL_UPLOAD . 'pdf/' . $_lang . '_sobre.pdf'))
                {
                    $app->flash('error', 'O arquivo "' . $_FILES['arquivo']['name'] . '" não pode ser salvo.');
                    $app->redirect($app->urlFor('visualizar_sobre', array('id' => $_lang)));
                }

                /**
                *
                * Nome do arquivo PDF
                *
                */
                $_arquivo = $_lang . '_sobre.pdf';
            }
            else
            {
                $app->flash('error', 'O arquivo "' . $_FILES['arquivo']['name'] . '" não é PDF.');
                $app->redirect($app->urlFor('visualizar_sobre', array('id' => $_lang)));
            }
        }
        else
        {
            /**
            * Se estiver vazio pegamos o valor atual
            */
            $_arquivo = $json[$_lang]->arquivo;
        }
    }

    /**
    *
    * Salva o novo JSON
    *
    */
    try
    {        
       

        $json[$_lang]->titulo = $_titulo;
        $json[$_lang]->subtitulo = $_subtitulo;
        $json[$_lang]->texto = $_texto;
        $json[$_lang]->arquivo = $_arquivo;
        
        $json = json_encode($json);

        file_put_contents($_SERVER['DOCUMENT_ROOT'] . $URL_UPLOAD . 'sobre/sobre.json', $json);        
    }
    catch (Exception $e)
    {
        fclose($fp);
        $app->flash('error', $e->getMessage());
        $app->redirect($app->urlFor('visualizar_sobre', array('id' => $_lang)));
    }

    $app->flash('notice', 'Alteração efetuada com sucesso.');
    $app->redirect($app->urlFor('visualizar_sobre', array('id' => $_lang)));
})->via('POST')->name('editar_sobre');


$app->get('/admin/sobre/:id', function ($id) use ($app, $URL_UPLOAD) {
    
    /**
    *
    * $id Referencia o idioma (pt/en)
    *
    * Obtemos o arquivo JSON com as informações do "Sobre"
    *    
    *
    * Manipula as informações da area Sobre
    *   - Os dados serão armazenados em um arquivo JSON
    *
    * Estrutura do Aruivo
    * 
    *   pt =>
    *       titulo =>
    *       subtitulo => 
    *       texto =>
    *       arquivo =>
    *   en =>
    *       titulo =>
    *       subtitulo =>
    *       texto =>
    *       arquivo =>
    *
    *
    */    

    $json = file_get_contents($_SERVER['DOCUMENT_ROOT'] . $URL_UPLOAD . 'sobre/sobre.json');
    
    $json = (object) json_decode($json);

    $json->{$id}->titulo = stripslashes($json->{$id}->titulo);
    $json->{$id}->subtitulo = stripslashes($json->{$id}->subtitulo);
    $json->{$id}->texto = stripslashes($json->{$id}->texto);    
   
    $app->render('admin/sobre/editar.html.twig', array('sobre' => $json->{$id}, 'lang' => $id) );
})->via('GET')->name('visualizar_sobre');



