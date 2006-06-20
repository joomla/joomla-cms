<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once( JPATH_BASE.'/includes/framework.php' );

/**
* Joomla! Application class
*
* Provide many supporting API functions
*
* @package Joomla
* @final
*/
class JAdministrator extends JApplication
{
	/**
	 * The url of the site
	 *
	 * @var string
	 * @access protected
	 */
	var $_siteURL = null;

	/**
	* Class constructor
	*
	* @access protected
	* @param integer A client id
	*/
	function __construct() {
		parent::__construct(1);
	}

	/**
	* Login authentication function
	*
	* @param string The username
	* @param string The password
	* @access public
	* @see JApplication::login
	*/
	function login($username=null, $password=null)
	{
		$username = trim( JRequest::getVar( 'username', '', 'post' ) );
		$password = trim( JRequest::getVar( 'passwd', '', 'post'  ) );

		if (parent::login($username, $password))
		{
			$lang = JRequest::getVar( 'lang' );
			$this->setUserState( 'application.lang', $lang  );
			JSession::pause();

			JAdministrator::purgeMessages();
			return true;
		}

		return false;
	}

	/**
	* Logout authentication function
	*
	* @access public
	* @see JApplication::login
	*/
	function logout() {
		parent::logout();
	}

	/**
	* Set Page Title
	*
	* @param string $title The title for the page
	* @since 1.5
	*/
	function setPageTitle( $title=null )
	{
		$document=& $this->getDocument();
		$document->setTitle($title);
	}

	/**
	* Get Page title
	*
	* @return string The page title
	* @since 1.5
	*/
	function getPageTitle()
	{
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

		$registry =& JFactory::getConfig();
		$registry->setValue('config.live_site', substr_replace($this->getBaseURL(), '', -1, 1));
		$registry->setValue('config.absolute_path', JPATH_SITE);
		
		// Create the JConfig object
		$config = new JConfig();

		//Insert configuration values into global scope (for backwards compatibility)
		foreach (get_object_vars($config) as $k => $v) {
			$name = 'mosConfig_'.$k;
			$GLOBALS[$name] = $v;
		}
	}

	/**
	 * Set the user session
	 *
	 * @access public
	 * @param string	The sessions name
	 */
	function setSession($name)
	{
		$this->_createSession($name);
		if (JSession::isIdle()) {
			// Build the URL
			$uri = JURI::getInstance();
			$url = basename($uri->getPath());
			$url .= $uri->toString(array('query', 'fragment'));

			// Build the user state
			$state = new stdClass();
			$state->post	= $_POST;
			$state->get		= $_GET;
			$state->request	= $_REQUEST;

			// Store the user state
			$cache = & JFactory::getCache();
			$user = $this->getUser();
			$cache->save(serialize($state), md5($user->get('id')), 'autoLogoutState');

			$this->logout();
		}

		JSession::updateIdle();
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

		//set document description
		$doc->setDescription( $this->getCfg('MetaDesc') );

		switch($type)
		{
			case 'html' :
			{
				$doc->setMetaData( 'keywords', 		$this->getCfg('MetaKeys') );

				if ( $user->get('id') ) {
					$doc->addScript( '../includes/js/joomla/common.js');
					$doc->addScript( '../includes/js/joomla.javascript.js');
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
		static $templates;

		if (!isset ($templates))
		{
			$templates = array();

			/*
			 * Load template entries for each menuid
			 */
			$db = $this->getDBO();
			$query = "SELECT template"
				. "\n FROM #__templates_menu"
				. "\n WHERE client_id = 1"
				. "\n AND menuid = 0"
				;
			$db->setQuery( $query );
			$templates[0] = $db->loadResult();
		}

		$template = $templates[0];

		$path = JPATH_ADMINISTRATOR ."/templates/$template/index.php";

		if (!file_exists( $path )) {
			$cur_template = 'khepri';
		}

		return $template;
	}

	/**
	* Get the url of the site
	*
	* @return string The site URL
	* @since 1.5
	*/
	function getSiteURL()
	{
		if(isset($this->_siteURL)) {
			return $this->_siteURL;
		}

		$url = $this->getBaseURL();
		$url = str_replace('administrator/', '', $url);

		$this->_siteURL = $url;
		return $url;
	}

	/**
	* Purge the jos_messages table of old messages
	*
	* static method
	* @since 1.5
	*/
	function purgeMessages()
	{
		$db = $this->getDBO();

		$userid = JSession::get('userid');

		$query = "SELECT *"
		. "\n FROM #__messages_cfg"
		. "\n WHERE user_id = $userid"
		. "\n AND cfg_name = 'auto_purge'"
		;
		$db->setQuery( $query );
		$user = null;
		$db->loadObject( $user );

		// check if auto_purge value set
		if ( $user->cfg_name == 'auto_purge' ) {
			$purge 	= $user->cfg_value;
		} else {
			// if no value set, default is 7 days
			$purge 	= 7;
		}
		// calculation of past date
		$past = date( 'Y-m-d H:i:s', time() - $purge * 60 * 60 * 24 );

		// if purge value is not 0, then allow purging of old messages
		if ($purge != 0) {
			// purge old messages at day set in message configuration
			$query = "DELETE FROM #__messages"
			. "\n WHERE date_time < '$past'"
			. "\n AND user_id_to = $userid"
			;
			$db->setQuery( $query );
			$db->query();
		}
	}

	function loadStoredUserState()
	{
		// Get the stored the user state if it exists
		$cache	= & JFactory::getCache();
		$user	= & $this->getUser();
		$state	= $cache->get(md5($user->get('id')), 'autoLogoutState');
		$cache->remove(md5($user->get('id')), 'autoLogoutState');

		// If the stored user state exists, lets restore it, remove the stored state and go back to where we were.
		if ($state) {
			$state		= unserialize($state);
			$_POST		= $state->post;
			$_GET		= $state->get;
			$_REQUEST	= $state->request;
			return true;
		}
		// No stored user state exists
		return false;
	}
}

/**
 * @global $_VERSION
 */
$_VERSION = new JVersion();


?>