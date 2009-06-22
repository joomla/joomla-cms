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
	<fieldset style="width:45%;float:left">
		<legend><?php echo JText::_('Menus_Item_Details');?></legend>
		<ol>
			<li>
				<?php echo $this->form->getLabel('menutype'); ?><br />
				<?php echo $this->form->getInput('menutype'); ?>
			</li>
			<li>
				<?php echo $this->form->getLabel('parent_id'); ?><br />
				<?php echo $this->form->getInput('parent_id'); ?>
			</li>
			<li>
				<?php echo $this->form->getLabel('title'); ?><br />
				<?php echo $this->form->getInput('title'); ?>
			</li>
			<li>
				<?php echo $this->form->getLabel('alias'); ?><br />
				<?php echo $this->form->getInput('alias'); ?>
			</li>
			<li>
				<?php echo $this->form->getLabel('link'); ?><br />
				<?php echo $this->form->getInput('link'); ?>
			</li>
			<li>
				<?php echo $this->form->getLabel('type'); ?><br />
				<?php echo $this->form->getInput('type'); ?>
			</li>
			<li>
				<?php echo $this->form->getLabel('published'); ?><br />
				<?php echo $this->form->getInput('published'); ?>
			</li>
			<li>
				<?php echo $this->form->getLabel('ordering'); ?><br />
				<?php echo $this->form->getInput('ordering'); ?>
			</li>
			<li>
				<?php echo $this->form->getLabel('browserNav'); ?><br />
				<?php echo $this->form->getInput('browserNav'); ?>
			</li>
			<li>
				<?php echo $this->form->getLabel('access'); ?><br />
				<?php echo $this->form->getInput('access'); ?>
			</li>
			<li>
				<?php echo $this->form->getLabel('home'); ?><br />
				<?php echo $this->form->getInput('home'); ?>
			</li>
			<li>
				<?php echo $this->form->getLabel('template_id'); ?><br />
				<?php echo $this->form->getInput('template_id'); ?>
			</li>
		</ol>
	</fieldset>

	<fieldset style="width:45%; float:left;">
		<legend><?php echo JText::_('Menus_Item_Options'); ?></legend>
		<?php echo $this->loadTemplate('options'); ?>
	</fieldset>

	<fieldset style="width:45%; float:left;">
		<legend><?php echo JText::_('Menus_Item_Module_Assignment'); ?></legend>
		<?php echo $this->loadTemplate('modules'); ?>
	</fieldset>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
<div class="clr"></div>
