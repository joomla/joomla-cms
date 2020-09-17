<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Engine\Driver;

// Protection against direct access
defined('AKEEBAENGINE') || die();

use Akeeba\Engine\Platform;
use Exception;
use Joomla\CMS\Factory;

class Joomla
{
	/** @var Base The real database connection object */
	private $dbo;

	/**
	 * Database object constructor
	 *
	 * @param   array  $options  List of options used to configure the connection
	 */
	public function __construct($options = [])
	{
		// Get best matching Akeeba Backup driver instance
		if (class_exists('JFactory'))
		{
			// Get the database driver *AND* make sure it's connected.
			$db = Factory::getDBO();
			$db->connect();

			$options['connection'] = $db->getConnection();

			switch ($db->name)
			{
				case 'mysql':
					// So, Joomla! 4's "mysql" is, actually, "pdomysql".
					$driver = 'mysql';

					if (version_compare(JVERSION, '3.99999.99999', 'gt'))
					{
						$driver = 'pdomysql';
					}
					break;

				case 'mysqli':
					$driver = 'mysqli';
					break;

				case 'pdomysql':
					$driver = 'pdomysql';
					break;

				default:
					throw new \RuntimeException("Unsupported database driver {$db->name}");

					break;
			}

			$driver = '\\Akeeba\\Engine\\Driver\\' . ucfirst($driver);
		}
		else
		{
			$driver = Platform::getInstance()->get_default_database_driver(false);
		}

		$this->dbo = new $driver($options);
	}

	public function close()
	{
		/**
		 * We should not, in fact, try to close the connection by calling the parent method.
		 *
		 * If you close the connection we ask PHP's mysql / mysqli / pdomysql driver to disconnect the MySQL connection
		 * resource from the database server inside our instance of Akeeba Engine's database driver. However, this
		 * identical resource is also present in Joomla's database driver. Joomla will also try to close the connection
		 * to a now invalid resource, causing a PHP notice to be recorded.
		 *
		 * By setting the connection resource to null in our own driver object we prevent closing the resource,
		 * delegating that responsibility to Joomla. It will gladly do so at the very least automatically, through its
		 * db driver's __destruct.
		 */
		$this->dbo->setConnection(null);
	}

	public function open()
	{
		if (method_exists($this->dbo, 'open'))
		{
			$this->dbo->open();
		}
		elseif (method_exists($this->dbo, 'connect'))
		{
			$this->dbo->connect();
		}
	}

	/**
	 * Magic method to proxy all calls to the loaded database driver object
	 *
	 * @throws  Exception
	 */
	public function __call($name, array $arguments)
	{
		if (is_null($this->dbo))
		{
			throw new Exception('Akeeba Engine database driver is not loaded');
		}

		if (method_exists($this->dbo, $name) || in_array($name, ['q', 'nq', 'qn']))
		{
			// Call_user_func_array is ~3 times slower than direct method calls.
			// (thank you, Nooku Framework, for the tip!)
			switch (count($arguments))
			{
				case 0 :
					$result = $this->dbo->$name();
					break;
				case 1 :
					$result = $this->dbo->$name($arguments[0]);
					break;
				case 2:
					$result = $this->dbo->$name($arguments[0], $arguments[1]);
					break;
				case 3:
					$result = $this->dbo->$name($arguments[0], $arguments[1], $arguments[2]);
					break;
				case 4:
					$result = $this->dbo->$name($arguments[0], $arguments[1], $arguments[2], $arguments[3]);
					break;
				case 5:
					$result = $this->dbo->$name($arguments[0], $arguments[1], $arguments[2], $arguments[3], $arguments[4]);
					break;
				default:
					// Resort to using call_user_func_array for many segments
					$result = call_user_func_array([$this->dbo, $name], $arguments);
			}

			return $result;
		}
		else
		{
			throw new Exception('Method ' . $name . ' not found in Akeeba Platform');
		}
	}

	public function __get($name)
	{
		if (isset($this->dbo->$name) || property_exists($this->dbo, $name))
		{
			return $this->dbo->$name;
		}
		else
		{
			$this->dbo->$name = null;

			user_error('Database driver does not support property ' . $name);
		}

		return null;
	}

	public function __set($name, $value)
	{
		if (isset($this->dbo->name) || property_exists($this->dbo, $name))
		{
			$this->dbo->$name = $value;
		}
		else
		{
			$this->dbo->$name = null;
			user_error('Database driver not support property ' . $name);
		}
	}
}
