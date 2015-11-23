<?php
/**
 * @version		$Id: default.php 01 2013-07-29 11:37:09Z maverick $
 * @package		CoreJoomla.cjlib
 * @subpackage	Components
 * @copyright	Copyright (C) 2009 - 2011 corejoomla.com. All rights reserved.
 * @author		Maverick
 * @link		http://www.corejoomla.com/
 * @license		License GNU General Public License version 2 or later
 */
defined('_JEXEC') or die;

$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$saveOrder	= $listOrder == 'a.ordering';
?><tr>
	<th><?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'q.id', $listDirn, $listOrder); ?></th>
	<th width="20">
		<?php if(APP_VERSION < 3):?>
		<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(<?php echo count($this->items); ?>);" />
		<?php else :?>
		<?php echo JHtml::_('grid.checkall'); ?>
		<?php endif;?>
	</th>
	<th><?php echo JHtml::_('grid.sort', 'COM_CJLIB_SUBJECT', 'm.subject', $listDirn, $listOrder); ?></th>	
	<th><?php echo JHtml::_('grid.sort', 'COM_CJLIB_ASSET_NAME', 'm.asset_name', $listDirn, $listOrder); ?></th>
	<th><?php echo JHtml::_('grid.sort', 'COM_CJLIB_ASSET_ID', 'm.asset_id', $listDirn, $listOrder); ?></th>
	<th><?php echo JHtml::_('grid.sort', 'COM_CJLIB_TO_ADDR', 'q.to_addr', $listDirn, $listOrder); ?></th>
	<th><?php echo JHtml::_('grid.sort', 'JDATE', 'm.created', $listDirn, $listOrder); ?></th>
	<th><?php echo JHtml::_('grid.sort', 'COM_CJLIB_PROCESSED_DATE', 'q.processed', $listDirn, $listOrder); ?></th>
	<th><?php echo JHtml::_('grid.sort', 'COM_CJLIB_HTML', 'q.html', $listDirn, $listOrder); ?></th>
	<th><?php echo JHtml::_('grid.sort', 'JSTATUS', 'q.status', $listDirn, $listOrder); ?></th>
</tr>

