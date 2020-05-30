<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_csp
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.multiselect');

$user      = Factory::getUser();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrder = $listOrder == 'a.id';

?>
<form action="<?php echo Route::_('index.php?option=com_csp&view=reports'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
				<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
				<?php if ($this->httpHeadersId) : ?>
					<?php $link = Route::_('index.php?option=com_plugins&client_id=0&task=plugin.edit&extension_id=' . $this->httpHeadersId . '&tmpl=component&layout=modal'); ?>
					<?php echo HTMLHelper::_(
						'bootstrap.renderModal',
						'plugin' . $this->httpHeadersId . 'Modal',
						array(
							'url'         => $link,
							'title'       => Text::_('COM_CSP_EDIT_PLUGIN_SETTINGS'),
							'height'      => '400px',
							'width'       => '800px',
							'bodyHeight'  => '70',
							'modalWidth'  => '80',
							'closeButton' => false,
							'backdrop'    => 'static',
							'keyboard'    => false,
							'footer'      => '<button type="button" class="btn" data-dismiss="modal"'
								. ' onclick="Joomla.iframeButtonClick({iframeSelector: \'#plugin' . $this->httpHeadersId . 'Modal\', buttonSelector: \'#closeBtn\'})">'
								. Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>'
								. '<button type="button" class="btn btn-primary" data-dismiss="modal" onclick="Joomla.iframeButtonClick({iframeSelector: \'#plugin' . $this->httpHeadersId . 'Modal\', buttonSelector: \'#saveBtn\'})">'
								. Text::_("JSAVE") . '</button>'
								. '<button type="button" class="btn btn-success" onclick="Joomla.iframeButtonClick({iframeSelector: \'#plugin' . $this->httpHeadersId . 'Modal\', buttonSelector: \'#applyBtn\'})">'
								. Text::_("JAPPLY") . '</button>'
						)
					); ?>
				<?php endif; ?>
				<?php if (isset($this->trashWarningMessage)) : ?>
					<?php Factory::getApplication()->enqueueMessage($this->trashWarningMessage, 'warning'); ?>
				<?php endif; ?>
				<?php if (empty($this->items)) : ?>
					<div class="alert alert-info">
						<span class="fas fa-info-circle" aria-hidden="true"></span><span class="sr-only"><?php echo Text::_('INFO'); ?></span>
						<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
					</div>
				<?php else : ?>
					<table class="table" id="articleList">
						<caption id="captionTable" class="sr-only">
							<?php echo Text::_('COM_CSP_TABLE_CAPTION'); ?>,
							<span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
							<span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
						</caption>
						<thead>
							<tr>
								<td class="text-center">
									<?php echo HTMLHelper::_('grid.checkall'); ?>
								</td>
								<th scope="col" class="text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_CSP_HEADING_DOCUMENT_URI', 'a.document_uri', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_CSP_HEADING_BLOCKED_URI', 'a.blocked_uri', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_CSP_HEADING_DIRECTIVE', 'a.directive', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'JCLIENT', 'a.client', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_CSP_HEADING_CREATED', 'a.created', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
								</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($this->items as $i => $item) : ?>
								<?php $canChange  = $user->authorise('core.edit.state', 'com_csp'); ?>
								<tr class="row<?php echo $i % 2; ?>">
									<td class="text-center">
										<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
									</td>
									<td class="text-center">
										<?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'reports.', $canChange, 'cb'); ?>
									</td>
									<th scope="row" class="small d-md-table-cell text-break">
										<?php echo $item->document_uri; ?>
									</th>
									<td class="small d-none d-md-table-cell">
										<?php echo $item->blocked_uri; ?>
									</td>
									<td class="d-none d-md-table-cell">
										<?php echo $item->directive; ?>
									</td>
									<td class="d-none d-md-table-cell">
										<?php echo Text::_('J' . strtoupper($item->client)); ?>
									</td>
									<td class="d-none d-md-table-cell">
										<?php echo $item->created > 0 ? HTMLHelper::_('date', $item->created, Text::_('DATE_FORMAT_LC4')) : '-'; ?>
									</td>
									<td class="text-center d-none d-md-table-cell">
										<?php echo $item->id; ?>
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
		</div>
	</div>
</form>
