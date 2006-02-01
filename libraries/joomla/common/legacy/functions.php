<?php
/**
* @version $Id: legacy.php 1525 2005-12-21 21:08:29Z Jinx $
* @package Joomla.Legacy
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
* Legacy function, use josErrorAlert instead
* 
* @deprecated As of version 1.1
* @package Joomla.Legacy
*/
function mosErrorAlert( $text, $action='window.history.go(-1);', $mode=1 ) {
	return josErrorAlert( $text, $action='window.history.go(-1);', $mode=1 );
}

/**
* Legacy function, use JPath::clean instead
* 
* @deprecated As of version 1.1
* @package Joomla.Legacy
*/
function mosPathName($p_path, $p_addtrailingslash = true) {
	return JPath::clean( $p_path, $p_addtrailingslash );
}

/**
* Legacy function, use JFolder::files or JFolder::folders instead
* 
* @deprecated As of version 1.1
* @package Joomla.Legacy
*/
function mosReadDirectory( $path, $filter='.', $recurse=false, $fullpath=false  ) {
	$arr = array(null);

	// Get the files and folders
	$files = JFolder::files($path, $filter, $recurse, $fullpath);
	$folders = JFolder::folders($path, $filter, $recurse, $fullpath);
	// Merge files and folders into one array
	$arr = array_merge($files, $folders);
	// Sort them all
	asort($arr);
	return $arr;
}

/**
* Legacy function, use JFactory::getMailer() instead
* 
* @deprecated As of version 1.1
* @package Joomla.Legacy
*/
function mosCreateMail( $from='', $fromname='', $subject, $body ) {

	$mail =& JFactory::getMailer();

	$mail->From 	= $from ? $from : $mail->From;
	$mail->FromName = $fromname ? $fromname : $mail->FromName;
	$mail->Subject 	= $subject;
	$mail->Body 	= $body;

	return $mail;
}

/**
* Legacy function, use josMail instead
* 
* @deprecated As of version 1.1
* @package Joomla.Legacy
*/
function mosMail($from, $fromname, $recipient, $subject, $body, $mode=0, $cc=NULL, $bcc=NULL, $attachment=NULL, $replyto=NULL, $replytoname=NULL ) {
	josMail($from, $fromname, $recipient, $subject, $body, $mode=0, $cc=NULL, $bcc=NULL, $attachment=NULL, $replyto=NULL, $replytoname=NULL );
}

/**
* Legacy function, use josSendAdminMail instead
* 
* @deprecated As of version 1.1
* @package Joomla.Legacy
*/
function mosSendAdminMail( $adminName, $adminEmail, $email, $type, $title, $author ) {
	josSendAdminMail( $adminName, $adminEmail, $email, $type, $title, $author );
}

/**
* Legacy function, use JAuthenticateHelper::genRandomPassword() instead
* 
* @deprecated As of version 1.1
* @package Joomla.Legacy
*/
function mosMakePassword() {
	return JAuthenticateHelper::genRandomPassword();
}

/**
* Legacy function, use josRedirect instead
* 
* @deprecated As of version 1.1
* @package Joomla.Legacy
*/
function mosRedirect( $url, $msg='' ) {
	josRedirect( $url, $msg );
}

/**
 * Legacy function, use JFolder::delete($path)
 * 
 * @deprecated As of version 1.1
 * @package Joomla.Legacy
 */
function deldir( $dir ) {
	$current_dir = opendir( $dir );
	$old_umask = umask(0);
	while ($entryname = readdir( $current_dir )) {
		if ($entryname != '.' and $entryname != '..') {
			if (is_dir( $dir . $entryname )) {
				deldir( mosPathName( $dir . $entryname ) );
			} else {
                @chmod($dir . $entryname, 0777);
				unlink( $dir . $entryname );
			}
		}
	}
	umask($old_umask);
	closedir( $current_dir );
	return rmdir( $dir );
}

/**
* Legacy function, use JFolder::create
* 
* @deprecated As of version 1.1
* @package Joomla.Legacy
*/
function mosMakePath($base, $path='', $mode = NULL) {
	return JFolder::create($base.$path, $mode);
}

/**
 * Legacy function, use JPath::setPermissions instead
 * 
 * @deprecated As of version 1.1
 * @package Joomla.Legacy
 */
function mosChmod( $path ) {
	return JPath::setPermissions( $path );
}

/**
 * Legacy function, use JPath::setPermissions instead
 * 
 * @deprecated As of version 1.1
 * @package Joomla.Legacy
 */
function mosChmodRecursive( $path, $filemode=NULL, $dirmode=NULL ) {
	return JPath::setPermissions( $path, $filemode, $dirmode );
}

/**
* Legacy function, use JPath::canCHMOD
* 
* @deprecated As of version 1.1
* @package Joomla.Legacy
*/
function mosIsChmodable( $file ) {
	return JPath::canChmod( $file );
}

