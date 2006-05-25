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

		$style = $mainframe->getUserStateFromRequest('media.list.style', 'listStyle', 'thumbs');

		$listStyle = "
			<ul id=\"submenu\">
				<li class=\"item-smenu\"><a onclick=\"document.mediamanager.setViewType('thumbs')\">".JText::_('Thumbnail View')."</a></li>
				<li class=\"item-smenu\"><a onclick=\"document.mediamanager.setViewType('details')\">".JText::_('Detail View')."</a></li>
			</ul>
		";

		$document =& $mainframe->getDocument();
		$document->set('module', 'submenu', $listStyle);	
		$document->addScript('components/com_media/includes/mediamanager.js');


		JMediaViews::_loadJS();
		?>
		<form action="index.php" name="adminForm" method="post" enctype="multipart/form-data" >
		<table width="100%" border="0" cellspacing="1" cellpadding="3"  class="adminheading">
		<tr valign="top">
			<td width="150px">
				<fieldset style="padding: 10px; width:140px; overflow: auto; height: 365px">
					<legend><?php echo JText::_( 'Folders' ); ?></legend>
					<?php JMediaViews::_buildFolderTree($tree); ?>
				</fieldset>
				<fieldset>
					<legend><?php echo JText::_( 'Create Folder' ); ?></legend>
					<input class="inputbox" type="text" name="foldername" id="foldername" />
					<input type="button" value="Create" onclick="javascript:submitbutton('newdir')" />
				</div>
				</fieldset>
			</td>
			<td>
				<fieldset>
					<legend><?php echo JText::_( 'Files' ); ?></legend>
					<div class="manager" style="display: block; margin: 0; padding: 0; height: 360px;">
						<iframe style="height: 100%" src="index.php?option=com_media&amp;task=list&amp;tmpl=component.html&amp;cFolder=<?php echo $current;?>" name="fileview" id="fileview" name="fileview" width="100%" marginwidth="0" marginheight="0" scrolling="auto" frameborder="0" onload="document.mediamanager.onload();"></iframe>
					</div>
				</fieldset>
				<fieldset>
					<legend><?php echo JText::_( 'Upload File' ); ?></legend>
					<button onclick="jsAddFile();return false">
						+ Add more files
					</button>
					[ <?php echo JText::_( 'Max' ); ?>&nbsp;<?php echo ini_get( 'post_max_size' );?> ]		
					<div id="uploads">
						<div style="padding: 4px;">
							<input class="inputbox" name="uploads[]" type="file" size="60" />
						</div>
					</div>
					
					<button onclick="javascript:submitbutton('uploadBatch')">Upload Files</button>
				</fieldset>
			</td>
		</tr>
		</table>

		<input type="hidden" name="option" value="com_media" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="cb1" id="cb1" value="0" />
		<input type="hidden" name="dirpath" id="dirpath" value="<?php echo $current; ?>" />
		</form>
		<?php
		mosHTML::keepAlive();
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

		$doc->addStyleSheet('components/com_media/includes/medialist-'.$style.'.css');

		$style = ucfirst($style);
		JMediaViews::imageStyle($current);

		if (count($images) > 0 || count($folders) > 0 || count($docs) > 0) 
		{
			//now sort the folders and images by name.
			ksort($images);
			ksort($folders);
			ksort($docs);

			$method = 'draw'.$style.'Header';
			JMediaViews::$method();
			
			$method = 'showUp'.$style;
			JMediaViews::$method('/' . $folderName, $folder, $current);

			// Handle the folders
			if (count($folders)) {
				$method = 'showFolder'.$style;
				foreach ($folders as $folder => $folderName) {
					JMediaViews::$method('/' . $folderName, $folder, $current);
				}
			}

			// Handle the documents
			if ($numDocs = count($docs)) {
				$method = 'showDoc'.$style;
				for($i=0;$i<$numDocs;$i++) {
					$extfile = substr($docs[$i]['name'], strrpos($docs[$i]['name'], '.') + 1);
					
					$iconfile = JPATH_ADMINISTRATOR.DS."components".DS."com_media".DS."images".DS.$extfile."_16.png";
					if (file_exists($iconfile)) {
						$icon = "components/com_media/images/".$extfile."_16.png";
					} else {
						$icon = "components/com_media/images/con_info.png";
					}
					JMediaViews::$method($docs[$i]['name'], $docs[$i]['size'], $current, $icon);
				}
			}

			// Handle the images
			if ($numImages = count($images)) {
				$method = 'showImg'.$style;
				for($i=0;$i<$numImages;$i++) {
					JMediaViews::$method($images[$i]['file'], $images[$i]['name'], $images[$i]['imgInfo'], $images[$i]['size'], $current);
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

	function drawDetailsHeader() 
	{
		?>
		<div class="manager">
		<table width="100%" cellspacing="0">
		<thead>
			<tr>
				<th>Preview</td>
				<th>Name</td>
				<th>Dimensions</td>
				<th>Size</td>
				<th>Delete</td>
			</tr>
		</thead>
		<tbody>
		<?php
	}

	function drawDetailsFooter() 
	{
		?>
		</tbody>
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

		?>
		<div class="imgOutline">
			<div class="imgTotal">
				<div align="center" class="imgBorder">
					<a style="display: block; width: 100%; height: 100%">
						<div class="image">
							<img src="<?php echo $img_url; ?>" <?php echo $img_dimensions; ?> border="0" />
						</div>
					</a>
				</div>
			</div>
			<div class="imginfoBorder">
				<?php echo htmlspecialchars( substr( $file, 0, 10 ) . ( strlen( $file ) > 10 ? '...' : ''), ENT_QUOTES ); ?>
				<div class="buttonOut">
					<a href="index.php?option=com_media&amp;tmpl=component.html&amp;task=delete&amp;delFile=<?php echo $file; ?>&amp;folder=<?php echo $listdir; ?>&amp;cFolder=<?php echo $listdir; ?>" target="imgManager" onclick="return deleteImage('<?php echo $file; ?>');" title="<?php echo JText::_( 'Delete Item' ); ?>">
						<img src="components/com_media/images/remove.png" width="16" height="16" border="0" alt="<?php echo JText::_( 'Delete' ); ?>" />
					</a>
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

		$link = 'index.php?option=com_media&amp;task=list&amp;tmpl=component.html&amp;cFolder='.$listdir.$path;

		?>
		<div class="imgOutline">
			<div class="imgTotal">
				<div align="center" class="imgBorder">
					<a href="<?php echo $link; ?>" target="fileview">
						<img src="components/com_media/images/folder.png" width="80" height="80" border="0" />
					</a>
				</div>
			</div>
			<div class="imginfoBorder">
				<?php echo substr( $dir, 0, 10 ) . ( strlen( $dir ) > 10 ? '...' : ''); ?>
				<div class="buttonOut">
					<a href="index.php?option=com_media&amp;tmpl=component.html&amp;task=deletefolder&amp;delFolder=<?php echo $path; ?>&amp;folder=<?php echo $listdir; ?>&amp;cFolder=<?php echo $listdir; ?>" target="imgManager" onclick="return deleteFolder('<?php echo $dir; ?>', <?php echo $num_files; ?>);" title="<?php echo JText::_( 'Delete Item' ); ?>">
						<img src="components/com_media/images/remove.png" width="16" height="16" border="0" alt="<?php echo JText::_( 'Delete' ); ?>" />
					</a>
				</div>
			</div>
		</div>
		<?php
	}
	
	function showUpThumbs($path, $dir, $listdir) 
	{
		$count		= JMediaHelper::countFiles(COM_MEDIA_BASE.$listdir.$path);
		$num_files	= $count[0];
		$num_dir	= $count[1];

		if ($listdir == '/') {
			$listdir = '';
		}
		
		$folder = str_replace("\\", "/", dirname($listdir.$path));

		$link = 'index.php?option=com_media&amp;task=list&amp;tmpl=component.html&amp;cFolder='.$folder;

		?>
		<div class="imgOutline">
			<div class="imgTotal">
				<div align="center" class="imgBorder">
					<a href="<?php echo $link; ?>" target="fileview">
						<img src="components/com_media/images/folder.png" width="80" height="80" border="0" /></a>
				</div>
			</div>
			<div class="imginfoBorder">
				...
			</div>
		</div>
		<?php
	}

	function showDocThumbs($doc, $size, $listdir, $icon) 
	{
		global $mainframe;

		$size = JMediaHelper::parseSize($size);
		$base = "/images/";
		$doc_url	= COM_MEDIA_BASEURL.$listdir.'/'.rawurlencode($doc);
		?>
		<div class="imgOutline">
			<div class="imgTotal">
				<div align="center" class="imgBorder">
				 	<a style="display: block; width: 100%; height: 100%">
		  				<img border="0" src="<?php echo $icon ?>" alt="<?php echo $doc; ?>" />
		  			</a>
		  		</div>
			</div>
			<div class="imginfoBorder">
				<?php echo $doc; ?>
				<div class="buttonOut">
					<a href="index.php?option=com_media&amp;tmpl=component.html&amp;task=delete&amp;delFile=<?php echo $doc; ?>&amp;folder=<?php echo $listdir; ?>&amp;cFolder=<?php echo $listdir; ?>" target="imgManager" onclick="return deleteImage('<?php echo $doc; ?>');">
						<img src="components/com_media/images/remove.png" width="16" height="16" border="0" alt="<?php echo JText::_( 'Delete' ); ?>" />
					</a>
				</div>
			</div>
		</div>
		<?php
	}

	function showImgDetails($img, $file, $info, $size, $listdir) 
	{
		$img_file	= basename($img);
		$img_url	= COM_MEDIA_BASEURL.$listdir.'/'.rawurlencode($img_file);
		$filesize	= JMediaHelper::parseSize($size);

		if (($info[0] > 16) || ($info[0] > 16)) {
			$img_dimensions = JMediaHelper::imageResize($info[0], $info[1], 16);
		} else {
			$img_dimensions = 'width="' . $info[0] . '" height="' . $info[1] . '"';
		}

		?>
		<tr>
			<td>
				<img src="<?php echo $img_url; ?>" <?php echo $img_dimensions; ?> alt="<?php echo $file; ?> - <?php echo $filesize; ?>" border="0" />
			</td>
			<td class="description">
				<?php echo htmlspecialchars( $file, ENT_QUOTES ); ?>
			</td>
			<td>
				<?php echo $info[0]; ?> x <?php echo $info[1]; ?>
			</td>
			<td>
				<?php echo $filesize; ?>
			</td>
			<td>
				<a href="index.php?option=com_media&amp;tmpl=component.html&amp;task=delete&amp;delFile=<?php echo $file; ?>&amp;folder=<?php echo $listdir; ?>&amp;cFolder=<?php echo $listdir; ?>" target="imgManager" onclick="return deleteImage('<?php echo $file; ?>');" title="<?php echo JText::_( 'Delete Item' ); ?>">
					<img src="components/com_media/images/remove.png" width="16" height="16" border="0" alt="<?php echo JText::_( 'Delete' ); ?>" />
				</a>
			</td>
		</tr>
		<?php
	}

	function showFolderDetails($path, $dir, $listdir) 
	{
		$count		= JMediaHelper::countFiles(COM_MEDIA_BASE.$listdir.$path);
		$num_files	= $count[0];
		$num_dir	= $count[1];

		if ($listdir == '/') {
			$listdir = '';
		}

		$link = 'index.php?option=com_media&amp;task=list&amp;tmpl=component.html&amp;cFolder='.$listdir.$path;
		?>
		<tr>
			<td class="imgTotal">
				<a href="<?php echo $link; ?>" target="fileview">
					<img src="components/com_media/images/folder_sm.png" width="16" height="16" border="0" alt="<?php echo $dir; ?>" />
				</a>
			</td>
			<td class="description">
				<?php echo $dir; ?>
			</td>
			<td>
				&nbsp;
			</td>
			<td>
				&nbsp;
			</td>
			<td>
				<a href="index.php?option=com_media&amp;tmpl=component.html&amp;task=deletefolder&amp;delFolder=<?php echo $path; ?>&amp;folder=<?php echo $listdir; ?>&amp;cFolder=<?php echo $listdir; ?>" target="imgManager" onclick="return deleteFolder('<?php echo $dir; ?>', <?php echo $num_files; ?>);" title="<?php echo JText::_( 'Delete Item' ); ?>">
					<img src="components/com_media/images/remove.png" width="16" height="16" border="0" alt="<?php echo JText::_( 'Delete' ); ?>" />
				</a>
			</td>
		</tr>
		<?php
	}
	
	function showUpDetails($path, $dir, $listdir) 
	{
		$count		= JMediaHelper::countFiles(COM_MEDIA_BASE.$listdir.$path);
		$num_files	= $count[0];
		$num_dir	= $count[1];
		
		if ($listdir == '/') {
			$listdir = '';
		}
		
		$folder = str_replace("\\", "/", dirname($listdir.$path));
		
		$link = 'index.php?option=com_media&amp;task=list&amp;tmpl=component.html&amp;cFolder='.$folder;
		?>
		<tr>
			<td class="imgTotal">
				<a href="<?php echo $link; ?>" target="fileview">
					<img src="components/com_media/images/folderup.png" width="16" height="16" border="0" alt="<?php echo $dir; ?>" />
				</a>
			</td>
			<td class="description">
				...
			</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		<?php
	}

	function showDocDetails($doc, $size, $listdir, $icon) 
	{
		global $mainframe;

		$size = JMediaHelper::parseSize($size);
		$base = "/images/";
		$doc_url	= COM_MEDIA_BASEURL.$listdir.'/'.rawurlencode($doc);
		?>
		<tr>
			<td>
				<a>
					<img src="<?php echo $icon ?>" width="16" height="16" border="0" alt="<?php echo $doc; ?>" />
				</a>
			</td>
			<td class="description">
				<?php echo $doc; ?>
			</td>
			<td>
				&nbsp;
			</td>
			<td>
				<?php echo $size; ?>
			</td>
			<td>
				<a href="index.php?option=com_media&amp;tmpl=component.html&amp;task=delete&amp;delFile=<?php echo $doc; ?>&amp;folder=<?php echo $listdir; ?>&amp;cFolder=<?php echo $listdir; ?>" target="imgManager" onclick="return deleteImage('<?php echo $doc; ?>');">
					<img src="components/com_media/images/remove.png" width="16" height="16" border="0" alt="<?php echo JText::_( 'Delete' ); ?>" />
				</a>
			</td>
		</tr>
		<?php
	}

	function imageStyle($listdir) 
	{
		if ($listdir == '') {
			$listdir = '/';
		}
		?>
		<script language="javascript" type="text/javascript">
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
			$txt .= "d.add(".$node['id'].", ".$node['pid'].", '".$node['name']."', '".$node['url']."', '".$node['title']."', '".$node['target']."', '../includes/js/dtree/img/folder.gif');\n";
		}
		?>
		<script language="JavaScript" type="text/javascript">
			d = new dTree('d', '../includes/js/dtree/img/');
			d.config.useCookies = false;
			<?php echo $txt; ?>
			document.write(d);
		</script>
		<?php
	}

	function _loadJS()
	{	
		$js = "		
		function jsAddFile() {
			div = document.getElementById( 'uploads' );
		
			div.appendChild( writeUploadField() );
			return false;
		}
		
		function writeUploadField() {
			// <input class=\"inputbox\" name=\"upload\" type=\"file\" size=\"70\" />
			div = document.createElement( 'div' );
			div.setAttribute( 'style', 'padding: 4px' );
		
			tag = document.createElement( 'input' );
			tag.setAttribute( 'type', 'file' );
			tag.setAttribute( 'name', 'uploads[]' );
			tag.setAttribute( 'size', '60' );
			tag.setAttribute( 'class', 'inputbox' );
			div.appendChild( tag );
		
			return div;
		}
" ;
		global $mainframe;
		$doc =& $mainframe->getDocument();
		$doc->addScriptDeclaration($js);
	}
}
?>
