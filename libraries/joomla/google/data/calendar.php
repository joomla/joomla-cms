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
	 * @param   JRegistry    $options  Google options object.
	 * @param   JGoogleAuth  $auth     Google data http client object.
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
	 * Method to retrieve calendar list from Google
	 *
	 * @param   int     $max     The max results to return.
	 * @param   string  $access  The required access level to list.
	 * @param   int     $page    The page of results to return.
	 * @param   bool    $hidden  Whether hidden calendars should be returned.
	 *
	 * @return  mixed  Data from Google.
	 *
	 * @since   1234
	 */
	public function getCalendarList($max = false, $access = false, $page = false, $hidden = false)
	{
		if ($this->authenticated())
		{
			$params = array();
			$max && ($params['maxResults'] = $max);
			$access && $params['minAccessRole'] = $access;
			$page && $params['pageToken'] = $page;
			$hidden && $params['showHidden'] = $hidden;
			$jdata = $this->auth->query('https://www.googleapis.com/calendar/v3/users/me/calendarList', $params, null, 'get');
			if ($data = json_decode($jdata->body, true))
			{
				return $data;
			}
			else
			{
				throw new Exception('Data could not be decoded.');
			}
		}
		else
		{
			return false;
		}
	}
}
