<?php
/**
 * @version		$Id$
 * @package		Joomla.Legacy
 * @subpackage	1.5
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

/**
 * Legacy function, use <jdoc:exists type="modules" condition="{POSITION}" /> instead
 *
 * @deprecated	As of version 1.5
 */
function mosCountModules( $position='left' ) {
	jimport('joomla.application.module.helper');
	return count(JModuleHelper::getModules($position));
}

/**
 * Legacy function, use <jdoc:include type="component" /> instead
 *
 * @deprecated	As of version 1.5
 */
function mosMainBody() {
	?><jdoc:include type="component" /><?php
}

/**
 * Legacy function, use <jdoc:include type="modules" /> instead
 *
 * @deprecated		As of version 1.5
 */
function mosLoadModules( $position='left', $style=0 )
{
	// Select the module chrome function
	if (is_numeric($style))
	{
		switch ( $style )
		{
			case -3:
				$style = 'rounded';
				break;

			case -2:
				$style = 'xhtml';
				break;

			case -1:
				$style = 'raw';
				break;

			case 0  :
			default :
				$style = 'table';
				break;
		}
	}
	?><jdoc:include type="modules" name="<?php echo $position ?>" style="<?php echo $style ?>"/><?php
}

/**
 * Legacy function, use <jdoc:include type="module" /> instead
 *
 * @deprecated		As of version 1.5
 */
function mosLoadModule( $name, $style=-1 ) {
	?><jdoc:include type="module" name="<?php echo $name ?>" style="<?php echo $style ?>" /><?php
}

/**
 * Legacy function, use <jdoc:include type="head" /> instead
 *
 * @deprecated	As of version 1.5
 */
function mosShowHead() {
	?><jdoc:include type="head" /><?php
}

/**
 * Legacy function, using <jdoc:exists> instead
 *
 * @deprecated	As of version 1.5
 */
function mosCountAdminModules(  $position='left' ) {
	$document =& JFactory::getDocument();
	return count($document->getModules($position));
}

/**
 * Legacy function, using <jdoc:include type="component" /> instead
 *
 * @deprecated	As of version 1.5
 */
function mosMainBody_Admin() {
	?><jdoc:include type="component" /><?php
}

/**
 * Legacy function, using <jdoc:include type="modules" /> instead
 *
 * @deprecated	As of version 1.5
 */

function mosLoadAdminModules( $position='left', $style=0 ) {

	// Select the module chrome function
	if (is_numeric($style))
	{
		switch ( $style )
		{
			case 2:
				$style = 'xhtml';
				break;

			case 0  :
			default :
				$style = 'raw';
				break;
		}
	}
	?><jdoc:include type="modules" name="<?php echo $position ?>" style="<?php echo $style ?>" /><?php
}

/**
 * Legacy function, using <jdoc:include type="module" /> instead
 *
 * @deprecated	As of version 1.5
 */
function mosLoadAdminModule( $name, $style=0 ) {
	?><jdoc:include type="module" name="<?php echo $name ?>" style="<?php echo $style ?>" /><?php
}

/**
 * Legacy function, using <jdoc:include type="head" /> instead
 *
 * @deprecated	As of version 1.5
 */
function mosShowHead_Admin() {
	?><jdoc:include type="head" /><?php
}

/**
 * Legacy function, always use {@link JRequest::getVar()} instead
 *
 * @deprecated	As of version 1.5
 */
function mosStripslashes( &$value )
{
	$ret = '';
	if (is_string( $value )) {
		$ret = stripslashes( $value );
	} else {
		if (is_array( $value )) {
			$ret = array();
			foreach ($value as $key => $val) {
				$ret[$key] = mosStripslashes( $val );
			}
		} else {
			$ret = $value;
		}
	}
	return $ret;
}

/**
 * Legacy function, use {@link JArrayHelper JArrayHelper->toObject()} instead
 *
 * @deprecated	As of version 1.5
 */
