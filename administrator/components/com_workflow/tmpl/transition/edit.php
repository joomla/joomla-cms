<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', '.advancedSelect', null, array('disable_search_threshold' => 0 ));

// In case of modal
$isModal = $this->input->get('layout') == 'modal' ? true : false;
$layout  = $isModal ? 'modal' : 'edit';
$tmpl    = $isModal || $this->input->get('tmpl', '', 'cmd') === 'component' ? '&tmpl=component' : '';

?>

<form action="<?php echo JRoute::_('index.php?option=com_workflow&view=transition&workflow_id=' . $this->workflowID . '&extension=' . $this->input->getCmd('extension') . '&layout=' . $layout . $tmpl . '&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="workflow-form" class="form-validate">

	<?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>

	<div>
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'details', empty($this->item->id) ? JText::_('COM_WORKFLOW_BASIC_TAB') : JText::_('COM_WORKFLOW_EDIT_TAB')); ?>
		<div class="row">
			<div class="col-md-9">
				<?php echo $this->form->getInput('description'); ?>
			</div>
			<div class="col-md-3">
				<div class="card card-block card-light">
					<fieldset class="form-vertical form-no-margin">
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('published'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('published'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('from_state_id'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('from_state_id'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('to_state_id'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('to_state_id'); ?>
							</div>
						</div>
					</fieldset>
				</div>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'permissions', JText::_('COM_WORKFLOW_RULES_TAB')); ?>
			<?php echo $this->form->getInput('rules'); ?>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php echo JHtml::_('bootstrap.endTabSet'); ?>
	</div>
	<?php echo $this->form->getInput('workflow_id'); ?>
	<input type="hidden" name="task" value="transition.edit" />
	<?php echo JHtml::_('form.token'); ?>
</form>
