<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Massmail
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
	/**
	 * Method to show the standard Media Manager view
	 *
	 * @param string $dirPath The current path select box
	 * @param string $listdir The current working directory
	 * @since 1.0
	 */
	function showMedia($tree, $ftp)
	{
		global $mainframe;
		$config		=& JFactory::getConfig();
		$configMedia	=& JComponentHelper::getParams( 'com_media' );
		// Get current path from request
		$current = JRequest::getVar( 'folder', '', '', 'path' );
		if ($current == '/') {
			$current = '';
		}

		$listStyle = "
			<ul id=\"submenu\">
				<li><a id=\"thumbs\" onclick=\"document.mediamanager.setViewType('thumbs')\">".JText::_('Thumbnail View')."</a></li>
				<li><a id=\"details\" onclick=\"document.mediamanager.setViewType('details')\">".JText::_('Detail View')."</a></li>
			</ul>
		";

		$document =& JFactory::getDocument();
		$document->setBuffer($listStyle, 'module', 'submenu');

		JHTML::_('behavior.mootools');
		$document->addScript('components/com_media/assets/mediamanager.js');
		$document->addStyleSheet('components/com_media/assets/mediamanager.css');
//		$document->addStyleSheet('components/com_media/assets/preview.css');

		JHTML::_('behavior.modal');
		$document->addScriptDeclaration("
		window.addEvent('domready', function() {
			document.preview = SqueezeBox;
		});");

		JHTML::_('behavior.uploader', 'file-upload', array('onAllComplete' => 'function(){ $(\'folderframe\').src = $(\'folderframe\').src; }'));
		$session = &JFactory::getSession();

		MediaViews::_loadJS();
		?>
		<form action="index.php" name="adminForm" id="adminForm" method="post" enctype="multipart/form-data" >
		<?php if ($ftp): ?>
		<fieldset title="<?php echo JText::_('DESCFTPTITLE'); ?>">
			<legend><?php echo JText::_('DESCFTPTITLE'); ?></legend>
			<?php echo JText::_('DESCFTP'); ?>
			<table class="adminform nospace">
				<tbody>
					<tr>
						<td width="120">
							<label for="username"><?php echo JText::_('Username'); ?>:</label>
						</td>
						<td>
							<input type="text" id="username" name="username" class="input_box" size="70" value="" />
						</td>
					</tr>
					<tr>
						<td width="120">
							<label for="password"><?php echo JText::_('Password'); ?>:</label>
						</td>
						<td>
							<input type="password" id="password" name="password" class="input_box" size="70" value="" />
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
		<?php endif; ?>
		<table width="100%" cellspacing="0">
		<tr valign="top">
			<td width="150px">
				<fieldset id="treeview">
					<legend><?php echo JText::_( 'Folders' ); ?></legend>
					<?php MediaViews::_buildFolderTree($tree); ?>
				</fieldset>
			</td>
			<td>
				<fieldset id="folderview">
					<legend><?php echo JText::_( 'Files' ); ?></legend>
					<div class="path">
						<input class="inputbox" type="text" id="folderpath" readonly="readonly" />/
						<input class="inputbox" type="text" id="foldername" name="foldername"  />
						<button type="button" onclick="document.mediamanager.oncreatefolder()" /><?php echo JText::_( 'Create Folder' ); ?></button>
					</div>
					<div class="view">
						<iframe src="index.php?option=com_media&amp;task=list&amp;tmpl=component&amp;folder=<?php echo $current;?>" id="folderframe" name="folderframe" width="100%" marginwidth="0" marginheight="0" scrolling="auto" frameborder="0"></iframe>
					</div>
				</fieldset>
			</td>
		</tr>
		</table>

		<input type="hidden" name="option" value="com_media" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="cb1" id="cb1" value="0" />
		<input type="hidden" name="dirpath" id="dirpath" value="<?php echo $current; ?>" />
		</form>

		<form action="<?php echo JURI::base(); ?>index.php?option=com_media&amp;task=upload&amp;tmpl=component&amp;<?php echo $session->getName().'='.$session->getId(); ?>" id="uploadForm" method="post" enctype="multipart/form-data">
			<fieldset>
				<legend><?php echo JText::_( 'Upload File' ); ?> [ <?php echo JText::_( 'Max' ); ?>&nbsp;<?php echo ($configMedia->get('upload_maxsize') / 1000000); ?>M ]</legend>
				<fieldset class="actions">
					<input type="file" id="file-upload" />
					<input type="submit" id="file-upload-submit" value="Start Upload"/>
					<span id="upload-clear"></span>
				</fieldset>
				<ul class="upload-queue" id="upload-queue">
					<li style="display: none" />
				</ul>
			</fieldset>
		</form>

		<?php
		echo JHTML::_('behavior.keepalive');
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

		JHTML::_('behavior.mootools');

		$doc	=& JFactory::getDocument();
		$style	= $mainframe->getUserStateFromRequest('media.list.style', 'listStyle', 'thumbs', 'word');
		$doc->addStyleSheet('components/com_media/assets/medialist-'.$style.'.css');

		$doc->addScriptDeclaration("
		window.addEvent('domready', function() {
			$$('a.preview').each(function(el) {
				el.addEvent('click', function(e) {
					new Event(e).stop();
					window.top.document.preview.fromElement(el);
				});
			});
		});");

		$style = ucfirst($style);
		if (($style != 'Details') && ($style != 'Thumbs')) {
			$style = 'Thumbs';
		}

		MediaViews::imageStyle($current);

		if (count($images) > 0 || count($folders) > 0 || count($docs) > 0)
		{
			//now sort the folders and images by name.
			ksort($images);
			ksort($folders);
			ksort($docs);

			$method = 'draw'.$style.'Header';
			MediaViews::$method($current);

			$method = 'showUp'.$style;
			MediaViews::$method($current);

			// Handle the folders
			if (count($folders)) {
				$method = 'showFolder'.$style;
				foreach ($folders as $folder => $folderName) {
					MediaViews::$method('/' . $folderName, $folder, $current);
				}
			}

			// Handle the documents
			if ($numDocs = count($docs)) {
				$method = 'showDoc'.$style;
				for($i=0;$i<$numDocs;$i++) {
					$extfile = MediaHelper::getTypeIcon($docs[$i]['name']);
					MediaViews::$method($docs[$i]['name'], $docs[$i]['size'], $current, $extfile);
				}
			}

			// Handle the images
			if ($numImages = count($images)) {
				$method = 'showImg'.$style;
				for($i=0;$i<$numImages;$i++) {
					MediaViews::$method($images[$i]['file'], $images[$i]['name'], $images[$i]['imgInfo'], $images[$i]['size'], $current);
				}
			}

			$method = 'draw'.$style.'Footer';
			MediaViews::$method();
		} else {
			MediaViews::drawNoResults();
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

	function drawThumbsHeader($current)
	{
		if ($current == '/') {
			$current = '';
		}
		?>
		<form action="index.php?option=com_media&amp;tmpl=component&amp;folder=<?php echo $current; ?>" method="post" id="mediamanager-form" name="mediamanager-form">
		<div class="manager">
		<?php
	}

	function drawThumbsFooter()
	{
		?>
		</div>
		<input type="hidden" name="task" value="list" />
		<input type="hidden" name="username" value="" />
		<input type="hidden" name="password" value="" />
		</form>
		<?php
	}

	function drawDetailsHeader($current)
	{
		if ($current == '/') {
			$current = '';
		}
		?>
		<form action="index.php?option=com_media&amp;tmpl=component&amp;folder=<?php echo $current; ?>" method="post" id="mediamanager-form" name="mediamanager-form">
		<div class="manager">
		<table width="100%" cellspacing="0">
		<thead>
			<tr>
				<th><?php echo JText::_( 'Preview' ); ?></td>
				<th><?php echo JText::_( 'Name' ); ?></td>
				<th><?php echo JText::_( 'Dimensions' ); ?></td>
				<th><?php echo JText::_( 'Size' ); ?></td>
				<th><?php echo JText::_( 'Delete' ); ?></td>
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
		<input type="hidden" name="task" value="list" />
		<input type="hidden" name="username" value="" />
		<input type="hidden" name="password" value="" />
		</form>
		<?php
	}

	function showImgThumbs($img, $file, $info, $size, $folder)
	{
		$img_file	= basename($img);
		if ($folder) {
			$img_url = COM_MEDIA_BASEURL.'/'.$folder.'/'.rawurlencode($img_file);
		} else {
			$img_url = COM_MEDIA_BASEURL.'/'.rawurlencode($img_file);
		}
		$filesize	= MediaHelper::parseSize($size);

		if (($info[0] > 70) || ($info[0] > 70)) {
			$img_dimensions = MediaHelper::imageResize($info[0], $info[1], 80);
		} else {
			$img_dimensions = 'width="' . $info[0] . '" height="' . $info[1] . '"';
		}

		?>
		<div class="imgOutline">
			<div class="imgTotal">
				<div align="center" class="imgBorder">
					<a class="preview" href="<?php echo $img_url; ?>" title="<?php echo $file; ?>" style="display: block; width: 100%; height: 100%">
						<div class="image">
							<img src="<?php echo $img_url; ?>" <?php echo $img_dimensions; ?> alt="<?php echo $file; ?> - <?php echo $filesize; ?>" border="0" />
						</div>
					</a>
				</div>
			</div>
			<div class="imginfoBorder">
				<a href="<?php echo $img_url; ?>" title="<?php echo $file; ?>" rel="preview"><?php echo htmlspecialchars( substr( $file, 0, 10 ) . ( strlen( $file ) > 10 ? '...' : ''), ENT_QUOTES ); ?></a>
			</div>
		</div>
		<?php
	}

	function showFolderThumbs($path, $dir, $folder)
	{
		$path = trim($path, '\\/ ');
		$folder = trim ($folder, '\\/ ');
		$count		= MediaHelper::countFiles(COM_MEDIA_BASE.DS.$folder.DS.$path);
		$num_files	= $count[0];
		$num_dir	= $count[1];

		$fullfolder = ($folder) ? $folder.'/' : '';
		$fullfolder .= $path;
		$link = 'index.php?option=com_media&amp;task=list&amp;tmpl=component&amp;folder='.$fullfolder;

		?>
		<div class="imgOutline">
			<div class="imgTotal">
				<div align="center" class="imgBorder">
					<a href="<?php echo $link; ?>" target="folderframe">
						<img src="components/com_media/images/folder.png" width="80" height="80" border="0" />
					</a>
				</div>
			</div>
			<div class="imginfoBorder">
				<a href="<?php echo $link; ?>" target="folderframe"><?php echo substr( $dir, 0, 10 ) . ( strlen( $dir ) > 10 ? '...' : ''); ?></a>
			</div>
		</div>
		<?php
	}

	function showUpThumbs($path)
	{
		$count		= MediaHelper::countFiles(COM_MEDIA_BASE.DS.$path);
		$num_files	= $count[0];
		$num_dir	= $count[1];

		$folder = str_replace("\\", "/", dirname($path));
		if ($folder == '.') {
			$folder = '';
		}
		$link = 'index.php?option=com_media&amp;task=list&amp;tmpl=component&amp;folder='.$folder;

		?>
		<div class="imgOutline">
			<div class="imgTotal">
				<div align="center" class="imgBorder">
					<a href="<?php echo $link; ?>" target="folderframe">
						<img src="components/com_media/images/folderup_32.png" width="32" height="32" border="0" alt=".." />
					</a>
				</div>
			</div>
			<div class="imginfoBorder">
				<a href="<?php echo $link; ?>" target="folderframe">..</a>
			</div>
		</div>
		<?php
	}

	function showDocThumbs($doc, $size, $folder, $icon)
	{
		global $mainframe;

		$size = MediaHelper::parseSize($size);
		$base = "/images/";
		if ($folder) {
			$doc_url = COM_MEDIA_BASEURL.'/'.$folder.'/'.rawurlencode($doc);
		} else {
			$doc_url = COM_MEDIA_BASEURL.'/'.rawurlencode($doc);
		}

		$iconfile = JPATH_ADMINISTRATOR.DS."components".DS."com_media".DS."images".DS."mime-icon-32".DS.$icon.".png";
		if (file_exists($iconfile)) {
			$icon = "components/com_media/images/mime-icon-32/".$icon.".png";
		} else {
			$icon = "components/com_media/images/con_info.png";
		}
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
			</div>
		</div>
		<?php
	}

	function showImgDetails($img, $file, $info, $size, $folder)
	{
		$img_file	= basename($img);
		if ($folder) {
			$img_url = COM_MEDIA_BASEURL.'/'.$folder.'/'.rawurlencode($img_file);
		} else {
			$img_url = COM_MEDIA_BASEURL.'/'.rawurlencode($img_file);
		}
		$filesize	= MediaHelper::parseSize($size);

		if (($info[0] > 16) || ($info[0] > 16)) {
			$img_dimensions = MediaHelper::imageResize($info[0], $info[1], 16);
		} else {
			$img_dimensions = 'width="' . $info[0] . '" height="' . $info[1] . '"';
		}

		?>
		<tr>
			<td>
				<a class="preview" href="<?php echo $img_url; ?>" title="<?php echo $file;?>"><img src="<?php echo $img_url; ?>" <?php echo $img_dimensions; ?> alt="<?php echo $file; ?> - <?php echo $filesize; ?>" border="0" /></a>
			</td>
			<td class="description">
				<a href="<?php echo $img_url; ?>" title="<?php echo $file;?>" rel="preview"><?php echo htmlspecialchars( $file, ENT_QUOTES ); ?></a>
			</td>
			<td>
				<?php echo $info[0]; ?> x <?php echo $info[1]; ?>
			</td>
			<td>
				<?php echo $filesize; ?>
			</td>
			<td>
				<img src="components/com_media/images/remove.png" width="16" height="16" border="0" alt="<?php echo JText::_( 'Delete' ); ?>" onclick="confirmDeleteImage('<?php echo $file; ?>');" />
				<input type="checkbox" name="rm[]" value="<?php echo $file; ?>" />
			</td>
		</tr>
		<?php
	}

	function showFolderDetails($path, $dir, $folder)
	{
		$path = trim($path, '\\/ ');
		$folder = trim ($folder, '\\/ ');
		$count		= MediaHelper::countFiles(COM_MEDIA_BASE.DS.$folder.DS.$path);
		$num_files	= $count[0];
		$num_dir	= $count[1];

		$fullfolder = ($folder) ? $folder.'/' : '';
		$fullfolder .= $path;
		$link = 'index.php?option=com_media&amp;task=list&amp;tmpl=component&amp;folder='.$fullfolder;
		?>
		<tr>
			<td class="imgTotal">
				<a href="<?php echo $link; ?>" target="folderframe">
					<img src="components/com_media/images/folder_sm.png" width="16" height="16" border="0" alt="<?php echo $dir; ?>" />
				</a>
			</td>
			<td class="description">
				<a href="<?php echo $link; ?>" target="folderframe"><?php echo $dir; ?></a>
			</td>
			<td>
				&nbsp;
			</td>
			<td>
				&nbsp;
			</td>
			<td>
				<img src="components/com_media/images/remove.png" width="16" height="16" border="0" alt="<?php echo JText::_( 'Delete' ); ?>" onclick="confirmDeleteFolder('<?php echo $path; ?>', <?php echo $num_files+$num_dir; ?>);" />
				<input type="checkbox" name="rm[]" value="<?php echo $path; ?>" />
			</td>
		</tr>
		<?php
	}

	function showUpDetails($path)
	{
		$count		= MediaHelper::countFiles(COM_MEDIA_BASE.$path);
		$num_files	= $count[0];
		$num_dir	= $count[1];

		$folder = str_replace("\\", "/", dirname($path));
		if ($folder == '.') {
			$folder = '';
		}
		$link = 'index.php?option=com_media&amp;task=list&amp;tmpl=component&amp;folder='.$folder;

		?>
		<tr>
			<td class="imgTotal">
				<a href="<?php echo $link; ?>" target="folderframe">
					<img src="components/com_media/images/folderup_16.png" width="16" height="16" border="0" alt=".." />
				</a>
			</td>
			<td class="description">
				<a href="<?php echo $link; ?>" target="folderframe">..</a>
			</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		<?php
	}

	function showDocDetails($doc, $size, $folder, $icon)
	{
		global $mainframe;

		$iconfile = JPATH_ADMINISTRATOR.DS."components".DS."com_media".DS."images".DS."mime-icon-16".DS.$icon.".png";
		if (file_exists($iconfile)) {
			$icon = "components/com_media/images/mime-icon-16/".$icon.".png";
		} else {
			$icon = "components/com_media/images/con_info.png";
		}
		$size = MediaHelper::parseSize($size);
		$base = "/images/";
		if ($folder) {
			$doc_url = COM_MEDIA_BASEURL.'/'.$folder.'/'.rawurlencode($doc);
		} else {
			$doc_url = COM_MEDIA_BASEURL.'/'.rawurlencode($doc);
		}
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
				<img src="components/com_media/images/remove.png" width="16" height="16" border="0" alt="<?php echo JText::_( 'Delete' ); ?>" onclick="confirmDeleteImage('<?php echo $doc; ?>');" />
				<input type="checkbox" name="rm[]" value="<?php echo $doc; ?>" />
			</td>
		</tr>
		<?php
	}

	function imageStyle()
	{
		?>
		<script language="javascript" type="text/javascript">
		function confirmDeleteImage(file)
		{
			if(confirm("<?php echo JText::_( 'Delete file' ); ?> \""+file+"\"?")) {
				var form = document.getElementById('mediamanager-form');
				form.task.value = 'delete';
				if (top.$('username')) {
					form.username.value = top.$('username').value;
					form.password.value = top.$('password').value;
				}
				var files = document.getElementsByName('rm[]');
				for (var i = 0; i < files.length; i++) {
					files[i].checked = (files[i].value == file);
				}
				form.submit();
			}
		}
		function confirmDeleteFolder(folder, numFiles)
		{
			if(numFiles > 0) {
				alert("<?php echo JText::_( 'There are', true ); ?> "+numFiles+" <?php echo JText::_( 'files/folders in' ); ?> \""+folder+"\".\n\n<?php echo JText::_( 'Please delete all files/folder in' ); ?> \""+folder+"\" <?php echo JText::_( 'first.' ); ?>");
				return false;
			}

			if(confirm("<?php echo JText::_( 'Delete folder', true ); ?> \""+folder+"\"?")) {
				var form = document.getElementById('mediamanager-form');
				form.task.value = 'delete';
				if (top.$('username')) {
					form.username.value = top.$('username').value;
					form.password.value = top.$('password').value;
				}
				var folders = document.getElementsByName('rm[]');
				for (var i = 0; i < folders.length; i++) {
					folders[i].checked = (folders[i].value == folder);
				}
				form.submit();
			}
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
		$lang	=& JFactory::getLanguage();
		$doc	=& JFactory::getDocument();

		$doc->addScript('../includes/js/dtree/dtree.js');
		$cssfile = $lang->isRTL() ? 'dtree_rtl.css': 'dtree.css';
		$doc->addStyleSheet('../includes/js/dtree/'.$cssfile);

		$txt = null;
		foreach($tree as $node) {
			$txt .= "d.add(".$node['id'].", ".$node['pid'].", '".$node['name']."', '".$node['url']."', '".$node['title']."', '".$node['target']."', '../includes/js/dtree/img/folder.gif');\n";
		}
		?>
		<script language="JavaScript" type="text/javascript">
			d = new dTree('d', '../includes/js/dtree/<?php echo $lang->isRTL() ? 'rtl_img': 'img'; ?>/');
			d.config.useCookies = false;
			<?php echo $txt; ?>
			document.write(d);
		</script>
		<?php
	}

	function _loadJS()
	{
		global $mainframe;

		$style = $mainframe->getUserStateFromRequest('media.list.style', 'listStyle', 'thumbs', 'word');
		$base = str_replace("\\","/",JPATH_ROOT);
		$js = "
			var basepath = '".$base.'/images'."';
			var viewstyle = '".$style."';
		" ;

		$doc =& JFactory::getDocument();
		$doc->addScriptDeclaration($js);
	}
}
?>
