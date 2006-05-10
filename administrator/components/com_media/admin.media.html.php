<?php
/**
* @version $Id$
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
	/**
	 * Method to show the standard Media Manager view
	 *
	 * @param string $dirPath The current path select box
	 * @param string $listdir The current working directory
	 * @since 1.0
	 */
	function showMedia($dirPath, $current, $tree) 
	{
		global $mainframe;

		JMediaViews::_loadJS();
		$style = $mainframe->getUserStateFromRequest('media.list.style', 'listStyle', 'thumbs');
		$styles[] = mosHTML::makeOption('thumbs', 'Thumbs');
		$styles[] = mosHTML::makeOption('list', 'List');
		$listStyle = mosHTML::selectList($styles, 'listStyle', 'onchange="setStyle();"', 'value', 'text', $style )		
		?>
		<form action="index.php" name="adminForm" method="post" enctype="multipart/form-data" >

		<table>
		<tr>
			<td align="right" width="20%" style="padding-right:10px;white-space:nowrap">
				<label for="foldername">
					<?php echo JText::_( 'Create Directory' ); ?>
				</label>
				<input class="inputbox" type="text" name="foldername" id="foldername" style="width: 150px" />
			</td>
			<td align="right" width="80%" style="padding-right:10px;white-space:nowrap">
				<label for="imagecode">
					<?php echo JText::_( 'Image/Url Code' ); ?>
				</label>
				<input class="inputbox" type="text" name="imagecode" id="imagecode" style="width: 400px" />
			</td>
		</tr>
		</table>

		<div id="tablecell">
			<table width="100%" align="center">
			<tr>
				<td align="center">
					<fieldset>
						<table width="99%" align="center" border="0" cellspacing="2" cellpadding="2">
						<tr>
							<td>
								<table border="0" cellspacing="1" cellpadding="3"  class="adminheading">
								<tr>
									<td class="buttonOut" width="10">
										<a href="javascript:dirup()">
											<img src="components/com_media/images/btnFolderUp.gif" width="15" height="15" border="0" alt="<?php echo JText::_( 'Up' ); ?>" />
										</a>
									</td>
									<td class="buttonOut" width="10">
										<?php echo $listStyle; ?>
									</td>
									<td align="right">
										<label for="uploadfile">
											<?php echo JText::_( 'File Upload' ); ?>
										</label>
										<small>[ <?php echo JText::_( 'Max' ); ?>&nbsp;<?php echo ini_get( 'post_max_size' );?> ]</small>
										&nbsp;&nbsp;&nbsp;&nbsp;
										<input class="inputbox" type="file" name="upload" id="uploadfile" size="50" />&nbsp;
									</td>
								</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td align="center" bgcolor="white">
								<div class="navigation" style="width: 20%; display: block; position: relative; float: left; margin: 0; padding: 2px; overflow: hidden;">
									<?php JMediaViews::_buildFolderTree($tree); ?>
								</div>
								<div class="manager" style="width: 78%; display: block; float: left; margin: 0; padding: 2px 0px 0px 0px;">
									<iframe height="360" src="index3.php?option=com_media&amp;task=list&amp;cFolder=<?php echo $current;?>" name="imgManager" id="imgManager" width="100%" marginwidth="0" marginheight="0" scrolling="auto" frameborder="0"></iframe>
								</div>
							</td>
						</tr>
						</table>
					</fieldset>
				</td>
			</tr>
			<tr>
				<td>

				</td>
			</tr>
			<tr>
				<td>
					<div style="text-align: right;">
					</div>
				</td>
			</tr>
			</table>
		</div>

		<input type="hidden" name="option" value="com_media" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="cb1" id="cb1" value="0" />
		<input type="hidden" name="dirpath" id="dirpath" value="<?php echo $current; ?>" />
		</form>
		<?php
	}

	/**
	 * Method to show the media list (in the iframe)
	 *
	 * @param string $listFolder The current working folder
	 * @param array $folders Array of folders in the current working folder
	 * @param array $docs Array of documents in the current working folder
	 * @param array $images Array of images in the current working folder
	 * @since 1.5
	 */
	function listMedia($current, $folders, $docs, $images) 
	{
		global $mainframe;

		$doc =& $mainframe->getDocument();
		$style = $mainframe->getUserStateFromRequest('media.list.style', 'listStyle', 'thumbs');
		
		$doc->addStyleSheet('templates/_system/css/media'.$style.'.css');

		$style = ucfirst($style);
		JMediaViews::imageStyle($current);

		if (count($images) > 0 || count($folders) > 0 || count($docs) > 0) {
			//now sort the folders and images by name.
			ksort($images);
			ksort($folders);
			ksort($docs);

			$method = 'draw'.$style.'Header';
			JMediaViews::$method();

			// Handle the folders
			if (count($folders)) {
				$method = 'showFolder'.$style;
				foreach ($folders as $folder => $folderName) {
					JMediaViews::$method('/' . $folderName, $folder, $current);
				}
			}

			// Handle the documents
			if (count($docs)) {
				$method = 'showDoc'.$style;
				foreach ($docs as $doc => $docDetails) {
					$extfile = substr($doc, strrpos($doc, '.') + 1);
					$iconfile = JPATH_ADMINISTRATOR . DS . "components" . DS . "com_media" . DS . "images" . DS . $extfile . "_16.png";
					if (file_exists($iconfile)) {
						$icon = "components/com_media/images/" . $extfile . "_16.png";
					} else {
						$icon = "components/com_media/images/con_info.png";
					}
					JMediaViews::$method($doc, $docDetails['size'], $current, $icon);
				}
			}

			// Handle the images
			if (count($images)) {
				$method = 'showImg'.$style;
				foreach ($images as $image => $imageDetails) {
					JMediaViews::$method($imageDetails['file'], $image, $imageDetails['imgInfo'], $imageDetails['size'], $current);
				}
			}

			$method = 'draw'.$style.'Footer';
			JMediaViews::$method();
		} else {
			JMediaViews::drawNoResults();
		}
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

	function drawThumbsHeader() 
	{
		mosCommonHTML::loadOverlib();
		?>
		<div class="manager">
		<?php
	}

	function drawThumbsFooter() 
	{
		?>
		</div>
		<?php
	}

	function drawListHeader() 
	{
		mosCommonHTML::loadOverlib();
		?>
		<div class="manager">
		<table width="100%">
		<?php
	}

	function drawListFooter() 
	{
		?>
		</table>
		</div>
		<?php
	}

	function showImgThumbs($img, $file, $info, $size, $listdir) 
	{
		$img_file	= basename($img);
		$img_url	= COM_MEDIA_BASEURL.$listdir.'/'.rawurlencode($img_file);
		$filesize	= JMediaHelper::parseSize($size);

		if (($info[0] > 70) || ($info[0] > 70)) {
			$img_dimensions = JMediaHelper::imageResize($info[0], $info[1], 80);
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
		$overlib .= '<br/> ' . JText::_('*Click to Enlarge*');
		$overlib .= '<br/> ' . JText::_('*Click for Image Code*');
		?>
		<div style="float:left; padding: 5px">
			<div class="imgTotal"  onmouseover="return overlib( '<?php echo $overlib; ?>', CAPTION, '<?php echo addslashes( $file ); ?>', BELOW, LEFT, WIDTH, 150 );" onmouseout="return nd();">
				<div align="center" class="imgBorder">
					<a onclick="javascript: window.open( '<?php echo $img_url; ?>', 'win1', 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=<?php echo $info[0] * 1.5;?>,height=<?php echo $info[1] * 1.5;?>,directories=no,location=no,left=120,top=80'); window.top.document.forms[0].imagecode.value = '<img src=&quot;<?php echo $img_url;?>&quot; align=&quot;left&quot; hspace=&quot;6&quot; alt=&quot;<?php echo JText::_( 'Image' ); ?>&quot; />';" style="display: block; width: 100%; height: 100%">
						<div class="image">
							<img src="<?php echo $img_url; ?>" <?php echo $img_dimensions; ?> alt="<?php echo $file; ?> - <?php echo $filesize; ?>" border="0" />
						</div></a>
				</div>
			</div>
			<div class="imginfoBorder">
				<small>
					<?php echo htmlspecialchars( $file, ENT_QUOTES ); ?>
				</small>
				<div class="buttonOut">
					<a href="index2.php?option=com_media&amp;task=delete&amp;delFile=<?php echo $file; ?>&amp;listdir=<?php echo $listdir; ?>" target="_top" onclick="return deleteImage('<?php echo $file; ?>');" title="<?php echo JText::_( 'Delete Item' ); ?>">
						<img src="components/com_media/images/edit_trash.gif" width="15" height="15" border="0" alt="<?php echo JText::_( 'Delete' ); ?>" /></a>
					<a onclick="javascript:window.top.document.forms[0].imagecode.value = '<img src=&quot;<?php echo $img_url;?>&quot; align=&quot;left&quot; hspace=&quot;6&quot; alt=&quot;<?php echo JText::_( 'Image' ); ?>&quot; />';" title="<?php echo JText::_( 'Image Code' ); ?>">
						<img src="components/com_media/images/edit_pencil.gif" width="15" height="15" border="0" alt="<?php echo JText::_( 'Code' ); ?>" /></a>
				</div>
			</div>
		</div>
		<?php
	}

	function showFolderThumbs($path, $dir, $listdir) 
	{
		$count		= JMediaHelper::countFiles(COM_MEDIA_BASE.$listdir.$path);
		$num_files	= $count[0];
		$num_dir	= $count[1];

		if ($listdir == '/') {
			$listdir = '';
		}

		$link = 'index3.php?option=com_media&amp;task=list&amp;cFolder='.$listdir.$path;

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
					<a href="<?php echo $link; ?>" target="imgManager" onclick="javascript:updateDir();">
						<img src="components/com_media/images/folder.gif" width="80" height="80" border="0" alt="<?php echo $dir; ?>" /></a>
				</div>
			</div>
			<div class="imginfoBorder">
				<small>
					<?php echo $dir; ?>
				</small>
				<div class="buttonOut">
					<a href="index2.php?option=com_media&amp;task=deletefolder&amp;delFolder=<?php echo $path; ?>&amp;listdir=<?php echo $listdir; ?>" target="_top" onclick="return deleteFolder('<?php echo $dir; ?>', <?php echo $num_files; ?>);">
						<img src="components/com_media/images/edit_trash.gif" width="15" height="15" border="0" alt="<?php echo JText::_( 'Delete' ); ?>" /></a>
				</div>
			</div>
		</div>
		<?php
	}

	function showDocThumbs($doc, $size, $listdir, $icon) 
	{
		global $mainframe;

		$size = JMediaHelper::parseSize($size);
		$base = "/images/";
		$overlib = JText::_('Filesize') . ': ' . $size;
		$overlib .= '<br /><br />' . JText::_('*Click for Url*');
		?>
		<div style="float:left; padding: 5px">
			<div class="imgTotal" onmouseover="return overlib( '<?php echo $overlib; ?>', CAPTION, '<?php echo $doc; ?>', BELOW, RIGHT, WIDTH, 200 );" onmouseout="return nd();">
				<div align="center" class="imgBorder">
				  <a href="index3.php?option=com_media&amp;task=list&amp;listdir=<?php echo $listdir; ?>" onclick="javascript:window.top.document.forms[0].imagecode.value = '<a href=&quot;<?php echo $mainframe->getSiteURL(). $base . $listdir  .'/'. $doc;?>&quot;><?php echo JText::_( 'Insert your text here' ); ?></a>';">
		  				<img border="0" src="<?php echo $icon ?>" alt="<?php echo $doc; ?>" /></a>
		  		</div>
			</div>
			<div class="imginfoBorder">
				<small>
					<?php echo $doc; ?>
				</small>
				<div class="buttonOut">
					<a href="index2.php?option=com_media&amp;task=delete&amp;delFile=<?php echo $doc; ?>&amp;listdir=<?php echo $listdir; ?>" target="_top" onclick="return deleteImage('<?php echo $doc; ?>');">
						<img src="components/com_media/images/edit_trash.gif" width="15" height="15" border="0" alt="<?php echo JText::_( 'Delete' ); ?>" /></a>
				</div>
			</div>
		</div>
		<?php
	}

	function showImgList($img, $file, $info, $size, $listdir) 
	{
		$img_file	= basename($img);
		$img_url	= COM_MEDIA_BASEURL.$listdir.'/'.rawurlencode($img_file);
		$filesize	= JMediaHelper::parseSize($size);

		if (($info[0] > 40) || ($info[0] > 40)) {
			$img_dimensions = JMediaHelper::imageResize($info[0], $info[1], 50);
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
		$overlib .= '<br/> ' . JText::_('*Click to Enlarge*');
		$overlib .= '<br/> ' . JText::_('*Click for Image Code*');
		?>
		<tr>
			<td onmouseover="return overlib( '<?php echo $overlib; ?>', CAPTION, '<?php echo addslashes( $file ); ?>', BELOW, LEFT, WIDTH, 150 );" onmouseout="return nd();">
				<a onclick="javascript: window.open( '<?php echo $img_url; ?>', 'win1', 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=<?php echo $info[0] * 1.5;?>,height=<?php echo $info[1] * 1.5;?>,directories=no,location=no,left=120,top=80'); window.top.document.forms[0].imagecode.value = '<img src=&quot;<?php echo $img_url;?>&quot; align=&quot;left&quot; hspace=&quot;6&quot; alt=&quot;<?php echo JText::_( 'Image' ); ?>&quot; />';" style="display: block; width: 100%; height: 100%">
					<img src="<?php echo $img_url; ?>" <?php echo $img_dimensions; ?> alt="<?php echo $file; ?> - <?php echo $filesize; ?>" border="0" />
				</a>
			</td>
			<td>
				<small>
					<?php echo htmlspecialchars( $file, ENT_QUOTES ); ?>
				</small>
			</td>
			<td>
				<a href="index2.php?option=com_media&amp;task=delete&amp;delFile=<?php echo $file; ?>&amp;listdir=<?php echo $listdir; ?>" target="_top" onclick="return deleteImage('<?php echo $file; ?>');" title="<?php echo JText::_( 'Delete Item' ); ?>">
					<img src="components/com_media/images/edit_trash.gif" width="15" height="15" border="0" alt="<?php echo JText::_( 'Delete' ); ?>" />
				</a>
			</td>
			<td>
				<a onclick="javascript:window.top.document.forms[0].imagecode.value = '<img src=&quot;<?php echo $img_url;?>&quot; align=&quot;left&quot; hspace=&quot;6&quot; alt=&quot;<?php echo JText::_( 'Image' ); ?>&quot; />';" title="<?php echo JText::_( 'Image Code' ); ?>">
					<img src="components/com_media/images/edit_pencil.gif" width="15" height="15" border="0" alt="<?php echo JText::_( 'Code' ); ?>" />
				</a>
			</td>
		</tr>
		<?php
	}

	function showFolderList($path, $dir, $listdir) 
	{
		$count		= JMediaHelper::countFiles(COM_MEDIA_BASE.$listdir.$path);
		$num_files	= $count[0];
		$num_dir	= $count[1];

		if ($listdir == '/') {
			$listdir = '';
		}

		$link = 'index3.php?option=com_media&amp;task=list&amp;cFolder='.$listdir.$path;

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
		<tr>
			<td class="imgTotal" onmouseover="return overlib( '<?php echo $overlib; ?>', CAPTION, '<?php echo $dir; ?>', BELOW, RIGHT, WIDTH, 150 );" onmouseout="return nd();">
				<a href="<?php echo $link; ?>" target="imgManager" onclick="javascript:updateDir();">
					<img src="components/com_media/images/folder.gif" width="50" height="50" border="0" alt="<?php echo $dir; ?>" />
				</a>
			</td>
			<td>
				<small>
					<?php echo $dir; ?>
				</small>
			</td>
			<td>
				<a href="index2.php?option=com_media&amp;task=deletefolder&amp;delFolder=<?php echo $path; ?>&amp;listdir=<?php echo $listdir; ?>" target="_top" onclick="return deleteFolder('<?php echo $dir; ?>', <?php echo $num_files; ?>);">
					<img src="components/com_media/images/edit_trash.gif" width="15" height="15" border="0" alt="<?php echo JText::_( 'Delete' ); ?>" />
				</a>
			</td>
		</tr>
		<?php
	}

	function showDocList($doc, $size, $listdir, $icon) 
	{
		global $mainframe;

		$size = JMediaHelper::parseSize($size);
		$base = "/images/";
		$overlib = JText::_('Filesize') . ': ' . $size;
		$overlib .= '<br /><br />' . JText::_('*Click for Url*');
		?>
		<tr>
			<td class="imgTotal" onmouseover="return overlib( '<?php echo $overlib; ?>', CAPTION, '<?php echo $doc; ?>', BELOW, RIGHT, WIDTH, 200 );" onmouseout="return nd();">
				<a href="index3.php?option=com_media&amp;task=list&amp;listdir=<?php echo $listdir; ?>" onclick="javascript:window.top.document.forms[0].imagecode.value = '<a href=&quot;<?php echo $mainframe->getSiteURL(). $base . $listdir  .'/'. $doc;?>&quot;><?php echo JText::_( 'Insert your text here' ); ?></a>';">
	  				<img border="0" src="<?php echo $icon ?>" alt="<?php echo $doc; ?>" />
	  			</a>
			</td>
			<td>
				<small>
					<?php echo $doc; ?>
				</small>
			</td>
			<td>
				<a href="index2.php?option=com_media&amp;task=delete&amp;delFile=<?php echo $doc; ?>&amp;listdir=<?php echo $listdir; ?>" target="_top" onclick="return deleteImage('<?php echo $doc; ?>');">
					<img src="components/com_media/images/edit_trash.gif" width="15" height="15" border="0" alt="<?php echo JText::_( 'Delete' ); ?>" />
				</a>
			</td>
		</tr>
		<?php
	}

	function imageStyle($listdir) 
	{
		?>
		<script language="javascript" type="text/javascript">
		function updateDir(){
			window.top.document.forms[0].dirpath.value = '<?php echo $listdir; ?>';
			var tree = window.parent.d;
			tree.openToByName('<?php echo $listdir; ?>', true);
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

	function _buildFolderTree($tree)
	{
		global $mainframe;

		$doc =& $mainframe->getDocument();
		$doc->addScript('../includes/js/dtree/dtree.js');
		$doc->addStyleSheet('../includes/js/dtree/dtree.css');
		$txt = null;
		foreach($tree as $node) {
			$txt .= "d.add(".$node['id'].", ".$node['pid'].", '".$node['name']."', '".$node['url']."', '".$node['title']."', '".$node['target']."');\n";
		}
		?>
		<script language="JavaScript" type="text/javascript">
			d = new dTree('d', '../includes/js/dtree/img/');
			<?php echo $txt; ?>
			document.write(d);
			d.openToByName('/',true);
		</script>
		<?php
	}

	function _loadJS()
	{
		global $mainframe;
		
		$url = ($mainframe->isAdmin()) ? $mainframe->getSiteURL() : $mainframe->getBaseURL();
		$js = "
		var base_url = '".$url."';	
			
		function dirup(){
			var urlquery=frames['imgManager'].location.search.substring(1);
			var curdir= urlquery.substring(urlquery.indexOf('cFolder=')+8);
			var listdir=curdir.substring(0,curdir.lastIndexOf('/'));
			frames['imgManager'].location.href='index.php?option=com_media&task=list&tmpl=component.html&cFolder=' + listdir;
		}

		function goUpDir() {
			var dir = document.forms[0].dirpath.value;
			frames['imgManager'].location.href='index.php?option=com_media&task=list&tmpl=component.html&cFolder=' + dir;
		}
		
		function setStyle() {
			var selection = document.forms[0].listStyle;
			var style = selection.options[selection.selectedIndex].value;
			var urlquery=frames['imgManager'].location.search.substring(1);
			var curdir= urlquery.substring(urlquery.indexOf('cFolder=')+8);
			frames['imgManager'].location.href='index.php?option=com_media&task=list&tmpl=component.html&cFolder=' + curdir + '&listStyle=' + style;
		}
		
		// Opens the tree to a specific node
		dTree.prototype.openToByName = function(nName, bSelect, bFirst) {
			var nId = 0;
				for (var n=0; n<this.aNodes.length; n++) {
					if (this.aNodes[n].name == nName) {
						nId=n;
						break;
					}
				}
			var cn=this.aNodes[nId];
			if (cn.pid==this.root.id || !cn._p) return;
			cn._io = true;
			cn._is = bSelect;
			if (this.completed && cn._hc) this.nodeStatus(true, cn._ai, cn._ls);
			if (this.completed && bSelect) this.s(cn._ai);
			else if (bSelect) this._sn=cn._ai;
			this.openTo(cn._p._ai, false, true);
		};
		";
		$doc =& $mainframe->getDocument();
		$doc->addScriptDeclaration($js);
	}
}
?>