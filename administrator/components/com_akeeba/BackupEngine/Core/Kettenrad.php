<?php
/**
 * Akeeba Engine
 *
 * @package   akeebaengine
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Engine\Core;

defined('AKEEBAENGINE') || die();

use Akeeba\Engine\Base\Part;
use Akeeba\Engine\Factory;
use Akeeba\Engine\Platform;
use Exception;
use RuntimeException;
use Throwable;

/**
 * Kettenrad is the main controller of Akeeba Engine. It's responsible for setting the engine into motion, running each
 * and all domain objects to their completion.
 */
class Kettenrad extends Part
{
	/**
	 * Set to true when deadOnTimeout is registered as a shutdown function
	 *
	 * @var bool
	 */
	public static $registeredShutdownCallback = false;

	/**
	 * Set to true when akeebaBackupErrorHandler is registered as an error handler
	 *
	 * @var bool
	 */
	public static $registeredErrorHandler = false;

	/**
	 * Cached copy of the response array
	 *
	 * @var array
	 */
	private $array_cache = null;

	/**
	 * The list of remaining steps
	 *
	 * @var array
	 */
	private $domain_chain = [];

	/**
	 * The current domain's name
	 *
	 * @var string
	 */
	private $domain = '';

	/**
	 * The active domain's class name
	 *
	 * @var string
	 */
	private $class = '';

	/**
	 * The current backup's tag (actually: the backup's origin)
	 *
	 * @var string
	 */
	private $tag = null;

	/**
	 * How many steps the domain_chain array contained when the backup began. Used for percentage calculations.
	 *
	 * @var int
	 */
	private $total_steps = 0;

	/**
	 * A unique backup ID which allows us to run multiple parallel backups using the same backup origin (tag)
	 *
	 * @var string
	 */
	private $backup_id = '';

	/**
	 * Set to true when there are warnings available when getStatusArray() is called. This is used at the end of the
	 * backup to send a different push message depending on whether the backup completed with or without warnings.
	 *
	 * @var  bool
	 */
	private $warnings_issued = false;

	/**
	 * Are we running under PHP 7 or later?
	 *
	 * Used in tikc() to decide whether to catch Exception or Throwable in the try-catch.
	 *
	 * @var bool
	 */
	private $isPHPSeven = false;

	/**
	 * Kettenrad constructor.
	 *
	 * Overrides the Part constructor to initialize Kettenrad-specific properties.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();

		// Is this PHP 7.x or later?
		$this->isPHPSeven = version_compare(PHP_VERSION, '7.0.0', 'ge');

		// Register the error handler
		if (!static::$registeredErrorHandler)
		{
			static::$registeredErrorHandler = true;
			set_error_handler('\\Akeeba\\Engine\\Core\\akeebaEngineErrorHandler');
		}
	}

	/**
	 * Returns the unique Backup ID
	 *
	 * @return string
	 */
	public function getBackupId()
	{
		return $this->backup_id;
	}

	/**
	 * Sets the unique backup ID.
	 *
	 * @param   string  $backup_id
	 *
	 * @return void
	 */
	public function setBackupId($backup_id = null)
	{
		$this->backup_id = $backup_id;
	}

	/**
	 * Returns the current backup tag. If none is specified, it sets it to be the
	 * same as the current backup origin and returns the new setting.
	 *
	 * @return string
	 */
	public function getTag()
	{
		if (empty($this->tag))
		{
			// If no tag exists, we resort to the pre-set backup origin
			$tag       = Platform::getInstance()->get_backup_origin();
			$this->tag = $tag;
		}

		return $this->tag;
	}

