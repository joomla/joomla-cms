<?php
/**
* @version $Id: admin.media.html.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Massmail
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_VALID_MOS') or die('Restricted access');

/**
 * @package Joomla
 * @subpackage Polls
 */
class mediaScreens {
	/**
	 * Static method to create the template object
	 * @param array An array of other standard files to include
	 * @return patTemplate
	 */
	function & createTemplate($files = null) {
		$tmpl = & mosFactory :: getPatTemplate( $files );
		$tmpl->setRoot(dirname(__FILE__).'/tmpl');

		return $tmpl;
	}

	/**
	* List languages
	* @param array
	*/
	function view( $listdir, &$lists, $style = 'icons' ) {
		$tmpl = & mediaScreens :: createTemplate();
		$tmpl->setAttribute('body', 'src', 'view.html');

		$tmpl->readTemplatesFromInput( 'list_' . $style . '.html' );

		$tmpl->addVar('form', 'formEnctype', 'multipart/form-data');

		$tmpl->addVar( 'body', 'path', $listdir);
		$tmpl->addRows( 'files-list', $lists['folders']);
		$tmpl->addRows( 'files-list-media', $lists['folders-media'] );

		$files = listImages( $listdir );
		$tmpl->addRows( 'list-files', $files );

		$tmpl->addVar( 'body', 'post_max_size', ini_get('post_max_size'));
		$tmpl->addVar( 'body', 'upone', dirname( $listdir ) );
		$tmpl->addGlobalVar( 'listdir', $listdir );

		$tmpl->displayParsedTemplate('form');
	}
}

/**
* Build imagelist
* @param string The image directory to display
*/
function listImages( $listdir ) {
	global $mosConfig_absolute_path;
	global $mosConfig_live_site;

	$path = mosFS :: getNativePath( $mosConfig_absolute_path . DIRECTORY_SEPARATOR . $listdir);

	// get list of images for directory
	$regex 		= '\.(bmp|gif|jpg|png|txt|swf|pdf|xcf)$';
	$imgRegex 	= '\.(bmp|gif|jpg|png|xcf)$';

	if ( !is_dir( $path ) ) {
		//HTML_Media :: draw_no_dir();
		return;
	}

	$f = mosFS :: listFolders( $path, '.' );
	$d = mosFS :: listFiles( $path, $regex );
	$files = array();

	if (count( $d ) < 1) {
		//HTML_Media :: draw_no_results();
		return;
	}

	foreach ( $f as $folder ) {
		$temp = array(
			'type' 			=> 'folder',
			'name' 			=> $folder,
			'path' 			=> $path . $folder,
			'numfiles' 		=> num_files( $path . $folder )
		);
		$files[] = $temp;
	}

	foreach ( $d as $file ) {
		$filepath = $path.$file;
		if (eregi($imgRegex, $file)) {
			// get info of image file
			$image_info = @ getimagesize( $filepath );
			$temp = array(
				'type' 		=> 'image',
				'name' 		=> $file,
				'path' 		=> $filepath,
				'url' 		=> '/' . str_replace( '\\', '/', $listdir ) . '/' . $file,
				'width' 	=> $image_info[0],
				'height' 	=> $image_info[1],
				'pwidth' 	=> $image_info[0] + 70,
				'pheight' 	=> $image_info[1] + 70,
				'size' 		=> parse_size( filesize( $filepath ) )
			);

			if ( $image_info[0] < 85 && $image_info[1] < 40  ) {
				$temp['padding'] = 'class="image"';
			}

			if ( $image_info[0] > 85 || $image_info[1] > 85 ) {
				imageResize2( $image_info[0], $image_info[1], 85 );
			}
			$temp['iwidth'] 	= $image_info[0];
			$temp['iheight'] 	= $image_info[1];

		} else {
			// document files
			$temp = array(
				'type' 		=> 'doc',
				'name' 		=> $file,
				'path' 		=> $filepath,
				'size' 		=> parse_size( filesize( $filepath ) )
			);
		}

		$files[] = $temp;
	}

	//now sort the folders and images by name.

	// handling for document file display
	// TODO Still need to do docs
	/*
	foreach ($docs as $doc_name => $doc) {
		$doc_name = key($docs);
		$iconfile = $mosConfig_absolute_path.'/administrator/images/'.substr($doc_name, -3).'_16.png';

		// represent documents with icon images
		if (file_exists($iconfile)) {
			$icon = 'images/'. (substr($doc_name, -3)).'_16.png';
		} else {
			$icon = "images/con_info.png";
		}

		//HTML_Media :: show_doc($doc['file'], $listdir, $icon, $doc['size']);
	}
	*/

	return $files;
}

