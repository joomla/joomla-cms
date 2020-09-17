<?php
/**
 * Akeeba Engine
 *
 * @package   akeebaengine
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Engine;

defined('AKEEBAENGINE') || die();

use Akeeba\Engine\Base\Part;
use Akeeba\Engine\Core\Database;
use Akeeba\Engine\Core\Filters;
use Akeeba\Engine\Core\Kettenrad;
use Akeeba\Engine\Core\Timer;
use Akeeba\Engine\Driver\Base;
use Akeeba\Engine\Postproc\PostProcInterface;
use Akeeba\Engine\Util\ConfigurationCheck;
use Akeeba\Engine\Util\CRC32;
use Akeeba\Engine\Util\Encrypt;
use Akeeba\Engine\Util\EngineParameters;
use Akeeba\Engine\Util\FactoryStorage;
use Akeeba\Engine\Util\FileLister;
use Akeeba\Engine\Util\FileSystem;
use Akeeba\Engine\Util\Logger;
use Akeeba\Engine\Util\PushMessages;
use Akeeba\Engine\Util\RandomValue;
use Akeeba\Engine\Util\SecureSettings;
use Akeeba\Engine\Util\Statistics;
use Akeeba\Engine\Util\TemporaryFiles;
use Exception;
use RuntimeException;

// Try to kill errors display
if (function_exists('ini_set') && !defined('AKEEBADEBUG'))
{
	ini_set('display_errors', false);
}

// Make sure the class autoloader is loaded
require_once __DIR__ . '/Autoloader.php';

/**
 * The Akeeba Engine Factory class
 *
 * This class is responsible for instantiating all Akeeba Engine classes
 */
abstract class Factory
{
	/**
	 * The absolute path to Akeeba Engine's installation
	 *
	 * @var  string
	 */
	private static $root;

	/**
	 * Partial class names of the loaded engines e.g. 'archiver' => 'Archiver\\Jpa'. Survives serialization.
	 *
	 * @var  array
	 */
	private static $engineClassnames = [];

	/**
	 * A list of instantiated objects which will persist after serialisation / unserialisation
	 *
	 * @var   array
	 */
	private static $objectList = [];

	/**
	 * A list of instantiated objects which will NOT persist after serialisation / unserialisation
	 *
	 * @var   array
	 */
	private static $temporaryObjectList = [];

	/**
	 * Gets a serialized snapshot of the Factory for safekeeping (hibernate)
	 *
	 * @return  string  The serialized snapshot of the Factory
	 */
	public static function serialize()
	{
		// Call _onSerialize in all objects known to the factory
		foreach (static::$objectList as $class_name => $object)
		{
			if (method_exists($object, '_onSerialize'))
			{
				call_user_func([$object, '_onSerialize']);
			}
		}

		// Serialise an array with all the engine information
		$engineInfo = [
			'root'             => static::$root,
			'objectList'       => static::$objectList,
			'engineClassnames' => static::$engineClassnames,
		];

		// Serialize the factory
		return serialize($engineInfo);
	}

	/**
	 * Regenerates the full Factory state from a serialized snapshot (resume)
	 *
	 * @param   string  $serialized_data  The serialized snapshot to resume from
	 *
	 * @return  void
	 */
	public static function unserialize($serialized_data)
	{
		static::nuke();

		$engineInfo = unserialize($serialized_data);

		static::$root                = $engineInfo['root'] ?? '';
		static::$objectList          = $engineInfo['objectList'] ?? [];
		static::$engineClassnames    = $engineInfo['engineClassnames'] ?? [];
		static::$temporaryObjectList = [];
	}

	/**
	 * Reset the internal factory state, freeing all previously created objects
	 *
	 * @return  void
	 */
	public static function nuke()
	{
		foreach (static::$objectList as $key => $object)
		{
			$object = null;
		}

		foreach (static::$temporaryObjectList as $key => $object)
		{
			$object = null;
		}

		static::$objectList          = [];
		static::$temporaryObjectList = [];
	}

