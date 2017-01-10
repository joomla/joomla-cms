<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;

/**
 * Joomla! Administrator Application class
 *
 * @since  3.2
 */
class JApplicationAdministrator extends JApplicationCms
{
	/**
	 * Class constructor.
	 *
	 * @param   JInput                 $input   An optional argument to provide dependency injection for the application's
	 *                                          input object.  If the argument is a JInput object that object will become
	 *                                          the application's input object, otherwise a default input object is created.
	 * @param   Registry               $config  An optional argument to provide dependency injection for the application's
	 *                                          config object.  If the argument is a Registry object that object will become
	 *                                          the application's config object, otherwise a default config object is created.
	 * @param   JApplicationWebClient  $client  An optional argument to provide dependency injection for the application's
	 *                                          client object.  If the argument is a JApplicationWebClient object that object will become
	 *                                          the application's client object, otherwise a default client object is created.
	 *
	 * @since   3.2
	 */
	public function __construct(JInput $input = null, Registry $config = null, JApplicationWebClient $client = null)
	{
		// Register the application name
		$this->_name = 'administrator';

		// Register the client ID
		$this->_clientId = 1;

		// Execute the parent constructor
		parent::__construct($input, $config, $client);

		// Set the root in the URI based on the application name
		JUri::root(null, rtrim(dirname(JUri::base(true)), '/\\'));
	}

	/**
	 * Dispatch the application
	 *
	 * @param   string  $component  The component which is being rendered.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function dispatch($component = null)
	{
		if ($component === null)
		{
			$component = JAdministratorHelper::findOption();
		}

		// Load the document to the API
		$this->loadDocument();

		// Set up the params
		$document = JFactory::getDocument();

		// Register the document object with JFactory
		JFactory::$document = $document;

		switch ($document->getType())
		{
			case 'html':
				$document->setMetaData('keywords', $this->get('MetaKeys'));

				// Get the template
				$template = $this->getTemplate(true);

				// Store the template and its params to the config
				$this->set('theme', $template->template);
				$this->set('themeParams', $template->params);

				break;

			default:
				break;
		}

		$document->setTitle($this->get('sitename') . ' - ' . JText::_('JADMINISTRATION'));
		$document->setDescription($this->get('MetaDesc'));
		$document->setGenerator('Joomla! - Open Source Content Management');

		$contents = JComponentHelper::renderComponent($component);
		$document->setBuffer($contents, 'component');

		// Trigger the onAfterDispatch event.
		JPluginHelper::importPlugin('system');
		$this->triggerEvent('onAfterDispatch');
	}

	/**
	 * Method to run the Web application routines.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	protected function doExecute()
	{
		// Initialise the application
		$this->initialiseApp(array('language' => $this->getUserState('application.lang')));

		// Test for magic quotes
		if (get_magic_quotes_gpc())
		{
			$lang = $this->getLanguage();

			if ($lang->hasKey('JERROR_MAGIC_QUOTES'))
			{
				$this->enqueueMessage(JText::_('JERROR_MAGIC_QUOTES'), 'error');
			}
			else
			{
				$this->enqueueMessage('Your host needs to disable magic_quotes_gpc to run this version of Joomla!', 'error');
			}
		}

		// Mark afterInitialise in the profiler.
		JDEBUG ? $this->profiler->mark('afterInitialise') : null;

		// Route the application
		$this->route();

		// Mark afterRoute in the profiler.
		JDEBUG ? $this->profiler->mark('afterRoute') : null;

		/*
		 * Check if the user is required to reset their password
		 *
		 * Before $this->route(); "option" and "view" can't be safely read using:
		 * $this->input->getCmd('option'); or $this->input->getCmd('view');
		 * ex: due of the sef urls
		 */
		$this->checkUserRequireReset('com_admin', 'profile', 'edit', 'com_admin/profile.save,com_admin/profile.apply,com_login/logout');

		// Dispatch the application
		$this->dispatch();

