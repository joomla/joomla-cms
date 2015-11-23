<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjlib
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

class CjLibDateUtils
{
	/**
	 * Gets the human friendly date string from a date
	 *
	 * @param string $strdate date
	 *
	 * @return string formatted date string
	 */
	public static function getHumanReadableDate($strdate) 
	{
		if(empty($strdate) || $strdate == '0000-00-00 00:00:00')
		{
			return JText::_('LBL_NA');
		}
	
		jimport('joomla.utilities.date');
		$user = JFactory::getUser();
	
		// Given time
		$date = new JDate(JHtml::date($strdate, 'Y-m-d H:i:s'));
		$compareTo = new JDate(JHtml::date('now', 'Y-m-d H:i:s'));
	
		$diff = $compareTo->toUnix() - $date->toUnix();
		$futureDate = $diff < 0 ? true : false;
		$suffix = $futureDate ? JText::_('COM_CJLIB_DATE_SUFFIX_FROM_NOW') : JText::_('COM_CJLIB_DATE_SUFFIX_AGO');
	
		$diff = abs($diff);
		$dayDiff = floor($diff/86400);
	
		if($dayDiff == 0) 
		{
			if($diff < 60) 
			{
				return JText::_('COM_CJLIB_JUST_NOW');
			} 
			elseif($diff < 120) 
			{
				return JText::sprintf('COM_CJLIB_DATE_ONE_MINUTE', $suffix);
			} 
			elseif($diff < 3600) 
			{
				return JText::sprintf('COM_CJLIB_DATE_N_MINUTES', floor($diff/60), $suffix);
			} 
			elseif($diff < 7200) 
			{
				return JText::sprintf('COM_CJLIB_DATE_ONE_HOUR', $suffix);
			} 
			elseif($diff < 86400) 
			{
				return JText::sprintf('COM_CJLIB_DATE_N_HOURS', floor($diff/3600), $suffix);
			}
		} 
		elseif($dayDiff == 1) 
		{
			return $futureDate ? JText::_('COM_CJLIB_TOMORROW') : JText::_('COM_CJLIB_YESTERDAY');
		} 
		elseif($dayDiff < 7) 
		{
			return JText::sprintf('COM_CJLIB_DATE_N_DAYS', $dayDiff, $suffix);
		} 
		elseif($dayDiff == 7) 
		{
			return JText::sprintf('COM_CJLIB_DATE_ONE_WEEK', $suffix);
		} 
		elseif($dayDiff < (7*6)) 
		{
			return JText::sprintf('COM_CJLIB_DATE_N_WEEKS', ceil($dayDiff/7), $suffix);
		} 
		elseif($dayDiff > 30 && $dayDiff <= 60) 
		{
			return JText::sprintf('COM_CJLIB_DATE_ONE_MONTH', $suffix);
		} 
		elseif($dayDiff < 365) 
		{
			return JText::sprintf('COM_CJLIB_DATE_N_MONTHS', ceil($dayDiff/(365/12)), $suffix);
		} 
		else 
		{
			$years = round($dayDiff/365);
			if($years == 1)
			{
				return JText::sprintf('COM_CJLIB_DATE_ONE_YEAR', $suffix);
			}
			else
			{
				return JText::sprintf('COM_CJLIB_DATE_N_YEARS', round($dayDiff/365), $suffix);
			}
		}
	}
	
	/**
	 * Returns date/time in short format. i.e. 6m, 6h, 6d, 6w, 6m, 6y etc
	 * @param unknown $date
	 * @return Ambigous <string, string, mixed, multitype:>|Ambigous <string, string, mixed>
	 */
	public static function getShortDate($date)
	{
		if(empty($date) || $date == '0000-00-00 00:00:00')
		{
			return JText::_('LBL_NA');
		}
	
		jimport('joomla.utilities.date');
		$user = JFactory::getUser();
	
		// Given time
		$date = new JDate(JHtml::date($date, 'Y-m-d H:i:s'));
		$compareTo = new JDate(JHtml::date('now', 'Y-m-d H:i:s'));
		$diff = $compareTo->toUnix() - $date->toUnix();
	
		$diff = abs($diff);
		$dayDiff = floor($diff/86400);
	
		if($dayDiff == 0)
		{
			if($diff < 120)
			{
				return '1m';
			}
			elseif($diff < 3600)
			{
				return floor($diff/60).'m';
			}
			else
			{
				return floor($diff/3600).'h';
			}
		} elseif($dayDiff < 7)
		{
			return $dayDiff.'d';
		}
		elseif($dayDiff < (7*6))
		{
			return ceil($dayDiff/7).'w';
		}
		elseif($dayDiff < 365)
		{
			return ceil($dayDiff/(365/12)).'m';
		}
		else
		{
			return round($dayDiff/365).'y';
		}
	}
}
