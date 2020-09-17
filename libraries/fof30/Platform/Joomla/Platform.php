<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Platform\Joomla;

defined('_JEXEC') || die;

use ActionlogsModelActionlog;
use Exception;
use FOF30\Container\Container;
use FOF30\Date\Date;
use FOF30\Date\DateDecorator;
use FOF30\Input\Input;
use FOF30\Platform\Base\Platform as BasePlatform;
use JDatabaseDriver;
use JEventDispatcher;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Application\CliApplication as JApplicationCli;
use Joomla\CMS\Application\CMSApplication as JApplicationCms;
use Joomla\CMS\Application\ConsoleApplication;
use Joomla\CMS\Application\WebApplication as JApplicationWeb;
use Joomla\CMS\Authentication\Authentication as JAuthentication;
use Joomla\CMS\Authentication\AuthenticationResponse as JAuthenticationResponse;
use Joomla\CMS\Cache\Cache as JCache;
use Joomla\CMS\Document\Document;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Language;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Session\Session as JSession;
use Joomla\CMS\Uri\Uri as JUri;
use Joomla\CMS\User\User;
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\Version;
use Joomla\Component\Actionlogs\Administrator\Model\ActionlogModel;
use Joomla\Registry\Registry;

/**
 * Part of the FOF Platform Abstraction Layer.
 *
 * This implements the platform class for Joomla! 3
 *
 * @since    2.1
 */
class Platform extends BasePlatform
{
	/**
	 * Is this a CLI application?
	 *
	 * @var   bool
	 */
	protected static $isCLI = null;

	/**
	 * Is this an administrator application?
	 *
	 * @var   bool
	 */
	protected static $isAdmin = null;

	/**
	 * Is this an API application?
	 *
	 * @var   bool
	 */
	protected static $isApi = null;

	/**
	 * A fake session storage for CLI apps. This is only used for legacy CLI applications which are not using the FOF
	 * Base CLI script.
	 *
	 * @var   Registry
	 */
	protected static $fakeSession = null;

	/**
	 * The table and table field cache object, used to speed up database access
	 *
	 * @var  Registry|null
	 */
	private $_cache = null;

	/**
	 * Public constructor.
	 *
	 * Overridden to cater for CLI applications not having access to a session object.
	 *
	 * @param   Container  $c  The component container
	 */
	public function __construct(Container $c)
	{
		parent::__construct($c);

		if ($this->isCli())
		{
			static::$fakeSession = new Registry();
		}
	}

	/**
	 * Checks if the current script is run inside a valid CMS execution
	 *
	 * @return  bool
	 * @see     PlatformInterface::checkExecution()
	 *
	 */
	public function checkExecution()
	{
		return defined('_JEXEC');
	}

	/**
	 * Raises an error, using the logic requested by the CMS (PHP Exception or dedicated class)
	 *
	 * @param   integer  $code
	 * @param   string   $message
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 */
	public function raiseError($code, $message)
	{
		$this->showErrorPage(new Exception($message, $code));
	}

	/**
	 * Returns absolute path to directories used by the CMS.
	 *
	 * @return  array  A hash array with keys root, public, admin, tmp and log.
	 * @see PlatformInterface::getPlatformBaseDirs()
	 *
	 */
	public function getPlatformBaseDirs()
	{
		return [
			'root'   => JPATH_ROOT,
			'public' => JPATH_SITE,
			'media'  => JPATH_SITE . '/media',
			'admin'  => JPATH_ADMINISTRATOR,
			'tmp'    => JFactory::getConfig()->get('tmp_path'),
			'log'    => JFactory::getConfig()->get('log_path'),
		];
	}

	/**
	 * Returns the base (root) directories for a given component.
	 *
	 * @param   string  $component  The name of the component. For Joomla! this
	 *                              is something like "com_example"
	 *
	 * @return  array  A hash array with keys main, alt, site and admin.
	 * @see PlatformInterface::getComponentBaseDirs()
	 *
	 */
	public function getComponentBaseDirs($component)
	{
		if (!$this->isBackend())
		{
			$mainPath = JPATH_SITE . '/components/' . $component;
			$altPath  = JPATH_ADMINISTRATOR . '/components/' . $component;
		}
		else
		{
			$mainPath = JPATH_ADMINISTRATOR . '/components/' . $component;
			$altPath  = JPATH_SITE . '/components/' . $component;
		}

		return [
			'main'  => $mainPath,
			'alt'   => $altPath,
			'site'  => JPATH_SITE . '/components/' . $component,
			'admin' => JPATH_ADMINISTRATOR . '/components/' . $component,
		];
	}

