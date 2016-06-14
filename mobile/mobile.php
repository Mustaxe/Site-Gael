<?php

ini_set("display_errors", 0);


/**
*
* TODO_CONFIG: Paths
*
*/
$ENVIRONMENTS = array(
  'localhost:8080' => 'localhost:8080/git/site_gael/Site-Gael',
  '192.168.1.30:8080' => '192.168.1.30:8080/git/site_gael/Site-Gael',
  'homologacao.gael.ag' => 'homologacao.gael.ag',
  'www.gael.ag' => 'gael.ag',
  'gael.ag' => 'gael.ag'
);

/**
*
* Redefine a variavel global HTTP_HOST
*
*/
$_SERVER[HTTP_HOST] = $ENVIRONMENTS[$_SERVER['HTTP_HOST']];


/**
*
* Define o idioma
*
* Se existir o parametro PT na variavel global "HTTP_ACCEPT_LANGUAGE", então assumimos que a "lang" é PT, senão é EN.
*
*/
$langStr = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
$isPt = stripos($langStr, 'pt');

if ($isPt === false) {
  $lang = 'en';
} else { 
  $lang = 'pt';
}

if(isset($_GET['lang'])) {
  $lang = $_GET['lang'];
}

/**
* Define o idioma da sessão
*/
$_SESSION['lang'] = $lang;


if ($lang == 'pt') {
  require 'mobile_pt.php';
} else {
  require 'mobile_en.php';
}