<?php
/**
* @version $Id: admin.media.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Massmail
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

// ensure user has access to this function
if (!($acl->acl_check( 'administration', 'edit', 'users', $my->usertype, 'components', 'all' )
 | $acl->acl_check( 'com_media', 'manage', 'users', $my->usertype, 'components', 'com_media' ))) {
	mosRedirect( 'index2.php', $_LANG->_('NOT_AUTH') );
}

mosFS::load( '@admin_html' );

$listdir = mosGetParam( $_REQUEST, 'listdir', 'images' );
$listdir = mosFS::getNativePath( $listdir, false );

// check for snooping
mosFS::check( mosFS::getNativePath( $mosConfig_absolute_path . '/' . $listdir ) );

// base path of images directory
$basePath 	= mosFS::getNativePath( $mosConfig_absolute_path . '/images' );
$baseUrl 	= $mosConfig_live_site .'/images';

switch ($task) {
	case 'cancel':
		mosRedirect( 'index2.php' );
		break;

	case 'upload':
		upload();
		break;

	// popup directory creation interface for use by components
	case 'popupDirectory':
		HTML_Media::popupDirectory( $basePath );
		break;

	// popup upload interface for use by components
	case 'popupUpload':
		HTML_Media::popupUpload( $basePath );
		break;

	case 'create_folder':
		if ( ini_get('safe_mode') == 'On' ) {
			$msg = $_LANG->_( 'WARNDIRCREATIONNOTALLOWEDSAFEMODE' );
			mosErrorAlert( $msg );
		} else {
			create_folder( $listdir );
		}
		break;

	case 'delete':
		delete_file( $delFile, $listdir );
		showMedia($listdir);
		break;

	case 'deletefolder':
		delete_folder( $delFolder, $listdir );
		showMedia( $listdir );
		break;

	case 'list':
		listImages( $listdir );
		break;

	default:
		showMedia( $listdir );
		break;
}


/**
* Show media manager
* @param string The image directory to display
*/
function showMedia( $listdir ) {
	global $basePath;

	$lists['folders'] 		= mosFS::listFolderTree( $GLOBALS['mosConfig_absolute_path'] . '/images', '.' );
	$lists['folders-media'] = mosFS::listFolderTree( $GLOBALS['mosConfig_absolute_path'] . '/media', '.' );
	$lists['filelist']		= mosFS::listFiles( $GLOBALS['mosConfig_absolute_path'] . '/' . $listdir );

	mediaScreens::view( $listdir, $lists );
}

function delete_file( $delfile, $listdir ) {
	global $mosConfig_absolute_path, $_LANG;

	$file = mosFS::getNativePath( $mosConfig_absolute_path . '/' . $listdir .'/'. $delfile, false );
	if (mosFS::deleteFile( $file )) {
		$msg = $_LANG->_( 'Success' );
	} else {
		$msg = $_LANG->_( 'Failed' );
	}
	mosRedirect( 'index2.php?option=com_media&listdir='.$listdir, $msg );
}

function create_folder( $dirPath ) {
	global $mosConfig_absolute_path;
	global $_LANG;

	$folder_name = mosGetParam( $_POST, 'foldername', '' );
	// error check
	if ( !$folder_name ) {
		mosErrorAlert( $_LANG->_( 'Please enter the name of the directory you want to create' ) );
	}

	if (strlen( $folder_name ) > 0) {
		if (eregi("[^0-9a-zA-Z_]", $folder_name)) {
			mosErrorAlert( $_LANG->_( 'WARNDIRNAMEMUSTCONTAINALPHACHARACTERS' ) );
		}
		$folder = mosFS::getNativePath( $mosConfig_absolute_path . '/' . $dirPath . '/'. $folder_name, false );

		if (!is_dir( $folder ) && !is_file( $folder )) {
			mosFS::autocreatePath( $folder );
			$fp = fopen( $folder .'/index.html', 'w' );
			fwrite( $fp, '<html><body></body></html>' );
			fclose( $fp );
			mosFS::CHMOD( $folder . '/index.html' );
			$refresh_dirs = true;
		}
	}

	mosRedirect( 'index2.php?option=com_media&listdir=' . $dirPath, $_LANG->_( 'Directory successfully Created' ) );
}

function delete_folder( $delFolder, $listdir ) {
	global $basePath;
	global $_LANG;

	$del_html 	= $basePath .$listdir . $delFolder .'/index.html';
	$del_folder = $basePath .$listdir . $delFolder;

	$entry_count = 0;
	$dir = opendir( $del_folder );
	while ( $entry = readdir( $dir )) {
		if( $entry != "." & $entry != ".." & strtolower( $entry ) != 'index.html' )
		$entry_count++;
	}
	closedir( $dir );

	if ( $entry_count < 1 ) {
		@unlink( $del_html );
		rmdir( $del_folder );
	} else {
		echo '<font color="red">'. $_LANG->_( 'Unable to delete: not empty!' ) .'</font>';
	}
}

