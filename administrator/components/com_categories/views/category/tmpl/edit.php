<?php
/**
 * @version	 $Id$
 * @package		Joomla.Administrator
 * @subpackage	com_categories
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

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
		if (task == 'category.cancel' || document.formvalidator.isValid($('item-form'))) {
			submitform(task);
		}
	}
// -->
</script>

<form action="<?php JRoute::_('index.php?option=com_menus'); ?>" method="post" name="adminForm" id="item-form">
	<div class="width-60" style="float:left;">
		<fieldset>
			<legend><?php echo JText::_('Categories_Fieldset_Details');?></legend>
			<ol>
				<li>
					<?php echo $this->form->getLabel('extension'); ?><br />
					<?php echo $this->form->getInput('extension'); ?>
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
					<?php echo $this->form->getLabel('published'); ?><br />
					<?php echo $this->form->getInput('published'); ?>
				</li>
				<li>
					<?php echo $this->form->getLabel('access'); ?><br />
					<?php echo $this->form->getInput('access'); ?>
				</li>
			</ol>
		</fieldset>
	</div>

	<div class="width-40" style="float:left;">
		<fieldset>
			<legend><?php echo JText::_('Categories_Fieldset_Options'); ?></legend>
			<?php echo $this->loadTemplate('options'); ?>
		</fieldset>

		<fieldset>
			<legend><?php echo JText::_('Categories_Fieldset_Metadata'); ?></legend>
			<?php echo $this->loadTemplate('metadata'); ?>
		</fieldset>
	</div>

	<br class="clr" />
	<?php echo $this->form->getLabel('description'); ?><br />
	<?php echo $this->form->getInput('description'); ?>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
<div class="clr"></div>
