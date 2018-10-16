<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Cache\Cache;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Editor\Editor;
use Joomla\CMS\Input\Input;
use Joomla\CMS\Language\Language;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Mail\Mail;
use Joomla\CMS\Mail\MailHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;
use Joomla\Registry\Registry;

/**
 * Joomla Platform Factory class.
 *
 * @since  1.7.0
 */
abstract class Factory
{
	/**
	 * Global application object
	 *
	 * @var    CMSApplication
	 * @since  1.7.0
	 */
	public static $application = null;

	/**
	 * Global cache object
	 *
	 * @var    Cache
	 * @since  1.7.0
	 */
	public static $cache = null;

	/**
	 * Global configuraiton object
	 *
	 * @var    \JConfig
	 * @since  1.7.0
	 */
	public static $config = null;

	/**
	 * Container for Date instances
	 *
	 * @var    array
	 * @since  1.7.3
	 */
	public static $dates = array();

	/**
	 * Global session object
	 *
	 * @var    Session
	 * @since  1.7.0
	 */
	public static $session = null;

	/**
	 * Global language object
	 *
	 * @var   Language
	 * @since  1.7.0
	 */
	public static $language = null;

	/**
	 * Global document object
	 *
	 * @var    \JDocument
	 * @since  1.7.0
	 */
	public static $document = null;

	/**
	 * Global ACL object
	 *
	 * @var    Access
	 * @since  1.7.0
	 * @deprecated  4.0
	 */
	public static $acl = null;

	/**
	 * Global database object
	 *
	 * @var    \JDatabaseDriver
	 * @since  1.7.0
	 */
	public static $database = null;

	/**
	 * Global mailer object
	 *
	 * @var    Mail
	 * @since  1.7.0
	 */
	public static $mailer = null;

	/**
	 * Get an application object.
	 *
	 * Returns the global {@link CMSApplication} object, only creating it if it doesn't already exist.
	 *
	 * @param   mixed   $id      A client identifier or name.
	 * @param   array   $config  An optional associative array of configuration settings.
	 * @param   string  $prefix  Application prefix
	 *
	 * @return  CMSApplication object
	 *
	 * @see     JApplication
	 * @since   1.7.0
	 * @throws  \Exception
	 */
	public static function getApplication($id = null, array $config = array(), $prefix = 'J')
	{
		if (!self::$application)
		{
			if (!$id)
			{
				throw new \Exception('Failed to start application', 500);
			}

			self::$application = CMSApplication::getInstance($id);

			// Attach a delegated JLog object to the application
			self::$application->setLogger(Log::createDelegatedLogger());
		}

		return self::$application;
	}

	/**
	 * Get a configuration object
	 *
	 * Returns the global {@link \JConfig} object, only creating it if it doesn't already exist.
	 *
	 * @param   string  $file       The path to the configuration file
	 * @param   string  $type       The type of the configuration file
	 * @param   string  $namespace  The namespace of the configuration file
	 *
	 * @return  Registry
	 *
	 * @see     Registry
	 * @since   1.7.0
	 */
	public static function getConfig($file = null, $type = 'PHP', $namespace = '')
	{
		if (!self::$config)
		{
			if ($file === null)
			{
				$file = JPATH_CONFIGURATION . '/configuration.php';
			}

			self::$config = self::createConfig($file, $type, $namespace);
		}

		return self::$config;
	}

	/**
	 * Get a session object.
	 *
	 * Returns the global {@link Session} object, only creating it if it doesn't already exist.
	 *
	 * @param   array  $options  An array containing session options
	 *
	 * @return  Session object
	 *
	 * @see     Session
	 * @since   1.7.0
	 */
	public static function getSession(array $options = array())
	{
		if (!self::$session)
		{
			self::$session = self::createSession($options);
		}

		return self::$session;
	}

	/**
	 * Get a language object.
	 *
	 * Returns the global {@link Language} object, only creating it if it doesn't already exist.
	 *
	 * @return  Language object
	 *
	 * @see     Language
	 * @since   1.7.0
	 */
	public static function getLanguage()
	{
		if (!self::$language)
		{
			self::$language = self::createLanguage();
		}

		return self::$language;
	}

