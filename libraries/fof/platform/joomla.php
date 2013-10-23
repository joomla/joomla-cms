<?php
/**
 * @package    FrameworkOnFramework
 * @copyright  Copyright (C) 2010 - 2012 Akeeba Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('_JEXEC') or die;

/**
 * Part of the FOF Platform Abstraction Layer.
 *
 * This implements the platform class for Joomla! 2.5 or later
 *
 * @package  FrameworkOnFramework
 * @since    2.1
 */
class FOFPlatformJoomla extends FOFPlatform implements FOFPlatformInterface
{
	private $_cache = null;

	/**
	 * Is this platform enabled?
	 *
	 * @see FOFPlatformInterface::isEnabled()
	 *
	 * @return  boolean
	 */
	public function isEnabled()
	{
		if (is_null($this->isEnabled))
		{
			$this->isEnabled = true;

			// Make sure _JEXEC is defined
			if (!defined('_JEXEC'))
			{
				$this->isEnabled = false;
			}

			// We need JVERSION to be defined
			if ($this->isEnabled)
			{
				if (!defined('JVERSION'))
				{
					$this->isEnabled = false;
				}
			}

			// Check if JFactory exists
			if ($this->isEnabled)
			{
				if (!class_exists('JFactory'))
				{
					$this->isEnabled = false;
				}
			}

			// Check if JApplication exists
			if ($this->isEnabled)
			{
				$appExists = class_exists('JApplication');
				$appExists = $appExists || class_exists('JCli');
				$appExists = $appExists || class_exists('JApplicationCli');

				if (!$appExists)
				{
					$this->isEnabled = false;
				}
			}
		}

		return $this->isEnabled;
	}

	/**
	 * Main function to detect if we're running in a CLI environment and we're admin
	 *
	 * @return  array  isCLI and isAdmin. It's not an associtive array, so we can use list.
	 */
	protected function isCliAdmin()
	{
		static $isCLI   = null;
		static $isAdmin = null;

		if (is_null($isCLI) && is_null($isAdmin))
		{
			try
			{
				if (is_null(JFactory::$application))
				{
					$isCLI = true;
				}
				else
				{
					$isCLI = JFactory::getApplication() instanceof JException;
				}
			}
			catch (Exception $e)
			{
				$isCLI = true;
			}

			if ($isCLI)
			{
				$isAdmin = false;
			}
			else
			{
				$isAdmin = !JFactory::$application ? false : JFactory::getApplication()->isAdmin();
			}
		}

		return array($isCLI, $isAdmin);
	}

	/**
	 * Returns the base (root) directories for a given component.
	 *
	 * @param   string  $component  The name of the component. For Joomla! this
	 *                              is something like "com_example"
	 *
	 * @see FOFPlatformInterface::getComponentBaseDirs()
	 *
	 * @return  array  A hash array with keys main, alt, site and admin.
	 */
	public function getComponentBaseDirs($component)
	{
		if ($this->isFrontend())
		{
			$mainPath	= JPATH_SITE . '/components/' . $component;
			$altPath	= JPATH_ADMINISTRATOR . '/components/' . $component;
		}
		else
		{
			$mainPath	= JPATH_ADMINISTRATOR . '/components/' . $component;
			$altPath	= JPATH_SITE . '/components/' . $component;
		}

		return array(
			'main'	=> $mainPath,
			'alt'	=> $altPath,
			'site'	=> JPATH_SITE . '/components/' . $component,
			'admin'	=> JPATH_ADMINISTRATOR . '/components/' . $component,
		);
	}

	/**
	 * Return a list of the view template paths for this component.
	 *
	 * @param   string   $component  The name of the component. For Joomla! this
	 *                               is something like "com_example"
	 * @param   string   $view       The name of the view you're looking a
	 *                               template for
	 * @param   string   $layout     The layout name to load, e.g. 'default'
	 * @param   string   $tpl        The sub-template name to load (null by default)
	 * @param   boolean  $strict     If true, only the specified layout will be searched for.
	 *                               Otherwise we'll fall back to the 'default' layout if the
	 *                               specified layout is not found.
	 *
	 * @see FOFPlatformInterface::getViewTemplateDirs()
	 *
	 * @return  array
	 */
	public function getViewTemplatePaths($component, $view, $layout = 'default', $tpl = null, $strict = false)
	{
		$isAdmin = $this->isBackend();

		$basePath = $isAdmin ? 'admin:' : 'site:';
		$basePath .= $component . '/';
		$altBasePath = $basePath;
		$basePath .= $view . '/';
		$altBasePath .= (FOFInflector::isSingular($view) ? FOFInflector::pluralize($view) : FOFInflector::singularize($view)) . '/';

		if ($strict)
		{
			$paths = array(
				$basePath . $layout . ($tpl ? "_$tpl" : ''),
				$altBasePath . $layout . ($tpl ? "_$tpl" : ''),
			);
		}
		else
		{
			$paths = array(
				$basePath . $layout . ($tpl ? "_$tpl" : ''),
				$basePath . $layout,
				$basePath . 'default' . ($tpl ? "_$tpl" : ''),
				$basePath . 'default',
				$altBasePath . $layout . ($tpl ? "_$tpl" : ''),
				$altBasePath . $layout,
				$altBasePath . 'default' . ($tpl ? "_$tpl" : ''),
				$altBasePath . 'default',
			);
			$paths = array_unique($paths);
		}

		return $paths;
	}

