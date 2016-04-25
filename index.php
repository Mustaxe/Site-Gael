<?php

ini_set("display_errors", 0);


/**
*
* TODO_CONFIG: Config de path
*	
*/

/**
*
* PRODUCAO:
* $_SERVER[HTTP_HOST] = "gael.ag";
*
*/

$ENVIRONENTS = array(
	'localhost:8080' => 'localhost:8080/git/site_gael/Site-Gael',
	'homologacao.gael.ag' => 'homologacao.gael.ag',
	'www.gael.ag' => 'gael.ag',
	'gael.ag' => 'gael.ag'
);


/**
*
* Redefine a variavel global HTTP_HOST
*
*/
$_SERVER[HTTP_HOST] = $ENVIRONENTS[$_SERVER['HTTP_HOST']];




/**
*
* Define o idioma
*
* Se existir o parametro PT na variavel global "HTTP_ACCEPT_LANGUAGE", então assumimos que a "lang" é PT, senão é EN.
*
*/
$langStr = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
$yes = stripos($langStr, 'pt');

if ($yes === false) {
	$lang = 'en';
} else { 
	$lang = 'pt';
}

if(isset($_GET['lang'])) {
	$lang = $_GET['lang'];
}

$_SESSION['lang'] = $lang;


if($lang == 'pt') {
	require 'index_pt.php';
} else {
	require 'index_en.php';
}