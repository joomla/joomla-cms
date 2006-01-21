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

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once( 'includes/framework.php' );

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
	* Set Page Title
	* 
	* @param string $title The title for the page
	* @since 1.1
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
	* @since 1.1
	*/
	function getPageTitle() 
	{
		$document=& $this->getDocument();
		return $document->getTitle();
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
			$cur_template = 'joomla_admin';
		}
		
		return $template;
	}
	
	/**
	* Get the url of the site 
	* 
	* @return string The site URL
	* @since 1.0
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
}

/** 
 * @global $_VERSION 
 */
$_VERSION = new JVersion();

/** 
 * @global $_PROFILER
 */
$_PROFILER = new JProfiler( 'Core' );

/**
 *  Legacy global
 * 	use JApplicaiton->registerEvent and JApplication->triggerEvent for event handling
 *  use JPlugingHelper::importGroup and JPluginHelper::import to load bot code
 *  @deprecated As of version 1.1
 */
$_MAMBOTS = new mosMambotHandler();

?>