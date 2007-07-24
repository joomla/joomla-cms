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
class JSite extends JApplication
{
	/**
	 * The pathway store (for breadcrumb generation).
	 *
	 * @var object  JPathWay object
	 * @access protected
	 */
	var $_pathway = null;

	/**
	* Class constructor
	*
	* @access protected
	* @param integer A client id
	*/
	function __construct()
	{
		parent::__construct(0);
	}

	/**
	* Initialise the application.
	*
	* @access public
	*/
	function initialise( $options = array())
	{
		// if a language was specified it has priority
		// otherwise use user or default language settings
		if (empty($options['language']))
		{
			$user = & JFactory::getUser();
			$lang	= $user->getParam( 'language' );

			// Make sure that the user's language exists
			if ( $lang && JLanguage::exists($lang) ) {
				$options['language'] = $lang;
			} else {
				jimport( 'joomla.application.helper' );
				$params = JComponentHelper::getParams('com_languages');
				$client	= JApplicationHelper::getClientInfo($this->getClientId());
				$options['language'] = $params->get($client->name, 'en-GB');
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
	function route() {
		parent::route();
	}

	/**
	* Dispatch the application
	*
	* @access public
	*/
	function dispatch($component)
	{
		// Build the application pathway
		$this->_createPathWay();

		$document	=& JFactory::getDocument();
		$config		=& JFactory::getConfig();
		$user		=& JFactory::getUser();

		switch($document->getType())
		{
			case 'html':
			{
				//set metadata
				$document->setMetaData( 'keywords', $this->getCfg('MetaKeys') );

				if ( $user->get('id') ) {
					$document->addScript( 'includes/js/joomla.javascript.js');
				}
			} break;

			default: break;
		}


		$document->setTitle( $this->getCfg('sitename' ));
		$document->setDescription( $this->getCfg('MetaDesc') );

		$contents = JComponentHelper::renderComponent($component);
		$document->setBuffer( $contents, 'component');
	}

	/**
	* Display the application.
	*
	* @access public
	*/
	function render()
	{
		$document =& JFactory::getDocument();
		$user     =& JFactory::getUser();

		// get the format to render
		$format = $document->getType();

		switch($format)
		{
			case 'feed' :
			{
				$params = array();
			} break;

			case 'html' :
			default     :
			{
				$template	= $this->getTemplate();
				$file 		= JRequest::getCmd('tmpl', 'index');

				if ($this->getCfg('offline') && $user->get('gid') < '23' ) {
					$file = 'offline';
				}
				if (!is_dir( JPATH_THEMES.DS.$template ) && !$this->getCfg('offline')) {
					$file = 'component';
				}
				$params = array(
					'template' 	=> $template,
					'file'		=> $file.'.php',
					'directory'	=> JPATH_THEMES
				);
			} break;
 		}

		$data = $document->render( $this->getCfg('caching'), $params);
		JResponse::setBody($data);
	}

   /**
	* Check if the user can access the application
	*
	* @access public
	*/
	function authorize($itemid)
	{
		//TODO :: should we show a login screen here ?
		$menus =& JMenu::getInstance();
		$user  =& JFactory::getUser();
		if(!$menus->authorize($itemid, $user->get('aid'))) {
			JError::raiseError( 403, JText::_('Not Authorised') );
		}
	}

	/**
	* Login authentication function
	*
	* @param string The username
	* @param string The password
	* @access public
	* @see JApplication::login
	*/
	function login($credentials, $options = array())
	{
		return parent::login($credentials, $options);
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
	* Get Page Parameters
	*
	* @return object The page parameters object
	* @since 1.5
	*/
	function &getPageParameters($option=null)
	{
		static $params;

		if (!is_object($params)) {
			// Get component parameters
			if (!$option) {
				$option = JRequest::getCmd('option');
			}
			$params = &JComponentHelper::getParams($option);

			// Get menu parameters
			$menus	= &JMenu::getInstance();
			$menu	= $menus->getActive();

			// Lets cascade the parameters if we have menu item parameters
			if (is_object($menu)) {
				$params->merge(new JParameter($menu->params));
			}
		}

		return $params;
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
		$registry->setValue('config.live_site', substr_replace(JURI::base(), '', -1, 1));
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
		// Allows for overriding the active template from a component, and caches the result of this function
		// e.g. $mainframe->setTemplate('solar-flare-ii');
		if ($template = $this->get('setTemplate')) {
			return $template;
		}

		// Load template entries for each menuid
		$db =& JFactory::getDBO();
		$query = 'SELECT template, menuid'
			. ' FROM #__templates_menu'
			. ' WHERE client_id = 0'
			;
		$db->setQuery( $query );
		$templates = $db->loadObjectList('menuid');

		// Get the id of the active menu item
		$menu =& JMenu::getInstance();
		$item = $menu->getActive();

		// Find out the assigned template for the active menu item
		if (!empty($item->id) && (isset($templates[$item->id]))) {
			$template = $templates[$item->id]->template;
		} else {
			$template = $templates[0]->template;
		}

		// Allows for overriding the active template from the request
		$template = JRequest::getCmd('template', $template);
		$template = JFilterInput::clean($template, 'cmd'); // need to filter the default value as well

		// Fallback template
		if (!file_exists(JPATH_THEMES.DS.$template.DS.'index.php')) {
			$template = 'rhuk_milkyway';
		}

		// Cache the result
		$this->set('setTemplate', $template);
		return $template;
	}

	/**
	 * Overrides the default template that would be used
	 *
	 * @param string The template name
	 */
	function setTemplate( $template )
	{
		if (is_dir(JPATH_THEMES.DS.$template)) {
			$this->set('setTemplate', $template);
		}
	}

	/**
	 * Return a reference to the JPathWay object.
	 *
	 * @access public
	 * @return object JPathway.
	 * @since 1.5
	 */
	function &getPathWay()
	{
		return $this->_pathway;
	}

	/**
	 * Create a JPathWay object and set the home/component items of the pathway.
	 *
	 * @access private
	 * @return object JPathway.
	 * @since 1.5
	 */
	function &_createPathWay()
	{
		//Load the pathway object
		jimport( 'joomla.application.pathway' );

		// Create a JPathWay object
		$this->_pathway = new JPathWay();

		$menu   =& JMenu::getInstance();
		$item   = $menu->getActive();
		$menus	= $menu->getMenu();
		$home	= $menu->getDefault();

		if( $item->id != $home->id)
		{
			foreach($item->tree as $menupath) {
				$this->_pathway->addItem( $menus[$menupath]->name, 'index.php?Itemid='.$menupath);
			}
		}
		return $this->_pathway;
	}
}
?>