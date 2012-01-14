<?php
/**
 * @version     $Id: default.php 18243 2010-07-25 15:15:53Z infograf768 $
 * @package     Joomla.Administrator
 * @subpackage  com_content
 * @copyright   Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.DS.'helpers'.DS.'html');
JHtml::_('behavior.tooltip');

$user   = JFactory::getUser();
$listOrder  = $this->state->get('list.ordering');
$listDirn   = $this->state->get('list.direction');
$canOrder   = $user->authorise('core.edit.state', 'com_content.article');
$saveOrder  = $listOrder == 'fp.ordering';
?>

<form action="<?php echo JRoute::_('index.php?option=com_content&view=featured');?>" method="post" name="adminForm">
    <fieldset id="filter-bar">
        <div class="filter-search fltlft">
            <label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
            <input type="text" name="filter_search" id="filter_search" value="<?php echo $this->state->get('filter.search'); ?>" title="<?php echo JText::_('COM_CONTENT_FILTER_SEARCH_DESC'); ?>" />
            <button type="submit"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
            <button type="button" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSearch_Filter_Clear'); ?></button>
        </div>
        <div class="filter-select fltrt">
            <select name="filter_access" class="inputbox" onchange="this.form.submit()">
                <option value=""><?php echo JText::_('JOPTION_SELECT_ACCESS');?></option>
                <?php echo JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access'));?>
            </select>

            <select name="filter_published" class="inputbox" onchange="this.form.submit()">
                <option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
                <?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true);?>
            </select>
            <select name="filter_language" class="inputbox" onchange="this.form.submit()">
                <option value=""><?php echo JText::_('JOPTION_SELECT_LANGUAGE');?></option>
                <?php echo JHtml::_('select.options', JHtml::_('contentlanguage.existing', true, true), 'value', 'text', $this->state->get('filter.language'));?>
            </select>
        </div>
    </fieldset>
    <?php if( $this->pagination->total > 0 ): ?><div id="pagination-top"><?php echo $this->pagination->getListFooter(); ?></div><?php endif; ?>
    <div class="clr"> </div>
    <?php if( $this->items ): ?>
    <table class="adminlist">
        <thead>
            <tr>
                <th width="1%">
                    <input type="checkbox" name="checkall-toggle" value="" onclick="checkAll(this)" />
                </th>
                <th width="2%">
                    <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
                </th>
                <th class="title">
                    <?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
                </th>
                <th width="5%">
                    <?php echo JHtml::_('grid.sort', 'JPUBLISHED', 'a.state', $listDirn, $listOrder); ?>
                </th>
                <th width="5%">
                    <?php echo JHtml::_('grid.sort', 'JCATEGORY', 'a.catid', $listDirn, $listOrder); ?>
                </th>
                <th width="10%">
                    <?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ORDERING', 'fp.ordering', $listDirn, $listOrder); ?>
                    <?php if ($canOrder && $saveOrder) :?>
                        <?php echo JHtml::_('grid.order',  $this->items, 'filesave.png', 'featured.saveorder'); ?>
                    <?php endif; ?>
                </th>
                <th width="10%">
                    <?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ACCESS', 'category', $listDirn, $listOrder); ?>
                </th>
                <th width="10%">
                    <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_CREATED_BY', 'a.created_by', $listDirn, $listOrder); ?>
                </th>
                <th width="5%">
                    <?php echo JHtml::_('grid.sort',  'JDATE', 'a.created', $listDirn, $listOrder); ?>
                </th>
                <th width="5%">
                    <?php echo JHtml::_('grid.sort',  'JGLOBAL_HITS', 'a.hits', $listDirn, $listOrder); ?>
                </th>
                <th width="5%" class="nowrap">
                    <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder); ?>
                </th>
            </tr>
        </thead>
        <?php if( $this->pagination->total >= 10 ): ?>
        <tfoot>
            <tr>
                <th width="1%">
                    <input type="checkbox" name="checkall-toggle" value="" onclick="checkAll(this)" />
                </th>
                <th width="2%">
                    <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
                </th>
                <th class="title">
                    <?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
                </th>
                <th width="5%">
                    <?php echo JHtml::_('grid.sort', 'JPUBLISHED', 'a.state', $listDirn, $listOrder); ?>
                </th>
                <th width="5%">
                    <?php echo JHtml::_('grid.sort', 'JCATEGORY', 'a.catid', $listDirn, $listOrder); ?>
                </th>
                <th width="10%">
                    <?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ORDERING', 'fp.ordering', $listDirn, $listOrder); ?>
                    <?php if ($canOrder && $saveOrder) :?>
                        <?php echo JHtml::_('grid.order',  $this->items, 'filesave.png', 'featured.saveorder'); ?>
                    <?php endif; ?>
                </th>
                <th width="10%">
                    <?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ACCESS', 'category', $listDirn, $listOrder); ?>
                </th>
                <th width="10%">
                    <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_CREATED_BY', 'a.created_by', $listDirn, $listOrder); ?>
                </th>
                <th width="5%">
                    <?php echo JHtml::_('grid.sort',  'JDATE', 'a.created', $listDirn, $listOrder); ?>
                </th>
                <th width="5%">
                    <?php echo JHtml::_('grid.sort',  'JGLOBAL_HITS', 'a.hits', $listDirn, $listOrder); ?>
                </th>
                <th width="5%" class="nowrap">
                    <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder); ?>
                </th>
            </tr>
        </tfoot>
        <?php endif; ?>
        <tbody>
        <?php
        foreach ($this->items as $i => $item) :
            $item->max_ordering = 0; //??
            $ordering   = ($listOrder == 'fp.ordering');
            $assetId    = 'com_content.article.'.$item->id;
            $canCreate  = $user->authorise('core.create',       'com_content.category.'.$item->catid);
            $canEdit    = $user->authorise('core.edit',         'com_content.article.'.$item->id);
            $canCheckin = $user->authorise('core.manage',       'com_checkin') || $item->checked_out==$user->get('id')|| $item->checked_out==0;
            $canChange  = $user->authorise('core.edit.state',   'com_content.article.'.$item->id) && $canCheckin;
            ?>
            <tr class="row<?php echo $i % 2; ?>">
                <td class="center">
                    <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                </td>
                <td class="center table-id">
                    <?php echo (int) $item->id; ?>
                </td>
                <td>
                    <?php if ($item->checked_out) : ?>
                        <?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'featured.', $canCheckin); ?>
                    <?php endif; ?>
                    <?php if ($canCreate || $canEdit) : ?>
                    <a class="table-title" href="<?php echo JRoute::_('index.php?option=com_content&task=article.edit&id='.$item->id);?>">
                        <?php echo $this->escape($item->title); ?></a>
                    <?php else : ?>
                        <?php echo $this->escape($item->title); ?>
                    <?php endif; ?>
                    <p class="smallsub">
                        <?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias));?></p>
                </td>
                <td class="center">
                    <?php echo JHtml::_('jgrid.published', $item->state, $i, 'articles.', $canChange); ?>
                </td>
                <td class="center">
                    <?php echo $this->escape($item->category_title); ?>
                </td>
                <td class="order">
                    <?php if ($canChange) : ?>
                        <?php if ($saveOrder) :?>
                            <span><?php echo $this->pagination->orderUpIcon($i, true, 'featured.orderup', 'JLIB_HTML_MOVE_UP', $ordering); ?></span>
                            <span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, true, 'featured.orderdown', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
                        <?php endif; ?>
                        <?php $disabled = $saveOrder ?  '' : 'disabled="disabled"'; ?>
                        <input type="text" name="order[]" size="5" value="<?php echo $item->ordering;?>" <?php echo $disabled ?> class="text-area-order" />
                    <?php else : ?>
                        <?php echo $item->ordering; ?>
                    <?php endif; ?>
                </td>
                <td class="center">
                    <?php echo $this->escape($item->access_level); ?>
                </td>
                <td class="center">
                    <?php echo $this->escape($item->author_name); ?>
                </td>
                <td class="center nowrap">
                    <?php echo JHTML::_('date',$item->created, JText::_('DATE_FORMAT_LC4')); ?>
                </td>
                <td class="center">
                    <?php echo (int) $item->hits; ?>
                </td>
                <td class="center">
                    <?php if ($item->language=='*'):?>
                        <?php echo JText::_('JALL'); ?>
                    <?php else:?>
                        <?php echo $item->language_title ? $this->escape($item->language_title) : JText::_('JUNDEFINED'); ?>
                    <?php endif;?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php if( $this->pagination->total > 0 ): ?><div id="pagination-bottom"><?php echo $this->pagination->getListFooter(); ?></div><?php endif; ?>
    <div>
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="boxchecked" value="0" />
        <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
        <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>
<?php else: ?>
    <div class="noresults"><p><?php echo JText::_('COM_CONTENT_NO_ARTICLES_LABEL'); ?></p></div>
<?php endif; ?>

