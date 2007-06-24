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
		// if a language was specified it has priority
		// otherwise use user or default language settings
		if (empty($options['language'])) {
			$user = & JFactory::getUser();
			$lang	= $user->getParam( 'admin_language' );

			// Make sure that the user's language exists
			if ( $lang && JLanguage::exists($lang) ) {
				$options['language'] = $lang;
			} else {
				$options['language'] = $this->getCfg('lang_administrator');
			}

		}

		// One last check to make sure we have something
		if ( ! JLanguage::exists($options['language']) ) {
			$options['language'] = 'en-GB';
		}

		parent::initialise($options);
	}

	/**
	* Route the application
	*
	* @access public
	*/
	function route()
	{

	}

	/**
	* Dispatch the application
	*
	* @access public
	*/
	function dispatch($component)
	{
		$document	=& JFactory::getDocument();
		$config		=& JFactory::getConfig();
		$user		=& JFactory::getUser();

		switch($document->getType())
		{
			case 'html' :
			{
				$document->setMetaData( 'keywords', 		$this->getCfg('MetaKeys') );
				// TODO NOTE: Here we are checking for Konqueror - If they fix thier issue with compressed, we will need to update this
				$konkcheck = phpversion() <= "4.2.1" ? getenv( "HTTP_USER_AGENT" ) : $_SERVER['HTTP_USER_AGENT'];
				$konkcheck = strpos (strtolower($konkcheck), "konqueror");
				if ($config->getValue('config.debug') || $konkcheck ) {
					$document->addScript( '../includes/js/mootools-uncompressed.js');
				} else {
					$document->addScript( '../includes/js/mootools.js');
				}

				if ( $user->get('id') ) {
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
	function render()
	{
		$component	= JRequest::getCmd('option');
		$template	= $this->getTemplate();
		$file 		= JRequest::getCmd('tmpl', 'index');

		if($component == 'com_login') {
			$file = 'login';
		}

		$params = array(
			'template' 	=> $template,
			'file'		=> $file.'.php',
			'directory'	=> JPATH_THEMES
		);

		$document =& JFactory::getDocument();
		$data = $document->render($this->getCfg('caching'), $params );
		JResponse::setBody($data);
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
		$result = parent::login($username, $password, $remember, false);

		if(!JError::isError($result))
		{
			$lang = JRequest::getCmd('lang');
			$lang = preg_replace( '/[^A-Z-]/i', '', $lang );
			$this->setUserState( 'application.lang', $lang  );

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
	 * Set the configuration
	 *
	 * @access public
	 * @param string	The path to the configuration file
	 * @param string	The type of the configuration file
	 * @since 1.5
	 */
	function loadConfiguration($file, $type = 'config')
	{
		parent::loadConfiguration($file, $type);

		$registry =& JFactory::getConfig();
		$registry->setValue('config.live_site', substr_replace($this->getSiteURL(), '', -1, 1));
		$registry->setValue('config.absolute_path', JPATH_SITE);
	}

	/**
	 * Get the template
	 *
	 * @return string The template name
	 * @since 1.0
	 */
	function getTemplate()
	{
		static $template;

		if (!isset($template))
		{
			// Load the template name from the database
			$db =& JFactory::getDBO();
			$query = 'SELECT template'
				. ' FROM #__templates_menu'
				. ' WHERE client_id = 1'
				. ' AND menuid = 0'
				;
			$db->setQuery( $query );
			$template = $db->loadResult();

			$template = JInputFilter::clean($template, 'cmd');

			if (!file_exists(JPATH_THEMES.DS.$template.DS.'index.php')) {
				$template = 'khepri';
			}
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

		$query = 'SELECT *'
		. ' FROM #__messages_cfg'
		. ' WHERE user_id = ' . (int) $userid
		. ' AND cfg_name = "auto_purge"'
		;
		$db->setQuery( $query );
		$config = $db->loadObject( );

		// check if auto_purge value set
		if (is_object( $config ) and $config->cfg_name == 'auto_purge' )
		{
			$purge 	= $config->cfg_value;
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

			$query = 'DELETE FROM #__messages'
			. ' WHERE date_time < ' . $db->Quote( $past )
			. ' AND user_id_to = ' . (int) $userid
			;
			$db->setQuery( $query );
			$db->query();
		}
	}
}

?>