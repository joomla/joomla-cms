<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

/** @var \Joomla\Component\Banners\Administrator\View\Tracks\HtmlView $this */

$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));
?>
<form action="<?php echo Route::_('index.php?option=com_banners&view=tracks'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
				<?php echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>
				<?php if (empty($this->items)) : ?>
					<div class="alert alert-info">
						<span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
						<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
					</div>
				<?php else : ?>
					<table class="table">
						<caption class="visually-hidden">
							<?php echo Text::_('COM_BANNERS_TRACKS_TABLE_CAPTION'); ?>,
							<span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
							<span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
						</caption>
						<thead>
							<tr>
								<th scope="col" class="title">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_BANNERS_HEADING_NAME', 'b.name', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="w-20">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_BANNERS_HEADING_CLIENT', 'cl.name', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="w-10 d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_BANNERS_HEADING_TYPE', 'a.track_type', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="w-10 d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_BANNERS_HEADING_COUNT', 'a.count', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="w-10 d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'JDATE', 'a.track_date', $listDirn, $listOrder); ?>
								</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($this->items as $i => $item) : ?>
								<tr class="row<?php echo $i % 2; ?>">
									<th scope="row">
										<?php echo $item->banner_name; ?>
										<div class="small">
											<?php echo Text::_('JCATEGORY') . ': ' . $this->escape($item->category_title); ?>
										</div>
									</th>
									<td>
										<?php echo $item->client_name; ?>
									</td>
									<td class="small d-none d-md-table-cell">
										<?php echo $item->track_type == 1 ? Text::_('COM_BANNERS_IMPRESSION') : Text::_('COM_BANNERS_CLICK'); ?>
									</td>
									<td class="d-none d-md-table-cell">
										<?php echo $item->count; ?>
									</td>
									<td class="d-none d-md-table-cell">
										<?php echo HTMLHelper::_('date', $item->track_date, Text::_('DATE_FORMAT_LC5')); ?>
									</td>
								</tr>
							<?php endforeach; ?>
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
