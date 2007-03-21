<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Media
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from works
* licensed under the GNU General Public License or other free or open source
* software licenses. See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Media Manager Views
 *
 * @static
 * @package		Joomla
 * @subpackage	Media
 * @since 1.5
 */
class MediaViews
{
	function imgManager($dirPath, $folder)
	{
		global $mainframe;

		?>
		<form action="index.php" id="uploadForm" method="post" enctype="multipart/form-data">
			<div id="messages" style="display: none;">
				<span id="message"></span><img src="img/dots.gif" width="22" height="12" alt="..." />
			</div>
			<fieldset>
				<div style="float: left">
					<label for="folder"><?php echo JText::_('Directory') ?></label>
					<?php echo $dirPath; ?>
					<button type="button" id="upbutton" title="<?php echo JText::_('Directory Up') ?>"><?php echo JText::_('Up') ?></button>
				</div>
				<div style="float: right">
					<button type="button" onclick="document.imagemanager.onok();window.top.document.popup.hide();"><?php echo JText::_('Insert') ?></button>
					<button type="button" onclick="window.top.document.popup.hide();"><?php echo JText::_('Cancel') ?></button>
				</div>
			</fieldset>
			<iframe id="imageframe" name="imageframe" src="index.php?option=com_media&amp;task=imgManagerList&amp;tmpl=component&amp;folder=<?php echo $folder?>"></iframe>

			<fieldset>
				<table class="properties">
					<tr>
						<td><label for="f_url"><?php echo JText::_('Image URL') ?></label></td>
						<td><input type="text" id="f_url" value="" /></td>
						<td><label for="f_align"><?php echo JText::_('Align') ?></label></td>
						<td>
							<select size="1" id="f_align" title="Positioning of this image">
								<option value="" selected="selected"><?php echo JText::_('Not Set') ?></option>
								<option value="left"><?php echo JText::_('Left') ?></option>
								<option value="right"><?php echo JText::_('Right') ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<td><label for="f_alt"><?php echo JText::_('Image description') ?></label></td>
						<td><input type="text" id="f_alt" value="" /></td>
					</tr>
					<tr>
						<td><label for="f_title"><?php echo JText::_('Title') ?></label></td>
						<td><input type="text" id="f_title" value="" /></td>
						<td><label for="f_caption"><?php echo JText::_('Caption') ?></label></td>
						<td><input type="checkbox" id="f_caption" /></td>
					</tr>
				</table>
			</fieldset>
			<div id="uploadpanel">
				<h3 id="uploadtoggler" class="toggler title"><span><?php echo JText::_('Upload') ?></span></h3>
				<div id="uploadpane" class="content">
					<iframe src="index.php?option=com_media&amp;task=popupUpload&amp;tmpl=component" id="uploadview" name="uploadview" scrolling="no"></iframe>
				</div>
			</div>
			<input type="hidden" id="f_file" name="f_file" />
			<input type="hidden" id="tmpl" name="component" />
		</form>
		<?php
	}

	function imgManagerList($listFolder, $folders, $images)
	{
		if (count($images) > 0 || count($folders) > 0)
		{
			//now sort the folders and images by name.
			ksort($images);
			ksort($folders);

			?><div class="manager"><?php

			// Handle the folders
			if (count($folders)) {
				foreach ($folders as $folder => $folderName) {
					MediaViews::renderFolder('/' . $folderName, $folder, $listFolder);
				}
			}

			// Handle the images
			if (count($images)) {
				foreach ($images as $image => $imageDetails) {
					MediaViews::renderImage($imageDetails['file'], $image, $imageDetails['imgInfo'], $imageDetails['size'], $listFolder);
				}
			}

			?></div><?php
		}
		else
		{
			?>
			<table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0">
			<tr>
				<td>
					<div align="center" style="font-size:large;font-weight:bold;color:#CCCCCC;font-family: Helvetica, sans-serif;">
						<?php echo JText::_( 'No Images Found' ); ?>
					</div>
				</td>
			</tr>
			</table>
			<?php
		}
	}

	function renderImage($img, $file, $info, $size, $listdir)
	{
		$img_file	= basename($img);
		$img_url	= COM_MEDIA_BASEURL.$listdir.'/'.rawurlencode($img_file);
		$insert_url = '/'.rawurlencode($img_file);
		$filesize	= MediaHelper::parseSize($size);

		if (($info[0] > 70) || ($info[0] > 70)) {
			$img_dimensions = MediaHelper::imageResize($info[0], $info[1], 80);
		} else {
			$img_dimensions = 'width="' . $info[0] . '" height="' . $info[1] . '"';
		}

		?>
		<div class="item">
			<a href="javascript:window.parent.document.imagemanager = new window.parent.JImageManager(); window.parent.document.imagemanager.populateFields('<?php echo $insert_url;?>')">
				<img src="<?php echo $img_url; ?>" <?php echo $img_dimensions; ?> alt="<?php echo $file; ?> - <?php echo $filesize; ?>" />
				<span><?php echo $file; ?></span>
			</a>
		</div>
		<?php
	}