	/**
	 * The public interface to Kettenrad.
	 *
	 * Internally it calls Part::tick(), wrapped in a try-catch block which traps any runaway Exception (PHP 5) or
	 * Throwable we didn't manage to successfully suppress yet.
	 *
	 * @param   int  $nesting
	 *
	 * @return  array  A response array
	 */
	public function tick($nesting = 0)
	{
		$ret = null;
		$e   = null;

		if ($this->isPHPSeven)
		{
			// PHP 7.x -- catch any unhandled Throwable, including PHP fatal errors
			try
			{
				$ret = parent::tick($nesting);
			}
			catch (Throwable $e)
			{
				$this->setState(self::STATE_ERROR);
				$this->lastException = $e;
			}
		}
		else
		{
			// PHP 5.x -- catch unhandled exceptions but not PHP fatal errors
			try
			{
				$ret = parent::tick($nesting);
			}
			catch (Exception $e)
			{
				$this->setState(self::STATE_ERROR);
				$this->lastException = $e;
			}
		}

		// If an error occurred we don't have a return table. If that's the case create one and do log our errors.
		if (!isset($ret))
		{
			// Log the existence of an unhandled exception
			Factory::getLog()->warning("Kettenrad :: Caught unhandled exception. The backup will now fail.");

			// Recursively log unhandled exceptions
			self::logErrorsFromException($e);

			// Create the missing return table
			$ret               = $this->makeReturnTable();
			$this->array_cache = array_merge(is_null($this->array_cache) ? [] : $this->array_cache, $ret);
		}

		return $ret;
	}

	/**
	 * Returns a copy of the class's status array
	 *
	 * @return array
	 */
	public function getStatusArray()
	{
		// Get the cached array
		if (!empty($this->array_cache))
		{
			return $this->array_cache;
		}

		// Get the default table
		$array = $this->makeReturnTable();

		// Add the warnings
		$array['Warnings'] = Factory::getLog()->getWarnings();

		// Did we have warnings?
		if (is_array($array['Warnings']) || $array['Warnings'] instanceof \Countable ? count($array['Warnings']) : 0)
		{
			$this->warnings_issued = true;
		}

		// Get the current step number
		$stepCounter = Factory::getConfiguration()->get('volatile.step_counter', 0);

		// Add the archive name
		$statistics       = Factory::getStatistics();
		$record           = $statistics->getRecord();
		$array['Archive'] = $record['archivename'] ?? '';

		// Translate HasRun to what the rest of the suite expects
		$array['HasRun'] = ($this->getState() == self::STATE_FINISHED) ? 1 : 0;

		$array['Error']      = is_null($array['ErrorException']) ? '' : $array['Error'];
		$array['tag']        = $this->tag;
		$array['Progress']   = $this->getProgress();
		$array['backupid']   = $this->getBackupId();
		$array['sleepTime']  = $this->waitTimeMsec;
		$array['stepNumber'] = $stepCounter;
		$array['stepState']  = $this->stateToString($this->getState());

		$this->array_cache = $array;

		return $this->array_cache;
	}

	/**
	 * Gets the percentage of the backup process done so far.
	 *
	 * @return string
	 */
	public function getProgress()
	{
		// Get the overall percentage (based on domains complete so far)
		$remainingSteps = count($this->domain_chain) + 1;
		$totalSteps     = max($this->total_steps, 1);
		$overall        = 1 - ($remainingSteps / $totalSteps);

		// How much is this step worth?
		$currentStepMaxContribution = 1 / $totalSteps;

		// Get the percentage reported from the domain object, zero if we can't get a domain object.
		$object = !empty($this->class) ? Factory::getDomainObject($this->class) : null;
		$local  = is_object($object) ? $object->getProgress() : 0;

		// Calculate the percentage and apply [0, 100] bounds.
		$percentage = (int) (100 * ($overall + $local * $currentStepMaxContribution));
		$percentage = max(0, $percentage);
		$percentage = min(100, $percentage);

		return $percentage;
	}

	/**
	 * Obsolete method.
	 *
	 * @deprecated 7.0
	 */
	public function resetWarnings()
	{
		Factory::getLog()->debug('DEPRECATED: Akeeba Engine consumers must remove calls to resetWarnings()');
	}

