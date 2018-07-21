<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

?>
<fieldset class="adminform">
	<legend><?php echo Text::_('COM_ADMIN_SYSTEM_INFORMATION'); ?></legend>
	<table class="table">
		<thead>
			<tr>
				<th scope="col" style="width:25%">
					<?php echo Text::_('COM_ADMIN_SETTING'); ?>
				</th>
				<th scope="col">
					<?php echo Text::_('COM_ADMIN_VALUE'); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="2">&#160;</td>
			</tr>
		</tfoot>
		<tbody>
			<tr>
				<th scope="row">
					<?php echo Text::_('COM_ADMIN_PHP_BUILT_ON'); ?>
				</th>
				<td>
					<?php echo $this->info['php']; ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo Text::_('COM_ADMIN_DATABASE_TYPE'); ?>
				</th>
				<td>
					<?php echo $this->info['dbserver']; ?>
				</td>
			</tr>			
			<tr>
				<th scope="row">
					<?php echo Text::_('COM_ADMIN_DATABASE_VERSION'); ?>
				</th>
				<td>
					<?php echo $this->info['dbversion']; ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo Text::_('COM_ADMIN_DATABASE_COLLATION'); ?>
				</th>
				<td>
					<?php echo $this->info['dbcollation']; ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo Text::_('COM_ADMIN_DATABASE_CONNECTION_COLLATION'); ?>
				</th>
				<td>
					<?php echo $this->info['dbconnectioncollation']; ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo Text::_('COM_ADMIN_PHP_VERSION'); ?>
				</th>
				<td>
					<?php echo $this->info['phpversion']; ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo Text::_('COM_ADMIN_WEB_SERVER'); ?>
				</th>
				<td>
					<?php echo HTMLHelper::_('system.server', $this->info['server']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo Text::_('COM_ADMIN_WEBSERVER_TO_PHP_INTERFACE'); ?>
				</th>
				<td>
					<?php echo $this->info['sapi_name']; ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo Text::_('COM_ADMIN_JOOMLA_VERSION'); ?>
				</th>
				<td>
					<?php echo $this->info['version']; ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo Text::_('COM_ADMIN_USER_AGENT'); ?>
				</th>
				<td>
					<?php echo htmlspecialchars($this->info['useragent'], ENT_COMPAT, 'UTF-8'); ?>
				</td>
			</tr>
		</tbody>
	</table>
</fieldset>