function mosBindArrayToObject( $array, &$obj, $ignore='', $prefix=NULL, $checkSlashes=true )
{
	if (!is_array( $array ) || !is_object( $obj )) {
		return (false);
	}

	foreach (get_object_vars($obj) as $k => $v)
	{
		if( substr( $k, 0, 1 ) != '_' )
		{
			// internal attributes of an object are ignored
			if (strpos( $ignore, $k) === false)
			{
				if ($prefix) {
					$ak = $prefix . $k;
				} else {
					$ak = $k;
				}
				if (isset($array[$ak])) {
					$obj->$k = ($checkSlashes && get_magic_quotes_gpc()) ? mosStripslashes( $array[$ak] ) : $array[$ak];
				}
			}
		}
	}

	return true;
}

/**
 * Legacy function, use {@link JUtility::getHash()} instead
 *
 * @deprecated	As of version 1.5
 */
function mosHash( $seed ) {
	return JUtility::getHash( $seed );
}

/**
* Legacy function
 *
 * @deprecated	As of version 1.5
*/
function mosNotAuth()
{
	$user =& JFactory::getUser();
	echo JText::_('ALERTNOTAUTH');
	if ($user->get('id') < 1) {
		echo "<br />" . JText::_( 'You need to login.' );
	}
}

/**
 * Legacy function, use (@link JError} or {@link JApplication::redirect()} instead.
 *
 * @deprecated	As of version 1.5
 */
function mosErrorAlert( $text, $action='window.history.go(-1);', $mode=1 )
{
	global $mainframe;

	$text = nl2br( $text );
	$text = addslashes( $text );
	$text = strip_tags( $text );

	switch ( $mode ) {
		case 2:
			echo "<script>$action</script> \n";
			break;

		case 1:
		default:
			echo "<script>alert('$text'); $action</script> \n";
			echo '<noscript>';
			echo "$text\n";
			echo '</noscript>';
			break;
	}

	$mainframe->close();
}

/**
 * Legacy function, use {@link JPath::clean()} instead
 *
 * @deprecated	As of version 1.5
 */
function mosPathName($p_path, $p_addtrailingslash = true)
{
	jimport('joomla.filesystem.path');
	$path = JPath::clean($p_path);
	if ($p_addtrailingslash) {
		$path = rtrim($path, DS) . DS;
	}
	return $path;
}

/**
 * Legacy function, use {@link JFolder::files()} or {@link JFolder::folders()} instead
 *
 * @deprecated	As of version 1.5
 */
function mosReadDirectory( $path, $filter='.', $recurse=false, $fullpath=false  )
{
	$arr = array(null);

	// Get the files and folders
	jimport('joomla.filesystem.folder');
	$files		= JFolder::files($path, $filter, $recurse, $fullpath);
	$folders	= JFolder::folders($path, $filter, $recurse, $fullpath);
	// Merge files and folders into one array
	$arr = array_merge($files, $folders);
	// Sort them all
	asort($arr);
	return $arr;
}

/**
 * Legacy function, use {@link JFactory::getMailer()} instead
 *
 * @deprecated	As of version 1.5
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
 * Legacy function, use {@link JUtility::sendMail()} instead
 *
 * @deprecated	As of version 1.5
 */
function mosMail($from, $fromname, $recipient, $subject, $body, $mode=0, $cc=NULL, $bcc=NULL, $attachment=NULL, $replyto=NULL, $replytoname=NULL ) {
	return JUTility::sendMail($from, $fromname, $recipient, $subject, $body, $mode, $cc, $bcc, $attachment, $replyto, $replytoname );
}

/**
 * Legacy function, use {@link JUtility::sendAdminMail()} instead
 *
 * @deprecated	As of version 1.5
 */
function mosSendAdminMail( $adminName, $adminEmail, $email, $type, $title, $author ) {
	JUtility::sendAdminMail( $adminName, $adminEmail, $email, $type, $title, $author );
}

/**
 * Legacy function, use {@link JUserHelper::genRandomPassword()} instead
 *
 * @deprecated	As of version 1.5
 */
function mosMakePassword() {
	jimport('joomla.user.helper');
	return JUserHelper::genRandomPassword();
}

/**
 * Legacy function, use {@link JApplication::redirect() JApplication->redirect()} instead
 *
 * @deprecated	As of version 1.5
 */
function mosRedirect( $url, $msg='' ) {
	global $mainframe;
	$mainframe->redirect( $url, $msg );
}

/**
 * Legacy function, use {@link JFolder::create()}
 *
 * @deprecated	As of version 1.5
 */
