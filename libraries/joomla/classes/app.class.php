<?php

/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

jimport( 'joomla.classes.object' );

/**
* Base class for a Joomla! application
* Provide many supporting API functions
*
* @package Joomla
* @subpackage JFramework
* @abstract
* @since 1.1
*/

class JApplication extends JObject {
	/** @var database Internal database class pointer */
	var $_db				= null;
	/** @var object An object of configuration variables */
	var $_config			= null;
	/** @var object An object of path variables */
	var $_path				= null;
	/** @var mosSession The current session */
	var $_session			= null;
	/** @var string The current template */
	var $_template			= null;
	/** @var array An array to hold global user state within a session */
	var $_userstate			= null;
	/** @var string Custom html string to append to the pathway */
	var $_custom_pathway	= null;
	/** @var boolean True if in the admin client */
	var $_client 			= null;
	/** @var string A string holding the current active language */
	var $_lang 			    = null;

	/**
	* Class constructor
	* @param database A database connection object
	*/
	function __construct( &$db, $client=0 ) {

		$this->_db =& $db;

		$this->_client 		    = $client;

		$this->_createTemplate( );
	}
		
	/**
	* @return string
	*/
	function getCustomPathWay() {
		return $this->_custom_pathway;
	}

	function appendPathWay( $html ) {
	$this->_custom_pathway[] = $html;
  }
  
  /**
	* Gets the value of a user state variable
	* @param string The name of the variable
	*/
	function getUserState( $var_name ) {
		if (is_array( $this->_userstate )) {
			return mosGetParam( $this->_userstate, $var_name, null );
		} else {
			return null;
		}
	}
	/**
	* Gets the value of a user state variable
	* @param string The name of the user state variable
	* @param string The name of the variable passed in a request
	* @param string The default value for the variable if not found
	*/
	function getUserStateFromRequest( $var_name, $req_name, $var_default=null ) {
		if (is_array( $this->_userstate )) {
			if (isset( $_REQUEST[$req_name] )) {
				$this->setUserState( $var_name, $_REQUEST[$req_name] );
			} else if (!isset( $this->_userstate[$var_name] )) {
				$this->setUserState( $var_name, $var_default );
			}
			return $this->_userstate[$var_name];
		} else {
			return null;
		}
	}
	/**
	* Sets the value of a user state variable
	* @param string The name of the variable
	* @param string The value of the variable
	*/
	function setUserState( $var_name, $var_value ) {
		if (is_array( $this->_userstate )) {
			$this->_userstate[$var_name] = $var_value;
		}
	}
	
	/**
	* Registers a function to a particular event group
	* @param string The event name
	* @param string The function name
	* @since 1.1
	*/
	
	function registerEvent($event, $function) {
		$eventHandler =& JEventHandler::getInstance();
		return $eventHandler->registerFunction($event, $function);
	}
	
	
	/**
	* Calls all functions associated with an event group
	* @param string The event name
	* @param array An array of arguments
	* @return array An array of results from each function call
	* @since 1.1
	*/
	function triggerEvent($event, $args=null) {
		$eventHandler =& JEventHandler::getInstance();
		return $eventHandler->trigger($event, $args);
	}