	/**
	 * Returns the application's template name
	 *
	 * @param   boolean|array  $params  An optional associative array of configuration settings
	 *
	 * @return  string  The template name. System is the fallback.
	 */
	public function getTemplate($params = false)
	{
		return JFactory::getApplication()->getTemplate($params);
	}

	/**
	 * Get application-specific suffixes to use with template paths. This allows
	 * you to look for view template overrides based on the application version.
	 *
	 * @return  array  A plain array of suffixes to try in template names
	 */
	public function getTemplateSuffixes()
	{
		$jversion     = new Version;
		$versionParts = explode('.', $jversion->getShortVersion());
		$majorVersion = array_shift($versionParts);
		$suffixes     = [
			'.j' . str_replace('.', '', $jversion->getHelpVersion()),
			'.j' . $majorVersion,
		];

		return $suffixes;
	}

	/**
	 * Return the absolute path to the application's template overrides
	 * directory for a specific component. We will use it to look for template
	 * files instead of the regular component directories. If the application
	 * does not have such a thing as template overrides return an empty string.
	 *
	 * @param   string   $component  The name of the component for which to fetch the overrides
	 * @param   boolean  $absolute   Should I return an absolute or relative path?
	 *
	 * @return  string  The path to the template overrides directory
	 */
	public function getTemplateOverridePath($component, $absolute = true)
	{
		[$isCli, $isAdmin] = $this->isCliAdmin();

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

			$path .= $this->getTemplate() .
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
	 * @return  void
	 * @see PlatformInterface::loadTranslations()
	 *
	 */
	public function loadTranslations($component)
	{
		if ($this->isBackend())
		{
			$paths = [JPATH_ROOT, JPATH_ADMINISTRATOR];
		}
		else
		{
			$paths = [JPATH_ADMINISTRATOR, JPATH_ROOT];
		}

		$jlang = $this->getLanguage();
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
	 * @return  boolean  True to allow loading the component, false to halt loading
	 * @see PlatformInterface::authorizeAdmin()
	 *
	 */
	public function authorizeAdmin($component)
	{
		if ($this->isBackend())
		{
			// Master access check for the back-end, Joomla! 1.6 style.
			$user = $this->getUser();

			if (!$user->authorise('core.manage', $component)
				&& !$user->authorise('core.admin', $component)
			)
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
	 * @return  User  The JUser object for the specified user
	 * @see PlatformInterface::getUser()
	 */
	public function getUser($id = null)
	{
		/**
		 * If I'm in CLI I need load the User directly, otherwise JFactory will check the session (which doesn't exist
		 * in CLI)
		 */
		if ($this->isCli())
		{
			if ($id)
			{
				return User::getInstance($id);
			}

			return new User();
		}

		return JFactory::getUser($id);
	}

	/**
	 * Returns the JDocument object which handles this component's response.
	 *
	 * @return  Document
	 * @see PlatformInterface::getDocument()
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
	 * Returns an object to handle dates
	 *
	 * @param   mixed  $time      The initial time
	 * @param   null   $tzOffest  The timezone offset
	 * @param   bool   $locale    Should I try to load a specific class for current language?
	 *
	 * @return  Date object
	 */
	public function getDate($time = 'now', $tzOffest = null, $locale = true)
	{
		if ($locale)
		{
			// Work around a bug in Joomla! 3.7.0.
			if ($time == 'now')
			{
				$time = time();
			}

			$coreObject = JFactory::getDate($time, $tzOffest);

			return new DateDecorator($coreObject);
		}
		else
		{
			return new Date($time, $tzOffest);
		}
	}

	/**
	 * Return the \JLanguage instance of the CMS/application
	 *
	 * @return Language
	 */
	public function getLanguage()
	{
		return JFactory::getLanguage();
	}

	/**
	 * Returns the database driver object of the CMS/application
	 *
	 * @return JDatabaseDriver
	 */
	public function getDbo()
	{
		return JFactory::getDbo();
	}

	/**
	 * This method will try retrieving a variable from the request (input) data.
	 *
	 * @param   string   $key           The user state key for the variable
	 * @param   string   $request       The request variable name for the variable
	 * @param   Input    $input         The Input object with the request (input) data
	 * @param   mixed    $default       The default value. Default: null
	 * @param   string   $type          The filter type for the variable data. Default: none (no filtering)
	 * @param   boolean  $setUserState  Should I set the user state with the fetched value?
	 *
	 * @return  mixed  The value of the variable
	 * @see PlatformInterface::getUserStateFromRequest()
	 *
	 */
	public function getUserStateFromRequest($key, $request, $input, $default = null, $type = 'none', $setUserState = true)
	{
		[$isCLI, $isAdmin] = $this->isCliAdmin();

		unset($isAdmin); // Just to make phpStorm happy

		if ($isCLI)
		{
			$ret = $input->get($request, $default, $type);

			if ($ret === $default)
			{
				$input->set($request, $ret);
			}

			return $ret;
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
	 * @return void
	 *
	 * @codeCoverageIgnore
	 * @see PlatformInterface::importPlugin()
	 *
	 */
	public function importPlugin($type)
	{
		// Should I actually run the plugins?
		$runPlugins = $this->isAllowPluginsInCli() || !$this->isCli();

		if ($runPlugins)
		{
			PluginHelper::importPlugin($type);
		}
	}

	/**
	 * Execute plugins (system-level triggers) and fetch back an array with
	 * their return values.
	 *
	 * @param   string  $event  The event (trigger) name, e.g. onBeforeScratchMyEar
	 * @param   array   $data   A hash array of data sent to the plugins as part of the trigger
	 *
	 * @return  array  A simple array containing the results of the plugins triggered
	 *
	 * @codeCoverageIgnore
	 * @see PlatformInterface::runPlugins()
	 *
	 */
	public function runPlugins($event, $data)
	{
		// Should I actually run the plugins?
		$runPlugins = $this->isAllowPluginsInCli() || !$this->isCli();

		if ($runPlugins)
		{
			// First, try with JEventDispatcher (Joomla 3.x)
			if (class_exists('JEventDispatcher'))
			{
				return JEventDispatcher::getInstance()->trigger($event, $data);
			}

			// If there's no JEventDispatcher try getting JApplication
			try
			{
				$app = JFactory::getApplication();
			}
			catch (Exception $e)
			{
				// If I can't get JApplication I cannot run the plugins.
				return [];
			}

			return $app->triggerEvent($event, $data);
		}
		else
		{
			// I am not allowed to run plugins
			return [];
		}
	}

	/**
	 * Perform an ACL check.
	 *
	 * @param   string  $action     The ACL privilege to check, e.g. core.edit
	 * @param   string  $assetname  The asset name to check, typically the component's name
	 *
	 * @return  boolean  True if the user is allowed this action
	 * @see PlatformInterface::authorise()
	 *
	 */
	public function authorise($action, $assetname)
	{
		if ($this->isCli())
		{
			return true;
		}

		$ret = JFactory::getUser()->authorise($action, $assetname);

		// Work around Joomla returning null instead of false in some cases.
		return $ret ? true : false;
	}

	/**
	 * Is this the administrative section of the component?
	 *
	 * @return  boolean
	 * @see PlatformInterface::isBackend()
	 *
	 */
	public function isBackend()
	{
		[$isCli, $isAdmin] = $this->isCliAdmin();

		return $isAdmin && !$isCli;
	}

	/**
	 * Is this the public section of the component?
	 *
	 * @return  boolean
	 * @see PlatformInterface::isFrontend()
	 *
	 */
	public function isFrontend()
	{
		[$isCli, $isAdmin] = $this->isCliAdmin();

		return !$isAdmin && !$isCli && !$this->isApi();
	}

	/**
	 * Is this a component running in a CLI application?
	 *
	 * @return  boolean
	 * @see PlatformInterface::isCli()
	 *
	 */
	public function isCli()
	{
		[$isCli, $isAdmin] = $this->isCliAdmin();

		return !$isAdmin && $isCli;
	}

	public function isApi()
	{
		if (!is_null(static::$isApi))
		{
			return static::$isApi;
		}

		[$isCli, $isAdmin] = $this->isCliAdmin();

		if (version_compare(JVERSION, '3.999.999', 'le') || $isCli || $isAdmin)
		{
			static::$isApi = false;

			return static::$isApi;
		}

		try
		{
			$app         = JFactory::getApplication();
			static::$isApi = $app->isClient('api');
		}
		catch (Exception $e)
		{
			static::$isApi = false;
		}

		if (static::$isApi)
		{
			static::$isCLI   = false;
			static::$isAdmin = false;
		}

		return static::$isApi;
	}

	/**
	 * Is AJAX re-ordering supported? This is 100% Joomla!-CMS specific. All
	 * other platforms should return false and never ask why.
	 *
	 * @return  boolean
	 *
	 * @codeCoverageIgnore
	 * @see PlatformInterface::supportsAjaxOrdering()
	 *
	 */
	public function supportsAjaxOrdering()
	{
		return true;
	}

	/**
	 * Is the global F0F cache enabled?
	 *
	 * @return  boolean
	 *
	 * @codeCoverageIgnore
	 */
	public function isGlobalF0FCacheEnabled()
	{
		return !(defined('JDEBUG') && JDEBUG);
	}

	/**
	 * Retrieves data from the cache. This is supposed to be used for system-side
	 * F0F data, not application data.
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
	 * Saves something to the cache. This is supposed to be used for system-wide
	 * F0F data, not application data.
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
	 * Clears the cache of system-wide F0F data. You are supposed to call this in
	 * your components' installation script post-installation and post-upgrade
	 * methods or whenever you are modifying the structure of database tables
	 * accessed by F0F. Please note that F0F's cache never expires and is not
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
	 * Returns an object that holds the configuration of the current site.
	 *
	 * @return  Registry
	 *
	 * @codeCoverageIgnore
	 */
	public function getConfig()
	{
		return JFactory::getConfig();
	}

	/**
	 * logs in a user
	 *
	 * @param   array  $authInfo  Authentication information
	 *
	 * @return  boolean  True on success
	 */
	public function loginUser($authInfo)
	{
		$options = ['remember' => false];

		$response         = new JAuthenticationResponse();
		$response->type   = 'fof';
		$response->status = JAuthentication::STATUS_FAILURE;

		if (isset($authInfo['username']))
		{
			$authenticate = JAuthentication::getInstance();
			$response     = $authenticate->authenticate($authInfo, $options);
		}

		// Use our own authentication handler, onFOFUserAuthenticate, as a fallback
		if ($response->status != JAuthentication::STATUS_SUCCESS)
		{
			$this->container->platform->importPlugin('user');
			$this->container->platform->importPlugin('fof');
			$pluginResults = $this->container->platform->runPlugins('onFOFUserAuthenticate', [$authInfo, $options]);

			/**
			 * Loop through all plugin results until we find a successful login. On failure we fall back to Joomla's
			 * previous authentication response.
			 */
			foreach ($pluginResults as $result)
			{
				if (empty($result))
				{
					continue;
				}

				if (!is_object($result) || !($result instanceof JAuthenticationResponse))
				{
					continue;
				}

				if ($result->status != JAuthentication::STATUS_SUCCESS)
				{
					continue;
				}

				$response = $result;

				break;
			}
		}

		// User failed to authenticate: maybe he enabled two factor authentication?
		// Let's try again "manually", skipping the check vs two factor auth
		// Due the big mess with encryption algorithms and libraries, we are doing this extra check only
		// if we're in Joomla 2.5.18+ or 3.2.1+
		if ($response->status != JAuthentication::STATUS_SUCCESS && method_exists('JUserHelper', 'verifyPassword') && isset($authInfo['username']))
		{
			$db     = JFactory::getDbo();
			$query  = $db->getQuery(true)
				->select($db->qn(['id', 'password']))
				->from('#__users')
				->where('username=' . $db->quote($authInfo['username']));
			$result = $db->setQuery($query)->loadObject();

			if ($result)
			{
				$match = UserHelper::verifyPassword($authInfo['password'], $result->password, $result->id);

				if ($match === true)
				{
					// Bring this in line with the rest of the system
					$user               = User::getInstance($result->id);
					$response->email    = $user->email;
					$response->fullname = $user->name;

					[$isCli, $isAdmin] = $this->isCliAdmin();

					if ($isAdmin)
					{
						$response->language = $user->getParam('admin_language');
					}
					else
					{
						$response->language = $user->getParam('language');
					}

					$response->status        = JAuthentication::STATUS_SUCCESS;
					$response->error_message = '';
				}
			}
		}

		if ($response->status == JAuthentication::STATUS_SUCCESS)
		{
			$this->importPlugin('user');
			$results = $this->runPlugins('onLoginUser', [(array) $response, $options]);

			unset($results); // Just to make phpStorm happy

			$userid = UserHelper::getUserId($response->username);
			$user   = $this->getUser($userid);

			$session = $this->container->session;
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
		$app        = JFactory::getApplication();
		$user       = $this->getUser();
		$options    = ['remember' => false];
		$parameters = [
			'username' => $user->username,
			'id'       => $user->id,
		];

		// Set clientid in the options array if it hasn't been set already and shared sessions are not enabled.
		if (!$app->get('shared_session', '0'))
		{
			$options['clientid'] = $app->getClientId();
		}

		$ret = $app->triggerEvent('onUserLogout', [$parameters, $options]);

		return !in_array(false, $ret, true);
	}

	/**
	 * Add a log file for FOF
	 *
	 * @param   string  $file
	 *
	 * @return  void
	 *
	 * @codeCoverageIgnore
	 */
	public function logAddLogger($file)
	{
		Log::addLogger(['text_file' => $file], Log::ALL, ['fof']);
	}

	/**
	 * Logs a deprecated practice. In Joomla! this results in the $message being output in the
	 * deprecated log file, found in your site's log directory.
	 *
	 * @param   string  $message  The deprecated practice log message
	 *
	 * @return  void
	 *
	 * @codeCoverageIgnore
	 */
	public function logDeprecated($message)
	{
		Log::add($message, Log::WARNING, 'deprecated');
	}

	/**
	 * Adds a message to the application's debug log
	 *
	 * @param   string  $message
	 *
	 * @return  void
	 *
	 * @codeCoverageIgnore
	 */
	public function logDebug($message)
	{
		Log::add($message, Log::DEBUG, 'fof');
	}

	/**
	 * Adds a message
	 *
	 * @param   string|array  $title      A title, or an array of additional fields to add to the log entry
	 * @param   string        $logText    The translation key to the log text
	 * @param   string        $extension  The name of the extension logging this entry
	 * @param   User|null     $user       The user the action is being logged for
	 *
	 * @return  void
	 */
	public function logUserAction($title, $logText, $extension, $user = null)
	{
		static $joomlaModelAdded = false;

		// Find out which Joomla version I am running under
		$isJoomla4 = version_compare(JVERSION, '3.999.999', 'gt');
		$isJoomla3 = !$isJoomla4 && version_compare(JVERSION, '3.9.0', 'ge');

		// I need Joomla! 3.9 or later
		if (!$isJoomla4 && !$isJoomla3)
		{
			return;
		}

		/**
		 * Do not perform user actions logging under CLI and Joomla 3.
		 *
		 * In Joomla 3 the ActionlogsModelActionlog always goes through JFactory to get the current user. However, this
		 * goes through the session which is normally not initialized under CLI – that's why we needed to come up with
		 * our own CLI implementation to begin with.
		 *
		 * Joomla 4 doesn't have that problem. J4 CLI scripts use a CLI-aware session service provider.
		 */
		if ($isJoomla3 && $this->isCli())
		{
			return;
		}

		// Include required Joomla Model. Only applicable on Joomla 3.
		if ($isJoomla3 && !$joomlaModelAdded)
		{
			BaseDatabaseModel::addIncludePath(JPATH_ROOT . '/administrator/components/com_actionlogs/models', 'ActionlogsModel');
			$joomlaModelAdded = true;
		}

		if (is_null($user))
		{
			$user = $this->getUser();
		}

		// No log for guest users
		if ($user->guest)
		{
			return;
		}

		$message = [
			'title'       => $title,
			'username'    => $user->username,
			'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
		];

		if (is_array($title))
		{
			unset ($message['title']);

			$message = array_merge($message, $title);
		}

		if (version_compare(JVERSION, '3.999.999', 'le'))
		{
			/** @var ActionlogsModelActionlog $model * */
			try
			{
				$model = BaseDatabaseModel::getInstance('Actionlog', 'ActionlogsModel');
			}
			catch (Exception $e)
			{
				return;
			}
		}
		else
		{
			try
			{
				/** @var MVCFactoryInterface $factory */
				$factory = JFactory::getApplication()->bootComponent('com_actionlogs')->getMVCFactory();
				/** @var ActionlogModel $model */
				$model = $factory->createModel('Actionlog', 'Administrator');
			}
			catch (Exception $e)
			{
				return;
			}
		}

		try
		{
			$model->addLog([$message], $logText, $extension, $user->id);
		}
		catch (Exception $e)
		{
			return;
		}
	}

	/**
	 * Returns the root URI for the request.
	 *
	 * @param   boolean  $pathonly  If false, prepend the scheme, host and port information. Default is false.
	 * @param   string   $path      The path
	 *
	 * @return  string  The root URI string.
	 *
	 * @codeCoverageIgnore
	 */
	public function URIroot($pathonly = false, $path = null)
	{
		return JUri::root($pathonly, $path);
	}

	/**
	 * Returns the base URI for the request.
	 *
	 * @param   boolean  $pathonly  If false, prepend the scheme, host and port information. Default is false.
	 *
	 * @return  string  The base URI string
	 *
	 * @codeCoverageIgnore
	 */
	public function URIbase($pathonly = false)
	{
		return JUri::base($pathonly);
	}

	/**
	 * Method to set a response header.  If the replace flag is set then all headers
	 * with the given name will be replaced by the new one (only if the current platform supports header caching)
	 *
	 * @param   string   $name     The name of the header to set.
	 * @param   string   $value    The value of the header to set.
	 * @param   boolean  $replace  True to replace any headers with the same name.
	 *
	 * @return  void
	 *
	 * @codeCoverageIgnore
	 */
	public function setHeader($name, $value, $replace = false)
	{
		JFactory::getApplication()->setHeader($name, $value, $replace);
	}

	/**
	 * In platforms that perform header caching, send all headers.
	 *
	 * @return  void
	 *
	 * @codeCoverageIgnore
	 */
	public function sendHeaders()
	{
		JFactory::getApplication()->sendHeaders();
	}

	/**
	 * Immediately terminate the containing application's execution
	 *
	 * @param   int  $code  The result code which should be returned by the application
	 *
	 * @return  void
	 */
	public function closeApplication($code = 0)
	{
		// Necessary workaround for broken System - Page Cache plugin in Joomla! 3.7.0
		$this->bugfixJoomlaCachePlugin();

		JFactory::getApplication()->close($code);
	}

	/**
	 * Perform a redirection to a different page, optionally enqueuing a message for the user.
	 *
	 * @param   string  $url     The URL to redirect to
	 * @param   int     $status  (optional) The HTTP redirection status code, default 303 (See Other)
	 * @param   string  $msg     (optional) A message to enqueue
	 * @param   string  $type    (optional) The message type, e.g. 'message' (default), 'warning' or 'error'.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 */
	public function redirect($url, $status = 303, $msg = '', $type = 'message')
	{
		// Necessary workaround for broken System - Page Cache plugin in Joomla! 3.7.0
		$this->bugfixJoomlaCachePlugin();

		$app = JFactory::getApplication();

		if (class_exists('JApplicationCms') && class_exists('JApplicationWeb')
			&& ($app instanceof JApplicationCms)
			&& ($app instanceof JApplicationWeb))
		{
			// In modern Joomla! versions we have versatility on setting the message and the redirection HTTP code
			if (!empty($msg))
			{
				if (empty($type))
				{
					$type = 'message';
				}

				$app->enqueueMessage($msg, $type);
			}

			$app->redirect($url, $status);
		}

		/**
		 * If you're here, you have an ancient Joomla version and we have to use the legacy four parameter method...
		 * Note that we can't set a custom HTTP code, we can only tell it if it's a permanent redirection or not.
		 */
		$app->redirect($url, $msg, $type, $status == 301);
	}

	/**
	 * Handle an exception in a way that results to an error page. We use this under Joomla! to work around a bug in
	 * Joomla! 3.7 which results in error pages leading to white pages because Joomla's System - Page Cache plugin is
	 * broken.
	 *
	 * @param   Exception  $exception  The exception to handle
	 *
	 * @throws  Exception  We rethrow the exception
	 */
	public function showErrorPage(Exception $exception)
	{
		// Necessary workaround for broken System - Page Cache plugin in Joomla! 3.7.0
		$this->bugfixJoomlaCachePlugin();

		throw $exception;
	}

	/**
	 * Set a variable in the user session
	 *
	 * @param   string  $name       The name of the variable to set
	 * @param   string  $value      (optional) The value to set it to, default is null
	 * @param   string  $namespace  (optional) The variable's namespace e.g. the component name. Default: 'default'
	 *
	 * @return  void
	 */
	public function setSessionVar($name, $value = null, $namespace = 'default')
	{
		if ($this->isCli() && !class_exists('FOFApplicationCLI'))
		{
			static::$fakeSession->set("$namespace.$name", $value);

			return;
		}

		$this->container->session->set($name, $value, $namespace);
	}

	/**
	 * Get a variable from the user session
	 *
	 * @param   string  $name       The name of the variable to set
	 * @param   string  $default    (optional) The default value to return if the variable does not exit, default: null
	 * @param   string  $namespace  (optional) The variable's namespace e.g. the component name. Default: 'default'
	 *
	 * @return  mixed
	 */
	public function getSessionVar($name, $default = null, $namespace = 'default')
	{
		if ($this->isCli() && !class_exists('FOFApplicationCLI'))
		{
			return static::$fakeSession->get("$namespace.$name", $default);
		}

		return $this->container->session->get($name, $default, $namespace);
	}

	/**
	 * Unset a variable from the user session
	 *
	 * @param   string  $name       The name of the variable to unset
	 * @param   string  $namespace  (optional) The variable's namespace e.g. the component name. Default: 'default'
	 *
	 * @return  void
	 */
	public function unsetSessionVar($name, $namespace = 'default')
	{
		$this->setSessionVar($name, null, $namespace);
	}

	/**
	 * Return the session token. Two types of tokens can be returned:
	 *
	 * Session token ($formToken == false): Used for anti-spam protection of forms. This is specific to a session
	 *   object.
	 *
	 * Form token ($formToken == true): A secure hash of the user ID with the session token. Both the session and the
	 *   user are fetched from the application container. They are interpolated with the site's secret and passed
	 *   through MD5, making this harder to spoof than the plain old session token.
	 *
	 * @param   bool  $formToken  Should I return a form token?
	 * @param   bool  $forceNew   Should I force the creation of a new token?
	 *
	 * @return  mixed
	 */
	public function getToken($formToken = false, $forceNew = false)
	{
		// For CLI apps we implement our own fake token system
		if ($this->isCli())
		{
			$token = $this->getSessionVar('session.token');

			// Create a token
			if (is_null($token) || $forceNew)
			{
				$token = UserHelper::genRandomPassword(32);
				$this->setSessionVar('session.token', $token);
			}

			if (!$formToken)
			{
				return $token;
			}

			$user = $this->getUser();

			return ApplicationHelper::getHash($user->id . $token);
		}

		// Web application, go through the regular Joomla! API.
		if ($formToken)
		{
			return JSession::getFormToken($forceNew);
		}

		return $this->container->session->getToken($forceNew);
	}

	/** @inheritDoc */
	public function addScriptOptions($key, $value, $merge = true)
	{
		/** @var HtmlDocument $document */
		$document = $this->getDocument();

		if (!method_exists($document, 'addScriptOptions'))
		{
			return;
		}

		$document->addScriptOptions($key, $value, $merge);
	}

	/** @inheritDoc */
	public function getScriptOptions($key = null)
	{
		/** @var HtmlDocument $document */
		$document = $this->getDocument();

		if (!method_exists($document, 'getScriptOptions'))
		{
			return [];
		}

		return $document->getScriptOptions($key);
	}

	/**
	 * Main function to detect if we're running in a CLI environment and we're admin
	 *
	 * @return  array  isCLI and isAdmin. It's not an associative array, so we can use list().
	 */
	protected function isCliAdmin()
	{
		if (is_null(static::$isCLI) && is_null(static::$isAdmin))
		{
			static::$isCLI   = false;
			static::$isAdmin = false;

			try
			{
				if (is_null(JFactory::$application))
				{
					static::$isCLI   = true;
					static::$isAdmin = false;

					return [static::$isCLI, static::$isAdmin];
				}

				$app           = JFactory::getApplication();
				static::$isCLI = $app instanceof Exception;

				if (class_exists('Joomla\CMS\Application\CliApplication'))
				{
					static::$isCLI = static::$isCLI || $app instanceof JApplicationCli;
				}

				if (class_exists('Joomla\CMS\Application\ConsoleApplication'))
				{
					static::$isCLI = static::$isCLI || ($app instanceof ConsoleApplication);
				}
			}
			catch (Exception $e)
			{
				static::$isCLI = true;
			}

			if (static::$isCLI)
			{
				return [static::$isCLI, static::$isAdmin];
			}

			try
			{
				$app = JFactory::getApplication();
			}
			catch (Exception $e)
			{
				return [static::$isCLI, static::$isAdmin];
			}

			if (method_exists($app, 'isAdmin'))
			{
				static::$isAdmin = $app->isAdmin();
			}
			elseif (method_exists($app, 'isClient'))
			{
				static::$isAdmin = $app->isClient('administrator');
			}
		}

		return [static::$isCLI, static::$isAdmin];
	}

	/**
	 * Gets a reference to the cache object, loading it from the disk if
	 * needed.
	 *
	 * @param   boolean  $force  Should I forcibly reload the registry?
	 *
	 * @return  Registry
	 */
	private function &getCacheObject($force = false)
	{
		// Check if we have to load the cache file or we are forced to do that
		if (is_null($this->_cache) || $force)
		{
			// Try to get data from Joomla!'s cache
			$cache        = JFactory::getCache('fof', '');
			$this->_cache = $cache->get('cache', 'fof');

			$isRegistry = is_object($this->_cache);

			if ($isRegistry)
			{
				$isRegistry = class_exists('JRegistry') ? ($this->_cache instanceof Registry) : ($this->_cache instanceof Registry);
			}

			if (!$isRegistry)
			{
				// Create a new Registry object
				$this->_cache = class_exists('JRegistry') ? new Registry() : new Registry();
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
		// Get the Registry object of our cached data
		$registry = $this->getCacheObject();

		$cache = JFactory::getCache('fof', '');

		return $cache->store($registry, 'cache', 'fof');
	}

	/**
	 * Joomla! 3.7 has a broken System - Page Cache plugin. When this plugin is enabled it FORCES the caching of all
	 * pages as soon as Joomla! starts loading, before the plugin has a chance to request to not be cached. Event worse,
	 * in case of a redirection, it doesn't try to remove the cache lock. This means that the next request will be
	 * treated as though the result of the page should be cached. Since there is NO cache content for the page Joomla!
	 * returns an empty response with a 200 OK header. This will, of course, get in the way of every single attempt to
	 * perform a redirection in the frontend of the site.
	 */
	private function bugfixJoomlaCachePlugin()
	{
		// Only Joomla! 3.7 and later is broken.
		if (version_compare(JVERSION, '3.6.999', 'le'))
		{
			return;
		}

		// Only do something when the System - Cache plugin is activated
		if (!class_exists('PlgSystemCache'))
		{
			return;
		}

		// Forcibly uncache the current request
		$options = [
			'defaultgroup' => 'page',
			'browsercache' => false,
			'caching'      => false,
		];

		$cache_key = JUri::getInstance()->toString();
		JCache::getInstance('page', $options)->cache->remove($cache_key, 'page');
	}
}
