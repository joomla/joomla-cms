<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_redirect
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.multiselect');

$user      = Factory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<form action="<?php echo Route::_('index.php?option=com_redirect&view=links'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="j-main-container" class="j-main-container">
		<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
		<?php if ($this->redirectPluginId) : ?>
			<?php $link = Route::_('index.php?option=com_plugins&client_id=0&task=plugin.edit&extension_id=' . $this->redirectPluginId . '&tmpl=component&layout=modal'); ?>
			<?php echo JHtml::_(
				'bootstrap.renderModal',
				'plugin' . $this->redirectPluginId . 'Modal',
				array(
					'url'         => $link,
					'title'       => Text::_('COM_REDIRECT_EDIT_PLUGIN_SETTINGS'),
					'height'      => '400px',
					'width'       => '800px',
					'bodyHeight'  => '70',
					'modalWidth'  => '80',
					'closeButton' => false,
					'backdrop'    => 'static',
					'keyboard'    => false,
					'footer'      => '<button type="button" class="btn" data-dismiss="modal" aria-hidden="true"'
						. ' onclick="jQuery(\'#plugin' . $this->redirectPluginId . 'Modal iframe\').contents().find(\'#closeBtn\').click();">'
						. Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>'
						. '<button type="button" class="btn btn-primary" data-dismiss="modal" aria-hidden="true" onclick="jQuery(\'#plugin' . $this->redirectPluginId . 'Modal iframe\').contents().find(\'#saveBtn\').click();">'
						. Text::_("JSAVE") . '</button>'
						. '<button type="button" class="btn btn-success" aria-hidden="true" onclick="jQuery(\'#plugin' . $this->redirectPluginId . 'Modal iframe\').contents().find(\'#applyBtn\').click(); return false;">'
						. Text::_("JAPPLY") . '</button>'
				)
			); ?>
		<?php endif; ?>

		<?php if (empty($this->items)) : ?>
			<joomla-alert type="warning"><?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></joomla-alert>
		<?php else : ?>
			<table class="table table-striped">
				<thead>
					<tr>
						<th style="width:1%" class="text-center nowrap">
							<?php echo JHtml::_('grid.checkall'); ?>
						</th>
						<th style="width:1%" class="text-center nowrap">
							<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap title">
							<?php echo JHtml::_('searchtools.sort', 'COM_REDIRECT_HEADING_OLD_URL', 'a.old_url', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap">
							<?php echo JHtml::_('searchtools.sort', 'COM_REDIRECT_HEADING_NEW_URL', 'a.new_url', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap d-none d-md-table-cell">
							<?php echo JHtml::_('searchtools.sort', 'COM_REDIRECT_HEADING_REFERRER', 'a.referer', $listDirn, $listOrder); ?>
						</th>
						<th style="width:1%" class="nowrap d-none d-md-table-cell">
							<?php echo JHtml::_('searchtools.sort', 'COM_REDIRECT_HEADING_CREATED_DATE', 'a.created_date', $listDirn, $listOrder); ?>
						</th>
						<th style="width:1%" class="nowrap d-none d-md-table-cell">
							<?php echo JHtml::_('searchtools.sort', 'COM_REDIRECT_HEADING_HITS', 'a.hits', $listDirn, $listOrder); ?>
						</th>
						<th style="width:1%" class="nowrap d-none d-md-table-cell">
							<?php echo JHtml::_('searchtools.sort', 'COM_REDIRECT_HEADING_STATUS_CODE', 'a.header', $listDirn, $listOrder); ?>
						</th>
						<th style="width:1%" class="nowrap d-none d-md-table-cell">
							<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td colspan="9">
							<?php echo $this->pagination->getListFooter(); ?>
						</td>
					</tr>
				</tfoot>
				<tbody>
				<?php foreach ($this->items as $i => $item) :
					$canEdit   = $user->authorise('core.edit',       'com_redirect');
					$canChange = $user->authorise('core.edit.state', 'com_redirect');
					?>
					<tr class="row<?php echo $i % 2; ?>">
						<td class="text-center">
							<?php echo JHtml::_('grid.id', $i, $item->id); ?>
						</td>
						<td class="text-center">
							<div class="btn-group">
								<?php echo JHtml::_('redirect.published', $item->published, $i); ?>
							</div>
						</td>
						<td class="break-word">
							<?php if ($canEdit) : ?>
								<a href="<?php echo Route::_('index.php?option=com_redirect&task=link.edit&id=' . $item->id); ?>" title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo $this->escape($item->old_url); ?>">
									<span class="fa fa-pencil-square mr-2" aria-hidden="true"></span><?php echo $this->escape(str_replace(Uri::root(), '', rawurldecode($item->old_url))); ?></a>
							<?php else : ?>
									<?php echo $this->escape(str_replace(Uri::root(), '', rawurldecode($item->old_url))); ?>
							<?php endif; ?>
						</td>
						<td class="small break-word">
							<?php echo $this->escape(rawurldecode($item->new_url)); ?>
						</td>
						<td class="small break-word d-none d-md-table-cell">
							<?php echo $this->escape($item->referer); ?>
						</td>
						<td class="small d-none d-md-table-cell">
							<?php echo JHtml::_('date', $item->created_date, Text::_('DATE_FORMAT_LC4')); ?>
						</td>
						<td class="d-none d-md-table-cell">
							<?php echo (int) $item->hits; ?>
						</td>
						<td class="d-none d-md-table-cell">
							<?php echo (int) $item->header; ?>
						</td>
						<td class="d-none d-md-table-cell">
							<?php echo (int) $item->id; ?>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>

		<?php if (!empty($this->items)) : ?>
			<?php echo $this->loadTemplate('addform'); ?>
		<?php endif; ?>
		<?php // Load the batch processing form if user is allowed ?>
			<?php if ($user->authorise('core.create', 'com_redirect')
				&& $user->authorise('core.edit', 'com_redirect')
				&& $user->authorise('core.edit.state', 'com_redirect')) : ?>
				<?php echo JHtml::_(
					'bootstrap.renderModal',
					'collapseModal',
					array(
						'title'  => Text::_('COM_REDIRECT_BATCH_OPTIONS'),
						'footer' => $this->loadTemplate('batch_footer'),
					),
					$this->loadTemplate('batch_body')
				); ?>
			<?php endif; ?>

		<input type="hidden" name="task" value="">
		<input type="hidden" name="boxchecked" value="0">
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
