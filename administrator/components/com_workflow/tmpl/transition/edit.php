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

$app = JFactory::getApplication();
$input = $app->input;

// In case of modal
$isModal = $input->get('layout') == 'modal' ? true : false;
$layout  = $isModal ? 'modal' : 'edit';
$tmpl    = $isModal || $input->get('tmpl', '', 'cmd') === 'component' ? '&tmpl=component' : '';

$workflowID = $input->getCmd('workflow_id');
$fromStatusSql     = 'SELECT `id` AS `value`, `title` AS `from_status_id` FROM #__workflow_status WHERE workflow_id=' . $workflowID;
$toStatusSql     = 'SELECT `id` AS `value`, `title` AS `to_status_id` FROM #__workflow_status WHERE workflow_id=' . $workflowID;
$this->form->setFieldAttribute('from_status_id', 'query', $fromStatusSql);
$this->form->setFieldAttribute('to_status_id', 'query', $toStatusSql);
?>

<form action="<?php echo JRoute::_('index.php?option=com_workflow&view=transition&workflow_id=' . $workflowID . '&layout=' . $layout . $tmpl . '&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="workflow-form" class="form-validate">

	<?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>

	<div>
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'details', empty($this->item->id) ? JText::_('COM_WORKFLOW_BASIC_TAB') : JText::_('COM_WORKFLOW_EDIT_TAB')); ?>
		<div class="row">
			<div class="col-md-6">
				<?php echo $this->form->renderField('description'); ?>
			</div>
			<div class="col-md-6">
				<div class="card card-block card-light">
					<?php echo $this->form->renderField('from_status_id'); ?>
					<?php echo $this->form->renderField('to_status_id'); ?>
				</div>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php echo JHtml::_('bootstrap.endTabSet'); ?>
	</div>
	<?php echo $this->form->getInput('workflow_id'); ?>
	<input type="hidden" name="task" value="transition.edit" />
	<?php echo JHtml::_('form.token'); ?>
</form>
