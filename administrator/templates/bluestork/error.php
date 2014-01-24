<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	Templates.bluestork
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		1.6
 */

// no direct access
defined('_JEXEC') or die;

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo  $this->language; ?>" lang="<?php echo  $this->language; ?>" dir="<?php echo  $this->direction; ?>" >
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title><?php echo $this->error->getCode(); ?> - <?php echo htmlspecialchars($this->error->getMessage()); ?></title>
	<link rel="stylesheet" href="templates/system/css/system.css" type="text/css" />
	<link href="templates/<?php echo  $this->template ?>/css/template.css" rel="stylesheet" type="text/css" />

	<?php if ($this->direction == 'rtl') : ?>
		<link href="templates/<?php echo  $this->template ?>/css/template_rtl.css" rel="stylesheet" type="text/css" />
	<?php endif; ?>

	<!--[if gte IE 7]>
	<link href="templates/<?php echo  $this->template ?>/css/ie7.css" rel="stylesheet" type="text/css" />
	<![endif]-->
</head>
<body id="minwidth-body">
	<div id="border-top" class="h_blue">
		<span class="logo"><a href="http://www.joomla.org" target="_blank"><img src="templates/<?php echo  $this->template ?>/images/logo.png" alt="Joomla!" /></a></span>
	</div>
	<div id="content-box">
		<div class="border">
			<div class="padding">
				<h1><?php echo $this->error->getCode() ?> - <?php echo JText::_('JERROR_AN_ERROR_HAS_OCCURRED') ?></h1>
				<p><?php echo htmlspecialchars($this->error->getMessage()); ?></p>
				<p><a href="index.php"><?php echo JText::_('JGLOBAL_TPL_CPANEL_LINK_TEXT') ?></a></p>
				<p><?php if ($this->debug) :
					echo $this->renderBacktrace();
				endif; ?></p>
			</div>
		</div>
	</div>
	<div class="clr"></div>
	<noscript>
		<?php echo  JText::_('JGLOBAL_WARNJAVASCRIPT') ?>
	</noscript>
	<div class="clr"></div>
	<div id="border-bottom"><div><div></div></div></div>

</body>
</html>
