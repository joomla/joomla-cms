<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cache
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

/** @var \Joomla\Component\Cache\Administrator\View\Cache\HtmlView $this */

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

HTMLHelper::_('script', 'com_cache/admin-cache-default.js', ['version' => 'auto', 'relative' => true]);
?>
<form action="<?php echo Route::_('index.php?option=com_cache'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
				<div class="alert alert-info">
					<span class="fas fa-info-circle" aria-hidden="true"></span>
					<span class="sr-only"><?php echo Text::_('INFO'); ?></span>
					<?php echo Text::_('COM_CACHE_PURGE_INSTRUCTIONS'); ?>
				</div>
				<?php echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>
				<?php if (!$this->data) : ?>
					<div class="alert alert-info">
						<span class="fas fa-info-circle" aria-hidden="true"></span><span class="sr-only"><?php echo Text::_('INFO'); ?></span>
						<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
					</div>
				<?php else : ?>
				<table class="table">
					<caption id="captionTable" class="sr-only">
						<?php echo Text::_('COM_CACHE_TABLE_CAPTION'); ?>,
							<span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
							<span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
					</caption>
						<thead>
						<tr>
							<td class="w-1 text-center">
								<?php echo HTMLHelper::_('grid.checkall'); ?>
							</td>
							<th scope="col" class="title">
								<?php echo HTMLHelper::_('searchtools.sort', 'COM_CACHE_GROUP', 'group', $listDirn, $listOrder); ?>
							</th>
							<th scope="col" class="w-10 text-center">
								<?php echo HTMLHelper::_('searchtools.sort', 'COM_CACHE_NUMBER_OF_FILES', 'count', $listDirn, $listOrder); ?>
							</th>
							<th scope="col" class="w-10 text-right">
								<?php echo HTMLHelper::_('searchtools.sort', 'COM_CACHE_SIZE', 'size', $listDirn, $listOrder); ?>
							</th>
						</tr>
					</thead>
					<tbody>
						<?php $i = 0; ?>
						<?php foreach ($this->data as $folder => $item) : ?>
							<tr>
								<td>
									<input type="checkbox" id="cb<?php echo $i; ?>" name="cid[]" value="<?php echo $this->escape($item->group); ?>" class="cache-entry">
								</td>
								<th scope="row">
									<label for="cb<?php echo $i; ?>">
										<?php echo $this->escape($item->group); ?>
									</label>
								</th>
								<td class="text-center">
									<?php echo $item->count; ?>
								</td>
								<td class="text-right">
									<?php echo '&#x200E;' . HTMLHelper::_('number.bytes', $item->size); ?>
								</td>
							</tr>
						<?php $i++; endforeach; ?>
					</tbody>
				</table>

				<?php // Load the pagination. ?>
				<?php echo $this->pagination->getListFooter(); ?>

				<?php endif; ?>
				<input type="hidden" name="task" value="">
				<input type="hidden" name="boxchecked" value="0">
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
