<?php
/**
 * Akeeba Engine
 *
 * @package   akeebaengine
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Engine\Core\Domain;

defined('AKEEBAENGINE') || die();

use Akeeba\Engine\Base\Part;
use Akeeba\Engine\Factory;
use Akeeba\Engine\Platform;
use RuntimeException;

/**
 * Backup initialization domain
 */
class Init extends Part
{
	/** @var   string  The backup description */
	private $description = '';

	/** @var   string  The backup comment */
	private $comment = '';

	/**
	 * Implements the constructor of the class
	 *
	 * @return  void
	 */
	public function __construct()
	{
		parent::__construct();

		Factory::getLog()->debug(__CLASS__ . " :: New instance");
	}

	/**
	 * Converts a PHP error to a string
	 *
	 * @return  string
	 */
	public static function error2string()
	{
		if (!function_exists('error_reporting'))
		{
			return "Not applicable; host too restrictive";
		}

		$value       = error_reporting();
		$level_names = [
			E_ERROR         => 'E_ERROR', E_WARNING => 'E_WARNING',
			E_PARSE         => 'E_PARSE', E_NOTICE => 'E_NOTICE',
			E_CORE_ERROR    => 'E_CORE_ERROR', E_CORE_WARNING => 'E_CORE_WARNING',
			E_COMPILE_ERROR => 'E_COMPILE_ERROR', E_COMPILE_WARNING => 'E_COMPILE_WARNING',
			E_USER_ERROR    => 'E_USER_ERROR', E_USER_WARNING => 'E_USER_WARNING',
			E_USER_NOTICE   => 'E_USER_NOTICE',
		];

		if (defined('E_STRICT'))
		{
			$level_names[E_STRICT] = 'E_STRICT';
		}

		$levels = [];

		if (($value & E_ALL) == E_ALL)
		{
			$levels[] = 'E_ALL';
			$value    &= ~E_ALL;
		}

		foreach ($level_names as $level => $name)
		{
			if (($value & $level) == $level)
			{
				$levels[] = $name;
			}
		}

		return implode(' | ', $levels);
	}

	/**
	 * Reports whether the error display (output to HTML) is enabled or not
	 *
	 * @return string
	 */
	public static function errordisplay()
	{
		if (!function_exists('ini_get'))
		{
			return "Not applicable; host too restrictive";
		}

		return ini_get('display_errors') ? 'on' : 'off';
	}

	/**
	 * Implements the _prepare abstract method
	 *
	 * @return  void
	 */
	protected function _prepare()
	{
		// Load parameters (description and comment)
		$jpskey   = '';
		$angiekey = '';

		if (!empty($this->_parametersArray))
		{
			$params = $this->_parametersArray;

			if (isset($params['description']))
			{
				$this->description = $params['description'];
			}

			if (isset($params['comment']))
			{
				$this->comment = $params['comment'];
			}

			if (isset($params['jpskey']))
			{
				$jpskey = $params['jpskey'];
			}

			if (isset($params['angiekey']))
			{
				$angiekey = $params['angiekey'];
			}
		}

		// Load configuration -- No. This is already done by the model. Doing it again removes all overrides.
		// Platform::getInstance()->load_configuration();

		// Initialize counters
		$registry = Factory::getConfiguration();

		if (!empty($jpskey))
		{
			$registry->set('engine.archiver.jps.key', $jpskey);
		}

		if (!empty($angiekey))
		{
			$registry->set('engine.installer.angie.key', $angiekey);
		}

		// Initialize temporary storage
		Factory::getFactoryStorage()->reset();

		// Force load the tag -- do not delete!
		$kettenrad = Factory::getKettenrad();
		$tag       = $kettenrad->getTag(); // Yes, this is an unused variable by we MUST run this method. DO NOT DELETE.

		// Push the comment and description in temp vars for use in the installer phase
		$registry->set('volatile.core.description', $this->description);
		$registry->set('volatile.core.comment', $this->comment);

		$this->setState(self::STATE_PREPARED);
	}

