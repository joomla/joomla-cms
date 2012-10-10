<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Google
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Google Calendar data class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Google
 * @since       1234
 */
class JGoogleDataCalendar extends JGoogleData
{
	/**
	 * Constructor.
	 *
	 * @param   JRegistry    $options  Google options object
	 * @param   JGoogleAuth  $auth     Google data http client object
	 *
	 * @since   1234
	 */
	public function __construct(JRegistry $options = null, JGoogleAuth $auth = null)
	{
		$options = isset($options) ? $options : new JRegistry;
		if (!$options->get('scope'))
		{
			$options->set('scope', 'https://www.googleapis.com/auth/calendar');
		}
		if (isset($auth) && !$auth->getOption('scope'))
		{
			$auth->setOption('scope', 'https://www.googleapis.com/auth/calendar');
		}

		parent::__construct($options, $auth);
	}

	/**
	 * Method to remove a calendar from a user's calendar list
	 *
	 * @param   string  $calendarID  ID of calendar to delete
	 *
	 * @return  bool  Success or failure
	 *
	 * @since   1234
	 * @throws UnexpectedValueException
	 */
	public function removeCalendar($calendarID)
	{
		if ($this->authenticated())
		{
			$jdata = $this->auth->query('https://www.googleapis.com/calendar/v3/users/me/calendarList/' . $calendarID, null, null, 'delete');
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
	 * @since   1234
	 * @throws UnexpectedValueException
	 */
	public function getCalendar($calendarID)
	{
		if ($this->authenticated())
		{
			$jdata = $this->auth->query('https://www.googleapis.com/calendar/v3/users/me/calendarList/' . $calendarID);
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
	 * @since   1234
	 * @throws UnexpectedValueException
	 */
	public function addCalendar($calendarID, $options = array())
	{
		if ($this->authenticated())
		{
			$options['id'] = $calendarID;
			$url = 'https://www.googleapis.com/calendar/v3/users/me/calendarList';
			$jdata = $this->auth->query($url, json_encode($options), array('Content-type' => 'application/json'), 'post');
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
	 * @since   1234
	 * @throws UnexpectedValueException
	 */
	public function listCalendars($options = array(), $maxpages = 1)
	{
		if ($this->authenticated())
		{
			$url = 'https://www.googleapis.com/calendar/v3/users/me/calendarList?' . implode('&', $options);
			$jdata = $this->auth->query($url);
			$data = json_decode($jdata->body, true);

			if ($data && array_key_exists('items', $data))
			{
				if ($maxpages != 1 && array_key_exists('nextPageToken', $data))
				{
					$data['items'] = array_merge($data['items'], $this->listEvents($options, $maxpages - 1));
				}
				return $data['items'];
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
	 * Method to edit a Google Calendar's settings
	 *
	 * @param   string  $calendarID  Calendar ID
	 * @param   array   $options     Calendar settings
	 *
	 * @return  mixed  Data from Google
	 *
	 * @since   1234
	 * @throws UnexpectedValueException
	 */
	public function editCalendarSettings($calendarID, $options)
	{
		if ($this->authenticated())
		{
			$url = 'https://www.googleapis.com/calendar/v3/users/me/calendarList/' . $calendarID;
			$jdata = $this->auth->query($url, json_encode($options), array('Content-type' => 'application/json'), 'put');
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
	 * @return  bool  Success or failure
	 *
	 * @since   1234
	 * @throws UnexpectedValueException
	 */
	public function clearCalendar($calendarID)
	{
		if ($this->authenticated())
		{
			$data = $this->auth->query('https://www.googleapis.com/calendar/v3/users/me/calendars/' . $calendarID . '/clear', null, null, 'post');
			if ($data->body != '')
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
	 * Method to delete a calendar from Google
	 *
	 * @param   string  $calendarID  ID of calendar to delete.
	 *
	 * @return  bool  Success or failure
	 *
	 * @since   1234
	 * @throws UnexpectedValueException
	 */
	public function deleteCalendar($calendarID)
	{
		if ($this->authenticated())
		{
			$data = $this->auth->query('https://www.googleapis.com/calendar/v3/users/me/calendars/' . $calendarID, null, null, 'delete');
			if ($data->body != '')
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
	 * Method to create a Google Calendar
	 *
	 * @param   string  $title    New calendar title
	 * @param   array   $options  New calendar settings
	 *
	 * @return  mixed  Data from Google.
	 *
	 * @since   1234
	 * @throws UnexpectedValueException
	 */
	public function createCalendar($title, $options = array())
	{
		if ($this->authenticated())
		{
			$options['summary'] = $title;
			$url = 'https://www.googleapis.com/calendar/v3/calendars';
			$jdata = $this->auth->query($url, json_encode($options), array('Content-type' => 'application/json'), 'post');
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
	 * @since   1234
	 * @throws UnexpectedValueException
	 */
	public function editCalendar($calendarID, $options)
	{
		if ($this->authenticated())
		{
			$url = 'https://www.googleapis.com/calendar/v3/users/me/calendars/' . $calendarID;
			$jdata = $this->auth->query($url, json_encode($options), array('Content-type' => 'application/json'), 'put');
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
	 * @return  bool  Success or failure.
	 *
	 * @since   1234
	 * @throws UnexpectedValueException
	 */
	public function deleteEvent($calendarID, $eventID)
	{
		if ($this->authenticated())
		{
			$url = 'https://www.googleapis.com/calendar/v3/users/me/calendars/' . $calendarID . '/events/' . $eventID;
			$jdata = $this->auth->query($url, null, null, 'delete');
			if ($jdata != '')
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
	 * Method to get an event from a Google Calendar
	 *
	 * @param   string  $calendarID  ID of calendar
	 * @param   string  $eventID     ID of event to get
	 * @param   array   $options     Options to send to Google
	 *
	 * @return  mixed  Data from Google.
	 *
	 * @since   1234
	 * @throws UnexpectedValueException
	 */
	public function getEvent($calendarID, $eventID, $options = array())
	{
		if ($this->authenticated())
		{
			$url = 'https://www.googleapis.com/calendar/v3/users/me/calendarList/' . $calendarID . '/events/' . $eventID . '?' . implode('&', $options);
			$jdata = $this->auth->query($url);
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
	 * @param   string  $calendarID  ID of calendar
	 * @param   mixed   $start       Event start time
	 * @param   mixed   $end         Event end time
	 * @param   array   $options     New event settings
	 * @param   mixed   $timezone    Timezone for event
	 * @param   bool    $allday      Treat event as an all-day event
	 * @param   bool    $notify      Notify participants
	 *
	 * @return  mixed  Data from Google.
	 *
	 * @since   1234
	 * @throws InvalidArgumentException
	 * @throws UnexpectedValueException
	 */
	public function createEvent($calendarID, $start, $end = false, $options = array(), $timezone = false, $allday = false, $notify = false)
	{
		if ($this->authenticated())
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
			elseif (is_string($start))
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
			elseif (is_a($end, 'DateTimeZone'))
			{
				$options['start']['timeZone'] = $timezone->getName();
				$options['end']['timeZone'] = $timezone->getName();
			}
			elseif (is_string($timezone))
			{
				$options['start']['timeZone'] = $timezone;
				$options['end']['timeZone'] = $timezone;
			}

			$url = 'https://www.googleapis.com/calendar/v3/calendars/' . $calendarID . '/events' . ($notify ? '?sendNotifications=true' : '');
			$jdata = $this->auth->query($url, json_encode($options), array('Content-type' => 'application/json'), 'post');
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
	 * @since   1234
	 * @throws UnexpectedValueException
	 */
	public function listRecurrences($calendarID, $eventID, $options = array(), $maxpages = 1)
	{
		if ($this->authenticated())
		{
			$url = 'https://www.googleapis.com/calendar/v3/users/me/calendars/' . $calendarID . '/events/' . $eventID . '/instances';
			$url .= '?' . implode('&', $options);
			$jdata = $this->auth->query($url);
			$data = json_decode($jdata->body, true);

			if ($data && array_key_exists('items', $data))
			{
				if ($maxpages != 1 && array_key_exists('nextPageToken', $data))
				{
					$data['items'] = array_merge($data['items'], $this->listRecurrences($calendarID, $eventID, $options, $maxpages - 1));
				}
				return $data['items'];
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
	 * @param   array   $options     Calendar settings
	 * @param   int     $maxpages    Cycle through pages of data to generate a complete list
	 *
	 * @return  mixed  Data from Google.
	 *
	 * @since   1234
	 * @throws UnexpectedValueException
	 */
	public function listEvents($calendarID, $options = array(), $maxpages = 1)
	{
		if ($this->authenticated())
		{
			$url = 'https://www.googleapis.com/calendar/v3/users/me/calendars/' . $calendarID . '?' . implode('&', $options);
			$jdata = $this->auth->query($url);
			$data = json_decode($jdata->body, true);

			if ($data && array_key_exists('items', $data))
			{
				if ($maxpages != 1 && array_key_exists('nextPageToken', $data))
				{
					$data['items'] = array_merge($data['items'], $this->listEvents($calendarID, $options, $maxpages - 1));
				}
				return $data['items'];
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
	 * Method to move an event from one calendar to another
	 *
	 * @param   string  $calendarID  Calendar ID
	 * @param   string  $eventID     ID of the event to change
	 * @param   string  $destID      Calendar ID
	 * @param   bool    $notify      Notify participants of changes
	 *
	 * @return  mixed  Data from Google.
	 *
	 * @since   1234
	 * @throws UnexpectedValueException
	 */
	public function moveEvent($calendarID, $eventID, $destID, $notify = false)
	{
		if ($this->authenticated())
		{
			$url = 'https://www.googleapis.com/calendar/v3/calendars/' . $calendarID . '/events/' . $eventID . '/move';
			$url .= '?destination=' . $destID . ($notify ? '&sendNotifications=true' : '');
			$jdata = $this->auth->query($url, null, null, 'post');
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
	 * @param   string  $calendarID  Calendar ID
	 * @param   string  $eventID     ID of the event to change
	 * @param   array   $options     Event settings
	 * @param   bool    $notify      Notify participants of changes
	 *
	 * @return  mixed  Data from Google.
	 *
	 * @since   1234
	 * @throws UnexpectedValueException
	 */
	public function editEvent($calendarID, $eventID, $options, $notify = false)
	{
		if ($this->authenticated())
		{
			$url = 'https://www.googleapis.com/calendar/v3/calendars/' . $calendarID . '/events/' . $eventID . ($notify ? '?sendNotifications=true' : '');
			$jdata = $this->auth->query($url, json_encode($options), array('Content-type' => 'application/json'), 'put');
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
