<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Engine\Platform;

// Protection against direct access
defined('AKEEBAENGINE') || die();

use Akeeba\Engine\Driver\Joomla;
use Akeeba\Engine\Driver\Mysql;
use Akeeba\Engine\Driver\Mysqli;
use Akeeba\Engine\Driver\Pdomysql;
use Akeeba\Engine\Factory;
use Akeeba\Engine\Finalization\TestExtract;
use Akeeba\Engine\Platform;
use Akeeba\Engine\Platform\Base as BasePlatform;
use DateTimeZone;
use Exception;
use FOF30\Container\Container;
use FOF30\Date\Date;
use JLoader;
use JMail;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Mail\Mail;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Version;
use Psr\Log\LogLevel;

if (!defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR); // Still required by Joomla! :(
}

/**
 * Joomla! 3.x platform class
 */
class Joomla3x extends BasePlatform
{
	/**
	 * Override profile ID, for use in automated testing only
	 *
	 * @var   int|null
	 */
	public static $profile_id = null;
	/**
	 * Platform class priority
	 *
	 * @var  int
	 */
	public $priority = 53;
	/**
	 * This platform's name
	 *
	 * @var  string
	 */
	public $platformName = 'joomla3x';
	/**
	 * The container of the Akeeba Backup component
	 *
	 * @var  Container
	 */
	protected $container = null;
	/**
	 * Flash variables for the CLI application. We use this array since we're hell bent on NOT using Joomla's broken
	 * session package.
	 *
	 * @var   array
	 *
	 * @since 5.3.5
	 */
	protected $flashVariables = [];

	/**
	 * Public constructor
	 */
	function __construct()
	{
		$configOverrides = [];

		if (class_exists('Akeeba\\Engine\\Finalization\\TestExtract'))
		{
			$configOverrides['volatile.core.finalization.action_handlers']     = [
				new TestExtract(),
			];
			$configOverrides['volatile.core.finalization.action_queue_before'] = [
				'test_extract',
			];
		}

		// Apply the configuration overrides, please
		$this->configOverrides = $configOverrides;

		$this->container = Container::getInstance('com_akeeba');
	}

