<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	templates.bluestork
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

$app = JFactory::getApplication();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo  $this->language; ?>" lang="<?php echo  $this->language; ?>" dir="<?php echo  $this->direction; ?>" id="minwidth" >
<head>
<jdoc:include type="head" />

<link rel="stylesheet" href="templates/system/css/system.css" type="text/css" />
<link href="templates/<?php echo  $this->template ?>/css/template.css" rel="stylesheet" type="text/css" />
<?php if ($this->direction == 'rtl') : ?>
	<link href="templates/<?php echo  $this->template ?>/css/template_rtl.css" rel="stylesheet" type="text/css" />
<?php endif; ?>

<!--[if IE 7]>
<link href="templates/<?php echo  $this->template ?>/css/ie7.css" rel="stylesheet" type="text/css" />
<![endif]-->

<!--[if lte IE 6]>
<link href="templates/<?php echo  $this->template ?>/css/ie6.css" rel="stylesheet" type="text/css" />
<![endif]-->

<?php if ($this->params->get('useRoundedCorners')) : ?>
	<link rel="stylesheet" type="text/css" href="templates/<?php echo  $this->template ?>/css/rounded.css" />
<?php else : ?>
	<link rel="stylesheet" type="text/css" href="templates/<?php echo  $this->template ?>/css/norounded.css" />
<?php endif; ?>


<?php if ($this->params->get('textBig')) : ?>
	<link rel="stylesheet" type="text/css" href="templates/<?php echo  $this->template ?>/css/textbig.css" />
<?php endif; ?>

<?php if ($this->params->get('highContrast')) : ?>
	<link rel="stylesheet" type="text/css" href="templates/<?php echo  $this->template ?>/css/highcontrast.css" />
<?php endif; ?>

</head>
<body id="minwidth-body">
	<div id="border-top" class="h_blue">
		<div>
			<div>
				<span class="logo"><a href="http://www.joomla.org" target="_blank"><img src="templates/<?php echo  $this->template ?>/images/logo.png" alt="Joomla!" /></a></span>
				<span class="title"><a href="index.php"><?php echo $this->params->get('showSiteName') ? $app->getCfg('sitename') : JText::_('Administration'); ?></a></span>
			</div>
		</div>
	</div>
	<div id="header-box">
		<div id="module-status">
			<jdoc:include type="modules" name="status"  />
		</div>
		<div id="module-menu">
			<jdoc:include type="modules" name="menu" />
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
					<div class="adminform">
						<div class="cpanel-left">
							<jdoc:include type="modules" name="icon" />
						</div>
						<div class="cpanel-right">
								<jdoc:include type="component" />
						</div>
					</div>
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
		<jdoc:include type="modules" name="footer" style="none"  />

</body>
</html>
