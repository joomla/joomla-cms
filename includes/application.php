<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* Joomla! Application define
*/

//Global definitions
define( 'DS', DIRECTORY_SEPARATOR );

//Joomla framework paht definitions
$parts = explode( DS, JPATH_BASE );

//Defines
define( 'JPATH_ROOT'         , implode( DS, $parts ) );

define( 'JPATH_SITE'         , JPATH_ROOT );
define( 'JPATH_CONFIGURATION', JPATH_ROOT );
define( 'JPATH_ADMINISTRATOR', JPATH_ROOT . DS . 'administrator' );
define( 'JPATH_LIBRARIES'    , JPATH_ROOT . DS . 'libraries' );
define( 'JPATH_INSTALLATION' , JPATH_ROOT . DS . 'installation' );

/**
 * Joomla! system checks
 */

@set_magic_quotes_runtime( 0 );

if (!file_exists( JPATH_CONFIGURATION . DS . 'configuration.php' ) || (filesize( JPATH_CONFIGURATION . DS . 'configuration.php' ) < 10)) {
	header( 'Location: installation/index.php' );
	exit();
}

if (in_array( 'globals', array_keys( array_change_key_case( $_REQUEST, CASE_LOWER ) ) ) ) {
	die( 'Fatal error.  Global variable hack attempted.' );
}
if (in_array( '_post', array_keys( array_change_key_case( $_REQUEST, CASE_LOWER ) ) ) ) {
	die( 'Fatal error.  Post variable hack attempted.' );
}

/**
 * Joomla! system startup
 */

//System includes
require_once( JPATH_SITE      	  . DS . 'globals.php' );
require_once( JPATH_CONFIGURATION . DS . 'configuration.php' );
require_once( JPATH_LIBRARIES 	  . DS . 'loader.php' );

//System configuration
$CONFIG = new JConfig();

if (@$CONFIG->error_reporting === 0) {
	error_reporting( 0 );
} else if (@$CONFIG->error_reporting > 0) {
	error_reporting( $CONFIG->error_reporting );
}

define('JDEBUG', $CONFIG->debug); 

unset($CONFIG);

//System profiler
if(JDEBUG) {
	jimport('joomla.utilities.profiler');
	$_PROFILER =& JProfiler::getInstance('Application');
}

/**
 * Joomla! framework loading
 */
 
//Joomla library imports
jimport( 'joomla.common.compat.compat' );

jimport( 'joomla.version' );
jimport( 'joomla.utilities.functions' );
jimport( 'joomla.utilities.error');
jimport( 'joomla.application.user.user' );
jimport( 'joomla.application.environment.session' );
jimport( 'joomla.application.environment.request' );
jimport( 'joomla.database.table' );
jimport( 'joomla.presentation.html' );
jimport( 'joomla.factory' );
//jimport( 'joomla.presentation.parameter.parameter' );
jimport( 'joomla.i18n.language' );
jimport( 'joomla.i18n.string' );
jimport( 'joomla.application.event' );
jimport( 'joomla.application.extension.plugin' );
jimport( 'joomla.application.application');
jimport( 'joomla.application.menu' );

// support for legacy classes & functions that will be depreciated
jimport( 'joomla.common.legacy.*' );

JDEBUG ? $_PROFILER->mark('afterLoadFramework') : null;

/**
* Joomla! Application class
*
* Provide many supporting API functions
* 
* @package Joomla
* @final
*/
class JSite extends JApplication 
{

	/**
	* Class constructor
	* 
	* @access protected
	* @param integer A client id
	*/
	function __construct() {
		parent::__construct(0);
	}
	
	/**
	* Login authentication function
	* 
	* @param string The username
	* @param string The password
	* @access public
	* @see JApplication::login
	*/
	function login($username=null, $password=null, $return=null) 
	{
		if(!$username || !$password) {
			$username = trim( JRequest::getVar( 'username', '', 'post' ) );
			$password = trim( JRequest::getVar( 'passwd', '', 'post' ) );
		}
	
		if (parent::login($username, $password)) 
		{	
			$return = JRequest::getVar( 'return' );
		
			if ( $return && !( strpos( $return, 'com_registration' ) || strpos( $return, 'com_login' ) ) ) {
				// checks for the presence of a return url
				// and ensures that this url is not the registration or login pages
				josRedirect( $return );
			}
		} 
		
		return false;
	}
	
