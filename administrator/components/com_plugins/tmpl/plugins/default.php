<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_plugins
 *
 * @copyright   (C) 2007 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('multiselect');

$user      = Factory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrder = $listOrder == 'ordering';

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_plugins&task=plugins.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
	HTMLHelper::_('draggablelist.draggable');
}
?>
<form action="<?php echo Route::_('index.php?option=com_plugins&view=plugins'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="j-main-container" class="j-main-container">
		<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
		<?php if (empty($this->items)) : ?>
			<div class="alert alert-info">
				<span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
				<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>
			<table class="table" id="pluginList">
				<caption class="visually-hidden">
					<?php echo Text::_('COM_PLUGINS_TABLE_CAPTION'); ?>,
							<span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
							<span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
				</caption>
				<thead>
					<tr>
						<td class="w-1 text-center">
							<?php echo HTMLHelper::_('grid.checkall'); ?>
						</td>
						<th scope="col" class="w-1 text-center d-none d-md-table-cell">
							<?php echo HTMLHelper::_('searchtools.sort', '', 'ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-sort'); ?>
						</th>
						<th scope="col" class="w-1 text-center">
							<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'enabled', $listDirn, $listOrder); ?>
						</th>
						<th scope="col" class="title">
							<?php echo HTMLHelper::_('searchtools.sort', 'COM_PLUGINS_NAME_HEADING', 'name', $listDirn, $listOrder); ?>
						</th>
						<th scope="col" class="w-10 d-none d-md-table-cell">
							<?php echo HTMLHelper::_('searchtools.sort', 'COM_PLUGINS_FOLDER_HEADING', 'folder', $listDirn, $listOrder); ?>
						</th>
						<th scope="col" class="w-10 d-none d-md-table-cell">
							<?php echo HTMLHelper::_('searchtools.sort', 'COM_PLUGINS_ELEMENT_HEADING', 'element', $listDirn, $listOrder); ?>
						</th>
						<th scope="col" class="w-10 d-none d-md-table-cell">
							<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'access', $listDirn, $listOrder); ?>
						</th>
						<th scope="col" class="w-5 d-none d-md-table-cell">
							<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'extension_id', $listDirn, $listOrder); ?>
						</th>
					</tr>
				</thead>
				<tbody <?php if ($saveOrder) :?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" data-nested="true"<?php endif; ?>>
				<?php foreach ($this->items as $i => $item) :
					$ordering   = ($listOrder == 'ordering');
					$canEdit    = $user->authorise('core.edit',       'com_plugins');
					$canCheckin = $user->authorise('core.manage',     'com_checkin') || $item->checked_out == $user->get('id') || is_null($item->checked_out);
					$canChange  = $user->authorise('core.edit.state', 'com_plugins') && $canCheckin;
					?>
					<tr class="row<?php echo $i % 2; ?>" data-draggable-group="<?php echo $item->folder; ?>">
						<td class="text-center">
							<?php echo HTMLHelper::_('grid.id', $i, $item->extension_id, false, 'cid', 'cb', $item->name); ?>
						</td>
						<td class="text-center d-none d-md-table-cell">
							<?php
							$iconClass = '';
							if (!$canChange)
							{
								$iconClass = ' inactive';
							}
							elseif (!$saveOrder)
							{
								$iconClass = ' inactive" title="' . Text::_('JORDERINGDISABLED');
							}
							?>
							<span class="sortable-handler<?php echo $iconClass; ?>">
								<span class="icon-ellipsis-v" aria-hidden="true"></span>
							</span>
							<?php if ($canChange && $saveOrder) : ?>
								<input type="text" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order hidden">
							<?php endif; ?>
						</td>
						<td class="text-center">
							<?php echo HTMLHelper::_('jgrid.published', $item->enabled, $i, 'plugins.', $canChange); ?>
						</td>
						<th scope="row">
							<?php if ($item->checked_out) : ?>
								<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'plugins.', $canCheckin); ?>
							<?php endif; ?>
							<?php if ($canEdit) : ?>
								<a href="<?php echo Route::_('index.php?option=com_plugins&task=plugin.edit&extension_id=' . (int) $item->extension_id); ?>" title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo $this->escape($item->name); ?>">
									<?php echo $item->name; ?></a>
									<?php if (!empty($item->note)) : ?>
										<div class="small">
											<?php echo Text::sprintf('JGLOBAL_LIST_NOTE', $this->escape($item->note)); ?>
										</div>
									<?php endif; ?>
							<?php else : ?>
									<?php echo $item->name; ?>
							<?php endif; ?>
						</th>
						<td class="small d-none d-md-table-cell">
							<?php echo $this->escape($item->folder); ?>
						</td>
						<td class="small d-none d-md-table-cell">
							<?php echo $this->escape($item->element); ?>
						</td>
						<td class="small d-none d-md-table-cell">
							<?php echo $this->escape($item->access_level); ?>
						</td>
						<td class="d-none d-md-table-cell">
							<?php echo (int) $item->extension_id; ?>
						</td>
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
