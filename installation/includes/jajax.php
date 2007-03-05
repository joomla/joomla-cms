<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Installation
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

define( '_JEXEC', 1 );

define( 'JXPATH_BASE', dirname( __FILE__ ) );

//Global definitions
define( 'DS', DIRECTORY_SEPARATOR );

//Joomla framework path definitions
$parts = explode( DS, JXPATH_BASE );
array_pop( $parts );
define( 'JPATH_BASE',			implode( DS, $parts )  );
array_pop( $parts );

define( 'JPATH_ROOT',			implode( DS, $parts ) );
define( 'JPATH_SITE',			JPATH_ROOT );
define( 'JPATH_CONFIGURATION',	JPATH_ROOT );
define( 'JPATH_LIBRARIES',		JPATH_ROOT . DS . 'libraries' );

// Require the library loader
require_once( JPATH_LIBRARIES . DS .'loader.php' );
// Require the xajax library
require_once (JXPATH_BASE.DS.'xajax'.DS.'xajax.inc.php');
$xajax = new xajax();
$xajax->errorHandlerOn();

$xajax->registerFunction(array('getFtpRoot', 'JAJAXHandler', 'ftproot'));
$xajax->registerFunction(array('FTPVerify', 'JAJAXHandler', 'ftpverify'));
$xajax->registerFunction(array('instDefault', 'JAJAXHandler', 'sampledata'));

jimport( 'joomla.base.object' );
jimport( 'joomla.utilities.error' );
JError::setErrorHandling(E_ERROR, 'callback', array('JAJAXHandler','handleError'));
JError::setErrorHandling(E_WARNING, 'callback', array('JAJAXHandler','handleError'));
JError::setErrorHandling(E_NOTICE, 'callback', array('JAJAXHandler','handleError'));
jimport( 'joomla.filesystem.*' );

/**
 * AJAX Task handler class
 *
 * @static
 * @package		Joomla
 * @subpackage	Installer
 * @since 1.5
 */
class JAJAXHandler
{
	/**
	 * Method to get the path from the FTP root to the Joomla root directory
	 */
	function ftproot($args)
	{
		jimport( 'joomla.application.application' );
		jimport( 'joomla.registry.registry' );

		$lang = new JAJAXLang($args['lang']);
//		$lang->setDebug(true);

		$objResponse = new xajaxResponse();
		$args = $args['vars'];

		require_once(JXPATH_BASE.DS."classes.php");
		$root = JInstallationHelper::findFtpRoot($args['ftpUser'], $args['ftpPassword'], $args['ftpHost'], $args['ftpPort']);
		if (JError::isError($root)) {
			$objResponse->addScript('document.getElementById(\'ftpdisable\').checked = true;');
			$objResponse->addAlert($lang->_($root->get('message')));
		} else {
			$objResponse->addAssign('ftproot', 'value', $root);
			$objResponse->addAssign('rootPath', 'style.display', '');
			$objResponse->addScript('document.getElementById(\'verifybutton\').click();');
		}

		return $objResponse;
	}

	/**
	 * Method to verify the ftp values are valid
	 */
	function ftpverify($args)
	{
		jimport( 'joomla.application.application' );
		jimport( 'joomla.registry.registry' );

		$lang = new JAJAXLang($args['lang']);
//		$lang->setDebug(true);

		$objResponse = new xajaxResponse();
		$args = $args['vars'];

		require_once(JXPATH_BASE.DS."classes.php");
		$status =  JInstallationHelper::FTPVerify($args['ftpUser'], $args['ftpPassword'], $args['ftpRoot'], $args['ftpHost'], $args['ftpPort']);
		if (JError::isError($status)) {
			if (($msg = $status->get('message')) != 'INVALIDROOT') {
				$msg = $lang->_('INVALIDFTP') ."\n". $lang->_($msg);
			} else {
				$msg = $lang->_($msg);
			}
			$objResponse->addScript('document.getElementById(\'ftpdisable\').checked = true;');
			$objResponse->addAlert($msg);
		} else {
			$objResponse->addScript('document.getElementById(\'ftpenable\').checked = true;');
			$objResponse->addAlert($lang->_('VALIDFTP'));
		}

		return $objResponse;
	}

	/**
	 * Method to load and execute a sql script
	 */
	function sampledata($args)
	{
		jimport( 'joomla.database.database');
		jimport( 'joomla.i18n.language');
		jimport( 'joomla.registry.registry');

		require_once(JXPATH_BASE.DS."classes.php");

		$errors = null;
		$msg = '';
		$objResponse = new xajaxResponse();
		$lang = new JAJAXLang($args['lang']);
//		$lang->setDebug(true);

		/*
		 * execute the default sample data file
		 */
		$type = $args['DBtype'];
		if ($type == 'mysqli') {
			$type = 'mysql';
		}
		$dbsample = '../sql'.DS.$type.DS.'sample_data.sql';

		$db = & JDatabase::getInstance($args['DBtype'], $args['DBhostname'], $args['DBuserName'], $args['DBpassword'], $args['DBname'], $args['DBPrefix']);
		$result = JInstallationHelper::populateDatabase($db, $dbsample, $errors);

		/*
		 * prepare sql error messages if returned from populate
		 */
		if (!is_null($errors)){
			foreach($errors as $error){
				$msg .= stripslashes( $error['msg'] );
				$msg .= chr(13)."-------------".chr(13);
				$txt = '<textarea cols="35" rows="5" name="instDefault" readonly="readonly" >'.$lang->_('Database Errors Reported').chr(13).$msg.'</textarea>';
			}
		} else {
			// consider other possible errors from populate
			$msg = $result == 0 ? $lang->_("Sample data installed successfully") : $lang->_("Error installing SQL script") ;
			$txt = '<input size="35" name="instDefault" value="'.$msg.'" readonly="readonly" />';
		}

		$objResponse->addAssign("theDefault", "innerHTML", $txt);
		return $objResponse;
	}

