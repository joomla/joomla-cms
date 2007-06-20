<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	Utilities
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/*
if(!defined('DATE_FORMAT_LC')) {
	define('DATE_FORMAT_LC', '%A, %d %B %Y');
}

if(!defined('DATE_FORMAT_LC2')) {
	define('DATE_FORMAT_LC2', '%A, %d %B %Y %H:%M');
}

if(!defined('DATE_FORMAT_LC3')) {
	define('DATE_FORMAT_LC3', '%d %B %Y');
}

if(!defined('DATE_FORMAT_LC4')) {
	define('DATE_FORMAT_LC4', '%d.%m.%y');
}
*/

/**
 * JDate is a class that stores a date
 *
 * @author	Johan Janssens <johan.janssens@joomla.org>
 *
 * @package		Joomla.Framework
 * @subpackage	Utilities
 * @since		1.5
 */
class JDate extends JObject
{
	/**
	 * Unix timestamp
	 *
	 * @var		string
	 * @access	protected
	 */
	var $_date = 0;

	/**
	 * Timeoffset (in hours)
	 *
	 * @var		string
	 * @access	protected
	 */
	var $_offset = 0;

	/**
	 * Creates a new instance of JDate representing a given date.
	 *
	 * Accepts RFC 822, ISO 8601 date formats as well as unix time stamps.
	 * If not specified, the current date and time is used.
	 *
	 * @param mixed $date optional the date this JDate will represent.
	 */
	function __construct($date = 'now', $tzOffset = 0)
	{

		if ($date == 'now' || empty($date))
		{
			$this->_date = gmdate('U');
			return;
		}

		if (is_numeric($date))
		{
			$this->_date = $date + ($tzOffset * 3600);
			return;
		}

		if (preg_match("~(?:(?:Mon|Tue|Wed|Thu|Fri|Sat|Sun),\\s+)?(\\d{1,2})\\s+([a-zA-Z]{3})\\s+(\\d{4})\\s+(\\d{2}):(\\d{2}):(\\d{2})\\s+(.*)~",$date,$matches))
		{
			$months = Array("Jan"=>1,"Feb"=>2,"Mar"=>3,"Apr"=>4,"May"=>5,"Jun"=>6,"Jul"=>7,"Aug"=>8,"Sep"=>9,"Oct"=>10,"Nov"=>11,"Dec"=>12);
			$this->_date = gmmktime($matches[4],$matches[5],$matches[6],$months[$matches[2]],$matches[1],$matches[3]);

			if (substr($matches[7],0,1)=='+' OR substr($matches[7],0,1)=='-') {
				$tzOffset = (substr($matches[7],0,3) * 60 + substr($matches[7],-2)) * 60;
			} else {
				if (strlen($matches[7])==1) {
					$oneHour = 3600;
					$ord = ord($matches[7]);
					if ($ord < ord("M")) {
						$tzOffset = (ord("A") - $ord - 1) * $oneHour;
					} elseif ($ord >= ord("M") AND $matches[7]!="Z") {
						$tzOffset = ($ord - ord("M")) * $oneHour;
					} elseif ($matches[7]=="Z") {
						$tzOffset = 0;
					}
				}
				switch ($matches[7]) {
					case "UT":
					case "GMT":	$tzOffset = 0;
				}
			}
			$this->_date -= $tzOffset;
			return;
		}
		if (preg_match("~(\\d{4})-(\\d{2})-(\\d{2})T(\\d{2}):(\\d{2}):(\\d{2})(.*)~",$date,$matches))
		{
			$this->_date = gmmktime($matches[4],$matches[5],$matches[6],$matches[2],$matches[3],$matches[1]);
			if (substr($matches[7],0,1)=='+' OR substr($matches[7],0,1)=='-') {
				$tzOffset = (substr($matches[7],0,3) * 60 + substr($matches[7],-2)) * 60;
			} else {
				if ($matches[7]=="Z") {
					$tzOffset = 0;
				}
			}
			$this->_date -= $tzOffset;
			return;
		}

		$this->_date = strtotime($date) + $this->serverOffset() - ($tzOffset*3600);
	}

	/**
	 * Set the date offset (in hours)
	 *
	 * @access public
	 * @param integer $offset The offset in hours
	 */
	function setOffset($offset) {
		$this->_offset = $offset;
	}

	/**
	 * Get the date offset (in hours)
	 *
	 * @access public
	 * @return integer
	 */
	function getOffset() {
		return $this->_offset;
	}

	/**
	 * Gets the date as an RFC 822 date.
	 *
	 * @return a date in RFC 822 format
	 * @link http://www.ietf.org/rfc/rfc2822.txt?number=2822 IETF RFC 2822 (replaces RFC 822)
	 */
	function toRFC822()
	{
		$date = date("D, d M Y H:i:s O", $this->_date);
		return $date;
	}

	/**
	 * Gets the date as an ISO 8601 date.
	 *
	 * @return a date in ISO 8601 (RFC 3339) format
	 * @link http://www.ietf.org/rfc/rfc3339.txt?number=3339 IETF RFC 3339
	 */
	function toISO8601()
	{
		$date = date("Y-m-d\TH:i:sP", $this->_date);
		return $date;
	}

	/**
	 * Gets the date as in MySQL datetime format
	 *
	 * @return a date in MySQL datetime format
	 * @link http://dev.mysql.com/doc/refman/4.1/en/datetime.html MySQL DATETIME format
	 */
	function toMySQL()
	{
		$date = gmdate("Y-m-d H:i:s", $this->_date);
		return $date;
	}

	/**
	 * Gets the date as UNIX time stamp.
	 *
	 * @return a date as a unix time stamp
	 */
	function toUnix()
	{
		$date =  $this->_date;
		return $date;
	}

	/**
	 * Gets the date in a specific format
	 *
	 * Returns a string formatted according to the given format. Month and weekday names and
	 * other language dependent strings respect the current locale
	 *
	 * @param string $format  The date format specification string (see {@link PHP_MANUAL#strftime})
	 * @return a date in a specific format
	 */
	function toFormat($format = '%Y-%m-%d %H:%M:%S')
	{
		$date = gmstrftime($format, $this->_date + ($this->_offset * 3600));

		// for Windows there is a need to convert the OS date string to utf-8.
		$lang =& JFactory::getLanguage();
		if ( JUtility::isWinOS() && function_exists('iconv') ) {
			return iconv($lang->getWinCP(), "UTF-8", $date);
		}

		return $date;
	}

	function serverOffset()
	{
		$tz = date('O');

		$tzOffset = ((intval(substr($tz,1,2))*60) + intval(substr($tz,-2)))*60;
		if (substr($tz,0,1) == '-') {
			$tzOffset = -$tzOffset;
		}

		return $tzOffset;
	}

}