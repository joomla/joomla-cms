<?php
/**
* @version $Id: app.php 1534 2005-12-22 01:38:31Z Jinx $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

jimport( 'joomla.common.base.object' );

/**
* Base class for a Joomla! application
*
* Acts as a Factory class for application specific objects and
* provides many supporting API functions.
*
* @package Joomla.Framework
* @subpackage Application
* @abstract
* @since 1.1
*/

class JApplication extends JObject 
{
	/** 
	 * The current session
	 * 
	 * @var JModelSession  
	 * @access protected
	 */
	var $_session = null;
	
	/** 
	 * The pathway store
	 * 
	 * @var object  JPathWay object
	 * @access protected
	 */
	var $_pathway = null;
	
	/** 
	 * The client identifier
	 * 
	 * @var integer
	 * @access protected
	 */
	var $_client = null;
	
	/** 
	 * A string holding the active language
	 * 
	 * @var string  
	 * @access protected
	 */
	var $_lang  = null;
	
	/** 
	 * Application persistent store
	 * 
	 * @var object  JRegistry object
	 * @access protected
	 */
	var $_registry = null;
	
	/** 
	 * The active user object
	 * 
	 * @var object JModelUser 
	 * @access protected
	 */
	var $_user = null;

	/**
	* Class constructor
	*
	* @param string 	The URL option passed in
	* @param integer	A client identifier
	*/
	function __construct( $client=0 ) 
	{
		$this->_client 		    = $client;
	}
	
	 /**
	 * Gets a configuration value
	 *
	 * @access public
	 * @param string 	$varname 	The name of the value to get
	 * @return The user state
	 */
	function getCfg( $varname ) {
		return $this->_registry->getValue('config.'.$varname);
	}
	
	/**
	 * Gets a user state
	 *
	 * @access public
	 * @param string 	$key 	The path of the state
	 * @return The user state
	 */
	function getUserState( $key ) 
	{	
		$registry =& JSession::get('registry');
		if(!is_null($registry)) {
			return $registry->getValue($key);
		}
		return false;
	}

	/**
	* Sets the value of a user state variable
	*
	* @access public
	* @param string $key 	The path of the state
	* @param string $value 	The value of the variable
	* @return mixed The previous state if exist
	*/
	function setUserState( $key, $value ) 
	{
		$registry =& JSession::get('registry');
		if(!is_null($registry)) {
			return $registry->setValue($key, $value);
		}
		return false;
	}

	/**
	* Gets the value of a user state variable
	*
	* @access public
	* @param string The key of the user state variable
	* @param string The name of the variable passed in a request
	* @param string The default value for the variable if not found
	* @return The request user state
	*/
	function getUserStateFromRequest( $key, $request, $default=null ) 
	{	
		//Force namespace
		$key = 'request.'.$key;
		
		$old_state = $this->getUserState( $key );
		$cur_state = isset( $old_state ) ? $old_state : $default;
		$new_state = isset( $_REQUEST[$request] ) ? $_REQUEST[$request] : $cur_state; 
				
		$this->setUserState( $key, $new_state );
		
		return $new_state;
	}

	/**
	* Registers a handler to a particular event group
	*
	* @static
	* @param string The event name
	* @param mixed The handler, a function or an instance of a event object
	* @since 1.1
	*/

	function registerEvent($event, $handler) {
		$dispatcher =& JEventDispatcher::getInstance();
		return $dispatcher->register($event, $handler);
	}


	/**
	* Calls all handlers associated with an event group
	*
	* @static
	* @param string The event name
	* @param array An array of arguments
	* @return array An array of results from each function call
	* @since 1.1
	*/
	function triggerEvent($event, $args=null) {
		$dispatcher =& JEventDispatcher::getInstance();
		return $dispatcher->trigger($event, $args);
	}