function mosMakePath($base, $path='', $mode = NULL) {

	if ($mode===null) {
		$mode = 0755;
	}

	jimport('joomla.filesystem.folder');
	return JFolder::create($base.$path, $mode);
}

/**
 * Legacy function, use {@link JArrayHelper::toInteger()} instead
 *
 * @deprecated	As of version 1.5
 */
function mosArrayToInts( &$array, $default=null ) {
	return JArrayHelper::toInteger( $array, $default );
}

/**
 * Legacy function, use {@link JError::getBackTrace() JError->getBackTrace()} instead
 *
 * @deprecated	As of version 1.5
 */
function mosBackTrace( $message='' ) {
	if (function_exists( 'debug_backtrace' )) {
		echo '<div align="left">';
		if ($message) {
			echo '<p><strong>' . $message . '</strong></p>';
		}
		foreach( debug_backtrace() as $back) {
			if (@$back['file']) {
				echo '<br />' . str_replace( JPATH_ROOT, '', $back['file'] ) . ':' . $back['line'];
			}
		}
		echo '</div>';
	}
}

/**
 * Legacy function, use {@link JPath::setPermissions()} instead
 *
 * @deprecated	As of version 1.5
 */
function mosChmod( $path ) {
	jimport('joomla.filesystem.path');
	return JPath::setPermissions( $path );
}

/**
 * Legacy function, use {@link JPath::setPermissions()} instead
 *
 * @deprecated	As of version 1.5
 */
function mosChmodRecursive( $path, $filemode=NULL, $dirmode=NULL ) {
	jimport('joomla.filesystem.path');
	return JPath::setPermissions( $path, $filemode, $dirmode );
}

/**
 * Legacy function, use {@link JPath::canChmod()} instead
 *
 * @deprecated	As of version 1.5
 */
function mosIsChmodable( $file ) {
	jimport('joomla.filesystem.path');
	return JPath::canChmod( $file );
}

/**
 * Legacy function, replaced by geshi bot
 *
 * @deprecated	As of version 1.5
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
 * Legacy function, use mosLoadModule( 'breadcrumb', -1 ); instead
 *
 * @deprecated	As of version 1.5
 */
function mosPathWay() {
	mosLoadModule('breadcrumb', -1);
}

/**
 * Legacy function, use {@link JBrowser::getInstance()} instead
 *
 * @deprecated	As of version 1.5
 */
function mosGetBrowser( $agent ) {
	jimport('joomla.environment.browser');
	$instance =& JBrowser::getInstance();
	return $instance;
}

/**
 * Legacy function, use {@link JApplication::getBrowser()} instead
 *
 * @deprecated	As of version 1.5
 */
function mosGetOS( $agent ) {
	jimport('joomla.environment.browser');
	$instance =& JBrowser::getInstance();
	return $instance->getPlatform();
}

/**
 * Legacy function, use {@link JArrayHelper::getValue()} instead
 *
 * @deprecated	As of version 1.5
 */
function mosGetParam( &$arr, $name, $def=null, $mask=0 )
{
	// Static input filters for specific settings
	static $noHtmlFilter	= null;
	static $safeHtmlFilter	= null;

	$var = JArrayHelper::getValue( $arr, $name, $def, '' );

	// If the no trim flag is not set, trim the variable
	if (!($mask & 1) && is_string($var)) {
		$var = trim($var);
	}

	// Now we handle input filtering
	if ($mask & 2) {
		// If the allow raw flag is set, do not modify the variable
		$var = $var;
	} elseif ($mask & 4) {
		// If the allow html flag is set, apply a safe html filter to the variable
		if (is_null($safeHtmlFilter)) {
			$safeHtmlFilter = & JFilterInput::getInstance(null, null, 1, 1);
		}
		$var = $safeHtmlFilter->clean($var, 'none');
	} else {
		// Since no allow flags were set, we will apply the most strict filter to the variable
		if (is_null($noHtmlFilter)) {
			$noHtmlFilter = & JFilterInput::getInstance(/* $tags, $attr, $tag_method, $attr_method, $xss_auto */);
		}
		$var = $noHtmlFilter->clean($var, 'none');
	}
	return $var;
}

/**
 * Legacy function, use {@link JHTML::_('list.genericordering', )} instead
 *
 * @deprecated	As of version 1.5
 */