	/**
	 * Get a document object.
	 *
	 * Returns the global {@link \JDocument} object, only creating it if it doesn't already exist.
	 *
	 * @return  \JDocument object
	 *
	 * @see     \JDocument
	 * @since   1.7.0
	 */
	public static function getDocument()
	{
		if (!self::$document)
		{
			self::$document = self::createDocument();
		}

		return self::$document;
	}

	/**
	 * Get a user object.
	 *
	 * Returns the global {@link User} object, only creating it if it doesn't already exist.
	 *
	 * @param   integer  $id  The user to load - Can be an integer or string - If string, it is converted to ID automatically.
	 *
	 * @return  User object
	 *
	 * @see     User
	 * @since   1.7.0
	 */
	public static function getUser($id = null)
	{
		$instance = self::getSession()->get('user');

		if (is_null($id))
		{
			if (!($instance instanceof User))
			{
				$instance = User::getInstance();
			}
		}
		// Check if we have a string as the id or if the numeric id is the current instance
		elseif (!($instance instanceof User) || is_string($id) || $instance->id !== $id)
		{
			$instance = User::getInstance($id);
		}

		return $instance;
	}

	/**
	 * Get a cache object
	 *
	 * Returns the global {@link CacheController} object
	 *
	 * @param   string  $group    The cache group name
	 * @param   string  $handler  The handler to use
	 * @param   string  $storage  The storage method
	 *
	 * @return  \Joomla\CMS\Cache\CacheController object
	 *
	 * @see     JCache
	 * @since   1.7.0
	 */
	public static function getCache($group = '', $handler = 'callback', $storage = null)
	{
		$hash = md5($group . $handler . $storage);

		if (isset(self::$cache[$hash]))
		{
			return self::$cache[$hash];
		}

		$handler = ($handler == 'function') ? 'callback' : $handler;

		$options = array('defaultgroup' => $group);

		if (isset($storage))
		{
			$options['storage'] = $storage;
		}

		$cache = Cache::getInstance($handler, $options);

		self::$cache[$hash] = $cache;

		return self::$cache[$hash];
	}

	/**
	 * Get an authorization object
	 *
	 * Returns the global {@link Access} object, only creating it
	 * if it doesn't already exist.
	 *
	 * @return  Access object
	 *
	 * @deprecated  4.0 - Use JAccess directly.
	 */
	public static function getAcl()
	{
		Log::add(__METHOD__ . ' is deprecated. Use Access directly.', Log::WARNING, 'deprecated');

		if (!self::$acl)
		{
			self::$acl = new Access;
		}

		return self::$acl;
	}

	/**
	 * Get a database object.
	 *
	 * Returns the global {@link \JDatabaseDriver} object, only creating it if it doesn't already exist.
	 *
	 * @return  \JDatabaseDriver
	 *
	 * @see     \JDatabaseDriver
	 * @since   1.7.0
	 */
	public static function getDbo()
	{
		if (!self::$database)
		{
			self::$database = self::createDbo();
		}

		return self::$database;
	}

	/**
	 * Get a mailer object.
	 *
	 * Returns the global {@link \JMail} object, only creating it if it doesn't already exist.
	 *
	 * @return  \JMail object
	 *
	 * @see     JMail
	 * @since   1.7.0
	 */
	public static function getMailer()
	{
		if (!self::$mailer)
		{
			self::$mailer = self::createMailer();
		}

		$copy = clone self::$mailer;

		return $copy;
	}

	/**
	 * Get a parsed XML Feed Source
	 *
	 * @param   string   $url         Url for feed source.
	 * @param   integer  $cache_time  Time to cache feed for (using internal cache mechanism).
	 *
	 * @return  mixed  SimplePie parsed object on success, false on failure.
	 *
	 * @since   1.7.0
	 * @throws  \BadMethodCallException
	 * @deprecated  4.0  Use directly JFeedFactory or supply SimplePie instead. Mehod will be proxied to JFeedFactory beginning in 3.2
	 */
	public static function getFeedParser($url, $cache_time = 0)
	{
		if (!class_exists('JSimplepieFactory'))
		{
			throw new \BadMethodCallException('JSimplepieFactory not found');
		}

		Log::add(__METHOD__ . ' is deprecated.   Use JFeedFactory() or supply SimplePie instead.', Log::WARNING, 'deprecated');

		return \JSimplepieFactory::getFeedParser($url, $cache_time);
	}

