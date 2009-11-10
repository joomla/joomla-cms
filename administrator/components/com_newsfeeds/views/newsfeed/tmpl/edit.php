<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_newsfeeds
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access.
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');

?>
<script type="text/javascript">
<!--
	function submitbutton(task)
	{
		// @todo Validation is currently busted
		//if (task == 'newsfeed.cancel' || document.formvalidator.isValid(document.id('newsfeed-form'))) {
		if (task == 'newsfeed.cancel') {
			submitform(task);
		}
		// @todo Deal with the editor methods
		submitform(task);
	}
// -->
</script>

<form action="<?php JRoute::_('index.php?option=com_newsfeeds'); ?>" method="post" name="adminForm" id="newsfeed-form" class="form-validate">
<div class="width-60 fltlft">
	<fieldset class="adminform">
		<legend><?php echo empty($this->item->id) ? JText::_('Newsfeeds_New_Newsfeed') : JText::sprintf('Newsfeeds_Edit_Newsfeed', $this->item->id); ?></legend>

			<?php echo $this->form->getLabel('name'); ?>
			<?php echo $this->form->getInput('name'); ?>

			<?php echo $this->form->getLabel('alias'); ?>
			<?php echo $this->form->getInput('alias'); ?>

			<?php echo $this->form->getLabel('published'); ?>
			<?php echo $this->form->getInput('published'); ?>
			
			<?php echo $this->form->getLabel('access'); ?>
			<?php echo $this->form->getInput('access'); ?>

			<?php echo $this->form->getLabel('catid'); ?>
			<?php echo $this->form->getInput('catid'); ?>

			<?php echo $this->form->getLabel('link'); ?>
			<?php echo $this->form->getInput('link'); ?>
			
			<?php echo $this->form->getLabel('ordering'); ?>
			<?php echo $this->form->getInput('ordering'); ?>

	</fieldset>
</div>

<div class="width-40 fltrt">
	<fieldset class="adminform">
		<legend><?php echo JText::_('Newsfeeds_Options'); ?></legend>
		
			<?php echo $this->form->getLabel('numarticles'); ?>
			<?php echo $this->form->getInput('numarticles'); ?>

			<?php echo $this->form->getLabel('cache_time'); ?>
			<?php echo $this->form->getInput('cache_time'); ?>

			<?php echo $this->form->getLabel('rtl'); ?>
			<?php echo $this->form->getInput('rtl'); ?>

			<?php echo $this->form->getLabel('language'); ?>
			<?php echo $this->form->getInput('language'); ?>
			
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