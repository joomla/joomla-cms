<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Installation
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

require_once( JPATH_BASE.'/includes/framework.php' );

/**
* Joomla! Application class
*
* Provide many supporting API functions
*
* @package Joomla
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
	function __construct( ) {
		parent::__construct(2);
		$this->_createConfiguration();
	}

	/**
	 * Set the application language
	 *
	 * @access private
	 * @param string 	The language name
	 */

	function setLanguage($lang = null)
	{
		if(empty($lang)) {
			$lang = JLanguageHelper::detectLanguage();
		}

		/*
		 * One last check to make sure we have something
		 */
		if (empty($lang)) {
			$lang = 'en-GB';
		}

		//Set the language in the class
		$this->_lang =& JLanguage::getInstance( $lang );
		$this->_lang->setDebug( $this->getCfg('debug') );
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
		
		//set document link
		$doc->setLink( $this->getBaseURL() );

		return $this->_document;
	}

	/**
	 * Create the user session
	 *
	 * @access private
	 * @param string	The sessions name
	 */
	function _createSession( $name )
	{
		JSession::useCookies(true);
		JSession::start(md5( $name ));

		JSession::get('registry', new JRegistry('application'));

		JSession::setIdle(900);

		if (JSession::isIdle()) {
			JSession::destroy();
		}

		JSession::updateIdle();
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
		$url = str_replace('installation/', '', $url);
		$this->_siteURL = $url;
		return $url;
	}
}

/**
 * @global $_VERSION
 */
$_VERSION = new JVersion();

?>