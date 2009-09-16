<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_menus
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
?>

<script type="text/javascript">
<!--
	function submitbutton(task)
	{
		if (task == 'item.cancel' || document.formvalidator.isValid($('item-form'))) {
			submitform(task);
		}
	}
// -->
</script>

<form action="<?php JRoute::_('index.php?option=com_menus'); ?>" method="post" name="adminForm" id="item-form" class="form-validate">

<div class="width-45">
	<fieldset>
		<legend><?php echo JText::_('Menus_Item_Details');?></legend>
		
			<?php echo $this->form->getLabel('title'); ?>
				<?php echo $this->form->getInput('title'); ?>
			
				<?php echo $this->form->getLabel('alias'); ?>
				<?php echo $this->form->getInput('alias'); ?>
		
				<?php echo $this->form->getLabel('menutype'); ?>
				<?php echo $this->form->getInput('menutype'); ?>
			
				<?php echo $this->form->getLabel('parent_id'); ?>
				<?php echo $this->form->getInput('parent_id'); ?>
			
			
			
				<?php echo $this->form->getLabel('link'); ?>
				<?php echo $this->form->getInput('link'); ?>
			
				<?php echo $this->form->getLabel('type'); ?>
				<?php echo $this->form->getInput('type'); ?>
			
				<?php echo $this->form->getLabel('published'); ?>
				<?php echo $this->form->getInput('published'); ?>
			
				<?php echo $this->form->getLabel('browserNav'); ?>
				<?php echo $this->form->getInput('browserNav'); ?>
			
				<?php echo $this->form->getLabel('access'); ?>
				<?php echo $this->form->getInput('access'); ?>
			
				<?php echo $this->form->getLabel('home'); ?>
				<?php echo $this->form->getInput('home'); ?>
			
				<?php echo $this->form->getLabel('template_id'); ?>
				<?php echo $this->form->getInput('template_id'); ?>
			
	</fieldset>
</div>

<div class="width-45" style="clear:right;">
	<fieldset>
		<legend><?php echo JText::_('Menus_Item_Module_Assignment'); ?></legend>
		<?php echo $this->loadTemplate('modules'); ?>
	</fieldset>
</div>
<div class="width-45">
		<?php echo $this->loadTemplate('options'); ?>
</div>


	<input type="hidden" name="task" value="" />
	<?php echo $this->form->getInput('component_id'); ?>
	<?php echo JHtml::_('form.token'); ?>
</form>
<div class="clr"></div>