	/**
	 * Implements the _run() abstract method
	 *
	 * @return  void
	 */
	protected function _run()
	{
		if ($this->getState() == self::STATE_POSTRUN)
		{
			Factory::getLog()->debug(__CLASS__ . " :: Already finished");
			$this->setStep('');
			$this->setSubstep('');

			return;
		}
		else
		{
			$this->setState(self::STATE_RUNNING);
		}

		// Initialise the extra notes variable, used by platform classes to return warnings and errors
		$extraNotes = null;

		// Load the version defines
		Platform::getInstance()->load_version_defines();

		$registry = Factory::getConfiguration();

		// Write log file's header
		$version = defined('AKEEBABACKUP_VERSION') ? AKEEBABACKUP_VERSION : AKEEBA_VERSION;
		$date    = defined('AKEEBABACKUP_DATE') ? AKEEBABACKUP_DATE : AKEEBA_DATE;

		Factory::getLog()->info("--------------------------------------------------------------------------------");
		Factory::getLog()->info("Akeeba Backup " . $version . ' (' . $date . ')');
		Factory::getLog()->info("--------------------------------------------------------------------------------");

		// PHP configuration variables are tried to be logged only for debug and info log levels
		if ($registry->get('akeeba.basic.log_level') >= 2)
		{
			Factory::getLog()->info("--- System Information ---");
			Factory::getLog()->info("PHP Version        :" . PHP_VERSION);
			Factory::getLog()->info("PHP OS             :" . PHP_OS);
			Factory::getLog()->info("PHP SAPI           :" . PHP_SAPI);

			if (function_exists('php_uname'))
			{
				Factory::getLog()->info("OS Version         :" . php_uname('s'));
			}

			$db = Factory::getDatabase();
			Factory::getLog()->info("DB Version         :" . $db->getVersion());

			if (isset($_SERVER['SERVER_SOFTWARE']))
			{
				$server = $_SERVER['SERVER_SOFTWARE'];
			}
			elseif (($sf = getenv('SERVER_SOFTWARE')))
			{
				$server = $sf;
			}
			else
			{
				$server = 'n/a';
			}

			Factory::getLog()->info("Web Server         :" . $server);

			$platform     = 'Unknown platform';
			$version      = '(unknown version)';
			$platformData = Platform::getInstance()->getPlatformVersion();
			Factory::getLog()->info($platformData['name'] . " version    :" . $platformData['version']);

			if (isset($_SERVER['HTTP_USER_AGENT']))
			{
				Factory::getLog()->info("User agent         :" . $_SERVER['HTTP_USER_AGENT']);
			}

			Factory::getLog()->info("Safe mode          :" . ini_get("safe_mode"));
			Factory::getLog()->info("Display errors     :" . ini_get("display_errors"));
			Factory::getLog()->info("Error reporting    :" . self::error2string());
			Factory::getLog()->info("Error display      :" . self::errordisplay());
			Factory::getLog()->info("Disabled functions :" . ini_get("disable_functions"));
			Factory::getLog()->info("open_basedir restr.:" . ini_get('open_basedir'));
			Factory::getLog()->info("Max. exec. time    :" . ini_get("max_execution_time"));
			Factory::getLog()->info("Memory limit       :" . ini_get("memory_limit"));

			if (function_exists("memory_get_usage"))
			{
				Factory::getLog()->info("Current mem. usage :" . memory_get_usage());
			}

			if (function_exists("gzcompress"))
			{
				Factory::getLog()->info("GZIP Compression   : available (good)");
			}
			else
			{
				Factory::getLog()->info("GZIP Compression   : n/a (no compression)");
			}

			$extraNotes = Platform::getInstance()->log_platform_special_directories();

			if (!empty($extraNotes) && is_array($extraNotes))
			{
				if (isset($extraNotes['warnings']) && is_array($extraNotes['warnings']))
				{
					foreach ($extraNotes['warnings'] as $warning)
					{
						Factory::getLog()->warning($warning);
					}
				}

				if (isset($extraNotes['errors']) && is_array($extraNotes['errors']))
				{
					foreach ($extraNotes['errors'] as $error)
					{
						Factory::getLog()->error($error);
					}

					if (!empty($extraNotes['errors']))
					{
						throw new RuntimeException($extraNotes['errors'][0]);
					}
				}
			}

			$min_time = $registry->get('akeeba.tuning.min_exec_time');
			$max_time = $registry->get('akeeba.tuning.max_exec_time');
			$bias     = $registry->get('akeeba.tuning.run_time_bias');

			Factory::getLog()->info("Min/Max/Bias       :" . $min_time . '/' . $max_time . '/' . $bias);
			Factory::getLog()->info("Output directory   :" . $registry->get('akeeba.basic.output_directory'), ['root_translate' => false]);
			Factory::getLog()->info("Part size (bytes)  :" . $registry->get('engine.archiver.common.part_size', 0));
			Factory::getLog()->info("--------------------------------------------------------------------------------");
		}

		// Quirks reporting
		$quirks = Factory::getConfigurationChecks()->getDetailedStatus(true);

		if (!empty($quirks))
		{
			Factory::getLog()->info("Akeeba Backup has detected the following potential problems:");

			foreach ($quirks as $q)
			{
				Factory::getLog()->info('- ' . $q['code'] . ' ' . $q['description'] . ' (' . $q['severity'] . ')');
			}

			Factory::getLog()->info("You probably do not have to worry about them, but you should be aware of them.");
			Factory::getLog()->info("--------------------------------------------------------------------------------");
		}

		$phpVersion = PHP_VERSION;

		if (version_compare($phpVersion, '7.3.0', 'lt'))
		{
			Factory::getLog()->warning("You are using PHP $phpVersion which is officially End of Life. We recommend using PHP 7.4 or later for best results. Your version of PHP, $phpVersion, will stop being supported by this backup software in the future.");
		}

		// Report profile ID
		$profile_id = Platform::getInstance()->get_active_profile();
		Factory::getLog()->info("Loaded profile #$profile_id");

		// Get archive name
		[$relativeArchiveName, $absoluteArchiveName] = $this->getArchiveName();

		// ==== Stats initialisation ===
		$origin     = Platform::getInstance()->get_backup_origin(); // Get backup origin
		$profile_id = Platform::getInstance()->get_active_profile(); // Get active profile

		$registry   = Factory::getConfiguration();
		$backupType = $registry->get('akeeba.basic.backup_type');
		Factory::getLog()->debug("Backup type is now set to '" . $backupType . "'");

		// Substitute "variables" in the archive name
		$fsUtils     = Factory::getFilesystemTools();
		$description = $fsUtils->replace_archive_name_variables($this->description);
		$comment     = $fsUtils->replace_archive_name_variables($this->comment);

		if ($registry->get('volatile.writer.store_on_server', true))
		{
			// Archive files are stored on our server
			$stat_relativeArchiveName = $relativeArchiveName;
			$stat_absoluteArchiveName = $absoluteArchiveName;
		}
		else
		{
			// Archive files are not stored on our server (FTP backup, cloud backup, sent by email, etc)
			$stat_relativeArchiveName = '';
			$stat_absoluteArchiveName = '';
		}

		$kettenrad = Factory::getKettenrad();

		$temp = [
			'description'   => $description,
			'comment'       => $comment,
			'backupstart'   => Platform::getInstance()->get_timestamp_database(),
			'status'        => 'run',
			'origin'        => $origin,
			'type'          => $backupType,
			'profile_id'    => $profile_id,
			'archivename'   => $stat_relativeArchiveName,
			'absolute_path' => $stat_absoluteArchiveName,
			'multipart'     => 0,
			'filesexist'    => 1,
			'tag'           => $kettenrad->getTag(),
			'backupid'      => $kettenrad->getBackupId(),
		];

		// Save the entry
		$statistics = Factory::getStatistics();
		$statistics->setStatistics($temp);
		$statistics->release_multipart_lock();

		// Initialize the archive.
		if (Factory::getEngineParamsProvider()->getScriptingParameter('core.createarchive', true))
		{
			Factory::getLog()->debug("Expanded archive file name: " . $absoluteArchiveName);

			Factory::getLog()->debug("Initializing archiver engine");
			$archiver = Factory::getArchiverEngine();
			$archiver->initialize($absoluteArchiveName);
			$archiver->setComment($comment); // Add the comment to the archive itself.
		}

		$this->setState(self::STATE_POSTRUN);
	}

