<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.Debug
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * Joomla! Debug plugin.
 *
 * @since  1.5
 */
class PlgSystemDebug extends JPlugin
{
	/**
	 * True if debug lang is on.
	 *
	 * @var    boolean
	 * @since  3.0
	 */
	private $debugLang = false;

	/**
	 * Holds log entries handled by the plugin.
	 *
	 * @var    array
	 * @since  3.1
	 */
	private $logEntries = array();

	/**
	 * Count of deprecated log entries.
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	private $logEntriesDeprecated = 0;

	/**
	 * If true, deprecated logs will be counted but not displayed.
	 *
	 * @var    bolean
	 * @since  __DEPLOY_VERSION__
	 */
	private $logEntriesDeprecatedCountOnly = false;

	/**
	 * Holds SHOW PROFILES of queries.
	 *
	 * @var    array
	 * @since  3.1.2
	 */
	private $sqlShowProfiles = array();

	/**
	 * Holds all SHOW PROFILE FOR QUERY n, indexed by n-1.
	 *
	 * @var    array
	 * @since  3.1.2
	 */
	private $sqlShowProfileEach = array();

	/**
	 * Holds all EXPLAIN EXTENDED for all queries.
	 *
	 * @var    array
	 * @since  3.1.2
	 */
	private $explains = array();

	/**
	 * Holds total amount of executed queries.
	 *
	 * @var    int
	 * @since  3.2
	 */
	private $totalQueries = 0;

	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 * @since  3.3
	 */
	protected $app;

	/**
	 * Container for callback functions to be triggered when rendering the console.
	 *
	 * @var    callable[]
	 * @since  3.7.0
	 */
	private static $displayCallbacks = array();

	/**
	 * Constructor.
	 *
	 * @param   object  &$subject  The object to observe.
	 * @param   array   $config    An optional associative array of configuration settings.
	 *
	 * @since   1.5
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

        // Setup logging to files if configured.
        if ($this->params->get('log-deprecated') || $this->params->get('log-everything'))
        {
            $this->initLogFiles();
        }

		// Get the application if not done by JPlugin. This may happen during upgrades from Joomla 2.5.
		if (!$this->app)
		{
			$this->app = JFactory::getApplication();
		}

		$this->debugLang = $this->app->get('debug_lang');

		// Skip the plugin if debug is off
		if ($this->debugLang == '0' && $this->app->get('debug') == '0')
		{
			return;
		}

		// Only if debugging or language debug is enabled.
		if (JDEBUG || $this->debugLang)
		{
			JFactory::getConfig()->set('gzip', 0);
			ob_start();
			ob_implicit_flush(false);
		}

        // Setup logging to display in debug console
		if ($this->params->get('logs', 1))
		{
            $this->initLogging();
		}
	}

    /**
     * Initialize text file loggers for 'deprecated' and/or 'everying' logs.
     *
     * @return  void
     *
	 * @since   __DEPLOY_VERSION__
     */
    protected function initLogFiles()
    {
		// Log the deprecated API.
		if ($this->params->get('log-deprecated'))
		{
			JLog::addLogger(array('text_file' => 'deprecated.php'), JLog::ALL, array('deprecated'));

			// Log deprecated class aliases
			foreach (JLoader::getDeprecatedAliases() as $deprecation)
			{
				JLog::add(
					sprintf(
						'%1$s has been aliased to %2$s and the former class name is deprecated. The alias will be removed in %3$s.',
						$deprecation['old'],
						$deprecation['new'],
						$deprecation['version']
					),
					JLog::WARNING,
					'deprecated'
				);
			}
		}

		// Log everything (except deprecated APIs and sql queries, these are logged with different options).
		if ($this->params->get('log-everything'))
		{
			JLog::addLogger(array('text_file' => 'everything.php'), JLog::ALL, array('deprecated', 'databasequery'), true);
		}
    }

