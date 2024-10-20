<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

/** @var \Joomla\Component\Installer\Administrator\View\Update\HtmlView $this */

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('multiselect')
    ->useScript('table.columns')
    ->useScript('joomla.dialog-autocreate');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<div id="installer-update" class="clearfix">
    <form action="<?php echo Route::_('index.php?option=com_installer&view=update'); ?>" method="post" name="adminForm" id="adminForm">
        <div class="row">
            <div class="col-md-12">
                <div id="j-main-container" class="j-main-container">
                    <?php if ($this->showMessage) : ?>
                        <?php echo $this->loadTemplate('message'); ?>
                    <?php endif; ?>
                    <?php echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>
                    <?php if (empty($this->items)) : ?>
                        <div class="alert alert-info">
                            <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
                            <?php echo Text::_('COM_INSTALLER_MSG_UPDATE_NOUPDATES'); ?>
                        </div>
                    <?php else : ?>
                        <table class="table">
                            <caption class="visually-hidden">
                                <?php echo Text::_('COM_INSTALLER_UPDATE_TABLE_CAPTION'); ?>,
                            <span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
                            <span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
                            </caption>
                            <thead>
                            <tr>
                                <td class="w-1 text-center">
                                    <?php echo HTMLHelper::_('grid.checkall'); ?>
                                </td>
                                <th scope="col">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_NAME', 'u.name', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col" class="d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_LOCATION', 'client_translated', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col" class="d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_TYPE', 'type_translated', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col">
                                    <?php echo Text::_('COM_INSTALLER_CURRENT_VERSION'); ?>
                                </th>
                                <th scope="col">
                                    <?php echo Text::_('COM_INSTALLER_NEW_VERSION'); ?>
                                </th>
                                <th scope="col" class="d-none d-md-table-cell">
                                    <?php echo Text::_('COM_INSTALLER_CHANGELOG'); ?>
                                </th>
                                <th scope="col" class="d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_FOLDER', 'folder_translated', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col" class="d-none d-md-table-cell">
                                    <?php echo Text::_('COM_INSTALLER_HEADING_INSTALLTYPE'); ?>
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            foreach ($this->items as $i => $item) : ?>
                                <tr class="row<?php echo $i % 2; ?>">
                                    <td class="text-center">
                                        <?php if ($item->isMissingDownloadKey) : ?>
                                        <span class="icon-ban"></span>
                                        <?php else : ?>
                                            <?php echo HTMLHelper::_('grid.id', $i, $item->update_id, false, 'cid', 'cb', $item->name); ?>
                                        <?php endif; ?>
                                    </td>
                                    <th scope="row">
                                        <span tabindex="0"><?php echo $this->escape($item->name); ?></span>
                                        <div role="tooltip" id="tip<?php echo $i; ?>">
                                            <?php echo $item->description; ?>
                                        </div>
                                        <div class="small break-word">
                                        <?php echo $item->detailsurl; ?>
                                            <?php if (!empty($item->infourl)) : ?>
                                                <br>
                                                <a href="<?php echo $item->infourl; ?>" target="_blank" rel="noopener noreferrer"><?php echo $this->escape(trim($item->infourl)); ?></a>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($item->isMissingDownloadKey) : ?>
                                            <?php $url = 'index.php?option=com_installer&task=updatesite.edit&update_site_id=' . (int) $item->update_site_id; ?>
                                            <a class="btn btn-danger btn-sm text-decoration-none" href="<?php echo Route::_($url); ?>"><?php echo Text::_('COM_INSTALLER_DOWNLOADKEY_MISSING_LABEL'); ?></a>
                                        <?php endif; ?>
                                    </th>
                                    <td class="d-none d-md-table-cell">
                                        <?php echo $item->client_translated; ?>
                                    </td>
                                    <td class="d-none d-md-table-cell">
                                        <?php echo $item->type_translated; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning"><?php echo $item->current_version; ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success"><?php echo $item->version; ?></span>
                                    </td>
                                    <td class="d-none d-md-table-cell text-center">
                                        <?php if (!empty($item->changelogurl)) :
                                            $popupOptions = [
                                                'popupType'  => 'ajax',
                                                'textHeader' => Text::sprintf('COM_INSTALLER_CHANGELOG_TITLE', $item->name, $item->version),
                                                'src'        => Route::_('index.php?option=com_installer&task=manage.loadChangelogRaw&eid=' . $item->extension_id . '&source=update&format=raw', false),
                                                'width'      => '800px',
                                                'height'     => 'fit-content',
                                            ];
                                            ?>
                                            <button type="button" class="btn btn-info btn-sm"
                                                    data-joomla-dialog="<?php echo $this->escape(json_encode($popupOptions, JSON_UNESCAPED_SLASHES)); ?>">
                                                <?php echo Text::_('COM_INSTALLER_CHANGELOG'); ?></button>
                                        <?php else :?>
                                        <span>
                                            <?php echo Text::_('COM_INSTALLER_TYPE_NONAPPLICABLE')?>
                                        </span>

                                        <?php endif; ?>
                                    </td>
                                    <td class="d-none d-md-table-cell">
                                        <?php echo $item->folder_translated; ?>
                                    </td>
                                    <td class="d-none d-md-table-cell">
                                        <?php echo $item->install_type; ?>
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
</div>
