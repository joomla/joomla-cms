<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.helper');

/**
 * Joomla! Application class
 *
 * Provide many supporting API functions
 *
 * @package		Joomla.Site
 * @subpackage	Application
 */
final class JSite extends JApplication
{
	/**
	 * Currently active template
	 * @var object
	 */
	private $template = null;

	/**
	 * Class constructor
	 *
	 * @param	array An optional associative array of configuration settings.
	 * Recognized key values include 'clientId' (this list is not meant to be comprehensive).
	 */
	public function __construct($config = array())
	{
		$config['clientId'] = 0;
		parent::__construct($config);
	}

	/**
	 * Initialise the application.
	 *
	 * @param	array
	 */
	public function initialise($options = array())
	{
		$config =& JFactory::getConfig();
		// if a language was specified it has priority
		// otherwise use user or default language settings
		if (empty($options['language']))
		{
			// Detect user language
			$user = & JFactory::getUser();
			$lang	= $user->getParam('language');

			// Make sure that the user's language exists
			if ($lang && JLanguage::exists($lang)) {
				$options['language'] = $lang;
			}
		}
		if (empty($options['language']))
		{
			// Detect cookie language
			jimport('joomla.utilities.utility');
			$lang = JRequest::getString(JUtility::getHash('com_languages.tag'), null ,'cookie');

			// Make sure that the user's language exists
			if ($lang && JLanguage::exists($lang)) {
				$options['language'] = $lang;
			}
		}
		if (empty($options['language']))
		{
			// Detect browser language
			$options['language'] = JLanguageHelper::detectLanguage();
		}
		if (empty($options['language']))
		{
			// Detect default language
			$params =  JComponentHelper::getParams('com_languages');
			$client	= &JApplicationHelper::getClientInfo($this->getClientId());
			$options['language'] = $params->get($client->name, $config->getValue('config.language','en-GB'));
		}
		

		// One last check to make sure we have something
		if (!JLanguage::exists($options['language']))
		{
			$lang = $config->getValue('config.language','en-GB');
			if (JLanguage::exists($lang)) {
				$options['language'] = $lang;
			}
			else {
				$options['language'] = 'en-GB'; // as a last ditch fail to english
			}
		}
		parent::initialise($options);
	}

	/**
	 * Route the application.
	 *
	 */
	public function route()
	{
		parent::route();

		$Itemid = JRequest::getInt('Itemid');
		$this->authorize($Itemid);
	}

	/**
	 * Dispatch the application
	 *
	 * @param	string
	 */
	public function dispatch($component = null)
	{
		// Get the component if not set.
		if (!$component) {
			$component = JRequest::getCmd('option');
		}

		$document	= &JFactory::getDocument();
		$user		= &JFactory::getUser();
		$router		= &$this->getRouter();
		$params		= &$this->getParams();

		switch($document->getType())
		{
			case 'html':
				//set metadata
				$document->setMetaData('keywords', $this->getCfg('MetaKeys'));

				if ($router->getMode() == JROUTER_MODE_SEF) {
					$document->setBase(JURI::current());
				}
				break;

			case 'feed':
				$document->setBase(JURI::current());
				break;
		}

		$document->setTitle($params->get('page_title'));
		$document->setDescription($params->get('page_description'));

		$contents = JComponentHelper::renderComponent($component);
		$document->setBuffer($contents, 'component');

		// Trigger the onAfterDispatch event.
		JPluginHelper::importPlugin('system');
		$this->triggerEvent('onAfterDispatch');
	}

	/**
	 * Display the application.
	 */
	public function render()
	{
		$document	= &JFactory::getDocument();
		$user		= &JFactory::getUser();

		// get the format to render
		$format = $document->getType();

		switch ($format)
		{
			case 'feed':
				$params = array();
				break;

			case 'html':
			default:
				$template	= $this->getTemplate(true);
				$file		= JRequest::getCmd('tmpl', 'index');

				if ($this->getCfg('offline') && $user->get('gid') < '23') {
					$file = 'offline';
				}
				if (!is_dir(JPATH_THEMES.DS.$template->template) && !$this->getCfg('offline')) {
					$file = 'component';
				}
				$params = array(
					'template'	=> $template->template,
					'file'		=> $file.'.php',
					'directory'	=> JPATH_THEMES,
					'params'	=> $template->params
				);
				break;
		}

		// Parse the document.
		$document = &JFactory::getDocument();
		$document->parse($params);

		// Trigger the onBeforeRender event.
		JPluginHelper::importPlugin('system');
		$this->triggerEvent('onBeforeRender');

		// Render the document.
		JResponse::setBody($document->render($this->getCfg('caching'), $params));

		// Trigger the onAfterRender event.
		$this->triggerEvent('onAfterRender');
	}

	/**
	 * Login authentication function
	 *
	 * @param	array	Array('username' => string, 'password' => string)
	 * @param	array	Array('remember' => boolean)
	 *
	 * @see JApplication::login
	 */
	public function login($credentials, $options = array())
	{
		 // Set the application login entry point
		if (!array_key_exists('entry_url', $options)) {
			$options['entry_url'] = JURI::base().'index.php?option=com_users&task=user.login';
		}

		// Set the access control action to check.
		$options['action'] = 'core.login.site';

		return parent::login($credentials, $options);
	}

