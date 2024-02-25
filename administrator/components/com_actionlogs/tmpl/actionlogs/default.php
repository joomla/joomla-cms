<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_actionlogs
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\Actionlogs\Administrator\Helper\ActionlogsHelper;
use Joomla\Component\Actionlogs\Administrator\View\Actionlogs\HtmlView;

/** @var HtmlView $this */

$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('table.columns')
    ->useScript('multiselect')
    ->useScript('com_actionlogs.admin-actionlogs');

?>

<form action="<?php echo Route::_('index.php?option=com_actionlogs&view=actionlogs'); ?>" method="post" name="adminForm" id="adminForm">
    <div id="j-main-container" class="j-main-container">
        <?php // Search tools bar ?>
        <?php echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>
        <?php if (empty($this->items)) : ?>
            <div class="alert alert-info">
                <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
                <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
            </div>
        <?php else : ?>
            <table class="table" id="logsList">
                <caption class="visually-hidden">
                    <?php echo Text::_('COM_ACTIONLOGS_TABLE_CAPTION'); ?>,
                            <span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
                            <span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
                </caption>
                <thead>
                    <tr>
                        <td class="w-1 text-center">
                            <?php echo HTMLHelper::_('grid.checkall'); ?>
                        </td>
                        <th scope="col" class="d-md-table-cell">
                            <?php echo HTMLHelper::_('searchtools.sort', 'COM_ACTIONLOGS_ACTION', 'a.message', $listDirn, $listOrder); ?>
                        </th>
                        <th scope="col" class="w-15 d-none d-md-table-cell">
                            <?php echo HTMLHelper::_('searchtools.sort', 'COM_ACTIONLOGS_EXTENSION', 'a.extension', $listDirn, $listOrder); ?>
                        </th>
                        <th scope="col" class="w-15 d-none d-md-table-cell">
                            <?php echo HTMLHelper::_('searchtools.sort', 'COM_ACTIONLOGS_DATE', 'a.log_date', $listDirn, $listOrder); ?>
                        </th>
                        <th scope="col" class="w-10 d-md-table-cell">
                            <?php echo HTMLHelper::_('searchtools.sort', 'COM_ACTIONLOGS_NAME', 'a.user_id', $listDirn, $listOrder); ?>
                        </th>
                        <?php if ($this->showIpColumn) : ?>
                            <th scope="col" class="w-10 d-none d-md-table-cell">
                                <?php echo HTMLHelper::_('searchtools.sort', 'COM_ACTIONLOGS_IP_ADDRESS', 'a.ip_address', $listDirn, $listOrder); ?>
                            </th>
                        <?php endif; ?>
                        <th scope="col" class="w-1 d-none d-md-table-cell">
                            <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->items as $i => $item) :
                        $extension = strtok($item->extension, '.');
                        ActionlogsHelper::loadTranslationFiles($extension); ?>
                        <tr class="row<?php echo $i % 2; ?>">
                            <td class="text-center">
                                <?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
                            </td>
                            <th scope="row" class="d-md-table-cell">
                                <?php echo ActionlogsHelper::getHumanReadableLogMessage($item); ?>
                            </th>
                            <td class="d-none d-md-table-cell">
                                <?php echo $this->escape(Text::_($extension)); ?>
                            </td>
                            <td class="d-none d-md-table-cell">
                                <?php if ($this->dateRelative) : ?>
                                    <?php echo HTMLHelper::_('date.relative', $item->log_date); ?>
                                    <div class="small">
                                <?php endif; ?>
                                    <?php echo HTMLHelper::_('date', $item->log_date, Text::_('DATE_FORMAT_LC6')); ?>
                                <?php if ($this->dateRelative) : ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="d-md-table-cell">
                                <?php echo $this->escape($item->name); ?>
                            </td>
                            <?php if ($this->showIpColumn) : ?>
                                <td class="d-none d-md-table-cell">
                                    <?php echo Text::_($this->escape($item->ip_address)); ?>
                                </td>
                            <?php endif;?>
                            <td class="d-none d-md-table-cell">
                                <?php echo (int) $item->id; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php // Load the pagination. ?>
            <?php echo $this->pagination->getListFooter(); ?>

        <?php endif;?>
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="boxchecked" value="0" />
        <?php echo HTMLHelper::_('form.token'); ?>
    </div>
</form>
<form action="<?php echo Route::_('index.php?option=com_actionlogs&view=actionlogs'); ?>" method="post" name="exportForm" id="exportForm">
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="cids" value="" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