	/**
	* Login authentication function
	*
	* Username and encoded password are passed the the onLoginUser event who
	* is responsible for the user validation.
	* A successful validation updates the current session record with the
	* users details.
	*/
	function login( $username=null,$passwd=null ) 
	{
		global $database, $acl;

		if (!$username || !$passwd) {
			$username 	= $database->getEscaped( trim( mosGetParam( $_POST, 'username', '' ) ) );
			$passwd 	= $database->getEscaped( trim( mosGetParam( $_POST, 'passwd', '' ) ) );
			$bypost 	= 1;
		}

		if (!$username || !$passwd) {
			// Error check if still no username or password values
			echo "<script> alert(\"". JText::_( 'LOGIN_INCOMPLETE', true ) ."\"); </script>\n";
			mosRedirect( mosGetParam( $_POST, 'return', '/' ) );
			exit();
		} else {

			// Build the credentials array
			$credentials['username'] = $username;
			$credentials['password'] = $passwd;

			// Get the global JAuth object
			$auth = &JAuth::getInstance();

			return $auth->login($credentials);
		}
	}

	/**
	* Logout authentication function
	*
	* Passed the current user information to the onLogoutUser event and reverts the current
	* session record back to 'anonymous' parameters
	*/
	function logout() 
	{
		$auth = &JAuth::getInstance();
		return $auth->logout();
	}

	/**
	 * Return the application option string [main component]
	 *
	 * @access public
	 * @return string Option
	 * @since 1.1
	 */
	function getOption() {
		return JRequest::getVar('option');
	}

	/**
	 * Set the user session
	 *
	 * @access public
	 * @param string	The sessions name
	 */
	function setSession($name) {
		$this->_createSession($name);
	}
	
	/**
	 * Set the configuration
	 *
	 * @access public
	 * @param string	The path to the configuration file
	 * @param string	The type of the configuration file
	 */
	function setConfiguration($file, $type = 'config') {
		$this->_createConfiguration($file, $type);
	}
	
	/**
	 * Return a reference to the JPathWay object
	 *
	 * @access public
	 * @return jpathway 	JPathWay object
	 * @since 1.1
	 */
	function &getPathWay() {
		return $this->_pathway;
	}
	
	/**
	 * Gets the name of the current template
	 * 
	 * @return string
	 */
	function getTemplate() {
		return '_system';
	}

	/**
	 * Return a reference to the JDocument object
	 *
	 * @access public
	 * @since 1.1
	 */
	function &getDocument() 
	{
		$attributes = array (
            'charset'  => 'utf-8',
           	'lineend'  => 'unix',
            'tab'  => '  ',
          	'language' => 'eng_GB'
		);
		jimport('joomla.document.document');
		return JDocument::getInstance('html', $attributes);
	}

	/**
	 * Return a reference to a JDatabase instance
	 *
	 * @access public
	 * @param string $host 		Database host
	 * @param string $user 		Database user name
	 * @param string $password 	Database user password
	 * @param string $db  		Database name
	 * @param string $dbprefix	Common prefix for all tables
	 * @param string $dbtype	Database type
	 * @param string $debug		True if database needs to be set in debug mode
	 * @return jdatabase A JDatabase object
	 * @since 1.1
	 */
	function &getDBO($host = null, $user = null , $password = null, $db = null , $dbprefix = null,  $dbtype = null, $debug = null)
	{
		$host 		= is_null($host) 	? $this->getCfg('host')    : $host;
		$user 		= is_null($user) 	? $this->getCfg('user')    : $user;
		$password 	= is_null($password)? $this->getCfg('password'): $password;
		$db   		= is_null($db) 		? $this->getCfg('db') 	   : $db;
		$dbprefix 	= is_null($dbprefix)? $this->getCfg('dbprefix'): $dbprefix;
		$dbtype 	= is_null($dbtype) 	? $this->getCfg('dbtype')  : $dbtype;
		$debug 		= is_null($debug) 	? $this->getCfg('debug')   : $debug;

		jimport('joomla.database.database');

		/** @global $database */
		$database =& JDatabase::getInstance( $dbtype, $host, $user, $password, $db, $dbprefix );

		if ($database->getErrorNum()) {
			JError::raiseError('joomla.library:'.$database->getErrorNum(), 'JApplication::getDBO: Could not connect to database' );
		}
		$database->debug( $debug );
		return $database;
	}

