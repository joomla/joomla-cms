<?php
/**
 * Akeeba Engine
 *
 * @package   akeebaengine
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Engine\Base;

defined('AKEEBAENGINE') || die();

use Akeeba\Engine\Base\Exceptions\ErrorException;
use Akeeba\Engine\Factory;
use Exception;
use Psr\Log\LogLevel;
use Throwable;

/**
 * Base class for all Akeeba Engine parts.
 *
 * Parts are objects which perform a specific function during the backup process, e.g. backing up files or dumping
 * database contents. They have a fully defined and controlled lifecycle, from initialization to finalization. The
 * transition between lifecycle phases is handled by the `tick()` method which is essentially the only public interface
 * to interacting with an engine part.
 */
abstract class Part
{
	public const STATE_INIT = 0;
	public const STATE_PREPARED = 1;
	public const STATE_RUNNING = 2;
	public const STATE_POSTRUN = 3;
	public const STATE_FINISHED = 4;
	public const STATE_ERROR = 99;

	/**
	 * The current state of this part; see the constants at the top of this class
	 *
	 * @var int
	 */
	protected $currentState = self::STATE_INIT;

	/**
	 * The name of the engine part (a.k.a. Domain), used in return table
	 * generation.
	 *
	 * @var string
	 */
	protected $activeDomain = "";

	/**
	 * The step this engine part is in. Used verbatim in return table and
	 * should be set by the code in the _run() method.
	 *
	 * @var string
	 */
	protected $activeStep = "";

	/**
	 * A more detailed description of the step this engine part is in. Used
	 * verbatim in return table and should be set by the code in the _run()
	 * method.
	 *
	 * @var string
	 */
	protected $activeSubstep = "";

	/**
	 * Any configuration variables, in the form of an array.
	 *
	 * @var array
	 */
	protected $_parametersArray = [];

	/**
	 * The database root key
	 *
	 * @var  string
	 */
	protected $databaseRoot = [];

	/**
	 * Should we log the step nesting?
	 *
	 * @var  bool
	 */
	protected $nest_logging = false;

	/**
	 * Embedded installer preferences
	 *
	 * @var  object
	 */
	protected $installerSettings;

	/**
	 * How much milliseconds should we wait to reach the min exec time
	 *
	 * @var  int
	 */
	protected $waitTimeMsec = 0;

	/**
	 * Should I ignore the minimum execution time altogether?
	 *
	 * @var  bool
	 */
	protected $ignoreMinimumExecutionTime = false;

	/**
	 * The last exception thrown during the tick() method's execution.
	 *
	 * @var null|Exception
	 */
	protected $lastException = null;

	/**
	 * Public constructor
	 *
	 * @return  void
	 */
	public function __construct()
	{
		// Fetch the installer settings
		$this->installerSettings = (object) [
			'installerroot' => 'installation',
			'sqlroot'       => 'installation/sql',
			'databasesini'  => 1,
			'readme'        => 1,
			'extrainfo'     => 1,
			'password'      => 0,
		];

		$config               = Factory::getConfiguration();
		$installerKey         = $config->get('akeeba.advanced.embedded_installer');
		$installerDescriptors = Factory::getEngineParamsProvider()->getInstallerList();

		// Fall back to default ANGIE installer if the selected installer is not found
		if (!array_key_exists($installerKey, $installerDescriptors))
		{
			$installerKey = 'angie';
		}

		if (array_key_exists($installerKey, $installerDescriptors))
		{
			$this->installerSettings = (object) $installerDescriptors[$installerKey];
		}
	}

	/**
	 * Nested logging of exceptions
	 *
	 * The message is logged using the specified log level. The detailed information of the Throwable and its trace are
	 * logged using the DEBUG level.
	 *
	 * If the Throwable is nested, its parents are logged recursively. This should create a thorough trace leading to
	 * the root cause of an error.
	 *
	 * @param   Exception|Throwable  $exception  The Exception or Throwable to log
	 * @param   string               $logLevel   The log level to use, default ERROR
	 */
	protected static function logErrorsFromException($exception, $logLevel = LogLevel::ERROR)
	{
		$logger = Factory::getLog();

		$logger->log($logLevel, $exception->getMessage());

		$logger->debug(sprintf('[%s] %s(%u) – #%u ‹%s›', get_class($exception), $exception->getFile(), $exception->getLine(), $exception->getCode(), $exception->getMessage()));

		foreach (explode("\n", $exception->getTraceAsString()) as $line)
		{
			$logger->debug(rtrim($line));
		}

		$previous = $exception->getPrevious();

		if (!is_null($previous))
		{
			self::logErrorsFromException($previous, $logLevel);
		}
	}