	/**
	 * Reads a XML file.
	 *
	 * @param   string   $data    Full path and file name.
	 * @param   boolean  $isFile  true to load a file or false to load a string.
	 *
	 * @return  mixed    JXMLElement or SimpleXMLElement on success or false on error.
	 *
	 * @see     JXMLElement
	 * @since   1.7.0
	 * @note    When JXMLElement is not present a SimpleXMLElement will be returned.
	 * @deprecated  4.0 - Use SimpleXML directly.
	 */
	public static function getXml($data, $isFile = true)
	{
		Log::add(__METHOD__ . ' is deprecated. Use SimpleXML directly.', Log::WARNING, 'deprecated');

		$class = 'SimpleXMLElement';

		if (class_exists('JXMLElement'))
		{
			$class = 'JXMLElement';
		}

		// Disable libxml errors and allow to fetch error information as needed
		libxml_use_internal_errors(true);

		if ($isFile)
		{
			// Try to load the XML file
			$xml = simplexml_load_file($data, $class);
		}
		else
		{
			// Try to load the XML string
			$xml = simplexml_load_string($data, $class);
		}

		if ($xml === false)
		{
			Log::add(\JText::_('JLIB_UTIL_ERROR_XML_LOAD'), Log::WARNING, 'jerror');

			if ($isFile)
			{
				Log::add($data, Log::WARNING, 'jerror');
			}

			foreach (libxml_get_errors() as $error)
			{
				Log::add($error->message, Log::WARNING, 'jerror');
			}
		}

		return $xml;
	}

	/**
	 * Get an editor object.
	 *
	 * @param   string  $editor  The editor to load, depends on the editor plugins that are installed
	 *
	 * @return  Editor instance of Editor
	 *
	 * @since   1.7.0
	 * @throws  \BadMethodCallException
	 * @deprecated 4.0 - Use Editor directly
	 */
	public static function getEditor($editor = null)
	{
		Log::add(__METHOD__ . ' is deprecated. Use JEditor directly.', Log::WARNING, 'deprecated');

		if (!class_exists('JEditor'))
		{
			throw new \BadMethodCallException('JEditor not found');
		}

		// Get the editor configuration setting
		if (is_null($editor))
		{
			$conf = self::getConfig();
			$editor = $conf->get('editor');
		}

		return Editor::getInstance($editor);
	}

	/**
	 * Return a reference to the {@link Uri} object
	 *
	 * @param   string  $uri  Uri name.
	 *
	 * @return  Uri object
	 *
	 * @see     Uri
	 * @since   1.7.0
	 * @deprecated  4.0 - Use JUri directly.
	 */
	public static function getUri($uri = 'SERVER')
	{
		Log::add(__METHOD__ . ' is deprecated. Use JUri directly.', Log::WARNING, 'deprecated');

		return Uri::getInstance($uri);
	}

	/**
	 * Return the {@link Date} object
	 *
	 * @param   mixed  $time      The initial time for the JDate object
	 * @param   mixed  $tzOffset  The timezone offset.
	 *
	 * @return  Date object
	 *
	 * @see     Date
	 * @since   1.7.0
	 */
	public static function getDate($time = 'now', $tzOffset = null)
	{
		static $classname;
		static $mainLocale;

		$language = self::getLanguage();
		$locale = $language->getTag();

		if (!isset($classname) || $locale != $mainLocale)
		{
			// Store the locale for future reference
			$mainLocale = $locale;

			if ($mainLocale !== false)
			{
				$classname = str_replace('-', '_', $mainLocale) . 'Date';

				if (!class_exists($classname))
				{
					// The class does not exist, default to Date
					$classname = 'Joomla\\CMS\\Date\\Date';
				}
			}
			else
			{
				// No tag, so default to Date
				$classname = 'Joomla\\CMS\\Date\\Date';
			}
		}

		$key = $time . '-' . ($tzOffset instanceof \DateTimeZone ? $tzOffset->getName() : (string) $tzOffset);

		if (!isset(self::$dates[$classname][$key]))
		{
			self::$dates[$classname][$key] = new $classname($time, $tzOffset);
		}

		$date = clone self::$dates[$classname][$key];

		return $date;
	}