	/**
	 * Initialization. Sets the state to STATE_PREPARED.
	 *
	 * @return  void
	 */
	protected function _prepare()
	{
		// Initialize the timer class. Do not remove, even though we don't use the object it needs to be initialized!
		$timer = Factory::getTimer();

		// Do we have a tag?
		if (!empty($this->_parametersArray['tag']))
		{
			$this->tag = $this->_parametersArray['tag'];
		}

		// Make sure a tag exists (or create a new one)
		$this->tag = $this->getTag();

		// Reset the log
		$logTag = $this->getLogTag();
		Factory::getLog()->open($logTag);
		Factory::getLog()->reset($logTag);

		// Reset the storage
		$factoryStorageTag = $this->tag . (empty($this->backup_id) ? '' : ('.' . $this->backup_id));
		Factory::getFactoryStorage()->reset($factoryStorageTag);

		// Apply the configuration overrides
		$overrides = Platform::getInstance()->configOverrides;

		if (is_array($overrides) && @count($overrides))
		{
			$registry       = Factory::getConfiguration();
			$protected_keys = $registry->getProtectedKeys();
			$registry->resetProtectedKeys();

			foreach ($overrides as $k => $v)
			{
				$registry->set($k, $v);
			}

			$registry->setProtectedKeys($protected_keys);
		}

		// Get the domain chain
		$this->domain_chain = Factory::getEngineParamsProvider()->getDomainChain();
		$this->total_steps  = count($this->domain_chain) - 1; // Init shouldn't count in the progress bar

		// Mark this engine for Nesting Logging
		$this->nest_logging = true;

		// Preparation is over
		$this->array_cache = null;
		$this->setState(self::STATE_PREPARED);

		// Send a push message to mark the start of backup
		$platform    = Platform::getInstance();
		$timeStamp   = date($platform->translate('DATE_FORMAT_LC2'));
		$pushSubject = sprintf($platform->translate('COM_AKEEBA_PUSH_STARTBACKUP_SUBJECT'), $platform->get_site_name(), $platform->get_host());
		$pushDetails = sprintf($platform->translate('COM_AKEEBA_PUSH_STARTBACKUP_BODY'), $platform->get_site_name(), $platform->get_host(), $timeStamp, $this->getLogTag());
		Factory::getPush()->message($pushSubject, $pushDetails);
	}

