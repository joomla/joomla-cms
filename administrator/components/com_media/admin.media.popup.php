<?php
/**
* @version $Id: admin.media.html.php 3402 2006-05-06 02:38:08Z webImagery $
* @package Joomla
* @subpackage Massmail
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
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
 * @package Joomla
 * @subpackage Media
 * @since 1.5
 */
class JMediaViews 
{
	function imgManager($dirPath, $listFolder) 
	{
		global $mainframe;
		
		?>
		<form action="index.php" id="uploadForm" method="post" enctype="multipart/form-data">
		<div id="messages" style="display: none;"><span id="message"></span><img src="img/dots.gif" width="22" height="12" alt="..." /></div>
		<fieldset>
			<div style="float: left">
				<label for="folder">Directory</label>
				<?php echo $dirPath; ?>
				<button onclick="document.imagemanager.upFolder()" title="Directory Up">Up</button>
			</div>
			<div style="float: right">
				<button type="button" class="buttons" onclick="document.imagemanager.onok();window.top.document.popup.hide();">OK</button>
				<button type="button" class="buttons" onclick="window.top.document.popup.hide();">Cancel</button>
		    </div>
		</fieldset>
		<iframe src="index.php?option=com_media&amp;task=imgManagerList&amp;listdir=<?php echo $listFolder?>&amp;tmpl=component.html" id="imageview"></iframe>
		<fieldset>
		<table class="properties">
			<tr>
				<td>
					<label for="f_url">Image Path</label>
				</td>
				<td>
					<input type="text" id="f_url" value="" />
				</td>
				<td><label for="f_align">Align</label></td>
				<td>
					<select size="1" id="f_align"  title="Positioning of this image">
						<option value=""                             >Not Set</option>
						<option value="left"                         >Left</option>
						<option value="right"                        >Right</option>
						<option value="texttop"                      >Texttop</option>
						<option value="absmiddle"                    >Absmiddle</option>
						<option value="baseline" selected="selected" >Baseline</option>
						<option value="absbottom"                    >Absbottom</option>
						<option value="bottom"                       >Bottom</option>
						<option value="middle"                       >Middle</option>
						<option value="top"                          >Top</option>
					</select>
				</td>
			</tr>		
			<tr>
				<td>
					<label for="f_alt">Image Description</label>
				</td>
				<td>
					<input type="text" id="f_alt" value="" />
				</td>
			</tr>
			</table>
			</fieldset>
			<input type="hidden" id="f_file" name="f_file" />
			<input type="hidden" id="tmpl" name="component.html" />
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
					JMediaViews::renderFolder('/' . $folderName, $folder, $listFolder);
				}
			}

			// Handle the images
			if (count($images)) {
				foreach ($images as $image => $imageDetails) {
					JMediaViews::renderImage($imageDetails['file'], $image, $imageDetails['imgInfo'], $imageDetails['size'], $listFolder);
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
		$insert_url = $listdir.'/'.rawurlencode($img_file);
		$filesize	= JMediaHelper::parseSize($size);

		if (($info[0] > 70) || ($info[0] > 70)) {
			$img_dimensions = JMediaHelper::imageResize($info[0], $info[1], 80);
		} else {
			$img_dimensions = 'width="' . $info[0] . '" height="' . $info[1] . '"';
		}

		?>
		<div class="item">
			<a href="javascript::return(void)" onclick="javascript:window.parent.document.imagemanager.populateFields('<?php echo $insert_url;?>')">
				<img src="<?php echo $img_url; ?>" <?php echo $img_dimensions; ?> alt="<?php echo $file; ?> - <?php echo $filesize; ?>" />
				<span><?php echo $file; ?></span>
			</a>
		</div>
		<?php
	}

	function renderFolder($path, $dir, $listdir) 
	{
		$count		= JMediaHelper::countFiles(COM_MEDIA_BASE.$listdir.$path);
		$num_files	= $count[0];
		$num_dir	= $count[1];

		if ($listdir == '/') {
			$listdir = '';
		}

		$link = 'index.php?option=com_media&amp;task=imgManagerList&amp;tmpl=component.html&amp;folder='.$listdir.$path;

		?>
		<div class="item">
			<a href="<?php echo $link; ?>" onclick="javascript:window.parent.document.imagemanager.setFolder('<?php echo $listdir.$path ?>');">
				<img src="components/com_media/images/folder.gif" width="80" height="80" alt="<?php echo $dir; ?>" />
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
	
	function popupUpload($basePath) 
	{
		global $mosConfig_absolute_path;

		jimport('joomla.filesystem.folder');
		$imgFiles = JFolder::folders($basePath, '.', true, true);
		$folders = array ();
		$folders[] = mosHTML::makeOption('/');

		$len = strlen($basePath);
		foreach ($imgFiles as $file) {
			$folders[] = mosHTML::makeOption(str_replace('\\', '/', substr($file, $len)));
		}

		if (is_array($folders)) {
			sort($folders);
		}
		// create folder selectlist
		$dirPath = mosHTML::selectList($folders, 'dirPath', 'class="inputbox" size="1" ', 'value', 'text', '.');
		?>
		<form method="post" action="index3.php" enctype="multipart/form-data" name="adminForm">
		
		<fieldset>
			<legend><?php echo JText::_( 'Upload a File' ); ?></legend>

		<table class="admintable" cellspacing="1">
		<tr>
			<td class="key"><?php echo  JText::_( 'Select File' ); ?><br />
			[ <?php echo  JText::_( 'Max size' ); ?> = <?php echo ini_get( 'post_max_size' );?> ]
			</td>
			<td>
				
				<input class="inputbox" name="upload" type="file" size="70" />
			</td>
		</tr>
		<tr>
			<td class="key"><?php echo  JText::_( 'Destination Sub-folder' ); ?></td>
			<td><?php echo $dirPath; ?></td>
		</tr>
		<tr>
			<td class="key">&nbsp;</td>
			<td>

				<input class="button" type="button" value="<?php echo  JText::_( 'Upload' ); ?>" name="fileupload" onclick="javascript:submitbutton('upload')" />&nbsp;&nbsp;&nbsp;
				<input class="button" type="button" value="<?php echo  JText::_( 'Close' ); ?>" onclick="javascript:window.close();" align="right" />
			</td>
		</tr>
		</table>
		</fieldset>

		<input type="hidden" name="option" value="com_media" />
		<input type="hidden" name="task" value="popupUpload" />
		</form>
		<?php
	}

	function popupDirectory($basePath) 
	{

		$imgFiles = mosFS::listFolders($basePath, '.', true, true);
		$folders = array ();
		$folders[] = mosHTML::makeOption('/');

		$len = strlen($basePath);
		foreach ($imgFiles as $file) {
			$folders[] = mosHTML::makeOption(str_replace('\\', '/', substr($file, $len)));
		}

		if (is_array($folders)) {
			sort($folders);
		}
		// create folder selectlist
		$dirPath = mosHTML::selectList($folders, 'dirPath', 'class="inputbox" size="1"', 'value', 'text', '.');
		?>
		<form action="index2.php" name="adminForm" method="post">

		<table id="toolbar">
		<tr>
			<td>
				<?php echo mosAdminMenus::ImageCheck( 'module.png', '/administrator/images/', NULL, NULL, $_LANG->_( 'Upload a File' ), 'upload' ); ?>
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
				<input class="button" type="button" value="<?php echo JText::_( 'Create' ); ?>" onclick="javascript:submitbutton('newdir')" />
			</td>
			<td>
				<div align="right">
					<input class="button" type="button" value="<?php echo JText::_( 'Close' ); ?>" onclick="javascript:window.close();" align="right" />
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