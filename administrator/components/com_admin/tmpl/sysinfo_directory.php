<?php
/**
 * @version		$Id$
 */
// No direct access
defined('_JEXEC') or die;

jimport('joomla.filesystem.folder');
$cparams = JComponentHelper::getParams ('com_media');
$config = &JFactory::getConfig();
?>
<fieldset class="adminform">
	<legend><?php echo JText::_('Directory Permissions'); ?></legend>
		<table class="adminlist">
		<thead>
			<tr>
				<th width="650">
					<?php echo JText::_('Directory'); ?>
				</th>
				<th>
					<?php echo JText::_('Status'); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="2">
					&nbsp;
				</td>
			</tr>
		</tfoot>
		<tbody>
			<?php
			writableCell('administrator/backups');
			writableCell('administrator/components');
			writableCell('administrator/language');

			// List all admin languages
			$admin_langs = JFolder::folders(JPATH_ADMINISTRATOR.DS.'language');
			foreach ($admin_langs as $alang)
			{
				writableCell('administrator/language/'.$alang);
			}

			writableCell('administrator/modules');
			writableCell('administrator/templates');
			writableCell('components');
			writableCell('images');
			writableCell('images/banners');
			writableCell($cparams->get('image_path'));
			writableCell('language');

			// List all site languages
			$site_langs	= JFolder::folders(JPATH_SITE.DS.'language');
			foreach ($site_langs as $slang)
			{
				writableCell('language/'.$slang);
			}

			writableCell('media');
			writableCell('modules');
			writableCell('plugins');
			writableCell('plugins/content');
			writableCell('plugins/editors');
			writableCell('plugins/editors-xtd');
			writableCell('plugins/search');
			writableCell('plugins/system');
			writableCell('plugins/user');
			writableCell('plugins/xmlrpc');
			writableCell('templates');
			writableCell(JPATH_SITE.DS.'cache', 0, '<strong>'. JText::_('Cache Directory') .'</strong> ');
			writableCell(JPATH_ADMINISTRATOR.DS.'cache', 0, '<strong>'. JText::_('Cache Directory') .'</strong> ');
			writableCell($config->getValue('config.log_path', JPATH_ROOT.DS.'log'),0, '<strong>'. JText::_('Log Directory') .
							' ($log_path)</strong> ');
			writableCell($config->getValue('config.tmp_path', JPATH_ROOT.DS.'tmp'),0, '<strong>'. JText::_('Temp Directory') .
							' ($tmp_path)</strong> ');
			?>
		</tbody>
		</table>
</fieldset>
