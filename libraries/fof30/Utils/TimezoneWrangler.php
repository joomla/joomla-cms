<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Utils;

defined('_JEXEC') || die;

use DateTime;
use DateTimeZone;
use Exception;
use FOF30\Container\Container;
use FOF30\Date\Date;
use Joomla\CMS\User\User;

/**
 * A helper class to wrangle timezones, as used by Joomla!.
 *
 * @package  FOF30\Utils
 *
 * @since    3.1.3
 */
class TimezoneWrangler
{
	/**
	 * The default timestamp format string to use when one is not provided
	 *
	 * @var   string
	 */
	protected $defaultFormat = 'Y-m-d H:i:s T';

	/**
	 * When set, this timezone will be used instead of the Joomla! applicable timezone for the user.
	 *
	 * @var DateTimeZone
	 */
	protected $forcedTimezone = null;

	/**
	 * Cache of user IDs to applicable timezones
	 *
	 * @var array
	 */
	protected $userToTimezone = [];

	/**
	 * The component container for which we are created
	 *
	 * @var   Container
	 */
	protected $container;

	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	/**
	 * Get the default timestamp format to use when one is not provided
	 *
	 * @return  string
	 */
	public function getDefaultFormat()
	{
		return $this->defaultFormat;
	}

	/**
	 * Set the default timestamp format to use when one is not provided
	 *
	 * @param   string  $defaultFormat
	 *
	 * @return  void
	 */
	public function setDefaultFormat($defaultFormat)
	{
		$this->defaultFormat = $defaultFormat;
	}

	/**
	 * Returns the forced timezone which is used instead of the applicable Joomla! timezone.
	 *
	 * @return  DateTimeZone
	 */
	public function getForcedTimezone()
	{
		return $this->forcedTimezone;
	}

	/**
	 * Sets the forced timezone which is used instead of the applicable Joomla! timezone. If the new timezone is
	 * different than the existing one we will also reset the user to timezone cache.
	 *
	 * @param   DateTimeZone|string  $forcedTimezone
	 *
	 * @return  void
	 */
	public function setForcedTimezone($forcedTimezone)
	{
		// Are we unsetting the forced TZ?
		if (empty($forcedTimezone))
		{
			$this->forcedTimezone = null;
			$this->resetCache();

			return;
		}

		// If the new TZ is a string we have to create an object
		if (is_string($forcedTimezone))
		{
			$forcedTimezone = new DateTimeZone($forcedTimezone);
		}

		$oldTZ = '';

		if (is_object($this->forcedTimezone) && ($this->forcedTimezone instanceof DateTimeZone))
		{
			$oldTZ = $this->forcedTimezone->getName();
		}

		if ($oldTZ == $forcedTimezone->getName())
		{
			return;
		}

		$this->forcedTimezone = $forcedTimezone;

		$this->resetCache();
	}

	/**
	 * Reset the user to timezone cache. This is done automatically every time you change the forced timezone.
	 */
	public function resetCache()
	{
		$this->userToTimezone = [];
	}

	/**
	 * Get the applicable timezone for a user. If the user is not a guest and they have a timezone set up in their
	 * profile it will be used. Otherwise we fall back to the Server Timezone as set up in Global Configuration. If that
	 * fails, we use GMT. However, if you have used a non-blank forced timezone that will be used instead, circumventing
	 * this calculation. Therefore the returned timezone is one of the following, by descending order of priority:
	 * - Forced timezone
	 * - User's timezone (explicitly set in their user profile)
	 * - Server Timezone (from Joomla's Global Configuration)
	 * - GMT
	 *
	 * @param   User|null  $user
	 *
	 * @return  DateTimeZone
	 */
	public function getApplicableTimezone($user = null)
	{
		// If we have a forced timezone use it instead of trying to figure anything out.
		if (is_object($this->forcedTimezone))
		{
			return $this->forcedTimezone;
		}

		// No user? Get the current user.
		if (is_null($user))
		{
			$user = $this->container->platform->getUser();
		}

		// If there is a cached timezone return that instead.
		if (isset($this->userToTimezone[$user->id]))
		{
			return $this->userToTimezone[$user->id];
		}

		// Prefer the user timezone if it's set.
		if (!$user->guest)
		{
			$tz = $user->getParam('timezone', null);

			if (!empty($tz))
			{
				try
				{
					$this->userToTimezone[$user->id] = new DateTimeZone($tz);

					return $this->userToTimezone[$user->id];
				}
				catch (Exception $e)
				{

				}
			}
		}

		// Get the Server Timezone from Global Configuration with a fallback to GMT
		$tz = $this->container->platform->getConfig()->get('offset', 'GMT');

		try
		{
			$this->userToTimezone[$user->id] = new DateTimeZone($tz);
		}
		catch (Exception $e)
		{
			// If an invalid timezone was set we get to use GMT
			$this->userToTimezone[$user->id] = new DateTimeZone('GMT');
		}

		return $this->userToTimezone[$user->id];
	}

