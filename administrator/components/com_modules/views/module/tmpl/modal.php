<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Modules
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access.
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.combobox');

jimport('joomla.html.pane');
$pane = JPane::getInstance('sliders');

$hasContent = empty($this->item->module) || $this->item->module == 'custom' || $this->item->module == 'mod_custom';
?>
<script type="text/javascript">
<!--
	function submitbutton(task)
	{
		if (task == 'module.cancel' || document.formvalidator.isValid(document.id('module-form'))) {
			<?php
			if ($hasContent) :
				echo $this->form->getField('articletext')->save();
			endif;
			?>
			submitform(task);
		} else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
// -->
</script>

<form action="<?php JRoute::_('index.php?option=com_modules&layout=modal&tmpl=component'); ?>" method="post" name="adminForm" id="module-form" class="form-validate">

		<div class="fltrt">
			<button type="button" onclick="Joomla.submitform('module.save', this.form);window.top.setTimeout('window.parent.SqueezeBox.close()', 1400);">
				<?php echo JText::_('JSAVE');?></button>
			<button type="button" onclick="window.parent.SqueezeBox.close();">
				<?php echo JText::_('JCANCEL');?></button>
		</div>

				<script type="text/javascript">
			function allselections() {
				var e = document.getElementById('selections');
					e.disabled = true;
				var i = 0;
				var n = e.options.length;
				for (i = 0; i < n; i++) {
					e.options[i].disabled = true;
					e.options[i].selected = true;
				}
			}
			function disableselections() {
				var e = document.getElementById('selections');
					e.disabled = true;
				var i = 0;
				var n = e.options.length;
				for (i = 0; i < n; i++) {
					e.options[i].disabled = true;
					e.options[i].selected = false;
				}
			}
			function enableselections() {
				var e = document.getElementById('selections');
					e.disabled = false;
				var i = 0;
				var n = e.options.length;
				for (i = 0; i < n; i++) {
					e.options[i].disabled = false;

				}
			}
		</script>

		<div class="clr"></div>

	<div class="width-60 fltlft">
		<fieldset class="adminform">
			<?php if ($this->item->id) : ?>
			<legend><?php echo JText::sprintf('JGLOBAL_RECORD_NUMBER', $this->item->id); ?></legend>
			<?php endif; ?>

			<?php echo $this->form->getLabel('title'); ?>
			<?php echo $this->form->getInput('title'); ?>

			<?php echo $this->form->getLabel('module'); ?>
			<?php echo $this->form->getInput('module'); ?>

			<?php echo $this->form->getLabel('showtitle'); ?>
			<?php echo $this->form->getInput('showtitle'); ?>

			<?php echo $this->form->getLabel('published'); ?>
			<?php echo $this->form->getInput('published'); ?>

			<?php echo $this->form->getLabel('position'); ?>
			<?php echo $this->form->getInput('position'); ?>

			<?php echo $this->form->getLabel('ordering'); ?>
			<div id="jform_ordering" class="fltlft"><?php echo $this->form->getInput('ordering'); ?></div>

			<?php echo $this->form->getLabel('access'); ?><br />
			<?php echo $this->form->getInput('access'); ?>

			<?php echo $this->form->getLabel('client_id'); ?>
			<?php echo $this->form->getInput('client_id'); ?>

			<?php echo $this->form->getLabel('language'); ?>
			<?php echo $this->form->getInput('language'); ?>

			<br class="clr" />
			<!-- Module metadata -->
			<?php if ($this->item->xml) : ?>
				<?php if ($text = trim($this->item->xml->description)) : ?>
					<label>
						<?php echo JText::_('COM_MODULES_MODULE_DESCRIPTION'); ?>
					</label>
					<?php echo $this->escape($text); ?>
				<?php endif; ?>
			<?php else : ?>
				<?php echo JText::_('COM_MODULES_ERR_XML'); ?>
			<?php endif; ?>
		</fieldset>
	</div>

	<div class="width-40 fltrt">
	<?php echo $pane->startPane('options-pane'); ?>

		<?php echo $this->loadTemplate('options'); ?>

		<div class="clr"></div>

		<?php echo $pane->endPane(); ?>
	</div>

	<div class="width-60 fltlft">

		<?php echo $this->loadTemplate('assignment'); ?>

	</div>

	<div class="clr"></div>
	<?php if ($hasContent) : ?>
		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_MODULES_CUSTOM_OUTPUT'); ?></legend>

			<?php echo $this->form->getLabel('content'); ?>
			<?php echo $this->form->getInput('content'); ?>

		</fieldset>	endif;
	<?php endif; ?>

	<div>
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>