	/**
	 * Main backup process. Sets the state to STATE_RUNNING or STATE_POSTRUN.
	 *
	 * @return  void
	 */
	protected function _run()
	{
		$result = null;
		$logTag = $this->getLogTag();
		$logger = Factory::getLog();
		$logger->open($logTag);

		// Maybe we're already done or in an error state?
		if (in_array($this->getState(), [self::STATE_POSTRUN, self::STATE_ERROR]))
		{
			return;
		}

		// Set running state
		$this->setState(self::STATE_RUNNING);

		// Do I even have enough time...?
		$timer    = Factory::getTimer();
		$registry = Factory::getConfiguration();

		if (($timer->getTimeLeft() <= 0))
		{
			// We need to set the break flag for the part processing to not batch successive steps
			$registry->set('volatile.breakflag', true);

			return;
		}

		// Initialize operation counter
		$registry->set('volatile.operation_counter', 0);

		// Advance step counter
		$stepCounter = $registry->get('volatile.step_counter', 0);
		$registry->set('volatile.step_counter', ++$stepCounter);

		// Log step start number
		$logger->debug('====== Starting Step number ' . $stepCounter . ' ======');

		if (defined('AKEEBADEBUG'))
		{
			$root = Platform::getInstance()->get_site_root();
			$logger->debug('Site root: ' . $root);
		}

		$finished = false;
		$error    = false;
		// BREAKFLAG is optionally passed by domains to force-break current operation
		$breakFlag = false;

		// Apply an infinite time limit if required
		if ($registry->get('akeeba.tuning.settimelimit', 0))
		{
			if (function_exists('set_time_limit'))
			{
				set_time_limit(0);
			}
		}

		// Update statistics, marking the backup as currently processing a backup step.
		Factory::getStatistics()->updateInStep(true);

		// Loop until time's up, we're done or an error occurred, or BREAKFLAG is set
		$this->array_cache = null;
		$object            = null;

		while (($timer->getTimeLeft() > 0) && (!$finished) && (!$error) && (!$breakFlag))
		{
			// Reset the break flag
			$registry->set('volatile.breakflag', false);

			// Do we have to switch domains? This only happens if there is no active
			// domain, or the current domain has finished
			$have_to_switch = false;
			$object         = null;

			if ($this->class == '')
			{
				$have_to_switch = true;
			}
			else
			{
				$object = Factory::getDomainObject($this->class);

				if (!is_object($object))
				{
					$have_to_switch = true;
				}
				elseif (!in_array('getState', get_class_methods($object)))
				{
					$have_to_switch = true;
				}
				elseif ($object->getState() == self::STATE_FINISHED)
				{
					$have_to_switch = true;
				}
			}

			// Switch domain if necessary
			if ($have_to_switch)
			{
				$logger->debug('Kettenrad :: Switching domains');

				if (!Factory::getConfiguration()->get('akeeba.tuning.nobreak.domains', 0))
				{
					$logger->debug("Kettenrad :: BREAKING STEP BEFORE SWITCHING DOMAIN");
					$registry->set('volatile.breakflag', true);
				}

				// Free last domain
				$object = null;

				if (empty($this->domain_chain))
				{
					// Aw, we're done! No more domains to run.
					$this->setState(self::STATE_POSTRUN);
					$logger->debug("Kettenrad :: No more domains to process");
					$logger->debug('====== Finished Step number ' . $stepCounter . ' ======');
					$this->array_cache = null;

					return;
				}

				// Shift the next definition off the stack
				$this->array_cache = null;
				$new_definition    = array_shift($this->domain_chain);

				if (array_key_exists('class', $new_definition))
				{
					$logger->debug("Switching to domain {$new_definition['domain']}, class {$new_definition['class']}");
					$this->domain = $new_definition['domain'];
					$this->class  = $new_definition['class'];
					// Get a working object
					$object = Factory::getDomainObject($this->class);
					$object->setup($this->_parametersArray);
				}
				else
				{
					$logger->warning("Kettenrad :: No class defined trying to switch domains. The backup will crash.");
					$this->domain = null;
					$this->class  = null;
				}
			}
			elseif (!is_object($object))
			{
				$logger->debug("Kettenrad :: Getting domain object of class {$this->class}");
				$object = Factory::getDomainObject($this->class);
			}


			// Tick the object
			$logger->debug('Kettenrad :: Ticking the domain object');
			$this->lastException = null;

			try
			{
				// We ask the domain object to execute and return its output array
				$result = $object->tick();

				$hasErrorException = array_key_exists('ErrorException', $result) && is_object($result['ErrorException']);
				$hasErrorString = array_key_exists('Error', $result) && !empty($result['Error']);

				/**
				 * Legacy objects may not be throwing exceptions on error, instead returning an Error string in the
				 * output array. The code below addresses this discrepancy.
				 */
				if (!$hasErrorException && $hasErrorString)
				{
					$result['ErrorException'] = new RuntimeException($result['Error']);
					$hasErrorException        = true;
				}

				/**
				 * Some domain objects may be acting as nested Parts, e.g. the Database domain. In this case the
				 * internal Engine (itself a Part object) is absorbing the thrown exception and relays it in the output
				 * table's ErrorException key. This means that the code above will NOT catch the error. This code below
				 * addresses that situation by rethrowing the exception.
				 *
				 * Practical example: cannot connect to MySQL is thrown by the MySQL Dump engine. The Native database
				 * backup engine absorbs the exception and reports it back to the Database domain object through the
				 * returned output array. However, the Database domain object does not rethrow it, simply relaying it
				 * back to Kettenrad through its own returned output array. As a result we enter an infinite loop where
				 * Kettenrad asks the Database domain to tick, it asks the Native engine to tick which asks the MySQL
				 * Dump object to tick. However the latter fails again to connect to MySQL and the whole process is
				 * repeated ad nauseam. By rethrowing the propagated ErrorException we alleviate this problem.
				 */
				if ($hasErrorException)
				{
					throw $result['ErrorException'];
				}

				$logger->debug('Kettenrad :: Domain object returned without errors; propagating');
			}
			catch (Exception $e)
			{
				/**
				 * Exceptions are used to propagate error conditions through the engine. Catching them and storing them
				 * in $this->lastException lets us detect and report the error condition in Kettenrad, the integration-
				 * facing interface of the backup engine.
				 */
				$this->lastException = $e;

				$logger->debug('Kettenrad :: Domain object returned with errors; propagating');

				self::logErrorsFromException($this->lastException);

				$this->setState(self::STATE_ERROR);
			}

			// Advance operation counter
			$currentOperationNumber = $registry->get('volatile.operation_counter', 0);
			$currentOperationNumber++;
			$registry->set('volatile.operation_counter', $currentOperationNumber);

			// Process return array
			$this->setDomain($this->domain);
			$this->setStep($result['Step']);
			$this->setSubstep($result['Substep']);

			// Check for BREAKFLAG
			$breakFlag = $registry->get('volatile.breakflag', false);
			$logger->debug("Kettenrad :: Break flag status: " . ($breakFlag ? 'YES' : 'no'));

			// Process errors
			$error = $this->getState() === self::STATE_ERROR;

			// Check if the backup procedure should finish now
			$finished = $error ? true : !($result['HasRun']);

			// Log operation end
			$logger->debug('----- Finished operation ' . $currentOperationNumber . ' ------');
		}

		// Log the result
		$objectStepType = is_object($object) ? get_class($object) : 'INVALID OBJECT';

		if (!is_object($object))
		{
			$reason = ($timer->getTimeLeft() <= 0)
				? 'we already ran out of time'
				: 'a step break has already been requested';
			$logger->debug(sprintf(
				"Finishing step immediately because %s", $reason
			));
		}
		elseif (!$error)
		{
			$logger->debug("Successful Smart algorithm on " . $objectStepType);
		}
		else
		{
			$logger->error("Failed Smart algorithm on " . $objectStepType);
		}

		// Log if we have to do more work or not
		/**
		 * The domain object is not set in the following cases:
		 *
		 * - There is no time left, the while loop never ran.
		 * - The break flag was already set, the while loop never ran.
		 * - We are already finished, the while loop never ran. Shouldn't happen, the step status is set to POSTRUN.
		 * - There was an error, the while loop never ran. Shouldn't happen, we return immediately upon an error.
		 * - We tried to go to the next domain but something went wrong. Shouldn't happen.
		 *
		 * If we get to a condition that shouldn't happen we will throw a Runtime exception. In any other case we let
		 * the step finish.
		 */
		if (!is_object($object) && ($timer->getTimeLeft() > 0) && !$breakFlag)
		{
			throw new RuntimeException(sprintf(
				"Kettenrad :: Empty object found when processing domain '%s'. This should never happen.",
				$this->domain
			));
		}
		/** @noinspection PhpStatementHasEmptyBodyInspection */
		elseif (!is_object($object))
		{
			// This is an expected case.
			// I have to use an empty case because $object->getState() below would cause a PHP error on a NULL variable.
		}
		elseif ($object->getState() == self::STATE_RUNNING)
		{
			$logger->debug("Kettenrad :: More work required in domain '" . $this->domain . "'");
			// We need to set the break flag for the part processing to not batch successive steps
			$registry->set('volatile.breakflag', true);
		}
		elseif ($object->getState() == self::STATE_FINISHED)
		{
			$logger->debug("Kettenrad :: Domain '" . $this->domain . "' has finished.");
			$registry->set('volatile.breakflag', false);
		}
		elseif ($object->getState() == self::STATE_ERROR)
		{
			$logger->debug("Kettenrad :: Domain '" . $this->domain . "' has experienced an error.");
			$registry->set('volatile.breakflag', false);
		}

		// Log step end
		$logger->debug('====== Finished Step number ' . $stepCounter . ' ======');

		// Update statistics, marking the backup as having just finished processing a backup step.
		Factory::getStatistics()->updateInStep(false);

		if (!$registry->get('akeeba.tuning.nobreak.domains', 0))
		{
			// Force break between steps
			$logger->debug('Kettenrad :: Setting the break flag between domains');
			$registry->set('volatile.breakflag', true);
		}
	}

