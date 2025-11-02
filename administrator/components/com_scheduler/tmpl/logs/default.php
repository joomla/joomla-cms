<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_scheduler
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('table.columns')
    ->useScript('multiselect');

$user      = $this->getCurrentUser();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$canEdit   = $user->authorise('core.edit', 'com_scheduler');
$canChange = $user->authorise('core.edit.state', 'com_scheduler');
?>
<form action="<?php echo Route::_('index.php?option=com_scheduler&view=logs'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">
                <?php echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>
                <?php if (empty($this->items)) : ?>
                    <div class="alert alert-info">
                        <span class="icon-info-circle" aria-hidden="true"></span><span class="sr-only"><?php echo Text::_('INFO'); ?></span>
                        <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                    </div>
                <?php else : ?>
                    <table class="table itemList">
                        <caption id="captionTable" class="visually-hidden">
                            <?php echo Text::_('COM_SCHEDULER_TABLE_CAPTION'); ?>,
                            <span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
                            <span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
                        </caption>
                        <thead>
                            <tr>
                                <td class="w-1 text-center">
                                    <?php echo HTMLHelper::_('grid.checkall'); ?>
                                </td>
                                <th scope="col" style="min-width:100px">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.taskname', $listDirn, $listOrder); ?>
                                </th>
                                <!-- Task type header -->
                                <th scope="col" class="w-1 d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_SCHEDULER_TASK_TYPE', 'a.tasktype', $listDirn, $listOrder) ?>
                                </th>
                                <th scope="col" class="w-5 d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_SCHEDULER_LABEL_TIMES_EXEC', 'a.taskid', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col" class="w-5 d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_SCHEDULER_LAST_RUN_DATE', 'a.lastdate', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col" class="w-1 d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_SCHEDULER_LABEL_DURATION', 'a.duration', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col" class="w-5 d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_SCHEDULER_LABEL_EXIT_CODE', 'a.exitcode', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col" class="w-5 d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_SCHEDULER_LABEL_NEXT_EXEC', 'a.nextdate', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col" class="w-1 d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($this->items as $i => $item) : ?>
                                <tr class="row<?php echo $i % 2; ?>">
                                    <td class="text-center">
                                        <?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
                                    </td>
                                    <th scope="row">
                                        <?php if ($canEdit) : ?>
                                            <a href="<?php echo Route::_('index.php?option=com_scheduler&task=task.edit&id=' . $item->jobid); ?>"
                                                title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo $this->escape($item->taskname); ?>"> <?php echo $this->escape($item->taskname); ?>
                                            </a>
                                        <?php else : ?>
                                            <?php echo $this->escape(str_replace(Uri::root(), '', rawurldecode($item->taskname))); ?>
                                        <?php endif; ?>
                                    </th>
                                    <!-- Item type -->
                                    <td class="small d-none d-md-table-cell">
                                        <?php echo $this->escape($item->tasktype); ?>
                                    </td>
                                    <td class="small d-none d-md-table-cell">
                                        <?php echo (int) $item->taskid; ?>
                                    </td>
                                    <td class="small d-none d-md-table-cell">
                                        <?php echo HTMLHelper::_('date.relative', $item->lastdate, Text::_('DATE_FORMAT_LC6')); ?>
                                    </td>
                                    <td class="small d-none d-md-table-cell">
                                        <?php echo $item->duration; ?>
                                    </td>
                                    <td class="small d-none d-md-table-cell">
                                        <?php
                                        switch ($item->exitcode) {
                                            case '123':
                                                echo '<span class="badge bg-secondary">' . Text::sprintf('COM_SCHEDULER_TASK_RESUME', $item->exitcode) . '</span>';
                                                break;
                                            case '0':
                                                echo '<span class="badge bg-success">' . Text::sprintf('COM_SCHEDULER_TASK_EXECUTED', $item->exitcode) . '</span>';
                                                break;
                                            default:
                                                echo '<span class="badge bg-danger">' . Text::sprintf('COM_SCHEDULER_TASK_FAILED', $item->exitcode) . '</span>';
                                                break;
                                        }
                                        ?>
                                    </td>
                                    <td class="small d-none d-md-table-cell">
                                        <?php echo HTMLHelper::_('date', $item->nextdate, Text::_('DATE_FORMAT_LC6')); ?>
                                    </td>
                                    <td class="small d-none d-md-table-cell">
                                        <?php echo (int) $item->id; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php // load the pagination.
                    ?>
                    <?php echo $this->pagination->getListFooter(); ?>
                <?php endif; ?>
                <input type="hidden" name="task" value="">
                <input type="hidden" name="boxchecked" value="0">
                <?php echo HTMLHelper::_('form.token'); ?>
            </div>
        </div>
    </div>
</form>
