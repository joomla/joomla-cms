<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.helper');

/**
* Joomla! Application class
*
* Provide many supporting API functions
*
* @package		Joomla
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
	* Initialise the application.
	*
	* @access public
	* @param array An optional associative array of configuration settings.
	*/
	function initialise($options = array())
	{
		// if a language was specified at login it has priority
		// otherwise use user or default language settings
		if (empty($options['language'])) {
			$user =& JFactory::getUser();
			$options['language'] = $user->getParam( 'admin_language', $this->getCfg('lang_administrator') );
		}

		//One last check to make sure we have something
		if (empty($options['language'])) {
			$options['language'] = 'en-GB';
		}

		$config = & JFactory::getConfig();
		if ($config->getValue('config.legacy')) {
			jimport('joomla.common.legacy');
		}

		parent::initialise($options);
	}

	/**
	* Execute the application
	*
	* @access public
	*/
	function execute( $component )
	{
		$document	=& JFactory::getDocument();
		$user		=& JFactory::getUser();

		switch($document->getType())
		{
			case 'html' :
			{
				$document->setMetaData( 'keywords', 		$this->getCfg('MetaKeys') );

				if ( $user->get('id') ) {
					$document->addScript( '../includes/js/joomla/common.js');
					$document->addScript( '../includes/js/joomla.javascript.js');
				}
			} break;

			default : break;
		}

		$document->setTitle( $this->getCfg('sitename' ). ' - ' .JText::_( 'Administration' ));
		$document->setDescription( $this->getCfg('MetaDesc') );

		$contents = JComponentHelper::renderComponent($component);
		$document->setBuffer($contents, 'component');
	}

	/**
	* Display the application.
	*
	* @access public
	*/
	function display( $component )
	{
		$template	= JRequest::getVar( 'template', $this->getTemplate(), 'default', 'string' );
		$file 		= JRequest::getVar( 'tmpl', 'index',  '', 'string'  );

		if($component == 'com_login') {
			$file = 'login';
		}

		$params = array(
			'template' 	=> $template,
			'file'		=> $file.'.php',
			'directory'	=> JPATH_BASE.DS.'templates'
		);

		$document =& JFactory::getDocument();
		$document->display($this->getCfg('caching_tmpl'), $params );
	}

	/**
	* Login authentication function
	*
	* @param string The username
	* @param string The password
	* @access public
	* @see JApplication::login
	*/
	function login($username=null, $password=null, $remember = false)
	{
		$username = trim( JRequest::getVar( 'username', $username, 'post' ) );
		$password = trim( JRequest::getVar( 'passwd', $password, 'post' ) );
		$remember = JRequest::getVar( 'remember', $remember, 'post' );

		$result = parent::login($username, $password, $remember);
		if(!JError::isError($result))
		{
			$lang = JRequest::getVar( 'lang' );
			$this->setUserState( 'application.lang', $lang  );

			$session =& JFactory::getSession();
			$session->pause();

			JAdministrator::purgeMessages();
		}

		return $result;
	}

	/**
	* Logout authentication function
	*
	* @access public
	* @see JApplication::login
	*/
	function logout() {
		return parent::logout();
	}

	/**
	* Set Page Title
	*
	* @param string $title The title for the page
	* @since 1.5
	*/
	function setPageTitle( $title=null )
	{
		$document=& JFactory::getDocument();
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
		$document=& JFactory::getDocument();
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
		$registry->setValue('config.live_site', substr_replace($this->getSiteURL(), '', -1, 1));
		$registry->setValue('config.absolute_path', JPATH_SITE);

		// Create the JConfig object
		$config = new JConfig();

		if ( $config->legacy == 1 )
		{
			//Insert configuration values into global scope (for backwards compatibility)
			foreach (get_object_vars($config) as $k => $v) {
				$name = 'mosConfig_'.$k;
				$GLOBALS[$name] = $v;
			}

			$GLOBALS['mosConfig_live_site']		= substr_replace($this->getSiteURL(), '', -1, 1);
			$GLOBALS['mosConfig_absolute_path']	= JPATH_SITE;
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
		$session =& $this->_createSession($name);

		if ($session->getState() == 'expired')
		{
			// Build the URL
			$uri = JFactory::getURI();
			$url = basename($uri->getPath());
			$url .= $uri->toString(array('query', 'fragment'));

			// Build the user state
			$state = new stdClass();
			$state->post	= $_POST;
			$state->get		= $_GET;
			$state->request	= $_REQUEST;

			// Store the user state
			$cache	=& JFactory::getCache();
			$user	=& JFactory::getUser();
			$cache->save(serialize($state), md5($user->get('id')), 'autoLogoutState');

			$this->logout();
		}
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

			// Load template entries for each menuid
			$db =& JFactory::getDBO();
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

		$url = JURI::base();
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
		$db		=& JFactory::getDBO();
		$user	=& JFactory::getUser();

		$userid = $user->get('id');

		$query = "SELECT *"
		. "\n FROM #__messages_cfg"
		. "\n WHERE user_id = " . (int) $userid
		. "\n AND cfg_name = 'auto_purge'"
		;
		$db->setQuery( $query );
		$user = $db->loadObject( );

		// check if auto_purge value set
		if (is_object( $user ) and $user->cfg_name == 'auto_purge' )
		{
			$purge 	= $user->cfg_value;
		}
		else
		{
			// if no value set, default is 7 days
			$purge 	= 7;
		}
		// calculation of past date

		// if purge value is not 0, then allow purging of old messages
		if ($purge > 0)
		{
			// purge old messages at day set in message configuration

			$past = date( 'Y-m-d H:i:s', time() - $purge * 86400 );

			$query = "DELETE FROM #__messages"
			. "\n WHERE date_time < " . $db->Quote( $past )
			. "\n AND user_id_to = " . (int) $userid
			;
			$db->setQuery( $query );
			$db->query();
		}
	}

	function loadStoredUserState()
	{
		// Get the stored the user state if it exists
		$cache	= & JFactory::getCache();
		$user	= & JFactory::getUser();
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
 * @package		Joomla
 * @static
 */
class JAdministratorHelper
{
	/**
	 * Return the application option string [main component]
	 *
	 * @access public
	 * @return string Option
	 * @since 1.5
	 */
	function findOption()
	{
		$option = strtolower(JRequest::getVar('option', null));

		$session =& JFactory::getSession();
		if (is_null($session->get('session.user.id')) || !$session->get('session.user.id')) {
			$option = 'com_login';
		}

		if(empty($option)) {
			$option = 'com_cpanel';

		}

		return JRequest::setVar('option', $option);
	}
}
?>