	/**
	 * Handle a raised error : for now just silently return
	 *
	 * @access	private
	 * @param	object	$error	JError object
	 * @return	object	$error	JError object
	 * @since	1.5
	 */
	function &handleError(&$error)
	{
		return $error;
	}
}


/**
 * Languages/translation handler class
 *
 * @package 	Joomla.Framework
 * @subpackage	I18N
 * @since		1.5
 */
class JAJAXLang extends JObject
{
	/**
	 * Debug language, If true, highlights if string isn't found
	 *
	 * @var boolean
	 * @access protected
	 */
	var $_debug 	= false;

	/**
	 * Identifying string of the language
	 *
	 * @var string
	 * @access protected
	 */
	var $_identifyer = null;

	/**
	 * The language to load
	 *
	 * @var string
	 * @access protected
	 */
	var $_lang = null;

	/**
	 * Transaltions
	 *
	 * @var array
	 * @access protected
	 */
	var $_strings = null;

	/**
	* Constructor activating the default information of the language
	*
	* @access protected
	*/
	function __construct($lang = null)
	{
		$this->_strings = array ();

		if ($lang == null) {
			$lang = 'en-GB';
		}

		$this->_lang= $lang;

		$this->load();
	}

	/**
	* Translator function, mimics the php gettext (alias _) function
	*
	* @access public
	* @param string		$string 	The string to translate
	* @param boolean	$jsSafe		Make the result javascript safe
	* @return string	The translation of the string
	*/
	function _($string, $jsSafe = false)
	{
		//$key = str_replace( ' ', '_', strtoupper( trim( $string ) ) );echo '<br>'.$key;
		$key = strtoupper($string);
		$key = substr($key, 0, 1) == '_' ? substr($key, 1) : $key;
		if (isset ($this->_strings[$key])) {
			$string = $this->_debug ? "&bull;".$this->_strings[$key]."&bull;" : $this->_strings[$key];
		} else {
			if (defined($string)) {
				$string = $this->_debug ? "!!".constant($string)."!!" : constant($string);
			} else {
				$string = $this->_debug ? "??".$string."??" : $string;
			}
		}
		if ($jsSafe) {
			$string = addslashes($string);
		}
		return $string;
	}

	/**
	 * Loads a single language file and appends the results to the existing strings
	 *
	 * @access public
	 * @param string 	$prefix 	The prefix
	 * @param string 	$basePath  	The basepath to use
	 * $return boolean	True, if the file has successfully loaded.
	 */
	function load( $prefix = '', $basePath = JPATH_BASE )
	{
		$path = JAJAXLang::getLanguagePath( $basePath, $this->_lang);

		$filename = empty( $prefix ) ?  $this->_lang : $this->_lang . '.' . $prefix ;

		$result = false;

		$newStrings = $this->_load( $path.DS.$filename.'.ini' );

		if (is_array($newStrings)) {
			$this->_strings = array_merge( $this->_strings, $newStrings);
			$result = true;
		}

		return $result;

	}

	/**
	* Loads a language file and returns the parsed values
	*
	* @access private
	* @param string The name of the file
	* @return mixed Array of parsed values if successful, boolean False if failed
	*/
	function _load( $filename )
	{
		if ($content = @file_get_contents( $filename )) {
			if( $this->_identifyer === null ) {
				$this->_identifyer = basename( $filename, '.ini' );
			}

			$registry = new JRegistry();
			$registry->loadINI($content);
			return $registry->toArray( );
		}

		return false;
	}

	/**
	* Set the Debug property
	*
	* @access public
	*/
	function setDebug($debug) {
		$this->_debug = $debug;
	}

	/**
	 * Determines is a key exists
	 *
	 * @access public
	 * @param key $key	The key to check
	 * @return boolean True, if the key exists
	 */
	function hasKey($key) {
		return isset ($this->_strings[strtoupper($key)]);
	}

	/**
	 * Get the path to a language
	 *
	 * @access public
	 * @param string $basePath  The basepath to use
	 * @param string $language	The language tag
	 * @return string	language related path or null
	 */
	function getLanguagePath($basePath = JPATH_BASE, $language = null )
	{
		$dir = $basePath.DS.'language';
		if (isset ($language)) {
			$dir .= DS.$language;
		}
		return $dir;
	}
}



/*
 * Process the AJAX requests
 */
$xajax->cleanBufferOff(); //Needed for suPHP compilance
$xajax->processRequests();
?>
