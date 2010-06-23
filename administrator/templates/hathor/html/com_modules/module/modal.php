<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	templates.hathor
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		1.6
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

	<div class="col main-section">
		<fieldset class="adminform">
			<?php if ($this->item->id) : ?>
			<legend><?php echo JText::sprintf('JGLOBAL_RECORD_NUMBER', $this->item->id); ?></legend>
			<?php else : ?>
			<legend><?php echo JText::_('JDETAILS');?>	</legend>
			<?php endif; ?>

			<ul class="adminformlist">
			<li><?php echo $this->form->getLabel('title'); ?>
			<?php echo $this->form->getInput('title'); ?></li>

			<li><?php echo $this->form->getLabel('module'); ?>
			<?php echo $this->form->getInput('module'); ?></li>

			<li><?php echo $this->form->getLabel('showtitle'); ?>
			<?php echo $this->form->getInput('showtitle'); ?></li>

			<li><?php echo $this->form->getLabel('published'); ?>
			<?php echo $this->form->getInput('published'); ?></li>

			<li><?php echo $this->form->getLabel('position'); ?>
			<?php echo $this->form->getInput('position'); ?></li>

			<li><?php echo $this->form->getLabel('ordering'); ?>
			<?php echo $this->form->getInput('ordering'); ?></li>

			<li><?php echo $this->form->getLabel('access'); ?><br />
			<?php echo $this->form->getInput('access'); ?></li>

			<li><?php echo $this->form->getLabel('client_id'); ?>
			<?php echo $this->form->getInput('client_id'); ?></li>

			<li><?php echo $this->form->getLabel('language'); ?>
			<?php echo $this->form->getInput('language'); ?></li>


			<!-- Module metadata -->
			<?php if ($this->item->xml) : ?>
				<?php if ($text = trim($this->item->xml->description)) : ?>
					<li><label>
						<?php echo JText::_('COM_MODULES_MODULE_DESCRIPTION'); ?>
					</label>
					<?php echo $this->escape($text); ?></li>
				<?php endif; ?>
			<?php else : ?>
				<li><?php echo JText::_('COM_MODULES_ERR_XML'); ?></li>
			<?php endif; ?>
			</ul>
		</fieldset>

	</div>

	<div class="fltrt options-section">
	<?php echo $pane->startPane('options-pane'); ?>

		<?php echo $this->loadTemplate('options'); ?>

		<?php echo $pane->endPane(); ?>
	</div>

	<div class="col main-section">
		<?php echo $this->loadTemplate('assignment'); ?>
	</div>

	<div class="clr"></div>


	<?php if ($hasContent) : ?>
		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_MODULES_CUSTOM_OUTPUT'); ?></legend>
			<ul class="adminformlist">
				<li><?php echo $this->form->getLabel('content'); ?>
				<?php echo $this->form->getInput('content'); ?></li>
			</ul>
		</fieldset>	endif;
	<?php endif; ?>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>