function mosGetOrderingList( $sql, $chop='30' )
{
	return JHTML::_('list.genericordering', $sql, $chop);
}

/**
 * Legacy function, use {@link JRegistry} instead
 *
 * @deprecated	As of version 1.5
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
 */
function mosLoadComponent( $name )
{
	// set up some global variables for use by the frontend component
	global $mainframe, $database;
	$name = JFilterInput::clean($name, 'cmd');
	$path = JPATH_SITE.DS.'components'.DS.'com_'.$name.DS.$name.'.php';
	if (file_exists($path)) {
		include $path;
	}
}

/**
 * Legacy function, use {@link JEditor::init()} instead
 *
 * @deprecated	As of version 1.5
 */
function initEditor()
{
	$editor =& JFactory::getEditor();
	echo $editor->initialise();
}

/**
 * Legacy function, use {@link JEditor::save()} or {@link JEditor::getContent()} instead
 *
 * @deprecated	As of version 1.5
 */
function getEditorContents($editorArea, $hiddenField)
{
	jimport( 'joomla.html.editor' );
	$editor =& JFactory::getEditor();
	echo $editor->save( $hiddenField );
}

/**
 * Legacy function, use {@link JEditor::display()} instead
 *
 * @deprecated	As of version 1.5
 */
function editorArea($name, $content, $hiddenField, $width, $height, $col, $row)
{
	jimport( 'joomla.html.editor' );
	$editor =& JFactory::getEditor();
	echo $editor->display($hiddenField, $content, $width, $height, $col, $row);
}

/**
 * Legacy function, use {@link JMenu::authorize()} instead
 *
 * @deprecated	As of version 1.5
 */
function mosMenuCheck( $Itemid, $menu_option, $task, $gid )
{
	$user =& JFactory::getUser();
	$menus =& JSite::getMenu();
	$menus->authorize($Itemid, $user->get('aid'));
}

/**
 * Legacy function, use {@link JArrayHelper::fromObject()} instead
 *
 * @deprecated	As of version 1.5
 */
function mosObjectToArray( $p_obj, $recurse = true, $regex = null )
{
	$result = JArrayHelper::fromObject( $p_obj, $recurse, $regex );
	return $result;
}

/**
 * Legacy function, use {@link JHTML::_('date', )} instead
 *
 * @deprecated	As of version 1.5
 */
function mosFormatDate( $date = 'now', $format = null, $offset = null )  {

	if ( ! $format )
	{
		$format = JText::_('DATE_FORMAT_LC1');
	}

	return JHTML::_('date', $date, $format, $offset);
}

/**
 * Legacy function, use {@link JHTML::_('date', )} instead
 *
 * @deprecated	As of version 1.5
 */
function mosCurrentDate( $format="" )
{
	if ($format=="") {
		$format = JText::_( 'DATE_FORMAT_LC1' );
	}

	return JHTML::_('date', 'now', $format);
}

/**
 * Legacy function, use {@link JFilterOutput::objectHTMLSafe()} instead
 *
 * @deprecated	As of version 1.5
 */
function mosMakeHtmlSafe( &$mixed, $quote_style=ENT_QUOTES, $exclude_keys='' ) {
	jimport('joomla.filter.output');
	JFilterOutput::objectHTMLSafe( $mixed, $quote_style, $exclude_keys );
}

/**
 * Legacy function, handled by {@link JDocument} Zlib outputfilter
 *
 * @deprecated	As of version 1.5
 */