	/**
	 * Create a configuration object
	 *
	 * @param   string  $file       The path to the configuration file.
	 * @param   string  $type       The type of the configuration file.
	 * @param   string  $namespace  The namespace of the configuration file.
	 *
	 * @return  Registry
	 *
	 * @see     Registry
	 * @since   1.7.0
	 */
	protected static function createConfig($file, $type = 'PHP', $namespace = '')
	{
		if (is_file($file))
		{
			include_once $file;
		}

		// Create the registry with a default namespace of config
		$registry = new Registry;

		// Sanitize the namespace.
		$namespace = ucfirst((string) preg_replace('/[^A-Z_]/i', '', $namespace));

		// Build the config name.
		$name = 'JConfig' . $namespace;

		// Handle the PHP configuration type.
		if ($type == 'PHP' && class_exists($name))
		{
			// Create the JConfig object
			$config = new $name;

			// Load the configuration values into the registry
			$registry->loadObject($config);
		}

		return $registry;
	}

	/**
	 * Create a session object
	 *
	 * @param   array  $options  An array containing session options
	 *
	 * @return  Session object
	 *
	 * @since   1.7.0
	 */
	protected static function createSession(array $options = array())
	{
		// Get the Joomla configuration settings
		$conf    = self::getConfig();
		$handler = $conf->get('session_handler', 'none');

		// Config time is in minutes
		$options['expire'] = ($conf->get('lifetime')) ? $conf->get('lifetime') * 60 : 900;

		// The session handler needs a JInput object, we can inject it without having a hard dependency to an application instance
		$input = self::$application ? self::getApplication()->input : new Input;

		$sessionHandler = new \JSessionHandlerJoomla($options);
		$sessionHandler->input = $input;

		$session = Session::getInstance($handler, $options, $sessionHandler);

		if ($session->getState() == 'expired')
		{
			$session->restart();
		}

		return $session;
	}

	/**
	 * Create a database object
	 *
	 * @return  \JDatabaseDriver
	 *
	 * @see     \JDatabaseDriver
	 * @since   1.7.0
	 */
	protected static function createDbo()
	{
		$conf = self::getConfig();

		$host = $conf->get('host');
		$user = $conf->get('user');
		$password = $conf->get('password');
		$database = $conf->get('db');
		$prefix = $conf->get('dbprefix');
		$driver = $conf->get('dbtype');
		$debug = $conf->get('debug');

		$options = array('driver' => $driver, 'host' => $host, 'user' => $user, 'password' => $password, 'database' => $database, 'prefix' => $prefix);

		try
		{
			$db = \JDatabaseDriver::getInstance($options);
		}
		catch (\RuntimeException $e)
		{
			if (!headers_sent())
			{
				header('HTTP/1.1 500 Internal Server Error');
			}

			jexit('Database Error: ' . $e->getMessage());
		}

		$db->setDebug($debug);

		return $db;
	}

	/**
	 * Create a mailer object
	 *
	 * @return  \JMail object
	 *
	 * @see     \JMail
	 * @since   1.7.0
	 */
	protected static function createMailer()
	{
		$conf = self::getConfig();

		$smtpauth = ($conf->get('smtpauth') == 0) ? null : 1;
		$smtpuser = $conf->get('smtpuser');
		$smtppass = $conf->get('smtppass');
		$smtphost = $conf->get('smtphost');
		$smtpsecure = $conf->get('smtpsecure');
		$smtpport = $conf->get('smtpport');
		$mailfrom = $conf->get('mailfrom');
		$fromname = $conf->get('fromname');
		$mailer = $conf->get('mailer');

		// Create a Mail object
		$mail = Mail::getInstance();

		// Clean the email address
		$mailfrom = MailHelper::cleanLine($mailfrom);

		// Set default sender without Reply-to if the mailfrom is a valid address
		if (MailHelper::isEmailAddress($mailfrom))
		{
			// Wrap in try/catch to catch phpmailerExceptions if it is throwing them
			try
			{
				// Check for a false return value if exception throwing is disabled
				if ($mail->setFrom($mailfrom, MailHelper::cleanLine($fromname), false) === false)
				{
					Log::add(__METHOD__ . '() could not set the sender data.', Log::WARNING, 'mail');
				}
			}
			catch (\phpmailerException $e)
			{
				Log::add(__METHOD__ . '() could not set the sender data.', Log::WARNING, 'mail');
			}
		}

		// Default mailer is to use PHP's mail function
		switch ($mailer)
		{
			case 'smtp':
				$mail->useSmtp($smtpauth, $smtphost, $smtpuser, $smtppass, $smtpsecure, $smtpport);
				break;

			case 'sendmail':
				$mail->isSendmail();
				break;

			default:
				$mail->isMail();
				break;
		}

		return $mail;
	}

