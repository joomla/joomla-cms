<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_tags
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('com_tags.tag-list');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<div class="com-tags-compact__items">
    <form action="<?php echo htmlspecialchars(Uri::getInstance()->toString()); ?>" method="post" name="adminForm" id="adminForm" class="com-tags-tag-list__items">
        <?php if ($this->params->get('filter_field')) : ?>
            <div class="com-tags-tag__filter btn-group">
                <label class="filter-search-lbl visually-hidden" for="filter-search">
                    <?php echo Text::_('COM_TAGS_TITLE_FILTER_LABEL'); ?>
                </label>
                <input
                    type="text"
                    name="filter-search"
                    id="filter-search"
                    value="<?php echo $this->escape($this->state->get('list.filter')); ?>"
                    class="inputbox" onchange="document.adminForm.submit();"
                    placeholder="<?php echo Text::_('COM_TAGS_TITLE_FILTER_LABEL'); ?>"
                >
                <button type="submit" name="filter_submit" class="btn btn-primary"><?php echo Text::_('JGLOBAL_FILTER_BUTTON'); ?></button>
                <button type="reset" name="filter-clear-button" class="btn btn-secondary"><?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?></button>
            </div>
        <?php endif; ?>
        <?php if ($this->params->get('show_pagination_limit')) : ?>
            <div class="btn-group float-end">
                <label for="limit" class="visually-hidden">
                    <?php echo Text::_('JGLOBAL_DISPLAY_NUM'); ?>
                </label>
                <?php echo $this->pagination->getLimitBox(); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($this->items)) : ?>
            <div class="alert alert-info">
                <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
                    <?php echo Text::_('COM_TAGS_NO_ITEMS'); ?>
            </div>
        <?php else : ?>
            <table class="com-tags-tag-list__category category table table-striped table-bordered table-hover">
                <thead<?php echo $this->params->get('show_headings', '1') ? '' : ' class="visually-hidden"'; ?>>
                    <tr>
                        <th scope="col" id="categorylist_header_title">
                            <?php echo HTMLHelper::_('grid.sort', 'JGLOBAL_TITLE', 'c.core_title', $listDirn, $listOrder); ?>
                        </th>
                        <?php if ($date = $this->params->get('tag_list_show_date')) : ?>
                            <th scope="col" id="categorylist_header_date">
                                <?php if ($date === 'created') : ?>
                                    <?php echo HTMLHelper::_('grid.sort', 'COM_TAGS_' . $date . '_DATE', 'c.core_created_time', $listDirn, $listOrder); ?>
                                <?php elseif ($date === 'modified') : ?>
                                    <?php echo HTMLHelper::_('grid.sort', 'COM_TAGS_' . $date . '_DATE', 'c.core_modified_time', $listDirn, $listOrder); ?>
                                <?php elseif ($date === 'published') : ?>
                                    <?php echo HTMLHelper::_('grid.sort', 'COM_TAGS_' . $date . '_DATE', 'c.core_publish_up', $listDirn, $listOrder); ?>
                                <?php endif; ?>
                            </th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->items as $i => $item) : ?>
                        <?php if ($item->core_state == 0) : ?>
                            <tr class="system-unpublished cat-list-row<?php echo $i % 2; ?>">
                        <?php else : ?>
                            <tr class="cat-list-row<?php echo $i % 2; ?>" >
                        <?php endif; ?>
                            <th scope="row" class="list-title">
                                <?php if (($item->type_alias === 'com_users.category') || ($item->type_alias === 'com_banners.category')) : ?>
                                    <?php echo $this->escape($item->core_title); ?>
                                <?php else : ?>
                                    <a href="<?php echo Route::_($item->link); ?>">
                                        <?php echo $this->escape($item->core_title); ?>
                                    </a>
                                <?php endif; ?>
                                <?php if ($item->core_state == 0) : ?>
                                    <span class="list-published badge bg-warning text-light">
                                        <?php echo Text::_('JUNPUBLISHED'); ?>
                                    </span>
                                <?php endif; ?>
                            </th>
                            <?php if ($this->params->get('tag_list_show_date')) : ?>
                                <td class="list-date">
                                    <?php
                                    echo HTMLHelper::_(
                                        'date',
                                        $item->displayDate,
                                        $this->escape($this->params->get('date_format', Text::_('DATE_FORMAT_LC3')))
                                    ); ?>
                                </td>
                            <?php endif; ?>
                            </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <?php // Add pagination links ?>
        <?php if (($this->params->def('show_pagination', 2) == 1 || ($this->params->get('show_pagination') == 2)) && ($this->pagination->pagesTotal > 1)) : ?>
            <div class="com-tags-tag-list__pagination w-100">
                <?php if ($this->params->def('show_pagination_results', 1)) : ?>
                    <p class="counter float-end pt-3 pe-2">
                        <?php echo $this->pagination->getPagesCounter(); ?>
                    </p>
                <?php endif; ?>
                <?php echo $this->pagination->getPagesLinks(); ?>
            </div>
        <?php endif; ?>
        <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>">
        <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>">
        <input type="hidden" name="limitstart" value="">
        <input type="hidden" name="task" value="">
    </form>
</div>
