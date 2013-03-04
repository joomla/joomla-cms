<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	Templates.hathor
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		1.6
 */

// no direct access
defined('_JEXEC') or die;

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>" >
<head>
	<link rel="stylesheet" href="templates/<?php echo  $this->template ?>/css/error.css" type="text/css" />

	<!-- Load additional CSS styles for rtl sites -->
<?php if ($this->direction == 'rtl') : ?>
	<link href="templates/<?php echo  $this->template ?>/css/template_rtl.css" rel="stylesheet" type="text/css" />
<?php endif; ?>

</head>
<body class="errors">
	<div>
		<h1>
			<?php echo $this->error->getCode() ?> - <?php echo JText::_('JERROR_AN_ERROR_HAS_OCCURRED') ?>
		</h1>
	</div>
	<div>
		<p><?php echo $this->error->getMessage(); ?></p>
		<p><a href="index.php"><?php echo JText::_('JGLOBAL_TPL_CPANEL_LINK_TEXT') ?></a></p>
		<?php if ($this->debug) :
			echo $this->renderBacktrace();
		endif; ?>
	</div>
	<div class="clr"></div>
	<noscript>
			<?php echo  JText::_('JGLOBAL_WARNJAVASCRIPT') ?>
	</noscript>
</body>
</html>