	/**
	 * Return a reference to the JBrowser object
	 *
	 * @return jbrowser A JBrowser object holding the browser information
	 */
	function &getBrowser()
	{
		jimport('joomla.application.environment.browser');
		return JBrowser::getInstance();
	}

	/**
	 * Returns a reference to the JUser object
	 *
	 * @return JModelUser A user object with the information from the current session
	 */
	function &getUser()
	{
		// Check to see if the user object exists
		if (!is_object($this->_user)) {
			// If it doesn't exist, create a new user object
			$this->_user =& JModel::getInstance('user', $this->getDBO());
		}

		// If there is a userid in the session, load the user object with the logged in user
		if (intval( JSession::get('userid')) && $this->_user->id < 1) {

			$this->_user->load(JSession::get('userid'));
			$this->_user->params = new JParameters($this->_user->params);
		}

		return $this->_user;
	}

	/**
	* Return a reference to the JLanguage object
	*
	* @return jlanguage 	A JLanguage object
	* @since 1.1
	*/
	function &getLanguage( )
	{
		if(is_null($this->_lang)) {
			$this->_createLanguage();
		}

		$lang =& JLanguage::getInstance( $this->_lang );
		$lang->setDebug( $this->getCfg('debug') );

		return $lang;
	}

	/**
	 * Create the language
	 *
	 * @access private
	 * @param string 	The language name
	 * @since 1.1
	 */

	function _createLanguage($strLang = null)
	{
		$user = $this->getUser();

		$strLang = $this->getUserState( 'application.lang' );

		if ($strLang == '' && isset( $user->params )) {

			// get user's prefered language
			if( $this->isAdmin() ) {
				$strLang = $user->params->get( 'admin_language', $strLang );
			} else {
				$strLang = $user->params->get( 'language', $strLang );
			}
		}

		// if no user preference load the default language file
		if ($strLang == '0' || $strLang == '') {
			if( $this->isAdmin() ) {
				$strLang = $this->getCfg('lang_administrator');
			} else {
				$strLang = $this->getCfg('lang');
			}
		}

		$this->_lang = $strLang;
	}

	/**
	 * Create a JPathWay object and set the home/component items of the pathway
	 *
	 * @access private
	 * @return boolean True if successful
	 * @since 1.1
	 */
	function _createPathWay()
	{
		//Load the pathway object
		jimport( 'joomla.pathway' );
		
		//Get some request variables
		$ItemID = JRequest::getVar('Itemid');
		$option = JRequest::getVar('option');
		
		// Create a JPathWay object
		$this->_pathway = new JPathWay();

		// If not on the frontpage, add the component item to the pathway
		if (($option == 'com_frontpage') || ($option == '')) {

			// Add the home item to the pathway only and it is not linked
			$this->_pathway->addItem( 'Home', '' );
		} else {

			// Initialize variables
			$IIDstring = null;

			// Add the home item to the pathway
			$this->_pathway->addItem( 'Home', 'index.php' );

			// Get the actual component name
			if (substr($option, 0, 4) == 'com_') {
				$comName = substr($option, 4);
			} else {
				$comName = $option;
			}
			// Handle the ItemID
			if ($ItemID) {
				$IIDstring = '&Itemid='.$ItemID;
			}

			$this->_pathway->addItem( $comName, 'index.php?option='.$option.$IIDstring);
		}

		return true;
	}

	/**
	 * Create the configuration registry
	 *
	 * @access private
	 * @param string $file 	The path to the configuration file
	 * @param string $type	The format type
	 */
	function _createConfiguration($file, $type = 'PHP') 
	{	
		jimport( 'joomla.registry.registry' );
		
		require_once( $file );
		
		// Create the JConfig object
		$config = new JConfig();

		// Create the registry with a default namespace of config which is read only
		$this->_registry =& new JRegistry( 'config', true );
		$this->_registry->loadObject($config);
	}

