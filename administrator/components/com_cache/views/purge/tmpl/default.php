<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cache
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>

<form action="<?php echo JRoute::_('index.php?option=com_cache'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
	<fieldset>
		<legend><?php echo JText::_('COM_CACHE_PURGE_EXPIRED_ITEMS'); ?></legend>
		<p><?php echo JText::_('COM_CACHE_PURGE_INSTRUCTIONS'); ?></p>
	</fieldset>
	<div class="alert">
		<p><?php echo JText::_('COM_CACHE_RESOURCE_INTENSIVE_WARNING'); ?></p>
	</div>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
