<?php
/**
 * @package     Jokte.Site
 * @subpackage	jokteantu
 * @author 	    Equipo de desarrollo juuntos.
 * @copyleft    (comparte igual)  Jokte!
 * @license     GNU General Public License version 3 o superior.
*/
// Previene el acceso directo.
defined('_JEXEC') or die;

JHtml::_('behavior.framework', true);

/*
 * Carga script para calculo posiciones
 * Since:	Jokte 1.0
 * 
 */
require_once (JPATH_BASE . DS . 'templates' . DS . $this->template . '/asset/asset.php');
/*
 * Carga script para skins CSS
 * Since:	Jokte 1.0
 * 
 */
require_once (JPATH_BASE . DS . 'templates' . DS . $this->template . '/asset/skins.php');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<jdoc:include type="head" />
	<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/system/css/general.css" type="text/css" />
		<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/system/css/system.css" type="text/css" />
		<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/system/css/jokte.css" type="text/css" />
        <!--<script type="text/javascript" src="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/javascript/menus.js"></script>-->		
        <link type="text/css" rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/template.css" />
		 <link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template; ?>/css/menu/<?php echo $typemenu; ?>.css" type="text/css" />
		  <link type="text/css" rel="stylesheet" href="<?php echo $baseurlskin; ?>/color.css" />
		<!--[if lte IE 9]>
			<link href="<?php echo $baseurlskin; ?>/ie9.css" rel="stylesheet" type="text/css" />
		<![endif]-->	
		
		
<?php if ($typobody != "no-google") { ?>
<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=<?php echo $typobody; ?>" />
<?php } ?>
<?php if ($typoespecial != "no-google") { ?>
<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=<?php echo $typoespecial; ?>" />
<?php } ?>

<style type="text/css"> 
<?php if ($typobody != "no-google") { ?>
body {font-family: '<?php echo $typobody; ?>',FreeSans,Verdana, Geneva,Helvetica,Arial,Sans-Serif;
} 
<?php } ?>
<?php if ($typoespecial != "no-google") { ?>
h2, h3 ,h1,#slide, .moduletable .menu,span.dia, span.mes, span.año,.item-title,.content_vote, span.content_rating,.content_vote input.button,.moduletable_menu li,#izquierda .moduletable ul,#derecha .moduletable ul,.weblink-category .title a,.contador .weblink-count dd,.contador .weblink-count dt,label,input,legend,button,span.capital,#search-results .result-title{
font-family:'<?php echo $typoespecial; ?>',gargi,Verdana, Geneva, sans-serif;}
<?php } ?>
</style> 

</head>
<body class="contentpane">
	<div id="all">
		<div id="main">
			<jdoc:include type="message" />
			<jdoc:include type="component" />
		</div>
	</div>
</body>
</html>
