<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.updatenotification
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Webauthn\Helper;

// Protect from unauthorized access
use DateTimeZone;
use Exception;
use JLoader;
use Joomla\CMS\Application\BaseApplication;
use Joomla\CMS\Application\CliApplication;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Authentication\Authentication;
use Joomla\CMS\Authentication\AuthenticationResponse;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\User\User;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\CMS\User\UserHelper;
use Joomla\Registry\Registry;
use RuntimeException;

defined('_JEXEC') or die();

/**
 * A helper class for abstracting core features in Joomla! 3.4 and later, including 4.x
 */
abstract class Joomla
{
	/**
	 * A fake session storage for CLI apps. Since CLI applications cannot have a session we are using a Registry object
	 * we manage internally.
	 *
	 * @var   Registry
	 * @since 1.0.0
	 */
	protected static $fakeSession = null;

	/**
	 * Are we inside the administrator application
	 *
	 * @var   bool
	 * @since 1.0.0
	 */
	protected static $isAdmin = null;

	/**
	 * Are we inside a CLI application
	 *
	 * @var   bool
	 * @since 1.0.0
	 */
	protected static $isCli = null;

	/**
	 * Which plugins have already registered a text file logger. Prevents double registration of a log file.
	 *
	 * @var   array
	 * @since 1.0.0
	 */
	protected static $registeredLoggers = [];

	/**
	 * The current Joomla Document type
	 *
	 * @var   string|null
	 */
	protected static $joomlaDocumentType = null;

	/**
	 * Are we inside an administrator page?
	 *
	 * @param   CMSApplication  $app  The current CMS application which tells us if we are inside an admin page
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public static function isAdminPage(CMSApplication $app = null): bool
	{
		if (is_null(self::$isAdmin))
		{
			if (is_null($app))
			{
				$app = Factory::getApplication();
			}

			self::$isAdmin = $app->isClient('administrator');
		}

		return self::$isAdmin;
	}

	/**
	 * Are we inside a CLI application
	 *
	 * @param   CMSApplication  $app  The current CMS application which tells us if we are inside an admin page
	 *
	 * @return  bool
	 *
	 * @since   1.0.0
	 */
	public static function isCli(CMSApplication $app = null): bool
	{
		if (is_null(self::$isCli))
		{
			if (is_null($app))
			{
				try
				{
					$app = Factory::getApplication();
				}
				catch (Exception $e)
				{
					$app = null;
				}
			}

			if (is_null($app))
			{
				self::$isCli = true;
			}

			if (is_object($app))
			{
				self::$isCli = $app instanceof \Exception;

				if (class_exists('Joomla\\CMS\\Application\\CliApplication'))
				{
					self::$isCli = self::$isCli || $app instanceof CliApplication;
				}
			}
		}

		return self::$isCli;
	}

	/**
	 * Is the current user allowed to edit the social login configuration of $user? To do so I must either be editing my
	 * own account OR I have to be a Super User.
	 *
	 * @param   User  $user  The user you want to know if we're allowed to edit
	 *
	 * @return  bool
	 *
	 * @since   1.0.0
	 */
	public static function canEditUser(User $user = null): bool
	{
		// I can edit myself
		if (empty($user))
		{
			return true;
		}

		// Guests can't have social logins associated
		if ($user->guest)
		{
			return false;
		}

		// Get the currently logged in used
		try
		{
			$myUser = Factory::getApplication()->getIdentity();
		}
		catch (Exception $e)
		{
			// Cannot get the application; no user, therefore no edit privileges.
			return false;
		}

		// Same user? I can edit myself
		if ($myUser->id == $user->id)
		{
			return true;
		}

		// To edit a different user I must be a Super User myself. If I'm not, I can't edit another user!
		if (!$myUser->authorise('core.admin'))
		{
			return false;
		}

		// I am a Super User editing another user. That's allowed.
		return true;
	}

	/**
	 * Helper method to render a JLayout.
	 *
	 * @param   string  $layoutFile   Dot separated path to the layout file, relative to base path (plugins/system/webauthn/layout)
	 * @param   object  $displayData  Object which properties are used inside the layout file to build displayed output
	 * @param   string  $includePath  Additional path holding layout files
	 * @param   mixed   $options      Optional custom options to load. Registry or array format. Set 'debug'=>true to output debug information.
	 *
	 * @return  string
	 *
	 * @since   1.0.0
	 */
	public static function renderLayout(string $layoutFile, $displayData = null, string $includePath = '', array $options = []): string
	{
		$basePath = JPATH_SITE . '/plugins/system/webauthn/layout';
		$layout   = new FileLayout($layoutFile, $basePath, $options);

		if (!empty($includePath))
		{
			$layout->addIncludePath($includePath);
		}

		return $layout->render($displayData);
	}

