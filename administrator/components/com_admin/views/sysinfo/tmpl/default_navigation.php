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
<div id="submenu-box">
	<div class="t"><div class="t"><div class="t"></div></div></div>
	<div class="m">
		<div class="submenu-box">
			<div class="submenu-pad">
				<ul id="submenu" class="information">
					<li>
						<a href="#" onclick="return false;" id="site" class="active">
							<?php echo JText::_('Admin_System_Info'); ?></a>
					</li>
					<li>
						<a href="#" onclick="return false;" id="phpsettings">
							<?php echo JText::_('Admin_PHP_Settings'); ?></a>
					</li>
					<li>
						<a href="#" onclick="return false;" id="config">
							<?php echo JText::_('Admin_Configuration_File'); ?></a>
					</li>
					<li>
						<a href="#" onclick="return false;" id="directory">
							<?php echo JText::_('Admin_Directory_Permissions'); ?></a>
					</li>
					<li>
						<a href="#" onclick="return false;" id="phpinfo">
							<?php echo JText::_('Admin_PHP_Information'); ?></a>
					</li>
				</ul>
				<div class="clr"></div>
			</div>
		</div>
		<div class="clr"></div>
	</div>
	<div class="b"><div class="b"><div class="b"></div></div></div>
</div>

