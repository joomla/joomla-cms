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
use Akeeba\Engine\Dump\Base as DumpBase;
use Akeeba\Engine\Factory;
use RuntimeException;

/**
 * Multiple database backup engine.
 */
class Db extends Part
{
	/** @var array A list of the databases to be packed */
	private $database_list = [];

	/** @var array The current database configuration data */
	private $database_config = null;

	/** @var DumpBase The current dumper engine used to backup tables */
	private $dump_engine = null;

	/** @var string The contents of the databases.json file */
	private $databases_json = '';

	/** @var array An array containing the database definitions of all dumped databases so far */
	private $dumpedDatabases = [];

	/** @var int Total number of databases left to be processed */
	private $total_databases = 0;

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
	 * Implements the getProgress() percentage calculation based on how many
	 * databases we have fully dumped and how much of the current database we
	 * have dumped.
	 *
	 * @return  float
	 */
	public function getProgress()
	{
		if (!$this->total_databases)
		{
			return 0;
		}

		// Get the overall percentage (based on databases fully dumped so far)
		$remaining_steps = count($this->database_list);
		$remaining_steps++;
		$overall = 1 - ($remaining_steps / $this->total_databases);

		// How much is this step worth?
		$this_max = 1 / $this->total_databases;

		// Get the percentage done of the current database
		$local = is_object($this->dump_engine) ? $this->dump_engine->getProgress() : 0;

		$percentage = $overall + $local * $this_max;

		if ($percentage < 0)
		{
			$percentage = 0;
		}
		elseif ($percentage > 1)
		{
			$percentage = 1;
		}

		return $percentage;
	}

	/**
	 * Implements the _prepare abstract method
	 *
	 * @return  void
	 */
	protected function _prepare()
	{
		Factory::getLog()->debug(__CLASS__ . " :: Preparing instance");

		// Populating the list of databases
		$this->populate_database_list();

		$this->total_databases = count($this->database_list);

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
		}
		else
		{
			$this->setState(self::STATE_RUNNING);
		}

		// Make sure we have a dumper instance loaded!
		if (is_null($this->dump_engine) && !empty($this->database_list))
		{
			Factory::getLog()->debug(__CLASS__ . " :: Iterating next database");

			// Reset the volatile key holding the table names for this database
			Factory::getConfiguration()->set('volatile.database.table_names', []);

			// Create a new instance
			$this->dump_engine = Factory::getDumpEngine(true);

			// Configure the dumper instance and pass on the volatile database root registry key
			$registry = Factory::getConfiguration();
			$rootkeys = array_keys($this->database_list);
			$root     = array_shift($rootkeys);
			$registry->set('volatile.database.root', $root);

			$this->database_config                         = array_shift($this->database_list);
			$this->database_config['root']                 = $root;
			$this->database_config['process_empty_prefix'] = ($root == '[SITEDB]') ? true : false;

			Factory::getLog()->debug(sprintf("%s :: Now backing up %s (%s)", __CLASS__, $root, $this->database_config['database']));

			$this->dump_engine->setup($this->database_config);
		}
		elseif (is_null($this->dump_engine) && empty($this->database_list))
		{
			throw new RuntimeException('Current dump engine died while resuming the step');
		}

		// Try to step the instance
		$retArray = $this->dump_engine->tick();

		// Error propagation
		$this->lastException = $retArray['ErrorException'];

		if (!is_null($this->lastException))
		{
			throw $this->lastException;
		}

		$this->setStep($retArray['Step']);
		$this->setSubstep($retArray['Substep']);

