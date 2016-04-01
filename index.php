<?php

ini_set("display_errors", 0);


/**	
* Define o idioma
*/
$lang = 'pt'; //$_SERVER['HTTP_ACCEPT_LANGUAGE'];
$_SESSION['lang'] = $lang;

if(isset($_GET['lang'])) {
	$lang = $_GET['lang'];
	$_SESSION['lang'] = $lang;
}

if($lang == 'pt') {
	require 'index_pt.php';
} else {
	require 'index_en.php';
}