function initGzip()
{
	global $mainframe, $do_gzip_compress;


	// attempt to disable session.use_trans_sid
	ini_set('session.use_trans_sid', false);

	$do_gzip_compress = FALSE;
	if ($mainframe->getCfg('gzip') == 1) {
		$phpver = phpversion();
		$useragent = mosGetParam( $_SERVER, 'HTTP_USER_AGENT', '' );
		$canZip = mosGetParam( $_SERVER, 'HTTP_ACCEPT_ENCODING', '' );

		if ( $phpver >= '4.0.4pl1' &&
				( strpos($useragent,'compatible') !== false ||
					strpos($useragent,'Gecko') !== false
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
 * Legacy function, use JFolder::delete($path)
 *
 * @deprecated	As of version 1.5
 */
function deldir( $dir )
{
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
 * Legacy function, handled by {@link JDocument} Zlib outputfilter
 *
 * @deprecated	As of version 1.5
 */
function doGzip()
{
	global $do_gzip_compress;
	if ( $do_gzip_compress )
	{
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
 * Legacy function, use {@link JArrayHelper::sortObjects()} instead
 *
 * @deprecated	As of version 1.5
 */
function SortArrayObjects( &$a, $k, $sort_direction=1 )
{
	JArrayHelper::sortObjects($a, $k, $sort_direction);
}

/**
 * Legacy function, {@link JRequest::getVar()}
 *
 * @deprecated	As of version 1.5
 */
function josGetArrayInts( $name, $type=NULL ) {
	
	$array	=  JRequest::getVar($name, array(), 'default', 'array' );
	
	return $array;
}

/**
 * Legacy function, {@link JSession} transparently checks for spoofing attacks
 *
 * @deprecated	As of version 1.5
 */
function josSpoofCheck( $header=false, $alternate=false )
{
	// Lets make sure they saw the html form
	$check = true;
	$hash	= josSpoofValue();
	$valid	= JRequest::getBool( $hash, 0, 'post' );
	if (!$valid) {
		$check = false;
	}

	// Make sure request came from a client with a user agent string.
	if (!isset( $_SERVER['HTTP_USER_AGENT'] )) {
		$check = false;
	}

	// Check to make sure that the request was posted as well.
	$requestMethod = JArrayHelper::getValue( $_SERVER, 'REQUEST_METHOD' );
	if ($requestMethod != 'POST') {
		$check = false;
	}

	if (!$check)
	{
		header( 'HTTP/1.0 403 Forbidden' );
		die( JText::_('E_SESSION_TIMEOUT') );
	}
}

/**
 * Legacy function, use {@link JUtility::getToken()} instead
 *
 * @deprecated	As of version 1.5
 */
function josSpoofValue($alt = NULL)
{
	global $mainframe;

	if ($alt) {
		if ( $alt == 1 ) {
			$random		= date( 'Ymd' );
		} else {
			$random		= $alt . date( 'Ymd' );
		}
	} else {
		$random		= date( 'dmY' );
	}
	// the prefix ensures that the hash is non-numeric
	// otherwise it will be intercepted by globals.php
	$validate 	= 'j' . mosHash( $mainframe->getCfg( 'db' ) . $random );

	return $validate;
}

/**
 * Legacy function to load the tooltip library.
 *
 * @deprecated	As of version 1.5
 */
function loadOverlib() {
	JHTML::_('behavior.tooltip');
}

/**
* Legacy utility function to provide ToolTips
*
* @deprecated	As of version 1.5
*/
function mosToolTip( $tooltip, $title='', $width='', $image='tooltip.png', $text='', $href='', $link=1 )
{
	// Initialize the toolips if required
	static $init;
	if ( ! $init ) {			JHTML::_('behavior.tooltip');
		$init = true;
	}
		
	return JHTML::_('tooltip', $tooltip, $title, $image, $text, $href, $link);
}

/**
 * Legacy function to convert an internal Joomla URL to a humanly readible URL.
 *
 * @deprecated	As of version 1.5
 */
function sefRelToAbs($value) {
	return JRoute::_($value);
}


/**
 * Legacy function to replaces &amp; with & for xhtml compliance
 *
 * @deprecated	As of version 1.5
 */
function ampReplace( $text )
{
	jimport('joomla.filter.output');
	return JFilterOutput::ampReplace($text);
}

/**
 * Legacy function to replaces &amp; with & for xhtml compliance
 *
 * @deprecated	As of version 1.5
 */
function mosTreeRecurse( $id, $indent, $list, &$children, $maxlevel=9999, $level=0, $type=1 )
{
	jimport('joomla.html.html');
	return JHTML::_('menu.treerecurse', $id, $indent, $list, $children, $maxlevel, $level, $type);
}

/**
 * Legacy function, use {@link JHTML::tooltip()} instead
 *
 * @deprecated	As of version 1.5
 */
function mosWarning($warning, $title='Joomla! Warning') {
	return JHTML::tooltip($warning, $title, 'warning.png', null, null, null);
}
