<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Version;

/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('table.columns')
    ->useScript('multiselect');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<div id="installer-languages" class="clearfix">
    <form action="<?php echo Route::_('index.php?option=com_installer&view=languages'); ?>" method="post" name="adminForm" id="adminForm">
        <div class="row">
            <div class="col-md-12">
                <div id="j-main-container" class="j-main-container">
                    <?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this, 'options' => array('filterButton' => false))); ?>
                    <?php if (empty($this->items)) : ?>
                        <div class="alert alert-info">
                            <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
                            <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                        </div>
                    <?php else : ?>
                    <table class="table">
                        <caption class="visually-hidden">
                            <?php echo Text::_('COM_INSTALLER_LANGUAGES_TABLE_CAPTION'); ?>,
                            <span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
                            <span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
                        </caption>
                        <thead>
                            <tr>
                                <td class="w-5"></td>
                                <th scope="col">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'name', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col" class="w-10 text-center">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_LANGUAGE_TAG', 'element', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col" class="w-15 d-none d-md-table-cell">
                                    <?php echo Text::_('JVERSION'); ?>
                                </th>
                                <th scope="col" class="w-35 d-none d-md-table-cell">
                                    <?php echo Text::_('COM_INSTALLER_HEADING_DETAILS_URL'); ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $version = new Version();
                        $currentShortVersion = preg_replace('#^([0-9\.]+)(|.*)$#', '$1', $version->getShortVersion());
                        $i = 0;
                        foreach ($this->items as $language) :
                            preg_match('#^pkg_([a-z]{2,3}-[A-Z]{2})$#', $language->element, $element);
                            $language->code  = $element[1];
                            ?>
                            <tr class="row<?php echo $i % 2; ?>">
                                <td>
                                    <?php $buttonText = (isset($this->installedLang[0][$language->code]) || isset($this->installedLang[1][$language->code])) ? 'REINSTALL' : 'INSTALL'; ?>
                                    <?php $buttonClass = (isset($this->installedLang[0][$language->code]) || isset($this->installedLang[1][$language->code])) ? 'btn btn-success btn-sm' : 'btn btn-primary btn-sm'; ?>
                                    <?php $onclick = 'document.getElementById(\'install_url\').value = \'' . $language->detailsurl . '\'; Joomla.submitbutton(\'install.install\');'; ?>
                                    <input type="button"
                                        class="<?php echo $buttonClass; ?>"
                                        value="<?php echo Text::_('COM_INSTALLER_' . $buttonText . '_BUTTON'); ?>"
                                        aria-label="<?php echo Text::sprintf('COM_INSTALLER_' . $buttonText . '_ARIA', $language->name); ?>"
                                        onclick="<?php echo $onclick; ?>"
                                    >
                                </td>
                                <th scope="row">
                                    <?php echo $language->name; ?>
                                </th>
                                <td class="text-center">
                                    <?php echo $language->code; ?>
                                </td>
                                <td class="d-none d-md-table-cell">
                                        <?php $minorVersion = $version::MAJOR_VERSION . '.' . $version::MINOR_VERSION; ?>
                                        <?php // Display a Note if language pack version is not equal to Joomla version ?>
                                        <?php if (strpos($language->version, $minorVersion) !== 0 || strpos($language->version, $currentShortVersion) !== 0) : ?>
                                            <span class="badge bg-warning text-dark"><?php echo $language->version; ?></span>
                                            <span class="icon-info-circle" aria-hidden="true" tabindex="0"></span>
                                            <div role="tooltip" class="text-start" id="tip<?php echo $language->code; ?>">
                                            <?php echo Text::_('JGLOBAL_LANGUAGE_VERSION_NOT_PLATFORM'); ?>
                                            </div>
                                        <?php else : ?>
                                            <span class="badge bg-success"><?php echo $language->version; ?></span>
                                        <?php endif; ?>
                                </td>
                                <td class="small d-none d-md-table-cell">
                                    <a href="<?php echo $language->detailsurl; ?>" target="_blank"><?php echo $language->detailsurl; ?></a>
                                </td>
                            </tr>
                            <?php $i++; ?>
                        <?php endforeach; ?>
                        </tbody>
                    </table>

                        <?php // load the pagination. ?>
                        <?php echo $this->pagination->getListFooter(); ?>

                    <?php endif; ?>
                    <input type="hidden" name="task" value="">
                    <input type="hidden" name="return" value="<?php echo base64_encode('index.php?option=com_installer&view=languages') ?>">
                    <input type="hidden" id="install_url" name="install_url">
                    <input type="hidden" name="installtype" value="url">
                    <input type="hidden" name="package" value="language">
                    <input type="hidden" name="boxchecked" value="0">
                    <?php echo HTMLHelper::_('form.token'); ?>
                </div>
            </div>
        </div>
    </form>
</div>
