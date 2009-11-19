<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_weblinks
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.DS.'helpers'.DS.'html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
?>
<script type="text/javascript">
<!--
	function submitbutton(task)
	{
		if (task == 'weblink.cancel' || document.formvalidator.isValid(document.id('weblink-form'))) {
			submitform(task);
		}
		// @todo Deal with the editor methods
		submitform(task);
	}
// -->
</script>

<form action="<?php JRoute::_('index.php?option=com_weblinks'); ?>" method="post" name="adminForm" id="weblink-form" class="form-validate">
<div class="width-70 fltlft">
	<fieldset class="adminform">
		<legend><?php echo empty($this->item->id) ? JText::_('Weblinks_New_Weblink') : JText::sprintf('Weblinks_Edit_Weblink', $this->item->id); ?></legend>

			<?php echo $this->form->getLabel('title'); ?>
			<?php echo $this->form->getInput('title'); ?>

			<?php echo $this->form->getLabel('alias'); ?>
			<?php echo $this->form->getInput('alias'); ?>

			<?php echo $this->form->getLabel('url'); ?>
			<?php echo $this->form->getInput('url'); ?>

			<?php echo $this->form->getLabel('state'); ?>
			<?php echo $this->form->getInput('state'); ?>

			<?php echo $this->form->getLabel('catid'); ?>
			<?php echo $this->form->getInput('catid'); ?>

			<?php echo $this->form->getLabel('ordering'); ?>
			<div id="jform_ordering" class="fltlft"><?php echo $this->form->getInput('ordering'); ?></div>

			<?php echo $this->form->getLabel('description'); ?>
			<div class="clr"></div>
			<?php echo $this->form->getInput('description'); ?>

			<?php echo $this->form->getLabel('access'); ?>
			<?php echo $this->form->getInput('access'); ?>

			<?php echo $this->form->getLabel('language'); ?>
			<?php echo $this->form->getInput('language'); ?>
	</fieldset>
</div>
<div class="width-30 fltrt">
	<fieldset class="adminform">
		<legend><?php echo JText::_('Weblinks_Options'); ?></legend>

		<?php foreach($this->form->getFields('params') as $field): ?>
			<?php if ($field->hidden): ?>
				<?php echo $field->input; ?>
			<?php else: ?>
			<div class="paramrow">
				<?php echo $field->label; ?>
				<?php echo $field->input; ?>
			</div>
			<?php endif; ?>
		<?php endforeach; ?>

	</fieldset>
</div>

<div class="clr"></div>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>