		// Mark afterDispatch in the profiler.
		JDEBUG ? $this->profiler->mark('afterDispatch') : null;
	}

	/**
	 * Return a reference to the JRouter object.
	 *
	 * @param   string  $name     The name of the application.
	 * @param   array   $options  An optional associative array of configuration settings.
	 *
	 * @return  JRouter
	 *
	 * @since	3.2
	 */
	public static function getRouter($name = 'administrator', array $options = array())
	{
		return parent::getRouter($name, $options);
	}

	/**
	 * Gets the name of the current template.
	 *
	 * @param   boolean  $params  True to return the template parameters
	 *
	 * @return  string  The name of the template.
	 *
	 * @since   3.2
	 * @throws  InvalidArgumentException
	 */
	public function getTemplate($params = false)
	{
		if (is_object($this->template))
		{
			if ($params)
			{
				return $this->template;
			}

			return $this->template->template;
		}

		$admin_style = JFactory::getUser()->getParam('admin_style');

		// Load the template name from the database
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('template, s.params')
			->from('#__template_styles as s')
			->join('LEFT', '#__extensions as e ON e.type=' . $db->quote('template') . ' AND e.element=s.template AND e.client_id=s.client_id');

		if ($admin_style)
		{
			$query->where('s.client_id = 1 AND id = ' . (int) $admin_style . ' AND e.enabled = 1', 'OR');
		}

		$query->where('s.client_id = 1 AND home = ' . $db->quote('1'), 'OR')
			->order('home');
		$db->setQuery($query);
		$template = $db->loadObject();

		$template->template = JFilterInput::getInstance()->clean($template->template, 'cmd');
		$template->params = new Registry($template->params);

		if (!file_exists(JPATH_THEMES . '/' . $template->template . '/index.php'))
		{
			$this->enqueueMessage(JText::_('JERROR_ALERTNOTEMPLATE'), 'error');
			$template->params = new Registry;
			$template->template = 'isis';
		}

		// Cache the result
		$this->template = $template;

		if (!file_exists(JPATH_THEMES . '/' . $template->template . '/index.php'))
		{
			throw new InvalidArgumentException(JText::sprintf('JERROR_COULD_NOT_FIND_TEMPLATE', $template->template));
		}

		if ($params)
		{
			return $template;
		}

		return $template->template;
	}

	/**
	 * Initialise the application.
	 *
	 * @param   array  $options  An optional associative array of configuration settings.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	protected function initialiseApp($options = array())
	{
		$user = JFactory::getUser();

		// If the user is a guest we populate it with the guest user group.
		if ($user->guest)
		{
			$guestUsergroup = JComponentHelper::getParams('com_users')->get('guest_usergroup', 1);
			$user->groups = array($guestUsergroup);
		}

		// If a language was specified it has priority, otherwise use user or default language settings
		if (empty($options['language']))
		{
			$lang = $user->getParam('admin_language');

			// Make sure that the user's language exists
			if ($lang && JLanguageHelper::exists($lang))
			{
				$options['language'] = $lang;
			}
			else
			{
				$params = JComponentHelper::getParams('com_languages');
				$options['language'] = $params->get('administrator', $this->get('language', 'en-GB'));
			}
		}

		// One last check to make sure we have something
		if (!JLanguageHelper::exists($options['language']))
		{
			$lang = $this->get('language', 'en-GB');

			if (JLanguageHelper::exists($lang))
			{
				$options['language'] = $lang;
			}
			else
			{
				// As a last ditch fail to english
				$options['language'] = 'en-GB';
			}
		}

		// Finish initialisation
		parent::initialiseApp($options);
	}

	/**
	 * Login authentication function
	 *
	 * @param   array  $credentials  Array('username' => string, 'password' => string)
	 * @param   array  $options      Array('remember' => boolean)
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.2
	 */
	public function login($credentials, $options = array())
	{
		// The minimum group
		$options['group'] = 'Public Backend';

		// Make sure users are not auto-registered
		$options['autoregister'] = false;

		// Set the application login entry point
		if (!array_key_exists('entry_url', $options))
		{
			$options['entry_url'] = JUri::base() . 'index.php?option=com_users&task=login';
		}

		// Set the access control action to check.
		$options['action'] = 'core.login.admin';

		$result = parent::login($credentials, $options);

		if (!($result instanceof Exception))
		{
			$lang = $this->input->getCmd('lang');
			$lang = preg_replace('/[^A-Z-]/i', '', $lang);

			if ($lang)
			{
				$this->setUserState('application.lang', $lang);
			}

			static::purgeMessages();
		}

		return $result;
	}

	/**
	 * Purge the jos_messages table of old messages
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public static function purgeMessages()
	{
		$user = JFactory::getUser();
		$userid = $user->get('id');

		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__messages_cfg'))
			->where($db->quoteName('user_id') . ' = ' . (int) $userid, 'AND')
			->where($db->quoteName('cfg_name') . ' = ' . $db->quote('auto_purge'), 'AND');
		$db->setQuery($query);
		$config = $db->loadObject();

		// Check if auto_purge value set
		if (is_object($config) and $config->cfg_name == 'auto_purge')
		{
			$purge = $config->cfg_value;
		}
		else
		{
			// If no value set, default is 7 days
			$purge = 7;
		}

		// If purge value is not 0, then allow purging of old messages
		if ($purge > 0)
		{
			// Purge old messages at day set in message configuration
			$past = JFactory::getDate(time() - $purge * 86400);
			$pastStamp = $past->toSql();

			$query->clear()
				->delete($db->quoteName('#__messages'))
				->where($db->quoteName('date_time') . ' < ' . $db->quote($pastStamp), 'AND')
				->where($db->quoteName('user_id_to') . ' = ' . (int) $userid, 'AND');
			$db->setQuery($query);
			$db->execute();
		}
	}

	/**
	 * Rendering is the process of pushing the document buffers into the template
	 * placeholders, retrieving data from the document and pushing it into
	 * the application response buffer.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	protected function render()
	{
		// Get the JInput object
		$input = $this->input;

		$component = $input->getCmd('option', 'com_login');
		$file      = $input->getCmd('tmpl', 'index');

		if ($component == 'com_login')
		{
			$file = 'login';
		}

		$this->set('themeFile', $file . '.php');

		// Safety check for when configuration.php root_user is in use.
		$rootUser = $this->get('root_user');

		if (property_exists('JConfig', 'root_user')
			&& (JFactory::getUser()->get('username') == $rootUser || JFactory::getUser()->id === (string) $rootUser))
		{
			$this->enqueueMessage(
				JText::sprintf(
					'JWARNING_REMOVE_ROOT_USER',
					'index.php?option=com_config&task=config.removeroot&' . JSession::getFormToken() . '=1'
				),
				'notice'
			);
		}

		parent::render();
	}

	/**
	 * Route the application.
	 *
	 * Routing is the process of examining the request environment to determine which
	 * component should receive the request. The component optional parameters
	 * are then set in the request object to be processed when the application is being
	 * dispatched.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	protected function route()
	{
		$uri = JUri::getInstance();

		if ($this->get('force_ssl') >= 1 && strtolower($uri->getScheme()) != 'https')
		{
			// Forward to https
			$uri->setScheme('https');
			$this->redirect((string) $uri, 301);
		}

		// Trigger the onAfterRoute event.
		JPluginHelper::importPlugin('system');
		$this->triggerEvent('onAfterRoute');
	}
}
