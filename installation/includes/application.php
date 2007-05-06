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

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once( JPATH_BASE.DS.'includes'.DS.'framework.php' );

/**
* Joomla! Application class
*
* Provide many supporting API functions
*
* @package		Joomla
* @final
*/
class JInstallation extends JApplication
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
	*/
	function __construct( )
	{
		parent::__construct(2);

		JError::setErrorHandling(E_ALL, 'Ignore');
		$this->_createConfiguration();
	}

	/**
	 * Render the application
	 *
	 * @access public
	 */
	function render()
	{

		$document	=& JFactory::getDocument();
		$config		=& JFactory::getConfig();
		$user		=& JFactory::getUser();

		switch($document->getType())
		{
			case 'html':
				//set metadata
				$document->setTitle(JText::_('PAGE_TITLE'));
				break;

			default: break;
		}

		// Define component path
		define( 'JPATH_COMPONENT',					JPATH_BASE.DS.'installer');
		define( 'JPATH_COMPONENT_SITE',				JPATH_SITE.DS.'installer');
		define( 'JPATH_COMPONENT_ADMINISTRATOR',	JPATH_ADMINISTRATOR.DS.'installer');

		// Execute the component
		ob_start();
		require_once(JPATH_COMPONENT.DS.'installer.php');
		$contents = ob_get_contents();
		ob_end_clean();

		$params = array(
			'template' 	=> 'template',
			'file'		=> 'index.php',
			'directory' => JPATH_THEMES
		);

		$document->setBuffer( $contents, 'installation');
		$document->setTitle(JText::_('PAGE_TITLE'));
		JResponse::setBody($document->render( false, $params));
	}

	/**
	* Initialise the application.
	*
	* @access public
	*/
	function initialise( $options = array())
	{
		// Check URL arguement - useful when user has just set the language preferences
		if(empty($options['language']))
		{
			$vars		= JRequest::getVar('vars');
			if ( is_array($vars) && ! empty($vars['lang'])  )
			{
				$varLang	= $vars['lang'];
				$options['language']	= $varLang;
			}
		}

		// Check the application state - useful when the user has previously set the language preference
		if(empty($options['language']))
		{
			$configLang = $this->getUserState('application.lang');
			if ( $configLang )
			{
				$options['language']	= $configLang;
			}
		}

		// This could be a first-time visit - try to determine what the client accepts
		if(empty($options['language']))
		{
			$forced = $this->getLocalise();
			if ( empty($forced['lang'])) {
				$options['language'] = JLanguageHelper::detectLanguage();
			} else {
				$options['language'] = $forced['lang'];
			}
		}

		// Give the user English
		if (empty($options['language'])) {
			$options['language'] = 'en-GB';
		}

		//Set the language in the class
		$conf =& JFactory::getConfig();
		$conf->setValue('config.language', $options['language']);
	}

	/**
	 * Set configuration values
	 *
	 * @access private
	 * @param array 	Array of configuration values
	 * @param string 	The namespace
	 */
	function setCfg( $vars, $namespace = 'config' ) {
		$this->_registry->loadArray( $vars, $namespace );
	}

	/**
	 * Create the configuration registry
	 *
	 * @access private
	 */
	function _createConfiguration()
	{
		jimport( 'joomla.registry.registry' );

		// Create the registry with a default namespace of config which is read only
		$this->_registry = new JRegistry( 'config' );
	}

	/**
	* Get the template
	*
	* @return string The template name
	*/
	function getTemplate()
	{
		return 'template';
	}

	/**
	 * Set the user session.
	 *
	 * @access public
	 * @param string The session's name.
	 */
	function loadSession($name)
	{
		$session =& $this->_createSession($name);
	}

	/**
	 * Create the user session
	 *
	 * @access private
	 * @param string		The sessions name
	 * @return	object 		JSession
	 */
	function &_createSession( $name )
	{
		$options = array();
		$options['name'] = $name;

		$session = &JFactory::getSession($options);
		if (!is_a($session->get('registry'), 'JRegistry')) {
			// Registry has been corrupted somehow
			$session->set('registry', new JRegistry('session'));
		}

		return $session;
	}

	/**
	 * returns the langauge code and help url set in the localise.xml file.
	 * Used for forcing a particular language in localised releases
	 */
	function getLocalise()
	{
		jimport('joomla.factory');
		$xml = & JFactory::getXMLParser('Simple');
		if (!$xml->loadFile(JPATH_SITE.DS.'installation'.DS.'localise.xml')) {
			return 'no file'; //null;
		}

		// Check that it's a localise file
		if ($xml->document->name() != 'localise') {
			return 'not a localise'; //null;
		}
		$tags =  $xml->document->children();
		$ret = array();
		$ret['lang'] = $tags[0]->data();
		$ret['helpurl'] = $tags[1]->data();
		return  $ret;

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
		$url = str_replace('installation/', '', $url);
		$this->_siteURL = $url;
		return $url;
	}
}

?>