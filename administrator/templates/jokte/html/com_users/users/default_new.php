<?php
/**
 * @version     $Id: default.php 19240 2010-10-28 04:45:48Z eddieajau $
 * @package     Joomla.Administrator
 * @subpackage  com_users
 * @copyright   Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');

$canDo = UsersHelper::getActions();
$listOrder  = $this->state->get('list.ordering');
$listDirn   = $this->state->get('list.direction');
?>

<form action="<?php echo JRoute::_('index.php?option=com_users&view=users');?>" method="post" name="adminForm" id="adminForm">
    <!--<fieldset id="filter-bar">
        <div class="filter-search fltlft">
            <label class="filter-search-lbl" for="filter_search"><?php echo JText::_('COM_USERS_SEARCH_USERS'); ?></label>
            <input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_USERS_SEARCH_USERS'); ?>" />
            <button type="submit"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
            <button type="button" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_RESET'); ?></button>
        </div>
        <div class="filter-select fltrt">
            <label for="filter_state">
                <?php echo JText::_('COM_USERS_FILTER_LABEL'); ?>
            </label>

            <select name="filter_state" class="inputbox" onchange="this.form.submit()">
                <option value="*"><?php echo JText::_('COM_USERS_FILTER_STATE');?></option>
                <?php echo JHtml::_('select.options', UsersHelper::getStateOptions(), 'value', 'text', $this->state->get('filter.state'));?>
            </select>

            <select name="filter_active" class="inputbox" onchange="this.form.submit()">
                <option value="*"><?php echo JText::_('COM_USERS_FILTER_ACTIVE');?></option>
                <?php echo JHtml::_('select.options', UsersHelper::getActiveOptions(), 'value', 'text', $this->state->get('filter.active'));?>
            </select>

            <select name="filter_group_id" class="inputbox" onchange="this.form.submit()">
                <option value=""><?php echo JText::_('COM_USERS_FILTER_USERGROUP');?></option>
                <?php echo JHtml::_('select.options', UsersHelper::getGroups(), 'value', 'text', $this->state->get('filter.group_id'));?>
            </select>
        </div>
    </fieldset>-->
    <div class="clr" style="margin-bottom: 10px;"></div>
    <!--<div class="pagination-top">
        <?php echo $this->pagination->getListFooter(); ?>
    </div>-->
    <?php foreach ($this->items as $i => $item) :
    $img_hash =  md5( strtolower( trim( $this->escape($item->email) ) ) );
    ?>
    <dl class="user-profile">
        <dt>
            <?php if ($canDo->get('core.edit')) : ?>
                <a href="<?php echo JRoute::_('index.php?option=com_users&task=user.edit&id='.(int) $item->id); ?>" title="<?php echo JText::sprintf('COM_USERS_EDIT_USER', $item->name); ?>">
                    <?php echo $this->escape($item->name); ?>
                </a>
            <?php else : ?>
                <?php echo $this->escape($item->name); ?>
            <?php endif; ?>
            <?php if (JDEBUG) : ?>
                <br /><small><a href="<?php echo JRoute::_('index.php?option=com_users&view=debuguser&user_id='.(int) $item->id);?>">
                <?php echo JText::_('COM_USERS_DEBUG_USER');?></a></small>
            <?php endif; ?>
        </dt>
        <dd><img src="http://www.gravatar.com/avatar/<?php echo $img_hash; ?>?d=mm" alt=" <?php echo $this->escape($item->name); ?>" /></dt>
        <dd><?php echo $this->escape($item->username); ?></dd>
        <dd><?php echo nl2br($item->group_names); ?></dd>
        <dd><?php echo $this->escape($item->email); ?></dd>
        <!--<dd><?php if ($item->lastvisitDate!='0000-00-00 00:00:00'):?>
                        <?php echo JHTML::_('date',$item->lastvisitDate, 'Y-m-d H:i:s'); ?>
                    <?php else:?>
                        <?php echo JText::_('JNEVER'); ?>
                    <?php endif;?></dd>
        <dd><?php echo JHTML::_('date',$item->registerDate, 'Y-m-d H:i:s'); ?></dd>
        <dd><?php echo (int) $item->id; ?></dd>-->
    </dl>
    <?php endforeach; ?>
    <div>
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="boxchecked" value="0" />
        <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
        <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>
