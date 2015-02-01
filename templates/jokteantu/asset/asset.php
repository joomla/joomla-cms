<?php

/**
 * @package     Jokte.Site
 * @subpackage	jokteantu
 * @author 	    Equipo de desarrollo juuntos.
 * @copyleft    (comparte igual)  Jokte!
 * @license     GNU General Public License version 3 o superior.
*/

// Acceso directo prohibido
defined('_JEXEC') or die;

$user1		= $this->countModules('user1');
$socialnet	= $this->countModules('socialnet');
$banner1 	= $this->countModules('banner1');
$banner2 	= $this->countModules('banner2');
$topmenu	= $this->countModules('topmenu');
$manager 	= $this->countModules('manager');
$slide 		= $this->countModules('slide');
$user2 		= $this->countModules('user2');
$user3 		= $this->countModules('user3');
$user4 		= $this->countModules('user4');
$bread 		= $this->countModules('breadcrumb');
$lefttop = $this->countModules('lefttop');
$insetleft = $this->countModules('insetleft');
$outsetleft = $this->countModules('outsetleft');
$leftbottom = $this->countModules('leftbottom');

$sobrecontent = $this->countModules('sobrecontent');
$bajocontent = $this->countModules('bajocontent');
$righttop	= $this->countModules('righttop');
$inset 		= $this->countModules('inset');
$outset 	= $this->countModules('outset');
$rightbot 	= $this->countModules('rightbottom');
$user5 		= $this->countModules('user5');
$user6 		= $this->countModules('user6');
$user7 		= $this->countModules('user7');
$footmenu 	= $this->countModules('footmenu');
$foot 		= $this->countModules('foot');
$right 		= ($righttop || $rightbot || $inset || $outset);
$left 		= ($lefttop || $insetleft || $outsetleft || $leftbottom);

// Parámetros
$app 		= JFactory::getApplication();
$params 	= $app->getTemplate(true)->params;
$sitename 	= $params->get('sitename');			// Nombre del sitio
$siteslogan	= $params->get('siteslogan');		// Eslogan del sitio
$tslogan	= $params->get('typeslogan');		// Tipo de eslogan
$logo 		= $params->get('logo'); 			// Logo URL IMG
$bodyw 		= $params->get('body');				// Ancho del cuerpo
$banners 	= $params->get('banners');			// Banners			
$user1w		= $params->get('user1');			// User 1 
$userstop 	= $params->get('userstop');			// Users Top (tres posiciones)
$usersbot	= $params->get('usersbottom');		// Users Bottom (tres espacios)
$leftw		= $params->get('left');				// Left
$contentw	= $params->get('content');			// Contenido
$rightw		= $params->get('right');			// Right
$rightc		= $params->get('right');			// Right calculo
$typemenu 	= $params->get('tipomenu');			// Tipo de Menú 
$ga			= $params->get('isanalytics');		// Google Analytics 
$gacode		= $params->get('analyticsid');		// Google Analytics
$typobody 	= $params->get('typobody');			// Tipo de Tipografía de body 
$typoespecial 	= $params->get('typoespecial');			// Tipo de Tipografía de cabeceras, menus y especiales 
$force 	= $params->get('force');			// Tipo de Tipografía de cabeceras, menus y especiales 

// Avertencia no eslogan 
(strlen($siteslogan)>0)? $siteslogan = $siteslogan : $siteslogan = JTEXT::_('TPL_NOSLOGAN_ERROR');

 // Banner1 - Banner2
$totalbanners 	= (int) $banners[0] + (int) $banners[1];
(isset($banners[0])) ?	$banner1w = 'width: '.round((($banners[0] / $totalbanners) * 100),2).'%' : $banner1w = 'display:none;';
(isset($banners[1])) ?	$banner2w = 'width: '.round((($banners[1] / $totalbanners) * 100),2).'%' : $banner2w = 'display:none;';

// User 2 - User 3 - User 4 
$totalusert = (int) $userstop[0] + (int) $userstop[1] + (int) $userstop[2];
(isset($userstop[0])) ?	$user2w = 'width: '.round((($userstop[0] / $totalusert) * 100),2).'%' : $user2w = 'display:none;';
(isset($userstop[1])) ?	$user3w = 'width: '.round((($userstop[1] / $totalusert) * 100),2).'%' : $user3w = 'display:none;';
(isset($userstop[2])) ?	$user4w = 'width: '.round((($userstop[2] / $totalusert) * 100),2).'%' : $user4w = 'display:none;';

// Left - Content - Right
//esto es lo nuevo
if ($force == '0')
{
	$contentw   = 100 - ($leftw + $rightw);
}
	
if ( !empty($left)  && !empty($right))
{
	$leftw	  	= 'width: '.$leftw.'%';
	$contentw   = 'width: '.$contentw.'%';
	$rightw 	= 'width: '.$rightw.'%';
} 
elseif (empty($right) && !empty($left)) 
{
	$leftw	  = 'width: '.$leftw.'%';
	$contentw = 'width: '.$contentw.'%';
	$rigthw	  = 'width: "0"';	
} 
elseif (empty($left) && !empty($right))
{
	$rightw	  = 'width: '.$rightw.'%';
	$contentw = 'width: '.$contentw.'%';
	$leftw	  = 'width: 0';
}
elseif (empty($left) &&  empty($right))
{
	$rightw	  = 'width: "0";';
	$contentw = 'width: "100%"';
	$leftw	  = 'width: "0";';
}


// User 5 - User 6 - User 7
$totaluserb 	= (int) $usersbot[0] + (int) $usersbot[1] + (int) $usersbot[2];
(isset($usersbot[0])) ?	$user5w = 'width: '.round((($usersbot[0] / $totaluserb) * 100),2).'%' : $user5w = 'display:none;';
(isset($usersbot[1])) ?	$user6w = 'width: '.round((($usersbot[1] / $totaluserb) * 100),2).'%' : $user6w = 'display:none;';
(isset($usersbot[2])) ?	$user7w = 'width: '.round((($usersbot[2] / $totaluserb) * 100),2).'%' : $user7w = 'display:none;';

?>
