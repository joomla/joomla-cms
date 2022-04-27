<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
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
$saveOrder = $listOrder == 'a.ordering';

if ($saveOrder && !empty($this->items))
{
	$saveOrderingUrl = 'index.php?option=com_languages&task=languages.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
	HTMLHelper::_('draggablelist.draggable');
}

?>
<form action="<?php echo Route::_('index.php?option=com_languages&view=languages'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
				<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
				<?php if (empty($this->items)) : ?>
					<div class="alert alert-info">
						<span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
						<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
					</div>
				<?php else : ?>
					<table class="table" id="languageList">
						<caption class="visually-hidden">
							<?php echo Text::_('COM_LANGUAGES_CONTENT_TABLE_CAPTION'); ?>,
							<span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
							<span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
						</caption>
						<thead>
							<tr>
								<td class="w-1 text-center">
									<?php echo HTMLHelper::_('grid.checkall'); ?>
								</td>
								<th scope="col" class="w-1 text-center d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-sort'); ?>
								</th>
								<th scope="col" class="w-1 text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="title">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="title d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_LANGUAGES_HEADING_TITLE_NATIVE', 'a.title_native', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="w-10 d-none d-md-table-cell text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_LANGUAGES_HEADING_LANG_TAG', 'a.lang_code', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="w-10 d-none d-md-table-cell text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_LANGUAGES_HEADING_LANG_CODE', 'a.sef', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="w-10 d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_LANGUAGES_HEADING_LANG_IMAGE', 'a.image', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="w-10 d-none d-md-table-cell text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="w-10 d-none d-md-table-cell text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_LANGUAGES_HEADING_HOMEPAGE', 'l.home', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="w-5 d-none d-md-table-cell text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.lang_id', $listDirn, $listOrder); ?>
								</th>
							</tr>
						</thead>
						<tbody<?php if ($saveOrder) : ?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>"<?php endif; ?>>
						<?php
						foreach ($this->items as $i => $item) :
							$canCreate = $user->authorise('core.create',     'com_languages');
							$canEdit   = $user->authorise('core.edit',       'com_languages');
							$canChange = $user->authorise('core.edit.state', 'com_languages');
						?>
							<tr class="row<?php echo $i % 2; ?>">
								<td>
									<?php echo HTMLHelper::_('grid.id', $i, $item->lang_id, false, 'cid', 'cb', $item->title); ?>
								</td>
								<td class="text-center d-none d-md-table-cell">
									<?php if ($canChange) :
										$disableClassName = '';
										$disabledLabel	  = '';

										if (!$saveOrder) :
											$disabledLabel    = Text::_('JORDERINGDISABLED');
											$disableClassName = 'inactive';
										endif; ?>
										<span class="sortable-handler <?php echo $disableClassName; ?>" title="<?php echo $disabledLabel; ?>">
											<span class="icon-ellipsis-v" aria-hidden="true"></span>
										</span>
										<input type="text" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order hidden">
									<?php else : ?>
										<span class="sortable-handler inactive">
											<span class="icon-ellipsis-v" aria-hidden="true"></span>
										</span>
									<?php endif; ?>
								</td>
								<td class="text-center">
									<?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'languages.', $canChange); ?>
								</td>
								<th scope="row">
									<?php if ($canEdit) : ?>
										<a href="<?php echo Route::_('index.php?option=com_languages&task=language.edit&lang_id=' . (int) $item->lang_id); ?>" title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo $this->escape($item->title); ?>">
											<?php echo $this->escape($item->title); ?></a>
									<?php else : ?>
										<?php echo $this->escape($item->title); ?>
									<?php endif; ?>
								</th>
								<td class="d-none d-md-table-cell">
									<?php echo $this->escape($item->title_native); ?>
								</td>
								<td class="d-none d-md-table-cell text-center">
									<?php echo $this->escape($item->lang_code); ?>
								</td>
								<td class="d-none d-md-table-cell text-center">
									<?php echo $this->escape($item->sef); ?>
								</td>
								<td class="d-none d-md-table-cell">
									<?php if ($item->image) : ?>
										<?php echo HTMLHelper::_('image', 'mod_languages/' . $item->image . '.gif', $item->image, array('class'=>'me-1'), true); ?><?php echo $this->escape($item->image); ?>
									<?php else : ?>
										<?php echo Text::_('JNONE'); ?>
									<?php endif; ?>
								</td>
								<td class="d-none d-md-table-cell text-center">
									<?php echo $this->escape($item->access_level); ?>
								</td>
								<td class="d-none d-md-table-cell text-center">
									<?php echo ($item->home == '1') ? Text::_('JYES') : Text::_('JNO'); ?>
								</td>
								<td class="d-none d-md-table-cell text-center">
									<?php echo $this->escape($item->lang_id); ?>
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
