<?php
/**
 * Items Model for a Workflow Component.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       4.0
 */
defined('_JEXEC') or die('Restricted Access');

JHtml::_('behavior.tooltip');

$workflowID = $this->escape($this->state->get('filter.workflow_id'));
?>
<form action="<?php echo JRoute::_('index.php?option=com_workflow&view=transition'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div id="j-sidebar-container" class="col-md-2">
			<?php echo $this->sidebar; ?>
		</div>
		<div class="col-md-10">
			<div id="j-main-container" class="j-main-container">
				<?php if (empty($this->transitions)) : ?>
					<div class="alert alert-warning alert-no-items">
						<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
					</div>
				<?php else: ?>
					<table class="table table-striped" id="emailList">
						<thead><?php echo $this->loadTemplate('head');?></thead>
						<tbody class="js-draggable"><?php echo $this->loadTemplate('body');?></tbody>
					</table>
				<?php endif; ?>
				<input type="hidden" name="task" value="">
				<input type="hidden" name="boxchecked" value="0">
				<input type="hidden" name="workflow_id" value="<?php echo $workflowID ?>"
				<?php echo JHtml::_('form.token'); ?>
			</div>
		</div>
	</div>