	/**
	 * Saves the engine state to temporary storage
	 *
	 * @param   string  $tag       The backup origin to save. Leave empty to get from already loaded Kettenrad instance.
	 * @param   string  $backupId  The backup ID to save. Leave empty to get from already loaded Kettenrad instance.
	 *
	 * @return  void
	 *
	 * @throws  RuntimeException  When the state save fails for any reason
	 */
	public static function saveState($tag = null, $backupId = null)
	{
		$kettenrad = static::getKettenrad();

		if (empty($tag))
		{
			$tag = $kettenrad->getTag();
		}

		if (empty($backupId))
		{
			$backupId = $kettenrad->getBackupId();
		}

		$saveTag = $tag . (empty($backupId) ? '' : ('.' . $backupId));
		$ret     = $kettenrad->getStatusArray();

		if ($ret['HasRun'] == 1)
		{
			Factory::getLog()->debug("Will not save a finished Kettenrad instance");

			return;
		}

		Factory::getLog()->debug("Saving Kettenrad instance $tag");

		// Save a Factory snapshot
		$factoryStorage = static::getFactoryStorage();

		$logger = static::getLog();
		$logger->resetWarnings();

		$serializedFactoryData = static::serialize();
		$result                = $factoryStorage->set($serializedFactoryData, $saveTag);

		if ($result === false)
		{
			$saveKey      = $factoryStorage->get_storage_filename($saveTag);
			$errorMessage = "Cannot save factory state in storage, storage filename $saveKey";
			$logger->error($errorMessage);

			throw new RuntimeException($errorMessage);
		}
	}

	/**
	 * Loads the engine state from the storage (if it exists).
	 *
	 * When failIfMissing is true (default) an exception will be thrown if the memory file / database record is no
	 * longer there. This is a clear indication of an issue with the storage engine, e.g. the host deleting the memory
	 * files in the middle of the backup step. Therefore we'll switch the storage engine type before throwing the
	 * exception.
	 *
	 * When failIfMissing is false we do NOT throw an exception. Instead, we do a hard reset of the backup factory. This
	 * is required by the resetState method when we ask it to reset multiple origins at once.
	 *
	 * @param   string  $tag            The backup origin to load
	 * @param   string  $backupId       The backup ID to load
	 * @param   bool    $failIfMissing  Throw an exception if the memory data is no longer there
	 *
	 * @return  void
	 */
	public static function loadState($tag = null, $backupId = null, $failIfMissing = true)
	{
		if (is_null($tag) && defined('AKEEBA_BACKUP_ORIGIN'))
		{
			$tag = AKEEBA_BACKUP_ORIGIN;
		}

		if (is_null($backupId) && defined('AKEEBA_BACKUP_ID'))
		{
			$tag = AKEEBA_BACKUP_ID;
		}

		$loadTag = $tag . (empty($backupId) ? '' : ('.' . $backupId));

		// In order to load anything, we need to have the correct profile loaded. Let's assume
		// that the latest backup record in this tag has the correct profile number set.
		$config = static::getConfiguration();

		if (empty($config->activeProfile))
		{
			$profile = Platform::getInstance()->get_active_profile();

			if (empty($profile) || ($profile <= 1))
			{
				// Only bother loading a configuration if none has been already loaded
				$statList = Platform::getInstance()->get_statistics_list([
						'filters'  => [
							['field' => 'tag', 'value' => $tag],
						], 'order' => [
							'by' => 'id', 'order' => 'DESC',
						],
					]
				);

				if (is_array($statList))
				{
					$stat    = array_pop($statList);
					$profile = $stat['profile_id'];
				}
			}

			Platform::getInstance()->load_configuration($profile);
		}

		$profile = $config->activeProfile;

		Factory::getLog()->open($loadTag);
		Factory::getLog()->debug("Kettenrad :: Attempting to load from database ($tag) [$loadTag]");

		$serialized_factory = static::getFactoryStorage()->get($loadTag);

		if ($serialized_factory === false)
		{
			if ($failIfMissing)
			{
				throw new RuntimeException("Akeeba Engine detected a problem while saving temporary data. Please restart your backup.", 500);
			}

			// There is no serialized factory. Nuke the in-memory factory.
			Factory::getLog()->debug(" -- Stored Akeeba Factory ($tag) [$loadTag] not found - hard reset");
			static::nuke();
			Platform::getInstance()->load_configuration($profile);
		}

		Factory::getLog()->debug(" -- Loaded stored Akeeba Factory ($tag) [$loadTag]");
		static::unserialize($serialized_factory);

		unset($serialized_factory);
	}