	/**
	 * Create the user session
	 *
	 * Old sessions are flushed based on the configuration value for the cookie
	 * lifetime. If an existing session, then the last access time is updated.
	 * If a new session, a session id is generated and a record is created in
	 * the #__sessions table.
	 *
	 * @access private
	 * @param string	The sessions name
	 * @param boolean 	Use cookies to store the session on the client
	 */
	function _createSession( $name, $useCookies = true)
	{
		JSession::useCookies(true);
		JSession::start(md5( $name ));
		
		JSession::get('registry', new JRegistry('application'));

		$session = & JModel::getInstance('session', $this->getDBO());
		$session->purge( intval( $this->getCfg( 'lifetime' ) ) );

		if ($session->load( $session->hash( JSession::id() ) )) {
			// Session cookie exists, update time in session table
			$session->update();
		} else {

			if (!$session->insert($session->hash( JSession::id()))) {
				die( $session->getError() );
			}
			$session->persist();
		}

		$this->_session = $session;

		JSession::setIdle($this->getCfg('lifetime'));

		if (JSession::isIdle()) {
			$this->logout();
		}

		JSession::updateIdle();
	}

	/**
	 * Gets the client id
	 * @param mixed A client identifier
	 * @since 1.1
	 */
	function getClient( ) {
		return $this->_client;
	}

	/** Is admin interface?
	 * @return boolean
	 * @since 1.0.2
	 */
	function isAdmin() {
		return ($this->_client == 1) ?  true : false;
	}

	/** Is site interface?
	 * @return boolean
	 * @since 1.1
	 */
	function isSite() {
		return ($this->_client == 0) ?  true : false;
	}

	/** Is admin interface?
	 * @return boolean
	 * @since 1.1
	 */
	function isInstall() {
		return ($this->_client == 2) ?  true : false;
	}

	/**
	 * Depreceated functions
	 */
	 
	 /**
	 * Depreceated, use JPathWay->addItem() method instead
	 * @since 1.1
	 */
	function appendPathWay( $name, $link = null ) {

		/*
		 * To provide backward compatability if no second parameter is set
		 * set it to null
		 */
		if ($link == null) {
			$link = '';
		}

		// Add item to the pathway object
		if ($this->_pathway->addItem($name, $link)) {
			return true;
		}

		return false;
  }

	 /**
 	 * Depreceated, use JPathWay->getNamePathWay() method instead
 	 * @since 1.1
 	 */
	function getCustomPathWay() {
		return $this->_pathway->getNamePathWay();
	}

	 /**
	* Depreacted, use JDocument->renderHead instead
	* @since 1.1
	*/
	 function getHead() {
		$document=& $this->getDocument();
		return $document->fetchHead();
	 }

	/**
	* Depreacted, use JDocument->setMetadata instead
	* @since 1.1
	*/
	function addMetaTag( $name, $content, $prepend='', $append='' ) {
		$document=& $this->getDocument();
		$document->setMetadata($name, $content);
	}

	/**
	* Depreacted, use JDocument->setMetadata instead
	* @since 1.1
	*/
	function appendMetaTag( $name, $content ) {
		$this->addMetaTag($name, $content);
	}

	/**
	* Depreacted, use JDocument->setMetadata instead
	* @since 1.1
	*/
	function prependMetaTag( $name, $content ) {
		$this->addMetaTag($name, $content);
	}

	/**
	* Depreacted, use JDocument->addCustomTag instead
	* @since 1.1
	*/
	function addCustomHeadTag( $html ) {
		$document=& $this->getDocument();
		return $document->addCustomTag($html);
	}

	/**
	* Depreacted, use JApplicationHelper::getItemid instead
	* @since 1.1
	*/
	function getItemid( $id, $typed=1, $link=1, $bs=1, $bc=1, $gbs=1 ) {
		return JApplicationHelper::getItemid( $id, $typed, $link, $bs, $bc, $gbs);
	}