	/**
	 * Check if the user can access the application
	 */
	public function authorize($itemid)
	{
		$menus	= &JSite::getMenu();
		$user	= &JFactory::getUser();

		if (!$menus->authorise($itemid))
		{
			if ($user->get('id') == 0)
			{
				// Redirect to login
				$uri		= JFactory::getURI();
				$return		= (string)$uri;

				$this->setUserState('users.login.form.data',array( 'return' => $return ) );

				$url	= 'index.php?option=com_users&view=login';
				$url	= JRoute::_($url, false);

				$this->redirect($url, JText::_('YOU_MUST_LOGIN_FIRST'));
			}
			else {
				JError::raiseError(403, JText::_('ALERTNOTAUTH'));
			}
		}
	}

	/**
	 * Get the appliaction parameters
	 *
	 * @param	string	The component option
	 * @return	object	The parameters object
	 * @since	1.5
	 */
	public function &getParams($option = null)
	{
		static $params = array();

		$hash = '__default';
		if (!empty($option)) {
			$hash = $option;
		}
		if (!isset($params[$hash]))
		{
			// Get component parameters
			if (!$option) {
				$option = JRequest::getCmd('option');
			}
			$params[$hash] = &JComponentHelper::getParams($option);

			// Get menu parameters
			$menus	= &JSite::getMenu();
			$menu	= $menus->getActive();

			$title = htmlspecialchars_decode($this->getCfg('sitename'));
			$description = $this->getCfg('MetaDesc');

			// Lets cascade the parameters if we have menu item parameters
			if (is_object($menu))
			{
				$params[$hash]->merge(new JParameter($menu->params));
				$title = $menu->title;
			}

			$params[$hash]->def('page_title', $title);
			$params[$hash]->def('page_description', $description);
		}

		return $params[$hash];
	}

	/**
	 * Get the appliaction parameters
	 *
	 * @param	string	The component option
	 *
	 * @return	object	The parameters object
	 * @since	1.5
	 */
	public function &getPageParameters($option = null)
	{
		return $this->getParams($option);
	}

	/**
	 * Get the template
	 *
	 * @return string The template name
	 * @since 1.0
	 */
	public function getTemplate($params = false)
	{
		if(is_object($this->template))
		{
			if ($params) {
				return $this->template;
			}
			return $this->template->template;
		}
		// Get the id of the active menu item
		$menu = &$this->getMenu();
		$item = $menu->getActive();

		$id = 0;
		if (is_object($item)) { // valid item retrieved
			$id = $item->template_style_id;
		}
		$condition = '';

		$tid = JRequest::getInt('template', 0);
		if ((int) $tid > 0) {
			$id = (int) $tid;
		}
		if ($id == 0) {
			$condition = 'home = 1';
		}
		else {
			$condition = 'id = '.(int) $id;
		}

		// Load template entries for the active menuid and the default template
		$db = &JFactory::getDbo();
		$query = 'SELECT template, params'
			. ' FROM #__template_styles'
			. ' WHERE client_id = 0 AND '.$condition
			;
		$db->setQuery($query, 0, 1);
		$template = $db->loadObject();

		// Allows for overriding the active template from the request
		$template->template = JRequest::getCmd('template', $template->template);
		$template->template = JFilterInput::getInstance()->clean($template->template, 'cmd'); // need to filter the default value as well

		// Fallback template
		if (!file_exists(JPATH_THEMES.DS.$template->template.DS.'index.php')) {
			$template->template = 'rhuk_milkyway';
		}

		$template->params = new JParameter($template->params);

		// Cache the result
		$this->template = $template;
		if ($params) {
			return $template;
		}
		return $template->template;
	}

	/**
	 * Overrides the default template that would be used
	 *
	 * @param string The template name
	 */
	public function setTemplate($template)
	{
		if (is_dir(JPATH_THEMES.DS.$template)) {
			$this->template = new stdClass();
			$this->template->params = new JParameter();
			$this->template->template = $template;
		}
	}

	/**
	 * Return a reference to the JPathway object.
	 *
	 * @return object JPathway.
	 * @since 1.5
	 */
	public function &getMenu()
	{
		$options	= array();
		$menu		= &parent::getMenu('site', $options);
		return $menu;
	}

	/**
	 * Return a reference to the JPathway object.
	 *
	 * @return object JPathway.
	 * @since 1.5
	 */
	public function &getPathWay()
	{
		$options = array();
		$pathway = &parent::getPathway('site', $options);
		return $pathway;
	}

	/**
	 * Return a reference to the JRouter object.
	 *
	 * @return	JRouter.
	 * @since	1.5
	 */
	static public function &getRouter()
	{
		$config = &JFactory::getConfig();
		$options['mode'] = $config->getValue('config.sef');
		$router = &parent::getRouter('site', $options);
		return $router;
	}
}
