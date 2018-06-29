<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cache
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

\Joomla\CMS\HTML\HTMLHelper::_('script', 'com_cache/admin-cache-default.js', ['relative' => true, 'version' => 'auto']);
?>
<form action="<?php echo Route::_('index.php?option=com_cache'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div id="j-sidebar-container" class="col-md-2">
			<?php echo $this->sidebar; ?>
		</div>
		<div class="col-md-10">
			<div id="j-main-container" class="j-main-container">
				<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
				<?php if (count($this->data) > 0) : ?>
				<table class="table table-striped">
					<thead>
						<tr>
							<th style="width:1%" class="nowrap text-center">
								<?php echo JHtml::_('grid.checkall'); ?>
							</th>
							<th class="title nowrap">
								<?php echo JHtml::_('searchtools.sort', 'COM_CACHE_GROUP', 'group', $listDirn, $listOrder); ?>
							</th>
							<th style="width:10%" class="nowrap text-center">
								<?php echo JHtml::_('searchtools.sort', 'COM_CACHE_NUMBER_OF_FILES', 'count', $listDirn, $listOrder); ?>
							</th>
							<th style="width:10%" class="nowrap text-center">
								<?php echo JHtml::_('searchtools.sort', 'COM_CACHE_SIZE', 'size', $listDirn, $listOrder); ?>
							</th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<td colspan="4">
							<?php echo $this->pagination->getListFooter(); ?>
							</td>
						</tr>
					</tfoot>
					<tbody>
						<?php $i = 0; ?>
						<?php foreach ($this->data as $folder => $item) : ?>
							<tr class="row<?php echo $i % 2; ?>">
								<td>
									<input type="checkbox" id="cb<?php echo $i; ?>" name="cid[]" value="<?php echo $this->escape($item->group); ?>" class="cache-entry">
								</td>
								<td>
									<label for="cb<?php echo $i; ?>">
										<strong><?php echo $this->escape($item->group); ?></strong>
									</label>
								</td>
								<td class="text-center">
									<?php echo $item->count; ?>
								</td>
								<td class="text-center">
									<?php echo JHtml::_('number.bytes', $item->size); ?>
								</td>
							</tr>
						<?php $i++; endforeach; ?>
					</tbody>
				</table>
				<?php endif; ?>
				<input type="hidden" name="task" value="">
				<input type="hidden" name="boxchecked" value="0">
				<?php echo JHtml::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
