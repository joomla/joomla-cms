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
		
		$this->_createPathWay( );
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

		return parent::login($username, $password);
	}

	/**
	* Logout authentication function
	*
	* @access public
	* @see JApplication::login
	*/
	function logout($return = null) {
		return parent::logout();
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
			case 'html':
				//set metadata
				$doc->setMetaData( 'keywords', 		$this->getCfg('MetaKeys') );

				if ( $user->get('id') ) {
					$doc->addScript( 'includes/js/joomla/common.js');
					$doc->addScript( 'includes/js/joomla.javascript.js');
				}
				break;

			default:
				break;
		}

		return $this->_document;
	}
	
	/**
	 * Return the application itemid
	 *
	 * @access public
	 * @return string Option
	 * @since 1.5
	 */
	function getItemid() 
	{
		$itemid = JRequest::getVar( 'Itemid', 0, '', 'int' );
		$option = strtolower(JRequest::getVar('option', null));
		
		if ( $itemid === 0 ) 
		{
			// checking if we can find the Itemid thru the content
			if($option == 'com_content') 
			{
				require_once (JApplicationHelper::getPath('helper', 'com_content'));
				$id 	= JRequest::getVar( 'id', 0, '', 'int' );
				$itemid = JContentHelper::getItemid($id);
			} 
			else
			{
				$menu =& JMenu::getInstance();
				$itemid = $menu->isDefault();
			}
		}
		
		return $itemid;
	}
	
	/**
	 * Return the application option string [main component]
	 *
	 * @access public
	 * @return string Option
	 * @since 1.5
	 */
	function getOption() 
	{	
		$option = strtolower(JRequest::getVar('option', null));
		
		if(empty($option)) 
		{
			$menu =& JMenu::getInstance();
			$item =& $menu->getItem($this->getItemid());
		
			$component = JTable::getInstance( 'component', $this->getDBO() );
			$component->load($item->componentid);
			
			$option = $component->option;

		}
		
		return $option;
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

		if ($template = $this->getUserState( 'setTemplate' ))
		{
			// ok, allows for an override of the template from a component
			// eg, $mainframe->setTemplate( 'solar-flare-ii' );
		}
		else if (!empty($Itemid) && (isset($templates[$Itemid])))
		{
			$template = $templates[$Itemid];
		}
		else
		{
			$template = $templates[0];
		}

		return $template->template;
	}

	/**
	 * Overrides the default template that would be used
	 * @param string The template name
	 */
	function setTemplate( $template )
	{
		if (is_dir( JPATH_SITE . '/templates/' . $template ))
		{
			$this->setUserState( 'setTemplate', $template );
		}
	}
	
	function setLanguage()
	{
		//get the user
		$user = & $this->getUser();

		// use user or default language settings
		$lang = $user->getParam( 'language', $this->getCfg('lang_site') );

		//One last check to make sure we have something
		if (empty($lang)) {
			$lang = 'en-GB';
		}

		//Set the language in the class
		$conf =& JFactory::getConfig();
		$conf->setValue('config.language', $lang);
		//set language debug
		$lang =& JFactory::getLanguage();
		$lang->setDebug($this->getCfg('debug_lang'));
		
		// create the backward compatible language value for old 3PD components
		$GLOBALS['mosConfig_lang']  = $lang->getBackwardLang();
	}

}


/**
 * @global $_VERSION
 */
$_VERSION = new JVersion();
?>