/**
* @package Joomla
* @subpackage Massmail
*/
class HTML_Media {

	// Built in function of dirname is faulty
	// It assumes that the directory nane can not contain a . (period)
	function dir_name( $dir ) {
		$lastSlash = intval( strrpos( $dir, '/' ) );
		if ($lastSlash == strlen($dir) - 1) {
			return substr($dir, 0, $lastSlash);
		} else {
			return dirname($dir);
		}
	}

	function draw_no_results() {
		global $_LANG;
		?>
		<div>
			<div align="center" class="noimages">
				<?php echo $_LANG->_( 'No Files Found' ); ?>
			</div>
		</div>
	  	<?php

	}

	function draw_no_dir() {
		global $_LANG;
		?>
		<div class="nodirectory">
			<?php echo $_LANG->_( 'Configuration Problem' ); ?>:
			&quot;/images/stories&quot;
			<?php echo $_LANG->_( 'does not exist.' ); ?>
		</div>
		<?php

	}

	function show_doc($doc, $listdir, $icon, $size) {
		global $mosConfig_absolute_path, $mosConfig_live_site;
		global $_LANG;

		$del_link = 'index2.php?option=com_media&amp;task=delete&amp;delFile='.$doc.'&amp;listdir='.$listdir;

		$filesize = parse_size($size);

		$path = $mosConfig_live_site.str_replace('\\', '/', $listdir).$doc;
		$text = $_LANG->_('Insert your text here');
		$onclick = "javascript:window.top.document.adminForm.imagecode.value = '<a href=&quot;$path&quot;>$text</a>';";

		$text = $doc;
		$text .= '<br/>'.$filesize;
		$text = htmlspecialchars( $text );
		$caption = $_LANG->_('File Information');
		$onmouseover = "this.T_BGIMG='$mosConfig_live_site/images/M_images/tt_bg.jpg';this.T_WIDTH=200;return escape('$text')";

		$alt_title = 'alt="'.$_LANG->_('Delete File').'" title="'.$_LANG->_('Delete File').'"';
		?>
		<div style="float:left;">
			<div align="center" class="imgBorder">
				<a href="#" onclick="<?php echo $onclick; ?>"  onmouseover="<?php echo $onmouseover; ?>" onMouseOut="return nd();">
				<div class="image">
					<img border="0" src="<?php echo $icon ?>" alt="<?php echo $doc; ?>" width="30" height="30" />
				</div>
				</a>
			</div>
			<div class="imginfoBorder">
				<?php echo $doc; ?>
				<div class="buttonOut">
					<a href="#" onclick="<?php echo $onclick; ?>">
					<img src="images/edit_pencil.gif" width="15" height="15" border="0" alt="<?php echo $_LANG->_( 'Generate Code' ); ?>" title="<?php echo $_LANG->_( 'Generate Code' ); ?>" />
					</a>
					<a href="<?php echo $del_link; ?>" target="_top" onclick="return deleteImage('<?php echo $doc; ?>');">
					<img src="images/edit_trash.gif" width="15" height="15" border="0" <?php echo $alt_title; ?>/>
					</a>
			</div>
			</div>
		</div>
		<?php
	}