	// ========================================================================
	// Public factory interface
	// ========================================================================

	/**
	 * Resets the engine state, wiping out any pending backups and/or stale temporary data.
	 *
	 * The configuration parameters are:
	 *
	 * global  bool  True to reset all origins, false to only reset the current origin (default: true)
	 * log     bool  True to log our actions (default: false)
	 * maxrun  int   Only backup records older than this number of seconds will be reset (default: 180)
	 *
	 * @param   array  $config  Configuration parameters for the reset operation
	 *
	 * @return  void
	 * @throws Exception
	 */
	public static function resetState($config = [])
	{
		$default_config = [
			'global' => true, // Reset all origins when true
			'log'    => false, // Log our actions
			'maxrun' => 180, // Consider "pending" backups as failed after this many seconds
		];

		$config = (object) array_merge($default_config, $config);

		// Pause logging if so desired
		if (!$config->log)
		{
			Factory::getLog()->pause();
		}

		$originTag = null;

		if (!$config->global)
		{
			// If we're not resetting globally, get a list of running backups per tag
			$originTag = Platform::getInstance()->get_backup_origin();
		}

		// Cache the factory before proceeding
		$factory = static::serialize();

		$runningList = Platform::getInstance()->get_running_backups($originTag);

		// Origins we have to clean
		$origins = [
			Platform::getInstance()->get_backup_origin(),
		];

		// 1. Detect failed backups
		if (is_array($runningList) && !empty($runningList))
		{
			// The current timestamp
			$now = time();

			// Mark running backups as failed
			foreach ($runningList as $running)
			{
				if (empty($originTag))
				{
					// Check the timestamp of the log file to decide if it's stuck,
					// but only if a tag is not set
					$tstamp = Factory::getLog()->getLastTimestamp($running['origin']);

					if (!is_null($tstamp))
					{
						// We can only check the timestamp if it's returned. If not, we assume the backup is stale
						$difference = abs($now - $tstamp);

						// Backups less than maxrun seconds old are not considered stale (default: 3 minutes)
						if ($difference < $config->maxrun)
						{
							continue;
						}
					}
				}

				$filenames = Factory::getStatistics()->get_all_filenames($running, false);
				$totalSize = 0;

				// Process if there are files to delete...
				if (!is_null($filenames))
				{
					// Delete the failed backup's archive, if exists
					foreach ($filenames as $failedArchive)
					{
						if (file_exists($failedArchive))
						{
							$totalSize += (int) @filesize($failedArchive);
							Platform::getInstance()->unlink($failedArchive);
						}
					}
				}

				// Mark the backup failed
				if (!$running['total_size'])
				{
					$running['total_size'] = $totalSize;
				}

				$running['status']    = 'fail';
				$running['multipart'] = 0;

				Platform::getInstance()->set_or_update_statistics($running['id'], $running);

				$backupId = isset($running['backupid']) ? ('.' . $running['backupid']) : '';

				$origins[] = $running['origin'] . $backupId;
			}
		}

		if (!empty($origins))
		{
			$origins = array_unique($origins);

			foreach ($origins as $originTag)
			{
				static::loadState($originTag, null, false);
				// Remove temporary files
				Factory::getTempFiles()->deleteTempFiles();
				// Delete any stale temporary data
				static::getFactoryStorage()->reset($originTag);
			}
		}

		// Reload the factory
		static::unserialize($factory);
		unset($factory);

		// Unpause logging if it was previously paused
		if (!$config->log)
		{
			Factory::getLog()->unpause();
		}
	}

