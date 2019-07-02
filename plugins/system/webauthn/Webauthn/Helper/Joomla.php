<?php
/**
 * @package   AkeebaPasswordlessLogin
 * @copyright Copyright (c)2018-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Passwordless\Webauthn\Helper;

// Protect from unauthorized access
use DateTimeZone;
use Exception;
use JDatabaseDriver;
use JEventDispatcher;
use JLoader;
use Joomla\CMS\Application\BaseApplication;
use Joomla\CMS\Application\CliApplication;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Authentication\Authentication;
use Joomla\CMS\Authentication\AuthenticationResponse;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Http\Http;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Mail\Mail;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\String\PunycodeHelper;
use Joomla\CMS\User\User;
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
				$app = self::getApplication();
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
					$app = self::getApplication();
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
		$myUser = self::getUser();

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
		$layout   = self::getJLayoutFromFile($layoutFile, $options, $basePath);

		if (!empty($includePath))
		{
			$layout->addIncludePath($includePath);
		}

		return $layout->render($displayData);
	}

	/**
	 * Execute a plugin event and return the results
	 *
	 * @param   string           $event  The plugin event to trigger.
	 * @param   array            $data   The data to pass to the event handlers.
	 * @param   BaseApplication  $app    The application to run plugins against,
	 *                                   default the currently loaded application.
	 *
	 * @return  array  The plugin responses
	 *
	 * @throws  RuntimeException  When we cannot run the plugins
	 * @throws  Exception         When we cannot create the application
	 *
	 * @since   1.0.0
	 */
	public static function runPlugins(string $event, array $data, ?BaseApplication $app = null): array
	{
		if (!is_object($app))
		{
			$app = self::getApplication();
		}

		if (method_exists($app, 'triggerEvent'))
		{
			return $app->triggerEvent($event, $data);
		}

		if (class_exists('JEventDispatcher'))
		{
			return JEventDispatcher::getInstance()->trigger($event, $data);
		}

		throw new RuntimeException('Cannot run plugins');
	}

	/**
	 * Tells Joomla! to load a plugin group.
	 *
	 * This is just a wrapper around JPluginHelper. We use our own helper method for future-proofing...
	 *
	 * @param   string       $group   The plugin group to import
	 * @param   string|null  $plugin  The specific plugin to import
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public static function importPlugins(string $group, ?string $plugin = null): void
	{
		PluginHelper::importPlugin($group, $plugin);
	}

	/**
	 * Get the CMS application object
	 *
	 * @return  CMSApplication
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public static function getApplication(): CMSApplication
	{
		$app = Factory::getApplication();

		if (self::isCmsApplication($app))
		{
			return $app;
		}

		throw new RuntimeException('Cannot find a valid CMS application object');
	}

	/**
	 * Returns the user, delegates to JFactory/Factory.
	 *
	 * @param   int|null  $id  The ID of the Joomla! user to load, default null (currently logged in user)
	 *
	 * @return  User
	 *
	 * @since   1.0.0
	 */
	public static function getUser(?int $id = null): User
	{
		return Factory::getUser($id);
	}

	/**
	 * Get the Joomla! session
	 *
	 * @return  Session
	 *
	 * @since   1.0.0
	 */
	protected static function getSession(): Session
	{
		return Factory::getSession();
	}

	/**
	 * Return a Joomla! layout object, creating from a layout file
	 *
	 * @param   string  $layoutFile  Path to the layout file
	 * @param   array   $options     Options to the layout file
	 * @param   string  $basePath    Base path for the layout file
	 *
	 * @return  FileLayout
	 *
	 * @since   1.0.0
	 */
	public static function getJLayoutFromFile(string $layoutFile, array $options, string $basePath): FileLayout
	{
		return new FileLayout($layoutFile, $basePath, $options);
	}

	/**
	 * Set a variable in the user session
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
			self::getSession()->set($name, $value, $namespace);

			return;
		}

		self::getSession()->set($qualifiedKey, $value);
	}

	/**
	 * Get a variable from the user session
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
			return self::getSession()->get($name, $default, $namespace);
		}

		return self::getSession()->get($qualifiedKey, $default);
	}

	/**
	 * Unset a variable from the user session
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
	 * Return the session token. Two types of tokens can be returned:
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
				$token = self::generateRandom(32);

				self::setSessionVar('session.token', $token);
			}

			return (string) $token;
		}

		// Web application, go through the regular Joomla! API.
		return self::getSession()->getToken();
	}

	/**
	 * Generate a random string
	 *
	 * @param   int  $length  Random string length
	 *
	 * @return  string
	 *
	 * @since   1.0.0
	 */
	public static function generateRandom(int $length = 32): string
	{
		return UserHelper::genRandomPassword($length);
	}

	/**
	 * Converts an email to punycode
	 *
	 * @param   string  $email  The original email, with Unicode characters
	 *
	 * @return  string  The punycode-transcribed email address
	 *
	 * @since   1.0.0
	 */
	public static function emailToPunycode(string $email): string
	{
		return PunycodeHelper::emailToPunycode($email);
	}

	/**
	 * Is the variable an CMS application object?
	 *
	 * @param   mixed  $app
	 *
	 * @return  bool
	 *
	 * @since   1.0.0
	 */
	public static function isCmsApplication($app): bool
	{
		if (!is_object($app))
		{
			return false;
		}

		return $app instanceof CMSApplication;
	}

	/**
	 * Get the Joomla! database driver object
	 *
	 * @return  JDatabaseDriver
	 *
	 * @since   1.0.0
	 */
	public static function getDbo(): JDatabaseDriver
	{
		return Factory::getDbo();
	}

	/**
	 * Get the Joomla! global configuration object
	 *
	 * @return  Registry
	 *
	 * @since   1.0.0
	 */
	public static function getConfig(): Registry
	{
		return Factory::getConfig();
	}

	/**
	 * Get the Joomla! mailer object
	 *
	 * @return  Mail
	 *
	 * @since   1.0.0
	 */
	public static function getMailer(): Mail
	{
		return Factory::getMailer();
	}

	/**
	 * Returns the numeric user ID given a username or 0 if the user does not exist.
	 *
	 * @param   string  $username  The username to look up
	 *
	 * @return  int
	 *
	 * @since   1.0.0
	 */
	public static function getUserId(string $username): int
	{
		return UserHelper::getUserId($username);
	}

	/**
	 * Return a translated string
	 *
	 * @param   string  $string  The translation key
	 *
	 * @return  string
	 *
	 * @since   1.0.0
	 */
	public static function _(string $string): string
	{
		return call_user_func_array(array('Joomla\\CMS\\Language\\Text', '_'), array($string));
	}

	/**
	 * Passes a string thru a sprintf.
	 *
	 * Note that this method can take a mixed number of arguments as for the sprintf function.
	 *
	 * The last argument can take an array of options:
	 *
	 * array('jsSafe'=>boolean, 'interpretBackSlashes'=>boolean, 'script'=>boolean)
	 *
	 * where:
	 *
	 * jsSafe is a boolean to generate a javascript safe strings.
	 * interpretBackSlashes is a boolean to interpret backslashes \\->\, \n->new line, \t->tabulation.
	 * script is a boolean to indicate that the string will be push in the javascript language store.
	 *
	 * @param   string  $string  The format string.
	 * @param   mixed   $args    The variable arguments to sprintf().
	 *
	 * @return  string
	 *
	 * @see     Text::sprintf().
	 *
	 * @since   1.0.0
	 */
	public static function sprintf(string $string, ...$args): string
	{
		return call_user_func_array(array('Joomla\\CMS\\Language\\Text', 'sprintf'), array_merge([$string], $args));
	}

	/**
	 * Get an HTTP client
	 *
	 * @param   array  $options  The options to pass to the factory when building the client.
	 *
	 * @return  Http
	 *
	 * @since   1.0.0
	 */
	public static function getHttpClient(array $options = []): Http
	{
		$optionRegistry = new Registry($options);

		return HttpFactory::getHttp($optionRegistry);
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
			$app = Joomla::getApplication();
		}

		$isAdmin = $app->isClient('administrator');
		$user    = Joomla::getUser($userId);

		// Does the user account have a pending activation?
		if (!empty($user->activation))
		{
			throw new RuntimeException(Joomla::_('JGLOBAL_AUTH_ACCESS_DENIED'));
		}

		// Is the user account blocked?
		if ($user->block)
		{
			throw new RuntimeException(Joomla::_('JGLOBAL_AUTH_ACCESS_DENIED'));
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
		$options = array(
			'remember' => true,
			'action' => 'core.login.site',
		);

		if (Joomla::isAdminPage())
		{
			$options['action'] = 'core.login.admin';
		}

		// Run the user plugins. They CAN block login by returning boolean false and setting $response->error_message.
		Joomla::importPlugins('user');
		$results = Joomla::runPlugins('onUserLogin', array((array) $response, $options), $app);

		// If there is no boolean FALSE result from any plugin the login is successful.
		if (in_array(false, $results, true) == false)
		{
			// Set the user in the session, letting Joomla! know that we are logged in.
			Joomla::getSession()->set('user', $user);

			// Trigger the onUserAfterLogin event
			$options['user']         = $user;
			$options['responseType'] = $response->type;

			// The user is successfully logged in. Run the after login events
			Joomla::runPlugins('onUserAfterLogin', array($options), $app);

			return;
		}

		// If we are here the plugins marked a login failure. Trigger the onUserLoginFailure Event.
		Joomla::runPlugins('onUserLoginFailure', array((array) $response), $app);

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
		Joomla::importPlugins('user');

		if (!is_object($app))
		{
			$app = Joomla::getApplication();
		}

		// Trigger onUserLoginFailure Event.
		Joomla::log($logContext, "Calling onUserLoginFailure plugin event");
		Joomla::runPlugins('onUserLoginFailure', array((array) $response), $app);

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
			$format = Joomla::_('DATE_FORMAT_LC6');
		}

		return $jDate->format($format, true);
	}
}
