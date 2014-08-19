<?php
/**
 * @version		$Id: login.php 22010 2011-08-28 14:52:17Z infograf768 $
 * @package		Joomla.Administrator
 * @subpackage	Templates.storkantu fork Bluestork (c) Opensource Matters
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.filesystem.file');

$app = JFactory::getApplication();
$doc = JFactory::getDocument();

$doc->addStyleSheet('templates/system/css/system.css');
$doc->addStyleSheet('templates/'.$this->template.'/css/template.css');

if ($this->direction == 'rtl') {
	$doc->addStyleSheet('templates/'.$this->template.'/css/template_rtl.css');
}

/** Load specific language related css */
$lang = JFactory::getLanguage();
$file = 'language/'.$lang->getTag().'/'.$lang->getTag().'.css';
if (JFile::exists($file)) {
	$doc->addStyleSheet($file);
}

JHtml::_('behavior.noframes');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>" >
<head>
<jdoc:include type="head" />

<!--[if IE 7]>
<link href="templates/<?php echo  $this->template ?>/css/ie7.css" rel="stylesheet" type="text/css" />
<![endif]-->

<script type="text/javascript">
	window.addEvent('domready', function () {
		document.getElementById('form-login').username.select();
		document.getElementById('form-login').username.focus();
	});
</script>
</head>
<body>
<div id="mainlogin">
	<div id="Jkcontent-login">
			<div id="element-box-login" class="login">
			<jdoc:include type="message" />
				<div class="m wbg">
				
				<h1><?php echo JText::_('COM_LOGIN_JOOMLA_ADMINISTRATION_LOGIN') ?></h1>
					<div id="lock"></div>
					
					<jdoc:include type="component" />
					<p class="text"><?php echo JText::_('COM_LOGIN_VALID') ?></p>
					<p class="text2"><a href="<?php echo JURI::root(); ?>"><?php echo JText::_('COM_LOGIN_RETURN_TO_SITE_HOME_PAGE') ?></a></p>
					
				</div>
				
			</div>
			<noscript>
				<?php echo JText::_('JGLOBAL_WARNJAVASCRIPT') ?>
			</noscript>
	</div>	
	<div id="Jkfooter-login">
		<p class="copyright">
			<a href="http://www.jokte.org" target="_blank" title="Jokte! se libera bajo licencia GNU/GPL v3.0 y su nombre y logo tienen licencia Copyleft">JOKTE!</a> es un proyecto de software Libre para Latinoamérica de la <a href="http://juuntos.net" target=_"blank" title="Comunidad Latinoamericana de Tecnología Web">Comunidad Juuntos</a>
		</p>
		<p>
		<p>
	</div>
	<div class="login-img-back">
		<span class="login-img-text">Jokte! Jeyuu</span>
		<img src="<?php echo $this->baseurl.'/templates/'.$this->template.'/images/back-jeyuu.png' ?>" alt="jeyuu" />	
	</div>
</div>
</body>
</html>
