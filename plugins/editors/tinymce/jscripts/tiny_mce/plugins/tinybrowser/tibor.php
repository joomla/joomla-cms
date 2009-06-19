<?php

$tbpath = pathinfo($_SERVER['SCRIPT_NAME']);

var_dump($tbpath);

//$cfg  = ((@$_SERVER['DOCUMENT_ROOT'] && file_exists(@$_SERVER['DOCUMENT_ROOT'] . $_SERVER['PHP_SELF'])) ? $_SERVER['DOCUMENT_ROOT'] : str_replace(dirname(@$_SERVER['PHP_SELF']), '', str_replace('\\', '/', realpath('.'))));
//echo "root=".$cfg;

	$cfg['ver'] 		= '1.3.9 - build 11242008';										// iBrowser version
	//$cfg['root_dir']	= realpath((getenv('DOCUMENT_ROOT') && ereg('^'.preg_quote(realpath(getenv('DOCUMENT_ROOT'))), realpath(__FILE__))) ? getenv('DOCUMENT_ROOT') : str_replace(dirname(@$_SERVER['PHP_SELF']), '', str_replace($osslash, '/', dirname(__FILE__))));
	$cfg['root_dir']    = ((@$_SERVER['DOCUMENT_ROOT'] && file_exists(@$_SERVER['DOCUMENT_ROOT'] . $_SERVER['PHP_SELF'])) ? $_SERVER['DOCUMENT_ROOT'] : str_replace(dirname(@$_SERVER['PHP_SELF']), '', str_replace('\\', '/', realpath('.'))));
	$cfg['base_url'] 	= 'http://' . $_SERVER['SERVER_NAME']; 							// base url; e.g. 'http://localhost/'
	//$cfg['main_dir'] 	= dirname($_SERVER['PHP_SELF']); 								// iBrowser main dir; e.g. '/home/domain/public_html/ibrowser/'
	$cfg['main_dir']    = ereg_replace("//", "/", dirname($_SERVER['PHP_SELF']));
	$cfg['scripts']  	= $cfg['main_dir'] . '/scripts/'; 								// scripts dir; e.g. '/home/domain/public_html/ibrowser/scripts/'
	$cfg['pop_url']    	= $cfg['scripts'] . 'popup.php'; 								// popup dir; relative to 'script' dir
	$cfg['temp']     	= realpath(dirname(__FILE__) . '/../../../../../../../images/stories'); 					// temp dir; e.g. 'D:/www/temp'

var_dump($cfg);

$docroot = rtrim($_SERVER['DOCUMENT_ROOT'],'/');
echo $docroot;
echo "<br>";
$img = realpath(dirname(__FILE__) . '/../../../../../../../images/stories');
echo $img;
echo "<br>";
echo preg_replace('[\\\]', '/', realpath(dirname(__FILE__) . '/../../../../../../../images/stories'));
echo "<br>";
echo str_replace($docroot,'', preg_replace('[\\\]', '/', realpath(dirname(__FILE__) . '/../../../../../../../images/stories'))) ."/";


?>
