<?php
/**
 * @package    PatchTester
 *
 * @copyright  Copyright (C) 2011 - 2012 Ian MacLennan, Copyright (C) 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');
JHtml::_('behavior.modal');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

?>
<script type="text/javascript">
	var submitpatch = function (task, id) {
		document.id('pull_id').set('value', id);
		return Joomla.submitbutton(task);
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_patchtester&view=pulls'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="j-main-container">
		<div id="filter-bar" class="btn-toolbar">
			<div class="btn-group pull-left hidden-phone">
				<button type="submit" class="btn"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
				<button type="button" class="btn" onclick="document.id('filter_search').value='';document.id('filter_searchid').value='';this.form.submit();">
					<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>
				</button>
			</div>
			<div class="btn-group pull-right hidden-phone">
				<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?></label>
				<?php echo $this->pagination->getLimitBox(); ?>
			</div>
		</div>
		<div class="clearfix"> </div>

		<table class="table table-striped">
			<thead>
			<tr>
				<th width="5%" class="nowrap center">
					<?php echo JHtml::_('grid.sort', 'COM_PATCHTESTER_PULL_ID', 'number', $listDirn, $listOrder); ?>
					<br />
					<input type="text" name="filter_searchid" id="filter_searchid" class="span10" value="<?php echo $this->escape($this->state->get('filter.searchid')); ?>" />
				</th>
				<th class="nowrap center">
					<?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'title', $listDirn, $listOrder); ?>
					<br />
					<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" />
				</th>
				<th class="nowrap center">I</th>
				<th class="nowrap center">
					<?php echo JText::_('COM_PATCHTESTER_JOOMLACODE_ISSUE'); ?>
				</th>
				<th width="20%" class="nowrap center">
					<?php echo JText::_('JSTATUS'); ?>
				</th>
				<th width="20%" class="nowrap center">
					<?php echo JText::_('COM_PATCHTESTER_TEST_THIS_PATCH'); ?>
				</th>
			</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="6">
					</td>
				</tr>
			</tfoot>
			<tbody>
			<?php echo $this->loadTemplate('items'); ?>
			</tbody>
		</table>
		<?php echo $this->pagination->getListFooter(); ?>

		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="boxchecked" value="0"/>
		<input type="hidden" name="pull_id" id="pull_id" value=""/>
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
