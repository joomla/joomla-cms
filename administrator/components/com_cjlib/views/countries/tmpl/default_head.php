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
	<th width="40"><?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'q.id', $listDirn, $listOrder); ?></th>
	<th width="20"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->items); ?>);" /></th>
	<th width="100"><?php echo JHtml::_('grid.sort', 'COM_CJLIB_COUNTRY_CODE', 'a.country_code', $listDirn, $listOrder); ?></th>	
	<th><?php echo JHtml::_('grid.sort', 'COM_CJLIB_COUNTRY_NAME', 'a.country_name', $listDirn, $listOrder); ?></th>
	<th><?php echo JHtml::_('grid.sort', 'COM_CJLIB_LANGUAGE', 'a.language', $listDirn, $listOrder); ?></th>
</tr>

