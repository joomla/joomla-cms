<?php
/**
* @version		$Id:timezones.php 6961 2007-03-15 16:06:53Z tcp $
* @package		Joomla.Framework
* @subpackage	Parameter
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

/**
 * Renders a timezones element
 *
 * @author 		Johan Janssens <johan.janssens@joomla.org>
 * @package 	Joomla.Framework
 * @subpackage		Parameter
 * @since		1.5
 */

class JElementTimezones extends JElement
{
   /**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = 'Timezones';

	function fetchElement($name, $value, &$node, $control_name)
	{
		if(!strlen($value)) {
			$conf =& JFactory::getConfig();
			$value = $conf->getValue('config.offset');
		}

		// LOCALE SETTINGS
		$timezones = array (
			JHTMLSelect::option(-12, JText::_('(UTC -12:00) International Date Line West')),
			JHTMLSelect::option(-11, JText::_('(UTC -11:00) Midway Island, Samoa')),
			JHTMLSelect::option(-10, JText::_('(UTC -10:00) Hawaii')),
			JHTMLSelect::option(-9.5, JText::_('(UTC -09:30) Taiohae, Marquesas Islands')),
			JHTMLSelect::option(-9, JText::_('(UTC -09:00) Alaska')),
			JHTMLSelect::option(-8, JText::_('(UTC -08:00) Pacific Time (US &amp; Canada)')),
			JHTMLSelect::option(-7, JText::_('(UTC -07:00) Mountain Time (US &amp; Canada)')),
			JHTMLSelect::option(-6, JText::_('(UTC -06:00) Central Time (US &amp; Canada), Mexico City')),
			JHTMLSelect::option(-5, JText::_('(UTC -05:00) Eastern Time (US &amp; Canada), Bogota, Lima')),
			JHTMLSelect::option(-4, JText::_('(UTC -04:00) Atlantic Time (Canada), Caracas, La Paz')),
			JHTMLSelect::option(-3.5, JText::_('(UTC -03:30) St. John`s, Newfoundland and Labrador')),
			JHTMLSelect::option(-3, JText::_('(UTC -03:00) Brazil, Buenos Aires, Georgetown')),
			JHTMLSelect::option(-2, JText::_('(UTC -02:00) Mid-Atlantic')),
			JHTMLSelect::option(-1, JText::_('(UTC -01:00) Azores, Cape Verde Islands')),
			JHTMLSelect::option(0, JText::_('(UTC 00:00) Western Europe Time, London, Lisbon, Casablanca')),
			JHTMLSelect::option(1, JText::_('(UTC +01:00) Amsterdam, Berlin, Brussels, Copenhagen, Madrid, Paris')),
			JHTMLSelect::option(2, JText::_('(UTC +02:00) Jerusalem, Kaliningrad, South Africa')),
			JHTMLSelect::option(3, JText::_('(UTC +03:00) Baghdad, Riyadh, Moscow, St. Petersburg')),
			JHTMLSelect::option(3.5, JText::_('(UTC +03:30) Tehran')),
			JHTMLSelect::option(4, JText::_('(UTC +04:00) Abu Dhabi, Muscat, Baku, Tbilisi')),
			JHTMLSelect::option(4.5, JText::_('(UTC +04:30) Kabul')),
			JHTMLSelect::option(5, JText::_('(UTC +05:00) Ekaterinburg, Islamabad, Karachi, Tashkent')),
			JHTMLSelect::option(5.5, JText::_('(UTC +05:30) Bombay, Calcutta, Madras, New Delhi')),
			JHTMLSelect::option(5.75, JText::_('(UTC +05:45) Kathmandu')),
			JHTMLSelect::option(6, JText::_('(UTC +06:00) Almaty, Dhaka, Colombo')),
			JHTMLSelect::option(6.30, JText::_('(UTC +06:30) Yagoon')),
			JHTMLSelect::option(7, JText::_('(UTC +07:00) Bangkok, Hanoi, Jakarta')),
			JHTMLSelect::option(8, JText::_('(UTC +08:00) Beijing, Perth, Singapore, Hong Kong')),
			JHTMLSelect::option(8.75, JText::_('(UTC +08:00) Western Australia')),
			JHTMLSelect::option(9, JText::_('(UTC +09:00) Tokyo, Seoul, Osaka, Sapporo, Yakutsk')),
			JHTMLSelect::option(9.5, JText::_('(UTC +09:30) Adelaide, Darwin, Yakutsk')),
			JHTMLSelect::option(10, JText::_('(UTC +10:00) Eastern Australia, Guam, Vladivostok')),
			JHTMLSelect::option(10.5, JText::_('(UTC +10:30) Lord Howe Island (Australia)')),
			JHTMLSelect::option(11, JText::_('(UTC +11:00) Magadan, Solomon Islands, New Caledonia')),
			JHTMLSelect::option(11.30, JText::_('(UTC +11:30) Norfolk Island')),
			JHTMLSelect::option(12, JText::_('(UTC +12:00) Auckland, Wellington, Fiji, Kamchatka')),
			JHTMLSelect::option(12.75, JText::_('(UTC +12:45) Chatham Island')),
			JHTMLSelect::option(13, JText::_('(UTC +13:00) Tonga')),
			JHTMLSelect::option(14, JText::_('(UTC +14:00) Kiribati')),);

		return JHTMLSelect::genericList($timezones, ''.$control_name.'['.$name.']', ' class="inputbox"', 'value', 'text', $value, $control_name.$name );
	}
}