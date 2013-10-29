<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Google
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Google Calendar data class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Google
 * @since       12.3
 */
class JGoogleDataCalendar extends JGoogleData
{
	/**
	 * Constructor.
	 *
	 * @param   JRegistry    $options  Google options object
	 * @param   JGoogleAuth  $auth     Google data http client object
	 *
	 * @since   12.3
	 */
	public function __construct(JRegistry $options = null, JGoogleAuth $auth = null)
	{
		parent::__construct($options, $auth);

		if (isset($this->auth) && !$this->auth->getOption('scope'))
		{
			$this->auth->setOption('scope', 'https://www.googleapis.com/auth/calendar');
		}
	}

	/**
	 * Method to remove a calendar from a user's calendar list
	 *
	 * @param   string  $calendarID  ID of calendar to delete
	 *
	 * @return  boolean  Success or failure
	 *
	 * @since   12.3
	 * @throws UnexpectedValueException
	 */
	public function removeCalendar($calendarID)
	{
		if ($this->isAuthenticated())
		{
			$jdata = $this->query('https://www.googleapis.com/calendar/v3/users/me/calendarList/' . urlencode($calendarID), null, null, 'delete');

			if ($jdata->body != '')
			{
				throw new UnexpectedValueException("Unexpected data received from Google: `{$jdata->body}`.");
			}
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to get a calendar's settings from Google
	 *
	 * @param   string  $calendarID  ID of calendar to get.
	 *
	 * @return  mixed  Data from Google
	 *
	 * @since   12.3
	 * @throws UnexpectedValueException
	 */
	public function getCalendar($calendarID)
	{
		if ($this->isAuthenticated())
		{
			$jdata = $this->query('https://www.googleapis.com/calendar/v3/users/me/calendarList/' . urlencode($calendarID));

			if ($data = json_decode($jdata->body, true))
			{
				return $data;
			}
			else
			{
				throw new UnexpectedValueException("Unexpected data received from Google: `{$jdata->body}`.");
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to add a calendar to a user's Google Calendar list
	 *
	 * @param   string  $calendarID  New calendar ID
	 * @param   array   $options     New calendar settings
	 *
	 * @return  mixed  Data from Google
	 *
	 * @since   12.3
	 * @throws UnexpectedValueException
	 */
	public function addCalendar($calendarID, $options = array())
	{
		if ($this->isAuthenticated())
		{
			$options['id'] = $calendarID;
			$url = 'https://www.googleapis.com/calendar/v3/users/me/calendarList';
			$jdata = $this->query($url, json_encode($options), array('Content-type' => 'application/json'), 'post');

			if ($data = json_decode($jdata->body, true))
			{
				return $data;
			}
			else
			{
				throw new UnexpectedValueException("Unexpected data received from Google: `{$jdata->body}`.");
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to retrieve calendar list from Google
	 *
	 * @param   array  $options   Search settings
	 * @param   int    $maxpages  Maximum number of pages of calendars to return
	 *
	 * @return  mixed  Data from Google
	 *
	 * @since   12.3
	 * @throws UnexpectedValueException
	 */
	public function listCalendars($options = array(), $maxpages = 1)
	{
		if ($this->isAuthenticated())
		{
			$next = array_key_exists('nextPageToken', $options) ? $options['nextPage'] : null;
			unset($options['nextPageToken']);
			$url = 'https://www.googleapis.com/calendar/v3/users/me/calendarList?' . http_build_query($options);

			return $this->listGetData($url, $maxpages, $next);
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to edit a Google Calendar's settings
	 *
	 * @param   string  $calendarID  Calendar ID
	 * @param   array   $options     Calendar settings
	 *
	 * @return  mixed  Data from Google
	 *
	 * @since   12.3
	 * @throws UnexpectedValueException
	 */
	public function editCalendarSettings($calendarID, $options)
	{
		if ($this->isAuthenticated())
		{
			$url = 'https://www.googleapis.com/calendar/v3/users/me/calendarList/' . urlencode($calendarID);
			$jdata = $this->query($url, json_encode($options), array('Content-type' => 'application/json'), 'put');

			if ($data = json_decode($jdata->body, true))
			{
				return $data;
			}
			else
			{
				throw new UnexpectedValueException("Unexpected data received from Google: `{$jdata->body}`.");
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to clear a Google Calendar
	 *
	 * @param   string  $calendarID  ID of calendar to clear
	 *
	 * @return  boolean  Success or failure
	 *
	 * @since   12.3
	 * @throws UnexpectedValueException
	 */
	public function clearCalendar($calendarID)
	{
		if ($this->isAuthenticated())
		{
			$data = $this->query('https://www.googleapis.com/calendar/v3/users/me/calendars/' . urlencode($calendarID) . '/clear', null, null, 'post');

			if ($data->body != '')
			{
				throw new UnexpectedValueException("Unexpected data received from Google: `{$data->body}`.");
			}
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to delete a calendar from Google
	 *
	 * @param   string  $calendarID  ID of calendar to delete.
	 *
	 * @return  boolean  Success or failure
	 *
	 * @since   12.3
	 * @throws UnexpectedValueException
	 */
	public function deleteCalendar($calendarID)
	{
		if ($this->isAuthenticated())
		{
			$data = $this->query('https://www.googleapis.com/calendar/v3/users/me/calendars/' . urlencode($calendarID), null, null, 'delete');

			if ($data->body != '')
			{
				throw new UnexpectedValueException("Unexpected data received from Google: `{$data->body}`.");
			}
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to create a Google Calendar
	 *
	 * @param   string  $title    New calendar title
	 * @param   array   $options  New calendar settings
	 *
	 * @return  mixed  Data from Google.
	 *
	 * @since   12.3
	 * @throws UnexpectedValueException
	 */
	public function createCalendar($title, $options = array())
	{
		if ($this->isAuthenticated())
		{
			$options['summary'] = $title;
			$url = 'https://www.googleapis.com/calendar/v3/calendars';
			$jdata = $this->query($url, json_encode($options), array('Content-type' => 'application/json'), 'post');

			if ($data = json_decode($jdata->body, true))
			{
				return $data;
			}
			else
			{
				throw new UnexpectedValueException("Unexpected data received from Google: `{$jdata->body}`.");
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to edit a Google Calendar
	 *
	 * @param   string  $calendarID  Calendar ID.
	 * @param   array   $options     Calendar settings.
	 *
	 * @return  mixed  Data from Google.
	 *
	 * @since   12.3
	 * @throws UnexpectedValueException
	 */
	public function editCalendar($calendarID, $options)
	{
		if ($this->isAuthenticated())
		{
			$url = 'https://www.googleapis.com/calendar/v3/users/me/calendars/' . urlencode($calendarID);
			$jdata = $this->query($url, json_encode($options), array('Content-type' => 'application/json'), 'put');
			$data = json_decode($jdata->body, true);

			if ($data && array_key_exists('items', $data))
			{
				return $data;
			}
			else
			{
				throw new UnexpectedValueException("Unexpected data received from Google: `{$jdata->body}`.");
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to delete an event from a Google Calendar
	 *
	 * @param   string  $calendarID  ID of calendar to delete from
	 * @param   string  $eventID     ID of event to delete.
	 *
	 * @return  boolean  Success or failure.
	 *
	 * @since   12.3
	 * @throws UnexpectedValueException
	 */
	public function deleteEvent($calendarID, $eventID)
	{
		if ($this->isAuthenticated())
		{
			$url = 'https://www.googleapis.com/calendar/v3/users/me/calendars/' . urlencode($calendarID) . '/events/' . urlencode($eventID);
			$data = $this->query($url, null, null, 'delete');

			if ($data->body != '')
			{
				throw new UnexpectedValueException("Unexpected data received from Google: `{$data->body}`.");
			}
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to get an event from a Google Calendar
	 *
	 * @param   string  $calendarID  ID of calendar
	 * @param   string  $eventID     ID of event to get
	 * @param   array   $options     Options to send to Google
	 *
	 * @return  mixed  Data from Google.
	 *
	 * @since   12.3
	 * @throws UnexpectedValueException
	 */
	public function getEvent($calendarID, $eventID, $options = array())
	{
		if ($this->isAuthenticated())
		{
			$url = 'https://www.googleapis.com/calendar/v3/users/me/calendarList/';
			$url .= urlencode($calendarID) . '/events/' . urlencode($eventID) . '?' . http_build_query($options);
			$jdata = $this->query($url);

			if ($data = json_decode($jdata->body, true))
			{
				return $data;
			}
			else
			{
				throw new UnexpectedValueException("Unexpected data received from Google: `{$jdata->body}`.");
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to create a Google Calendar event
	 *
	 * @param   string   $calendarID  ID of calendar
	 * @param   mixed    $start       Event start time
	 * @param   mixed    $end         Event end time
	 * @param   array    $options     New event settings
	 * @param   mixed    $timezone    Timezone for event
	 * @param   boolean  $allday      Treat event as an all-day event
	 * @param   boolean  $notify      Notify participants
	 *
	 * @return  mixed  Data from Google.
	 *
	 * @since   12.3
	 * @throws InvalidArgumentException
	 * @throws UnexpectedValueException
	 */
	public function createEvent($calendarID, $start, $end = false, $options = array(), $timezone = false, $allday = false, $notify = false)
	{
		if ($this->isAuthenticated())
		{
			if (!$start)
			{
				$startobj = new DateTime;
			}
			elseif (is_int($start))
			{
				$startobj = new DateTime;
				$startobj->setTimestamp($start);
			}
			elseif (is_string($start))
			{
				$startobj = new DateTime($start);
			}
			elseif (is_a($start, 'DateTime'))
			{
				$startobj = $start;
			}
			else
			{
				throw new InvalidArgumentException('Invalid event start time.');
			}

			if (!$end)
			{
				$endobj = $startobj;
			}
			elseif (is_int($end))
			{
				$endobj = new DateTime;
				$endobj->setTimestamp($end);
			}
			elseif (is_string($end))
			{
				$endobj = new DateTime($end);
			}
			elseif (is_a($end, 'DateTime'))
			{
				$endobj = $end;
			}
			else
			{
				throw new InvalidArgumentException('Invalid event end time.');
			}

			if ($allday)
			{
				$options['start'] = array('date' => $startobj->format('Y-m-d'));
				$options['end'] = array('date' => $endobj->format('Y-m-d'));
			}
			else
			{
				$options['start'] = array('dateTime' => $startobj->format(DateTime::RFC3339));
				$options['end'] = array('dateTime' => $endobj->format(DateTime::RFC3339));
			}

			if ($timezone === true)
			{
				$options['start']['timeZone'] = $startobj->getTimezone()->getName();
				$options['end']['timeZone'] = $endobj->getTimezone()->getName();
			}
			elseif (is_a($timezone, 'DateTimeZone'))
			{
				$options['start']['timeZone'] = $timezone->getName();
				$options['end']['timeZone'] = $timezone->getName();
			}
			elseif (is_string($timezone))
			{
				$options['start']['timeZone'] = $timezone;
				$options['end']['timeZone'] = $timezone;
			}

			$url = 'https://www.googleapis.com/calendar/v3/calendars/' . urlencode($calendarID) . '/events' . ($notify ? '?sendNotifications=true' : '');
			$jdata = $this->query($url, json_encode($options), array('Content-type' => 'application/json'), 'post');

			if ($data = json_decode($jdata->body, true))
			{
				return $data;
			}
			else
			{
				throw new UnexpectedValueException("Unexpected data received from Google: `{$jdata->body}`.");
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to retrieve a list of events on a Google calendar
	 *
	 * @param   string  $calendarID  Calendar ID
	 * @param   string  $eventID     ID of the event to change
	 * @param   array   $options     Search settings
	 * @param   int     $maxpages    Minimum number of events to retrieve (more may be retrieved depending on page size)
	 *
	 * @return  mixed  Data from Google.
	 *
	 * @since   12.3
	 * @throws UnexpectedValueException
	 */
	public function listRecurrences($calendarID, $eventID, $options = array(), $maxpages = 1)
	{
		if ($this->isAuthenticated())
		{
			$next = array_key_exists('nextPageToken', $options) ? $options['nextPage'] : null;
			unset($options['nextPageToken']);
			$url = 'https://www.googleapis.com/calendar/v3/users/me/calendars/' . urlencode($calendarID) . '/events/' . urlencode($eventID) . '/instances';
			$url .= '?' . http_build_query($options);

			return $this->listGetData($url, $maxpages, $next);
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to retrieve a list of events on a Google calendar
	 *
	 * @param   string  $calendarID  Calendar ID
	 * @param   array   $options     Calendar settings
	 * @param   int     $maxpages    Cycle through pages of data to generate a complete list
	 *
	 * @return  mixed  Data from Google.
	 *
	 * @since   12.3
	 * @throws UnexpectedValueException
	 */
	public function listEvents($calendarID, $options = array(), $maxpages = 1)
	{
		if ($this->isAuthenticated())
		{
			$next = array_key_exists('nextPageToken', $options) ? $options['nextPage'] : null;
			unset($options['nextPageToken']);
			$url = 'https://www.googleapis.com/calendar/v3/calendars/' . urlencode($calendarID) . '/events?' . http_build_query($options);

			return $this->listGetData($url, $maxpages, $next);
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to move an event from one calendar to another
	 *
	 * @param   string   $calendarID  Calendar ID
	 * @param   string   $eventID     ID of the event to change
	 * @param   string   $destID      Calendar ID
	 * @param   boolean  $notify      Notify participants of changes
	 *
	 * @return  mixed  Data from Google.
	 *
	 * @since   12.3
	 * @throws UnexpectedValueException
	 */
	public function moveEvent($calendarID, $eventID, $destID, $notify = false)
	{
		if ($this->isAuthenticated())
		{
			$url = 'https://www.googleapis.com/calendar/v3/calendars/' . urlencode($calendarID) . '/events/' . urlencode($eventID) . '/move';
			$url .= '?destination=' . $destID . ($notify ? '&sendNotifications=true' : '');
			$jdata = $this->query($url, null, null, 'post');

			if ($data = json_decode($jdata->body, true))
			{
				return $data;
			}
			else
			{
				throw new UnexpectedValueException("Unexpected data received from Google: `{$jdata->body}`.");
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to edit a Google Calendar event
	 *
	 * @param   string   $calendarID  Calendar ID
	 * @param   string   $eventID     ID of the event to change
	 * @param   array    $options     Event settings
	 * @param   boolean  $notify      Notify participants of changes
	 *
	 * @return  mixed  Data from Google.
	 *
	 * @since   12.3
	 * @throws UnexpectedValueException
	 */
	public function editEvent($calendarID, $eventID, $options, $notify = false)
	{
		if ($this->isAuthenticated())
		{
			$url = 'https://www.googleapis.com/calendar/v3/calendars/';
			$url .= urlencode($calendarID) . '/events/' . urlencode($eventID) . ($notify ? '?sendNotifications=true' : '');
			$jdata = $this->query($url, json_encode($options), array('Content-type' => 'application/json'), 'put');

			if ($data = json_decode($jdata->body, true))
			{
				return $data;
			}
			else
			{
				throw new UnexpectedValueException("Unexpected data received from Google: `{$jdata->body}`.");
			}
		}
		else
		{
			return false;
		}
	}
}
