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
	function imgManagerList($listFolder, $folders, $images) 
	{
		JMediaViews::imageStyle($listFolder);

		if (count($images) > 0 || count($folders) > 0) {
			//now sort the folders and images by name.
			ksort($images);
			ksort($folders);

			JMediaViews::drawTableHeader();

			// Handle the folders
			if (count($folders)) {
				foreach ($folders as $folder => $folderName) {
					JMediaViews::showDir2('/' . $folderName, $folder, $listFolder);
				}
			}

			// Handle the images
			if (count($images)) {
				foreach ($images as $image => $imageDetails) {
					JMediaViews::showImage2($imageDetails['file'], $image, $imageDetails['imgInfo'], $imageDetails['size'], $listFolder);
				}
			}

			JMediaViews::drawTableFooter();
		} else {
			JMediaViews::drawNoResults();
		}
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

	function drawNoResults() 
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

	function drawTableHeader() 
	{
		mosCommonHTML::loadOverlib();
		?>
		<div class="manager">
		<?php
	}

	function drawTableFooter() 
	{
		?>
		</div>
		<?php
	}

	function showImage2($img, $file, $info, $size, $listdir)
	{
		$img_file	= basename($img);
		$img_url	= COM_MEDIA_BASEURL.$listdir.'/'.rawurlencode($img_file);
		$insert_url = $listdir.'/'.rawurlencode($img_file);
		$filesize	= JMediaViews::parseSize($size);

		if (($info[0] > 70) || ($info[0] > 70)) {
			$img_dimensions = JMediaViews::imageResize($info[0], $info[1], 80);
		} else {
			$img_dimensions = 'width="' . $info[0] . '" height="' . $info[1] . '"';
		}

		$overlib = '<table>';
		$overlib .= '<tr>';
		$overlib .= '<td>';
		$overlib .= JText::_('Width');
		$overlib .= '</td>';
		$overlib .= '<td>';
		$overlib .= $info[0] . JText::_('px');
		$overlib .= '</td>';
		$overlib .= '</tr>';
		$overlib .= '<tr>';
		$overlib .= '<td>';
		$overlib .= JText::_('Height');
		$overlib .= '</td>';
		$overlib .= '<td>';
		$overlib .= $info[1] . JText::_('px');
		$overlib .= '</td>';
		$overlib .= '</tr>';
		$overlib .= '<tr>';
		$overlib .= '<td>';
		$overlib .= JText::_('Filesize');
		$overlib .= '</td>';
		$overlib .= '<td>';
		$overlib .= $filesize;
		$overlib .= '</td>';
		$overlib .= '</tr>';
		$overlib .= '</table>';
		$overlib .= '<br/> ' . JText::_('*Click for Image Code*');
		?>
		<div style="float:left; padding: 5px">
			<div class="imgTotal"  onmouseover="return overlib( '<?php echo $overlib; ?>', CAPTION, '<?php echo addslashes( $file ); ?>', BELOW, LEFT, WIDTH, 150 );" onmouseout="return nd();">
				<div align="center" class="imgBorder">
					<a onclick="javascript: window.parent.populateFields('<?php echo $insert_url;?>')" style="display: block; width: 100%; height: 100%">
						<div class="image">
							<img src="<?php echo $img_url; ?>" <?php echo $img_dimensions; ?> alt="<?php echo $file; ?> - <?php echo $filesize; ?>" border="0" />
						</div></a>
				</div>
			</div>
		</div>
		<?php
	}

	function showDir2($path, $dir, $listdir) 
	{
		$count		= JMediaViews::numFiles(COM_MEDIA_BASE.$listdir.$path);
		$num_files	= $count[0];
		$num_dir	= $count[1];

		if ($listdir == '/') {
			$listdir = '';
		}

		$link = 'index.php?option=com_media&amp;task=list&amp;tmpl=component.html&amp;listdir='.$listdir.$path;

		$overlib = '<table>';
		$overlib .= '<tr>';
		$overlib .= '<td>';
		$overlib .= JText::_('NUMFILES');
		$overlib .= '</td>';
		$overlib .= '<td>';
		$overlib .= $num_files;
		$overlib .= '</td>';
		$overlib .= '</tr>';
		$overlib .= '<tr>';
		$overlib .= '<td>';
		$overlib .= JText::_('NUMFOLDERS');
		$overlib .= '</td>';
		$overlib .= '<td>';
		$overlib .= $num_dir;
		$overlib .= '</td>';
		$overlib .= '</tr>';
		$overlib .= '</table>';
		$overlib .= '<br/>' . JText :: _('*Click to Open*');
		?>
		<div style="float:left; padding: 5px">
			<div class="imgTotal" onmouseover="return overlib( '<?php echo $overlib; ?>', CAPTION, '<?php echo $dir; ?>', BELOW, RIGHT, WIDTH, 150 );" onmouseout="return nd();">
				<div align="center" class="imgBorder">
					<a href="<?php echo $link; ?>" target="imgManager" onclick="javascript:window.parent.updateDir();">
						<img src="components/com_media/images/folder.gif" width="80" height="80" border="0" alt="<?php echo $dir; ?>" /></a>
				</div>
			</div>
		</div>
		<?php
	}

	function parseSize($size) 
	{
		if ($size < 1024) {
			return $size . ' bytes';
		} else
			if ($size >= 1024 && $size < 1024 * 1024) {
				return sprintf('%01.2f', $size / 1024.0) . ' Kb';
			} else {
				return sprintf('%01.2f', $size / (1024.0 * 1024)) . ' Mb';
			}
	}

	function imageResize($width, $height, $target) 
	{
		//takes the larger size of the width and height and applies the
		//formula accordingly...this is so this script will work
		//dynamically with any size image
		if ($width > $height) {
			$percentage = ($target / $width);
		} else {
			$percentage = ($target / $height);
		}

		//gets the new value and applies the percentage, then rounds the value
		$width = round($width * $percentage);
		$height = round($height * $percentage);

		//returns the new sizes in html image tag format...this is so you
		//can plug this function inside an image tag and just get the

		return "width=\"$width\" height=\"$height\"";
	}

	function numFiles($dir) 
	{
		$total_file = 0;
		$total_dir = 0;

		if (is_dir($dir)) {
			$d = dir($dir);

			while (false !== ($entry = $d->read())) {
				if (substr($entry, 0, 1) != '.' && is_file($dir . DIRECTORY_SEPARATOR . $entry) && strpos($entry, '.html') === false && strpos($entry, '.php') === false) {
					$total_file++;
				}
				if (substr($entry, 0, 1) != '.' && is_dir($dir . DIRECTORY_SEPARATOR . $entry)) {
					$total_dir++;
				}
			}

			$d->close();
		}

		return array (
			$total_file,
			$total_dir
		);
	}

	function imageStyle($listdir) 
	{
		?>
		<script language="javascript" type="text/javascript">
		function updateDir(){
			var allPaths = window.top.document.forms[0].dirPath.options;
			for(i=0; i<allPaths.length; i++) {
				allPaths.item(i).selected = false;
				if((allPaths.item(i).value)== '<?php if (strlen($listdir)>0) { echo $listdir ;} else { echo '/';}  ?>') {
					allPaths.item(i).selected = true;
				}
			}
		}

		function deleteImage(file) {
			if(confirm("<?php echo JText::_( 'Delete file' ); ?> \""+file+"\"?"))
			return true;

			return false;
		}
		function deleteFolder(folder, numFiles) {
			if(numFiles > 0) {
				alert("<?php echo JText::_( 'There are', true ); ?> "+numFiles+" <?php echo JText::_( 'files/folders in' ); ?> \""+folder+"\".\n\n<?php echo JText::_( 'Please delete all files/folder in' ); ?> \""+folder+"\" <?php echo JText::_( 'first.' ); ?>");
				return false;
			}

			if(confirm("<?php echo JText::_( 'Delete folder', true ); ?> \""+folder+"\"?"))
			return true;

			return false;
		}
		</script>
		</head>
		<body onload="updateDir()">
		<style type="text/css">
		<!--
		div.imgTotal {
			border-top: 1px solid #ccc;
			border-left: 1px solid #ccc;
			border-right: 1px solid #ccc;
		}
		div.imgBorder {
			height: 70px;
			vertical-align: middle;
			width: 88px;
			overflow: hidden;
		}
		div.imgBorder a {
			height: 70px;
			width: 88px;
			display: block;
		}
		div.imgBorder a:hover {
			height: 70px;
			width: 88px;
			background-color: #f1e8e6;
			color : #FF6600;
		}
		.imgBorderHover {
			background: #FFFFCC;
			cursor: hand;
		}
		div.imginfoBorder {
			background: #f6f6f6;
			width: 84px !important;
			width: 90px;
			height: 35px;
			vertical-align: middle;
			padding: 2px;
			overflow: hidden;
			border: 1px solid #ccc;
			font-family: Arial, Helvetica, sans-serif;
			font-size: 11px;
		}

		div.imgBorder a {
			cursor: pointer;
		}

		.buttonHover {
			border: 1px solid;
			border-color: ButtonHighlight ButtonShadow ButtonShadow ButtonHighlight;
			cursor: hand;
			background: #FFFFCC;
		}

		.buttonOut {
		 	border: 0px;
		}

		.imgCaption {
			font-size: 9pt;
			text-align: center;
		}
		.dirField {
			font-size: 9pt;
			width:110px;
		}
		div.image {
			padding-top: 10px;
		}
		-->
		</style>
		<?php
	}

	function imgManager($dirPath, $listFolder) 
	{
		global $mainframe;
		
		JMediaViews::_loadImgManagerJS();
		
		?>
		<form action="index.php&amp;tmpl=component.html" id="uploadForm" method="post" enctype="multipart/form-data">
		<fieldset><legend>Image Manager</legend>
		<div class="dirs">
			<label for="dirPath">Directory</label>
			<?php echo $dirPath; ?>
			<a onclick="javascript: goUpDir();" title="Directory Up"><img src="img/btnFolderUp.gif" height="15" width="15" alt="Directory Up" /></a>
			<div id="messages" style="display: none;"><span id="message"></span><img SRC="img/dots.gif" width="22" height="12" alt="..." /></div>
			<iframe src="index.php?option=com_media&amp;task=imgManagerList&amp;listdir=<?php echo $listFolder?>&amp;tmpl=component.html" name="imgManager" id="imgManager" width="100%" marginwidth="0" marginheight="0" style="overflow-x: false;" scrolling="auto" frameborder="0"></iframe>
		</div>
		</fieldset>
		<!-- image properties -->
			<table class="inputTable">
				<tr>
					<td align="right">
						<label for="f_url">Image File</label>
					</td>
					<td>
						<input type="text" id="f_url" class="largelWidth" value="" />
					</td>
					<td rowspan="3" align="right">
						&nbsp;
					</td>
					<td align="right">
						<label for="f_width">Width</label>
					</td>
					<td>
						<input type="text" id="f_width" class="smallWidth" value="" onchange="javascript:checkConstrains('width');"/>
					</td>
					<td rowspan="2" align="right">
						<img src="img/locked.gif" id="imgLock" width="25" height="32" alt="Constrained Proportions" />
					</td>
					<td rowspan="3" align="right">
						&nbsp;
					</td>
					<td align="right">
						<label for="f_vert">V Space</label>
					</td>
					<td>
						<input type="text" id="f_vert" class="smallWidth" value="" />
					</td>
				</tr>		
				<tr>
					<td align="right">
						<label for="f_alt">Alt</label>
					</td>
					<td>
						<input type="text" id="f_alt" class="largelWidth" value="" />
					</td>
					<td align="right">
						<label for="f_height">Height</label>
					</td>
					<td>
						<input type="text" id="f_height" class="smallWidth" value="" onchange="javascript:checkConstrains('height');"/>
					</td>
					<td align="right">
						<label for="f_horiz">H Space</label>
					</td>
					<td>
						<input type="text" id="f_horiz" class="smallWidth" value="" />
					</td>
				</tr>
				<tr>
					<td align="right"><label for="f_align">Align</label></td>
					<td colspan="2">
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
					<td align="right">
						<label for="f_border">Border</label>
					</td>
					<td>
						<input type="text" id="f_border" class="smallWidth" value="" />
					</td>
				</tr>
				<tr>
					<td colspan="4" align="right">
						<input type="hidden" id="orginal_width" />
						<input type="hidden" id="orginal_height" />
						<input type="checkbox" id="constrain_prop" checked="checked" onclick="javascript:toggleConstrains(this);" />
					</td>
					<td colspan="5">
						<label for="constrain_prop">Constrain Proportions</label>
					</td>
				</tr>
			</table>
		<!--// image properties -->	
			<div style="text-align: right;"> 
				<hr />
				<button type="button" class="buttons" onclick="onOK();window.top.document.popup.hide();">OK</button>
				<button type="button" class="buttons" onclick="window.top.document.popup.hide();">Cancel</button>
		    </div>
			<input type="hidden" id="f_file" name="f_file" />
		</form>
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

	function _loadImgManagerJS()
	{
		global $mainframe;
		
		$url = ($mainframe->isAdmin()) ? $mainframe->getSiteURL() : $mainframe->getBaseURL();
		$js = "
		var base_url = '".$url."';	
			
		function dirup(){
			var urlquery=frames['imgManager'].location.search.substring(1);
			var curdir= urlquery.substring(urlquery.indexOf('listdir=')+8);
			var listdir=curdir.substring(0,curdir.lastIndexOf('/'));
			frames['imgManager'].location.href='index.php?option=com_media&task=imgManagerList&tmpl=component.html&listdir=' + listdir;
		}

		function dirup(){
			var urlquery=frames['imgManager'].location.search.substring(1);
			var curdir= urlquery.substring(urlquery.indexOf('listdir=')+8);
			var listdir=curdir.substring(0,curdir.lastIndexOf('/'));
			frames['imgManager'].location.href='index.php?option=com_media&task=imgManagerList&tmpl=component.html&listdir=' + listdir;
		}

		function goUpDir() {
			var selection = document.forms[0].dirPath;
			var dir = selection.options[selection.selectedIndex].value;
			frames['imgManager'].location.href='index.php?option=com_media&task=imgManagerList&tmpl=component.html&listdir=' + dir;
		}";
		$doc =& $mainframe->getDocument();
		$doc->addScriptDeclaration($js);
	}
}
?>