	/**
	* Depreacted, use JApplicationHelper::getBlogSectionCount instead
	* @since 1.1
	*/
	function getBlogSectionCount( ) {
		return JApplicationHelper::getBlogSectionCount( );
	}

	/**
	* Depreacted, use JApplicationHelper::getBlogCategoryCount instead
	* @since 1.1
	*/
	function getBlogCategoryCount( ) {
		return JApplicationHelper::getBlogCategoryCount( );
	}

	/**
	* Depreacted, use JApplicationHelper::getGlobalBlogSectionCount instead
	* @since 1.1
	*/
	function getGlobalBlogSectionCount( ) {
		return JApplicationHelper::getGlobalBlogSectionCount( );
	}

	/**
	* Depreacted, use JApplicationHelper::getStaticContentCount instead
	* @since 1.1
	*/
	function getStaticContentCount( ) {
		return JApplicationHelper::getStaticContentCount( );
	}

	/**
	* Depreacted, use JApplicationHelper::getContentItemLinkCount instead
	* @since 1.1
	*/
	function getContentItemLinkCount( ) {
		return JApplicationHelper::getContentItemLinkCount( );
	}
	
	/**
	* Depreacted, use JApplicationHelper::getPath instead
	* @since 1.1
	*/
	function getPath($varname, $user_option=null) {
		return JApplicationHelper::getPath ($varname, $user_option);
	}
}

/**
 * Application helper functions
 * 
 * @static
 * @package Joomla.Framework
 * @subpackage Application
 * @since 1.1
 */
class JApplicationHelper
{
	/**
	 * Get the itemid for a content item
	 * 
	 * @access public
	 * @return integer
	 * @since 1.0
	 */
	function getItemid( $id, $typed=1, $link=1, $bs=1, $bc=1, $gbs=1 ) {
		global $Itemid, $database;

		$_Itemid = '';
		if ($_Itemid == '' && $typed) {
			// Search for typed link
			$query = "SELECT id"
			. "\n FROM #__menu"
			. "\n WHERE type = 'content_typed'"
			. "\n AND published = 1"
			. "\n AND link = 'index.php?option=com_content&task=view&id=$id'"
			;
			$database->setQuery( $query );
			$_Itemid = $database->loadResult();
		}

		if ($_Itemid == '' && $link) {
			// Search for item link
			$query = "SELECT id"
			."\n FROM #__menu"
			."\n WHERE type = 'content_item_link'"
			. "\n AND published = 1"
			. "\n AND link = 'index.php?option=com_content&task=view&id=$id'"
			;
			$database->setQuery( $query );
			$_Itemid = $database->loadResult();
		}

		if ($_Itemid == '') {
			// Search in sections
			$query = "SELECT m.id "
			. "\n FROM #__content AS i"
			. "\n LEFT JOIN #__sections AS s ON i.sectionid = s.id"
			. "\n LEFT JOIN #__menu AS m ON m.componentid = s.id "
			. "\n WHERE m.type = 'content_section'"
			. "\n AND m.published = 1"
			. "\n AND i.id = $id"
			;
			$database->setQuery( $query );
			$_Itemid = $database->loadResult();
		}

		if ($_Itemid == '' && $bs) {
			// Search in specific blog section
			$query = "SELECT m.id "
			. "\n FROM #__content AS i"
			. "\n LEFT JOIN #__sections AS s ON i.sectionid = s.id"
			. "\n LEFT JOIN #__menu AS m ON m.componentid = s.id "
			. "\n WHERE m.type = 'content_blog_section'"
			. "\n AND m.published = 1"
			. "\n AND i.id = $id"
			;
			$database->setQuery( $query );
			$_Itemid = $database->loadResult();
		}

		if ($_Itemid == '' && $bc) {
			// Search in specific blog category
			$query = "SELECT m.id "
			. "\n FROM #__content AS i"
			. "\n LEFT JOIN #__categories AS c ON i.catid = c.id"
			. "\n LEFT JOIN #__menu AS m ON m.componentid = c.id "
			. "\n WHERE m.type = 'content_blog_category'"
			. "\n AND m.published = 1"
			. "\n AND i.id = $id"
			;
			$database->setQuery( $query );
			$_Itemid = $database->loadResult();
		}

		if ($_Itemid == '' && $gbs) {
			// Search in global blog section
			$query = "SELECT id "
			. "\n FROM #__menu "
			. "\n WHERE type = 'content_blog_section'"
			. "\n AND published = 1"
			. "\n AND componentid = 0"
			;
			$database->setQuery( $query );
			$_Itemid = $database->loadResult();
		}

		if ($_Itemid == '') {
			// Search in categories
			$query = "SELECT m.id "
			. "\n FROM #__content AS i"
			. "\n LEFT JOIN #__categories AS cc ON i.catid = cc.id"
			. "\n LEFT JOIN #__menu AS m ON m.componentid = cc.id "
			. "\n WHERE m.type = 'content_category'"
			. "\n AND m.published = 1"
			. "\n AND i.id = $id"
			;
			$database->setQuery( $query );
			$_Itemid = $database->loadResult();
		}

		if ( $_Itemid != '' ) {
			return $_Itemid;
		} else {
			return $Itemid;
		}
	}

