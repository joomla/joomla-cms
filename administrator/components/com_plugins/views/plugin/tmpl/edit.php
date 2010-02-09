<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_plugins
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHTML::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
?>
<script type="text/javascript">
<!--
	function submitbutton(task)
	{
		if (task == 'plugin.cancel' || document.formvalidator.isValid(document.id('style-form'))) {
			submitform(task);
		}
	}
// -->
</script>

<form action="<?php JRoute::_('index.php?option=com_templates'); ?>" method="post" name="adminForm" id="style-form" class="form-validate">
	<div class="width-60 fltlft">
		<fieldset class="adminform">
			<?php if ($this->item->extension_id) : ?>
			<legend><?php echo JText::sprintf('JRecord_Number', $this->item->extension_id); ?></legend>
			<?php endif; ?>

			<?php echo $this->form->getLabel('name'); ?>
			<?php echo $this->form->getInput('name'); ?>

			<?php echo $this->form->getLabel('enabled'); ?>
			<?php echo $this->form->getInput('enabled'); ?>

			<?php echo $this->form->getLabel('access'); ?>
			<?php echo $this->form->getInput('access'); ?>

			<?php echo $this->form->getLabel('ordering'); ?>
			<?php echo $this->form->getInput('ordering'); ?>

			<?php echo $this->form->getLabel('folder'); ?>
			<?php echo $this->form->getInput('folder'); ?>

			<?php echo $this->form->getLabel('element'); ?>
			<?php echo $this->form->getInput('element'); ?>

			<br class="clr" />

			<!-- Plugin metadata -->
			<?php if ($this->item->xml) : ?>
				<?php if ($text = (string) $this->item->xml->description) : ?>
					<label>
						<?php echo JText::_('COM_PLUGINS_XML_DESCRIPTION'); ?>
					</label>
					<?php echo JText::_($text); ?>
				<?php endif; ?>
			<?php else : ?>
				<?php echo JText::_('COM_PLUGINS_XML_ERR'); ?>
			<?php endif; ?>

		</fieldset>
	</div>

	<div class="width-40 fltrt">
	<?php echo JHtml::_('sliders.start','plugin-sliders-'.$this->item->extension_id); ?>

		<?php echo $this->loadTemplate('options'); ?>

		<div class="clr"></div>

	<?php echo JHtml::_('sliders.end'); ?>
	</div>

	<div class="clr"></div>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