function upload() {
	global $basePath;
	global $_LANG;
	// error check
	if ( !isset( $_FILES['upload'] ) ) {
		mosErrorAlert( $_LANG->_( 'Please Select a file to Upload' ) );
	}

	if ( isset( $_FILES['upload'] ) && is_array( $_FILES['upload'] ) && isset( $_POST['dirPath'] ) ) {
		$dirPathPost = $_POST['dirPath'];

		if ( strlen( $dirPathPost ) > 0 ) {
			if( substr( $dirPathPost, 0, 1 ) == '/' ) {
				$IMG_ROOT .= $dirPathPost;
			} else {
				$IMG_ROOT = $dirPathPost;
			}
		}

		if ( strrpos( $IMG_ROOT, '/') != strlen( $IMG_ROOT ) - 1 ) {
			$IMG_ROOT .= '/';
		}

		do_upload( $_FILES['upload'], $basePath . $dirPathPost. '/' );
	}
}

function do_upload( $file, $dest_dir ) {
	global $clearUploads;
	global $_LANG;

	if ( file_exists( $dest_dir . $file['name'] ) ) {
		mosErrorAlert( $_LANG->_( 'Upload FAILED.File already exists' ) );
	}

	if ( ( strcasecmp( substr( $file['name'], -4 ), '.gif' ) ) && ( strcasecmp( substr($file['name'],-4),'.jpg')) && (strcasecmp(substr($file['name'],-4),'.png')) && (strcasecmp(substr($file['name'],-4),'.bmp')) &&(strcasecmp(substr($file['name'],-4),'.doc')) && (strcasecmp(substr($file['name'],-4),'.xls')) && (strcasecmp(substr($file['name'],-4),'.ppt')) && ( strcasecmp( substr( $file['name'], -4 ), '.swf' ) ) && ( strcasecmp( substr( $file['name'], -4 ), '.pdf' ) ) && ( strcasecmp( substr( $file['name'], -4 ), '.xcf' ) ) && ( strcasecmp( substr( $file['name'], -4 ), '.txt' ) ) ) {
		mosErrorAlert( $_LANG->_( 'Only files of type' ) .' gif, png, jpg, bmp, pdf, swf, txt, doc, xls, xcf '. $_LANG->_( 'or' ) .' ppt '. $_LANG->_( 'can be uploaded' ) );
	}

	if ( !move_uploaded_file( $file['tmp_name'], $dest_dir.strtolower($file['name'] ) ) ){
		mosErrorAlert( $_LANG->_( 'Upload FAILED' ) );
	} else {
		mosFS::CHMOD( $dest_dir . strtolower( $file['name'] ) );

		// kill popup window
		?>
		<script language="javascript" type="text/javascript">
		<!--
		alert( '<?php echo $_LANG->_( 'File successfully Uploaded' ); ?>' );
		onLoad = window.close( 'win1' );
		// reload main window
		opener.location.href ='index2.php?option=com_media';
		-->
		</script>
		<?php
		exit;
	}

	$clearUploads = true;
}

function rm_all_dir($dir) {
	if ( is_dir( $dir ) ) {
		$d = @dir( $dir );

		while ( false !== ( $entry = $d->read() ) ) {
			if($entry != '.' && $entry != '..') {
				$node = $dir .'/'. $entry;
				if ( is_file( $node ) ) {
					unlink( $node );
				} else if( is_dir( $node ) ) {
					rm_all_dir($node);
				}
			}
		}
		$d->close();

		rmdir($dir);
	}
}

function parse_size( $size ){
	if ( $size < 1024 ) {
		return $size.' bytes';
	} else if ( $size >= 1024 && $size < 1024 * 1024 ) {
		return sprintf( '%01.2f', $size / 1024.0 ). ' Kb';
	} else {
		return sprintf( '%01.2f', $size / ( 1024.0 * 1024 ) ) .' Mb';
	}
}

/*
* takes the larger size of the width and height and applies the
* formula accordingly...this is so this script will work
* dynamically with any size image
*/
function imageResize( $width, $height, $target ) {
	if ( $width > $target || $height > $target ) {
		if ( $width > $height ) {
			$percentage = ( $target / $width );
		} else {
			$percentage = ( $target / $height );
		}

		//gets the new value and applies the percentage, then rounds the value
		$width 	= round( $width * $percentage );
		$height = round( $height * $percentage );
	}

	return 'width="'. $width .'" height="'. $height .'"';
}

function imageResize2( &$width, &$height, $target ) {
	if ( $width > $target || $height > $target ) {
		if ( $width > $height ) {
			$percentage = ( $target / $width );
		} else {
			$percentage = ( $target / $height );
		}

		//gets the new value and applies the percentage, then rounds the value
		$width 	= round( $width * $percentage );
		$height = round( $height * $percentage );
	}
}

function num_files( $dir ) {
	$files = mosFS::listFiles( $dir );
	return count( $files );
}
?>