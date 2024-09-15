<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

/** @var \Joomla\Component\Modules\Administrator\View\Modules\HtmlView $this */

/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('table.columns')
    ->useScript('multiselect');

$clientId  = (int) $this->state->get('client_id', 0);
$user      = $this->getCurrentUser();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrder = ($listOrder == 'a.ordering');

if ($saveOrder && !empty($this->items)) {
    $saveOrderingUrl = 'index.php?option=com_modules&task=modules.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
    HTMLHelper::_('draggablelist.draggable');
}
?>
<form action="<?php echo Route::_('index.php?option=com_modules&view=modules&client_id=' . $clientId); ?>" method="post" name="adminForm" id="adminForm">
    <div id="j-main-container" class="j-main-container">
        <?php echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>
        <?php if ($this->total > 0) : ?>
            <table class="table" id="moduleList">
                <caption class="visually-hidden">
                    <?php echo Text::_('COM_MODULES_TABLE_CAPTION'); ?>,
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
                        <th scope="col" class="w-10 d-none d-md-table-cell">
                            <?php echo HTMLHelper::_('searchtools.sort', 'COM_MODULES_HEADING_POSITION', 'a.position', $listDirn, $listOrder); ?>
                        </th>
                        <th scope="col" class="w-10 d-none d-md-table-cell">
                            <?php echo HTMLHelper::_('searchtools.sort', 'COM_MODULES_HEADING_MODULE', 'name', $listDirn, $listOrder); ?>
                        </th>
                        <?php if ($clientId === 0) : ?>
                        <th scope="col" class="w-10 d-none d-md-table-cell">
                            <?php echo HTMLHelper::_('searchtools.sort', 'COM_MODULES_HEADING_PAGES', 'pages', $listDirn, $listOrder); ?>
                        </th>
                        <?php endif; ?>
                        <th scope="col" class="w-10 d-none d-md-table-cell">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'ag.title', $listDirn, $listOrder); ?>
                        </th>
                        <?php if (($clientId === 0) && (Multilanguage::isEnabled())) : ?>
                        <th scope="col" class="w-10 d-none d-md-table-cell">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'l.title', $listDirn, $listOrder); ?>
                        </th>
                        <?php elseif ($clientId === 1 && ModuleHelper::isAdminMultilang()) : ?>
                        <th scope="col" class="w-10 d-none d-md-table-cell">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'a.language', $listDirn, $listOrder); ?>
                        </th>
                        <?php endif; ?>
                        <th scope="col" class="w-5 d-none d-md-table-cell">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
                        </th>
                    </tr>
                </thead>
                <tbody <?php if ($saveOrder) :
                    ?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" data-nested="false"<?php
                       endif; ?>>
                <?php foreach ($this->items as $i => $item) :
                    $ordering   = ($listOrder == 'a.ordering');
                    $canCreate  = $user->authorise('core.create', 'com_modules');
                    $canEdit    = $user->authorise('core.edit', 'com_modules.module.' . $item->id);
                    $canCheckin = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $user->id || is_null($item->checked_out);
                    $canChange  = $user->authorise('core.edit.state', 'com_modules.module.' . $item->id) && $canCheckin;
                    ?>
                    <tr class="row<?php echo $i % 2; ?>" data-draggable-group="<?php echo $item->position ?: 'none'; ?>">
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
                                <span class="icon-ellipsis-v"></span>
                            </span>
                            <?php if ($canChange && $saveOrder) : ?>
                                <input type="text" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order hidden">
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php // Check if extension is enabled ?>
                            <?php if ($item->enabled > 0) : ?>
                                <?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'modules.', $canChange, 'cb', $item->publish_up, $item->publish_down); ?>
                            <?php else : ?>
                                <?php // Extension is not enabled, show a message that indicates this. ?>
                                <span class="tbody-icon" title="<?php echo Text::sprintf('COM_MODULES_MSG_MANAGE_EXTENSION_DISABLED', $this->escape($item->name)); ?>">
                                    <span class="icon-minus-circle" aria-hidden="true"></span>
                                </span>
                            <?php endif; ?>
                        </td>
                        <th scope="row" class="has-context">
                            <div>
                                <?php if ($item->checked_out) : ?>
                                    <?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'modules.', $canCheckin); ?>
                                <?php endif; ?>
                                <?php if ($canEdit) : ?>
                                    <a href="<?php echo Route::_('index.php?option=com_modules&task=module.edit&id=' . (int) $item->id); ?>" title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo $this->escape($item->title); ?>">
                                        <?php echo $this->escape($item->title); ?></a>
                                <?php else : ?>
                                    <?php echo $this->escape($item->title); ?>
                                <?php endif; ?>

                                <?php if (!empty($item->note)) : ?>
                                    <div class="small">
                                        <?php echo Text::sprintf('JGLOBAL_LIST_NOTE', $this->escape($item->note)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </th>
                        <td class="d-none d-md-table-cell">
                            <?php if ($item->position) : ?>
                                <span class="badge bg-info">
                                    <?php echo $item->position; ?>
                                </span>
                            <?php else : ?>
                                <span class="badge bg-secondary">
                                    <?php echo Text::_('JNONE'); ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="small d-none d-md-table-cell">
                            <?php echo $item->name; ?>
                        </td>
                        <?php if ($clientId === 0) : ?>
                        <td class="small d-none d-md-table-cell">
                            <?php echo $item->pages; ?>
                        </td>
                        <?php endif; ?>
                        <td class="small d-none d-md-table-cell">
                            <?php echo $this->escape($item->access_level); ?>
                        </td>
                        <?php if (($clientId === 0) && (Multilanguage::isEnabled())) : ?>
                        <td class="small d-none d-md-table-cell">
                            <?php echo LayoutHelper::render('joomla.content.language', $item); ?>
                        </td>
                        <?php elseif ($clientId === 1 && ModuleHelper::isAdminMultilang()) : ?>
                            <td class="small d-none d-md-table-cell">
                                <?php if ($item->language == '') :?>
                                    <?php echo Text::_('JUNDEFINED'); ?>
                                <?php elseif ($item->language == '*') :?>
                                    <?php echo Text::alt('JALL', 'language'); ?>
                                <?php else :?>
                                    <?php echo $this->escape($item->language); ?>
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
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

        <?php // Load the batch processing form. ?>
        <?php
        if (
            $user->authorise('core.create', 'com_modules')
            && $user->authorise('core.edit', 'com_modules')
            && $user->authorise('core.edit.state', 'com_modules')
        ) : ?>
            <template id="joomla-dialog-batch"><?php echo $this->loadTemplate('batch_body'); ?></template>
        <?php endif; ?>
        <input type="hidden" name="task" value="">
        <input type="hidden" name="boxchecked" value="0">
        <?php echo HTMLHelper::_('form.token'); ?>
    </div>
</form>
