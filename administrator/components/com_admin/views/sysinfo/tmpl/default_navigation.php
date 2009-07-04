<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_admin
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
?>
<div class="submenu-box">
	<div class="submenu-pad">
		<ul id="submenu" class="information">
			<li>
				<a id="site" class="active">
					<?php echo JText::_('Admin_System_Info'); ?></a>
			</li>
			<li>
				<a id="phpsettings">
					<?php echo JText::_('Admin_PHP_Settings'); ?></a>
			</li>
			<li>
				<a id="config">
					<?php echo JText::_('Admin_Configuration_File'); ?></a>
			</li>
			<li>
				<a id="directory">
					<?php echo JText::_('Admin_Directory_Permissions'); ?></a>
			</li>
			<li>
				<a id="phpinfo">
					<?php echo JText::_('Admin_PHP_Information'); ?></a>
			</li>
		</ul>
		<div class="clr"></div>
	</div>
</div>
<div class="clr"></div>
