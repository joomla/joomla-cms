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
defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');

$columns = 6;
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<form action="<?php echo JRoute::_('index.php?option=com_workflow&view=transitions&workflow_id=' . $this->workflowID . '&extension=' . $this->extension); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div id="j-sidebar-container" class="col-md-2">
			<?php echo $this->sidebar; ?>
		</div>
		<div class="col-md-10">
			<div id="j-main-container" class="j-main-container">
				<?php
					// Search tools bar
					echo \JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
				?>
				<?php if (empty($this->transitions)) : ?>
					<div class="alert alert-warning alert-no-items">
						<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
					</div>
				<?php else: ?>
					<table class="table table-striped" id="emailList">
						<thead><?php echo $this->loadTemplate('head');?></thead>
						<tfoot>
							<tr>
								<td colspan="<?php echo $columns; ?>">
									<?php echo $this->pagination->getListFooter(); ?>
								</td>
							</tr>
						</tfoot>
						<tbody class="js-draggable"><?php echo $this->loadTemplate('body');?></tbody>
					</table>
				<?php endif; ?>
				<input type="hidden" name="task" value="">
				<input type="hidden" name="boxchecked" value="0">
				<input type="hidden" name="workflow_id" value="<?php echo $this->workflowID ?>">
				<input type="hidden" name="extension" value="<?php echo $this->extension ?>">
				<?php echo JHtml::_('form.token'); ?>
			</div>
		</div>
	</div>
