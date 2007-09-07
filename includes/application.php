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
	* Class constructor
	*
	* @access protected
	* @param	array An optional associative array of configuration settings.
	* Recognized key values include 'clientId' (this list is not meant to be comprehensive).
	*/
	function __construct($config = array())
	{
		$config['clientId'] = 0;
		parent::__construct($config);
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
				$params =  JComponentHelper::getParams('com_languages');
				$client	=& JApplicationHelper::getClientInfo($this->getClientId());
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
		$document	=& JFactory::getDocument();
		$user		=& JFactory::getUser();
		$router     =& $this->getRouter();

		switch($document->getType())
		{
			case 'html':
			{
				//set metadata
				$document->setMetaData( 'keywords', $this->getCfg('MetaKeys') );

				if ( $user->get('id') ) {
					$document->addScript( 'includes/js/joomla.javascript.js');
				}

				if($router->getMode() == JROUTER_MODE_SEF) {
					$document->setBase(JURI::base());
				}
			} break;

			case 'feed':
			{
				$document->setBase(JURI::base());
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
		$menus	=& JSite::getMenu();
		$user	=& JFactory::getUser();
		$aid	= $user->get('aid');

		if(!$menus->authorize($itemid, $aid))
		{
			if ( ! $aid )
			{
				// Redirect to login
				$uri		= JFactory::getURI();
				$return		= $uri->toString();

				$url  = 'index.php?option=com_user&view=login';
				$url .= '&return='.base64_encode($return);;

				//$url	= JRoute::_($url, false);
				$this->redirect($url, "You must login first");
			}
			else
			{
				JError::raiseError( 403, JText::_('Not Authorised') );
			}
		}
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
			$menus	= &JSite::getMenu();
			$menu	= $menus->getActive();

			// Lets cascade the parameters if we have menu item parameters
			if (is_object($menu)) {
				$params->merge(new JParameter($menu->params));
			}
		}

		return $params;
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
		$menu =& JSite::getMenu();
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
	 * Return a reference to the JPathway object.
	 *
	 * @access public
	 * @return object JPathway.
	 * @since 1.5
	 */
	function &getMenu()
	{
		$options = array();
		$menu =& parent::getMenu('site', $options);
		return $menu;
	}

	/**
	 * Return a reference to the JPathway object.
	 *
	 * @access public
	 * @return object JPathway.
	 * @since 1.5
	 */
	function &getPathWay()
	{
		$options = array();
		$pathway =& parent::getPathway('site', $options);
		return $pathway;
	}

	/**
	 * Return a reference to the JRouter object.
	 *
	 * @access	public
	 * @return	JRouter.
	 * @since	1.5
	 */
	function &getRouter()
	{
		$options['mode'] = $this->getCfg('sef');
		if($options['mode'] && !$this->getCfg('sef_rewrite')) {
			$options['prefix'] = 'index.php/';
		}

		$router =& parent::getRouter('site', $options);
		return $router;
	}
}