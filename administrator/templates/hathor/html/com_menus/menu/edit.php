<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	Templates.hathor
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		1.6
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
?>

<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'menu.cancel' || document.formvalidator.isValid(document.id('item-form'))) {
			Joomla.submitform(task, document.getElementById('item-form'));
		}
	}
</script>

<div class="menu-edit">

<form action="<?php echo JRoute::_('index.php?option=com_menus&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="item-form">
<div class="col main-section">
	<fieldset class="adminform">
		<legend><?php echo JText::_('COM_MENUS_MENU_DETAILS');?></legend>
			<ul class="adminformlist">
				<li><?php echo $this->form->getLabel('title'); ?>
				<?php echo $this->form->getInput('title'); ?></li>

				<li><?php echo $this->form->getLabel('menutype'); ?>
				<?php echo $this->form->getInput('menutype'); ?></li>

				<li><?php echo $this->form->getLabel('description'); ?>
				<?php echo $this->form->getInput('description'); ?></li>
			</ul>
	</fieldset>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
<div class="clr"></div>
</div>