    /**
     * Initialize a logger to receive logs that should be displayed in the debug console.
     *
     * @return  void
     *
	 * @since   __DEPLOY_VERSION__
     */
    protected function initLogging()
    {
		$priority = 0;

		foreach ($this->params->get('log_priorities', array()) as $p)
		{
			$const = 'JLog::' . strtoupper($p);

			if (!defined($const))
			{
				continue;
			}

			$priority |= constant($const);
		}

		// Split into an array at any character other than alphabet, numbers, _, ., or -
		$categories = array_filter(preg_split('/[^A-Z0-9_\.-]/i', $this->params->get('log_categories', '')));
		$mode = $this->params->get('log_category_mode', 0);

        // True if deprecated is in the array and mode is exclude or not in the array and mode is include.
        $this->logEntriesDeprecatedCountOnly = in_array('deprecated', $categories) === !!$mode;

        // Now, in any case we need deprecated to go to the logger so remove it from the array if it exists.
        $categories = array_diff($categories, array('deprecated'));

        // And add it to the array if we are in inclusion mode.
        if (!$mode)
        {
            $categories[] = 'deprecated';
        }

		JLog::addLogger(array('logger' => 'callback', 'callback' => array($this, 'logger')), $priority, $categories, $mode);
    }

	/**
	 * Add the CSS for debug.
	 * We can't do this in the constructor because stuff breaks.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public function onAfterDispatch()
	{
		// Only if debugging or language debug is enabled.
		if ((JDEBUG || $this->debugLang) && $this->isAuthorisedDisplayDebug())
		{
			JHtml::_('stylesheet', 'cms/debug.css', array('version' => 'auto', 'relative' => true));
		}

		// Only if debugging is enabled for SQL query popovers.
		if (JDEBUG && $this->isAuthorisedDisplayDebug())
		{
			JHtml::_('bootstrap.tooltip');
			JHtml::_('bootstrap.popover', '.hasPopover', array('placement' => 'top'));
		}
	}

	/**
	 * Show the debug info.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function onAfterRespond()
	{
		if ($this->params->get('log-executed-sql', '0'))
		{
			$this->writeSQLLog();
		}

		// Do not render if debugging or language debug is not enabled.
		if (!JDEBUG && !$this->debugLang)
		{
			return;
		}

		// User has to be authorised to see the debug information.
		if (!$this->isAuthorisedDisplayDebug())
		{
			return;
		}

		// Only render for HTML output.
		if (JFactory::getDocument()->getType() !== 'html')
		{
			return;
		}

		// Capture output.
		$contents = ob_get_contents();

		if ($contents)
		{
			ob_end_clean();
		}

		// No debug for Safari and Chrome redirection.
		if (strstr(strtolower(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : ''), 'webkit') !== false
			&& substr($contents, 0, 50) === '<html><head><meta http-equiv="refresh" content="0;')
		{
			echo $contents;

			return;
		}

		// Load language.
		$this->loadLanguage();
		$db = JFactory::getDbo();

		$sections = array();

		if (JDEBUG)
		{
			if (!!JError::getErrors())
			{
				$sections['errors'] = array(
					'errors' => JError::getErrors()
				);
			}

			$sections['session'] = array(
				'id' => 0,
				'key' => '',
				'session' => JFactory::getSession()->getData(),
			);

			if ($this->params->get('profile', 1))
			{
				$sections['profile'] = array(
					'marks' => JProfiler::getInstance('Application')->getMarks()
				);

				if ($db->getLog())
				{
					$sections['profile']['timings'] = $db->getTimings();
				}
			}

			if ($this->params->get('memory', 1))
			{
				$sections['memory'] = array(
					'memory' => memory_get_usage(),
					'peak' => memory_get_peak_usage(),
					'limit' => ini_get('memory_limit'),
				);
			}

			if ($this->params->get('queries', 1))
			{
				$db->addDisconnectHandler(array($this, 'mysqlDisconnectHandler'));
				$db->disconnect();

				$sections['queries'] = array(
					'name' => $db->name,
					'log' => $db->getLog(),
					'timings' => $db->getTimings(),
					'callStacks' => $db->getCallStacks(),
					'totalQueries' => $db->getCount(),
					'sqlShowProfiles' => $this->sqlShowProfiles,
					'sqlShowProfileEach' => $this->sqlShowProfileEach,
					'explains' => $this->explains,
					'queryTypes' => $this->params->get('query_types', 1),
				);
			}

			if ($this->params->get('logs', 1) && !empty($this->logEntries))
			{
				$sections['logs'] = array(
					'entries' => $this->logEntries,
                    'deprecatedCount' => $this->logEntriesDeprecated,
				);
			}
		}

		if ($this->debugLang)
		{
			if ($this->params->get('language_errorfiles', 1))
			{
				$languageErrors = JFactory::getLanguage()->getErrorFiles();

				if (!empty($languageErrors))
				{
					$sections['language_files_in_error'] = array(
						'errors' => $languageErrors,
					);
				}
			}

			if ($this->params->get('language_files', 1))
			{
                $paths = JFactory::getLanguage()->getPaths();

                if (!empty($paths))
                {
                    $sections['language_files_loaded'] = array(
                        'paths' => $paths,
                    );
                }
			}

			if ($this->params->get('language_strings'))
			{
                $guesses = $this->getUntranslatedStringGuesses();

                if (!empty($guesses))
                {
                    $sections['untranslated_strings'] = array(
                        'guesses' => $guesses,
                    );
                }
			}
		}

		$callbacks = array_map('call_user_func', self::$displayCallbacks);
		$displayData = array('sections' => $sections, 'callbacks' => $callbacks);

		ob_start();
		$html = JLayoutHelper::render('plugins.system.debug.console', $displayData);
		ob_end_clean();

        echo substr_replace($contents, $html, strrpos($contents, '</body>'), 0);
	}

	/**
	 * Add a display callback to be rendered with the debug console.
	 *
	 * @param   string    $name      The name of the callable, this is used to generate the section title.
	 * @param   callable  $callable  The callback function to be added.
	 *
	 * @return  boolean
	 *
	 * @since   3.7.0
	 * @throws  InvalidArgumentException
	 */
	public static function addDisplayCallback($name, $callable)
	{
		// TODO - When PHP 5.4 is the minimum the parameter should be typehinted "callable" and this check removed
		if (!is_callable($callable))
		{
			throw new InvalidArgumentException('A valid callback function must be given.');
		}

		self::$displayCallbacks[$name] = $callable;

		return true;
	}

