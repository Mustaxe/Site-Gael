<?php

ini_set("display_errors", 0);


/**	
* Define o idioma
*/

$langStr = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
$yes = stripos($langStr, 'pt');

if ($yes === false) { $lang = 'en'; }
else { $lang = 'pt'; }

if(isset($_GET['lang'])) { $lang = $_GET['lang']; }

$_SESSION['lang'] = $lang;


if($lang == 'pt') {
	require 'index_pt.php';
} else {
	require 'index_en.php';
}