	/**
	 * Implements the abstract _finalize method
	 *
	 * @return  void
	 */
	protected function _finalize()
	{
		$this->setState(self::STATE_FINISHED);
	}

	/**
	 * Returns the relative and absolute path to the archive
	 */
	protected function getArchiveName()
	{
		$registry = Factory::getConfiguration();

		// Import volatile scripting keys to the registry
		Factory::getEngineParamsProvider()->importScriptingToRegistry();

		// Determine the extension
		$force_extension = Factory::getEngineParamsProvider()->getScriptingParameter('core.forceextension', null);

		if (is_null($force_extension))
		{
			$archiver  = Factory::getArchiverEngine();
			$extension = $archiver->getExtension();
		}
		else
		{
			$extension = $force_extension;
		}

		// Get the template name
		$templateName = $registry->get('akeeba.basic.archive_name');
		Factory::getLog()->debug("Archive template name: $templateName");

		/**
		 * Security: Protect archives in the default backup output directory
		 *
		 * If the configured backup output directory is the same as the default backup output directory the following
		 * actions are taken:
		 *
		 * 1. The backup archive name must include [RANDOM]. If it doesn't, '-[RANDOM]' will be appended to it.
		 * 2. We make sure that the direct web access blocking files .htaccess, web.config, index.html, index.htm and
		 *    index.php exist in that directory. If they do not they will be forcibly added.
		 */
		$configuredOutputPath = $registry->get('akeeba.basic.output_directory');
		$stockDirs            = Platform::getInstance()->get_stock_directories();
		$defaultOutputPath    = $stockDirs['[DEFAULT_OUTPUT]'];
		$fsUtils              = Factory::getFilesystemTools();

		if (@realpath($configuredOutputPath) === @realpath($defaultOutputPath))
		{
			$this->ensureHasRandom($templateName);

			$fsUtils->ensureNoAccess($defaultOutputPath);
		}

		// Parse all tags
		$fsUtils      = Factory::getFilesystemTools();
		$templateName = $fsUtils->replace_archive_name_variables($templateName);

		Factory::getLog()->debug("Expanded template name: $templateName");

		$relative_path = $templateName . $extension;
		$absolute_path = $fsUtils->TranslateWinPath($configuredOutputPath . DIRECTORY_SEPARATOR . $relative_path);

		return [$relative_path, $absolute_path];
	}

	/**
	 * Make sure that the archive template name contains the [RANDOM] variable.
	 *
	 * @param   string  $templateName
	 *
	 * @return void
	 */
	protected function ensureHasRandom(&$templateName)
	{
		if (strpos($templateName, '[RANDOM]') !== false)
		{
			return;
		}

		$templateName .= '-[RANDOM]';
	}
}