	/**
	 * Remove a registered display callback
	 *
	 * @param   string  $name  The name of the callable.
	 *
	 * @return  boolean
	 *
	 * @since   3.7.0
	 */
	public static function removeDisplayCallback($name)
	{
		unset(self::$displayCallbacks[$name]);

		return true;
	}

	/**
	 * Method to check if the current user is allowed to see the debug information or not.
	 *
	 * @return  boolean  True is access is allowed.
	 *
	 * @since   3.0
	 */
	private function isAuthorisedDisplayDebug()
	{
		static $result = null;

		if (!is_null($result))
		{
			return $result;
		}

		// If the user is not allowed to view the output then end here.
		$filterGroups = (array) $this->params->get('filter_groups', null);

		if (!empty($filterGroups))
		{
			$userGroups = JFactory::getUser()->get('groups');

			if (!array_intersect($filterGroups, $userGroups))
			{
				$result = false;

				return false;
			}
		}

		$result = true;

		return true;
	}

	/**
	 * Disconnect handler for database to collect profiling and explain information.
	 *
	 * @param   JDatabaseDriver  &$db  Database object.
	 *
	 * @return  void
	 *
	 * @since   3.1.2
	 */
	public function mysqlDisconnectHandler(&$db)
	{
		$db->setDebug(false);

		$this->totalQueries = $db->getCount();

		$dbVersion5037 = (strpos($db->name, 'mysql') !== false) && version_compare($db->getVersion(), '5.0.37', '>=');

		if ($dbVersion5037)
		{
			try
			{
				// Check if profiling is enabled.
				$db->setQuery("SHOW VARIABLES LIKE 'have_profiling'");
				$hasProfiling = $db->loadResult();

				if ($hasProfiling)
				{
					// Run a SHOW PROFILE query.
					$db->setQuery('SHOW PROFILES');
					$this->sqlShowProfiles = $db->loadAssocList();

					if ($this->sqlShowProfiles)
					{
						foreach ($this->sqlShowProfiles as $qn)
						{
							// Run SHOW PROFILE FOR QUERY for each query where a profile is available (max 100).
							$db->setQuery('SHOW PROFILE FOR QUERY ' . (int) ($qn['Query_ID']));
							$this->sqlShowProfileEach[(int) ($qn['Query_ID'] - 1)] = $db->loadAssocList();
						}
					}
				}
				else
				{
					$this->sqlShowProfileEach[0] = array(array('Error' => 'MySql have_profiling = off'));
				}
			}
			catch (Exception $e)
			{
				$this->sqlShowProfileEach[0] = array(array('Error' => $e->getMessage()));
			}
		}

		if (in_array($db->name, array('mysqli', 'mysql', 'pdomysql', 'postgresql')))
		{
			$log = $db->getLog();

			foreach ($log as $k => $query)
			{
				$dbVersion56 = (strpos($db->name, 'mysql') !== false) && version_compare($db->getVersion(), '5.6', '>=');

				if ((stripos($query, 'select') === 0) || ($dbVersion56 && ((stripos($query, 'delete') === 0) || (stripos($query, 'update') === 0))))
				{
					try
					{
						$db->setQuery('EXPLAIN ' . ($dbVersion56 ? 'EXTENDED ' : '') . $query);
						$this->explains[$k] = $db->loadAssocList();
					}
					catch (Exception $e)
					{
						$this->explains[$k] = array(array('Error' => $e->getMessage()));
					}
				}
			}
		}
	}

