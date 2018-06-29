<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_checkin
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;

JHtml::_('behavior.multiselect');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<form action="<?php echo Route::_('index.php?option=com_checkin'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div id="j-sidebar-container" class="col-md-2">
			<?php echo $this->sidebar; ?>
		</div>
		<div class="col-md-10">
			<div id="j-main-container" class="j-main-container">
				<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
				<?php if ($this->total > 0) : ?>
					<table id="global-checkin" class="table table-striped">
						<thead>
							<tr>
								<th style="width:1%"><?php echo JHtml::_('grid.checkall'); ?></th>
								<th><?php echo JHtml::_('searchtools.sort', 'COM_CHECKIN_DATABASE_TABLE', 'table', $listDirn, $listOrder); ?></th>
								<th><?php echo JHtml::_('searchtools.sort', 'COM_CHECKIN_ITEMS_TO_CHECK_IN', 'count', $listDirn, $listOrder); ?></th>
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
							<?php $i = 0; ?>
							<?php foreach ($this->items as $table => $count) : ?>
								<tr class="row<?php echo $i % 2; ?>">
									<td class="text-center"><?php echo JHtml::_('grid.id', $i, $table); ?></td>
									<td>
										<label for="cb<?php echo $i ?>">
											<?php echo Text::sprintf('COM_CHECKIN_TABLE', $table); ?>
										</label>
									</td>
									<td>
										<span class="badge badge-secondary"><?php echo $count; ?></span>
									</td>
								</tr>
								<?php $i++; ?>
							<?php endforeach; ?>
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
