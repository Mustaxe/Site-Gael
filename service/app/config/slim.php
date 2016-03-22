<?php

$settings = array(
    'slim' => array(
        'templates.path'      => ROOT.'/templates/',
        'view'                => new \Slim\Views\Twig(),
        'debug'               => true,
        'cookies.encrypt'     => true,
        'cookies.secret_key'  => md5('appsecretkey'),
        'cookies.cipher'      => MCRYPT_RIJNDAEL_256,
        'cookies.cipher_mode' => MCRYPT_MODE_CBC,
        'log.enabled'         => true,
        'log.writer'          => new \lib\Log\DateTimeFileWriter(array(
                                    'path'           => ROOT.'/log',
                                    'name_format'    => 'Y-m-d',
                                    'message_format' => '%label% - %date% - %message%'
                                 )),
        'provider'            => 'PDO',
        'auth.type'           => 'sessao',
        'chave.expira'        => 86400,
        'login.url'           => '/admin/login',
        'secured.urls'        => array(
                                    array('path' => '/admin'),
                                    array('path' => '/admin/.+')
                                ),
        'upload.mimetypes'    => array(
                                    'image/png',
                                    'image/gif',
                                    'image/jpeg',
                                    'image/jpg',
                                ),
        'upload.max_size'     => '5M',
        'upload.path'         => ROOT . '/web/uploads/',
        'ip.servidor'         => array('127.0.0.1', 'fe80::1', '::1', '10.0.2.2', '10.0.2.15', 'localhost', 'abb1-gael-site-institucional-dev.inkubaapps.com.br'),
        'dominio.frontend'    => 'http://www.gael.ag',
        'admin.menu'          => array(
                                    /* array(
                                        'descricao' => 'Home',
                                        'url'       => '/admin/home',
                                    ), */
                                    array(
                                        'descricao' => 'Cases',
                                        'url'       => '/admin/cases',
                                    ),
                                    array(
                                        'descricao' => 'Categorias',
                                        'url'       => '/admin/categorias',
                                    ),
                                    array(
                                        'descricao' => 'Contato',
                                        'url'       => '/admin/contato',
                                    ),
                                    array(
                                        'descricao' => 'Destaques',
                                        'url'       => '/admin/destaques',
                                    )
                                ),
    ),
    'session_cookies' => array(
        'expires' => '2 weeks',
    ),
    'database' => 'mysql',
    'mailer' => array(
        'host'     => 'localhost',
        'port'     => 25,
        'ssl'      => '',
        'username' => '',
        'password' => '',
    ),
    'facebook' => array(
        'appid'        => '',
        'secret'       => '',
        'file.upload'  => true,
        'scope'        => 'email,user_photos,publish_stream,photo_upload,friends_photos,user_photo_video_tags,friends_photo_video_tags',
        'redirect.uri' => 'http://localhost/framework-php/loginfb/',
    ),
);

return $settings;
