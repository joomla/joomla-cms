<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_banners
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
		if (task == 'banner.cancel' || document.formvalidator.isValid(document.id('banner-form'))) {
			submitform(task);
		}
		// @todo Deal with the editor methods
		submitform(task);
	}
	window.addEvent('domready', function() {
		document.id('jform_type0').addEvent('click', function(e){
			document.id('image').setStyle('display', 'block');
			document.id('custom').setStyle('display', 'none');
			document.id('jform_image_url').fireEvent('change');
		});
		document.id('jform_type1').addEvent('click', function(e){
			document.id('image').setStyle('display', 'none');
			document.id('flash').setStyle('display', 'none');
			document.id('alt').setStyle('display', 'none');
			document.id('custom').setStyle('display', 'block');
		});
		document.id('jform_image_url').addEvent('change',function(e){
			regex=/\.swf$/;
			if(regex.test(document.id('jform_image_url').value))
			{
				document.id('flash').setStyle('display', 'block');
				document.id('alt').setStyle('display', 'none');
			}
			else
			{
				document.id('flash').setStyle('display', 'none');
				document.id('alt').setStyle('display', 'block');
			}
		});
		if(document.id('jform_type0').checked==true)
		{
			document.id('jform_type0').fireEvent('click');
		}
		else
		{
			document.id('jform_type1').fireEvent('click');
		}
		document.id('jform_image_url').fireEvent('change');
	});


// -->
</script>

<form action="<?php JRoute::_('index.php?option=com_banners'); ?>" method="post" name="adminForm" id="banner-form" class="form-validate">
<div class="width-60 fltlft">
	<fieldset class="adminform">
		<legend><?php echo empty($this->item->id) ? JText::_('Banners_New_Banner') : JText::sprintf('Banners_Edit_Banner', $this->item->id); ?></legend>

		<?php foreach($this->form->getFields() as $field): ?>
			<?php if (!$field->hidden): ?>
				<?php echo $field->label; ?>
			<?php endif; ?>
			<?php echo $field->input; ?>
		<?php endforeach; ?>

		<div id="image">
			<?php foreach($this->form->getFields('image') as $field): ?>
				<?php if (!$field->hidden): ?>
					<?php echo $field->label; ?>
				<?php endif; ?>
				<?php echo $field->input; ?>
			<?php endforeach; ?>
		</div>
		<div id="flash">
			<?php foreach($this->form->getFields('flash') as $field): ?>
				<?php if (!$field->hidden): ?>
					<?php echo $field->label; ?>
				<?php endif; ?>
				<?php echo $field->input; ?>
			<?php endforeach; ?>
		</div>
		<div id="alt">
			<?php foreach($this->form->getFields('alt') as $field): ?>
				<?php if (!$field->hidden): ?>
					<?php echo $field->label; ?>
				<?php endif; ?>
				<?php echo $field->input; ?>
			<?php endforeach; ?>
		</div>
		<div id="custom">
			<?php foreach($this->form->getFields('custom') as $field): ?>
				<?php if (!$field->hidden): ?>
					<?php echo $field->label; ?>
				<?php endif; ?>
				<?php echo $field->input; ?>
			<?php endforeach; ?>
		</div>
	</fieldset>
</div>
<div class="width-40 fltrt">
	<?php echo JHtml::_('sliders.start','banner-sliders-'.$this->item->id, array('useCookie'=>1)); ?>

	<?php echo JHtml::_('sliders.panel',JText::_('Banners_Publishing_Details'), 'publishing-details'); ?>
		<fieldset class="adminform">
			<?php foreach($this->form->getFields('publish') as $field): ?>
				<?php if (!$field->hidden): ?>
					<?php echo $field->label; ?>
				<?php endif; ?>
				<?php echo $field->input; ?>
			<?php endforeach; ?>
		</fieldset>

	<?php echo JHtml::_('sliders.panel',JText::_('Banners_Metadata'), 'metadata'); ?>
		<fieldset class="adminform">
			<?php foreach($this->form->getFields('metadata') as $field): ?>
				<?php if (!$field->hidden): ?>
					<?php echo $field->label; ?>
				<?php endif; ?>
				<?php echo $field->input; ?>
			<?php endforeach; ?>
		</fieldset>

	<?php echo JHtml::_('sliders.end'); ?>
</div>

<div class="clr"></div>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
