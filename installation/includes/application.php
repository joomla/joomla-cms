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
		$this->_createConfiguration();
	}

	/**
	* Initialise the application.
	*
	* @access public
	*/
	function initialise( $options = array())
	{
		if(empty($options['language']))
		{
			$forced = JInstallationHelper::getLocalise();
			if ( empty($forced['lang'])) {
				$options['language'] = JLanguageHelper::detectLanguage();
			} else {
				$options['language'] = $forced['lang'];
			}
		}

		// One last check to make sure we have something
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
	function setSession($name)
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