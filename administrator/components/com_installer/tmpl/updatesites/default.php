<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   (C) 2014 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('table.columns')
    ->useScript('multiselect');

$user      = Factory::getApplication()->getIdentity();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<div id="installer-manage" class="clearfix">
    <form action="<?php echo Route::_('index.php?option=com_installer&view=updatesites'); ?>" method="post" name="adminForm" id="adminForm">
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
                            <?php echo Text::_('COM_INSTALLER_UPDATESITES_TABLE_CAPTION'); ?>,
                            <span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
                            <span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
                        </caption>
                        <thead>
                            <tr>
                                <td class="w-1 text-center">
                                    <?php echo HTMLHelper::_('grid.checkall'); ?>
                                </td>
                                <th scope="col" class="w-1 text-center">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'enabled', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_UPDATESITE_NAME', 'update_site_name', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col" class="w-20 d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_NAME', 'name', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col" class="w-10 d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_LOCATION', 'client_translated', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col" class="w-10 d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_TYPE', 'type_translated', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col" class="w-10 d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_FOLDER', 'folder_translated', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col" class="w-5 d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'update_site_id', $listDirn, $listOrder); ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($this->items as $i => $item) :
                            $canCheckin = $user->authorise('core.manage', 'com_checkin')
                                || $item->checked_out === $userId
                                || is_null($item->checked_out);
                            $canEdit    = $user->authorise('core.edit', 'com_installer');
                            ?>
                            <tr class="row<?php echo $i % 2;
                            if ((int) $item->enabled === 2) {
                                echo ' protected';
                            } ?>">
                                <td class="text-center">
                                    <?php echo HTMLHelper::_('grid.id', $i, $item->update_site_id, false, 'cid', 'cb', $item->update_site_name); ?>
                                </td>
                                <td class="text-center">
                                    <?php if (!$item->element) : ?>
                                        <strong>X</strong>
                                    <?php else : ?>
                                        <?php echo HTMLHelper::_('updatesites.state', $item->enabled, $i, $item->enabled < 2, 'cb'); ?>
                                    <?php endif; ?>
                                </td>
                                <th scope="row">
                                    <?php if ($item->checked_out) : ?>
                                        <?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'updatesites.', $canCheckin); ?>
                                    <?php endif; ?>
                                    <?php if ($canEdit) : ?>
                                        <a
                                            href="<?php echo Route::_('index.php?option=com_installer&task=updatesite.edit&update_site_id=' . (int) $item->update_site_id); ?>"
                                            title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo $this->escape($item->update_site_name); ?>"
                                        >
                                            <?php echo Text::_($item->update_site_name); ?>
                                        </a>
                                    <?php else : ?>
                                        <?php echo Text::_($item->update_site_name); ?>
                                    <?php endif; ?>
                                    <div class="small break-word">
                                        <a href="<?php echo $item->location; ?>" target="_blank" rel="noopener noreferrer"><?php echo $this->escape($item->location); ?></a>
                                    </div>
                                    <div class="small break-word">
                                        <?php if ($item->downloadKey['valid']) : ?>
                                        <span class="badge bg-info">
                                            <?php echo Text::_('COM_INSTALLER_DOWNLOADKEY_EXTRA_QUERY_LABEL'); ?>
                                        </span>
                                        <code><?php echo $item->downloadKey['value']; ?></code>
                                        <?php elseif ($item->downloadKey['supported']) : ?>
                                        <span class="badge bg-danger" tabindex="0">
                                            <?php echo Text::_('COM_INSTALLER_DOWNLOADKEY_MISSING_LABEL'); ?>
                                        </span>
                                        <div role="tooltip" id="tip-missing<?php echo $i; ?>">
                                            <?php echo Text::_('COM_INSTALLER_DOWNLOADKEY_MISSING_TIP'); ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </th>
                                <td class="d-none d-md-table-cell">
                                    <span tabindex="0">
                                        <?php echo $item->name; ?>
                                    </span>
                                    <?php if ($item->description) : ?>
                                        <div role="tooltip" id="tip<?php echo $i; ?>">
                                            <?php echo $item->description; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    <?php echo $item->client_translated; ?>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    <?php echo $item->type_translated; ?>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    <?php echo $item->folder_translated; ?>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    <?php echo $item->update_site_id; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>

                        <?php // Load the pagination. ?>
                        <?php echo $this->pagination->getListFooter(); ?>

                    <?php endif; ?>
                    <input type="hidden" name="task" value="">
                    <input type="hidden" name="boxchecked" value="0">
                    <?php echo HTMLHelper::_('form.token'); ?>
                </div>
            </div>
        </div>
    </form>
</div>