	/**
	 * The public interface to an engine part. This method takes care for
	 * calling the correct method in order to perform the initialisation -
	 * run - finalisation cycle of operation and return a proper response array.
	 *
	 * @param   int  $nesting
	 *
	 * @return  array  A response array
	 */
	public function tick($nesting = 0)
	{
		$configuration       = Factory::getConfiguration();
		$timer               = Factory::getTimer();
		$this->waitTimeMsec  = 0;
		$this->lastException = null;

		/**
		 * Call the right action method, depending on engine part state.
		 *
		 * The action method may throw an exception to signal failure, hence the try-catch. If there is an exception we
		 * will set the part's state to STATE_ERROR and store the last exception.
		 */
		try
		{
			switch ($this->getState())
			{
				case self::STATE_INIT:
					$this->_prepare();
					break;

				case self::STATE_PREPARED:
				case self::STATE_RUNNING:
					$this->_run();
					break;

				case self::STATE_POSTRUN:
					$this->_finalize();
					break;
			}
		}
		catch (Exception $e)
		{
			$this->lastException = $e;
			$this->setState(self::STATE_ERROR);
		}

		// If there is still time, we are not finished and there is no break flag set, re-run the tick()
		// method.
		$breakFlag = $configuration->get('volatile.breakflag', false);

		if (
			!in_array($this->getState(), [self::STATE_FINISHED, self::STATE_ERROR]) &&
			($timer->getTimeLeft() > 0) &&
			!$breakFlag &&
			($nesting < 20) &&
			($this->nest_logging)
		)
		{
			// Nesting is only applied if $this->nest_logging == true (currently only Kettenrad has this)
			$nesting++;

			if ($this->nest_logging)
			{
				Factory::getLog()->debug("*** Batching successive steps (nesting level $nesting)");
			}

			return $this->tick($nesting);
		}

		// Return the output array
		$out = $this->makeReturnTable();

		// If it's not a nest-logged part (basically, anything other than Kettenrad) return the output array.
		if (!$this->nest_logging)
		{
			return $out;
		}

		// From here on: things to do for nest-logged parts (i.e. Kettenrad)
		if ($breakFlag)
		{
			Factory::getLog()->debug("*** Engine steps batching: Break flag detected.");
		}

		// Reset the break flag
		$configuration->set('volatile.breakflag', false);

		// Log that we're breaking the step
		Factory::getLog()->debug("*** Batching of engine steps finished. I will now return control to the caller.");

		// Detect whether I need server-side sleep
		$serverSideSleep = $this->needsServerSideSleep();

		// Enforce minimum execution time
		if (!$this->ignoreMinimumExecutionTime)
		{
			$timer              = Factory::getTimer();
			$this->waitTimeMsec = (int) $timer->enforce_min_exec_time(true, $serverSideSleep);
		}

		// Send a Return Table back to the caller
		return $out;
	}

	/**
	 * Returns a copy of the class's status array
	 *
	 * @return  array  The response array
	 */
	public function getStatusArray()
	{
		return $this->makeReturnTable();
	}

	/**
	 * Sends any kind of setup information to the engine part. Using this,
	 * we avoid passing parameters to the constructor of the class. These
	 * parameters should be passed as an indexed array and should be taken
	 * into account during the preparation process only. This function will
	 * set the error flag if it's called after the engine part is prepared.
	 *
	 * @param   array  $parametersArray  The parameters to be passed to the engine part.
	 *
	 * @return  void
	 */
	public function setup($parametersArray)
	{
		if ($this->currentState == self::STATE_PREPARED)
		{
			$this->setState(self::STATE_ERROR);

			throw new ErrorException(__CLASS__ . ":: Can't modify configuration after the preparation of " . $this->activeDomain);
		}

		$this->_parametersArray = $parametersArray;

		if (array_key_exists('root', $parametersArray))
		{
			$this->databaseRoot = $parametersArray['root'];
		}
	}

	/**
	 * Returns the state of this engine part.
	 *
	 * @return  int  The state of this engine part.
	 */
	public function getState()
	{
		if (!is_null($this->lastException))
		{
			$this->currentState = self::STATE_ERROR;
		}

		return $this->currentState;
	}

	/**
	 * Translate the integer state to a string, used by consumers of the public Engine API.
	 *
	 * @param   int  $state  The part state to translate to string
	 *
	 * @return  string
	 */
	public function stateToString($state)
	{
		switch ($state)
		{
			case self::STATE_ERROR:
				return 'error';
				break;

			case self::STATE_INIT:
				return 'init';
				break;

			case self::STATE_PREPARED:
				return 'prepared';
				break;

			case self::STATE_RUNNING:
				return 'running';
				break;

			case self::STATE_POSTRUN:
				return 'postrun';
				break;

			case self::STATE_FINISHED:
				return 'finished';
				break;
		}

		return 'init';
	}

	/**
	 * Get the current domain of the engine
	 *
	 * @return  string  The current domain
	 */
	public function getDomain()
	{
		return $this->activeDomain;
	}