	/**
	 * Returns an Akeeba Configuration object
	 *
	 * @return  Configuration  The Akeeba Configuration object
	 */
	public static function getConfiguration()
	{
		return static::getObjectInstance('Configuration');
	}

	/**
	 * Returns a statistics object, used to track current backup's progress
	 *
	 * @return  Statistics
	 */
	public static function getStatistics()
	{
		return static::getObjectInstance('Util\\Statistics');
	}

	/**
	 * Returns the currently configured archiver engine
	 *
	 * @param   bool  $reset  Should I try to forcible create a new instance?
	 *
	 * @return  Archiver\Base
	 */
	public static function getArchiverEngine($reset = false)
	{
		return static::getEngineInstance(
			'archiver', 'akeeba.advanced.archiver_engine',
			'Archiver\\', 'Archiver\\Jpa',
			$reset
		);
	}

	/**
	 * Returns the currently configured dump engine
	 *
	 * @param   boolean  $reset  Should I try to forcible create a new instance?
	 *
	 * @return  Dump\Base
	 */
	public static function getDumpEngine($reset = false)
	{
		return static::getEngineInstance(
			'dump', 'akeeba.advanced.dump_engine',
			'Dump\\', 'Dump\\Native',
			$reset
		);
	}

	/**
	 * Returns the filesystem scanner engine instance
	 *
	 * @param   bool  $reset  Should I try to forcible create a new instance?
	 *
	 * @return  Scan\Base  The scanner engine
	 */
	public static function getScanEngine($reset = false)
	{
		return static::getEngineInstance(
			'scan', 'akeeba.advanced.scan_engine',
			'Scan\\', 'Scan\\Large',
			$reset
		);
	}

	/**
	 * Returns the current post-processing engine. If no class is specified we
	 * return the post-processing engine configured in akeeba.advanced.postproc_engine
	 *
	 * @param   string  $engine  The name of the post-processing class to forcibly return
	 *
	 * @return  PostProcInterface
	 */
	public static function getPostprocEngine($engine = null)
	{
		if (!is_null($engine))
		{
			static::$engineClassnames['postproc'] = 'Postproc\\' . ucfirst($engine);

			return static::getObjectInstance(static::$engineClassnames['postproc']);
		}

		return static::getEngineInstance(
			'postproc', 'akeeba.advanced.postproc_engine',
			'Postproc\\', 'Postproc\\None',
			true
		);
	}

	// ========================================================================
	// Core objects which are part of the engine state
	// ========================================================================

	/**
	 * Returns an instance of the Filters feature class
	 *
	 * @return  Filters  The Filters feature class' object instance
	 */
	public static function getFilters()
	{
		return static::getObjectInstance('Core\\Filters');
	}

	/**
	 * Returns an instance of the specified filter group class. Do note that it does not
	 * work with platform filter classes. They are handled internally by AECoreFilters.
	 *
	 * @param   string  $filter_name  The filter class to load, without AEFilter prefix
	 *
	 * @return  Filter\Base  The filter class' object instance
	 */
	public static function getFilterObject($filter_name)
	{
		return static::getObjectInstance('Filter\\' . ucfirst($filter_name));
	}

	/**
	 * Loads an engine domain class and returns its associated object
	 *
	 * @param   string  $domain_name  The name of the domain, e.g. installer for AECoreDomainInstaller
	 *
	 * @return  Part
	 */
	public static function getDomainObject($domain_name)
	{
		return static::getObjectInstance('Core\\Domain\\' . ucfirst($domain_name));
	}

	/**
	 * Returns a database connection object. It's an alias of AECoreDatabase::getDatabase()
	 *
	 * @param   array  $options  Options to use when instantiating the database connection
	 *
	 * @return  Base
	 */
	public static function getDatabase($options = null)
	{
		if (is_null($options))
		{
			$options = Platform::getInstance()->get_platform_database_options();
		}

		if (isset($options['username']) && !isset($options['user']))
		{
			$options['user'] = $options['username'];
		}

		return Database::getDatabase($options);
	}

