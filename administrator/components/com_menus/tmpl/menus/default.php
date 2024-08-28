<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

/** @var \Joomla\Component\Menus\Administrator\View\Menus\HtmlView $this */

/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('table.columns')
    ->useScript('multiselect')
    ->useScript('com_menus.admin-menus')
    ->useScript('joomla.dialog-autocreate');

$uri       = Uri::getInstance();
$return    = base64_encode($uri);
$user      = $this->getCurrentUser();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$modMenuId = (int) $this->get('ModMenuId');
$itemIds   = [];

foreach ($this->items as $item) {
    if ($user->authorise('core.edit', 'com_menus')) {
        $itemIds[] = $item->id;
    }
}

$saveOrder = $listOrder == 'a.ordering';

if ($saveOrder) {
    $saveOrderingUrl = 'index.php?option=com_menus&task=menus.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
    HTMLHelper::_('draggablelist.draggable');
}

$this->getDocument()->addScriptOptions('menus-default', ['items' => $itemIds]);

// Set up the modal options that will be used for module editor
$popupOptionsEdit = [
    'popupType'  => 'iframe',
    'textHeader' => Text::_('COM_MENUS_EDIT_MODULE_SETTINGS'),
];
$popupOptionsAdd = [
    'popupType'  => 'iframe',
    'textHeader' => Text::_('COM_MENUS_ADD_MENU_MODULE'),
];

