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
	 * Option to filter by language
	 */
	private $_language_filter = false;

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
		$config = JFactory::getConfig();
		
		jimport('joomla.language.helper');

		// if a language was specified it has priority
		// otherwise use user or default language settings
		if (empty($options['language'])) {
			$sef = JRequest::getString('lang', null);
			if (!empty($sef)) {
				$languages = JLanguageHelper::getLanguages('sef');
				if (isset($languages[$sef])) {
					$lang = $languages[$sef]->lang_code;
					// Make sure that the sef's language exists
					if ($lang && JLanguage::exists($lang)) {
						$config = JFactory::getConfig();
						$cookie_domain 	= $config->get('config.cookie_domain', '');
						$cookie_path 	= $config->get('config.cookie_path', '/');
						setcookie(JUtility::getHash('language'), $lang, time() + 365 * 86400, $cookie_path, $cookie_domain);
						$options['language'] = $lang;
					}
				}
			}
		}

		if (empty($options['language'])) {
			// Detect cookie language
			jimport('joomla.utilities.utility');
			$lang = JRequest::getString(JUtility::getHash('language'), null ,'cookie');

			// Make sure that the user's language exists
			if ($lang && JLanguage::exists($lang)) {
				$options['language'] = $lang;
			}
		}

		if (empty($options['language'])) {
			// Detect user language
			$lang = JFactory::getUser()->getParam('language');
			// Make sure that the user's language exists
			if ($lang && JLanguage::exists($lang)) {
				$options['language'] = $lang;
			}
		}

		if (empty($options['language'])) {
			// Detect browser language
			$lang = JLanguageHelper::detectLanguage();
			// Make sure that the user's language exists
			if ($lang && JLanguage::exists($lang)) {
				$options['language'] = $lang;
			}
		}

		if (empty($options['language'])) {
			// Detect default language
			$params =  JComponentHelper::getParams('com_languages');
			$client	= JApplicationHelper::getClientInfo($this->getClientId());
			$options['language'] = $params->get($client->name, $config->get('language', 'en-GB'));
		}

		// One last check to make sure we have something
		if (!JLanguage::exists($options['language'])) {
			$lang = $config->get('language','en-GB');
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

		$document	= JFactory::getDocument();
		$user		= JFactory::getUser();
		$router		= $this->getRouter();
		$params		= $this->getParams();

		switch($document->getType())
		{
			case 'html':
				// Get language
				$lang_code = JFactory::getLanguage()->getTag();
				$languages = JLanguageHelper::getLanguages('lang_code');
				
				// Set metadata
				$document->setMetaData('keywords', $this->getCfg('MetaKeys') . ($languages[$lang_code]->metakey ? (', ' . $languages[$lang_code]->metakey) : ''));
				$document->setMetaData('rights', $this->getCfg('MetaRights'));
				$document->setBase(JURI::root());
				break;

			case 'feed':
				$document->setBase(JURI::root());
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
		$document	= JFactory::getDocument();
		$user		= JFactory::getUser();

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
		$document = JFactory::getDocument();
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
		$menus	= JSite::getMenu();
		$user	= JFactory::getUser();

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
				JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
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
	public function getParams($option = null)
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
			// Get new instance of component global parameters
			$params[$hash] = clone JComponentHelper::getParams($option);

			// Get menu parameters
			$menus	= JSite::getMenu();
			$menu	= $menus->getActive();

			// Get language
			$lang_code = JFactory::getLanguage()->getTag();
			$languages = JLanguageHelper::getLanguages('lang_code');
			
			$title 			= htmlspecialchars_decode($this->getCfg('sitename'));
			$description	= $this->getCfg('MetaDesc') . $languages[$lang_code]->metadesc;
			$rights			= $this->getCfg('MetaRights');
			// Lets cascade the parameters if we have menu item parameters
			if (is_object($menu)) {
				$temp = new JRegistry;
				$temp->loadJSON($menu->params);
				$params[$hash]->merge($temp);
				$title = $menu->title;
			}

			$params[$hash]->def('page_title', $title);
			$params[$hash]->def('page_description', $description);
			$params[$hash]->def('page_rights', $rights);
		}

		return $params[$hash];
	}

	/**
	 * Get the application parameters
	 *
	 * @param	string	The component option
	 *
	 * @return	object	The parameters object
	 * @since	1.5
	 */
	public function getPageParameters($option = null)
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
		$menu = $this->getMenu();
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
		
		$cache = JFactory::getCache('com_templates', '');
		if (!$templates = $cache->get('0')) {
			// Load styles
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('id, home, template, params');
			$query->from('#__template_styles');
			$query->where('client_id = 0');
			
			$db->setQuery($query);
			$templates = $db->loadObjectList('id');
			
			foreach($templates as &$template) {
				$registry = new JRegistry;
				$registry->loadJSON($template->params);
				$template->params = $registry;

				// Create home element
				if ($template->home) {
					$templates[0] = clone $template;
				}
			}
			$cache->store($templates, '0');
		}
		
		$template = $templates[$id];
		
		// Allows for overriding the active template from the request
		$template->template = JRequest::getCmd('template', $template->template);
		$template->template = JFilterInput::getInstance()->clean($template->template, 'cmd'); // need to filter the default value as well

		// Fallback template
		if (!file_exists(JPATH_THEMES.DS.$template->template.DS.'index.php')) {
			$template->template = 'rhuk_milkyway';
		}

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
			$this->template->params = new JRegistry;
			$this->template->template = $template;
		}
	}

	/**
	 * Return a reference to the JPathway object.
	 *
	 * @return object JPathway.
	 * @since 1.5
	 */
	public function getMenu()
	{
		$options	= array();
		$menu		= parent::getMenu('site', $options);
		return $menu;
	}

	/**
	 * Return a reference to the JPathway object.
	 *
	 * @return object JPathway.
	 * @since 1.5
	 */
	public function getPathWay()
	{
		$options = array();
		$pathway = parent::getPathway('site', $options);
		return $pathway;
	}

	/**
	 * Return a reference to the JRouter object.
	 *
	 * @return	JRouter.
	 * @since	1.5
	 */
	static public function getRouter()
	{
		$config = JFactory::getConfig();
		$options['mode'] = $config->get('sef');
		$router = parent::getRouter('site', $options);
		return $router;
	}

	/**
	 * Return the current state of the language filter.
	 *
	 * @return	boolean
	 * @since	1.6
	 */
	public function getLanguageFilter()
	{
		return $this->_language_filter;
	}

	/**
	 * Set the current state of the language filter.
	 *
	 * @return	boolean	The old state
	 * @since	1.6
	 */
	public function setLanguageFilter($state=false)
	{
		$old = $this->_language_filter;
		$this->_language_filter=$state;
		return $old;
	}
}