	/**
	 * Returns a database connection object. It's an alias of AECoreDatabase::getDatabase()
	 *
	 * @param   array  $options  Options to use when instantiating the database connection
	 *
	 * @return  void
	 */
	public static function unsetDatabase($options = null)
	{
		if (is_null($options))
		{
			$options = Platform::getInstance()->get_platform_database_options();
		}

		$db = Database::getDatabase($options);
		$db->close();

		Database::unsetDatabase($options);
	}

	/**
	 * Get the a reference to the Akeeba Engine's timer
	 *
	 * @return  Timer
	 */
	public static function getTimer()
	{
		return static::getObjectInstance('Core\\Timer');
	}

	/**
	 * Get a reference to Akeeba Engine's main controller called Kettenrad
	 *
	 * @return  Kettenrad
	 */
	public static function getKettenrad()
	{
		return static::getObjectInstance('Core\\Kettenrad');
	}

	/**
	 * Returns an instance of the factory storage class (formerly Tempvars)
	 *
	 * @return  FactoryStorage
	 */
	public static function getFactoryStorage()
	{
		return static::getTempObjectInstance('Util\\FactoryStorage');
	}

	/**
	 * Returns an instance of the encryption class
	 *
	 * @return  Encrypt
	 */
	public static function getEncryption()
	{
		return static::getTempObjectInstance('Util\\Encrypt');
	}

	/**
	 * Returns an instance of the CRC32 calculations class
	 *
	 * @return  CRC32
	 */
	public static function getCRC32Calculator()
	{
		return static::getTempObjectInstance('Util\\CRC32');
	}

	/**
	 * Returns an instance of the crypto-safe random value generator class
	 *
	 * @return  RandomValue
	 */
	public static function getRandval()
	{
		return static::getTempObjectInstance('Util\\RandomValue');
	}

	/**
	 * Returns an instance of the filesystem tools class
	 *
	 * @return  FileSystem
	 */
	public static function getFilesystemTools()
	{
		return static::getTempObjectInstance('Util\\FileSystem');
	}

	/**
	 * Returns an instance of the filesystem tools class
	 *
	 * @return  FileLister
	 */
	public static function getFileLister()
	{
		return static::getTempObjectInstance('Util\\FileLister');
	}

	// ========================================================================
	// Temporary objects which are not part of the engine state
	// ========================================================================

	/**
	 * Returns an instance of the engine parameters provider which provides information on scripting, GUI configuration
	 * elements and engine parts
	 *
	 * @return  EngineParameters
	 */
	public static function getEngineParamsProvider()
	{
		return static::getTempObjectInstance('Util\\EngineParameters');
	}

	/**
	 * Returns an instance of the log object
	 *
	 * @return  Logger
	 */
	public static function getLog()
	{
		return static::getTempObjectInstance('Util\\Logger');
	}

	/**
	 * Returns an instance of the configuration checks object
	 *
	 * @return  ConfigurationCheck
	 */
	public static function getConfigurationChecks()
	{
		return static::getTempObjectInstance('Util\\ConfigurationCheck');
	}

	/**
	 * Returns an instance of the secure settings handling object
	 *
	 * @return  SecureSettings
	 */
	public static function getSecureSettings()
	{
		return static::getTempObjectInstance('Util\\SecureSettings');
	}

	/**
	 * Returns an instance of the secure settings handling object
	 *
	 * @return  TemporaryFiles
	 */
	public static function getTempFiles()
	{
		return static::getTempObjectInstance('Util\\TemporaryFiles');
	}

	/**
	 * Get the connector object for push messages
	 *
	 * @return  PushMessages
	 */
	public static function getPush()
	{
		return static::getObjectInstance('Util\\PushMessages');
	}

	/**
	 * Returns the absolute path to Akeeba Engine's installation
	 *
	 * @return  string
	 */
	public static function getAkeebaRoot()
	{
		if (empty(static::$root))
		{
			static::$root = __DIR__;
		}

		return static::$root;
	}

