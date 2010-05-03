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

$hasContent = empty($this->item->module) || $this->item->module == 'custom' || $this->item->module == 'mod_custom';
?>
<script type="text/javascript">
<!--
	function submitbutton(task)
	{
		if (task == 'module.cancel' || document.formvalidator.isValid(document.id('module-form'))) {
			<?php
			if ($hasContent) :
				echo $this->form->getField('content')->save();
			endif;
			?>
			submitform(task);
		}
		else {
			alert('<?php echo $this->escape(JText::_('COM_MODULES_ERROR_TITLE'));?>');
		}
	}
// -->
</script>

<form action="<?php JRoute::_('index.php?option=com_modules'); ?>" method="post" name="adminForm" id="module-form" class="form-validate">
	<div class="col main-section">
		<fieldset class="adminform">
			<legend><?php echo JText::_('JOPTION_REQUIRED');?>	</legend>

	<!-- Module metadata -->
		<div>
			<?php echo $this->form->getLabel('title'); ?>
			<?php echo $this->form->getInput('title'); ?>
		</div>
		<div>
			<?php echo $this->form->getLabel('position'); ?>
			<?php echo $this->form->getInput('custom_position'); ?>
			<?php echo $this->form->getInput('position'); ?>
		</div>

			<?php if ((string) $this->item->xml->name != 'Login Form'): ?>
			<div>
			<?php echo $this->form->getLabel('published'); ?>
			<?php echo $this->form->getInput('published'); ?>
			</div>
			<?php endif; ?>
		<div>
			<?php echo $this->form->getLabel('access'); ?>
			<?php echo $this->form->getInput('access'); ?>
		</div>
		</fieldset>
		<fieldset class="adminform">
			<legend><?php echo JText::_('JDETAILS'); ?></legend>
			<?php echo $this->form->getLabel('ordering'); ?>
			<div id="jform_ordering" class="fltlft"><?php echo $this->form->getInput('ordering'); ?></div>

			<div>
			<?php echo $this->form->getLabel('showtitle'); ?>
			<?php echo $this->form->getInput('showtitle'); ?>
			</div>
			<div>
			<?php echo $this->form->getLabel('note'); ?>
			<?php echo $this->form->getInput('note'); ?>
			</div>

			<?php if ((string) $this->item->xml->name != 'Login Form'): ?>
			<div>
			<?php echo $this->form->getLabel('publish_up'); ?>
			<?php echo $this->form->getInput('publish_up'); ?>
			</div>
			<div>
			<?php echo $this->form->getLabel('publish_down'); ?>
			<?php echo $this->form->getInput('publish_down'); ?>
			</div>
			<?php endif; ?>

			<div>
			<?php echo $this->form->getLabel('language'); ?>
			<?php echo $this->form->getInput('language'); ?>
			</div>
			<?php if ($this->item->id) : ?>
				<div>
				<?php echo $this->form->getLabel('id'); ?>
				<?php echo $this->form->getInput('id'); ?>
				</div>
			<?php endif; ?>
			<div>
			<?php echo $this->form->getLabel('module'); ?>
			<?php echo $this->form->getInput('module'); ?>
			<span class="faux-input"><?php if ($this->item->xml) echo ($text = (string) $this->item->xml->name) ? JText::_($text) : $this->item->module;else echo JText::_(MODULES_ERR_XML);?></span>
			</div>
			<div>
			<?php echo $this->form->getLabel('client_id'); ?>
			<input type="text" size="35" id="jform_client_id" value="<?php echo $this->item->client_id == 0 ? JText::_('JSITE') : JText::_('JADMINISTRATOR'); ?>	" class="readonly" readonly="readonly" />
			</div>
			<div class="clr"></div>
	<?php if ($this->item->xml) : ?>
				<?php if ($text = trim($this->item->xml->description)) : ?>
					<span class="faux-label">
						<?php echo JText::_('COM_MODULES_MODULE_DESCRIPTION'); ?>
					</span>
					<?php echo JText::_($text); ?>
				<?php endif; ?>
			<?php else : ?>
				<?php echo JText::_('COM_MODULES_ERR_XML'); ?>
			<?php endif; ?>
			<br class="clr" />
		</fieldset>

		<div>
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

	</div>

	<div class="col options-section">
	<?php echo JHtml::_('sliders.start','plugin-sliders-'.$this->item->id); ?>

		<?php echo $this->loadTemplate('options'); ?>

		<div class="clr"></div>

	<?php echo JHtml::_('sliders.end'); ?>
	</div>



	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