	/**
	 * Get the total number of published blog sections
	 * 
	 * @access public 
	 * @return integer
	 * @since 1.0
	 */
	function getBlogSectionCount( ) {
		global $database;

		$query = "SELECT COUNT( id )"
		."\n FROM #__menu "
		."\n WHERE type = 'content_blog_section'"
		."\n AND published = 1"
		;
		$database->setQuery( $query );
		$count = $database->loadResult();
		return $count;
	}

	/**
	 * Get the total number of published blog categories
	 * 
	 * @access public
	 * @return integer
	 * @since 1.0
	 */
	function getBlogCategoryCount( ) {
		global $database;

		$query = "SELECT COUNT( id )"
		."\n FROM #__menu "
		. "\n WHERE type = 'content_blog_category'"
		. "\n AND published = 1"
		;
		$database->setQuery( $query );
		$count = $database->loadResult();
		return $count;
	}

	/**
	 * Get the total number of published blog sections
	 * 
	 * @access public
	 * @return integer
	 * @since 1.0
	 */
	function getGlobalBlogSectionCount( ) {
		global $database;

		$query = "SELECT COUNT( id )"
		."\n FROM #__menu "
		."\n WHERE type = 'content_blog_section'"
		."\n AND published = 1"
		."\n AND componentid = 0"
		;
		$database->setQuery( $query );
		$count = $database->loadResult();
		return $count;
	}

	/**
	 * Get the total number of published static content items
	 * 
	 * @access public
	 * @return integer
	 * @since 1.0 
	 */
	function getStaticContentCount( ) {
		global $database;

		$query = "SELECT COUNT( id )"
		."\n FROM #__menu "
		."\n WHERE type = 'content_typed'"
		."\n AND published = 1"
		;
		$database->setQuery( $query );
		$count = $database->loadResult();
		return $count;
	}

	/**
	 * Get the total number of published content items
	 * 
	 * @access public
	 * @return integer
	 * @since 1.0
	 */
	function getContentItemLinkCount( ) {
		global $database;

		$query = "SELECT COUNT( id )"
		."\n FROM #__menu "
		."\n WHERE type = 'content_item_link'"
		."\n AND published = 1"
		;
		$database->setQuery( $query );
		$count = $database->loadResult();
		return $count;
	}
	
