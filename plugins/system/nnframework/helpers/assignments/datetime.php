<?php
/**
 * NoNumber Framework Helper File: Assignments: DateTime
 *
 * @package			NoNumber Framework
 * @version			12.6.4
 *
 * @author			Peter van Westen <peter@nonumber.nl>
 * @link			http://www.nonumber.nl
 * @copyright		Copyright © 2012 NoNumber All Rights Reserved
 * @license			http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Assignments: DateTime
 */
class NNFrameworkAssignmentsDateTime
{
	var $_version = '12.6.4';

	/**
	 * passDate
	 *
	 * @param <object> $params
	 * publish_up
	 * publish_down
	 * @param <array> $selection
	 * @param <string> $assignment
	 *
	 * @return <bool>
	 */
	function passDate(&$main, &$params, $selection = array(), $assignment = 'all')
	{
		if ($params->publish_up || $params->publish_down) {
			$now = strtotime($main->_date->format('Y-m-d H:i:s')) + $main->_date->getOffsetFromGMT();
			if ((int) $params->publish_up) {
				$publish_up = JFactory::getDate($params->publish_up);
				$publish_up = $publish_up->toUnix();

				if ($publish_up > $now) {
					// outside date range
					return ($assignment == 'exclude');
				}
			}
			if ((int) $params->publish_down) {
				$publish_down = JFactory::getDate($params->publish_down);
				$publish_down = $publish_down->toUnix();
				if ($publish_down < $now) {
					// outside date range
					return ($assignment == 'exclude');
				}
			}
		}
		// no date range set
		return ($assignment == 'include');
	}

	/**
	 * passSeasons
	 *
	 * @param <object> $params
	 * @param <array> $selection
	 * @param <string> $assignment
	 *
	 * @return <bool>
	 */
	function passSeasons(&$main, &$params, $selection = array(), $assignment = 'all')
	{
		$season = NNFrameworkAssignmentsDateTime::getSeason($main->_date, $params->hemisphere);
		return $main->passSimple($season, $selection, $assignment);
	}

	/**
	 * passSeason
	 *
	 * @param <object> $params
	 * @param <array> $selection
	 * @param <string> $assignment
	 *
	 * @return <bool>
	 */
	function passMonths(&$main, &$params, $selection = array(), $assignment = 'all')
	{
		$month = $main->_date->toFormat('%m', 1); // 01 (for January) through 12 (for December)
		return $main->passSimple((int) $month, $selection, $assignment);
	}

	/**
	 * passDays
	 *
	 * @param <object> $params
	 * @param <array> $selection
	 * @param <string> $assignment
	 *
	 * @return <bool>
	 */
	function passDays(&$main, &$params, $selection = array(), $assignment = 'all')
	{
		$day = $main->_date->toFormat('%w', 1); // 0 (for Sunday) though 6 (for Saturday )
		if (!$day) {
			$day = 7; // Change 0 to 7 for matching Sunday
		}

		return $main->passSimple($day, $selection, $assignment);
	}

	/**
	 * passDays
	 *
	 * @param <object> $params
	 * @param <array> $selection
	 * @param <string> $assignment
	 *
	 * @return <bool>
	 */
	function passTime(&$main, &$params, $selection = array(), $assignment = 'all')
	{
		$date = strtotime($main->_date->format('Y-m-d H:i:s')) + $main->_date->getOffsetFromGMT();

		$publish_up = strtotime($params->publish_up);
		$publish_down = strtotime($params->publish_down);

		$pass = 0;
		if ($publish_up > $publish_down) {
			// publish up is after publish down (spans midnight)
			// current time should be:
			// - after publish up
			// - OR before publish down
			if ($date >= $publish_up || $date < $publish_down) {
				$pass = 1;
			}
		} else {
			// publish down is after publish up (simple time span)
			// current time should be:
			// - after publish up
			// - AND before publish down
			if ($date >= $publish_up && $date < $publish_down) {
				$pass = 1;
			}
		}

		if ($pass) {
			return ($assignment == 'include');
		} else {
			return ($assignment == 'exclude');
		}
	}

	/**
	 * getSeason
	 *
	 * @param <string> $hemisphere (northern, southern, australia)
	 */
	function getSeason(&$d, $hemisphere = 'northern')
	{
		// Set $date to today
		$date = strtotime($d->format('Y-m-d H:i:s')) + $d->getOffsetFromGMT();

		// Get year of date specified
		$date_year = $d->toFormat('%Y', 1); // Four digit representation for the year

		// Specify the season names
		$season_names = array('winter', 'spring', 'summer', 'fall');

		// Declare season date ranges
		switch (strtolower($hemisphere)) {
			case 'southern':
				if (
					$date < strtotime($date_year.'-03-21')
					|| $date >= strtotime($date_year.'-12-21')
				) {
					return $season_names['2']; // Must be in Summer
				} else if ($date >= strtotime($date_year.'-09-23')) {
					return $season_names['1']; // Must be in Spring
				} else if ($date >= strtotime($date_year.'-06-21')) {
					return $season_names['0']; // Must be in Winter
				} else if ($date >= strtotime($date_year.'-03-21')) {
					return $season_names['3']; // Must be in Fall
				}
				break;
			case 'australia':
				if (
					$date < strtotime($date_year.'-03-01')
					|| $date >= strtotime($date_year.'-12-01')
				) {
					return $season_names['2']; // Must be in Summer
				} else if ($date >= strtotime($date_year.'-09-01')) {
					return $season_names['1']; // Must be in Spring
				} else if ($date >= strtotime($date_year.'-06-01')) {
					return $season_names['0']; // Must be in Winter
				} else if ($date >= strtotime($date_year.'-03-01')) {
					return $season_names['3']; // Must be in Fall
				}
				break;
			default: // northern
				if (
					$date < strtotime($date_year.'-03-21')
					|| $date >= strtotime($date_year.'-12-21')
				) {
					return $season_names['0']; // Must be in Winter
				} else if ($date >= strtotime($date_year.'-09-23')) {
					return $season_names['3']; // Must be in Fall
				} else if ($date >= strtotime($date_year.'-06-21')) {
					return $season_names['2']; // Must be in Summer
				} else if ($date >= strtotime($date_year.'-03-21')) {
					return $season_names['1']; // Must be in Spring
				}
				break;
		}
		return 0;
	}
}