	function renderFolder($path, $dir, $listdir)
	{
		global $mainframe;
		$img_path	= $mainframe->isAdmin() ? $mainframe->getSiteURL() : JURI::base();
		$count		= MediaHelper::countFiles(COM_MEDIA_BASE.$listdir.$path);
		$num_files	= $count[0];
		$num_dir	= $count[1];

		if ($listdir == '/') {
			$listdir = '';
		}

		$link = 'index.php?option=com_media&amp;task=imgManagerList&amp;tmpl=component&amp;folder='.$listdir.$path;

		?>
		<div class="item">
			<a href="<?php echo $link; ?>">
				<img src="<?php echo $img_path; ?>administrator/components/com_media/images/folder.gif" width="80" height="80" alt="<?php echo $dir; ?>" />
				<span><?php echo $dir; ?></span>
			</a>
		</div>
		<?php
	}

	/**
	 * Method to display an error message if the working directory is not valid
	 *
	 * since 1.5
	 */
	function listError()
	{
		global $BASE_DIR, $BASE_ROOT;
		?>
		<table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0">
		<tr>
			<td>
				<div align="center" style="font-size:small;font-weight:bold;color:#CC0000;font-family: Helvetica, sans-serif;">
					<?php echo JText::_( 'Configuration Problem' ); ?>: &quot;<?php echo $BASE_DIR.$BASE_ROOT; ?>&quot; <?php echo JText::_( 'does not exist.' ); ?>
				</div>
			</td>
		</tr>
		</table>
		<?php
	}

	function popupUpload($dirPath, $msg)
	{
		?>
		<form method="post" action="index.php" enctype="multipart/form-data" name="adminForm">
		<div id="message"><?php echo $msg ?></div>
		<table>
		<tr>
			<td>
				<label for="upload"><?php echo  JText::_( 'Select File' ); ?></label>
				<input class="inputbox" name="upload" id="upload" type="file" size="45" />
				<input class="button" type="button" value="<?php echo  JText::_( 'Upload' ); ?>" name="fileupload" onclick="document.imageupload.onupload()" />
				<span>[ <?php echo  JText::_( 'Max size' ); ?> = <?php echo ini_get( 'post_max_size' );?> ]</span>
			</td>
		</tr>
		</table>

		<input type="hidden" name="tmpl" value="component" />
		<input type="hidden" name="dirPath" value="/<?php echo $dirPath ?>" />
		<input type="hidden" name="option" value="com_media" />
		<input type="hidden" name="task" value="popupUpload" />
		</form>
		<?php
	}

	function popupDirectory($basePath)
	{
		$imgFiles = mosFS::listFolders($basePath, '.', true, true);
		$folders = array ();
		$folders[] = JHTMLSelect::option('/');

		$len = strlen($basePath);
		foreach ($imgFiles as $file) {
			$folders[] = JHTMLSelect::option(str_replace('\\', '/', substr($file, $len)));
		}

		if (is_array($folders)) {
			sort($folders);
		}
		// create folder selectlist
		$dirPath = JHTMLSelect::genericList($folders, 'dirPath', 'class="inputbox" size="1"', 'value', 'text', '.');
		?>
		<form action="index.php" name="adminForm" method="post">

		<table id="toolbar">
		<tr>
			<td>
				<?php echo JAdminMenus::ImageCheck( 'module.png', '/administrator/images/', NULL, NULL, $_LANG->_( 'Upload a File' ), 'upload' ); ?>
			</td>
			<td class="title">
				<?php echo  JText::_( 'Create a Directory' ); ?>
			</td>
		</tr>
		</table>

		<table class="adminform">
		<tr>
			<td colspan="2">
				<?php echo JText::_( 'Directory Name' ); ?>
			<br/>
				<input class="inputbox" name="foldername" type="text" size="60" />
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<?php echo JText::_( 'Parent Directory' ); ?>: <?php echo $dirPath; ?>
			</td>
		</tr>
		<tr>
			<td>
				<input class="button" type="button" value="<?php echo JText::_( 'Create' ); ?>" onclick="submitbutton('newdir')" />
			</td>
			<td>
				<div align="right">
					<input class="button" type="button" value="<?php echo JText::_( 'Close' ); ?>" onclick="window.close();" align="right" />
				</div>
			</td>
		</tr>
		</table>

		<input type="hidden" name="option" value="com_media" />
		<input type="hidden" name="task" value="" />
		</form>
		<?php
	}
}
?>