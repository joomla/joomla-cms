<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_search
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.multiselect');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
?>
<form action="<?php echo Route::_('index.php?option=com_search&view=searches'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="j-main-container" class="j-main-container">
		<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this, 'options' => array('filterButton' => false))); ?>
		<?php if (empty($this->items)) : ?>
			<joomla-alert type="warning"><?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></joomla-alert>
		<?php else : ?>
		<table class="table table-striped">
			<thead>
				<tr>
					<th class="nowrap">
						<?php echo JHtml::_('searchtools.sort', 'COM_SEARCH_HEADING_PHRASE', 'a.search_term', $listDirn, $listOrder); ?>
					</th>
					<th style="width:15%" class="nowrap">
						<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_HITS', 'a.hits', $listDirn, $listOrder); ?>
					</th>
					<th style="width:1%" class="nowrap text-center">
						<?php echo Text::_('COM_SEARCH_HEADING_RESULTS'); ?>
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
						<a class="badge <?php echo $item->returns > 0 ? ' badge-success' : ' badge-secondary'; ?>" target="_blank" href="<?php echo JUri::root(); ?>index.php?option=com_search&amp;view=search&amp;searchword=<?php echo JFilterOutput::stringURLSafe($item->search_term); ?>">
							<?php echo $item->returns; ?><span class="icon-out-2" aria-hidden="true"><span class="sr-only"><?php echo Text::_('JBROWSERTARGET_NEW'); ?></span></span></a>
					</td>
					<?php else : ?>
					<td class="text-center">
						<?php echo Text::_('COM_SEARCH_NO_RESULTS'); ?>
					</td>
					<?php endif; ?>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php endif; ?>
		<input type="hidden" name="task" value="">
		<input type="hidden" name="boxchecked" value="0">
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