	/**
	 * Set a variable in the user session.
	 *
	 * This method cannot be replaced with a call to Factory::getSession->set(). This method takes into account running
	 * under CLI, using a fake session storage. In the end of the day this plugin doesn't work under CLI but being able
	 * to fake session storage under CLI means that we don't have to add gnarly if-blocks everywhere in the code to make
	 * sure it doesn't break CLI either!
	 *
	 * @param   string  $name       The name of the variable to set
	 * @param   string  $value      (optional) The value to set it to, default is null
	 * @param   string  $namespace  (optional) The variable's namespace e.g. the component name. Default: 'default'
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public static function setSessionVar(string $name, ?string $value = null, string $namespace = 'default'): void
	{
		$qualifiedKey = "$namespace.$name";

		if (self::isCli())
		{
			self::getFakeSession()->set($qualifiedKey, $value);

			return;
		}

		if (version_compare(JVERSION, '3.99999.99999', 'lt'))
		{
			Factory::getSession()->set($name, $value, $namespace);

			return;
		}

		Factory::getSession()->set($qualifiedKey, $value);
	}

	/**
	 * Get a variable from the user session
	 *
	 * This method cannot be replaced with a call to Factory::getSession->get(). This method takes into account running
	 * under CLI, using a fake session storage. In the end of the day this plugin doesn't work under CLI but being able
	 * to fake session storage under CLI means that we don't have to add gnarly if-blocks everywhere in the code to make
	 * sure it doesn't break CLI either!
	 *
	 * @param   string  $name       The name of the variable to set
	 * @param   string  $default    (optional) The default value to return if the variable does not exit, default: null
	 * @param   string  $namespace  (optional) The variable's namespace e.g. the component name. Default: 'default'
	 *
	 * @return  mixed
	 *
	 * @since   1.0.0
	 */
	public static function getSessionVar(string $name, ?string $default = null, string $namespace = 'default')
	{
		$qualifiedKey = "$namespace.$name";

		if (self::isCli())
		{
			return self::getFakeSession()->get("$namespace.$name", $default);
		}

		if (version_compare(JVERSION, '3.99999.99999', 'lt'))
		{
			return Factory::getSession()->get($name, $default, $namespace);
		}

		return Factory::getSession()->get($qualifiedKey, $default);
	}

	/**
	 * Unset a variable from the user session
	 *
	 * This method cannot be replaced with a call to Factory::getSession->set(). This method takes into account running
	 * under CLI, using a fake session storage. In the end of the day this plugin doesn't work under CLI but being able
	 * to fake session storage under CLI means that we don't have to add gnarly if-blocks everywhere in the code to make
	 * sure it doesn't break CLI either!
	 *
	 * @param   string  $name       The name of the variable to unset
	 * @param   string  $namespace  (optional) The variable's namespace e.g. the component name. Default: 'default'
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public static function unsetSessionVar(string $name, string $namespace = 'default'): void
	{
		self::setSessionVar($name, null, $namespace);
	}

	/**
	 * Get a fake session registry for CLI applications
	 *
	 * @return  Registry
	 *
	 * @since   1.0.0
	 */
	protected static function getFakeSession(): Registry
	{
		if (!is_object(self::$fakeSession))
		{
			self::$fakeSession = new Registry();
		}

		return self::$fakeSession;
	}

	/**
	 * Return the session token. This method goes through our session abstraction to prevent a fatal exception if it's
	 * accidentally called under CLI.
	 *
	 * @return  mixed
	 *
	 * @since   1.0.0
	 */
	public static function getToken(): string
	{
		// For CLI apps we implement our own fake token system
		if (self::isCli())
		{
			$token = self::getSessionVar('session.token');

			// Create a token
			if (is_null($token))
			{
				$token = UserHelper::genRandomPassword(32);

				self::setSessionVar('session.token', $token);
			}

			return (string) $token;
		}

		// Web application, go through the regular Joomla! API.
		return Factory::getSession()->getToken();
	}

