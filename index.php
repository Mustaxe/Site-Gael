<?php

ini_set("display_errors", 0);


/**
*
* TODO_CONFIG: Config de path
*	
*/
$ENVIRONMENTES = array(
	'localhost:8080' => 'localhost:8080/gael/Site-Gael',
	'192.168.1.30:8080' => '192.168.1.30:8080/git/site_gael/Site-Gael',
	'homologacao.gael.ag' => 'homologacao.gael.ag',
	'www.gael.ag' => 'gael.ag',
	'gael.ag' => 'gael.ag'
);



/*******************************************************************************
*
* Detecta o dispositivo, se é mobile ou desktop
* - Verificação dos USER_AGENT atraves de palavras chave
*	 [Android, IEMobile, iPhone, iPad, webOS, BlackBerry, iPod, Symbian]
*
*
********************************************************************************/
$iPhone =  stripos($_SERVER['HTTP_USER_AGENT'],"iPhone");
$iPad =  stripos($_SERVER['HTTP_USER_AGENT'],"iPad");
$Android =  stripos($_SERVER['HTTP_USER_AGENT'],"Android");
$webOS =  stripos($_SERVER['HTTP_USER_AGENT'],"webOS");
$BlackBerry =  stripos($_SERVER['HTTP_USER_AGENT'],"BlackBerry");
$iPod =  stripos($_SERVER['HTTP_USER_AGENT'],"iPod");
$IEMobile =  stripos($_SERVER['HTTP_USER_AGENT'],"IEMobile");
$Symbian =  stripos($_SERVER['HTTP_USER_AGENT'],"Symbian");

if($iPhone || $iPad || $Android || $webOS || $BlackBerry || $iPod || $IEMobile || $Symbian) {

	//echo $_SERVER['HTTP_USER_AGENT'];
	//echo  $ENVIRONMENTES[$_SERVER['HTTP_HOST']] . '/mobile';

	
	echo '
		<!DOCTYPE html>
		<html>
			<head>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
				<script type="text/javascript"> window.location.href = "http://' . $ENVIRONMENTES[$_SERVER['HTTP_HOST']] . '/mobile/"; </script>
			</head>
			<body>Teste</body>
		</html>';
	
	die;
}



/**
*
* Redefine a variavel global HTTP_HOST
*
*/
$_SERVER[HTTP_HOST] = $ENVIRONMENTES[$_SERVER['HTTP_HOST']];




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