	function popupUpload( $basePath ) {
		global $mosConfig_absolute_path;
		global $_LANG;

		$imgFiles 	= mosFS::listFolders( $basePath, '.', true, true );
		$folders 	= array();
		$folders[] 	= mosHTML::makeOption( '/' );

		$len = strlen( $basePath );
		foreach ( $imgFiles as $file ) {
			$folders[] = mosHTML::makeOption( str_replace( '\\', '/', substr( $file, $len ) ) );
		}

		if ( is_array( $folders ) ) {
			sort( $folders );
		}
		// create folder selectlist
		$dirPath = mosHTML::selectList( $folders, 'dirPath', 'class="inputbox" size="1" ', 'value', 'text', '.' );
		?>
		<form method="post" action="index2.php" enctype="multipart/form-data" name="adminForm">

		<table id="toolbar">
		<tr>
			<td>
			<?php echo mosAdminHTML::imageCheck( 'mediamanager.png', '/administrator/images/', NULL, NULL, $_LANG->_( 'Upload a File' ), 'upload' ); ?>
			</td>
			<td class="title">
			<?php echo $_LANG->_( 'Upload a File' ); ?>
			</td>
		</tr>
		</table>

		<table class="adminform">
		<tr>
			<td colspan="2">
			<?php echo $_LANG->_( 'Select File' ); ?>&nbsp;&nbsp;&nbsp;
			[ <?php echo $_LANG->_( 'Max size' ); ?> = <?php echo ini_get( 'post_max_size' );?> ]
			<br/>
			<input class="inputbox" name="upload" type="file" size="70" />
			</td>
		</tr>
		<tr>
			<td colspan="2">
			<?php echo $_LANG->_( 'Destination Sub-folder' ); ?>: <?php echo $dirPath; ?>
			</td>
		</tr>
		<tr>
			<td>
			<input class="button" type="button" value="<?php echo $_LANG->_( 'Upload' ); ?>" name="fileupload" onclick="javascript:submitbutton('upload')" />
			</td>
			<td>
			<div align="right">
			<input class="button" type="button" value="<?php echo $_LANG->_( 'Close' ); ?>" onclick="javascript:window.close();" align="right" />
			</div>
			</td>
		</tr>
		</table>

		<input type="hidden" name="option" value="com_media" />
		<input type="hidden" name="task" value="" />
		</form>
		<?php
	}

	function popupDirectory( $basePath ) {
		global $_LANG;

		$imgFiles 	= mosFS::listFolders( $basePath, '.', true, true );
		$folders 	= array();
		$folders[] 	= mosHTML::makeOption( '/' );

		$len = strlen( $basePath );
		foreach ( $imgFiles as $file ) {
			$folders[] = mosHTML::makeOption( str_replace( '\\', '/', substr( $file, $len ) ) );
		}

		if ( is_array( $folders ) ) {
			sort( $folders );
		}
		// create folder selectlist
		$dirPath = mosHTML::selectList( $folders, 'dirPath', 'class="inputbox" size="1"', 'value', 'text', '.' );
		?>
		<form action="index2.php" name="adminForm" method="post">

		<table id="toolbar">
		<tr>
			<td>
			<?php echo mosAdminHTML::imageCheck( 'module.png', '/administrator/images/', NULL, NULL, $_LANG->_( 'Upload a File' ), 'upload' ); ?>
			</td>
			<td class="title">
			<?php echo $_LANG->_( 'Create a Directory' ); ?>
			</td>
		</tr>
		</table>

		<table class="adminform">
		<tr>
			<td colspan="2">
			<?php echo $_LANG->_( 'Directory Name' ); ?>
			<br/>
			<input class="inputbox" name="foldername" type="text" size="60" />
			</td>
		</tr>
		<tr>
			<td colspan="2">
			<?php echo $_LANG->_( 'Parent Directory' ); ?>: <?php echo $dirPath; ?>
			</td>
		</tr>
		<tr>
			<td>
			<input class="button" type="button" value="<?php echo $_LANG->_( 'Create' ); ?>" onclick="javascript:submitbutton('newdir')" />
			</td>
			<td>
			<div align="right">
			<input class="button" type="button" value="<?php echo $_LANG->_( 'Close' ); ?>" onclick="javascript:window.close();" align="right" />
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