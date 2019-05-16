<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cache
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

HTMLHelper::_('script', 'com_cache/admin-cache-default.js', ['version' => 'auto', 'relative' => true]);
?>
<form action="<?php echo Route::_('index.php?option=com_cache'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
				<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
				<?php if (count($this->data) > 0) : ?>
				<table class="table">
					<caption id="captionTable" class="sr-only">
						<?php echo Text::_('COM_CACHE_TABLE_CAPTION'); ?>, <?php echo Text::_('JGLOBAL_SORTED_BY'); ?>
					</caption>
						<thead>
						<tr>
							<td style="width:1%" class="text-center">
								<?php echo HTMLHelper::_('grid.checkall'); ?>
							</td>
							<th scope="col" class="title">
								<?php echo HTMLHelper::_('searchtools.sort', 'COM_CACHE_GROUP', 'group', $listDirn, $listOrder); ?>
							</th>
							<th scope="col" style="width:10%" class="text-center">
								<?php echo HTMLHelper::_('searchtools.sort', 'COM_CACHE_NUMBER_OF_FILES', 'count', $listDirn, $listOrder); ?>
							</th>
							<th scope="col" style="width:10%" class="text-right">
								<?php echo HTMLHelper::_('searchtools.sort', 'COM_CACHE_SIZE', 'size', $listDirn, $listOrder); ?>
							</th>
						</tr>
					</thead>
					<tbody>
						<?php $i = 0; ?>
						<?php foreach ($this->data as $folder => $item) : ?>
							<tr class="row<?php echo $i % 2; ?>">
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

				<?php // load the pagination. ?>
				<?php echo $this->pagination->getListFooter(); ?>

				<?php endif; ?>
				<input type="hidden" name="task" value="">
				<input type="hidden" name="boxchecked" value="0">
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