	/**
	 * Writes a log message to the debug log
	 *
	 * @param   string       $plugin     The Social Login plugin which generated this log message
	 * @param   string       $message    The message to write to the log
	 * @param   int          $priority   Log message priority, default is Log::DEBUG
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public static function log(string $plugin, string $message, $priority = Log::DEBUG): void
	{
		Log::add($message, $priority, 'webauthn.' . $plugin);
	}

	/**
	 * Register a debug log file writer for a Social Login plugin.
	 *
	 * @param   string  $plugin  The Social Login plugin for which to register a debug log file writer
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public static function addLogger(string $plugin): void
	{
		// Make sure this logger is not already registered
		if (in_array($plugin, self::$registeredLoggers))
		{
			return;
		}

		self::$registeredLoggers[] = $plugin;

		// We only log errors unless Site Debug is enabled
		$logLevels = Log::ERROR | Log::CRITICAL | Log::ALERT | Log::EMERGENCY;

		if (defined('JDEBUG') && JDEBUG)
		{
			$logLevels = Log::ALL;
		}

		// Add a formatted text logger
		Log::addLogger([
			'text_file' => "webauthn_{$plugin}.php",
			'text_entry_format' => '{DATETIME}	{PRIORITY} {CLIENTIP}	{MESSAGE}'
		], $logLevels, [
			"webauthn.{$plugin}"
		]);
	}

	/**
	 * Logs in a user to the site, bypassing the authentication plugins.
	 *
	 * @param   int              $userId  The user ID to log in
	 * @param   BaseApplication  $app     The application we are running in. Skip to auto-detect (recommended).
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public static function loginUser(int $userId, BaseApplication $app = null): void
	{
		// Trick the class auto-loader into loading the necessary classes
		JLoader::import('joomla.user.authentication');
		JLoader::import('joomla.plugin.helper');
		JLoader::import('joomla.user.helper');
		class_exists('Joomla\\CMS\\Authentication\\Authentication', true);

		// Fake a successful login message
		if (!is_object($app))
		{
			$app = Factory::getApplication();
		}

		$isAdmin = $app->isClient('administrator');
		$user    = Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById($userId);

		// Does the user account have a pending activation?
		if (!empty($user->activation))
		{
			throw new RuntimeException(Text::_('JGLOBAL_AUTH_ACCESS_DENIED'));
		}

		// Is the user account blocked?
		if ($user->block)
		{
			throw new RuntimeException(Text::_('JGLOBAL_AUTH_ACCESS_DENIED'));
		}

		$statusSuccess = Authentication::STATUS_SUCCESS;

		$response                = self::getAuthenticationResponseObject();
		$response->status        = $statusSuccess;
		$response->username      = $user->username;
		$response->fullname      = $user->name;
		$response->error_message = '';
		$response->language      = $user->getParam('language');
		$response->type          = 'Passwordless';

		if ($isAdmin)
		{
			$response->language = $user->getParam('admin_language');
		}

		/**
		 * Set up the login options.
		 *
		 * The 'remember' element forces the use of the Remember Me feature when logging in with Webauthn, as the
		 * users would expect.
		 *
		 * The 'action' element is actually required by plg_user_joomla. It is the core ACL action the logged in user
		 * must be allowed for the login to succeed. Please note that front-end and back-end logins use a different
		 * action. This allows us to provide the social login button on both front- and back-end and be sure that if a
		 * used with no backend access tries to use it to log in Joomla! will just slap him with an error message about
		 * insufficient privileges - the same thing that'd happen if you tried to use your front-end only username and
		 * password in a back-end login form.
		 */
		$options = [
			'remember' => true,
			'action'   => 'core.login.site',
		];

		if (Joomla::isAdminPage())
		{
			$options['action'] = 'core.login.admin';
		}

		// Run the user plugins. They CAN block login by returning boolean false and setting $response->error_message.
		PluginHelper::importPlugin('user');

		$results = $app->triggerEvent('onUserLogin', [(array) $response, $options]);

		// If there is no boolean FALSE result from any plugin the login is successful.
		if (in_array(false, $results, true) == false)
		{
			// Set the user in the session, letting Joomla! know that we are logged in.
			Factory::getSession()->set('user', $user);

			// Trigger the onUserAfterLogin event
			$options['user']         = $user;
			$options['responseType'] = $response->type;

			// The user is successfully logged in. Run the after login events
			$app->triggerEvent('onUserAfterLogin', [$options]);

			return;
		}

		// If we are here the plugins marked a login failure. Trigger the onUserLoginFailure Event.
		$app->triggerEvent('onUserLoginFailure', [(array) $response]);

		// Log the failure
		Log::add($response->error_message, Log::WARNING, 'jerror');

