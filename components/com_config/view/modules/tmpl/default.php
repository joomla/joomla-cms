<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.framework', true);
// JHtml::_('behavior.combobox');
JHtml::_('formbehavior.chosen', 'select');

?>

<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'config.cancel' || document.formvalidator.isValid(document.id('modules-form')))
		{
			Joomla.submitform(task, document.getElementById('modules-form'));
		}
	}
</script>

<form
	action="<?php echo JRoute::_('index.php?option=com_config&id='.$this->moduleId); ?>"
	method="post" name="adminForm" id="modules-form"
	class="form-validate">

	<div class="row-fluid">

		<!-- Begin Content -->
		<div class="span12">

			<div class="btn-toolbar">
				<div class="btn-group">
					<button type="button" class="btn btn-primary"
						onclick="Joomla.submitbutton('config.save.modules.apply')">
						<i class="icon-ok"></i>
						<?php echo JText::_('JSAVE') ?>
					</button>
				</div>
				<div class="btn-group">
					<button type="button" class="btn"
						onclick="Joomla.submitbutton('config.cancel')">
						<i class="icon-cancel"></i>
						<?php echo JText::_('JCANCEL') ?>
					</button>
				</div>
			</div>

			<hr class="hr-condensed" />
			
			<legend><?php echo JText::_('COM_MODULES_SETTINGS_TITLE'); ?></legend>

			<div class="row-fluid">
				<div class="span6">
					<fieldset class="form-horizontal">
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('title'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('title'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('showtitle'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('showtitle'); ?>
							</div>
						</div>
						<!-- div class="control-group">
							<div class="control-label">
								<?php //echo $this->form->getLabel('position'); ?>
							</div>
							<div class="controls">
								<?php //echo $this->loadTemplate('positions'); ?>
							</div>
						</div -->
	
						<hr />
	
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('access'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('access'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('ordering'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('ordering'); ?>
							</div>
						</div>
	
	
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('language'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('language'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('note'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('note'); ?>
							</div>
						</div>
	
						<hr />
	
						<div id="options">
							<?php echo $this->loadTemplate('options'); ?>
						</div>

					</fieldset>
				</div>



				<input type="hidden" name="task" value="" />
				<?php echo JHtml::_('form.token'); ?>


			</div>

		</div>
		<!-- End Content -->
	</div>

</form>