	/**
	* Login validation function
	*
	* Username and encoded password are passed the the onLoginUser event who
	* is responsible for the user validation.
	* A successful validation updates the current session record with the
	* users details.
	*/
	function login( $username=null,$passwd=null ) {
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

			//load user bot group
			JBotLoader::importGroup( 'user' );

			//trigger the onBeforeStoreUser event
			$results = $this->triggerEvent( 'onLoginUser', array( $username, $passwd ) );

			foreach($results as $result) {
				if ($result > 0) {

					$user = new mosUser( $database );
					$user->load( intval( $result ) );

					// check to see if user is blocked from logging in
					if ($user->block == 1) {
						echo "<script>alert(\"". JText::_( 'LOGIN_BLOCKED', true ) ."\"); </script>\n";
						mosRedirect(mosGetParam( $_POST, 'return', '/' ));
						exit();
					}
					// fudge the group stuff
					$grp 		= $acl->getAroGroup( $user->id );
					$row->gid 	= 1;

					if ( $acl->is_group_child_of( $grp->name, 'Registered', 'ARO' ) || $acl->is_group_child_of( $grp->name, 'Public Backend', 'ARO' )) {
						// fudge Authors, Editors, Publishers and Super Administrators into the Special Group
						$user->gid = 2;
					}
					$user->usertype = $grp->name;

					// access control check
					//if ( !$acl->acl_check( 'login', $this->_client, 'users', $user->usertype ) ) {
					//	return false;
					//}

					JSession::set('guest'		, 0);
					JSession::set('username' 	, $user->username);
					JSession::set('userid' 		, intval( $user->id ));
					JSession::set('usertype' 	, $user->usertype);
					JSession::set('gid' 		, intval( $user->gid ));

					$session =& $this->_session;

					$session->guest 		= 0;
					$session->username 		= $user->username;
					$session->userid 		= intval( $user->id );
					$session->usertype 		= $user->usertype;
					$session->gid 			= intval( $user->gid );

					$session->update();

					$user->setLastVisit();

					$remember = trim( mosGetParam( $_POST, 'remember', '' ) );
					if ($remember == 'yes') {
						$session->remember( $user->username, $user->password );
					}

					$cache = JFactory::getCache();
					$cache->cleanCache( );
					return true;
				}
			}
			return false;
		}
	}

	/**
	* User logout
	*
	* Passed the current user information to the onLogoutUser event and reverts the current
	* session record back to 'anonymous' parameters
	*/
	function logout() {

		//load user bot group
		JBotLoader::importGroup( 'user' );

		//get the user
		$user = $this->getUser();

		//trigger the onLogOutUser event
		$results = $this->triggerEvent( 'onLogoutUser', array( &$user ));

		//mosCache::cleanCache('com_content');
		mosCache::cleanCache();

		$session =& $this->_session;
		$session->destroy();

		JSession::destroy();
	}
	
	function &getPage() {
		
		$attributes = array (
            'charset'  => 'utf-8',
           	'lineend'  => 'unix',
            'tab'  => '  ',
          	'language' => 'eng_GB'
		);
		jimport('joomla.classes.page');
		return JPage::getInstance($attributes);
	}
	
	/**
	* @return mosUser A user object with the information from the current session
	*/
	//TODO : implement signleton
	function &getUser() {

		$user = new mosUser( $this->_db);

		if (intval( JSession::get('userid') )) {
			$user->load(JSession::get('userid'));
			$user->params = new mosParameters($user->params);
		}

		return $user;
	}

	/**
	 * Set language
	 *
	 * @param string 	The language name
	 * @since 1.1
	 */

	function _createLanguage($strLang = null)
	{
		global $my;

		$strLang = $this->getUserState( 'lang' );

		if ($strLang == '' && $my && isset( $my->params )) {

			// if admin && special lang?
			if( $this->isAdmin() ) {
				$strLang = $my->params->get( 'admin_language', $strLang );
			}
		}

		// loads english language file by default
		if ($strLang == '0' || $strLang == '') {
			$strLang = $this->getCfg('lang');
		}

		// In case of frontend modify the config value in order to keep backward compatiblitity
		if( !$this->isAdmin() ) {
			$mosConfig_lang = $strLang;
		}

		$this->_lang = $strLang;
	}

	/**
	* Return an instance of the JLanguage class
	*
	* @return JLanguage
	* @since 1.1
	*/
	function &getLanguage( ) {

		if(is_null($this->_lang)) {
			$this->_createLanguage();
		}

		$lang =& JLanguage::getInstance( $this->_lang );
		$lang->setDebug( $this->getCfg('debug') );

		return $lang;
	}

	/**
	* @return JBrowser A browser object holding the browser information
	*/
	function &getBrowser(){
		jimport('joomla.classes.browser');
		return JBrowser::getInstance();
	}
	
	/**
	 * @param string The name of the variable (from configuration.php)
	 * @return mixed The value of the configuration variable or null if not found
	 */
	function getCfg( $varname ) {
		$varname = 'mosConfig_' . $varname;
		if (isset( $GLOBALS[$varname] )) {
			return $GLOBALS[$varname];
		} else {
			return null;
		}
	}

	/**
	 * Setthe user session
	 *
	 * Old sessions are flushed based on the configuration value for the cookie
	 * lifetime. If an existing session, then the last access time is updated.
	 * If a new session, a session id is generated and a record is created in
	 * the mos_sessions table.
	 *
	 * @param string	The sessions name
	 * @param boolean 	Use cookies to store the session on the client
	 */
	function _createSession( $name, $useCookies = true)
	{
		JSession::useCookies(true);
		JSession::start(md5( $name ));

		if (!isset( $_SESSION['session_userstate'] )) {
			$_SESSION['session_userstate'] = array();
		}
		$this->_userstate =& $_SESSION['session_userstate'];

		$session = new mosSession( $this->_db );
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

	function _createTemplate( ) {
		global $Itemid;

		if ($this->isAdmin()) {
			$query = "SELECT template"
			. "\n FROM #__templates_menu"
			. "\n WHERE client_id = 1"
			. "\n AND menuid = 0"
			;
			$this->_db->setQuery( $query );
			$cur_template = $this->_db->loadResult();
			$path = JPATH_ADMINISTRATOR ."/templates/$cur_template/index.php";
			if (!file_exists( $path )) {
				$cur_template = 'joomla_admin';
			}

			$this->_templatePath 	= JPath::clean( JPATH_ADMINISTRATOR . '/templates/' . $cur_template );
			$this->_templateURL 	= JURL_SITE . '/administrator/templates/' . $cur_template;

		} else {
			$assigned = ( !empty( $Itemid ) ? " OR menuid = $Itemid" : '' );

			$query = "SELECT template"
			. "\n FROM #__templates_menu"
			. "\n WHERE client_id = 0"
			. "\n AND ( menuid = 0 $assigned )"
			. "\n ORDER BY menuid DESC"
			. "\n LIMIT 1"
			;
			$this->_db->setQuery( $query );
			$cur_template = $this->_db->loadResult();

			// TemplateChooser Start
			$jos_user_template = mosGetParam( $_COOKIE, 'jos_user_template', '' );
			$jos_change_template = mosGetParam( $_REQUEST, 'jos_change_template', $jos_user_template );
			if ($jos_change_template) {
				// check that template exists in case it was deleted
				if (file_exists( JPATH_SITE .'/templates/'. $jos_change_template .'/index.php' )) {
					$lifetime = 60*10;
					$cur_template = $jos_change_template;
					setcookie( 'jos_user_template', "$jos_change_template", time()+$lifetime);
				} else {
					setcookie( 'jos_user_template', '', time()-3600 );
				}
			}
			// TemplateChooser End
			$this->_templatePath 	= JPath::clean( JPATH_SITE . '/templates/' . $cur_template );
			$this->_templateURL 	= JURL_SITE . '/templates/' . $cur_template;
		}

		$this->_template = $cur_template;
	}

	/**
	 * Gets the name of the current template
	 * @return string
	 */
	function getTemplate() {
		return $this->_template;
	}

	/**
	 * Get the path to the current template
	 * @return string
	 * @since 1.1
	 */
	function getTemplatePath() {
		return $this->_templatePath;
	}

	/**
	 * Get the path to the current template
	 * @return string
	 * @since 1.1
	 */
	function getTemplateURL() {
		return $this->_templateURL;
	}


	/**
	 * Tries to find a file in the administrator or site areas
	 * @param string A file name
	 * @param int 0 to check site, 1 to check site and admin only, -1 to check admin only
	 * @since 1.1
	 */
	function _checkPath( $path, $checkAdmin=1 ) {
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
	/**
	* Returns a stored path variable
	* @return string
	*
	*/
	function getPath( $varname, $user_option=null ) {
		// check needed for handling of custom/new module xml file loading
		$check = ( ( $varname == 'mod0_xml' ) || ( $varname == 'mod1_xml' ) );
		if ( !$user_option && !$check ) {
			$user_option = $GLOBALS['option'];
		}

		$result = null;
		$name 	= substr( $user_option, 4 );

		if (isset( $this->_path->$varname ) ) {
			$result = $this->_path->$varname;
		} else {
			switch ($varname) {
				case 'front':
					$result = $this->_checkPath( '/components/'. $user_option .'/'. $name .'.php', 0 );
					break;

				case 'html':
				case 'front_html':
					if ( !( $result = $this->_checkPath( '/templates/'. $this->_template .'/components/'. $name .'.html.php', 0 ) ) ) {
						$result = $this->_checkPath( '/components/'. $user_option .'/'. $name .'.html.php', 0 );
					}
					break;

				case 'toolbar':
					$result = $this->_checkPath( '/components/'. $user_option .'/toolbar.'. $name .'.php', -1 );
					break;

				case 'toolbar_html':
					$result = $this->_checkPath( '/components/'. $user_option .'/toolbar.'. $name .'.html.php', -1 );
					break;

				case 'toolbar_default':
				case 'toolbar_front':
					$result = $this->_checkPath( '/includes/HTML_toolbar.php', 0 );
					break;

				case 'admin':
					$path 	= '/components/'. $user_option .'/admin.'. $name .'.php';
					$result = $this->_checkPath( $path, -1 );
					break;

				case 'admin_html':
					$path	= '/components/'. $user_option .'/admin.'. $name .'.html.php';
					$result = $this->_checkPath( $path, -1 );
					break;

				case 'class':
					if ( !( $result = $this->_checkPath( '/components/'. $user_option .'/'. $name .'.class.php' ) ) ) {
						$result = $this->_checkPath( '/includes/'. $name .'.php' );
					}
					break;

				case 'com_xml':
					$path 	= '/components/'. $user_option .'/'. $name .'.xml';
					$result = $this->_checkPath( $path, 1 );
					break;

				case 'mod0_xml':
					// Site modules
					if ( $user_option == '' ) {
						$path = '/modules/custom.xml';
					} else {
						$path = '/modules/'. $user_option .'.xml';
					}
					$result = $this->_checkPath( $path, 0 );
					break;

				case 'mod1_xml':
					// admin modules
					if ($user_option == '') {
						$path = '/modules/custom.xml';
					} else {
						$path = '/modules/'. $user_option .'.xml';
					}
					$result = $this->_checkPath( $path, -1 );
					break;

				case 'bot_xml':
					// Site mambots
					$path 	= '/mambots/'. $user_option .'.xml';
					$result = $this->_checkPath( $path, 0 );
					break;

				case 'menu_xml':
					$path 	= '/components/com_menus/'. $user_option .'/'. $user_option .'.xml';
					$result = $this->_checkPath( $path, -1 );
					break;

				case 'commonmenu_xml':
					$path 	= '/components/com_menus/menu.common.xml';
					$result = $this->_checkPath( $path, -1 );
					break;

				case 'blogmenu_xml':
					$path 	= '/components/com_menus/menu.content.blog.xml';
					$result = $this->_checkPath( $path, -1 );
					break;

				case 'tablemenu_xml':
					$path 	= '/components/com_menus/menu.content.table.xml';
					$result = $this->_checkPath( $path, -1 );
					break;

				case 'installer_html':
					$path 	= '/components/com_installer/'. $user_option .'/'. $user_option .'.html.php';
					$result = $this->_checkPath( $path, -1 );
					break;

				case 'installer_class':
					$path 	= '/components/com_installer/'. $user_option .'/'. $user_option .'.class.php';
					$result = $this->_checkPath( $path, -1 );
					break;

				case 'admin_functions':
					$path 	= '/components/'. $user_option .'/'. $name .'.functions.php';
					$result = $this->_checkPath( $path, -1 );
					break;
			}
		}

		return $result;
	}

	/**
	 * Gets the base path for the client
	 * @param mixed A client identifier
	 * @param boolean True (default) to add traling slash
	 */
	function getBasePath( $addTrailingSlash=true, $client = null ) {
		$client = is_null($client) ? $this->_client : $client;

		switch ($client) {

			case '2':
				return JPath::clean( JPATH_SITE . '/installation', $addTrailingSlash );
				break;

			case '1':
				return JPath::clean( JPATH_ADMINISTRATOR . '', $addTrailingSlash );
				break;

			case '0':
			default:
				return JPath::clean( JPATH_SITE, $addTrailingSlash );
				break;

		}
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
	* Depreacted, use JPage->renderHead instead
	* @since 1.1
	*/
	 function getHead() {
		$page=& $this->getPage();
		return $page->renderHead();
	 }
	 
	/**
	* Depreacted, use JPage->setMetadata instead
	* @since 1.1
	*/
	function addMetaTag( $name, $content, $prepend='', $append='' ) {
		$page=& $this->getPage();
		$page->setMetadata($name, $content);
	}
	
	/**
	* Depreacted, use JPage->setMetadata instead
	* @since 1.1
	*/
	function appendMetaTag( $name, $content ) {
		$this->addMetaTag($name, $content);
	}

	/**
	* Depreacted, use JPage->setMetadata instead
	* @since 1.1
	*/
	function prependMetaTag( $name, $content ) {
		$this->addMetaTag($name, $content);
	}
	 
	/**
	* Depreacted, use JPage->setTitle instead
	* @since 1.1
	*/
	function setPageTitle( $title=null ) {
		$page=& $this->getPage();
		$page->setTitle($title);
	}
	
	/**
	* Depreacted, use JPage->getTitle instead
	* @since 1.1
	*/
	function getPageTitle() {
		$page=& $this->getPage();
		return $page->getTitle();
	}
	
	/**
	* Depreacted, use JPage->addCustomTag instead
	* @since 1.1
	*/
	function addCustomHeadTag( $html ) {
		$page=& $this->getPage();
		return $page->addCustomTag($html);
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
}

class JApplicationHelper
{
	/**
	* @return correct Itemid for Content Item
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
	* @return number of Published Blog Sections
	*/
	function getBlogSectionCount( ) {
		global $database;

		$query = "SELECT COUNT( m.id )"
		."\n FROM #__content AS i"
		."\n LEFT JOIN #__sections AS s ON i.sectionid = s.id"
		."\n LEFT JOIN #__menu AS m ON m.componentid = s.id "
		."\n WHERE m.type = 'content_blog_section'"
		."\n AND m.published = 1"
		;
		$database->setQuery( $query );
		$count = $database->loadResult();
		return $count;
	}

	/**
	* @return number of Published Blog Categories
	*/
	function getBlogCategoryCount( ) {
		global $database;

		$query = "SELECT COUNT( m.id )"
		. "\n FROM #__content AS i"
		. "\n LEFT JOIN #__categories AS c ON i.catid = c.id"
		. "\n LEFT JOIN #__menu AS m ON m.componentid = c.id "
		. "\n WHERE m.type = 'content_blog_category'"
		. "\n AND m.published = 1"
		;
		$database->setQuery( $query );
		$count = $database->loadResult();
		return $count;
	}

	/**
	* @return number of Published Global Blog Sections
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
	* @return number of Static Content
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
	* @return number of Content Item Links
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
}

?>
