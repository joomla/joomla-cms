<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Template.system
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>" >
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title><?php echo $this->error->getCode(); ?> - <?php echo htmlspecialchars($this->error->getMessage(), ENT_QUOTES, 'UTF-8'); ?></title>
	<link rel="stylesheet" href="templates/system/css/error.css" type="text/css" />
</head>
<body>
	<table width="550" align="center" class="outline">
	<tr>
		<td align="center">
			<h1>
				<?php echo $this->error->getCode() ?> - <?php echo JText::_('JERROR_AN_ERROR_HAS_OCCURRED') ?>
			</h1>
		</td>
	</tr>
	<tr>
		<td width="39%" align="center">
			<p><?php echo htmlspecialchars($this->error->getMessage(), ENT_QUOTES, 'UTF-8'); ?></p>
			<p><a href="index.php"><?php echo JText::_('JGLOBAL_TPL_CPANEL_LINK_TEXT') ?></a></p>
			<?php if ($this->debug) : ?>
				<div>
					<?php echo $this->renderBacktrace(); ?>
					<?php // Check if there are more Exceptions and render their data as well ?>
					<?php if ($this->error->getPrevious()) : ?>
						<?php $loop = true; ?>
						<?php // Reference $this->_error here and in the loop as setError() assigns errors to this property and we need this for the backtrace to work correctly ?>
						<?php // Make the first assignment to setError() outside the loop so the loop does not skip Exceptions ?>
						<?php $this->setError($this->_error->getPrevious()); ?>
						<?php while ($loop === true) : ?>
							<p><strong><?php echo JText::_('JERROR_LAYOUT_PREVIOUS_ERROR'); ?></strong></p>
							<p><?php echo htmlspecialchars($this->_error->getMessage(), ENT_QUOTES, 'UTF-8'); ?></p>
							<?php echo $this->renderBacktrace(); ?>
							<?php $loop = $this->setError($this->_error->getPrevious()); ?>
						<?php endwhile; ?>
						<?php // Reset the main error object to the base error ?>
						<?php $this->setError($this->error); ?>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</td>
	</tr>
	</table>
</body>
</html>
