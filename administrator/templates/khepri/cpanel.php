<?php
/**
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
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
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo  $this->language; ?>" lang="<?php echo  $this->language; ?>" dir="<?php echo  $this->direction; ?>" >
<head>
<jdoc:include type="head" />

<?php if($this->direction == 'rtl') : ?>
	<link href="templates/<?php echo  $this->template ?>/css/template_rtl.css" rel="stylesheet" type="text/css" />
<?php else : ?>
	<link href="templates/<?php echo  $this->template ?>/css/template.css" rel="stylesheet" type="text/css" />
<?php endif; ?>

<!--[if lte IE 6]>
<link href="templates/<?php echo  $this->template ?>/css/ie.css" rel="stylesheet" type="text/css" />
<![endif]-->

<?php if($this->params->get('useRoundedCorners')) : ?>
	<link rel="stylesheet" type="text/css" href="templates/<?php echo  $this->template ?>/css/rounded.css" />
<?php else : ?>
	<link rel="stylesheet" type="text/css" href="templates/<?php echo  $this->template ?>/css/norounded.css" />
<?php endif; ?>

<script type="text/javascript" src="templates/<?php echo  $this->template ?>/js/menu.js"></script>

<script type="text/javascript" src="templates/<?php echo  $this->template ?>/js/fat.js"></script>
<script type="text/javascript" src="templates/<?php echo  $this->template ?>/js/index.js"></script>

</head>
<body>
	<div id="border-top">
		<div>
			<div>
				<span class="version"><?php echo  JText::_('Version') ?><?php echo  JVERSION; ?></span>
				<span class="title"><?php echo  JText::_('Administration') ?></span>
			</div>
		</div>
	</div>
	<div id="header-box">
		<div id="module-status">
			<jdoc:include type="modules" name="status"  />
		</div>
		<div id="module-menu">
			<jdoc:include type="module" name="menu" />
		</div>
		<div class="clr"></div>
	</div>
	<div id="content-box">
		<div class="border">
			<div class="padding">
				<div id="element-box">
					<jdoc:include type="message" />
					<div class="t">
						<div class="t">
							<div class="t"></div>
						</div>
					</div>
					<div class="m" >
						<table class="adminform">
						<tr>
							<td width="55%" valign="top">
								<jdoc:include type="modules" name="icon" />
							</td>
							<td width="45%" valign="top">
								<jdoc:include type="component" />
							</td>
						</tr>
						</table>
						<div class="clr"></div>
					</div>
					<div class="b">
						<div class="b">
							<div class="b"></div>
						</div>
					</div>
				</div>
				<noscript>
					<?php echo  JText::_('WARNJAVASCRIPT') ?>
				</noscript>
				<div class="clr"></div>
			</div>
		</div>
	</div>
	<div id="border-bottom"><div><div></div></div></div>
	<div id="footer">
		<p class="copyright">
			<a href="http://www.joomla.org" target="_blank">Joomla!</a>
			<?php echo  JText::_('ISFREESOFTWARE') ?>
		</p>
	</div>
</body>
</html>