/**
* Legacy function, replaced by geshi bot
* 
* @deprecated As of version 1.1
* @package Joomla.Legacy
*/
function mosShowSource( $filename, $withLineNums=false ) {

	ini_set('highlight.html', '000000');
	ini_set('highlight.default', '#800000');
	ini_set('highlight.keyword','#0000ff');
	ini_set('highlight.string', '#ff00ff');
	ini_set('highlight.comment','#008000');

	if (!($source = @highlight_file( $filename, true ))) {
		return JText::_( 'Operation Failed' );
	}
	$source = explode("<br />", $source);

	$ln = 1;

	$txt = '';
	foreach( $source as $line ) {
		$txt .= "<code>";
		if ($withLineNums) {
			$txt .= "<font color=\"#aaaaaa\">";
			$txt .= str_replace( ' ', '&nbsp;', sprintf( "%4d:", $ln ) );
			$txt .= "</font>";
		}
		$txt .= "$line<br /><code>";
		$ln++;
	}
	return $txt;
}

/**
* Legacy function, use mosLoadModule('breadcrumbs); instead
* 
* @deprecated As of version 1.1
* @package Joomla.Legacy
*/
function mosPathWay() {
	mosLoadModule('breadcrumb', -1);
}

/**
* Legacy function, use JApplication::getBrowser() instead
* 
* @deprecated As of version 1.1
* @package Joomla.Legacy
*/
function mosGetBrowser( $agent ) {
	$browser = JApplication::getBrowser();
	return $browser->getBrowser();
}

/**
* Legacy function, use JApplication::getBrowser() instead
* 
* @deprecated As of version 1.1
* @package Joomla.Legacy
*/
function mosGetOS( $agent ) {
	$browser = JApplication::getBrowser();
	return $browser->getPlatform();
}

/**
* Legacy function, use JRegsitry instead
* 
* @deprecated As of version 1.1
* @package Joomla.Legacy
*/
function mosParseParams( $txt ) {
	
	$registry = new JRegistry();
	$registry->loadINI($txt);
	return $registry->toObject( );
}

/**
* Legacy function, removed
* 
* @deprecated As of version 1.1
* @package Joomla.Legacy
*/
function mosLoadComponent( $name ) {
	// set up some global variables for use by the frontend component
	global $mainframe, $database;
	include( $mainframe->getCfg( 'absolute_path' )."/components/com_$name/$name.php" );
}

/**
 * Legacy function, use JEditor::init instead
 * 
 * @deprecated As of version 1.1
 * @package Joomla.Legacy
 */
function initEditor() {
	$editor =& JEditor::getInstance();
	echo $editor->init();
}

/**
 * Legacy function, use JEditor::getEditorContents instead
 * 
 * @deprecated As of version 1.1
 * @package Joomla.Legacy
 */
function getEditorContents($editorArea, $hiddenField) {
	$editor =& JEditor::getInstance();
	echo $editor->getEditorContents();
}

/**
 * Legacy function, use JEditor::getEditor instead
 * 
 * @deprecated As of version 1.1
 * @package Joomla.Legacy
 */
function editorArea($name, $content, $hiddenField, $width, $height, $col, $row) {
	$editor =& JEditor::getInstance();
	echo $editor->getEditor();
}

/**
* Legacy function, handled by JDocument Zlib outputfilter
* 
* @deprecated As of version 1.1
* @package Joomla.Legacy
*/
function initGzip() {
	global $mosConfig_gzip, $do_gzip_compress;
	$do_gzip_compress = FALSE;
	if ($mosConfig_gzip == 1) {
		$phpver = phpversion();
		$useragent = mosGetParam( $_SERVER, 'HTTP_USER_AGENT', '' );
		$canZip = mosGetParam( $_SERVER, 'HTTP_ACCEPT_ENCODING', '' );

		if ( $phpver >= '4.0.4pl1' &&
				( strpos($useragent,'compatible') !== false ||
				  strpos($useragent,'Gecko')	  !== false
				)
			) {
			if ( extension_loaded('zlib') && !ini_get('zlib.output_compression')) {
				// You cannot specify additional output handlers if
				// zlib.output_compression is activated here
				ob_start("ob_gzhandler" );
				return;
			}
		} else if ( $phpver > '4.0' ) {
			if ( strpos($canZip,'gzip') !== false ) {
				if (extension_loaded( 'zlib' )) {
					$do_gzip_compress = TRUE;
					ob_start();
					ob_implicit_flush(0);

					header( 'Content-Encoding: gzip' );
					return;
				}
			}
		}
	}
	ob_start();
}

/**
* Legacy function, handled by JDocument Zlib outputfilter
* 
* @deprecated As of version 1.1
* @package Joomla.Legacy
*/
function doGzip() {
	global $do_gzip_compress;
	if ( $do_gzip_compress ) {
		/**
		*Borrowed from php.net!
		*/
		$gzip_contents = ob_get_contents();
		ob_end_clean();

		$gzip_size = strlen($gzip_contents);
		$gzip_crc = crc32($gzip_contents);

		$gzip_contents = gzcompress($gzip_contents, 9);
		$gzip_contents = substr($gzip_contents, 0, strlen($gzip_contents) - 4);

		echo "\x1f\x8b\x08\x00\x00\x00\x00\x00";
		echo $gzip_contents;
		echo pack('V', $gzip_crc);
		echo pack('V', $gzip_size);
	} else {
		ob_end_flush();
	}
}
?>