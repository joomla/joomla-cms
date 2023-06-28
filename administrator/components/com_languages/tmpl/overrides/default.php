<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('table.columns')
    ->useScript('multiselect');

$client    = $this->state->get('filter.client') == 'site' ? $this->text('JSITE') : $this->text('JADMINISTRATOR');
$language  = $this->state->get('filter.language');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

$oppositeClient   = $this->state->get('filter.client') == 'administrator' ? $this->text('JSITE') : $this->text('JADMINISTRATOR');
$oppositeFilename = constant('JPATH_' . strtoupper($this->state->get('filter.client') === 'site' ? 'administrator' : 'site'))
    . '/language/overrides/' . $this->state->get('filter.language', 'en-GB') . '.override.ini';
$oppositeStrings  = LanguageHelper::parseIniFile($oppositeFilename);
?>

<form action="<?php echo Route::_('index.php?option=com_languages&view=overrides'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">
                <?php echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this, 'options' => ['selectorFieldName' => 'language_client']]); ?>
                <div class="clearfix"></div>
                <?php if (empty($this->items)) : ?>
                    <div class="alert alert-info">
                        <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo $this->text('INFO'); ?></span>
                        <?php echo $this->text('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                    </div>
                <?php else : ?>
                    <table class="table" id="overrideList">
                        <caption class="visually-hidden">
                            <?php echo $this->text('COM_LANGUAGES_OVERRIDES_TABLE_CAPTION'); ?>,
                            <span id="orderedBy"><?php echo $this->text('JGLOBAL_SORTED_BY'); ?> </span>,
                            <span id="filteredBy"><?php echo $this->text('JGLOBAL_FILTERED_BY'); ?></span>
                        </caption>
                        <thead>
                            <tr>
                                <td class="w-1 text-center">
                                    <?php echo HTMLHelper::_('grid.checkall'); ?>
                                </td>
                                <th scope="col" class="w-30">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_LANGUAGES_VIEW_OVERRIDES_KEY', 'key', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col" class="d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_LANGUAGES_VIEW_OVERRIDES_TEXT', 'text', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col" class="d-none d-md-table-cell">
                                    <?php echo $this->text('COM_LANGUAGES_FIELD_LANG_TAG_LABEL'); ?>
                                </th>
                                <th scope="col" class="d-none d-md-table-cell">
                                    <?php echo $this->text('JCLIENT'); ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php $canEdit = $this->getCurrentUser()->authorise('core.edit', 'com_languages'); ?>
                        <?php $i = 0; ?>
                        <?php foreach ($this->items as $key => $text) : ?>
                            <tr class="row<?php echo $i % 2; ?>" id="overriderrow<?php echo $i; ?>">
                                <td class="text-center">
                                    <?php echo HTMLHelper::_('grid.id', $i, $key, false, 'cid', 'cb', $key); ?>
                                </td>
                                <th scope="row">
                                    <?php if ($canEdit) : ?>
                                        <a id="key[<?php echo $this->escape($key); ?>]" href="<?php echo Route::_('index.php?option=com_languages&task=override.edit&id=' . $key); ?>" title="<?php echo $this->text('JACTION_EDIT'); ?> <?php echo $this->escape($key); ?>">
                                            <?php echo $this->escape($key); ?></a>
                                    <?php else : ?>
                                        <?php echo $this->escape($key); ?>
                                    <?php endif; ?>
                                </th>
                                <td class="d-none d-md-table-cell">
                                    <span id="string[<?php echo $this->escape($key); ?>]"><?php echo HTMLHelper::_('string.truncate', $this->escape($text), 200); ?></span>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    <?php echo $language; ?>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    <?php
                                    echo $client;
                                    if (isset($oppositeStrings[$key]) && $oppositeStrings[$key] === $text) :
                                        echo '/' . $oppositeClient;
                                    endif;
                                    ?>
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
                <input type="hidden" name="boxchecked" value="0">
                <?php echo HTMLHelper::_('form.token'); ?>
            </div>
        </div>
    </div>
</form>
