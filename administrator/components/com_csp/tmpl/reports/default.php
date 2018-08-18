<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_csp
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.multiselect');
JHtml::_('behavior.tabstate');

$user      = JFactory::getUser();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrder = $listOrder == 'a.id';

?>
<form action="<?php echo JRoute::_('index.php?option=com_csp&view=reports'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
				<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
				<?php if ($this->httpHeadersId) : ?>
					<?php $link = JRoute::_('index.php?option=com_plugins&client_id=0&task=plugin.edit&extension_id=' . $this->httpHeadersId . '&tmpl=component&layout=modal'); ?>
					<?php echo JHtml::_(
						'bootstrap.renderModal',
						'plugin' . $this->httpHeadersId . 'Modal',
						array(
							'url'         => $link,
							'title'       => JText::_('COM_CSP_EDIT_PLUGIN_SETTINGS'),
							'height'      => '400px',
							'width'       => '800px',
							'bodyHeight'  => '70',
							'modalWidth'  => '80',
							'closeButton' => false,
							'backdrop'    => 'static',
							'keyboard'    => false,
							'footer'      => '<button type="button" class="btn" data-dismiss="modal"'
								. ' onclick="jQuery(\'#plugin' . $this->httpHeadersId . 'Modal iframe\').contents().find(\'#closeBtn\').click();">'
								. JText::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>'
								. '<button type="button" class="btn btn-primary" data-dismiss="modal" onclick="jQuery(\'#plugin' . $this->httpHeadersId . 'Modal iframe\').contents().find(\'#saveBtn\').click();">'
								. JText::_("JSAVE") . '</button>'
								. '<button type="button" class="btn btn-success" onclick="jQuery(\'#plugin' . $this->httpHeadersId . 'Modal iframe\').contents().find(\'#applyBtn\').click(); return false;">'
								. JText::_("JAPPLY") . '</button>'
						)
					); ?>
				<?php endif; ?>
				<?php if (empty($this->items)) : ?>
					<joomla-alert type="warning"><?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></joomla-alert>
				<?php else : ?>
					<table class="table table-striped" id="articleList">
						<thead>
							<tr>
								<th style="width:1%" class="text-center">
									<?php echo JHtml::_('grid.checkall'); ?>
								</th>
								<th style="width:1%" class="nowrap text-center">
									<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
								</th>
								<th>
									<?php echo JHtml::_('searchtools.sort', 'COM_CSP_HEADING_DOCUMENT_URI', 'a.document_uri', $listDirn, $listOrder); ?>
								</th>
								<th>
									<?php echo JHtml::_('searchtools.sort', 'COM_CSP_HEADING_BLOCKED_URI', 'a.blocked_uri', $listDirn, $listOrder); ?>
								</th>
								<th>
									<?php echo JHtml::_('searchtools.sort', 'COM_CSP_HEADING_DIRECTIVE', 'a.directive', $listDirn, $listOrder); ?>
								</th>
								<th>
									<?php echo JHtml::_('searchtools.sort', 'COM_CSP_HEADING_CREATED', 'a.created', $listDirn, $listOrder); ?>
								</th>
								<th style="width:5%" class="nowrap d-none d-md-table-cell text-center">
									<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
								</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<td colspan="7">
									<?php echo $this->pagination->getListFooter(); ?>
								</td>
							</tr>
						</tfoot>
						<tbody>
							<?php foreach ($this->items as $i => $item) : ?>
								<?php $canChange  = $user->authorise('core.edit.state', 'com_csp'); ?>
								<tr class="row<?php echo $i % 2; ?>">
									<td class="text-center">
										<?php echo JHtml::_('grid.id', $i, $item->id); ?>
									</td>
									<td class="text-center">
										<div class="btn-group">
											<?php echo JHtml::_('jgrid.published', $item->published, $i, 'reports.', $canChange, 'cb'); ?>
										</div>
									</td>
									<td class="small d-none d-md-table-cell">
										<?php echo $item->document_uri; ?>
									</td>
									<td class="small d-none d-md-table-cell">
										<?php echo $item->blocked_uri; ?>
									</td>
									<td class="d-none d-md-table-cell">
										<?php echo $item->directive; ?>
									</td>
									<td class="d-none d-md-table-cell">
										<?php echo $item->created > 0 ? JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC4')) : '-'; ?>
									</td>
									<td class="text-center d-none d-md-table-cell text-center">
										<?php echo $item->id; ?>
									</td>
								</tr>
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