	public static function quirk_013()
	{
		$stock_dirs  = Platform::getInstance()->get_stock_directories();
		$default_out = @realpath($stock_dirs['[DEFAULT_OUTPUT]']);

		$registry = Factory::getConfiguration();
		$outdir   = $registry->get('akeeba.basic.output_directory');

		foreach ($stock_dirs as $macro => $replacement)
		{
			$outdir = str_replace($macro, $replacement, $outdir);
		}

		$outdir_real = @realpath($outdir);

		// If the output folder is the default one (or any subdir), we are safe
		if (strpos($outdir_real, $default_out) !== false)
		{
			return false;
		}

		$component_path = @realpath(JPATH_ADMINISTRATOR . '/components/com_akeeba');

		$forbiddenPaths = [
			'akeeba',
			'AliceChecks',
			'AliceEngine',
			'alice',
			'assets',
			'Assets',
			'BackupEngine',
			'BackupPlatform',
			'Controller',
			'controllers',
			'Dispatcher',
			'engine',
			'fields',
			'Helper',
			'helpers',
			'Master',
			'Model',
			'models',
			'platform',
			'plugins',
			'sql',
			'tables',
			'Toolbar',
			'View',
			'views',
			'ViewTemplates',
		];

		foreach ($forbiddenPaths as $subdir)
		{
			$checkPath = realpath($component_path . '/' . $subdir);

			if ($checkPath === false)
			{
				continue;
			}

			$checkPath .= DIRECTORY_SEPARATOR;

			if (strpos($outdir_real, $checkPath) === 0)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Loads the current configuration off the database table
	 *
	 * @param   int  $profile_id  The profile where to read the configuration from, defaults to current profile
	 *
	 * @return  bool  True if everything was read properly
	 */
	public function load_configuration($profile_id = null, $reset = true)
	{
		// Load the configuration
		parent::load_configuration($profile_id, $reset);

		// If there is no embedded installer or the wrong embedded installer is selected, fix it automatically
		$config             = Factory::getConfiguration();
		$embedded_installer = $config->get('akeeba.advanced.embedded_installer', null);

		if (empty($embedded_installer) || ($embedded_installer == 'angie-joomla'))
		{
			$protectedKeys = $config->getProtectedKeys();
			$config->setProtectedKeys([]);
			$config->set('akeeba.advanced.embedded_installer', 'angie');
			$config->setProtectedKeys($protectedKeys);
		}

		return true;
	}

	/**
	 * Saves the current configuration to the database table
	 *
	 * @param   int  $profile_id  The profile where to save the configuration to, defaults to current profile
	 *
	 * @return  bool  True if everything was saved properly
	 */
	public function save_configuration($profile_id = null)
	{
		// If there is no embedded installer or the wrong embedded installer is selected, fix it automatically
		$config             = Factory::getConfiguration();
		$embedded_installer = $config->get('akeeba.advanced.embedded_installer', null);

		if (empty($embedded_installer) || ($embedded_installer == 'angie-joomla'))
		{
			$protectedKeys = $config->getProtectedKeys();
			$config->setProtectedKeys([]);
			$config->set('akeeba.advanced.embedded_installer', 'angie');
			$config->setProtectedKeys($protectedKeys);
		}

		// Save the configuration
		return parent::save_configuration($profile_id);
	}

	/**
	 * Performs heuristics to determine if this platform object is the ideal
	 * candidate for the environment Akeeba Engine is running in.
	 *
	 * @return bool
	 */
	public function isThisPlatform()
	{
		// Make sure _JEXEC is defined
		if (!defined('_JEXEC'))
		{
			return false;
		}

		// We need JVERSION to be defined
		if (!defined('JVERSION'))
		{
			return false;
		}

		// Check if the Joomla Factory class exists
		if (!class_exists('JFactory') && !class_exists('Joomla\CMS\Factory'))
		{
			return false;
		}

		// Check if a valid application class exists
		$appExists = class_exists('Joomla\CMS\Application\CMSApplication')
			|| class_exists('Joomla\CMS\Application\CliApplication')
			|| class_exists('FOFApplicationCLI');

		if (!$appExists)
		{
			return false;
		}

		return true;
	}

	/**
	 * Returns an associative array of stock platform directories
	 *
	 * @return array
	 */
	public function get_stock_directories()
	{
		static $stock_directories = [];

		if (empty($stock_directories))
		{
			$jreg                                  = $this->container->platform->getConfig();
			$tmpdir                                = $jreg->get('tmp_path');
			$stock_directories['[SITEROOT]']       = $this->get_site_root();
			$stock_directories['[ROOTPARENT]']     = @realpath($this->get_site_root() . '/..');
			$stock_directories['[SITETMP]']        = $tmpdir;
			$stock_directories['[DEFAULT_OUTPUT]'] = $this->get_site_root() . '/administrator/components/com_akeeba/backup';
		}

		return $stock_directories;
	}

	/**
	 * Returns the absolute path to the site's root
	 *
	 * @return string
	 */
	public function get_site_root()
	{
		static $root = null;

		if (empty($root) || is_null($root))
		{
			$root = JPATH_ROOT;

			if (empty($root) || ($root == DIRECTORY_SEPARATOR) || ($root == '/'))
			{
				// Try to get the current root in a different way
				if (function_exists('getcwd'))
				{
					$root = getcwd();
				}

				if ($this->container->platform->isBackend())
				{
					if (empty($root))
					{
						$root = '../';
					}
					else
					{
						$adminPos = strpos($root, 'administrator');
						if ($adminPos !== false)
						{
							$root = substr($root, 0, $adminPos);
						}
						else
						{
							$root = '../';
						}

						// Degenerate case where $root = 'administrator'
						// without a leading slash before entering this
						// if-block
						if (empty($root))
						{
							$root = '../';
						}
					}
				}
				else
				{
					if (empty($root) || ($root == DIRECTORY_SEPARATOR) || ($root == '/'))
					{
						$root = './';
					}
				}
			}

			if (!in_array(substr($root, -1), ['/', '\\']))
			{
				$root .= DIRECTORY_SEPARATOR;
			}
		}

		return $root;
	}

	/**
	 * Returns the absolute path to the installer images directory
	 *
	 * @return string
	 */
	public function get_installer_images_path()
	{
		return JPATH_ADMINISTRATOR . '/components/com_akeeba/Master/Installers';
	}

	/**
	 * Returns the active profile number
	 *
	 * @return int
	 */
	public function get_active_profile()
	{
		// Automated testing override
		if (!is_null(self::$profile_id) && (self::$profile_id > 0))
		{
			return self::$profile_id;
		}
		// Constant override
		elseif (defined('AKEEBA_PROFILE'))
		{
			return AKEEBA_PROFILE;
		}
		// Use the session. If it's a CLI app always default to profile #1 (unless explicitly set otherwise)
		else
		{
			$defaultProfile = $this->container->platform->isCli() ? 1 : null;

			return $this->container->platform->getSessionVar('profile', $defaultProfile, 'akeeba');
		}
	}

	/**
	 * Returns the selected profile's name. If no ID is specified, the current
	 * profile's name is returned.
	 *
	 * @return string
	 */
	public function get_profile_name($id = null)
	{
		if (empty($id))
		{
			$id = $this->get_active_profile();
		}
		$id = (int) $id;

		$db  = Factory::getDatabase($this->get_platform_database_options());
		$sql = $db->getQuery(true)
			->select($db->qn('description'))
			->from($db->qn('#__ak_profiles'))
			->where($db->qn('id') . ' = ' . $db->q($id));
		$db->setQuery($sql);

		return $db->loadResult();
	}

	/**
	 * Returns the backup origin
	 *
	 * @return string Backup origin: backend|frontend
	 */
	public function get_backup_origin()
	{
		if (defined('AKEEBA_BACKUP_ORIGIN'))
		{
			return AKEEBA_BACKUP_ORIGIN;
		}

		if ($this->container->platform->isBackend())
		{
			return 'backend';
		}

		if ($this->container->platform->isFrontend())
		{
			return 'frontend';
		}

		return 'cli';
	}

	/**
	 * Returns a MySQL-formatted timestamp out of the current date
	 *
	 * @param   string  $date  [optional] The timestamp to use. Omit to use current timestamp.
	 *
	 * @return string
	 */
	public function get_timestamp_database($date = 'now')
	{
		$date = new Date($date);

		if (method_exists($date, 'toSql'))
		{
			return $date->toSql();
		}

		if (method_exists($date, 'toMySQL'))
		{
			return $date->toMySQL();
		}


		return '0000-00-00 00:00:00';
	}

	/**
	 * Returns the current timestamp, taking into account any TZ information,
	 * in the format specified by $format.
	 *
	 * @param   string  $format  Timestamp format string (standard PHP format string)
	 *
	 * @return string
	 */
	public function get_local_timestamp($format)
	{
		// Do I have a forced timezone?
		$tz = $this->get_platform_configuration_option('forced_backup_timezone', 'AKEEBA/DEFAULT');

		// No forced timezone set? Use the default Joomla! behavior.
		if (empty($tz) || ($tz == 'AKEEBA/DEFAULT'))
		{
			$tz = $this->getJoomlaTimezone();
		}

		$utcTimeZone = new DateTimeZone('UTC');
		$dateNow     = new Date('now', $utcTimeZone);
		$timezone    = new DateTimeZone($tz);
		$dateNow->setTimezone($timezone);

		return $dateNow->format($format, true);
	}

	/**
	 * Returns the current host name
	 *
	 * @return string
	 */
	public function get_host()
	{
		if ($this->container->platform->isCli())
		{
			$url  = Platform::getInstance()->get_platform_configuration_option('siteurl', '');
			$oURI = new Uri($url);
		}
		else
		{
			// Running under the web server
			$oURI = Uri::getInstance();
		}

		return $oURI->getHost();
	}

	public function get_site_name()
	{
		$jconfig = $this->container->platform->getConfig();

		return $jconfig->get('sitename', '');
	}

	/**
	 * Gets the best matching database driver class, according to CMS settings
	 *
	 * @param   bool  $use_platform  If set to false, it will forcibly try to assign one of the primitive type
	 *                               (Mysql/Mysqli) and NEVER tell you to use a platform driver.
	 *
	 * @return string
	 */
	public function get_default_database_driver($use_platform = true)
	{
		$jconfig = $this->container->platform->getConfig();
		$driver  = $jconfig->get('dbtype');
		$driver  = strtolower($driver);

		$hasPdo    = class_exists('\PDO');
		$hasMySQL  = function_exists('mysql_connect');
		$hasMySQLi = function_exists('mysqli_connect');

		// Prime with a default return value, favoring PDO MySQL if available
		$defaultDriver = Pdomysql::class;

		if (!$hasPdo)
		{
			// Second best choice is MySQLi
			$defaultDriver = Mysqli::class;

			// Third best choice is MySQL
			if (!$hasMySQLi && $hasMySQL)
			{
				$defaultDriver = Mysql::class;
			}
		}

		// Let's see what driver Joomla! uses...
		if ($use_platform)
		{
			$hasNookuContent = file_exists(JPATH_ROOT . '/plugins/system/nooku.php');

			switch ($driver)
			{
				// MySQL or MySQLi drivers are known to be working; use their
				// Akeeba Engine extended version, Akeeba\Engine\Driver\Joomla
				case 'mysql':
					// So, Joomla! 4's "mysql" is, actually, "pdomysql". Therefore I can use our own wrapper driver
					if (version_compare(JVERSION, '3.99999.99999', 'gt'))
					{
						return Joomla::class;
					}

					// The piece of crap called FaLang is lying about the database driver
					if (!$hasMySQL)
					{
						return Mysqli::class;
					}

					if ($hasNookuContent)
					{
						return Mysql::class;
					}

					return Joomla::class;

					break;

				case 'mysqli':
					if ($hasNookuContent)
					{
						return Mysqli::class;
					}

					return Joomla::class;

					break;

				// Any other case, use our platform-specific driver
				default:
					return Joomla::class;

					break;
			}
		}

		// Is this a subcase of mysqli or mysql drivers?
		if (substr($driver, 0, 8) == 'pdomysql')
		{
			return Pdomysql::class;
		}
		elseif (substr($driver, 0, 6) == 'mysqli')
		{
			return Mysqli::class;
		}
		elseif (substr($driver, 0, 5) == 'mysql')
		{
			// The piece of crap called FaLang is lying about the database driver
			if (!$hasMySQL)
			{
				return Mysqli::class;
			}

			return Mysql::class;
		}

		// Sometimes we get driver names in the form of foomysql instead of mysqlfoo. Let's look for that too.
		if (substr($driver, -8) == 'pdomysql')
		{
			return Pdomysql::class;
		}
		elseif (substr($driver, -6) == 'mysqli')
		{
			return Mysqli::class;
		}
		elseif (substr($driver, -5) == 'mysql')
		{
			/**
			 * Apparently there are some folks of dubious intelligence out there writing custom database drivers without
			 * understanding or caring about the differences between mysql and mysqli drivers in PHP. They don't play
			 * nice but I have my way to work around their ignorance, FORCING mysqli when they erroneously report mysql
			 * on servers which no longer support this ancient, obsolete database connector. Of course the proper way
			 * to address this would be having these folks fix their broken software but I think I'm asking for too
			 * much. They know who they are, fa la la...
			 */
			if (!$hasMySQL)
			{
				return Mysqli::class;
			}

			return Mysql::class;
		}

		// I give up! You'd better be usign a MySQL db server.
		return $defaultDriver;
	}

	/**
	 * Returns a set of options to connect to the default database of the current CMS
	 *
	 * @return array
	 */
	public function get_platform_database_options()
	{
		static $options;

		if (empty($options))
		{
			$conf    = $this->container->platform->getConfig();
			$options = [
				'host'     => $conf->get('host'),
				'user'     => $conf->get('user'),
				'password' => $conf->get('password'),
				'database' => $conf->get('db'),
				'prefix'   => $conf->get('dbprefix'),
			];
		}

		return $options;
	}

	/**
	 * Provides a platform-specific translation function
	 *
	 * @param   string  $key  The translation key
	 *
	 * @return string
	 */
	public function translate($key)
	{
		return Text::_($key);
	}

	/**
	 * Populates global constants holding the Akeeba version
	 */
	public function load_version_defines()
	{
		$basePath = JPATH_ADMINISTRATOR . '/components/com_akeeba';

		if (file_exists($basePath . '/version.php'))
		{
			require_once($basePath . '/version.php');
		}

		if (!defined('AKEEBA_VERSION'))
		{
			define("AKEEBA_VERSION", "dev");
		}
		if (!defined('AKEEBA_PRO'))
		{
			define('AKEEBA_PRO', false);
		}
		if (!defined('AKEEBA_DATE'))
		{
			$date = new Date();

			define("AKEEBA_DATE", $date->format('Y-m-d'));
		}
	}

	/**
	 * Returns the platform name and version
	 *
	 * @param   string  $platform_name  Name of the platform, e.g. Joomla!
	 * @param   string  $version        Full version of the platform
	 */
	public function getPlatformVersion()
	{
		$v = new Version();

		return [
			'name'    => 'Joomla!',
			'version' => $v->getShortVersion(),
		];
	}

	/**
	 * Logs platform-specific directories with LogLevel::INFO log level
	 */
	public function log_platform_special_directories()
	{
		$ret = [];

		Factory::getLog()->log(LogLevel::INFO, "JPATH_BASE         :" . JPATH_BASE, ['translate_root' => false]);
		Factory::getLog()->log(LogLevel::INFO, "JPATH_SITE         :" . JPATH_SITE, ['translate_root' => false]);
		Factory::getLog()->log(LogLevel::INFO, "JPATH_ROOT         :" . JPATH_ROOT, ['translate_root' => false]);
		Factory::getLog()->log(LogLevel::INFO, "JPATH_CACHE        :" . JPATH_CACHE, ['translate_root' => false]);
		Factory::getLog()->log(LogLevel::INFO, "Computed <root>    :" . $this->get_site_root(), ['translate_root' => false]);

		// If the release is older than 3 months, issue a warning
		if (defined('AKEEBA_DATE'))
		{
			$releaseDate = new Date(AKEEBA_DATE);

			if (time() - $releaseDate->toUnix() > 10368000)
			{
				if (!isset($ret['warnings']))
				{
					$ret['warnings'] = [];
					$ret['warnings'] = array_merge($ret['warnings'], [
						'Your version of Akeeba Backup is more than 120 days old and most likely already out of date. Please check if a newer version is published and install it.',
					]);
				}
			}

		}

		// Detect UNC paths and warn the user
		if (DIRECTORY_SEPARATOR == '\\')
		{
			if ((substr(JPATH_ROOT, 0, 2) == '\\\\') || (substr(JPATH_ROOT, 0, 2) == '//'))
			{
				if (!isset($ret['warnings']))
				{
					$ret['warnings'] = [];
				}

				$ret['warnings'] = array_merge($ret['warnings'], [
					'Your site\'s root is using a UNC path (e.g. \\\\SERVER\\path\\to\\root). PHP has known bugs which may',
					'prevent it from working properly on a site like this. Please take a look at',
					'https://bugs.php.net/bug.php?id=40163 and https://bugs.php.net/bug.php?id=52376. As a result your',
					'backup may fail.',
				]);
			}
		}

		if (empty($ret))
		{
			$ret = null;
		}

		return $ret;
	}

	/**
	 * Loads a platform-specific software configuration option
	 *
	 * @param   string  $key
	 * @param   mixed   $default
	 *
	 * @return mixed
	 */
	public function get_platform_configuration_option($key, $default)
	{
		$value = $this->container->params->get($key, $default);

		// Some configuration options may have to be decrypted
		switch ($key)
		{
			case 'frontend_secret_word':
				$secureSettings = Factory::getSecureSettings();
				$value          = $secureSettings->decryptSettings($value);
				break;
		}

		return $value;
	}

	/**
	 * Returns a list of emails to the Super Administrators
	 *
	 * @return  array
	 */
	public function get_administrator_emails()
	{
		$options = $this->get_platform_database_options();
		$db      = Factory::getDatabase($options);

		// Get all usergroups with Super User access
		$q      = $db->getQuery(true)
			->select([$db->qn('id')])
			->from($db->qn('#__usergroups'));
		$groups = $db->setQuery($q)->loadColumn();

		// Get the groups that are Super Users
		$groups = array_filter($groups, function ($gid) {
			return Access::checkGroup($gid, 'core.admin');
		});

		$mails = [];

		foreach ($groups as $gid)
		{
			$uids = Access::getUsersByGroup($gid);
			array_walk($uids, function ($uid, $index) use (&$mails) {
				$mails[] = $this->container->platform->getUser($uid)->email;
			});
		}

		return array_unique($mails);
	}

	/**
	 * Sends a very simple email using the platform's mailer facility
	 *
	 * @param   string  $to          The recipient's email address
	 * @param   string  $subject     The subject of the email
	 * @param   string  $body        The body of the email
	 * @param   string  $attachFile  The file to attach (null to not attach any files)
	 *
	 * @return  boolean
	 */
	public function send_email($to, $subject, $body, $attachFile = null)
	{
		Factory::getLog()->log(LogLevel::DEBUG, "-- Fetching mailer object");

		/** @var JMail $mailer */
		try
		{
			$mailer = Platform::getInstance()->getMailer();
		}
		catch (Exception $e)
		{
			$mailer = null;
		}

		if (!is_object($mailer))
		{
			Factory::getLog()->log(LogLevel::WARNING, "Could not send email to $to - Joomla! cannot send e-mails. Please check your From EMail and From Name fields in Global Configuration.");

			return false;
		}

		Factory::getLog()->log(LogLevel::DEBUG, "-- Creating email message");

		try
		{
			$recipient = [$to];

			$mailer->addRecipient($recipient);
			$mailer->setSubject($subject);
			$mailer->setBody($body);
		}
		catch (Exception $e)
		{
			Factory::getLog()->log(LogLevel::WARNING, "Could not send email to $to - Problem setting up the email. Joomla! reports error: " . $e->getMessage());

			return false;
		}

		try
		{
			if (!empty($attachFile))
			{
				Factory::getLog()->log(LogLevel::INFO, "-- Attaching $attachFile");

				if (!file_exists($attachFile) || !(is_file($attachFile) || is_link($attachFile)))
				{
					Factory::getLog()->log(LogLevel::WARNING, "The file does not exist, or it's not a file; no email sent");

					return false;
				}

				if (!is_readable($attachFile))
				{
					Factory::getLog()->log(LogLevel::WARNING, "The file is not readable; no email sent");

					return false;
				}

				$filesize = @filesize($attachFile);

				if ($filesize)
				{
					// Check that we have AT LEAST 2.5 times free RAM as the filesize (that's how much we'll need)
					if (!function_exists('ini_get'))
					{
						// Assume 8Mb of PHP memory limit (worst case scenario)
						$totalRAM = 8388608;
					}
					else
					{
						$totalRAM = ini_get('memory_limit');
						if (strstr($totalRAM, 'M'))
						{
							$totalRAM = (int) $totalRAM * 1048576;
						}
						elseif (strstr($totalRAM, 'K'))
						{
							$totalRAM = (int) $totalRAM * 1024;
						}
						elseif (strstr($totalRAM, 'G'))
						{
							$totalRAM = (int) $totalRAM * 1073741824;
						}
						else
						{
							$totalRAM = (int) $totalRAM;
						}
						if ($totalRAM <= 0)
						{
							// No memory limit? Cool! Assume 1Gb of available RAM (which is absurdely abundant as of March 2011...)
							$totalRAM = 1086373952;
						}
					}
					if (!function_exists('memory_get_usage'))
					{
						$usedRAM = 8388608;
					}
					else
					{
						$usedRAM = memory_get_usage();
					}

					$availableRAM = $totalRAM - $usedRAM;

					if ($availableRAM < 2.5 * $filesize)
					{
						Factory::getLog()->log(LogLevel::WARNING, "The file is too big to be sent by email. Please use a smaller Part Size for Split Archives setting.");
						Factory::getLog()->log(LogLevel::DEBUG, "Memory limit $totalRAM bytes -- Used memory $usedRAM bytes -- File size $filesize -- Attachment requires approx. " . (2.5 * $filesize) . " bytes");

						return false;
					}
				}
				else
				{
					Factory::getLog()->log(LogLevel::WARNING, "Your server fails to report the file size of $attachFile. If the backup crashes, please use a smaller Part Size for Split Archives setting");
				}

				$mailer->addAttachment($attachFile);
			}
		}
		catch (Exception $e)
		{
			Factory::getLog()->log(LogLevel::WARNING, "Could not send email to $to - Problem attaching file. Joomla! reports error: " . $e->getMessage());

			return false;
		}

		Factory::getLog()->log(LogLevel::DEBUG, "-- Sending message");

		try
		{
			$result = $mailer->Send();
		}
		catch (Exception $e)
		{
			$result = $e;
		}

		if ($result instanceof Exception)
		{
			Factory::getLog()->log(LogLevel::WARNING, "Could not email $to:");
			Factory::getLog()->log(LogLevel::WARNING, $result->getMessage());
			$ret = $result->getMessage();
			unset($result);
			unset($mailer);

			return $ret;
		}

		Factory::getLog()->log(LogLevel::DEBUG, "-- Email sent");

		return true;
	}

	/**
	 * Deletes a file from the local server using direct file access or FTP
	 *
	 * @param   string  $file
	 *
	 * @return bool
	 */
	public function unlink($file)
	{
		if (function_exists('jimport'))
		{
			$result = File::delete($file);

			if (!$result)
			{
				$result = @unlink($file);
			}
		}
		else
		{
			$result = parent::unlink($file);
		}

		return $result;
	}

	/**
	 * Moves a file around within the local server using direct file access or FTP
	 *
	 * @param   string  $from
	 * @param   string  $to
	 *
	 * @return bool
	 */
	public function move($from, $to)
	{
		if (function_exists('jimport'))
		{
			$result = File::move($from, $to);

			// JFile failed. Let's try rename()
			if (!$result)
			{
				$result = @rename($from, $to);
			}
			// Rename failed, too. Let's try copy/delete
			if (!$result)
			{
				// Try copying with JFile. If it fails, use copy().
				$result = File::copy($from, $to);
				if (!$result)
				{
					$result = @copy($from, $to);
				}

				// If the copy succeeded, try deleting the original with JFile. If it fails, use unlink().
				if ($result)
				{
					$result = $this->unlink($from);
				}
			}
		}
		else
		{
			$result = parent::move($from, $to);
		}

		return $result;
	}

	/**
	 * Joomla!-specific function to get an instance of the mailer class
	 *
	 * @return Mail
	 */
	public function &getMailer()
	{
		$mailer = \Joomla\CMS\Factory::getMailer();
		if (!is_object($mailer))
		{
			Factory::getLog()->log(LogLevel::WARNING, "Fetching Joomla!'s mailer was impossible; imminent crash!");
		}
		else
		{
			$emailMethod = $mailer->Mailer;
			Factory::getLog()->log(LogLevel::DEBUG, "-- Joomla!'s mailer is using $emailMethod mail method.");
		}

		return $mailer;
	}

	/**
	 * Stores a flash (temporary) variable in the session.
	 *
	 * @param   string  $name   The name of the variable to store
	 * @param   string  $value  The value of the variable to store
	 *
	 * @return  void
	 */
	public function set_flash_variable($name, $value)
	{
		if ($this->container->platform->isCli())
		{
			$this->flashVariables[$name] = $value;

			return;
		}

		$this->container->platform->setSessionVar($name, $value, 'akeebabackup');
	}

	/**
	 * Return the value of a flash (temporary) variable from the session and
	 * immediately removes it.
	 *
	 * @param   string  $name     The name of the flash variable
	 * @param   mixed   $default  Default value, if the variable is not defined
	 *
	 * @return  mixed  The value of the variable or $default if it's not set
	 */
	public function get_flash_variable($name, $default = null)
	{
		if ($this->container->platform->isCli())
		{
			$ret = $default;

			if (isset($this->flashVariables[$name]))
			{
				$ret = $this->flashVariables[$name];
				unset($this->flashVariables[$name]);
			}

			return $ret;
		}

		$ret = $this->container->platform->getSessionVar($name, $default, 'akeebabackup');
		$this->container->platform->setSessionVar($name, null, 'akeebabackup');

		return $ret;
	}

	/**
	 * Perform an immediate redirection to the defined URL
	 *
	 * @param   string  $url  The URL to redirect to
	 *
	 * @return  void
	 */
	public function redirect($url)
	{
		$this->container->platform->redirect($url);
	}

	public function apply_quirk_definitions()
	{
		Factory::getConfigurationChecks()->addConfigurationCheckDefinition('013', 'critical', 'COM_AKEEBA_CPANEL_WARNING_Q013', [
			Joomla3x::class, 'quirk_013',
		]);
	}

	/**
	 * Registers Akeeba Engine's core classes with JLoader
	 *
	 * @param   string  $path_prefix  The path prefix to look in
	 */
	protected function register_akeeba_engine_classes($path_prefix)
	{
		global $Akeeba_Class_Map;

		foreach ($Akeeba_Class_Map as $class_prefix => $path_suffix)
		{
			// Bail out if there is such directory, so as not to have Joomla! throw errors
			if (!@is_dir($path_prefix . '/' . $path_suffix))
			{
				continue;
			}

			$file_list = Folder::files($path_prefix . '/' . $path_suffix, '.*\.php');
			if (is_array($file_list) && !empty($file_list))
			{
				foreach ($file_list as $file)
				{
					$class_suffix = ucfirst(basename($file, '.php'));
					JLoader::register($class_prefix . $class_suffix, $path_prefix . '/' . $path_suffix . '/' . $file);
				}
			}
		}
	}

	/**
	 * Get the applicable timezone in the same way Joomla! calculates it: if there is a logged in
	 * user with a specific timezone set, use it. Otherwise use the Server Timezone defined in the
	 * site's Global Configuration. If nothing is set there, use GMT instead.
	 *
	 * @return  string
	 */
	private function getJoomlaTimezone()
	{
		// Out ultimate default is the server timezone set up in the Global Configuration
		$jregistry = $this->container->platform->getConfig();
		$tz        = $jregistry->get('offset', 'GMT');

		// If this is a CLI script, tough luck, we can't use a different TZ
		if ($this->container->platform->isCli())
		{
			return $tz;
		}

		// If it's a guest user they can't have a special TZ set, return.
		$user = $this->container->platform->getUser();

		if ($user->guest)
		{
			return $tz;
		}

		$tz = $user->getParam('timezone', $tz);

		return $tz;
	}
}