	/**
	* Get a path 
	* 
	* @access public
	* @param string $varname
	* @param string $user_option
	* @return string The requested path
	* @since 1.0
	*/
	function getPath( $varname, $user_option=null ) 
	{
		// check needed for handling of custom/new module xml file loading
		$check = ( ( $varname == 'mod0_xml' ) || ( $varname == 'mod1_xml' ) );
		
		if ( !$user_option && !$check ) {
			$user_option = $GLOBALS['option'];
		}

		$result = null;
		$name 	= substr( $user_option, 4 );

		switch ($varname) {
			case 'front':
				$result = JApplicationHelper::_checkPath( DS.'components'.DS. $user_option .DS. $name .'.php', 0 );
				break;

			case 'html':
			case 'front_html':
				if ( !( $result = JApplicationHelper::_checkPath( DS.'templates'.DS. JApplication::getTemplate() .DS.'components'.DS. $name .'.html.php', 0 ) ) ) {
					$result = JApplicationHelper::_checkPath( DS.'components'.DS. $user_option .DS. $name .'.html.php', 0 );
				}
				break;

			case 'toolbar':
				$result = JApplicationHelper::_checkPath( DS.'components'.DS. $user_option .DS.'toolbar.'. $name .'.php', -1 );
				break;

			case 'toolbar_html':
				$result = JApplicationHelper::_checkPath( DS.'components'.DS. $user_option .DS.'toolbar.'. $name .'.html.php', -1 );
				break;

			case 'toolbar_default':
			case 'toolbar_front':
				$result = JApplicationHelper::_checkPath( DS.'includes'.DS.'HTML_toolbar.php', 0 );
				break;
			
			case 'admin':
				$path 	= DS.'components'.DS. $user_option .DS.'admin.'. $name .'.php';
				$result = JApplicationHelper::_checkPath( $path, -1 );
				break;

			case 'admin_html':
				$path	= DS.'components'.DS. $user_option .DS.'admin.'. $name .'.html.php';
				$result = JApplicationHelper::_checkPath( $path, -1 );
				break;

			case 'admin_functions':
				$path	= DS.'components'.DS. $user_option .DS. $name .'.functions.php';
				$result = JApplicationHelper::_checkPath( $path, -1 );
				break;

			case 'class':
				if ( !( $result = JApplicationHelper::_checkPath( DS.'components'.DS. $user_option .DS. $name .'.class.php' ) ) ) {
					$result = JApplicationHelper::_checkPath( DS.'includes'.DS. $name .'.php' );
				}
				break;

			case 'com_xml':
				$path 	= DS.'components'.DS. $user_option .DS. $name .'.xml';
				$result = JApplicationHelper::_checkPath( $path, 1 );
				break;

			case 'mod0_xml':
				// Site modules
				if ( $user_option == '' ) {
					$path = DS.'modules'.DS.'custom.xml';
				} else {
					$path = DS.'modules'.DS. $user_option .DS. $user_option. '.xml';
				}
				$result = JApplicationHelper::_checkPath( $path, 0 );
				break;

			case 'mod1_xml':
				// admin modules
				if ($user_option == '') {
					$path = DS.'modules'.DS.'custom.xml';
				} else {
					$path = DS.'modules'.DS. $user_option .DS. $user_option. '.xml';
				}
				$result = JApplicationHelper::_checkPath( $path, -1 );
				break;

			case 'bot_xml':
				// Site plugins
				$path 	= DS.'plugins'.DS. $user_option .'.xml';
				$result = JApplicationHelper::_checkPath( $path, 0 );
				break;

			case 'menu_xml':
				$path 	= DS.'components'.DS.'com_menus'.DS. $user_option .DS. $user_option .'.xml';
				$result = JApplicationHelper::_checkPath( $path, -1 );
				break;
		}

		return $result;
	}
	
	/**
	 * Tries to find a file in the administrator or site areas
	 * 
	 * @access private
	 * @param string 	$parth			A file name
	 * @param integer 	$checkAdmin		0 to check site, 1 to check site and admin only, -1 to check admin only
	 * @since 1.1
	 */
	function _checkPath( $path, $checkAdmin=1 ) 
	{
		$file = JPATH_SITE . $path;
		if ($checkAdmin > -1 && file_exists( $file )) {
			return $file;
		} else if ($checkAdmin != 0) {
			$file = JPATH_ADMINISTRATOR . $path;
			if (file_exists( $file )) {
				return $file;
			}
		}

		return null;
	}
}

?>