?>
<form action="<?php echo Route::_('index.php?option=com_menus&view=menus'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">
                <?php echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this, 'options' => ['filterButton' => false]]); ?>
                <?php if (empty($this->items)) : ?>
                    <div class="alert alert-info">
                        <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
                        <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                    </div>
                <?php else : ?>
                    <table class="table" id="menuList">
                        <caption class="visually-hidden">
                            <?php echo Text::_('COM_MENUS_MENUS_TABLE_CAPTION'); ?>,
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
                                <th scope="col">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col" class="w-5 text-center">
                                    <?php echo Text::_('COM_MENUS_MENUS'); ?>
                                </th>
                                <th scope="col" class="w-10 text-center  d-none d-md-table-cell">
                                    <span class="icon-check" aria-hidden="true"></span>
                                    <span class="d-none d-md-inline"><?php echo Text::_('COM_MENUS_HEADING_PUBLISHED_ITEMS'); ?></span>
                                </th>
                                <th scope="col" class="w-10 text-center d-none d-md-table-cell">
                                    <span class="icon-times" aria-hidden="true"></span>
                                    <span class="d-none d-md-inline"><?php echo Text::_('COM_MENUS_HEADING_UNPUBLISHED_ITEMS'); ?></span>
                                </th>
                                <th scope="col" class="w-10 text-center d-none d-md-table-cell">
                                    <span class="icon-trash" aria-hidden="true"></span>
                                    <span class="d-none d-md-inline"><?php echo Text::_('COM_MENUS_HEADING_TRASHED_ITEMS'); ?></span>
                                </th>
                                <th scope="col" class="w-10 text-center d-none d-lg-table-cell">
                                    <span class="icon-cube" aria-hidden="true"></span>
                                    <span class="d-none d-md-inline"><?php echo Text::_('COM_MENUS_HEADING_LINKED_MODULES'); ?></span>
                                </th>
                                <th scope="col" class="w-5 d-none d-lg-table-cell text-center">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody <?php if ($saveOrder) :
                            ?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" data-nested="false"<?php
                               endif; ?>>
                        <?php foreach ($this->items as $i => $item) :
                            $ordering       = ($listOrder == 'a.ordering');
                            $canEdit        = $user->authorise('core.edit', 'com_menus.menu.' . (int) $item->id);
                            $canManageItems = $user->authorise('core.manage', 'com_menus.menu.' . (int) $item->id);
                            $canChange      = $user->authorise('core.edit.state', 'com_menus.menu.' . (int) $item->id);
                            ?>
                            <tr class="row<?php echo $i % 2; ?>" data-draggable-group="0">
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
                                    <span class="sortable-handler<?php echo $iconClass; ?>">
                                        <span class="icon-ellipsis-v" aria-hidden="true"></span>
                                    </span>
                                    <?php if ($canChange && $saveOrder) : ?>
                                        <input type="text" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order hidden">
                                    <?php endif; ?>
                                </td>
                                <th scope="row">
                                    <div class="name break-word">
                                        <?php if ($canEdit) : ?>
                                            <a href="<?php echo Route::_('index.php?option=com_menus&task=menu.edit&id=' . $item->id); ?>">
                                                <span class="visually-hidden"><?php echo Text::_('COM_MENUS_EDIT_MENU'); ?></span><?php echo $this->escape($item->title); ?>
                                            </a>
                                        <?php else : ?>
                                            <?php echo $this->escape($item->title); ?>
                                        <?php endif; ?>
                                        <?php if (!empty($item->description)) : ?>
                                            <div class="small">
                                                (<?php echo $this->escape($item->description); ?>)
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </th>
                                <td class="text-center btns">
                                    <?php if ($canManageItems) : ?>
                                        <a href="<?php echo Route::_('index.php?option=com_menus&view=items&menutype=' . $item->menutype); ?>">
                                            <span class="icon-list" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('COM_MENUS_MENUS'); ?></span>
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center btns d-none d-md-table-cell itemnumber">
                                    <?php if ($canManageItems) : ?>
                                        <a class="btn<?php echo ($item->count_published > 0) ? ' btn-success' : ' btn-secondary'; ?>"
                                            href="<?php echo Route::_('index.php?option=com_menus&view=items&menutype=' . $item->menutype . '&filter[published]=1'); ?>"
                                            aria-describedby="tip-publish<?php echo $i; ?>">
                                            <?php echo $item->count_published; ?>
                                        </a>
                                    <?php else : ?>
                                        <span class="btn<?php echo ($item->count_published > 0) ? ' btn-success' : ' btn-secondary'; ?>" tabindex="0"
                                            aria-describedby="tip-publish<?php echo $i; ?>">
                                            <?php echo $item->count_published; ?>
                                        </span>
                                    <?php endif; ?>
                                    <div role="tooltip" id="tip-publish<?php echo $i; ?>">
                                        <?php echo Text::_('COM_MENUS_COUNT_PUBLISHED_ITEMS'); ?>
                                    </div>
                                </td>
                                <td class="text-center btns d-none d-md-table-cell itemnumber">
                                    <?php if ($canManageItems) : ?>
                                        <a class="btn<?php echo ($item->count_unpublished > 0) ? ' btn-danger' : ' btn-secondary'; ?>"
                                            href="<?php echo Route::_('index.php?option=com_menus&view=items&menutype=' . $item->menutype . '&filter[published]=0'); ?>"
                                            aria-describedby="tip-unpublish<?php echo $i; ?>">
                                            <?php echo $item->count_unpublished; ?>
                                        </a>
                                    <?php else : ?>
                                        <span class="btn<?php echo ($item->count_unpublished > 0) ? ' btn-danger' : ' btn-secondary'; ?>" tabindex="0"
                                            aria-describedby="tip-unpublish<?php echo $i; ?>">
                                            <?php echo $item->count_unpublished; ?>
                                        </span>
                                    <?php endif; ?>
                                    <div role="tooltip" id="tip-unpublish<?php echo $i; ?>">
                                        <?php echo Text::_('COM_MENUS_COUNT_UNPUBLISHED_ITEMS'); ?>
                                    </div>
                                </td>
                                <td class="text-center btns d-none d-md-table-cell itemnumber">
                                    <?php if ($canManageItems) : ?>
                                        <a class="btn<?php echo ($item->count_trashed > 0) ? ' btn-danger' : ' btn-secondary'; ?>"
                                            href="<?php echo Route::_('index.php?option=com_menus&view=items&menutype=' . $item->menutype . '&filter[published]=-2'); ?>"
                                            aria-describedby="tip-trash<?php echo $i; ?>">
                                            <?php echo $item->count_trashed; ?>
                                        </a>
                                    <?php else : ?>
                                        <span class="btn<?php echo ($item->count_trashed > 0) ? ' btn-danger' : ' btn-secondary'; ?>" tabindex="0"
                                            aria-describedby="tip-trash<?php echo $i; ?>">
                                            <?php echo $item->count_trashed; ?>
                                        </span>
                                    <?php endif; ?>
                                    <div role="tooltip" id="tip-trash<?php echo $i; ?>">
                                        <?php echo Text::_('COM_MENUS_COUNT_TRASHED_ITEMS'); ?>
                                    </div>
                                </td>
                                <td class="text-center d-none d-lg-table-cell itemnumber">
                                    <?php if (isset($this->modules[$item->menutype])) : ?>
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                                                <?php echo Text::_('COM_MENUS_MODULES'); ?>
                                                <span class="caret"></span>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <?php foreach ($this->modules[$item->menutype] as $module) : ?>
                                                    <?php if ($user->authorise('core.edit', 'com_modules.module.' . (int) $module->id)) : ?>
                                                        <?php $popupOptionsEdit['src'] = Route::_('index.php?option=com_modules&task=module.edit&tmpl=component&layout=modal&id=' . $module->id, false); ?>
                                                        <button type="button" class="dropdown-item"
                                                            data-joomla-dialog="<?php echo $this->escape(json_encode($popupOptionsEdit, JSON_UNESCAPED_SLASHES)) ?>"
                                                            data-checkin-url="<?php echo Route::_('index.php?option=com_modules&task=modules.checkin&format=json&cid[]=' . $module->id); ?>"
                                                            data-close-on-message data-reload-on-close>
                                                            <?php echo Text::sprintf('COM_MENUS_MODULE_ACCESS_POSITION', $this->escape($module->title), $this->escape($module->access_title), $this->escape($module->position)); ?></button>
                                                    <?php else : ?>
                                                        <span class="dropdown-item"><?php echo Text::sprintf('COM_MENUS_MODULE_ACCESS_POSITION', $this->escape($module->title), $this->escape($module->access_title), $this->escape($module->position)); ?></span>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </div>
                                         </div>
                                    <?php elseif ($modMenuId) : ?>
                                        <?php $popupOptionsAdd['src'] = Route::_('index.php?option=com_modules&task=module.add&tmpl=component&layout=modal&eid=' . $modMenuId . '&params[menutype]=' . $item->menutype, false); ?>
                                        <button type="button" class="btn btn-sm btn-primary"
                                            data-joomla-dialog="<?php echo $this->escape(json_encode($popupOptionsAdd, JSON_UNESCAPED_SLASHES)) ?>"
                                            data-close-on-message data-reload-on-close><?php echo Text::_('COM_MENUS_ADD_MENU_MODULE'); ?></button>
                                    <?php endif; ?>
                                </td>
                                <td class="d-none d-lg-table-cell">
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
