<?
/**
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?= $this->language; ?>" lang="<?= $this->language; ?>" dir="<?= $this->direction; ?>" >
<head>
<jdoc:include type="head" />

<? if($this->direction == 'rtl') : ?>
	<link href="templates/<?= $this->template ?>/css/login_rtl.css" rel="stylesheet" type="text/css" />
<? else : ?>
	<link href="templates/<?= $this->template ?>/css/login.css" rel="stylesheet" type="text/css" />
<? endif; ?>

<!--[if lte IE 6]>
<link href="templates/<?= $this->template ?>/css/ie.css" rel="stylesheet" type="text/css" />
<![endif]-->

<? if($this->params->get('useRoundedCorners')) : ?>
	<link rel="stylesheet" type="text/css" href="templates/<?= $this->template ?>/css/rounded.css" />
<? else : ?>
	<link rel="stylesheet" type="text/css" href="templates/<?= $this->template ?>/css/norounded.css" />
<? endif; ?>

<script language="javascript" type="text/javascript">
	function setFocus() {
		document.loginForm.username.select();
		document.loginForm.username.focus();
	}
</script>
</head>
<body onload="javascript:setFocus()">
	<div id="border-top">
		<div>
			<div>
				<span class="title"><?= JText::_('Administration') ?></span>
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
					<h1><?= JText::_('Joomla! Administration Login') ?></h1>
            		<jdoc:include type="module" name="login" style="rounded" id="section-box" />
					<p><?= JText::_('DESCUSEVALIDLOGIN') ?></p>
					<p>
						<a href="<?= $mainframe->getSiteURL(); ?>"><?= JText::_('Return to site Home Page') ?></a>
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
				<?= JText::_('WARNJAVASCRIPT') ?>
			</noscript>
			<div class="clr"></div>
		</div>
	</div>
	<div id="border-bottom"><div><div></div></div>
</div>
<div id="footer">
	<p class="copyright">
		<a href="http://www.joomla.org" target="_blank">Joomla!</a>
		<?= JText::_('ISFREESOFTWARE') ?>
	</p>
</div>
</body>
</html>
