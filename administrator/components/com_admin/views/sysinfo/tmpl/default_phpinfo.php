<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_admin
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
?>
<fieldset class="adminform">
	<legend><?php echo JText::_('Admin_PHP_Information'); ?></legend>
	<table class="adminform">
		<thead>
			<tr>
				<th colspan="2">
					&nbsp;
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th colspan="2">
					&nbsp;
				</th>
			</tr>
		</tfoot>
		<tbody>
			<tr>
				<td>
					<?php echo $this->php_info;?>
				</td>
			</tr>
		</tbody>
	</table>
</fieldset>
