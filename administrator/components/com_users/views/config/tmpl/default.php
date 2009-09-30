<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Add the component HTML helper path.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::addIncludePath(JPATH_PLUGINS.'/system/jxtended/html/html');

// Load the stylesheets.
JHtml::stylesheet('system.css', 'administrator/templates/system/css/');

// Load the JavaScript behaviors.
JHtml::_('behavior.switcher');
JHtml::_('behavior.tooltip');
?>

<div id="jx-config">
	<fieldset>
		<div style="float: right">
			<button type="button" onclick="submitbutton('config.save');">
				<?php echo JText::_('JX_SAVE');?>
			</button>
			<button type="button" onclick="window.parent.document.getElementById('sbox-window').close();">
				<?php echo JText::_('JX_CANCEL');?>
			</button>
			<button type="button" onclick="window.location = '<?php echo JRoute::_('index.php?option=com_users&amp;view=config&layout=import&tmpl=component', false); ?>';">
					<?php echo JText::_('JX_CONFIG_IMPORT_EXPORT');?>
			</button>
		</div>
		<div class="configuration" >
			<?php echo JText::_('USERS_CONFIG_TOOLBAR_TITLE'); ?>
		</div>
	</fieldset>

	<div id="submenu-box">
		<div class="t">
			<div class="t">
				<div class="t"></div>
	 		</div>
		</div>
		<div class="m">
			<ul id="submenu">
				<li><a id="setup" class="active"><?php echo JText::_('USERS_CONFIG_SECTION_SETUP'); ?></a></li>
				<li><a id="permissions"><?php echo JText::_('USERS_CONFIG_SECTION_PERMISSIONS'); ?></a></li>
			</ul>
			<div class="clr"></div>
		</div>
		<div class="b">
			<div class="b">
	 			<div class="b"></div>
			</div>
		</div>
	</div>

	<form action="index.php?option=com_users" method="post" name="adminForm" autocomplete="off">
		<div id="config-document">
			<div id="page-setup">
				<fieldset>
					<legend><?php echo JText::_('USERS_CONFIG_SECTION_SETUP'); ?></legend>
					<?php echo JHtml::_('config.params', 'params', $this->config->toString(), 'models/forms/config/setup.xml'); ?>
				</fieldset>
			</div>

			<div id="page-permissions">
				<fieldset>
					<legend><?php echo JText::_('USERS_CONFIG_SECTION_PERMISSIONS'); ?></legend>
					<?php echo JHtml::_('config.params', 'params', $this->config->toString(), 'models/forms/config/permissions.xml'); ?>
				</fieldset>
			</div>
		</div>
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>