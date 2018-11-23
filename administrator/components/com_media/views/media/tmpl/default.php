<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$user  = JFactory::getUser();
$input = JFactory::getApplication()->input;
$lang  = JFactory::getLanguage();
$style = JFactory::getApplication()->getUserStateFromRequest('media.list.layout', 'layout', 'thumbs', 'word');

if (DIRECTORY_SEPARATOR == '\\')
{
	$base = str_replace(DIRECTORY_SEPARATOR, "\\\\", COM_MEDIA_BASE);
}
else
{
	$base = COM_MEDIA_BASE;
}

JFactory::getDocument()->addScriptDeclaration(
	"
		var basepath = '" . $base . "';
		var viewstyle = '" . $style . "';
	"
);

JHtml::_('behavior.keepalive');
JHtml::_('bootstrap.framework');
JHtml::_('script', 'media/mediamanager.min.js', array('version' => 'auto', 'relative' => true));
JHtml::_('script', 'media/mediaelement-and-player.js', array('version' => 'auto', 'relative' => true));
JHtml::_('stylesheet', 'media/mediaelementplayer.css', array('version' => 'auto', 'relative' => true));
JHtml::_('stylesheet', 'system/mootree.css', array('version' => 'auto', 'relative' => true));

if ($lang->isRtl())
{
	JHtml::_('stylesheet', 'system/mootree_rtl.css', array('version' => 'auto', 'relative' => true));
}
?>
<div class="row-fluid">
	<!-- Begin Sidebar -->
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
		<div class="j-toggle-sidebar-header">
		<h3><?php echo JText::_('COM_MEDIA_FOLDERS'); ?> </h3>
		</div>
		<div id="treeview" class="sidebar">
			<div id="media-tree_tree" class="tree-holder">
				<?php echo $this->loadTemplate('folders'); ?>
			</div>
		</div>
	</div>
	<!-- End Sidebar -->

	<!-- Begin Content -->
	<div id="j-main-container" class="span10">
		<?php echo $this->loadTemplate('navigation'); ?>
		<?php if (($user->authorise('core.create', 'com_media')) and $this->require_ftp) : ?>
			<form action="index.php?option=com_media&amp;task=ftpValidate" name="ftpForm" id="ftpForm" method="post">
				<fieldset title="<?php echo JText::_('COM_MEDIA_DESCFTPTITLE'); ?>">
					<legend><?php echo JText::_('COM_MEDIA_DESCFTPTITLE'); ?></legend>
					<?php echo JText::_('COM_MEDIA_DESCFTP'); ?>
					<label for="username"><?php echo JText::_('JGLOBAL_USERNAME'); ?></label>
					<input type="text" id="username" name="username" size="70" value="" />

					<label for="password"><?php echo JText::_('JGLOBAL_PASSWORD'); ?></label>
					<input type="password" id="password" name="password" size="70" value="" />
				</fieldset>
			</form>
		<?php endif; ?>

		<form action="index.php?option=com_media" name="adminForm" id="mediamanager-form" method="post" enctype="multipart/form-data" >
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="cb1" id="cb1" value="0" />
			<input class="update-folder" type="hidden" name="folder" id="folder" value="<?php echo $this->escape($this->state->folder); ?>" />
		</form>

		<?php if ($user->authorise('core.create', 'com_media')) : ?>
		<!-- File Upload Form -->
		<div id="collapseUpload" class="collapse">
			<form action="<?php echo JUri::base(); ?>index.php?option=com_media&amp;task=file.upload&amp;tmpl=component&amp;<?php echo $this->session->getName() . '=' . $this->session->getId(); ?>&amp;<?php echo JSession::getFormToken(); ?>=1&amp;format=html" id="uploadForm" class="form-inline" name="uploadForm" method="post" enctype="multipart/form-data">
				<div id="uploadform" class="uploadform">
					<fieldset id="upload-noflash" class="actions">
							<label for="upload-file" class="control-label"><?php echo JText::_('COM_MEDIA_UPLOAD_FILE'); ?></label>
								<input required type="file" id="upload-file" name="Filedata[]" multiple /> <button class="btn btn-primary" id="upload-submit"><span class="icon-upload icon-white"></span> <?php echo JText::_('COM_MEDIA_START_UPLOAD'); ?></button>
							<p class="help-block">
								<?php $cMax    = (int) $this->config->get('upload_maxsize'); ?>
								<?php $maxSize = JUtility::getMaxUploadSize($cMax . 'MB'); ?>
								<?php echo JText::sprintf('JGLOBAL_MAXIMUM_UPLOAD_SIZE_LIMIT', JHtml::_('number.bytes', $maxSize)); ?>
							</p>
					</fieldset>
					<input class="update-folder" type="hidden" name="folder" id="folder" value="<?php echo $this->escape($this->state->folder); ?>" />
					<?php JFactory::getSession()->set('com_media.return_url', 'index.php?option=com_media'); ?>
				</div>
			</form>
		</div>
		<div id="collapseFolder" class="collapse">
			<form action="index.php?option=com_media&amp;task=folder.create&amp;tmpl=<?php echo $input->getCmd('tmpl', 'index'); ?>" name="folderForm" id="folderForm" class="form-inline" method="post">
					<div class="path">
						<input type="text" id="folderpath" readonly="readonly" class="update-folder" />
						<input required type="text" id="foldername" name="foldername" />
						<input class="update-folder" type="hidden" name="folderbase" id="folderbase" value="<?php echo $this->escape($this->state->folder); ?>" />
						<button type="submit" class="btn"><span class="icon-folder-open"></span> <?php echo JText::_('COM_MEDIA_CREATE_FOLDER'); ?></button>
					</div>
					<?php echo JHtml::_('form.token'); ?>
			</form>
		</div>
		<?php endif; ?>

		<form action="index.php?option=com_media&amp;task=folder.create&amp;tmpl=<?php echo $input->getCmd('tmpl', 'index'); ?>" name="folderForm" id="folderForm" method="post">
			<div id="folderview">
				<div class="view">
					<iframe class="thumbnail" src="index.php?option=com_media&amp;view=mediaList&amp;tmpl=component&amp;folder=<?php echo $this->escape($this->state->folder); ?>" id="folderframe" name="folderframe" width="100%" height="500px" marginwidth="0" marginheight="0" scrolling="auto"></iframe>
				</div>
				<?php echo JHtml::_('form.token'); ?>
			</div>
		</form>
	</div>
<?php // Pre render all the bootstrap modals on the parent window

echo JHtml::_(
	'bootstrap.renderModal',
	'imagePreview',
	array(
		'title'  => JText::_('COM_MEDIA_PREVIEW'),
		'footer' => '<a type="button" class="btn" data-dismiss="modal" aria-hidden="true">'
			. JText::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</a>',
	),
	'<div id="image" style="text-align:center;"><img id="imagePreviewSrc" src="../media/jui/img/alpha.png" alt="preview" style="max-width:100%; max-height:300px;"/></div>'
);

echo JHtml::_(
	'bootstrap.renderModal',
	'videoPreview',
	array(
		'title'  => JText::_('COM_MEDIA_PREVIEW'),
		'footer' => '<a type="button" class="btn" data-dismiss="modal" aria-hidden="true">'
			. JText::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</a>',
	),
	'<div id="videoPlayer" style="z-index: -100;"><video id="mejsPlayer" style="height: 250px;"/></div>'
);
?>
	<!-- End Content -->
</div>
