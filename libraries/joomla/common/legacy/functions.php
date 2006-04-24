<?php
/**
* @version $Id: legacy.php 1525 2005-12-21 21:08:29Z Jinx $
* @package Joomla.Legacy
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
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
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 */
function mosErrorAlert( $text, $action='window.history.go(-1);', $mode=1 ) {
	return josErrorAlert( $text, $action='window.history.go(-1);', $mode=1 );
}

/**
 * Legacy function, use JPath::clean instead
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 */
function mosPathName($p_path, $p_addtrailingslash = true)
{
	jimport('joomla.filesystem.path');
	return JPath::clean( $p_path, $p_addtrailingslash );
}

/**
 * Legacy function, use JFolder::files or JFolder::folders instead
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 */
function mosReadDirectory( $path, $filter='.', $recurse=false, $fullpath=false  )
{
	$arr = array(null);

	// Get the files and folders
	jimport('joomla.filesystem.folder');
	$files   = JFolder::files($path, $filter, $recurse, $fullpath);
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
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
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
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 */
function mosMail($from, $fromname, $recipient, $subject, $body, $mode=0, $cc=NULL, $bcc=NULL, $attachment=NULL, $replyto=NULL, $replytoname=NULL ) {
	josMail($from, $fromname, $recipient, $subject, $body, $mode=0, $cc=NULL, $bcc=NULL, $attachment=NULL, $replyto=NULL, $replytoname=NULL );
}

/**
 * Legacy function, use josSendAdminMail instead
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 */
function mosSendAdminMail( $adminName, $adminEmail, $email, $type, $title, $author ) {
	josSendAdminMail( $adminName, $adminEmail, $email, $type, $title, $author );
}

/**
 * Legacy function, use JAuthenticateHelper::genRandomPassword() instead
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 */
function mosMakePassword() {
	return JAuthenticateHelper::genRandomPassword();
}

/**
 * Legacy function, use josRedirect instead
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 */
function mosRedirect( $url, $msg='' ) {
	josRedirect( $url, $msg );
}

/**
 * Legacy function, use JFolder::create
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 */
function mosMakePath($base, $path='', $mode = NULL) {

	if ($mode===null) {
		$mode = 0755;
	}

	jimport('joomla.filesystem.folder');
	return JFolder::create($base.$path, $mode);
}

/**
 * Legacy function, use JPath::setPermissions instead
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 */
function mosChmod( $path ) {
	jimport('joomla.filesystem.path');
	return JPath::setPermissions( $path );
}

/**
 * Legacy function, use JPath::setPermissions instead
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 */
function mosChmodRecursive( $path, $filemode=NULL, $dirmode=NULL ) {
	jimport('joomla.filesystem.path');
	return JPath::setPermissions( $path, $filemode, $dirmode );
}

/**
 * Legacy function, use JPath::canCHMOD
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 */
function mosIsChmodable( $file ) {
	jimport('joomla.filesystem.path');
	return JPath::canChmod( $file );
}

/**
 * Legacy function, replaced by geshi bot
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
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
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 */
function mosPathWay() {
	mosLoadModule('breadcrumb', -1);
}

/**
 * Legacy function, use JApplication::getBrowser() instead
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 */
function mosGetBrowser( $agent ) {
	$browser = JApplication::getBrowser();
	return $browser->getBrowser();
}

/**
 * Legacy function, use JApplication::getBrowser() instead
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 */
function mosGetOS( $agent ) {
	$browser = JApplication::getBrowser();
	return $browser->getPlatform();
}

/**
 * Legacy function, use JRegsitry instead
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 */
function mosParseParams( $txt ) {

	$registry = new JRegistry();
	$registry->loadINI($txt);
	return $registry->toObject( );
}

/**
 * Legacy function, removed
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 */
function mosLoadComponent( $name ) {
	// set up some global variables for use by the frontend component
	global $mainframe, $database;
	include( $mainframe->getCfg( 'absolute_path' )."/components/com_$name/$name.php" );
}

/**
 * Legacy function, use JEditor::init instead
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 */
function initEditor() {
	jimport( 'joomla.presentation.editor' );
	$editor =& JEditor::getInstance();
	echo $editor->init();
}

/**
 * Legacy function, use JEditor::save or JEditor::getContent instead
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 */
function getEditorContents($editorArea, $hiddenField) {
	jimport( 'joomla.presentation.editor' );
	$editor =& JEditor::getInstance();
	echo $editor->getEditorContents( $hiddenField );
}

/**
 * Legacy function, use JEditor::display instead
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 */
function editorArea($name, $content, $hiddenField, $width, $height, $col, $row) {
	jimport( 'joomla.presentation.editor' );
	$editor =& JEditor::getInstance();
	echo $editor->display($hiddenField, $content, $width, $height, $col, $row);
}

/**
 * Legacy function, handled by JDocument Zlib outputfilter
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 */
function initGzip() {
	global $mosConfig_gzip, $do_gzip_compress;


	// attempt to disable session.use_trans_sid
	ini_set('session.use_trans_sid', false);

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
			// Check for gzip header or northon internet securities
			if ( isset($_SERVER['HTTP_ACCEPT_ENCODING']) ) {
				$encodings = explode(',', strtolower($_SERVER['HTTP_ACCEPT_ENCODING']));
			}
			if ( (in_array('gzip', $encodings) || isset( $_SERVER['---------------']) ) && extension_loaded('zlib') && function_exists('ob_gzhandler') && !ini_get('zlib.output_compression') && !ini_get('session.use_trans_sid') ) {
				// You cannot specify additional output handlers if
				// zlib.output_compression is activated here
				ob_start( 'ob_gzhandler' );
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
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
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

/**
 * Legacy function, using <jdoc:exists> instead
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 */
function mosCountAdminModules(  $position='left' ) {
	global $mainframe;
	$document =& $mainframe->getDocument();
	return count($document->getModules($position));
}

/**
 * Legacy function, using <jdoc:include type="component" /> instead
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 */
function mosMainBody_Admin() {
	?><jdoc:include type="component" /><?php
}

/**
 * Legacy function, using <jdoc:include type="modules" /> instead
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 */

function mosLoadAdminModules( $position='left', $style=0 ) {
	?><jdoc:include type="modules" name="<?php echo $position ?>" style="<?php echo $style ?>" /><?php
}

/**
 * Legacy function, using <jdoc:include type="module" /> instead
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 */
function mosLoadAdminModule( $name, $style=0 ) {
	?><jdoc:include type="module" name="<?php echo $name ?>" style="<?php echo $style ?>" /><?php
}

/**
 * Legacy function, using <jdoc:include type="head" /> instead
 *
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 */
function mosShowHead_Admin() {
	?><jdoc:include type="head" /><?php
}
?>