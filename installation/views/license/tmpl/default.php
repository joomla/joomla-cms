<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<div id="step">
	<div class="buttons">
		<div class="button"><a href="index.php?view=preinstall" onclick="return Install.goToPage('preinstall');" rel="prev" title="<?php echo JText::_('JPREVIOUS'); ?>"><?php echo JText::_('JPREVIOUS'); ?></a></div>
		<div class="button"><a href="index.php?view=database" onclick="return Install.goToPage('database');" rel="next" title="<?php echo JText::_('JNEXT'); ?>"><?php echo JText::_('JNEXT'); ?></a></div>
	</div>
	<h2><?php echo JText::_('INSTL_LICENSE'); ?></h2>
</div>

<form action="index.php" method="post" id="adminForm" class="form-validate">
	<div id="installer">
		<h3><?php echo JText::_('INSTL_GNU_GPL_LICENSE'); ?></h3>
		<iframe src="gpl.html" class="license" marginwidth="25" scrolling="auto"></iframe>
	</div>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
