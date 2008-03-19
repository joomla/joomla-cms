<?php
/**
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>" >
<head>
<jdoc:include type="head" />

<link rel="stylesheet" href="templates/system/css/system.css" type="text/css" />
<link href="templates/<?php echo $this->template ?>/css/login.css" rel="stylesheet" type="text/css" />

<?php  if($this->direction == 'rtl') : ?>
	<link href="templates/<?php echo $this->template ?>/css/login_rtl.css" rel="stylesheet" type="text/css" />
<?php  endif; ?>

<!--[if IE 7]>
<link href="templates/<?php echo  $this->template ?>/css/ie7.css" rel="stylesheet" type="text/css" />
<![endif]-->

<!--[if lte IE 6]>
<link href="templates/<?php echo  $this->template ?>/css/ie6.css" rel="stylesheet" type="text/css" />
<![endif]-->

<?php  if($this->params->get('useRoundedCorners')) : ?>
	<link rel="stylesheet" type="text/css" href="templates/<?php echo $this->template ?>/css/rounded.css" />
<?php  else : ?>
	<link rel="stylesheet" type="text/css" href="templates/<?php echo $this->template ?>/css/norounded.css" />
<?php  endif; ?>

<script language="javascript" type="text/javascript">
	function setFocus() {
		document.login.username.select();
		document.login.username.focus();
	}
</script>
</head>
<body onload="javascript:setFocus()">
	<div id="border-top" class="<?php echo $this->params->get('headerColor','green');?>">
		<div>
			<div>
				<span class="title"><?php echo $this->params->get('showSiteName') ? $mainframe->getCfg( 'sitename' ) : JText::_('Administration'); ?></span>
			</div>
		</div>
	</div>
	<div id="content-box">
		<div class="padding">
			<div id="element-box" class="login">
				<div class="t">
					<div class="t">
						<div class="t"></div>
					</div>
				</div>
				<div class="m">

					<h1><?php echo JText::_('Joomla! Administration Login') ?></h1>
					<jdoc:include type="message" />
					<jdoc:include type="component" />
					<p><?php echo JText::_('DESCUSEVALIDLOGIN') ?></p>
					<p>
						<a href="<?php echo JURI::root(); ?>"><?php echo JText::_('Return to site Home Page') ?></a>
					</p>
					<div id="lock"></div>
					<div class="clr"></div>
				</div>
				<div class="b">
					<div class="b">
						<div class="b"></div>
					</div>
				</div>
			</div>
			<noscript>
				<?php echo JText::_('WARNJAVASCRIPT') ?>
			</noscript>
			<div class="clr"></div>
		</div>
	</div>
	<div id="border-bottom"><div><div></div></div>
</div>
<div id="footer">
	<p class="copyright">
		<a href="http://www.joomla.org" target="_blank">Joomla!</a>
		<?php echo JText::_('ISFREESOFTWARE') ?>
	</p>
</div>
</body>
</html>