	/**
	 * Get application-specific suffixes to use with template paths. This allows
	 * you to look for view template overrides based on the application version.
	 *
	 * @return  array  A plain array of suffixes to try in template names
	 */
	public function getTemplateSuffixes()
	{
		$jversion = new JVersion;
		$versionParts = explode('.', $jversion->RELEASE);
		$majorVersion = array_shift($versionParts);
		$suffixes = array(
			'.j' . str_replace('.', '', $jversion->getHelpVersion()),
			'.j' . $majorVersion,
		);

		return $suffixes;
	}

	/**
	 * Return the absolute path to the application's template overrides
	 * directory for a specific component. We will use it to look for template
	 * files instead of the regular component directorues. If the application
	 * does not have such a thing as template overrides return an empty string.
	 *
	 * @param   string   $component  The name of the component for which to fetch the overrides
	 * @param   boolean  $absolute   Should I return an absolute or relative path?
	 *
	 * @return  string  The path to the template overrides directory
	 */
	public function getTemplateOverridePath($component, $absolute = true)
	{
		list($isCli, $isAdmin) = $this->isCliAdmin();

		if (!$isCli)
		{
			if ($absolute)
			{
				$path = JPATH_THEMES . '/';
			}
			else
			{
				$path = $isAdmin ? 'administrator/templates/' : 'templates/';
			}

			if (substr($component, 0, 7) == 'media:/')
			{
				$directory = 'media/' . substr($component, 7);
			}
			else
			{
				$directory = 'html/' . $component;
			}

			$path .= JFactory::getApplication()->getTemplate() .
				'/' . $directory;
		}
		else
		{
			$path = '';
		}

		return $path;
	}

	/**
	 * Load the translation files for a given component.
	 *
	 * @param   string  $component  The name of the component. For Joomla! this
	 *                              is something like "com_example"
	 *
	 * @see FOFPlatformInterface::loadTranslations()
	 *
	 * @return  void
	 */
	public function loadTranslations($component)
	{
		if ($this->isBackend())
		{
			$paths = array(JPATH_ROOT, JPATH_ADMINISTRATOR);
		}
		else
		{
			$paths = array(JPATH_ADMINISTRATOR, JPATH_ROOT);
		}

		$jlang = JFactory::getLanguage();
		$jlang->load($component, $paths[0], 'en-GB', true);
		$jlang->load($component, $paths[0], null, true);
		$jlang->load($component, $paths[1], 'en-GB', true);
		$jlang->load($component, $paths[1], null, true);
	}

