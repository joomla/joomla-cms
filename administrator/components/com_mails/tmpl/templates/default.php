<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_mails
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('bootstrap.dropdown', '.dropdown-toggle');

/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('table.columns');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
?>
<form action="<?php echo Route::_('index.php?option=com_mails&view=templates'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">
                <?php
                // Search tools bar
                echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
                ?>
                <?php if (empty($this->items)) : ?>
                    <div class="alert alert-info">
                        <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
                        <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                    </div>
                <?php else : ?>
                    <table class="table" id="mailtemplateList">
                        <caption class="visually-hidden">
                            <?php echo Text::_('COM_MAILS_TABLE_CAPTION'); ?>,
                            <span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
                            <span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
                        </caption>
                        <thead>
                            <tr>
                                <th scope="col" class="w-20">
                                    <?php echo Text::_('JGLOBAL_TITLE'); ?>
                                </th>
                                <th scope="col" class="w-15 d-none d-md-table-cell">
                                    <?php echo Text::_('COM_MAILS_HEADING_EXTENSION'); ?>
                                </th>
                                <?php if (count($this->languages) > 1) : ?>
                                <th scope="col" class="w-10 text-center">
                                    <?php echo Text::_('COM_MAILS_HEADING_EDIT_TEMPLATES'); ?>
                                </th>
                                <?php endif; ?>
                                <th scope="col" class="w-25 d-none d-md-table-cell">
                                    <?php echo Text::_('COM_MAILS_HEADING_DESCRIPTION'); ?>
                                </th>
                                <th scope="col" class="w-20 d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.template_id', $listDirn, $listOrder); ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($this->items as $i => $item) :
                            list($component, $sub_id) = explode('.', $item->template_id, 2);
                            $sub_id = str_replace('.', '_', $sub_id);
                            ?>
                            <tr class="row<?php echo $i % 2; ?>">
                                <th scope="row">
                                    <a href="<?php echo Route::_('index.php?option=com_mails&task=template.edit&template_id=' . $item->template_id . '&language=' . $this->defaultLanguage->lang_code); ?>">
                                        <?php echo Text::_($component . '_MAIL_' . $sub_id . '_TITLE'); ?>
                                    </a>
                                </th>
                                <td class="d-none d-md-table-cell">
                                    <?php echo Text::_($component); ?>
                                </td>
                                <?php if (count($this->languages) > 1) : ?>
                                    <td>
                                        <ul class="list-unstyled d-flex justify-content-center">
                                        <?php foreach ($this->languages as $language) : ?>
                                            <li class="p-1">
                                                <a href="<?php echo Route::_('index.php?option=com_mails&task=template.edit&template_id=' . $item->template_id . '&language=' . $language->lang_code); ?>">
                                                    <?php if ($language->image) : ?>
                                                        <?php echo HTMLHelper::_('image', 'mod_languages/' . $language->image . '.gif', $language->title_native, array('title' => $language->title_native), true); ?>
                                                    <?php else : ?>
                                                        <span class="badge bg-secondary" title="<?php echo $language->title_native; ?>"><?php echo $language->lang_code; ?></span>
                                                    <?php endif; ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                        </ul>
                                    </td>
                                <?php endif; ?>
                                <td class="d-none d-md-table-cell">
                                    <?php echo Text::_($component . '_MAIL_' . $sub_id . '_DESC'); ?>
                                </td>
                                <td class="d-none d-md-table-cell text-break">
                                    <?php echo $item->template_id; ?>
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
