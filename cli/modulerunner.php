#!/usr/bin/env php -d error_reporting=-1 -d display_errors=1
<?php
/**
 * @package    Joomla.Cli
 *
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/* A note on the shebang
 * The shebang (the first line of this file) contains a little more than what you would
 * normally expect. It includes settings to override the default error reporting and
 * display error settings that might be in a php.ini file. This doesn't work when one
 * calls the script with PHP (e.g. php cli/modulerunner.php) but it does work when one
 * executes the script directly (e.g. cli/modulerunner.php). This is useful because it
 * means that if your environment's default behaviour is to tune down error reporting
 * or to hide errors, then running the script directly will override those settings and
 * cause PHP to emit errors by default. When developing this is particularly useful as
 * it will show you when you have a PHP fatal error due to parsing in the main script.
 */

// Initialize Joomla framework
const _JEXEC = 1;

error_reporting(E_ALL | E_NOTICE);
ini_set('display_errors', 1);

// Load system defines
if (file_exists(dirname(__DIR__) . '/defines.php'))
{
	require_once dirname(__DIR__) . '/defines.php';
}

if (!defined('_JDEFINES'))
{
	define('JPATH_BASE', dirname(__DIR__));
	require_once JPATH_BASE . '/includes/defines.php';
}

// Get the framework.
require_once JPATH_LIBRARIES . '/import.legacy.php';

// Bootstrap the CMS libraries.
require_once JPATH_LIBRARIES . '/cms.php';

// Load the configuration
require_once JPATH_CONFIGURATION . '/configuration.php';

/**
 * Wrapper to execute a single module.
 *
 * @package  Joomla.CLI
 * @since    3.3
 */
class ModuleRunner extends JApplicationCli
{
	/**
	 * Entry point for the script
	 *
	 * @return  void
	 *
	 * @since   3.3
	 */
	public function doExecute()
	{
		// If the debug flag is set or if profiling is set, enable debug.
		if ($this->input->getBool('debug') || $this->input->getBool('profiles'))
		{
			define('JDEBUG', 1);
		}
		

		$moduleName = $this->input->getString('name');
		$moduleId = $this->input->getRaw('id');

		$this->setupApplicationShim();
		// Use a switch(true) to figure out what to do.
		// Note: exit() used to prevent fallthrough.
		switch(true)
		{
			case $this->input->getBool('help') || $this->input->getBool('h'):
				$this->displayHelp();
				exit();
				break;

			case !is_bool($moduleId) && intval($moduleId) > 0 && strlen($moduleName) > 0 && $moduleName != 1:
				$this->out("Error: Use one of --id or --name but not both.");
				$this->displayUsage();
				exit();

			case !is_bool($moduleId) && intval($moduleId) > 0:
				$this->renderModuleById((int) $moduleId);
				break;

			case strlen($moduleName) > 0 && $moduleName != 1:
				$this->renderModuleByName($moduleName);
				break;

			default:
				$this->displayUsage();
				break;
		}

		$this->input->getBool('queries') ? $this->displayQueries() : null;
		$this->input->getBool('profiles') ? $this->displayProfiles() : null;
	}

	/**
	 * Set up the application shim to pretend we're a page request.
	 *
	 * @return  void
	 *
	 * @since   3.3
	 */
	protected function setupApplicationShim()
	{
		$_SERVER['HTTP_HOST']  = 'localhost';
		JFactory::getApplication('site');
	}

	/**
	 * Display usage details.
	 *
	 * @return  void
	 *
	 * @since   3.3
	 */
	protected function displayUsage()
	{
		$this->out("Usage:\tphp " . $this->input->executable . ' --id <module ID> [options]');
		$this->out("\tphp " . $this->input->executable . ' --name <module name> [options]');
	}

	/**
	 * Display help including usage details and options.
	 *
	 * @return  void
	 *
	 * @since   3.3
	 */
	protected function displayHelp()
	{
		$this->displayUsage();

		$this->out(<<<HELP

Options:
	--id <id>        The module ID from #__modules to load and render.
	--name <name>    The name of the module to render (e.g. mod_footer)
	--debug          Enable debug (sets JDEBUG)
	--queries        Display queries exeucted
	--profiles       Display profiles (implies --debug)
	--params <path>  Load parameters from a path (overrides DB for --id)
	--client <id>    The client ID to use for --id (defaults to 0, e.g. site)
	--help, -h       Display this message

Notes:
	URLs output will be incorrect unless the \$live_site variable is 
	set in your configuration.php file.
HELP
);
	}

