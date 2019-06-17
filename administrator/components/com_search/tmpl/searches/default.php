<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_search
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('behavior.multiselect');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
?>
<form action="<?php echo Route::_('index.php?option=com_search&view=searches'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="j-main-container" class="j-main-container">
		<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this, 'options' => array('filterButton' => false))); ?>
		<?php if (empty($this->items)) : ?>
			<div class="alert alert-info">
				<span class="fa fa-info-circle" aria-hidden="true"></span><span class="sr-only"><?php echo Text::_('INFO'); ?></span>
				<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>
		<table class="table">
			<caption id="captionTable" class="sr-only">
				<?php echo Text::_('COM_SEARCH_TABLE_CAPTION'); ?>, <?php echo Text::_('JGLOBAL_SORTED_BY'); ?>
			</caption>
			<thead>
				<tr>
					<th scope="col">
						<?php echo HTMLHelper::_('searchtools.sort', 'COM_SEARCH_HEADING_PHRASE', 'a.search_term', $listDirn, $listOrder); ?>
					</th>
					<th scope="col" style="width:15%">
						<?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_HITS', 'a.hits', $listDirn, $listOrder); ?>
					</th>
					<th scope="col" style="width:1%" class="text-center">
						<?php echo Text::_('COM_SEARCH_HEADING_RESULTS'); ?>
					</th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ($this->items as $i => $item) : ?>
				<tr class="row<?php echo $i % 2; ?>">
					<th scope="row" class="break-word">
						<?php echo $this->escape($item->search_term); ?>
					</th>
					<td>
						<?php echo (int) $item->hits; ?>
					</td>
					<?php if ($this->state->get('show_results')) : ?>
					<td class="text-center btns">
						<a class="badge <?php echo $item->returns > 0 ? ' badge-success' : ' badge-secondary'; ?>" target="_blank" href="<?php echo Uri::root(); ?>index.php?option=com_search&amp;view=search&amp;searchword=<?php echo JFilterOutput::stringURLSafe($item->search_term); ?>">
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

		<?php // load the pagination. ?>
		<?php echo $this->pagination->getListFooter(); ?>

		<?php endif; ?>
		<input type="hidden" name="task" value="">
		<input type="hidden" name="boxchecked" value="0">
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
