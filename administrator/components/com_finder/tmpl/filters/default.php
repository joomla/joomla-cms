<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

$user      = Factory::getUser();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

Text::script('COM_FINDER_INDEX_CONFIRM_DELETE_PROMPT');

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('com_finder.filters')
    ->useScript('table.columns')
    ->useScript('multiselect');

?>
<form action="<?php echo Route::_('index.php?option=com_finder&view=filters'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">
                <?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
                <?php if (empty($this->items)) : ?>
                    <div class="alert alert-info">
                        <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
                        <?php echo Text::_('COM_FINDER_NO_RESULTS_OR_FILTERS'); ?>
                    </div>
                <?php else : ?>
                <table class="table">
                    <caption class="visually-hidden">
                        <?php echo Text::_('COM_FINDER_FILTERS_TABLE_CAPTION'); ?>,
                            <span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
                            <span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
                    </caption>
                    <thead>
                        <tr>
                            <td class="w-1 text-center">
                                <?php echo HTMLHelper::_('grid.checkall'); ?>
                            </td>
                            <th scope="col" class="w-1 text-center">
                                <?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
                            </th>
                            <th scope="col">
                                <?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
                            </th>
                            <th scope="col" class="w-10 d-none d-md-table-cell">
                                <?php echo HTMLHelper::_('searchtools.sort', 'COM_FINDER_HEADING_CREATED_BY', 'a.created_by_alias', $listDirn, $listOrder); ?>
                            </th>
                            <th scope="col" class="w-10 d-none d-md-table-cell">
                                <?php echo HTMLHelper::_('searchtools.sort', 'COM_FINDER_HEADING_CREATED_ON', 'a.created', $listDirn, $listOrder); ?>
                            </th>
                            <th scope="col" class="w-5 d-none d-md-table-cell">
                                <?php echo HTMLHelper::_('searchtools.sort', 'COM_FINDER_HEADING_MAP_COUNT', 'a.map_count', $listDirn, $listOrder); ?>
                            </th>
                            <th scope="col" class="w-1 d-none d-md-table-cell">
                                <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.filter_id', $listDirn, $listOrder); ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $canCreate                  = $user->authorise('core.create', 'com_finder');
                        $canEdit                    = $user->authorise('core.edit', 'com_finder');
                        $userAuthoriseCoreManage    = $user->authorise('core.manage', 'com_checkin');
                        $userAuthoriseCoreEditState = $user->authorise('core.edit.state', 'com_finder');
                        $userId                     = $user->id;
                        foreach ($this->items as $i => $item) :
                            $canCheckIn   = $userAuthoriseCoreManage || $item->checked_out == $userId || is_null($item->checked_out);
                            $canChange    = $userAuthoriseCoreEditState && $canCheckIn;
                            $escapedTitle = $this->escape($item->title);
                            ?>
                        <tr class="row<?php echo $i % 2; ?>">
                            <td class="text-center">
                                <?php echo HTMLHelper::_('grid.id', $i, $item->filter_id, false, 'cid', 'cb', $item->title); ?>
                            </td>
                            <td class="text-center">
                                <?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'filters.', $canChange); ?>
                            </td>
                            <th scope="row">
                                <?php if ($item->checked_out) : ?>
                                    <?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'filters.', $canCheckIn); ?>
                                <?php endif; ?>
                                <?php if ($canEdit) : ?>
                                    <a href="<?php echo Route::_('index.php?option=com_finder&task=filter.edit&filter_id=' . (int) $item->filter_id); ?>">
                                        <?php echo $escapedTitle; ?></a>
                                <?php else : ?>
                                    <?php echo $escapedTitle; ?>
                                <?php endif; ?>
                            </th>
                            <td class="d-none d-md-table-cell">
                                <?php echo $item->created_by_alias ?: $item->user_name; ?>
                            </td>
                            <td class="d-none d-md-table-cell">
                                <?php echo HTMLHelper::_('date', $item->created, Text::_('DATE_FORMAT_LC4')); ?>
                            </td>
                            <td class="d-none d-md-table-cell">
                                <?php echo $item->map_count; ?>
                            </td>
                            <td class="d-none d-md-table-cell">
                                <?php echo (int) $item->filter_id; ?>
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
