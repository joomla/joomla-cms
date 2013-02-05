<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	Templates.hathor
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		1.6
 */

// No direct access
defined('_JEXEC') or die;
?>
<div id="submenu-box">
	<ul id="submenu" class="configuration">
		<li><a href="#" onclick="return false;" id="site" class="active"><?php echo JText::_('JSITE'); ?></a></li>
		<li><a href="#" onclick="return false;" id="system"><?php echo JText::_('COM_CONFIG_SYSTEM'); ?></a></li>
		<li><a href="#" onclick="return false;" id="server"><?php echo JText::_('COM_CONFIG_SERVER'); ?></a></li>
		<li><a href="#" onclick="return false;" id="permissions"><?php echo JText::_('COM_CONFIG_PERMISSIONS'); ?></a></li>
		<li><a href="#" onclick="return false;" id="filters"><?php echo JText::_('COM_CONFIG_TEXT_FILTERS')?></a></li>
	</ul>
	<div class="clr"></div>
</div>
