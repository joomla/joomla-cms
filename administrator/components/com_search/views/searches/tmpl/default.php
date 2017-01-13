<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_search
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
?>
<form action="<?php echo JRoute::_('index.php?option=com_search&view=searches'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="j-main-container" class="j-main-container">
		<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this, 'options' => array('filterButton' => false))); ?>
		<div class="clearfix"> </div>
		<?php if (empty($this->items)) : ?>
		<div class="alert alert-warning alert-no-items">
			<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
		</div>
		<?php else : ?>
		<table class="table table-striped">
			<thead>
				<tr>
					<th class="nowrap">
						<?php echo JHtml::_('searchtools.sort', 'COM_SEARCH_HEADING_PHRASE', 'a.search_term', $listDirn, $listOrder); ?>
					</th>
					<th width="15%" class="nowrap">
						<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_HITS', 'a.hits', $listDirn, $listOrder); ?>
					</th>
					<th width="1%" class="nowrap text-center">
						<?php echo JText::_('COM_SEARCH_HEADING_RESULTS'); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="3">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
			<?php foreach ($this->items as $i => $item) : ?>
				<tr class="row<?php echo $i % 2; ?>">
					<td class="break-word">
						<?php echo $this->escape($item->search_term); ?>
					</td>
					<td>
						<?php echo (int) $item->hits; ?>
					</td>
					<?php if ($this->state->get('show_results')) : ?>
					<td class="text-center btns">
						<a class="badge <?php echo $item->returns > 0 ? ' badge-success' : ' badge-default'; ?>" target="_blank" href="<?php echo JUri::root(); ?>index.php?option=com_search&amp;view=search&amp;searchword=<?php echo JFilterOutput::stringURLSafe($item->search_term); ?>">
							<?php echo $item->returns; ?><span class="icon-out-2"><span class="element-invisible"><?php echo JText::_('JBROWSERTARGET_NEW'); ?></span></span></a>
					</td>
					<?php else : ?>
					<td class="text-center">
						<?php echo JText::_('COM_SEARCH_NO_RESULTS'); ?>
					</td>
					<?php endif; ?>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php endif; ?>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
