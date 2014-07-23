<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Template.hathor
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>" >
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title><?php echo $this->title; ?> <?php echo htmlspecialchars($this->error->getMessage()); ?></title>
	<link rel="stylesheet" href="templates/<?php echo  $this->template; ?>/css/error.css" type="text/css" />
	<?php // If debug  mode
		$debug = JFactory::getConfig()->get('debug_lang');
		if ((defined('JDEBUG') && JDEBUG) || $debug) : ?>
		<!-- Load additional CSS styles for debug mode-->
		<link rel="stylesheet" href="<?php echo JUri::root(); ?>/media/cms/css/debug.css" type="text/css" />
	<?php endif; ?>

	<!-- Load additional CSS styles for rtl sites -->
	<?php if ($this->direction == 'rtl') : ?>
		<link href="templates/<?php echo  $this->template; ?>/css/template_rtl.css" rel="stylesheet" type="text/css" />
	<?php endif; ?>

</head>
<body class="errors">
	<div>
		<h1>
			<?php echo $this->error->getCode(); ?> - <?php echo JText::_('JERROR_AN_ERROR_HAS_OCCURRED'); ?>
		</h1>
	</div>
	<div>
		<p><?php echo htmlspecialchars($this->error->getMessage()); ?></p>
		<p><a href="index.php"><?php echo JText::_('JGLOBAL_TPL_CPANEL_LINK_TEXT'); ?></a></p>
		<?php if ($this->debug) : ?>
			<?php echo $this->renderBacktrace(); ?>
		<?php endif; ?>
	</div>
	<div class="clr"></div>
	<noscript>
			<?php echo JText::_('JGLOBAL_WARNJAVASCRIPT'); ?>
	</noscript>
</body>
</html>
