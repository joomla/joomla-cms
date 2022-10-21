<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\String\PunycodeHelper;
use Joomla\CMS\Version;

/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('table.columns');

$user      = Factory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<form action="<?php echo Route::_('index.php?option=com_languages&view=installed'); ?>" method="post" id="adminForm" name="adminForm">
    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">
                <?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
                <?php if (empty($this->rows)) : ?>
                    <div class="alert alert-info">
                        <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
                        <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                    </div>
                <?php else : ?>
                <table class="table">
                    <caption class="visually-hidden">
                        <?php echo Text::_('COM_LANGUAGES_INSTALLED_TABLE_CAPTION'); ?>,
                            <span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
                            <span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
                    </caption>
                    <thead>
                        <tr>
                            <td class="w-1">
                                &#160;
                            </td>
                            <th scope="col" class="w-15">
                                <?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'name', $listDirn, $listOrder); ?>
                            </th>
                            <th scope="col" class="w-15 d-none d-md-table-cell">
                                <?php echo HTMLHelper::_('searchtools.sort', 'COM_LANGUAGES_HEADING_TITLE_NATIVE', 'nativeName', $listDirn, $listOrder); ?>
                            </th>
                            <th scope="col" class="text-center">
                                <?php echo HTMLHelper::_('searchtools.sort', 'COM_LANGUAGES_HEADING_LANG_TAG', 'language', $listDirn, $listOrder); ?>
                            </th>
                            <th scope="col" class="w-5 text-center">
                                <?php echo HTMLHelper::_('searchtools.sort', 'COM_LANGUAGES_HEADING_DEFAULT', 'published', $listDirn, $listOrder); ?>
                            </th>
                            <th scope="col" class="w-5 d-none d-md-table-cell text-center">
                                <?php echo HTMLHelper::_('searchtools.sort', 'COM_LANGUAGES_HEADING_VERSION', 'version', $listDirn, $listOrder); ?>
                            </th>
                            <th scope="col" class="w-10 d-none d-md-table-cell text-center">
                                <?php echo HTMLHelper::_('searchtools.sort', 'COM_LANGUAGES_HEADING_DATE', 'creationDate', $listDirn, $listOrder); ?>
                            </th>
                            <th scope="col" class="w-10 d-none d-md-table-cell text-center">
                                <?php echo HTMLHelper::_('searchtools.sort', 'COM_LANGUAGES_HEADING_AUTHOR', 'author', $listDirn, $listOrder); ?>
                            </th>
                            <th scope="col" class="w-10 d-none d-md-table-cell text-center">
                                <?php echo HTMLHelper::_('searchtools.sort', 'COM_LANGUAGES_HEADING_AUTHOR_EMAIL', 'authorEmail', $listDirn, $listOrder); ?>
                            </th>
                            <th scope="col" class="w-5 d-none d-md-table-cell text-center">
                                <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'extension_id', $listDirn, $listOrder); ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $version = new Version();
                    $currentShortVersion = preg_replace('#^([0-9\.]+)(|.*)$#', '$1', $version->getShortVersion());
                    foreach ($this->rows as $i => $row) :
                        $canCreate = $user->authorise('core.create', 'com_languages');
                        $canEdit   = $user->authorise('core.edit', 'com_languages');
                        $canChange = $user->authorise('core.edit.state', 'com_languages');
                        ?>
                        <tr class="row<?php echo $i % 2; ?>">
                            <td>
                                <?php echo HTMLHelper::_('languages.id', $i, $row->language); ?>
                            </td>
                            <th scope="row">
                                <label for="cb<?php echo $i; ?>">
                                    <?php echo $this->escape($row->name); ?>
                                </label>
                            </th>
                            <td class="d-none d-md-table-cell">
                                <?php echo $this->escape($row->nativeName); ?>
                            </td>
                            <td class="text-center">
                                <?php echo $this->escape($row->language); ?>
                            </td>
                            <td class="text-center">
                                <?php echo HTMLHelper::_('jgrid.isdefault', $row->published, $i, 'installed.', !$row->published && $canChange); ?>
                            </td>
                            <td class="d-none d-md-table-cell text-center">
                            <?php $minorVersion = $version::MAJOR_VERSION . '.' . $version::MINOR_VERSION; ?>
                            <?php // Display a Note if language pack version is not equal to Joomla version ?>
                            <?php if (strpos($row->version, $minorVersion) !== 0 || strpos($row->version, $currentShortVersion) !== 0) : ?>
                                <span class="badge bg-warning text-dark" title="<?php echo Text::_('JGLOBAL_LANGUAGE_VERSION_NOT_PLATFORM'); ?>"><?php echo $row->version; ?></span>
                            <?php else : ?>
                                <span class="badge bg-success"><?php echo $row->version; ?></span>
                            <?php endif; ?>
                            </td>
                            <td class="d-none d-md-table-cell text-center">
                                <?php echo $this->escape($row->creationDate); ?>
                            </td>
                            <td class="d-none d-md-table-cell text-center">
                                <?php echo $this->escape($row->author); ?>
                            </td>
                            <td class="d-none d-md-table-cell text-center">
                                <?php echo PunycodeHelper::emailToUTF8($this->escape($row->authorEmail)); ?>
                            </td>
                            <td class="d-none d-md-table-cell text-center">
                                <?php echo $this->escape($row->extension_id); ?>
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
