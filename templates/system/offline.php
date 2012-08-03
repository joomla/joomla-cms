<?php
/**
 * @package     Jokte.Site
 * @subpackage	jokteantu
 * @author 	Equipo de desarrollo juuntos.
 * @copyright   Copyleft (Comparte igual) Open Jokte.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 * 
*/

defined('_JEXEC') or die;
$app = JFactory::getApplication();

$params = JFactory::getApplication()->getTemplate(true)->params;
$logo =  $params->get('logo');
$typobody 	= $params->get('typobody');			
$typoespecial 	= $params->get('typoespecial');	


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<jdoc:include type="head" />
	<link rel="stylesheet" href="<?php echo $this->baseurl ;?>/templates/<?php echo $this->template ;?>/css/general.css" type="text/css" />  
	<link rel="stylesheet" href="<?php echo $this->baseurl ;?>/templates/<?php echo $this->template ;?>/css/offline.css" type="text/css" /> 
	   
	 
	 
	 <?php if ($typobody != "no-google") { ?>
<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=<?php echo $typobody; ?>" />
<?php } ?>
<?php if ( $typoespecial != "no-google") { ?>
<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=<?php echo $typoespecial; ?>" />
<?php } ?>

<style type="text/css"> 
<?php if ($typobody != "no-google") { ?>
body {font-family: '<?php echo $typobody; ?>',FreeSans,Verdana, Geneva,Helvetica,Arial,Sans-Serif;
} 
<?php } ?>
<?php if ($typoespecial != "no-google") { ?>
h1 {
font-family:'<?php echo $typoespecial; ?>',gargi,Verdana, Geneva, sans-serif;}
<?php } ?>
</style> 
</head>
<body>
<jdoc:include type="message" />
<div id="bodyBg">
<div id="mensaje">
	
		<?php if ($app->getCfg('offline_image')) : ?>
		<img src="<?php echo $app->getCfg('offline_image'); ?>" alt="<?php echo $app->getCfg('sitename'); ?>" />
		<?php endif; ?>
		<h1>
			<?php echo $app->getCfg('sitename'); ?>
		</h1>
	<?php if ($app->getCfg('display_offline_message', 1) == 1 && str_replace(' ', '', $app->getCfg('offline_message')) != ''): ?>
		<p>
			<?php echo $app->getCfg('offline_message'); ?>
		</p>
	<?php elseif ($app->getCfg('display_offline_message', 1) == 2 && str_replace(' ', '', JText::_('JOFFLINE_MESSAGE')) != ''): ?>
		<p>
			<?php echo JText::_('JOFFLINE_MESSAGE'); ?>
		</p>
	<?php  endif; ?>
	</div>
	
	<div id="margen" class="outline">
	<form action="<?php echo JRoute::_('index.php', true); ?>" method="post" id="form-login">
	<fieldset class="input">
		<p id="form-login-username">
			<label for="username"><?php echo JText::_('JGLOBAL_USERNAME') ?></label>
			<input name="username" id="username" type="text" class="inputbox" alt="<?php echo JText::_('JGLOBAL_USERNAME') ?>" size="18" />
		</p>
		<p id="form-login-password">
			<label for="passwd"><?php echo JText::_('JGLOBAL_PASSWORD') ?></label>
			<input type="password" name="password" class="inputbox" size="18" alt="<?php echo JText::_('JGLOBAL_PASSWORD') ?>" id="passwd" />
		</p>
		<p id="form-login-remember">
			<label for="remember"><?php echo JText::_('JGLOBAL_REMEMBER_ME') ?></label>
			<input type="checkbox" name="remember" class="inputbox" value="yes" alt="<?php echo JText::_('JGLOBAL_REMEMBER_ME') ?>" id="remember" />
		</p>
		<input type="submit" name="Submit" class="button" value="<?php echo JText::_('JLOGIN') ?>" />
		<input type="hidden" name="option" value="com_users" />
		<input type="hidden" name="task" value="user.login" />
		<input type="hidden" name="return" value="<?php echo base64_encode(JURI::base()) ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</fieldset>
	</form>
	</div>
	<div class="joktepie">
		<a href="http://www.jokte.org" title="Visitar Proyecto Jokte!" target="_blank">Jokte! </a> es un proyecto con licencia GNU/GPL v2 hecho por la 
		<a href="http://www.juuntos.org" title="Visitar la Comunidad Latinoamericana Juuntos" target="_blank">Comunidad Lationamericana Juuntos</a> <br />
		
	</div>
	</div>
</body>
</html>