	/**
	 * Finalization. Sets the state to STATE_FINISHED.
	 *
	 * @return  void
	 */
	protected function _finalize()
	{
		// Open the log
		$logTag = $this->getLogTag();
		Factory::getLog()->open($logTag);

		// Kill the cached array
		$this->array_cache = null;

		// Remove the memory file
		$tempVarsTag = $this->tag . (empty($this->backup_id) ? '' : ('.' . $this->backup_id));
		Factory::getFactoryStorage()->reset($tempVarsTag);

		// All done.
		Factory::getLog()->debug("Kettenrad :: Just finished");
		$this->setState(self::STATE_FINISHED);

		// Send a push message to mark the end of backup
		$pushSubjectKey = $this->warnings_issued ? 'COM_AKEEBA_PUSH_ENDBACKUP_WARNINGS_SUBJECT' : 'COM_AKEEBA_PUSH_ENDBACKUP_SUCCESS_SUBJECT';
		$pushBodyKey    = $this->warnings_issued ? 'COM_AKEEBA_PUSH_ENDBACKUP_WARNINGS_BODY' : 'COM_AKEEBA_PUSH_ENDBACKUP_SUCCESS_BODY';
		$platform       = Platform::getInstance();
		$timeStamp      = date($platform->translate('DATE_FORMAT_LC2'));
		$pushSubject    = sprintf($platform->translate($pushSubjectKey), $platform->get_site_name(), $platform->get_host());
		$pushDetails    = sprintf($platform->translate($pushBodyKey), $platform->get_site_name(), $platform->get_host(), $timeStamp);
		Factory::getPush()->message($pushSubject, $pushDetails);
	}

