<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_redirect
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('multiselect');

$user      = Factory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<form action="<?php echo Route::_('index.php?option=com_redirect&view=links'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="j-main-container" class="j-main-container">
		<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
		<?php if ($this->redirectPluginId) : ?>
			<?php $link = Route::_('index.php?option=com_plugins&client_id=0&task=plugin.edit&extension_id=' . $this->redirectPluginId . '&tmpl=component&layout=modal'); ?>
			<?php echo HTMLHelper::_(
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
					'footer'      => '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal"'
						. ' onclick="Joomla.iframeButtonClick({iframeSelector: \'#plugin' . $this->redirectPluginId . 'Modal\', buttonSelector: \'#closeBtn\'})">'
						. Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>'
						. '<button type="button" class="btn btn-primary" data-bs-dismiss="modal" onclick="Joomla.iframeButtonClick({iframeSelector: \'#plugin' . $this->redirectPluginId . 'Modal\', buttonSelector: \'#saveBtn\'})">'
						. Text::_('JSAVE') . '</button>'
						. '<button type="button" class="btn btn-success" onclick="Joomla.iframeButtonClick({iframeSelector: \'#plugin' . $this->redirectPluginId . 'Modal\', buttonSelector: \'#applyBtn\'}); return false;">'
						. Text::_('JAPPLY') . '</button>'
				)
			); ?>
		<?php endif; ?>

		<?php if (empty($this->items)) : ?>
			<div class="alert alert-info">
				<span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
				<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>
			<table class="table">
				<caption class="visually-hidden">
					<?php echo Text::_('COM_REDIRECTS_TABLE_CAPTION'); ?>,
							<span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
							<span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
				</caption>
				<thead>
					<tr>
						<td class="w-1 text-center">
							<?php echo HTMLHelper::_('grid.checkall'); ?>
						</td>
						<th scope="col" class="w-1 text-center">
							<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
						</th>
						<th scope="col" class="title">
							<?php echo HTMLHelper::_('searchtools.sort', 'COM_REDIRECT_HEADING_OLD_URL', 'a.old_url', $listDirn, $listOrder); ?>
						</th>
						<th scope="col">
							<?php echo HTMLHelper::_('searchtools.sort', 'COM_REDIRECT_HEADING_NEW_URL', 'a.new_url', $listDirn, $listOrder); ?>
						</th>
						<th scope="col" class="d-none d-md-table-cell">
							<?php echo HTMLHelper::_('searchtools.sort', 'COM_REDIRECT_HEADING_REFERRER', 'a.referer', $listDirn, $listOrder); ?>
						</th>
						<th scope="col" class="w-1 d-none d-md-table-cell">
							<?php echo HTMLHelper::_('searchtools.sort', 'COM_REDIRECT_HEADING_CREATED_DATE', 'a.created_date', $listDirn, $listOrder); ?>
						</th>
						<th scope="col" class="w-1 d-none d-md-table-cell">
							<?php echo HTMLHelper::_('searchtools.sort', 'COM_REDIRECT_HEADING_HITS', 'a.hits', $listDirn, $listOrder); ?>
						</th>
						<th scope="col" class="w-1 d-none d-md-table-cell">
							<?php echo HTMLHelper::_('searchtools.sort', 'COM_REDIRECT_HEADING_STATUS_CODE', 'a.header', $listDirn, $listOrder); ?>
						</th>
						<th scope="col" class="w-1 d-none d-md-table-cell">
							<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ($this->items as $i => $item) :
					$canEdit   = $user->authorise('core.edit',       'com_redirect');
					$canChange = $user->authorise('core.edit.state', 'com_redirect');
					?>
					<tr class="row<?php echo $i % 2; ?>">
						<td class="text-center">
							<?php echo HTMLHelper::_('grid.id', $i, $item->id, false, 'cid', 'cb', $item->old_url); ?>
						</td>
						<td class="text-center">
							<?php echo HTMLHelper::_('redirect.published', $item->published, $i); ?>
						</td>
						<th scope="row" class="break-word">
							<?php if ($canEdit) : ?>
								<a href="<?php echo Route::_('index.php?option=com_redirect&task=link.edit&id=' . $item->id); ?>" title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo $this->escape($item->old_url); ?>">
									<?php echo $this->escape(str_replace(Uri::root(), '', rawurldecode($item->old_url))); ?>
								</a>
							<?php else : ?>
									<?php echo $this->escape(str_replace(Uri::root(), '', rawurldecode($item->old_url))); ?>
							<?php endif; ?>
						</th>
						<td class="small break-word">
							<?php echo $this->escape(rawurldecode($item->new_url)); ?>
						</td>
						<td class="small break-word d-none d-md-table-cell">
							<?php echo $this->escape($item->referer); ?>
						</td>
						<td class="small d-none d-md-table-cell">
							<?php echo HTMLHelper::_('date', $item->created_date, Text::_('DATE_FORMAT_LC4')); ?>
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

			<?php // load the pagination. ?>
			<?php echo $this->pagination->getListFooter(); ?>

		<?php endif; ?>

		<?php if (!empty($this->items)) : ?>
			<?php echo $this->loadTemplate('addform'); ?>
		<?php endif; ?>
		<?php // Load the batch processing form if user is allowed ?>
			<?php if ($user->authorise('core.create', 'com_redirect')
				&& $user->authorise('core.edit', 'com_redirect')
				&& $user->authorise('core.edit.state', 'com_redirect')) : ?>
				<?php echo HTMLHelper::_(
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
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
