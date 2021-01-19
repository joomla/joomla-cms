<?php
/**
 * Akeeba Engine
 *
 * @package   akeebaengine
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Engine\Dump;

defined('AKEEBAENGINE') || die();

use Akeeba\Engine\Base\Exceptions\ErrorException;
use Akeeba\Engine\Base\Part;
use Akeeba\Engine\Dump\Base as DumpBase;
use Akeeba\Engine\Factory;
use RuntimeException;

class Native extends Part
{
	/** @var DumpBase */
	private $_engine = null;

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
	 * Runs the preparation for this part. Should set _isPrepared
	 * to true
	 *
	 * @return  void
	 */
	protected function _prepare()
	{
		Factory::getLog()->debug(__CLASS__ . " :: Processing parameters");

		$options = null;

		// Get the DB connection parameters
		if (is_array($this->_parametersArray))
		{
			$driver   = array_key_exists('driver', $this->_parametersArray) ? $this->_parametersArray['driver'] : 'mysql';
			$host     = array_key_exists('host', $this->_parametersArray) ? $this->_parametersArray['host'] : '';
			$port     = array_key_exists('port', $this->_parametersArray) ? $this->_parametersArray['port'] : '';
			$username = array_key_exists('username', $this->_parametersArray) ? $this->_parametersArray['username'] : '';
			$username = array_key_exists('user', $this->_parametersArray) ? $this->_parametersArray['user'] : $username;
			$password = array_key_exists('password', $this->_parametersArray) ? $this->_parametersArray['password'] : '';
			$database = array_key_exists('database', $this->_parametersArray) ? $this->_parametersArray['database'] : '';
			$prefix   = array_key_exists('prefix', $this->_parametersArray) ? $this->_parametersArray['prefix'] : '';

			if (($driver == 'mysql') && !function_exists('mysql_connect'))
			{
				$driver = 'mysqli';
			}

			$options = [
				'driver'   => $driver,
				'host'     => $host . ($port != '' ? ':' . $port : ''),
				'user'     => $username,
				'password' => $password,
				'database' => $database,
				'prefix'   => is_null($prefix) ? '' : $prefix,
			];
		}

		$db         = Factory::getDatabase($options);

		if ($db->getErrorNum() > 0)
		{
			$error = $db->getErrorMsg();

			throw new RuntimeException(__CLASS__ . ' :: Database Error: ' . $error);
		}

		$driverType = $db->getDriverType();
		$className  = '\\Akeeba\\Engine\\Dump\\Native\\' . ucfirst($driverType);

		// Check if we have a native dump driver
		if (!class_exists($className, true))
		{
			$this->setState(self::STATE_ERROR);

			throw new ErrorException('Akeeba Engine does not have a native dump engine for ' . $driverType . ' databases');
		}

		Factory::getLog()->debug(__CLASS__ . " :: Instanciating new native database dump engine $className");

		$this->_engine = new $className;

		$this->_engine->setup($this->_parametersArray);

		$this->_engine->callStage('_prepare');
		$this->setState($this->_engine->getState());
	}

	/**
	 * Runs the finalisation process for this part. Should set
	 * _isFinished to true.
	 *
	 * @return  void
	 */
	protected function _finalize()
	{
		$this->_engine->callStage('_finalize');
		$this->setState($this->_engine->getState());
	}

	/**
	 * Runs the main functionality loop for this part. Upon calling,
	 * should set the _isRunning to true. When it finished, should set
	 * the _hasRan to true. If an error is encountered, setError should
	 * be used.
	 *
	 * @return  void
	 */
	protected function _run()
	{
		$this->_engine->callStage('_run');
		$this->setState($this->_engine->getState());
		$this->setStep($this->_engine->getStep());
		$this->setSubstep($this->_engine->getSubstep());
		$this->partNumber = $this->_engine->partNumber;
	}

}