		// Throw an exception to let the caller know that the login failed
		throw new RuntimeException($response->error_message);
	}

	/**
	 * Returns a (blank) Joomla! authentication response
	 *
	 * @return  AuthenticationResponse
	 *
	 * @since   1.0.0
	 */
	public static function getAuthenticationResponseObject(): AuthenticationResponse
	{
		// Force the class auto-loader to load the JAuthentication class
		JLoader::import('joomla.user.authentication');
		class_exists('Joomla\\CMS\\Authentication\\Authentication', true);

		return new AuthenticationResponse();
	}

	/**
	 * Have Joomla! process a login failure
	 *
	 * @param   AuthenticationResponse  $response    The Joomla! auth response object
	 * @param   BaseApplication         $app         The application we are running in. Skip to auto-detect (recommended).
	 * @param   string                  $logContext  Logging context (plugin name). Default: system.
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public static function processLoginFailure(AuthenticationResponse $response, BaseApplication $app = null, string $logContext = 'system')
	{
		// Import the user plugin group.
		PluginHelper::importPlugin('user');

		if (!is_object($app))
		{
			$app = Factory::getApplication();
		}

		// Trigger onUserLoginFailure Event.
		Joomla::log($logContext, "Calling onUserLoginFailure plugin event");
		$app->triggerEvent('onUserLoginFailure', array((array) $response));

		// If status is success, any error will have been raised by the user plugin
		$expectedStatus = Authentication::STATUS_SUCCESS;

		if ($response->status !== $expectedStatus)
		{
			Joomla::log($logContext, "The login failure has been logged in Joomla's error log");

			// Everything logged in the 'jerror' category ends up being enqueued in the application message queue.
			Log::add($response->error_message, Log::WARNING, 'jerror');
		}
		else
		{
			Joomla::log($logContext, "The login failure was caused by a third party user plugin but it did not return any further information. Good luck figuring this one out...", Log::WARNING);
		}

		return false;
	}

	/**
	 * Format a date for display.
	 *
	 * The $tzAware parameter defines whether the formatted date will be timezone-aware. If set to false the formatted
	 * date will be rendered in the UTC timezone. If set to true the code will automatically try to use the logged in
	 * user's timezone or, if none is set, the site's default timezone (Server Timezone). If set to a positive integer
	 * the same thing will happen but for the specified user ID instead of the currently logged in user.
	 *
	 * @param   string|\DateTime  $date     The date to format
	 * @param   string            $format   The format string, default is Joomla's DATE_FORMAT_LC6 (usually "Y-m-d H:i:s")
	 * @param   bool|int          $tzAware  Should the format be timezone aware? See notes above.
	 *
	 * @return  string
	 */
	public static function formatDate($date, ?string $format = null, bool $tzAware = true): string
	{
		$utcTimeZone = new DateTimeZone('UTC');
		$jDate       = new Date($date, $utcTimeZone);

		// Which timezone should I use?
		$tz = null;

		if ($tzAware !== false)
		{
			$userId = is_bool($tzAware) ? null : (int) $tzAware;

			try
			{
				$tzDefault = Factory::getApplication()->get('offset');
			}
			catch (\Exception $e)
			{
				$tzDefault = 'GMT';
			}

			$user = Factory::getUser($userId);
			$tz   = $user->getParam('timezone', $tzDefault);
		}

		if (!empty($tz))
		{
			try
			{
				$userTimeZone = new DateTimeZone($tz);

				$jDate->setTimezone($userTimeZone);
			}
			catch (\Exception $e)
			{
				// Nothing. Fall back to UTC.
			}
		}

		if (empty($format))
		{
			$format = Text::_('DATE_FORMAT_LC6');
		}

		return $jDate->format($format, true);
	}

	/**
	 * Returns the current Joomla document type.
	 *
	 * The error catching is necessary because the application document object or even the application object itself may
	 * have not yet been initialized. For example, a system plugin running inside a custom application object which does
	 * not create a document object or which does not go through Joomla's Factory to create the application object. In
	 * practice these are CLI and custom web applications used for maintenance and third party service callbacks. They
	 * end up loading the system plugins but either don't go through Factory or at least don't create a document object.
	 *
	 * @return  string
	 */
	public static function getDocumentType(): string
	{
		if (is_null(self::$joomlaDocumentType))
		{
			try
			{
				/** @var CMSApplication $app */
				$app      = Factory::getApplication();
				$document = $app->getDocument();
			}
			catch (Exception $e)
			{
				$document = null;
			}

			self::$joomlaDocumentType = (is_null($document)) ? 'error' : $document->getType();
		}

		return self::$joomlaDocumentType;
	}
}