	/**
	 * Get the current step of the engine
	 *
	 * @return  string  The current step
	 */
	public function getStep()
	{
		return $this->activeStep;
	}

	/**
	 * Get the current sub-step of the engine
	 *
	 * @return  string  The current sub-step
	 */
	public function getSubstep()
	{
		return $this->activeSubstep;
	}

	/**
	 * Implement this if your Engine Part can return the percentage of its work already complete
	 *
	 * @return  float  A number from 0 (nothing done) to 1 (all done)
	 */
	public function getProgress()
	{
		return 0;
	}

	/**
	 * Get the value of the minimum execution time ignore flag.
	 *
	 * DO NOT REMOVE. It is used by the Engine consumers.
	 *
	 * @return boolean
	 */
	public function isIgnoreMinimumExecutionTime()
	{
		return $this->ignoreMinimumExecutionTime;
	}

	/**
	 * Set the value of the minimum execution time ignore flag. When set, the nested logging parts (basically,
	 * Kettenrad) will ignore the minimum execution time parameter.
	 *
	 * DO NOT REMOVE. It is used by the Engine consumers.
	 *
	 * @param   boolean  $ignoreMinimumExecutionTime
	 */
	public function setIgnoreMinimumExecutionTime($ignoreMinimumExecutionTime)
	{
		$this->ignoreMinimumExecutionTime = $ignoreMinimumExecutionTime;
	}

	/**
	 * Runs any initialization code. Must set the state to STATE_PREPARED.
	 *
	 * @return  void
	 */
	abstract protected function _prepare();

	/**
	 * Runs any finalisation code. Must set the state to STATE_FINISHED.
	 *
	 * @return  void
	 */
	abstract protected function _finalize();

	/**
	 * Performs the main objective of this part. While still processing the state must be set to STATE_RUNNING. When the
	 * main objective is complete and we're ready to proceed to finalization the state must be set to STATE_POSTRUN.
	 *
	 * @return  void
	 */
	abstract protected function _run();

	/**
	 * Sets the BREAKFLAG, which instructs this engine part that the current step must break immediately,
	 * in fear of timing out.
	 *
	 * @return  void
	 */
	protected function setBreakFlag()
	{
		$registry = Factory::getConfiguration();
		$registry->set('volatile.breakflag', true);
	}

	/**
	 * Sets the engine part's internal state, in an easy to use manner
	 *
	 * @param   int  $state  The part state to set
	 *
	 * @return  void
	 */
	protected function setState($state = self::STATE_INIT)
	{
		$this->currentState = $state;
	}

	/**
	 * Constructs a Response Array based on the engine part's state.
	 *
	 * @return  array  The Response Array for the current state
	 */
	protected function makeReturnTable()
	{
		$errors = [];
		$e      = $this->lastException;

		while (!empty($e))
		{
			$errors[] = $e->getMessage();
			$e        = $e->getPrevious();
		}

		return [
			'HasRun'         => $this->currentState != self::STATE_FINISHED,
			'Domain'         => $this->activeDomain,
			'Step'           => $this->activeStep,
			'Substep'        => $this->activeSubstep,
			'Error'          => implode("\n", $errors),
			'Warnings'       => [],
			'ErrorException' => $this->lastException,
		];
	}

	/**
	 * Set the current domain of the engine
	 *
	 * @param   string  $new_domain  The domain to set
	 *
	 * @return  void
	 */
	protected function setDomain($new_domain)
	{
		$this->activeDomain = $new_domain;
	}

	/**
	 * Set the current step of the engine
	 *
	 * @param   string  $new_step  The step to set
	 *
	 * @return  void
	 */
	protected function setStep($new_step)
	{
		$this->activeStep = $new_step;
	}

	/**
	 * Set the current sub-step of the engine
	 *
	 * @param   string  $new_substep  The sub-step to set
	 *
	 * @return  void
	 */
	protected function setSubstep($new_substep)
	{
		$this->activeSubstep = $new_substep;
	}

	/**
	 * Do I need to apply server-side sleep for the time difference between the elapsed time and the minimum execution
	 * time?
	 *
	 * @return bool
	 */
	private function needsServerSideSleep()
	{
		/**
		 * If the part doesn't support tagging, i.e. I can't determine if this is a backend backup or not, I will always
		 * use server-side sleep.
		 */
		if (!method_exists($this, 'getTag'))
		{
			return true;
		}

		/**
		 * If this is not a backend backup I will always use server-side sleep. That is to say that legacy front-end,
		 * remote JSON API and CLI backups must always use server-side sleep since they do not support client-side
		 * sleep.
		 */
		if (!in_array($this->getTag(), ['backend']))
		{
			return true;
		}

		return Factory::getConfiguration()->get('akeeba.basic.clientsidewait', 0) == 0;
	}
}