		// Check if the instance has finished
		if (!$retArray['HasRun'])
		{
			// Set the number of parts
			$this->database_config['parts'] = $this->dump_engine->partNumber + 1;

			// Push the list of tables in the database into the definition of the last database backed up
			$this->database_config['tables'] = Factory::getConfiguration()->get('volatile.database.table_names', []);
			Factory::getConfiguration()->set('volatile.database.table_names', []);

			// Push the definition of the last database backed up into dumpedDatabases
			array_push($this->dumpedDatabases, $this->database_config);

			// Go to the next entry in the list and dispose the old AkeebaDumperDefault instance
			$this->dump_engine = null;

			// Are we past the end of the list?
			if (empty($this->database_list))
			{
				Factory::getLog()->debug(__CLASS__ . " :: No more databases left to iterate");
				$this->setState(self::STATE_POSTRUN);
			}
		}
	}

	/**
	 * Implements the _finalize() abstract method
	 *
	 * @return  void
	 */
	protected function _finalize()
	{
		$this->setState(self::STATE_FINISHED);

		// If we are in db backup mode, don't create a databases.json
		$configuration = Factory::getConfiguration();

		if (!Factory::getEngineParamsProvider()->getScriptingParameter('db.databasesini', 1))
		{
			Factory::getLog()->debug(__CLASS__ . " :: Skipping databases.json");
		}
		// Create the databases.json contents
		// P.A. This still has the old name with the "ini" string. That's for legacy support. Must update it in the future
		elseif ($this->installerSettings->databasesini)
		{
			$this->createDatabasesJSON();

			Factory::getLog()->debug(__CLASS__ . " :: Creating databases.json");

			// Create a new string
			$databasesJSON = json_encode($this->databases_json, JSON_PRETTY_PRINT);

			Factory::getLog()->debug(__CLASS__ . " :: Writing databases.json contents");

			$archiver        = Factory::getArchiverEngine();
			$virtualLocation = (Factory::getEngineParamsProvider()->getScriptingParameter('db.saveasname', 'normal') == 'short') ? '' : $this->installerSettings->sqlroot;
			$archiver->addFileVirtual('databases.json', $virtualLocation, $databasesJSON);
		}

		// On alldb mode, we have to finalize the archive as well
		if (Factory::getEngineParamsProvider()->getScriptingParameter('db.finalizearchive', 0))
		{
			Factory::getLog()->info("Finalizing database dump archive");

			$archiver = Factory::getArchiverEngine();
			$archiver->finalize();
		}

		// In CLI mode we'll also close the database connection
		if (defined('AKEEBACLI'))
		{
			Factory::getLog()->info("Closing the database connection to the main database");
			Factory::unsetDatabase();
		}

		return;
	}

	/**
	 * Populates database_list with the list of databases in the settings
	 *
	 * @return void
	 */
	protected function populate_database_list()
	{
		// Get database inclusion filters
		$filters             = Factory::getFilters();
		$this->database_list = $filters->getInclusions('db');

		if (Factory::getEngineParamsProvider()->getScriptingParameter('db.skipextradb', 0))
		{
			// On database only backups we prune extra databases
			Factory::getLog()->debug(__CLASS__ . " :: Adding only main database");

			if (count($this->database_list) > 1)
			{
				$this->database_list = array_slice($this->database_list, 0, 1);
			}
		}
	}

	protected function createDatabasesJSON()
	{
		// caching databases.json contents
		Factory::getLog()->debug(__CLASS__ . " :: Creating databases.json data");

		// Create a new array
		$this->databases_json = [];

		$registry = Factory::getConfiguration();

		$blankOutPass = $registry->get('engine.dump.common.blankoutpass', 0);
		$siteRoot     = $registry->get('akeeba.platform.newroot', '');

		// Loop through databases list
		foreach ($this->dumpedDatabases as $definition)
		{
			$section = basename($definition['dumpFile']);

			$dboInstance = Factory::getDatabase($definition);
			$type        = $dboInstance->name;
			$tech        = $dboInstance->getDriverType();

			// If the database is a sqlite one, we have to process the database name which contains the path
			// At the moment we only handle the case where the db file is UNDER site root
			if ($tech == 'sqlite')
			{
				$definition['database'] = str_replace($siteRoot, '#SITEROOT#', $definition['database']);
			}

			$this->databases_json[$section] = [
				'dbtype'  => $type,
				'dbtech'  => $tech,
				'dbname'  => $definition['database'],
				'sqlfile' => $definition['dumpFile'],
				'marker'  => "\n/**ABDB**/",
				'dbhost'  => $definition['host'],
				'dbuser'  => $definition['username'],
				'dbpass'  => $definition['password'],
				'prefix'  => $definition['prefix'],
				'parts'   => $definition['parts'],
				'tables'  => $definition['tables'],
			];

			if ($blankOutPass)
			{
				$this->databases_json[$section]['dbuser'] = '';
				$this->databases_json[$section]['dbpass'] = '';
			}
		}
	}
}
