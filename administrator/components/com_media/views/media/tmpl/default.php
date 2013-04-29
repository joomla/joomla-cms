<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_media
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;
$user = JFactory::getUser();
?>
<table width="100%">
	<tr valign="top">
		<td>
			<fieldset id="treeview">
				<legend><?php echo JText::_('COM_MEDIA_FOLDERS'); ?></legend>
				<div id="media-tree_tree"></div>
				<?php echo $this->loadTemplate('folders'); ?>
			</fieldset>
		</td>
		<td>
			<?php if (($user->authorise('core.create', 'com_media')) and $this->require_ftp): ?>
				<form action="index.php?option=com_media&amp;task=ftpValidate" name="ftpForm" id="ftpForm" method="post">
					<fieldset title="<?php echo JText::_('COM_MEDIA_DESCFTPTITLE'); ?>">
						<legend><?php echo JText::_('COM_MEDIA_DESCFTPTITLE'); ?></legend>
						<?php echo JText::_('COM_MEDIA_DESCFTP'); ?>
						<label for="username"><?php echo JText::_('JGLOBAL_USERNAME'); ?></label>
						<input type="text" id="username" name="username" class="inputbox" size="70" value="" />

						<label for="password"><?php echo JText::_('JGLOBAL_PASSWORD'); ?></label>
						<input type="password" id="password" name="password" class="inputbox" size="70" value="" />
					</fieldset>
				</form>
			<?php endif; ?>

			<form action="index.php?option=com_media" name="adminForm" id="mediamanager-form" method="post" enctype="multipart/form-data" >
				<input type="hidden" name="task" value="" />
				<input type="hidden" name="cb1" id="cb1" value="0" />
				<input class="update-folder" type="hidden" name="folder" id="folder" value="<?php echo $this->state->folder; ?>" />
			</form>

			<form action="index.php?option=com_media&amp;task=folder.create&amp;tmpl=<?php echo JRequest::getCmd('tmpl', 'index');?>" name="folderForm" id="folderForm" method="post">
				<fieldset id="folderview">
					<div class="view">
						<iframe src="index.php?option=com_media&amp;view=mediaList&amp;tmpl=component&amp;folder=<?php echo $this->state->folder;?>" id="folderframe" name="folderframe" width="100%" marginwidth="0" marginheight="0" scrolling="auto"></iframe>
					</div>
					<legend><?php echo JText::_('COM_MEDIA_FILES'); ?></legend>
					<div class="path">
					<?php if ($user->authorise('core.create', 'com_media')): ?>
						<input class="inputbox" type="text" id="folderpath" readonly="readonly" />
						<input class="inputbox" type="text" id="foldername" name="foldername"  />
						<input class="update-folder" type="hidden" name="folderbase" id="folderbase" value="<?php echo $this->state->folder; ?>" />
						<button type="submit"><?php echo JText::_('COM_MEDIA_CREATE_FOLDER'); ?></button>
					<?php endif; ?>
					</div>
					<?php echo JHtml::_('form.token'); ?>
				</fieldset>
			</form>

			<?php if ($user->authorise('core.create', 'com_media')):?>
			<!-- File Upload Form -->
			<form action="<?php echo JURI::base(); ?>index.php?option=com_media&amp;task=file.upload&amp;tmpl=component&amp;<?php echo $this->session->getName().'='.$this->session->getId(); ?>&amp;<?php echo JSession::getFormToken();?>=1&amp;format=html" id="uploadForm" name="uploadForm" method="post" enctype="multipart/form-data">
				<fieldset id="uploadform">
					<legend><?php echo $this->config->get('upload_maxsize')=='0' ? JText::_('COM_MEDIA_UPLOAD_FILES_NOLIMIT') : JText::sprintf('COM_MEDIA_UPLOAD_FILES', $this->config->get('upload_maxsize')); ?></legend>
					<fieldset id="upload-noflash" class="actions">
						<label for="upload-file" class="hidelabeltxt"><?php echo JText::_('COM_MEDIA_UPLOAD_FILE'); ?></label>
						<input type="file" id="upload-file" name="Filedata[]" multiple />
						<label for="upload-submit" class="hidelabeltxt"><?php echo JText::_('COM_MEDIA_START_UPLOAD'); ?></label>
						<input type="submit" id="upload-submit" value="<?php echo JText::_('COM_MEDIA_START_UPLOAD'); ?>"/>
					</fieldset>
					<input type="hidden" name="return-url" value="<?php echo base64_encode('index.php?option=com_media'); ?>" />
				</fieldset>
			</form>
			<?php endif;?>
		</td>
	</tr>
</table>
