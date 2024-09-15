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
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\Finder\Administrator\Helper\LanguageHelper;

/** @var \Joomla\Component\Finder\Administrator\View\Index\HtmlView $this */

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$lang      = $this->getLanguage();

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('multiselect')
    ->useScript('table.columns')
    ->useScript('joomla.dialog-autocreate');

// Show warning that the content - finder plugin is disabled
if ($this->finderPluginId) {
    $popupOptions = [
        'popupType'  => 'iframe',
        'textHeader' => Text::_('COM_FINDER_EDIT_PLUGIN_SETTINGS'),
        'src'        => Route::_('index.php?option=com_plugins&client_id=0&task=plugin.edit&extension_id=' . $this->finderPluginId . '&tmpl=component&layout=modal', false),
    ];
    $link = HTMLHelper::_(
        'link',
        '#',
        Text::_('COM_FINDER_CONTENT_PLUGIN'),
        [
            'class'                 => 'alert-link',
            'data-joomla-dialog'    => $this->escape(json_encode($popupOptions, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)),
            'data-checkin-url'      => Route::_('index.php?option=com_plugins&task=plugins.checkin&format=json&cid[]=' . $this->finderPluginId),
            'data-close-on-message' => '',
            'data-reload-on-close'  => '',
        ],
    );
    Factory::getApplication()->enqueueMessage(Text::sprintf('COM_FINDER_INDEX_PLUGIN_CONTENT_NOT_ENABLED_LINK', $link), 'warning');
}

?>
<form action="<?php echo Route::_('index.php?option=com_finder&view=index'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">
                <?php echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>
                <?php if (empty($this->items)) : ?>
                    <div class="alert alert-info">
                        <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
                        <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                    </div>
                <?php else : ?>
                    <table class="table">
                        <caption class="visually-hidden">
                            <?php echo Text::_('COM_FINDER_INDEX_TABLE_CAPTION'); ?>,
                            <span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
                            <span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
                        </caption>
                        <thead>
                            <tr>
                                <td class="w-1 text-center">
                                    <?php echo HTMLHelper::_('grid.checkall'); ?>
                                </td>
                                <th scope="col" class="w-1 text-center">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'l.published', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'l.title', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col" class="w-10 d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_FINDER_INDEX_HEADING_INDEX_TYPE', 't.title', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col" class="w-10 d-none d-md-table-cell text-center">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_FINDER_INDEX_HEADING_INDEX_DATE', 'l.indexdate', $listDirn, $listOrder); ?>
                                </th>
                                <?php if (Multilanguage::isEnabled()) : ?>
                                    <th scope="col" class="w-10 nowrap d-none d-md-table-cell">
                                        <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'l.language', $listDirn, $listOrder); ?>
                                    </th>
                                <?php endif; ?>
                                <th scope="col" class="w-15 d-none d-md-table-cell text-center">
                                    <?php echo Text::_('COM_FINDER_INDEX_HEADING_DETAILS'); ?>
                                </th>
                                <th scope="col" class="w-30 d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_FINDER_INDEX_HEADING_LINK_URL', 'l.url', $listDirn, $listOrder); ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $canChange = $this->getCurrentUser()->authorise('core.manage', 'com_finder'); ?>
                            <?php foreach ($this->items as $i => $item) : ?>
                            <tr class="row<?php echo $i % 2; ?>">
                                <td class="text-center">
                                    <?php echo HTMLHelper::_('grid.id', $i, $item->link_id, false, 'cid', 'cb', $item->title); ?>
                                </td>
                                <td class="text-center">
                                    <?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'index.', $canChange, 'cb'); ?>
                                </td>
                                <th scope="row">
                                    <?php if (JDEBUG) : ?>
                                        <a href="index.php?option=com_finder&view=item&id=<?php echo $item->link_id; ?>">
                                            <?php echo $this->escape($item->title); ?>
                                        </a>
                                    <?php else : ?>
                                        <?php echo $this->escape($item->title); ?>
                                    <?php endif; ?>
                                </th>
                                <td class="small d-none d-md-table-cell">
                                    <?php
                                    $key = LanguageHelper::branchSingular($item->t_title);
                                    echo $lang->hasKey($key) ? Text::_($key) : $item->t_title;
                                    ?>
                                </td>
                                <td class="small d-none d-md-table-cell text-center">
                                    <?php echo HTMLHelper::_('date', $item->indexdate, Text::_('DATE_FORMAT_LC4')); ?>
                                </td>
                                <?php if (Multilanguage::isEnabled()) : ?>
                                    <td class="small d-none d-md-table-cell">
                                        <?php echo LayoutHelper::render('joomla.content.language', $item); ?>
                                    </td>
                                <?php endif; ?>
                                <td class="text-center d-none d-md-table-cell text-center">
                                <?php if ((int) $item->publish_start_date or (int) $item->publish_end_date or (int) $item->start_date or (int) $item->end_date) : ?>
                                    <span tabindex="0">
                                        <span class="icon-calendar" aria-hidden="true"></span>
                                        <span class="visually-hidden"><?php echo Text::_('COM_FINDER_INDEX_DATE_INFO_TITLE'); ?></span>
                                    </span>
                                    <div role="tooltip" id="tip<?php echo $i; ?>">
                                        <?php
                                            $publishStartDate = $item->publish_start_date !== null ? HTMLHelper::_('date', $item->publish_start_date, Text::_('DATE_FORMAT_LC5'), 'UTC') : '';
                                            $publishEndDate   = $item->publish_end_date !== null ? HTMLHelper::_('date', $item->publish_end_date, Text::_('DATE_FORMAT_LC5'), 'UTC') : '';
                                            $startDate        = $item->start_date !== null ? HTMLHelper::_('date', $item->start_date, Text::_('DATE_FORMAT_LC5'), 'UTC') : '';
                                            $endDate          = $item->end_date !== null ? HTMLHelper::_('date', $item->end_date, Text::_('DATE_FORMAT_LC5'), 'UTC') : '';
                                        ?>
                                        <?php echo Text::sprintf('COM_FINDER_INDEX_DATE_INFO', $publishStartDate, $publishEndDate, $startDate, $endDate); ?>
                                    </div>
                                <?php endif; ?>
                                </td>
                                <td class="small break-word d-none d-md-table-cell">
                                    <?php echo (strlen($item->url) > 80) ? substr($item->url, 0, 70) . '...' : $item->url; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <?php // load the pagination. ?>
                    <?php echo $this->pagination->getListFooter(); ?>
                <?php endif; ?>

                <input type="hidden" name="task" value="display">
                <input type="hidden" name="boxchecked" value="0">
                <?php echo HTMLHelper::_('form.token'); ?>
            </div>
        </div>
    </div>
</form>
