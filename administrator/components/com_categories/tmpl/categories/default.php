<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Doctrine\Inflector\InflectorFactory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

/** @var \Joomla\Component\Categories\Administrator\View\Categories\HtmlView $this */

/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('table.columns')
    ->useScript('multiselect');

$user      = $this->getCurrentUser();
$userId    = $user->id;
$extension = $this->escape($this->state->get('filter.extension'));
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrder = ($listOrder == 'a.lft' && strtolower($listDirn) == 'asc');
$parts     = explode('.', $extension, 2);
$component = $parts[0];
$section   = null;

if (count($parts) > 1) {
    $section = $parts[1];

    $inflector = InflectorFactory::create()->build();

    if ($inflector->pluralize($inflector->singularize($section)) !== $section) {
        $section = $inflector->pluralize($section);
    }
}

if ($saveOrder && !empty($this->items)) {
    $saveOrderingUrl = 'index.php?option=com_categories&task=categories.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
    HTMLHelper::_('draggablelist.draggable');
}
?>
<form action="<?php echo Route::_('index.php?option=com_categories&view=categories&extension=' . $this->state->get('filter.extension')); ?>" method="post" name="adminForm" id="adminForm">
    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">
                <?php
                // Search tools bar
                echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]);
                ?>
                <?php if (empty($this->items)) : ?>
                    <div class="alert alert-info">
                        <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
                        <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                    </div>
                <?php else : ?>
                    <table class="table" id="categoryList">
                        <caption class="visually-hidden">
                            <?php echo Text::_('COM_CATEGORIES_TABLE_CAPTION'); ?>,
                            <span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
                            <span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
                        </caption>
                        <thead>
                            <tr>
                                <td class="w-1 text-center">
                                    <?php echo HTMLHelper::_('grid.checkall'); ?>
                                </td>
                                <th scope="col" class="w-1 text-center d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('searchtools.sort', '', 'a.lft', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-sort'); ?>
                                </th>
                                <th scope="col" class="w-1 text-center">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
                                </th>
                                <?php if (isset($this->items[0]) && property_exists($this->items[0], 'count_published')) : ?>
                                    <th scope="col" class="w-10 text-center d-none d-md-table-cell">
                                        <span class="icon-check" aria-hidden="true"></span>
                                        <span class="d-none d-lg-inline"><?php echo Text::_('COM_CATEGORIES_HEADING_PUBLISHED'); ?></span>
                                    </th>
                                <?php endif; ?>
                                <?php if (isset($this->items[0]) && property_exists($this->items[0], 'count_unpublished')) : ?>
                                    <th scope="col" class="w-10 text-center d-none d-md-table-cell">
                                        <span class="icon-times" aria-hidden="true"></span>
                                        <span class="d-none d-lg-inline"><?php echo Text::_('COM_CATEGORIES_HEADING_UNPUBLISHED'); ?></span>
                                    </th>
                                <?php endif; ?>
                                <?php if (isset($this->items[0]) && property_exists($this->items[0], 'count_archived')) : ?>
                                    <th scope="col" class="w-10 text-center d-none d-md-table-cell">
                                        <span class="icon-folder icon-fw" aria-hidden="true"></span>
                                        <span class="d-none d-lg-inline"><?php echo Text::_('COM_CATEGORIES_HEADING_ARCHIVED'); ?></span>
                                    </th>
                                <?php endif; ?>
                                <?php if (isset($this->items[0]) && property_exists($this->items[0], 'count_trashed')) : ?>
                                    <th scope="col" class="w-10 text-center d-none d-md-table-cell">
                                        <span class="icon-trash" aria-hidden="true"></span>
                                        <span class="d-none d-lg-inline"><?php echo Text::_('COM_CATEGORIES_HEADING_TRASHED'); ?></span>
                                    </th>
                                <?php endif; ?>
                                <th scope="col" class="w-10 d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'access_level', $listDirn, $listOrder); ?>
                                </th>
                                <?php if ($this->assoc) : ?>
                                    <th scope="col" class="w-10 d-none d-md-table-cell">
                                        <?php echo HTMLHelper::_('searchtools.sort', 'COM_CATEGORIES_HEADING_ASSOCIATION', 'association', $listDirn, $listOrder); ?>
                                    </th>
                                <?php endif; ?>
                                <?php if (Multilanguage::isEnabled()) : ?>
                                    <th scope="col" class="w-10 d-none d-md-table-cell">
                                        <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'language_title', $listDirn, $listOrder); ?>
                                    </th>
                                <?php endif; ?>
                                <th scope="col" class="w-5 d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody <?php if ($saveOrder) :
                            ?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" data-nested="false" <?php
                               endif; ?>>
                            <?php foreach ($this->items as $i => $item) : ?>
                                <?php
                                $canEdit    = $user->authorise('core.edit', $extension . '.category.' . $item->id);
                                $canCheckin = $user->authorise('core.admin', 'com_checkin') || $item->checked_out == $userId || is_null($item->checked_out);
                                $canEditOwn = $user->authorise('core.edit.own', $extension . '.category.' . $item->id) && $item->created_user_id == $userId;
                                $canChange  = $user->authorise('core.edit.state', $extension . '.category.' . $item->id) && $canCheckin;

                                // Get the parents of item for sorting
                                if ($item->level > 1) {
                                    $parentsStr = '';
                                    $_currentParentId = $item->parent_id;
                                    $parentsStr = ' ' . $_currentParentId;
                                    for ($i2 = 0; $i2 < $item->level; $i2++) {
                                        foreach ($this->ordering as $k => $v) {
                                            $v = implode('-', $v);
                                            $v = '-' . $v . '-';
                                            if (strpos($v, '-' . $_currentParentId . '-') !== false) {
                                                $parentsStr .= ' ' . $k;
                                                $_currentParentId = $k;
                                                break;
                                            }
                                        }
                                    }
                                } else {
                                    $parentsStr = '';
                                }
                                ?>
                                <tr class="row<?php echo $i % 2; ?>" data-draggable-group="<?php echo $item->parent_id; ?>" data-item-id="<?php echo $item->id ?>" data-parents="<?php echo $parentsStr ?>" data-level="<?php echo $item->level ?>">
                                    <td class="text-center">
                                        <?php echo HTMLHelper::_('grid.id', $i, $item->id, false, 'cid', 'cb', $item->title); ?>
                                    </td>
                                    <td class="text-center d-none d-md-table-cell">
                                        <?php
                                        $iconClass = '';
                                        if (!$canChange) {
                                            $iconClass = ' inactive';
                                        } elseif (!$saveOrder) {
                                            $iconClass = ' inactive" title="' . Text::_('JORDERINGDISABLED');
                                        }
                                        ?>
                                        <span class="sortable-handler<?php echo $iconClass ?>">
                                            <span class="icon-ellipsis-v"></span>
                                        </span>
                                        <?php if ($canChange && $saveOrder) : ?>
                                            <input type="text" class="hidden" name="order[]" size="5" value="<?php echo $item->lft; ?>">
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'categories.', $canChange); ?>
                                    </td>
                                    <th scope="row">
                                        <?php $prefix = LayoutHelper::render('joomla.html.treeprefix', ['level' => $item->level]); ?>
                                        <?php echo $prefix; ?>
                                        <?php if ($item->checked_out) : ?>
                                            <?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'categories.', $canCheckin); ?>
                                        <?php endif; ?>
                                        <?php if ($canEdit || $canEditOwn) : ?>
                                            <a href="<?php echo Route::_('index.php?option=com_categories&task=category.edit&id=' . $item->id . '&extension=' . $extension); ?>" title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo $this->escape($item->title); ?>">
                                                <?php echo $this->escape($item->title); ?></a>
                                        <?php else : ?>
                                            <?php echo $this->escape($item->title); ?>
                                        <?php endif; ?>
                                        <div>
                                            <?php echo $prefix; ?>
                                            <span class="small">
                                                <?php if (empty($item->note)) : ?>
                                                    <?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
                                                <?php else : ?>
                                                    <?php echo Text::sprintf('JGLOBAL_LIST_ALIAS_NOTE', $this->escape($item->alias), $this->escape($item->note)); ?>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                    </th>
                                    <?php if (isset($this->items[0]) && property_exists($this->items[0], 'count_published')) : ?>
                                        <td class="text-center btns d-none d-md-table-cell itemnumber">
                                            <a class="btn <?php echo ($item->count_published > 0) ? 'btn-success' : 'btn-secondary'; ?>" href="<?php echo Route::_('index.php?option=' . $component . ($section ? '&view=' . $section : '') . '&filter[category_id]=' . (int) $item->id . '&filter[published]=1&filter[level]=1'); ?>" aria-describedby="tip-publish<?php echo $i; ?>">
                                                <?php echo $item->count_published; ?>
                                            </a>
                                            <div role="tooltip" id="tip-publish<?php echo $i; ?>">
                                                <?php echo Text::_('COM_CATEGORIES_COUNT_PUBLISHED_ITEMS'); ?>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                    <?php if (isset($this->items[0]) && property_exists($this->items[0], 'count_unpublished')) : ?>
                                        <td class="text-center btns d-none d-md-table-cell itemnumber">
                                            <a class="btn <?php echo ($item->count_unpublished > 0) ? 'btn-danger' : 'btn-secondary'; ?>" href="<?php echo Route::_('index.php?option=' . $component . ($section ? '&view=' . $section : '') . '&filter[category_id]=' . (int) $item->id . '&filter[published]=0&filter[level]=1'); ?>" aria-describedby="tip-unpublish<?php echo $i; ?>">
                                                <?php echo $item->count_unpublished; ?>
                                            </a>
                                            <div role="tooltip" id="tip-unpublish<?php echo $i; ?>">
                                                <?php echo Text::_('COM_CATEGORIES_COUNT_UNPUBLISHED_ITEMS'); ?>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                    <?php if (isset($this->items[0]) && property_exists($this->items[0], 'count_archived')) : ?>
                                        <td class="text-center btns d-none d-md-table-cell itemnumber">
                                            <a class="btn <?php echo ($item->count_archived > 0) ? 'btn-info' : 'btn-secondary'; ?>" href="<?php echo Route::_('index.php?option=' . $component . ($section ? '&view=' . $section : '') . '&filter[category_id]=' . (int) $item->id . '&filter[published]=2&filter[level]=1'); ?>" aria-describedby="tip-archive<?php echo $i; ?>">
                                                <?php echo $item->count_archived; ?>
                                            </a>
                                            <div role="tooltip" id="tip-archive<?php echo $i; ?>">
                                                <?php echo Text::_('COM_CATEGORIES_COUNT_ARCHIVED_ITEMS'); ?>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                    <?php if (isset($this->items[0]) && property_exists($this->items[0], 'count_trashed')) : ?>
                                        <td class="text-center btns d-none d-md-table-cell itemnumber">
                                            <a class="btn <?php echo ($item->count_trashed > 0) ? 'btn-dark' : 'btn-secondary'; ?>" href="<?php echo Route::_('index.php?option=' . $component . ($section ? '&view=' . $section : '') . '&filter[category_id]=' . (int) $item->id . '&filter[published]=-2&filter[level]=1'); ?>" aria-describedby="tip-trash<?php echo $i; ?>">
                                                <?php echo $item->count_trashed; ?>
                                            </a>
                                            <div role="tooltip" id="tip-trash<?php echo $i; ?>">
                                                <?php echo Text::_('COM_CATEGORIES_COUNT_TRASHED_ITEMS'); ?>
                                            </div>
                                        </td>
                                    <?php endif; ?>

                                    <td class="small d-none d-md-table-cell">
                                        <?php echo $this->escape($item->access_level); ?>
                                    </td>
                                    <?php if ($this->assoc) : ?>
                                        <td class="d-none d-md-table-cell">
                                            <?php if ($item->association) : ?>
                                                <?php echo HTMLHelper::_('categoriesadministrator.association', $item->id, $extension); ?>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
                                    <?php if (Multilanguage::isEnabled()) : ?>
                                        <td class="small d-none d-md-table-cell">
                                            <?php echo LayoutHelper::render('joomla.content.language', $item); ?>
                                        </td>
                                    <?php endif; ?>
                                    <td class="d-none d-md-table-cell">
                                        <?php echo (int) $item->id; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <?php // load the pagination.
                    ?>
                    <?php echo $this->pagination->getListFooter(); ?>

                    <?php // Load the batch processing form.
                    ?>
                    <?php
                    if (
                        $user->authorise('core.create', $extension)
                        && $user->authorise('core.edit', $extension)
                        && $user->authorise('core.edit.state', $extension)
                    ) : ?>
                        <template id="joomla-dialog-batch"><?php echo $this->loadTemplate('batch_body'); ?></template>
                    <?php endif; ?>
                <?php endif; ?>

                <?php echo $this->filterForm->renderControlFields(); ?>
            </div>
        </div>
    </div>
</form>
