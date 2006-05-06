<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

jimport( 'joomla.common.base.object' );

// your local timezone, set to "" to disable or for GMT
define("TIME_ZONE","+01:00");

/**
 * JDate is a class that stores a date
 *
 * @author	Johan Janssens <johan.janssens@joomla.org>
 *
 * @package 	Joomla.Framework
 * @subpackage 	Utilities
 * @since	1.5
 */
class JDate extends JObject
{
	/**
	 * Date in unix format
	 * 
	 * @var		string
	 * @access	protected 	
	 */
	var $_date = "";

	/**
	 * Creates a new instance of JDate representing a given date.
	 * Accepts RFC 822, ISO 8601 date formats as well as unix time stamps.
	 * 
	 * @param mixed $date optional the date this FeedDate will represent. If not specified, the current date and time is used.
	 */
	function __construct($date = "") 
	{
		if ($date == "") {
			$date = date("r");
		}

		if (is_numeric($date)) 
		{
			$this->_date = $date;
			return;
		}
		
		if (preg_match("~(?:(?:Mon|Tue|Wed|Thu|Fri|Sat|Sun),\\s+)?(\\d{1,2})\\s+([a-zA-Z]{3})\\s+(\\d{4})\\s+(\\d{2}):(\\d{2}):(\\d{2})\\s+(.*)~",$date,$matches)) 
		{
			$months = Array("Jan"=>1,"Feb"=>2,"Mar"=>3,"Apr"=>4,"May"=>5,"Jun"=>6,"Jul"=>7,"Aug"=>8,"Sep"=>9,"Oct"=>10,"Nov"=>11,"Dec"=>12);
			$this->unix = mktime($matches[4],$matches[5],$matches[6],$months[$matches[2]],$matches[1],$matches[3]);
			
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
			$this->_date += $tzOffset;
			return;
		}
		if (preg_match("~(\\d{4})-(\\d{2})-(\\d{2})T(\\d{2}):(\\d{2}):(\\d{2})(.*)~",$date,$matches)) 
		{
			$this->_date = mktime($matches[4],$matches[5],$matches[6],$matches[2],$matches[3],$matches[1]);
			if (substr($matches[7],0,1)=='+' OR substr($matches[7],0,1)=='-') {
				$tzOffset = (substr($matches[7],0,3) * 60 + substr($matches[7],-2)) * 60;
			} else {
				if ($matches[7]=="Z") {
					$tzOffset = 0;
				}
			}
			$this->unix += $tzOffset;
			return;
		}
		
		$this->_date = 0;
	}

	/**
	 * Gets the date stored in this FeedDate as an RFC 822 date.
	 *
	 * @return a date in RFC 822 format
	 */
	function toRCF822() 
	{
		$date = gmdate("D, d M Y H:i:s", $this->_date);
		if (TIME_ZONE!="") $date .= " ".str_replace(":","",TIME_ZONE);
		return $date;
	}

	/**
	 * Gets the date as an ISO 8601 date.
	 *
	 * @return a date in ISO 8601 (RFC 3339) format
	 */
	function toISO8601() 
	{
		$date = gmdate("Y-m-d\TH:i:sO",$this->_date);
		$date = substr($date,0,22) . ':' . substr($date,-2);
		if (TIME_ZONE!="") $date = str_replace("+00:00",TIME_ZONE,$date);
		return $date;
	}

	/**
	 * Gets the date as unix time stamp.
	 *
	 * @return a date as a unix time stamp
	 */
	function toUnix() {
		return $this->_date;
	}
}

?>