	/**
	 * Store log messages so they can be displayed later.
	 * This function is passed log entries by JLogLoggerCallback.
	 *
	 * @param   JLogEntry  $entry  A log entry.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function logger(JLogEntry $entry)
	{
        if ($entry->category == 'deprecated')
        {
            $this->logEntriesDeprecated++;

            if ($this->logEntriesDeprecatedCountOnly)
            {
                return;
            }
        }

		$this->logEntries[] = $entry;
	}

	/**
	 * Write query to the log file
	 *
	 * @return  void
	 *
	 * @since   3.5
	 */
	protected function writeSQLLog()
	{
		$file   = JFactory::getApplication()->get('log_path') . '/' . $this->getLogFileName('databasequery');

		// Get the queries from log.
		$queries = array();
		$db      = JFactory::getDbo();
		$log     = $db->getLog();
		$timings = $db->getTimings();

        $search = array('`', "\t", "\r\n", "\n");
        $replace = array('', ' ', ' ', ' ');

		foreach ($log as $id => $query)
		{
			if (isset($timings[$id * 2 + 1]))
			{
				$queries[] = str_replace($search, $replace, $log[$id]) . ';';
			}
		}

		if (JFile::exists($file))
		{
			JFile::delete($file);
		}

		// Write new file.
		$log = implode("\n", $queries);
		JFile::write($file, $log);
	}

    /**
     * Builds a name for a log file based on the current site, option, view, layout and log category
     *
     * @param   string  $category  Log category or other description of logs
     *
     * @return  string
     *
	 * @since   __DEPLOY_VERSION__
     */
    protected function getLogFileName($category)
    {
		$app   = JFactory::getApplication();
        $input = $app->input;
        $parts = array(
            $category,
            ($app->isSite()) ? 'site' : 'admin',
            $input->get('option'),
            $input->get('view'),
            $input->get('layout'),
        );

        return implode('_', $parts) . '.php';
    }

	/**
	 * Method to get guesses about untranslated strings.
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
    protected function getUntranslatedStringGuesses()
    {
        $stripFirst = $this->params->get('strip-first');
        $stripPref = $this->params->get('strip-prefix');
        $stripSuff = $this->params->get('strip-suffix');

        $orphans = JFactory::getLanguage()->getOrphans();
        $guesses = array();

        if (empty($orphans))
        {
            return $guesses;
        }

        ksort($orphans, SORT_STRING);

        foreach ($orphans as $key => $occurance)
        {
        	if (!is_array($occurance) || !isset($occurance[0]))
        	{
                continue;
            }

    		$info = $occurance[0];
    		$file = ($info['file']) ? $info['file'] : '';

    		if (!isset($guesses[$file]))
    		{
    			$guesses[$file] = array();
    		}

    		// Prepare the key.
    		if (($pos = strpos($info['string'], '=')) > 0)
    		{
                // Should we not limit this explode to 2 parts?
    			$parts = explode('=', $info['string']);
    			$key = $parts[0];
    			$guess = $parts[1];
    		}
    		else
    		{
    			$guess = str_replace('_', ' ', $info['string']);

    			if ($stripFirst)
    			{
    				$parts = explode(' ', $guess);

    				if (count($parts) > 1)
    				{
    					array_shift($parts);
    					$guess = implode(' ', $parts);
    				}
    			}

    			$guess = trim($guess);

    			if ($stripPref)
    			{
    				$guess = trim(preg_replace(chr(1) . '^' . $stripPref . chr(1) . 'i', '', $guess));
    			}

    			if ($stripSuff)
    			{
    				$guess = trim(preg_replace(chr(1) . $stripSuff . '$' . chr(1) . 'i', '', $guess));
    			}
    		}

    		$key = trim(strtoupper($key));
    		$key = preg_replace('#\s+#', '_', $key);
    		$key = preg_replace('#\W#', '', $key);

    		// Prepare the text.
    		$guesses[$file][] = $key . '="' . $guess . '"';

        }

        return $guesses;
    }
}