	/**
	 * Returns the tag used to open the correct log file
	 *
	 * @return string
	 */
	protected function getLogTag()
	{
		$tag = $this->getTag();

		if (!empty($this->backup_id))
		{
			$tag .= '.' . $this->backup_id;
		}

		return $tag;
	}
}

/**
 * Timeout error handler
 */
function akeebaEnginePHPTimeoutHandler()
{
	if (connection_status() == 1)
	{
		Factory::getLog()->error('The process was aborted on user\'s request');

		return;
	}

	if (connection_status() >= 2)
	{
		Factory::getLog()->error('Akeeba Backup has timed out. Please read the documentation.');

		return;
	}
}

// Register the timeout error handler
if (!Kettenrad::$registeredShutdownCallback)
{
	Kettenrad::$registeredShutdownCallback = true;

	register_shutdown_function("\\Akeeba\\Engine\\Core\\akeebaEnginePHPTimeoutHandler");
}

/**
 * Custom PHP error handler to log catchable PHP errors to the backup log file
 *
 * @param   int     $errno
 * @param   string  $errstr
 * @param   string  $errfile
 * @param   int     $errline
 *
 * @return bool|null
 */
function akeebaEngineErrorHandler($errno, $errstr, $errfile, $errline)
{
	// Sanity check
	if (!function_exists('error_reporting'))
	{
		return false;
	}

	// Do not proceed if the error springs from an @function() construct, or if
	// the overall error reporting level is set to report no errors.
	$error_reporting = error_reporting();

	if ($error_reporting == 0)
	{
		return false;
	}

	switch ($errno)
	{

		case E_ERROR:
		case E_USER_ERROR:
		case E_RECOVERABLE_ERROR:
			/**
			 * This will only work for E_RECOVERABLE_ERROR and E_USER_ERROR, not E_ERROR. In PHP 7 all errors throw an
			 * Error throwable (a special kind of exception) which propagates nicely within our architecture.
			 */
			Factory::getLog()->error("PHP FATAL ERROR on line $errline in file $errfile:");
			Factory::getLog()->error($errstr);
			Factory::getLog()->error("Execution aborted due to PHP fatal error");
			break;

		case E_WARNING:
		case E_USER_WARNING:
			// Log as debug messages so that we don't spook the user with warnings
			Factory::getLog()->debug("PHP WARNING (not an error; you can ignore) on line $errline in file $errfile:");
			Factory::getLog()->debug($errstr);
			break;

		case E_NOTICE:
		case E_USER_NOTICE:
			// Log as debug messages so that we don't spook the user with notices
			Factory::getLog()->debug("PHP NOTICE (not an error; you can ignore) on line $errline in file $errfile:");
			Factory::getLog()->debug($errstr);
			break;

		case E_DEPRECATED:
		case E_USER_DEPRECATED:
			// Log as debug messages so that we don't spook the user with deprecated notices
			Factory::getLog()->debug("PHP DEPRECATED (not an error; you can ignore) on line $errline in file $errfile:");
			Factory::getLog()->debug($errstr);
			break;

		default:
			// These are E_DEPRECATED, E_STRICT etc. Let PHP handle them
			return false;

			break;
	}

	// Uncomment to prevent the execution of PHP's internal error handler
	//return true;

	// Let PHP's internal error handler take care of the error.
	return false;
}
