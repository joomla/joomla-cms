<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_tags
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\String\Inflector;

/** @var \Joomla\Component\Tags\Administrator\View\Tags\HtmlView $this */

/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('table.columns')
    ->useScript('multiselect');

$app       = Factory::getApplication();
$user      = $this->getCurrentUser();
$userId    = $user->id;
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrder = ($listOrder == 'a.lft' && strtolower($listDirn) == 'asc');
$extension = $this->escape($this->state->get('filter.extension'));
$parts     = explode('.', $extension);
$component = $parts[0];
$section   = null;
$mode      = false;

if (count($parts) > 1) {
    $section = $parts[1];
    $inflector = Inflector::getInstance();

    if (!$inflector->isPlural($section)) {
        $section = $inflector->toPlural($section);
    }
}

if ($section === 'categories') {
    $mode = true;
    $section = $component;
    $component = 'com_categories';
}

if ($saveOrder && !empty($this->items)) {
    $saveOrderingUrl = 'index.php?option=com_tags&task=tags.saveOrderAjax&' . Session::getFormToken() . '=1';
    HTMLHelper::_('draggablelist.draggable');
}
?>
<form action="<?php echo Route::_('index.php?option=com_tags&view=tags'); ?>" method="post" name="adminForm" id="adminForm">
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
            <table class="table" id="tagList">
                <caption class="visually-hidden">
                    <?php echo Text::_('COM_TAGS_TABLE_CAPTION'); ?>,
                            <span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
                            <span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
                </caption>
                <thead>
                    <tr>
                        <td class="w-1 text-center">
                            <?php echo HTMLHelper::_('grid.checkall'); ?>
                        </td>
                        <th scope="col" class="w-1 d-none d-md-table-cell text-center">
                            <?php echo HTMLHelper::_('searchtools.sort', '', 'a.lft', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-sort'); ?>
                        </th>
                        <th scope="col" class="w-1 text-center">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
                        </th>
                        <th scope="col">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
                        </th>

                        <?php if (isset($this->items[0]) && property_exists($this->items[0], 'count_published')) : ?>
                            <th scope="col" class="w-1 text-center d-none d-md-table-cell">
                                <span class="icon-check" aria-hidden="true" title="<?php echo Text::_('COM_TAGS_COUNT_PUBLISHED_ITEMS'); ?>"><span class="visually-hidden"><?php echo Text::_('COM_TAGS_COUNT_PUBLISHED_ITEMS'); ?></span></span>
                            </th>
                        <?php endif; ?>
                        <?php if (isset($this->items[0]) && property_exists($this->items[0], 'count_unpublished')) : ?>
                            <th scope="col" class="w-1 text-center d-none d-md-table-cell">
                                <span class="icon-times" aria-hidden="true" title="<?php echo Text::_('COM_TAGS_COUNT_UNPUBLISHED_ITEMS'); ?>"><span class="visually-hidden"><?php echo Text::_('COM_TAGS_COUNT_UNPUBLISHED_ITEMS'); ?></span></span>
                            </th>
                        <?php endif; ?>
                        <?php if (isset($this->items[0]) && property_exists($this->items[0], 'count_archived')) : ?>
                            <th scope="col" class="w-1 text-center d-none d-md-table-cell">
                                <span class="icon-folder icon-fw" aria-hidden="true" title="<?php echo Text::_('COM_TAGS_COUNT_ARCHIVED_ITEMS'); ?>"><span class="visually-hidden"><?php echo Text::_('COM_TAGS_COUNT_ARCHIVED_ITEMS'); ?></span></span>
                            </th>
                        <?php endif; ?>
                        <?php if (isset($this->items[0]) && property_exists($this->items[0], 'count_trashed')) : ?>
                            <th scope="col" class="w-1 text-center d-none d-md-table-cell">
                                <span class="icon-trash" aria-hidden="true" title="<?php echo Text::_('COM_TAGS_COUNT_TRASHED_ITEMS'); ?>"><span class="visually-hidden"><?php echo Text::_('COM_TAGS_COUNT_TRASHED_ITEMS'); ?></span></span>
                            </th>
                        <?php endif; ?>

                        <th scope="col" class="w-10 d-none d-md-table-cell">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder); ?>
                        </th>
                        <?php if (Multilanguage::isEnabled()) : ?>
                            <th scope="col" class="w-10 d-none d-md-table-cell">
                                <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'a.language', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
                            </th>
                        <?php endif; ?>
                        <th scope="col" class="w-10 d-none d-md-table-cell text-center">
                            <?php echo HTMLHelper::_('searchtools.sort', 'COM_TAGS_COUNT_TAGGED_ITEMS', 'countTaggedItems', $listDirn, $listOrder); ?>
                        </th>
                        <th scope="col" class="w-5 d-none d-md-table-cell">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
                        </th>
                    </tr>
                </thead>
                <tbody <?php if ($saveOrder) :
                    ?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" data-nested="true"<?php
                       endif; ?>>
                <?php
                foreach ($this->items as $i => $item) :
                    $orderkey   = array_search($item->id, $this->ordering[$item->parent_id]);
                    $canCreate  = $user->authorise('core.create', 'com_tags');
                    $canEdit    = $user->authorise('core.edit', 'com_tags');
                    $canCheckin = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $user->id || is_null($item->checked_out);
                    $canChange  = $user->authorise('core.edit.state', 'com_tags') && $canCheckin;

                    // Get the parents of item for sorting
                    if ($item->level > 1) {
                        $parentsStr = '';
                        $_currentParentId = $item->parent_id;
                        $parentsStr = ' ' . $_currentParentId;
                        for ($j = 0; $j < $item->level; $j++) {
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
                        <tr class="row<?php echo $i % 2; ?>" data-draggable-group="<?php echo $item->parent_id; ?>"
                            data-item-id="<?php echo $item->id; ?>" data-parents="<?php echo $parentsStr; ?>"
                            data-level="<?php echo $item->level; ?>">
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
                                    <input type="text" class="hidden" name="order[]" size="5" value="<?php echo $orderkey + 1; ?>">
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'tags.', $canChange); ?>
                            </td>
                            <th scope="row">
                                <?php echo LayoutHelper::render('joomla.html.treeprefix', ['level' => $item->level]); ?>
                                <?php if ($item->checked_out) : ?>
                                    <?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'tags.', $canCheckin); ?>
                                <?php endif; ?>
                                <?php if ($canEdit) : ?>
                                    <a href="<?php echo Route::_('index.php?option=com_tags&task=tag.edit&id=' . $item->id); ?>" title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo $this->escape($item->title); ?>">
                                        <?php echo $this->escape($item->title); ?></a>
                                <?php else : ?>
                                    <?php echo $this->escape($item->title); ?>
                                <?php endif; ?>
                                <div class="small" title="<?php echo $this->escape($item->path); ?>">
                                    <?php if (empty($item->note)) : ?>
                                        <?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
                                    <?php else : ?>
                                        <?php echo Text::sprintf('JGLOBAL_LIST_ALIAS_NOTE', $this->escape($item->alias), $this->escape($item->note)); ?>
                                    <?php endif; ?>
                                </div>
                            </th>

                        <?php if (isset($this->items[0]) && property_exists($this->items[0], 'count_published')) : ?>
                            <td class="text-center btns d-none d-md-table-cell itemnumber">
                                <a class="btn <?php echo $item->count_published > 0 ? 'btn-success' : 'btn-secondary'; ?>" title="<?php echo Text::_('COM_TAGS_COUNT_PUBLISHED_ITEMS'); ?>" href="<?php echo Route::_('index.php?option=' . $component . ($mode ? '&extension=' . $section : '&view=' . $section) . '&filter[tag]=' . (int) $item->id . '&filter[published]=1'); ?>">
                                    <?php echo $item->count_published; ?></a>
                            </td>
                        <?php endif; ?>
                        <?php if (isset($this->items[0]) && property_exists($this->items[0], 'count_unpublished')) : ?>
                            <td class="text-center btns d-none d-md-table-cell itemnumber">
                                <a class="btn <?php echo $item->count_unpublished > 0 ? 'btn-danger' : 'btn-secondary'; ?>" title="<?php echo Text::_('COM_TAGS_COUNT_UNPUBLISHED_ITEMS'); ?>" href="<?php echo Route::_('index.php?option=' . $component . ($mode ? '&extension=' . $section : '&view=' . $section) . '&filter[tag]=' . (int) $item->id . '&filter[published]=0'); ?>">
                                    <?php echo $item->count_unpublished; ?></a>
                            </td>
                        <?php endif; ?>
                        <?php if (isset($this->items[0]) && property_exists($this->items[0], 'count_archived')) : ?>
                            <td class="text-center btns d-none d-md-table-cell itemnumber">
                                <a class="btn <?php echo $item->count_archived > 0 ? 'btn-primary' : 'btn-secondary'; ?>" title="<?php echo Text::_('COM_TAGS_COUNT_ARCHIVED_ITEMS'); ?>" href="<?php echo Route::_('index.php?option=' . $component . ($mode ? '&extension=' . $section : '&view=' . $section) . '&filter[tag]=' . (int) $item->id . '&filter[published]=2'); ?>">
                                    <?php echo $item->count_archived; ?></a>
                            </td>
                        <?php endif; ?>
                        <?php if (isset($this->items[0]) && property_exists($this->items[0], 'count_trashed')) : ?>
                            <td class="text-center btns d-none d-md-table-cell itemnumber">
                                <a class="btn <?php echo $item->count_trashed > 0 ? 'btn-danger' : 'btn-secondary'; ?>" title="<?php echo Text::_('COM_TAGS_COUNT_TRASHED_ITEMS'); ?>" href="<?php echo Route::_('index.php?option=' . $component . ($mode ? '&extension=' . $section : '&view=' . $section) . '&filter[tag]=' . (int) $item->id . '&filter[published]=-2'); ?>">
                                    <?php echo $item->count_trashed; ?></a>
                            </td>
                        <?php endif; ?>
                        <td class="small d-none d-md-table-cell">
                            <?php echo $this->escape($item->access_title); ?>
                        </td>
                        <?php if (Multilanguage::isEnabled()) : ?>
                            <td class="small d-none d-md-table-cell">
                                <?php echo LayoutHelper::render('joomla.content.language', $item); ?>
                            </td>
                        <?php endif; ?>
                        <td class="small d-none d-md-table-cell text-center">
                            <span class="badge bg-info">
                                <?php echo $item->countTaggedItems; ?>
                            </span>
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

            <?php // Load the batch processing form if user is allowed ?>
            <?php
            if (
                $user->authorise('core.create', 'com_tags')
                && $user->authorise('core.edit', 'com_tags')
                && $user->authorise('core.edit.state', 'com_tags')
            ) : ?>
                <template id="joomla-dialog-batch"><?php echo $this->loadTemplate('batch_body'); ?></template>
            <?php endif; ?>
        <?php endif; ?>

        <input type="hidden" name="task" value="">
        <input type="hidden" name="boxchecked" value="0">
        <?php echo HTMLHelper::_('form.token'); ?>
    </div>
</form>
