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
JHtml::stylesheet('default.css', 'administrator/components/com_users/media/css/');

// Load the JavaScript behaviors.
JHtml::_('behavior.switcher');
JHtml::_('behavior.tooltip');
?>

<div id="comments-config">
	<fieldset>
		<div style="float: right">
			<button type="button" onclick="submitbutton('config.import');">
				<?php echo JText::_('JX_IMPORT');?>
			</button>
			<button type="button" onclick="window.location = 'index.php?option=com_users&amp;task=config.export';">
				<?php echo JText::_('JX_EXPORT');?>
			</button>
			<button type="button" onclick="window.location = '<?php echo JRoute::_('index.php?option=com_users&view=config&tmpl=component', false); ?>';">
				<?php echo JText::_('JX_CANCEL');?>
			</button>
		</div>
		<div class="configuration" >
			<?php echo JText::_('USERS_CONFIG_TOOLBAR_TITLE'); ?>
		</div>
	</fieldset>

	<fieldset>
		<legend><?php echo JText::_('JX_CONFIG_IMPORT_EXPORT_HELP'); ?></legend>
		<p><?php echo JText::_('JX_CONFIG_IMPORT_EXPORT_INSTRUCTIONS'); ?></p>
	</fieldset>

	<form action="index.php?option=com_users" method="post" name="adminForm" autocomplete="off" enctype="multipart/form-data">
		<fieldset>
			<legend><?php echo JText::_('JX_IMPORT'); ?></legend>

			<label for="import_file"><?php echo JText::_('JX_CONFIG_IMPORT_FROM_FILE'); ?></label><br />
			<input type="file" name="configFile" id="import_file" size="50" />

			<br /><br />

			<label for="import_string"><?php echo JText::_('JX_CONFIG_IMPORT_FROM_STRING'); ?></label><br />
			<textarea name="configString" rows="10" cols="50"></textarea>
		</fieldset>
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>