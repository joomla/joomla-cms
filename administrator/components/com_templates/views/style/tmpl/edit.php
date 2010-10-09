<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_templates
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
$user = JFactory::getUser();
$canDo = TemplatesHelper::getActions();
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'style.cancel' || document.formvalidator.isValid(document.id('style-form'))) {
			Joomla.submitform(task, document.getElementById('style-form'));
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_templates'); ?>" method="post" name="adminForm" id="style-form" class="form-validate">
	<div class="width-60 fltlft">
		<fieldset class="adminform">
			<legend><?php echo JText::_('JDETAILS');?></legend>

			<?php echo $this->form->getLabel('title'); ?>
			<?php echo $this->form->getInput('title'); ?>

			<?php echo $this->form->getLabel('template'); ?>
			<?php echo $this->form->getInput('template'); ?>

			<?php echo $this->form->getLabel('client_id'); ?>
			<?php echo $this->form->getInput('client_id'); ?>
			<input type="text" size="35" value="<?php echo $this->item->client_id == 0 ? JText::_('JSITE') : JText::_('JADMINISTRATOR'); ?>	" class="readonly" readonly="readonly" />

			<?php echo $this->form->getLabel('home'); ?>
			<?php echo $this->form->getInput('home'); ?>
			<div class="clr"></div>

			<?php if ($this->item->id) : ?>
				<?php echo $this->form->getLabel('id'); ?>
				<span class="readonly"><?php echo $this->item->id; ?></span>
			<?php endif; ?>
		</fieldset>
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>

	<div class="width-40 fltrt">
	<?php echo JHtml::_('sliders.start','template-sliders-'.$this->item->id); ?>

		<?php //get the menu parameters that are automatically set but may be modified.
			echo $this->loadTemplate('options'); ?>

		<div class="clr"></div>

	<?php echo JHtml::_('sliders.end'); ?>
	</div>
	<?php if ($user->authorise('core.edit','com_menu') && $this->item->client_id==0):?>
		<?php if ($canDo->get('core.edit.state')) : ?>
			<div class="width-60 fltlft">
			<?php echo $this->loadTemplate('assignment'); ?>
			</div>
			<?php endif; ?>
		<?php endif;?>

	<div class="clr"></div>
</form>
