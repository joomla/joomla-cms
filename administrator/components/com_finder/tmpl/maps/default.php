<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\Finder\Administrator\Helper\FinderHelperLanguage;

$listOrder     = $this->escape($this->state->get('list.ordering'));
$listDirn      = $this->escape($this->state->get('list.direction'));
$lang          = JFactory::getLanguage();
$branchFilter  = $this->escape($this->state->get('filter.branch'));
Text::script('COM_FINDER_MAPS_CONFIRM_DELETE_PROMPT');
HTMLHelper::_('script', 'com_finder/maps.js', ['version' => 'auto', 'relative' => true]);
?>
<form action="<?php echo Route::_('index.php?option=com_finder&view=maps'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div id="j-sidebar-container" class="col-md-2">
			<?php echo $this->sidebar; ?>
		</div>
		<div class="col-md-10">
			<div id="j-main-container" class="j-main-container">
				<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
				<?php if (empty($this->items)) : ?>
					<div class="alert alert-warning">
						<?php echo Text::_('COM_FINDER_MAPS_NO_CONTENT'); ?>
					</div>
				<?php else : ?>
				<table class="table">
					<caption id="captionTable" class="sr-only">
						<?php echo Text::_('COM_FINDER_MAPS_TABLE_CAPTION'); ?>, <?php echo Text::_('JGLOBAL_SORTED_BY'); ?>
					</caption>
						<thead>
						<tr>
							<td style="width:1%" class="text-center">
								<?php echo HTMLHelper::_('grid.checkall'); ?>
							</td>
							<th scope="col" style="width:1%" class="text-center">
								<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
							</th>
							<th scope="col">
								<?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'd.branch_title', $listDirn, $listOrder); ?>
							</th>
							<?php if (!$branchFilter) : ?>
								<th scope="col" style="width:1%" class="text-center">
									<?php echo Text::_('COM_FINDER_HEADING_CHILDREN'); ?>
								</th>
							<?php endif; ?>
							<th scope="col" style="width:1%" class="text-center">
								<span class="icon-publish" aria-hidden="true"></span>
								<span class="d-none d-md-inline"><?php echo Text::_('COM_FINDER_MAPS_COUNT_PUBLISHED_ITEMS'); ?></span>
							</th>
							<th scope="col" style="width:1%" class="text-center">
								<span class="icon-unpublish" aria-hidden="true"></span>
								<span class="d-none d-md-inline"><?php echo Text::_('COM_FINDER_MAPS_COUNT_UNPUBLISHED_ITEMS'); ?></span>
							</th>
						</tr>
					</thead>
					<tbody>
						<?php $canChange = Factory::getApplication()->getIdentity()->authorise('core.manage', 'com_finder'); ?>
						<?php foreach ($this->items as $i => $item) : ?>
						<tr class="row<?php echo $i % 2; ?>">
							<td class="text-center">
								<?php echo JHtml::_('grid.id', $i, $item->id); ?>
							</td>
							<td class="text-center">
								<?php echo JHtml::_('jgrid.published', $item->state, $i, 'maps.', $canChange, 'cb'); ?>
							</td>
							<th scope="row">
								<?php
								if (trim($item->parent_title, '**') === 'Language')
								{
									$title = FinderHelperLanguage::branchLanguageTitle($item->title);
								}
								else
								{
									$key = FinderHelperLanguage::branchSingular($item->title);
									$title = $lang->hasKey($key) ? Text::_($key) : $item->title;
								}
								?>
								<?php if ((int) $item->num_children === 0) : ?>
									<span class="gi">&mdash;</span>
								<?php endif; ?>
								<label for="cb<?php echo $i; ?>" style="display:inline-block;">
									<?php echo $this->escape($title); ?>
								</label>
								<?php if ($this->escape(trim($title, '**')) === 'Language' && Multilanguage::isEnabled()) : ?>
									<strong><?php echo Text::_('COM_FINDER_MAPS_MULTILANG'); ?></strong>
								<?php endif; ?>
							</th>
							<?php if (!$branchFilter) : ?>
							<td class="text-center btns">
							<?php if ((int) $item->num_children !== 0) : ?>
								<a href="<?php echo Route::_('index.php?option=com_finder&view=maps&filter[branch]=' . $item->id); ?>">
									<span class="badge <?php if ($item->num_children > 0) echo 'badge-info'; ?>"><?php echo $item->num_children; ?></span></a>
							<?php else : ?>
								-
							<?php endif; ?>
							</td>
							<?php endif; ?>
							<td class="text-center btns">
							<?php if ((int) $item->num_children === 0) : ?>
								<a class="badge <?php if ((int) $item->count_published > 0) echo 'badge-success'; ?>" title="<?php echo Text::_('COM_FINDER_MAPS_COUNT_PUBLISHED_ITEMS'); ?>" href="<?php echo Route::_('index.php?option=com_finder&view=index&filter[state]=1&filter[content_map]=' . $item->id); ?>">
								<?php echo (int) $item->count_published; ?></a>
							<?php else : ?>
								-
							<?php endif; ?>
							</td>
							<td class="text-center btns">
							<?php if ((int) $item->num_children === 0) : ?>
								<a class="badge <?php if ((int) $item->count_unpublished > 0) echo 'badge-danger'; ?>" title="<?php echo Text::_('COM_FINDER_MAPS_COUNT_UNPUBLISHED_ITEMS'); ?>" href="<?php echo Route::_('index.php?option=com_finder&view=index&filter[state]=0&filter[content_map]=' . $item->id); ?>">
								<?php echo (int) $item->count_unpublished; ?></a>
							<?php else : ?>
								-
							<?php endif; ?>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

				<?php // load the pagination. ?>
				<?php echo $this->pagination->getListFooter(); ?>

				<?php endif; ?>
			</div>

			<input type="hidden" name="task" value="display">
			<input type="hidden" name="boxchecked" value="0">
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</div>
</form>
