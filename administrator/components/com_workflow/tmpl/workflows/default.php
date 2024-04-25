<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       4.0.0
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

/** @var \Joomla\Component\Workflow\Administrator\View\Workflows\HtmlView $this */

/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('table.columns')
    ->useScript('multiselect');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

$saveOrder = $listOrder == 'w.ordering';

$orderingColumn = 'created';
$saveOrderingUrl = '';

if (strpos($listOrder, 'modified') !== false) {
    $orderingColumn = 'modified';
}

if ($saveOrder) {
    $saveOrderingUrl = 'index.php?option=com_workflow&task=workflows.saveOrderAjax&tmpl=component&extension=' . $this->escape($this->extension) . '&' . Session::getFormToken() . '=1';
    HTMLHelper::_('draggablelist.draggable');
}

$extension = $this->escape($this->state->get('filter.extension'));

$user = $this->getCurrentUser();
$userId = $user->id;
?>
<form action="<?php echo Route::_('index.php?option=com_workflow&view=workflows&extension=' . $extension); ?>" method="post" name="adminForm" id="adminForm">
    <div class="row">
        <?php if (!empty($this->sidebar)) : ?>
            <div id="j-sidebar-container" class="col-md-2">
                <?php echo $this->sidebar; ?>
            </div>
        <?php endif; ?>
        <div class="<?php if (!empty($this->sidebar)) {
            echo 'col-md-10';
                    } else {
                        echo 'col-md-12';
                    } ?>">
            <div id="j-main-container" class="j-main-container">
                <?php
                    // Search tools bar
                    echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this, 'options' => ['selectorFieldName' => 'extension']]);
                ?>
                <?php if (empty($this->workflows)) : ?>
                    <div class="alert alert-info">
                        <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
                        <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                    </div>
                <?php else : ?>
                    <table class="table">
                        <caption class="visually-hidden">
                            <?php echo Text::_('COM_WORKFLOW_WORKFLOWS_TABLE_CAPTION'); ?>,
                            <span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
                            <span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
                        </caption>
                        <thead>
                            <tr>
                                <td class="w-1 text-center d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('grid.checkall'); ?>
                                </td>
                                <th scope="col" class="w-1 text-center d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('searchtools.sort', '', 'w.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-sort'); ?>
                                </th>
                                <th scope="col" class="w-1 text-center">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'w.published', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_WORKFLOW_NAME', 'w.title', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col" class="w-10 text-center d-none d-md-table-cell">
                                    <?php echo Text::_('COM_WORKFLOW_DEFAULT'); ?>
                                </th>
                                <th scope="col" class="w-10 text-center d-none d-md-table-cell">
                                    <?php echo Text::_('COM_WORKFLOW_COUNT_STAGES'); ?>
                                </th>
                                <th scope="col" class="w-10 text-center d-none d-md-table-cell">
                                    <?php echo Text::_('COM_WORKFLOW_COUNT_TRANSITIONS'); ?>
                                </th>
                                <th scope="col" class="w-10 d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_WORKFLOW_ID', 'w.id', $listDirn, $listOrder); ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody <?php if ($saveOrder) :
                            ?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" data-nested="false"<?php
                               endif; ?>>
                        <?php foreach ($this->workflows as $i => $item) :
                            $states = Route::_('index.php?option=com_workflow&view=stages&workflow_id=' . $item->id . '&extension=' . $extension);
                            $transitions = Route::_('index.php?option=com_workflow&view=transitions&workflow_id=' . $item->id . '&extension=' . $extension);
                            $edit = Route::_('index.php?option=com_workflow&task=workflow.edit&id=' . $item->id . '&extension=' . $extension);

                            $canEdit    = $user->authorise('core.edit', $extension . '.workflow.' . $item->id);
                            $canCheckin = $user->authorise('core.admin', 'com_workflow') || $item->checked_out == $userId || is_null($item->checked_out);
                            $canEditOwn = $user->authorise('core.edit.own', $extension . '.workflow.' . $item->id) && $item->created_by == $userId;
                            $canChange  = $user->authorise('core.edit.state', $extension . '.workflow.' . $item->id) && $canCheckin;
                            ?>
                            <tr class="row<?php echo $i % 2; ?>" data-draggable-group="0">
                                <td class="text-center d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('grid.id', $i, $item->id, false, 'cid', 'cb', Text::_($item->title)); ?>
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
                                        <span class="icon-ellipsis-v" aria-hidden="true"></span>
                                    </span>
                                    <?php if ($canChange && $saveOrder) : ?>
                                        <input type="text" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order hidden">
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'workflows.', $canChange); ?>
                                </td>
                                <th scope="row">
                                    <?php if ($item->checked_out) : ?>
                                        <?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'workflows.', $canCheckin); ?>
                                    <?php endif; ?>
                                    <?php if ($canEdit || $canEditOwn) : ?>
                                        <a href="<?php echo $edit; ?>" title="<?php echo Text::_('JACTION_EDIT', true); ?> <?php echo Text::_($item->title, true); ?>">
                                            <?php echo $this->escape(Text::_($item->title)); ?>
                                        </a>
                                        <div class="small"><?php echo $item->description; ?></div>
                                    <?php else : ?>
                                        <?php echo $this->escape(Text::_($item->title)); ?>
                                        <div class="small"><?php echo $item->description; ?></div>
                                    <?php endif; ?>
                                </th>
                                <td class="text-center d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('jgrid.isdefault', $item->default, $i, 'workflows.', $canChange); ?>
                                </td>
                                <td class="text-center btns d-none d-md-table-cell itemnumber">
                                    <a class="btn <?php echo ($item->count_states > 0) ? 'btn-warning' : 'btn-secondary'; ?>"
                                        href="<?php echo Route::_('index.php?option=com_workflow&view=stages&workflow_id=' . (int) $item->id . '&extension=' . $extension); ?>" aria-describedby="tip-stages<?php echo $i; ?>">
                                        <?php echo $item->count_states; ?>
                                    </a>
                                    <div role="tooltip" id="tip-stages<?php echo $i; ?>">
                                        <?php echo Text::_('COM_WORKFLOW_COUNT_STAGES'); ?>
                                    </div>
                                </td>
                                <td class="text-center btns d-none d-md-table-cell itemnumber">
                                    <a class="btn <?php echo ($item->count_transitions > 0) ? 'btn-primary' : 'btn-secondary'; ?>"
                                        href="<?php echo Route::_('index.php?option=com_workflow&view=transitions&workflow_id=' . (int) $item->id . '&extension=' . $extension); ?>" aria-describedby="tip-transitions<?php echo $i; ?>">
                                        <?php echo $item->count_transitions; ?>
                                    </a>
                                    <div role="tooltip" id="tip-transitions<?php echo $i; ?>">
                                        <?php echo Text::_('COM_WORKFLOW_COUNT_TRANSITIONS'); ?>
                                    </div>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    <?php echo $item->id; ?>
                                </td>
                            </tr>
                        <?php endforeach ?>
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