	/**
	 * Create a language object
	 *
	 * @return  Language object
	 *
	 * @see     Language
	 * @since   1.7.0
	 */
	protected static function createLanguage()
	{
		$conf = self::getConfig();
		$locale = $conf->get('language');
		$debug = $conf->get('debug_lang');
		$lang = Language::getInstance($locale, $debug);

		return $lang;
	}

	/**
	 * Create a document object
	 *
	 * @return  \JDocument object
	 *
	 * @see     \JDocument
	 * @since   1.7.0
	 */
	protected static function createDocument()
	{
		$lang = self::getLanguage();

		$input = self::getApplication()->input;
		$type = $input->get('format', 'html', 'cmd');

		$version = new Version;

		$attributes = array(
			'charset'      => 'utf-8',
			'lineend'      => 'unix',
			'tab'          => "\t",
			'language'     => $lang->getTag(),
			'direction'    => $lang->isRtl() ? 'rtl' : 'ltr',
			'mediaversion' => $version->getMediaVersion(),
		);

		return \JDocument::getInstance($type, $attributes);
	}

	/**
	 * Creates a new stream object with appropriate prefix
	 *
	 * @param   boolean  $use_prefix   Prefix the connections for writing
	 * @param   boolean  $use_network  Use network if available for writing; use false to disable (e.g. FTP, SCP)
	 * @param   string   $ua           UA User agent to use
	 * @param   boolean  $uamask       User agent masking (prefix Mozilla)
	 *
	 * @return  \JStream
	 *
	 * @see     \JStream
	 * @since   1.7.0
	 */
	public static function getStream($use_prefix = true, $use_network = true, $ua = null, $uamask = false)
	{
		\JLoader::import('joomla.filesystem.stream');

		// Setup the context; Joomla! UA and overwrite
		$context = array();
		$version = new Version;

		// Set the UA for HTTP and overwrite for FTP
		$context['http']['user_agent'] = $version->getUserAgent($ua, $uamask);
		$context['ftp']['overwrite'] = true;

		if ($use_prefix)
		{
			$FTPOptions = \JClientHelper::getCredentials('ftp');
			$SCPOptions = \JClientHelper::getCredentials('scp');

			if ($FTPOptions['enabled'] == 1 && $use_network)
			{
				$prefix = 'ftp://' . $FTPOptions['user'] . ':' . $FTPOptions['pass'] . '@' . $FTPOptions['host'];
				$prefix .= $FTPOptions['port'] ? ':' . $FTPOptions['port'] : '';
				$prefix .= $FTPOptions['root'];
			}
			elseif ($SCPOptions['enabled'] == 1 && $use_network)
			{
				$prefix = 'ssh2.sftp://' . $SCPOptions['user'] . ':' . $SCPOptions['pass'] . '@' . $SCPOptions['host'];
				$prefix .= $SCPOptions['port'] ? ':' . $SCPOptions['port'] : '';
				$prefix .= $SCPOptions['root'];
			}
			else
			{
				$prefix = JPATH_ROOT . '/';
			}

			$retval = new \JStream($prefix, JPATH_ROOT, $context);
		}
		else
		{
			$retval = new \JStream('', '', $context);
		}

		return $retval;
	}
}