	/**
	 * Authorise access to the component in the back-end.
	 *
	 * @param   string  $component  The name of the component.
	 *
	 * @see FOFPlatformInterface::authorizeAdmin()
	 *
	 * @return  boolean  True to allow loading the component, false to halt loading
	 */
	public function authorizeAdmin($component)
	{
		if ($this->isBackend())
		{
			// Master access check for the back-end, Joomla! 1.6 style.
			$user = JFactory::getUser();

			if (!$user->authorise('core.manage', $component)
				&& !$user->authorise('core.admin', $component))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Return a user object.
	 *
	 * @param   integer  $id  The user ID to load. Skip or use null to retrieve
	 *                        the object for the currently logged in user.
	 *
	 * @see FOFPlatformInterface::getUser()
	 *
	 * @return  JUser  The JUser object for the specified user
	 */
	public function getUser($id = null)
	{
		return JFactory::getUser($id);
	}

	/**
	 * Returns the JDocument object which handles this component's response.
	 *
	 * @see FOFPlatformInterface::getDocument()
	 *
	 * @return  JDocument
	 */
	public function getDocument()
	{
		$document = null;

		if (!$this->isCli())
		{
			try
			{
				$document = JFactory::getDocument();
			}
			catch (Exception $exc)
			{
				$document = null;
			}
		}

		return $document;
	}

	/**
	 * This method will try retrieving a variable from the request (input) data.
	 *
	 * @param   string    $key           The user state key for the variable
	 * @param   string    $request       The request variable name for the variable
	 * @param   FOFInput  $input         The FOFInput object with the request (input) data
	 * @param   mixed     $default       The default value. Default: null
	 * @param   string    $type          The filter type for the variable data. Default: none (no filtering)
	 * @param   boolean   $setUserState  Should I set the user state with the fetched value?
	 *
	 * @see FOFPlatformInterface::getUserStateFromRequest()
	 *
	 * @return  mixed  The value of the variable
	 */
	public function getUserStateFromRequest($key, $request, $input, $default = null, $type = 'none', $setUserState = true)
	{
		list($isCLI, $isAdmin) = $this->isCliAdmin();

		if ($isCLI)
		{
			return $input->get($request, $default, $type);
		}

		$app = JFactory::getApplication();

		if (method_exists($app, 'getUserState'))
		{
			$old_state = $app->getUserState($key, $default);
		}
		else
		{
			$old_state = null;
		}

		$cur_state = (!is_null($old_state)) ? $old_state : $default;
		$new_state = $input->get($request, null, $type);

		// Save the new value only if it was set in this request
		if ($setUserState)
		{
			if ($new_state !== null)
			{
				$app->setUserState($key, $new_state);
			}
			else
			{
				$new_state = $cur_state;
			}
		}
		elseif (is_null($new_state))
		{
			$new_state = $cur_state;
		}

		return $new_state;
	}

	/**
	 * Load plugins of a specific type. Obviously this seems to only be required
	 * in the Joomla! CMS.
	 *
	 * @param   string  $type  The type of the plugins to be loaded
	 *
	 * @see FOFPlatformInterface::importPlugin()
	 *
	 * @return void
	 */
	public function importPlugin($type)
	{
		if (!$this->isCli())
		{
			JPluginHelper::importPlugin($type);
		}
	}

	/**
	 * Execute plugins (system-level triggers) and fetch back an array with
	 * their return values.
	 *
	 * @param   string  $event  The event (trigger) name, e.g. onBeforeScratchMyEar
	 * @param   array   $data   A hash array of data sent to the plugins as part of the trigger
	 *
	 * @see FOFPlatformInterface::runPlugins()
	 *
	 * @return  array  A simple array containing the resutls of the plugins triggered
	 */
	public function runPlugins($event, $data)
	{
		if (!$this->isCli())
		{
			$dispatcher = JDispatcher::getInstance();

			return $dispatcher->trigger($event, $data);
		}
		else
		{
			return array();
		}
	}

	/**
	 * Perform an ACL check.
	 *
	 * @param   string  $action     The ACL privilege to check, e.g. core.edit
	 * @param   string  $assetname  The asset name to check, typically the component's name
	 *
	 * @see FOFPlatformInterface::authorise()
	 *
	 * @return  boolean  True if the user is allowed this action
	 */
	public function authorise($action, $assetname)
	{
		if ($this->isCli())
		{
			return true;
		}

		return JFactory::getUser()->authorise($action, $assetname);
	}

	/**
	 * Is this the administrative section of the component?
	 *
	 * @see FOFPlatformInterface::isBackend()
	 *
	 * @return  boolean
	 */
	public function isBackend()
	{
		list ($isCli, $isAdmin) = $this->isCliAdmin();

		return $isAdmin && !$isCli;
	}

	/**
	 * Is this the public section of the component?
	 *
	 * @see FOFPlatformInterface::isFrontend()
	 *
	 * @return  boolean
	 */
	public function isFrontend()
	{
		list ($isCli, $isAdmin) = $this->isCliAdmin();

		return !$isAdmin && !$isCli;
	}

	/**
	 * Is this a component running in a CLI application?
	 *
	 * @see FOFPlatformInterface::isCli()
	 *
	 * @return  boolean
	 */
	public function isCli()
	{
		list ($isCli, $isAdmin) = $this->isCliAdmin();

		return !$isAdmin && $isCli;
	}

	/**
	 * Is AJAX re-ordering supported? This is 100% Joomla!-CMS specific. All
	 * other platforms should return false and never ask why.
	 *
	 * @see FOFPlatformInterface::supportsAjaxOrdering()
	 *
	 * @return  boolean
	 */
	public function supportsAjaxOrdering()
	{
		return $this->checkVersion(JVERSION, '3.0', 'ge');
	}

	/**
	 * Is the global FOF cache enabled?
	 *
	 * @return  boolean
	 */
	public function isGlobalFOFCacheEnabled()
	{
		return !(defined('JDEBUG') && JDEBUG);
	}

	/**
	 * Saves something to the cache. This is supposed to be used for system-wide
	 * FOF data, not application data.
	 *
	 * @param   string  $key      The key of the data to save
	 * @param   string  $content  The actual data to save
	 *
	 * @return  boolean  True on success
	 */
	public function setCache($key, $content)
	{
		$registry = $this->getCacheObject();

		$registry->set($key, $content);

		return $this->saveCache();
	}

	/**
	 * Retrieves data from the cache. This is supposed to be used for system-side
	 * FOF data, not application data.
	 *
	 * @param   string  $key      The key of the data to retrieve
	 * @param   string  $default  The default value to return if the key is not found or the cache is not populated
	 *
	 * @return  string  The cached value
	 */
	public function getCache($key, $default = null)
	{
		$registry = $this->getCacheObject();

		return $registry->get($key, $default);
	}

	/**
	 * Gets a reference to the cache object, loading it from the disk if
	 * needed.
	 *
	 * @param   boolean  $force  Should I forcibly reload the registry?
	 *
	 * @return  JRegistry
	 */
	private function &getCacheObject($force = false)
	{
		// Check if we have to load the cache file or we are forced to do that
		if (is_null($this->_cache) || $force)
		{
			// Create a new JRegistry object
			JLoader::import('joomla.registry.registry');
			$this->_cache = new JRegistry;

			// Try to get data from Joomla!'s cache
			$cache = JFactory::getCache('fof', '');
			$data = $cache->get('cache', 'fof');

			// If data is not found, fall back to the legacy (FOF 2.1.rc3 and earlier) method
			if ($data === false)
			{
				// Find the path to the file
				$cachePath = JPATH_CACHE . '/fof';
				$filename  = $cachePath . '/cache.php';

				JLoader::import('joomla.filesystem.file');

				// Load the cache file if it exists. JRegistryFormatPHP fails
				// miserably, so I have to work around it.
				if (JFile::exists($filename))
				{
					@include_once $filename;

					JFile::delete($filename);

					$className = 'FOFCacheStorage';

					if (class_exists($className))
					{
						$object = new $className;
						$this->_cache->loadObject($object);

						$options = array(
							'class' => 'FOFCacheStorage'
						);
						$cache->store($this->_cache, 'cache', 'fof');
					}
				}
			}
			else
			{
				$this->_cache = $data;
			}
		}

		return $this->_cache;
	}

	/**
	 * Save the cache object back to disk
	 *
	 * @return  boolean  True on success
	 */
	private function saveCache()
	{
		// Get the JRegistry object of our cached data
		$registry = $this->getCacheObject();

		$cache = JFactory::getCache('fof', '');
		return $cache->store($registry, 'cache', 'fof');
	}

	/**
	 * Clears the cache of system-wide FOF data. You are supposed to call this in
	 * your components' installation script post-installation and post-upgrade
	 * methods or whenever you are modifying the structure of database tables
	 * accessed by FOF. Please note that FOF's cache never expires and is not
	 * purged by Joomla!. You MUST use this method to manually purge the cache.
	 *
	 * @return  boolean  True on success
	 */
	public function clearCache()
	{
		$false = false;
		$cache = JFactory::getCache('fof', '');
		$cache->store($false, 'cache', 'fof');
	}

	/**
	 * logs in a user
	 *
	 * @param   array  $authInfo  authentification information
	 *
	 * @return  boolean  True on success
	 */
	public function loginUser($authInfo)
	{
		JLoader::import('joomla.user.authentication');
		$options = array('remember'		 => false);
		$authenticate = JAuthentication::getInstance();
		$response = $authenticate->authenticate($authInfo, $options);

		if ($response->status == JAuthentication::STATUS_SUCCESS)
		{
			$this->importPlugin('user');
			$results = $this->runPlugins('onLoginUser', array((array) $response, $options));

			JLoader::import('joomla.user.helper');
			$userid = JUserHelper::getUserId($response->username);
			$user = $this->getUser($userid);

			$session = JFactory::getSession();
			$session->set('user', $user);

			return true;
		}

		return false;
	}

	/**
	 * logs out a user
	 *
	 * @return  boolean  True on success
	 */
	public function logoutUser()
	{
		JLoader::import('joomla.user.authentication');
		$app = JFactory::getApplication();
		$options = array('remember'	 => false);
		$parameters = array('username'	 => $this->getUser()->username);

		return $app->triggerEvent('onLogoutUser', array($parameters, $options));
	}
}