	/**
	 * Render the module from the #__modules table.
	 *
	 * @param   integer  $moduleId  The ID of the module from the database to render.
	 *
	 * @return  void
	 *
	 * @since   3.3
	 */
	protected function renderModuleById($moduleId)
	{
		$this->out('Rendering module ID ' . $moduleId);
		$db = JFactory::getDbo();

		// We want to get the module details but we're not fussed on when it is or isn't published
		// if the module is active or even if it's published just that it looks valid.
		$query = $db->getQuery(true)
			->select('m.id, m.title, m.module, m.position, m.content, m.showtitle, m.params, mm.menuid')
			->from('#__modules AS m')
			->join('LEFT', '#__modules_menu AS mm ON mm.moduleid = m.id')
			->where('mm.moduleid = ' . $moduleId)	
			->join('LEFT', '#__extensions AS e ON e.element = m.module AND e.client_id = m.client_id')
			->where('m.client_id = ' . $this->input->getInt('client', 0));

		$db->setQuery($query);

		$result = $db->loadObject();

		$this->renderModule($result);
	}

	/**
	 * Render a module based on the module name.
	 *
	 * @param   string  $moduleName  The name of the module to render.
	 *
	 * @return  void
	 *
	 * @since   3.3
	 */
	protected function renderModuleByName($moduleName)
	{
		$this->out('Rendering module name ' . $moduleName);

		// JModuleHelper::getModule always hits the DB for some reason.
		// So we copy that code here.
		// Note: we leave params blank here and handle that in renderModule.
		$result            = new stdClass;
		$result->id        = 0;
		$result->title     = '';
		$result->module    = $moduleName;
		$result->position  = '';
		$result->content   = '';
		$result->showtitle = 0;
		$result->control   = '';
		$result->params    = '';
		$this->renderModule($result);
	}

	/**
	 * Render a module.
	 *
	 * @param   stdClass  $module  The module details to render.
	 *
	 * @return  void
	 *
	 * @since   3.3
	 */
	protected function renderModule($module)
	{
		// Check to see if we need to override the params.
		if ($this->input->getString("params"))
		{
			$module->params = file_get_contents($this->input->getString("params"));
		}
		$this->startupQueries = JFactory::getDbo()->getLog();
		$output = JModuleHelper::renderModule($module);

		$outputFile = $this->input->getString("out");
		if ($outputFile)
		{
			$this->out("Outputting to " . $outputFile);
			file_put_contents($outputFile, $output);
		}
		else
		{
			$this->out($output);
		}
	}

	/**
	 * Display queries executed during rendering.
	 *
	 * @return  void
	 *
	 * @since   3.3
	 */
	protected function displayQueries()
	{
		$db = JFactory::getDbo();

		$log = $db->getLog();
		$timings = $db->getTimings();		
		$timing = array();
		$maxtime = 0;

		if (isset($timings[0]))
		{
			$startTime = $timings[0];
			$endTime = $timings[count($timings) - 1];
			$totalBargraphTime = $endTime - $startTime;

			if ($totalBargraphTime > 0)
			{
				foreach ($log as $id => $query)
				{
					if (isset($timings[$id * 2 + 1]))
					{
						// Compute the query time: $timing[$k] = array( queryTime, timeBetweenQueries ):
						$timing[$id] = array(($timings[$id * 2 + 1] - $timings[$id * 2]) * 1000, $id > 0 ? ($timings[$id * 2] - $timings[$id * 2 - 1]) * 1000 : 0);
						$maxtime = max($maxtime, $timing[$id]['0']);
					}
				}
			}
		}

		// Let's unpack this a bit:
		// 1) Get all of the queries executed.
		// 2) Get the copy of the queries we executed before we loaded the module (aronud 8 or so)
		// 3) Use array_diff to extract the new queries; we could have used array splice here as well.
		// 4) Use array_values to essentially re-order the numeric indexes for output.
		// Note: we'll usually have two queries in here not from the module:
		//              - one to get all of the modules AND
		//              - one to get the template styles
		$queries = array_diff($log, $this->startupQueries);
		$this->out(sprintf('Total Queries: %d of %d in %f ms', count($queries), count($log), $maxtime));
		$format = '%0' . strlen(count($queries)) . 'd';
		$index = 1;
		foreach ($queries as $offset => $query)
		{
			$this->out(sprintf("\t[$format] [%f] %s\n", $index++, $timing[$offset][0], $query));
		}
	}

	/**
	 * Display profiles captured during processing.
	 *
	 * @return  void
	 *
	 * @since   3.3
	 */
	protected function displayProfiles()
	{
		$this->out();
		$this->out("Profiles:");
		foreach(JProfiler::getInstance('Application')->getBuffer() as $buffer)
		{
			$this->out("\t" . $buffer);
		}
	}	
}

JApplicationCli::getInstance('ModuleRunner')->execute();