	/**
	 * @param   string  $engineType  Engine type, e.g. 'archiver', 'postproc', ...
	 * @param   string  $configKey   Profile config key with configured engine e.g. 'akeeba.advanced.archiver_engine'
	 * @param   string  $prefix      Prefix for engine classes, e.g. 'Archiver\\'
	 * @param   string  $fallback    Fallback class if the configured one doesn't exist e.g. 'Archiver\\Jpa'. Empty for
	 *                               no fallback.
	 * @param   bool    $reset       Should I force-reload the engine? Default: false.
	 *
	 * @return  mixed  The Singleton engine object instance
	 */
	protected static function getEngineInstance($engineType, $configKey, $prefix, $fallback, $reset = false)
	{
		if (!$reset && !empty(static::$engineClassnames[$engineType]))
		{
			return static::getObjectInstance(static::$engineClassnames[$engineType]);
		}

		// Unset the existing engine object
		if (!empty(static::$engineClassnames[$engineType]))
		{
			static::unsetObjectInstance(static::$engineClassnames[$engineType]);
		}

		// Get the engine name from the backup profile, construct a class name and check if it exists
		$registry                              = static::getConfiguration();
		$engine                                = $registry->get($configKey);
		static::$engineClassnames[$engineType] = $prefix . ucfirst($engine);
		$object                                = static::getObjectInstance(static::$engineClassnames[$engineType]);

		// If the engine object does not exist, fall back to the default
		if (!empty($fallback) && $object === false)
		{
			static::unsetObjectInstance(static::$engineClassnames[$engineType]);

			static::$engineClassnames[$engineType] = $fallback;
		}

		return static::getObjectInstance(static::$engineClassnames[$engineType]);
	}

	/**
	 * Internal function which instantiates an object of a class named $class_name.
	 *
	 * @param   string  $class_name
	 *
	 * @return  mixed
	 */
	protected static function getObjectInstance($class_name)
	{
		$class_name = trim($class_name, '\\');

		if (isset(static::$objectList[$class_name]))
		{
			return static::$objectList[$class_name];
		}

		static::$objectList[$class_name] = false;

		$searchClass = '\\Akeeba\\Engine\\' . $class_name;

		if (class_exists($searchClass))
		{
			static::$objectList[$class_name] = new $searchClass;
		}

		return static::$objectList[$class_name];
	}

	// ========================================================================
	// Handy functions
	// ========================================================================

	/**
	 * Internal function which removes the object of the class named $class_name
	 *
	 * @param   string  $class_name
	 *
	 * @return  void
	 */
	protected static function unsetObjectInstance($class_name)
	{
		if (isset(static::$objectList[$class_name]))
		{
			static::$objectList[$class_name] = null;
			unset(static::$objectList[$class_name]);
		}
	}

	/**
	 * Internal function which instantiates an object of a class named $class_name. This is a temporary instance which
	 * will not survive serialisation and subsequent unserialisation.
	 *
	 * @param   string  $class_name
	 *
	 * @return  mixed
	 */
	protected static function getTempObjectInstance($class_name)
	{
		$class_name = trim($class_name, '\\');

		if (!isset(static::$temporaryObjectList[$class_name]))
		{
			static::$temporaryObjectList[$class_name] = false;

			$searchClassname = '\\Akeeba\\Engine\\' . $class_name;

			if (class_exists($searchClassname))
			{
				static::$temporaryObjectList[$class_name] = new $searchClassname;
			}
		}

		return static::$temporaryObjectList[$class_name];
	}
}

/**
 * Timeout handler. It is registered as a global PHP shutdown function.
 *
 * If a PHP reports a timeout we will log this before letting PHP kill us.
 */
function AkeebaTimeoutTrap()
{
	if (connection_status() >= 2)
	{
		Factory::getLog()->error('Akeeba Engine has timed out');
	}
}

register_shutdown_function("\\Akeeba\\Engine\\AkeebaTimeoutTrap");
