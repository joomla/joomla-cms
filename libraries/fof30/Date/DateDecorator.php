<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Date;

defined('_JEXEC') || die;

use DateTime;
use JDatabaseDriver;

/**
 * This decorator will get any DateTime descendant and turn it into a FOF30\Date\Date compatible class. If the methods
 * specific to Date/JDate are available they will be used. Otherwise a new Date object will be spun from the information
 * in the decorated DateTime object and the results of a call to its method will be returned.
 */
class DateDecorator extends Date
{
	/**
	 * The decorated object
	 *
	 * @var   DateTime
	 */
	protected $decorated = null;

	public function __construct($date = 'now', $tz = null)
	{
		if (is_object($date) && ($date instanceof DateTime))
		{
			$this->decorated = $date;
		}
		else
		{
			$this->decorated = new Date($date, $tz);
		}

		$timestamp = $this->decorated->toISO8601(true);

		parent::__construct($timestamp);

		$this->setTimezone($this->decorated->getTimezone());

		return;
	}

	public static function getInstance($date = 'now', $tz = null)
	{
		$coreObject = new Date($date, $tz);

		return new DateDecorator($coreObject);
	}

	/**
	 * Magic method to access properties of the date given by class to the format method.
	 *
	 * @param   string  $name  The name of the property.
	 *
	 * @return  mixed   A value if the property name is valid, null otherwise.
	 */
	public function __get($name)
	{
		return $this->decorated->$name;
	}

	public function __call($name, $arguments)
	{
		if (method_exists($this->decorated, $name))
		{
			return call_user_func_array([$this->decorated, $name], $arguments);
		}

		throw new \InvalidArgumentException("JDate object does not have a $name method");
	}

	public function sub($interval)
	{
		// Note to self: ignore phpStorm; we must NOT use a typehint for $interval
		return $this->decorated->sub($interval);
	}

	public function add($interval)
	{
		// Note to self: ignore phpStorm; we must NOT use a typehint for $interval
		return $this->decorated->add($interval);
	}

	public function modify($modify)
	{
		return $this->decorated->modify($modify);
	}

	public function __toString()
	{
		return (string) $this->decorated;
	}

	public function dayToString($day, $abbr = false)
	{
		return $this->decorated->dayToString($day, $abbr);
	}

	public function calendar($format, $local = false, $translate = true)
	{
		return $this->decorated->calendar($format, $local, $translate);
	}

	public function format($format, $local = false, $translate = true)
	{
		return $this->decorated->format($format, $local, $translate);
	}

	public function getOffsetFromGmt($hours = false)
	{
		return $this->decorated->getOffsetFromGMT($hours);
	}

	public function monthToString($month, $abbr = false)
	{
		return $this->monthToString($month, $abbr);
	}

	public function setTimezone($tz)
	{
		return $this->decorated->setTimezone($tz);
	}

	public function getTimezone()
	{
		return $this->decorated->getTimezone();
	}

	public function getOffset()
	{
		return $this->decorated->getOffset();
	}

	public function toISO8601($local = false)
	{
		return $this->decorated->toISO8601($local);
	}

	public function toSql($local = false, JDatabaseDriver $db = null)
	{
		return $this->decorated->toSql($local, $db);
	}

	public function toRFC822($local = false)
	{
		return $this->decorated->toRFC822($local);
	}

	public function toUnix()
	{
		return $this->decorated->toUnix();
	}

	public function getTimestamp()
	{
		return $this->decorated->getTimestamp();
	}

	public function setTime($hour, $minute, $second = 0, $microseconds = 0)
	{
		return $this->decorated->setTime($hour, $minute, $second, $microseconds);
	}

	public function setDate($year, $month, $day)
	{
		return $this->decorated->setDate($year, $month, $day);
	}

	public function setISODate($year, $week, $day = 1)
	{
		return $this->decorated->setISODate($year, $week, $day);
	}

	public function setTimestamp($unixtimestamp)
	{
		return $this->decorated->setTimestamp($unixtimestamp);
	}

	public function diff($datetime2, $absolute = false)
	{
		return $this->decorated->diff($datetime2, $absolute);
	}
}
