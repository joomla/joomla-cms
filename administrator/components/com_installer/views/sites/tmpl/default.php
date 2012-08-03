<?php

/**
 * @author      Jeremy Wilken - Gnome on the run
 * @link        www.gnomeontherun.com
 * @copyright   Copyright 2011 Gnome on the run. All Rights Reserved.
 * @category    
 * @package     
 */

defined('_JEXEC') or die;

$listDirection = $this->escape($this->state->get('list.direction'));
$listOrder = $this->escape($this->state->get('list.order'));

?>

<form action="<?php echo JRoute::_('index.php?option=com_installer&view=servers'); ?>" method="post" name="adminForm" id="adminForm">
	
    <table class="adminlist" cellspacing="1">
        <thead>
            <tr>
                <th width="20"><input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" /></th>
                <th><?php echo JHtml::_('grid.sort', 'COM_INSTALLER_HEADING_NAME', 'name', $listDirection, $listOrder); ?></th>
                <th><?php echo JText::_('COM_INSTALLER_HEADING_LOCATION'); ?></th>
                <th width="50"><?php echo JHtml::_('grid.sort', 'JSTATUS', 'enabled', $listDirection, $listOrder); ?></th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <td colspan="5"><?php echo $this->pagination->getListFooter(); ?></td>
            </tr>
        </tfoot>
        <tbody>
            <?php if (count($this->items)) : ?>
            <?php foreach ($this->items as $i => $item) : ?>
            <tr class="row<?php echo $i%2; ?>">
                <td><?php echo JHtml::_('grid.id', $i, $item->update_site_id, false, 'cid'); ?></td>
                <td><a href="<?php echo JRoute::_('index.php?option=com_installer&view=site&id='.$item->update_site_id); ?>"><?php echo $item->name; ?></a></td>
                <td><?php echo $item->location; ?></td>
                <td align="center"><?php echo JHtml::_('jgrid.published', $item->enabled, $i, 'sites.'); ?></td>
            </tr>
            <?php endforeach; ?>
            <?php else : ?>
            <tr>
                <td colspan="5" align="center"><?php echo JText::_('COM_INSTALLER_MSG_NOSITES'); ?></td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="" />
    <input type="hidden" name="filter_order" value="<?php echo $listOrder ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirection ?>" />
    <?php echo JHtml::_('form.token'); ?>
    
</form>