	/**
	 * Returns a FOF Date object with its timezone set to the user's applicable timezone.
	 *
	 * If no user is specified the current user will be used.
	 *
	 * $time can be a DateTime object (including Date and JDate), an integer (UNIX timestamp) or a date string. If no
	 * timezone is specified in a date string we assume it's GMT.
	 *
	 * @param   User   $user  Applicable user for timezone calculation. Null = current user.
	 * @param   mixed  $time  Source time. Leave blank for current date/time.
	 *
	 * @return  Date
	 */
	public function getLocalDateTime($user, $time = null)
	{
		$time = empty($time) ? 'now' : $time;
		$date = new Date($time);
		$tz   = $this->getApplicableTimezone($user);
		$date->setTimezone($tz);

		return $date;
	}

	/**
	 * Returns a FOF Date object with its timezone set to GMT.
	 *
	 * If no user is specified the current user will be used.
	 *
	 * $time can be a DateTime object (including Date and JDate), an integer (UNIX timestamp) or a date string. If no
	 * timezone is specified in a date string we assume it's the user's applicable timezone.
	 *
	 * @param   User   $user
	 * @param   mixed  $time
	 *
	 * @return  Date
	 */
	public function getGMTDateTime($user, $time)
	{
		$time        = empty($time) ? 'now' : $time;
		$tz          = $this->getApplicableTimezone($user);
		$date        = new Date($time, $tz);
		$gmtTimezone = new DateTimeZone('GMT');
		$date->setTimezone($gmtTimezone);

		return $date;
	}

	/**
	 * Returns a formatted date string in the user's applicable timezone.
	 *
	 * If no format is specified we will use $defaultFormat.
	 *
	 * If no user is specified the current user will be used.
	 *
	 * $time can be a DateTime object (including Date and JDate), an integer (UNIX timestamp) or a date string. If no
	 * timezone is specified in a date string we assume it's GMT.
	 *
	 * $translate requires you to have loaded the relevant translation file (e.g. en-GB.ini). JApplicationCms does that
	 * for you automatically. If you're under CLI, a custom JApplicationWeb etc you will probably have to load this
	 * file
	 * manually.
	 *
	 * @param   string|null                    $format     Timestamp format. If empty $defaultFormat is used.
	 * @param   User|null                      $user       Applicable user for timezone calculation. Null = current
	 *                                                     user.
	 * @param   DateTime|Date|string|int|null  $time       Source time. Leave blank for current date/time.
	 * @param   bool                           $translate  Translate day of week and month names?
	 *
	 * @return  string
	 */
	public function getLocalTimeStamp($format = null, $user = null, $time = null, $translate = false)
	{
		$date   = $this->getLocalDateTime($user, $time);
		$format = empty($format) ? $this->defaultFormat : $format;

		return $date->format($format, true, $translate);
	}

	/**
	 * Returns a formatted date string in the GMT timezone.
	 *
	 * If no format is specified we will use $defaultFormat.
	 *
	 * If no user is specified the current user will be used.
	 *
	 * $time can be a DateTime object (including Date and JDate), an integer (UNIX timestamp) or a date string. If no
	 * timezone is specified in a date string we assume it's the user's applicable timezone.
	 *
	 * $translate requires you to have loaded the relevant translation file (e.g. en-GB.ini). JApplicationCms does that
	 * for you automatically. If you're under CLI, a custom JApplicationWeb etc you will probably have to load this
	 * file
	 * manually.
	 *
	 * @param   string|null                    $format     Timestamp format. If empty $defaultFormat is used.
	 * @param   User|null                      $user       Applicable user for timezone calculation. Null = current
	 *                                                     user.
	 * @param   DateTime|Date|string|int|null  $time       Source time. Leave blank for current date/time.
	 * @param   bool                           $translate  Translate day of week and month names?
	 *
	 * @return  string
	 */
	public function getGMTTimeStamp($format = null, $user = null, $time = null, $translate = false)
	{
		$date   = $this->localToGMT($user, $time);
		$format = empty($format) ? $this->defaultFormat : $format;

		return $date->format($format, true, $translate);
	}

	/**
	 * Convert a local time back to GMT. Returns a Date object with its timezone set to GMT.
	 *
	 * This is an alias to getGMTDateTime
	 *
	 * @param   string|Date  $time
	 * @param   User|null    $user
	 *
	 * @return  Date
	 */
	public function localToGMT($time, $user = null)
	{
		return $this->getGMTDateTime($user, $time);
	}

	/**
	 * Convert a GMT time to local timezone. Returns a Date object with its timezone set to the applicable user's TZ.
	 *
	 * This is an alias to getLocalDateTime
	 *
	 * @param   string|Date  $time
	 * @param   User|null    $user
	 *
	 * @return  Date
	 */
	public function GMTToLocal($time, $user = null)
	{
		return $this->getLocalDateTime($user, $time);
	}
}