	/**
	* Logout authentication function
	* 
	* @access public
	* @see JApplication::login
	*/
	function logout($return = null) 
	{
		parent::logout();
		
		$return = JRequest::getVar( 'return' );

		if ( $return && !( strpos( $return, 'com_registration' ) || strpos( $return, 'com_login' ) ) ) {
			// checks for the presence of a return url
			// and ensures that this url is not the registration or logout pages
			josRedirect( $return );
		} else {
			josRedirect( 'index.php' );
		}
	}
	
	/**
	* Set Page Title
	* 
	* @param string $title The title for the page
	* @since 1.5
	*/
	function setPageTitle( $title=null ) {
	
		$site = $this->getCfg('sitename');
		
		if($this->getCfg('offline')) {
			$site .= ' [Offline]';
		}
		
		$title = stripslashes($title);
		
		$document=& $this->getDocument();
		$document->setTitle( $site.' - '.$title);
	}

	/**
	* Get Page title
	* 
	* @return string The page title
	* @since 1.5
	*/
	function getPageTitle() {
		$document=& $this->getDocument();
		return $document->getTitle();
	}
	
	/**
	 * Set the configuration
	 *
	 * @access public
	 * @param string	The path to the configuration file
	 * @param string	The type of the configuration file
	 * @since 1.5
	 */
	function setConfiguration($file, $type = 'config') 
	{
		parent::setConfiguration($file, $type);
		
		// Create the JConfig object
		$config = new JConfig();
		$config->live_site     = substr_replace($this->getBaseURL(), '', -1, 1);
		$config->absolute_path = JPATH_SITE;

		// Load the configuration values into the registry
		$this->_registry->loadObject($config);
		
		//Insert configuration values into global scope (for backwards compatibility)
		foreach (get_object_vars($config) as $k => $v) {
			$name = 'mosConfig_'.$k;
			$GLOBALS[$name] = $v;
		}
	}
	
	/**
	 * Return a reference to the JDocument object
	 *
	 * @access public
	 * @since 1.5
	 */
	function &getDocument($type = 'html')
	{
		if(is_object($this->_document)) {
			return $this->_document;
		}
		
		$doc  =& parent::getDocument($type);	
		$user =& $this->getUser();
	
		switch($type) 
		{
			case 'html' :
			{
				//set metadata
				$doc->setMetaData( 'description', 	$this->getCfg('MetaDesc') );
				$doc->setMetaData( 'keywords', 		$this->getCfg('MetaKeys') );

				//set base URL
				$doc->setBase( $this->getBaseURL() );
		
				if ( $user->get('id') ) {
					$doc->addScript( 'includes/js/joomla.javascript.js');
				}
			} break;
			
			default : break;
		}
		
		return $this->_document;
	}
	
	/**
	* Get the template
	* 
	* @return string The template name
	* @since 1.0
	*/
	function getTemplate()
	{
		global $Itemid;

		static $templates;

		if (!isset ($templates))
		{
			$templates = array();
			
			/*
			 * Load template entries for each menuid
			 */
			$db = $this->getDBO();
			$query = "SELECT template, menuid"
				. "\n FROM #__templates_menu"
				. "\n WHERE client_id = 0"
				;
			$db->setQuery( $query );
			$templates = $db->loadObjectList('menuid');
		}
		
		if (!empty($Itemid) && (isset($templates[$Itemid])))
		{
			$template = $templates[$Itemid];
		}
		else
		{
			$template = $templates[0];
		}

		return $template->template;
	}
}


/** 
 * @global $_VERSION 
 */
$_VERSION = new JVersion();

/**
 *  Legacy global
 * 	use JApplicaiton->registerEvent and JApplication->triggerEvent for event handling
 *  use JPlugingHelper::importPlugin to load bot code
 *  @deprecated As of version 1.5
 */
$_MAMBOTS = new mosMambotHandler();
?>