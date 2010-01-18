<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
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
		if (task == 'article.cancel' || document.formvalidator.isValid(document.id('item-form'))) {
			<?php echo $this->form->getField('articletext')->save(); ?>
			submitform(task);
		}
		else {
			alert('<?php echo $this->escape(JText::_('JValidation_Form_failed'));?>');
		}
	}
// -->
</script>

<form action="<?php JRoute::_('index.php?option=com_content'); ?>" method="post" name="adminForm" id="item-form" class="form-validate">
	<div class="width-60 fltlft">
		<fieldset class="adminform">
		<legend><?php echo JText::_('ARTICLE_DETAILS'); ?></legend>

		<?php echo $this->form->getLabel('title'); ?>
		<?php echo $this->form->getInput('title'); ?>

		<?php echo $this->form->getLabel('alias'); ?>
		<?php echo $this->form->getInput('alias'); ?>

		<?php echo $this->form->getLabel('catid'); ?>
		<?php echo $this->form->getInput('catid'); ?>

		<?php echo $this->form->getLabel('state'); ?>
		<?php echo $this->form->getInput('state'); ?>

		<?php echo $this->form->getLabel('access'); ?>
		<?php echo $this->form->getInput('access'); ?>
		
		<?php echo $this->form->getLabel('language'); ?>
		<?php echo $this->form->getInput('language'); ?>

		<div class="clr"></div>
		<?php echo $this->form->getLabel('articletext'); ?>
		<div class="clr"></div>
		<?php echo $this->form->getInput('articletext'); ?>
		</fieldset>
	</div>

	<div class="width-40 fltrt">
		<?php echo JHtml::_('sliders.start','content-sliders-'.$this->item->id, array('useCookie'=>1)); ?>

		<?php echo JHtml::_('sliders.panel',JText::_('Content_Fieldset_Publishing'), 'publishing-details'); ?>

		<fieldset class="panelform">

			<?php echo $this->form->getLabel('created_by'); ?>
			<?php echo $this->form->getInput('created_by'); ?>

			<?php echo $this->form->getLabel('created_by_alias'); ?>
			<?php echo $this->form->getInput('created_by_alias'); ?>

			<?php echo $this->form->getLabel('created'); ?>
			<?php echo $this->form->getInput('created'); ?>

			<?php echo $this->form->getLabel('publish_up'); ?>
			<?php echo $this->form->getInput('publish_up'); ?>

			<?php echo $this->form->getLabel('publish_down'); ?>
			<?php echo $this->form->getInput('publish_down'); ?>

			<?php echo $this->form->getLabel('modified'); ?>
			<?php echo $this->form->getInput('modified'); ?>

			<?php echo $this->form->getLabel('version'); ?>
			<?php echo $this->form->getInput('version'); ?>

			<?php echo $this->form->getLabel('hits'); ?>
			<?php echo $this->form->getInput('hits'); ?>

		</fieldset>

		<?php echo JHtml::_('sliders.panel',JText::_('Content_Fieldset_Options'), 'basic-options'); ?>
		<fieldset class="panelform">
		<?php foreach($this->form->getFields('attribs') as $field): ?>
			<?php if ($field->hidden): ?>
				<?php echo $field->input; ?>
			<?php else: ?>
				<?php echo $field->label; ?>
				<?php echo $field->input; ?>
			<?php endif; ?>
		<?php endforeach; ?>
		</fieldset>

		<?php echo JHtml::_('sliders.panel',JText::_('Content_Fieldset_Access'), 'access-rules'); ?>
		<fieldset class="panelform">
			<?php echo $this->form->getLabel('rules'); ?>
			<?php echo $this->form->getInput('rules'); ?>
		</fieldset>

		<?php echo JHtml::_('sliders.panel',JText::_('Content_Fieldset_Metadata'), 'meta-options'); ?>
		<fieldset class="panelform">

			<?php echo $this->form->getLabel('metadesc'); ?>
			<?php echo $this->form->getInput('metadesc'); ?>

			<?php echo $this->form->getLabel('metakey'); ?>
			<?php echo $this->form->getInput('metakey'); ?>

			<?php foreach($this->form->getFields('metadata') as $field): ?>
				<?php echo $field->label; ?>
				<?php echo $field->input; ?>
			<?php endforeach; ?>

			<?php echo $this->form->getLabel('xreference'); ?>
			<?php echo $this->form->getInput('xreference'); ?>
		</fieldset>

		<?php echo JHtml::_('sliders.end'); ?>
	</div>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
<div class="clr"></div>
