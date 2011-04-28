<?php
/**
 * @package     Joomla.Platform
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Renders a timezones element
 *
 * @package		Joomla.Platform
 * @subpackage		Parameter
 * @since		11.1
 * @deprecated	JParameter is deprecated and will be removed in a future version. Use JForm instead.
 */

class JElementTimezones extends JElement
{
	/**
	* Element name
	*
	* @var		string
	*/
	protected $_name = 'Timezones';

	public function fetchElement($name, $value, &$node, $control_name)
	{
		if (!strlen($value)) {
			$conf = JFactory::getConfig();
			$value = $conf->get('offset');
		}

		// LOCALE SETTINGS
		$timezones = array (
			JHtml::_('select.option', -12, JText::_('UTC__12_00__INTERNATIONAL_DATE_LINE_WEST')),
			JHtml::_('select.option', -11, JText::_('UTC__11_00__MIDWAY_ISLAND__SAMOA')),
			JHtml::_('select.option', -10, JText::_('UTC__10_00__HAWAII')),
			JHtml::_('select.option', -9.5, JText::_('UTC__09_30__TAIOHAE__MARQUESAS_ISLANDS')),
			JHtml::_('select.option', -9, JText::_('UTC__09_00__ALASKA')),
			JHtml::_('select.option', -8, JText::_('UTC__08_00__PACIFIC_TIME__US__AMP__CANADA_')),
			JHtml::_('select.option', -7, JText::_('UTC__07_00__MOUNTAIN_TIME__US__AMP__CANADA_')),
			JHtml::_('select.option', -6, JText::_('UTC__06_00__CENTRAL_TIME__US__AMP__CANADA___MEXICO_CITY')),
			JHtml::_('select.option', -5, JText::_('UTC__05_00__EASTERN_TIME__US__AMP__CANADA___BOGOTA__LIMA')),
			JHtml::_('select.option', -4, JText::_('UTC__04_00__ATLANTIC_TIME__CANADA___CARACAS__LA_PAZ')),
			JHtml::_('select.option', -4.5, JText::_('UTC__04_30__VENEZUELA')),
			JHtml::_('select.option', -3.5, JText::_('UTC__03_30__ST__JOHN_S__NEWFOUNDLAND__LABRADOR')),
			JHtml::_('select.option', -3, JText::_('UTC__03_00__BRAZIL__BUENOS_AIRES__GEORGETOWN')),
			JHtml::_('select.option', -2, JText::_('UTC__02_00__MID_ATLANTIC')),
			JHtml::_('select.option', -1, JText::_('UTC__01_00__AZORES__CAPE_VERDE_ISLANDS')),
			JHtml::_('select.option', 0, JText::_('UTC_00_00__WESTERN_EUROPE_TIME__LONDON__LISBON__CASABLANCA')),
			JHtml::_('select.option', 1, JText::_('UTC__01_00__AMSTERDAM__BERLIN__BRUSSELS__COPENHAGEN__MADRID__PARIS')),
			JHtml::_('select.option', 2, JText::_('UTC__02_00__ISTANBUL__JERUSALEM__KALININGRAD__SOUTH_AFRICA')),
			JHtml::_('select.option', 3, JText::_('UTC__03_00__BAGHDAD__RIYADH__MOSCOW__ST__PETERSBURG')),
			JHtml::_('select.option', 3.5, JText::_('UTC__03_30__TEHRAN')),
			JHtml::_('select.option', 4, JText::_('UTC__04_00__ABU_DHABI__MUSCAT__BAKU__TBILISI')),
			JHtml::_('select.option', 4.5, JText::_('UTC__04_30__KABUL')),
			JHtml::_('select.option', 5, JText::_('UTC__05_00__EKATERINBURG__ISLAMABAD__KARACHI__TASHKENT')),
			JHtml::_('select.option', 5.5, JText::_('UTC__05_30__BOMBAY__CALCUTTA__MADRAS__NEW_DELHI__COLOMBO')),
			JHtml::_('select.option', 5.75, JText::_('UTC__05_45__KATHMANDU')),
			JHtml::_('select.option', 6, JText::_('UTC__06_00__ALMATY__DHAKA')),
			JHtml::_('select.option', 6.5, JText::_('UTC__06_30__YAGOON')),
			JHtml::_('select.option', 7, JText::_('UTC__07_00__BANGKOK__HANOI__JAKARTA__PHNOM_PENH')),
			JHtml::_('select.option', 8, JText::_('UTC__08_00__BEIJING__PERTH__SINGAPORE__HONG_KONG')),
			JHtml::_('select.option', 8.75, JText::_('UTC__08_00__WESTERN_AUSTRALIA')),
			JHtml::_('select.option', 9, JText::_('UTC__09_00__TOKYO__SEOUL__OSAKA__SAPPORO__YAKUTSK')),
			JHtml::_('select.option', 9.5, JText::_('UTC__09_30__ADELAIDE__DARWIN__YAKUTSK')),
			JHtml::_('select.option', 10, JText::_('UTC__10_00__EASTERN_AUSTRALIA__GUAM__VLADIVOSTOK')),
			JHtml::_('select.option', 10.5, JText::_('UTC__10_30__LORD_HOWE_ISLAND__AUSTRALIA_')),
			JHtml::_('select.option', 11, JText::_('UTC__11_00__MAGADAN__SOLOMON_ISLANDS__NEW_CALEDONIA')),
			JHtml::_('select.option', 11.5, JText::_('UTC__11_30__NORFOLK_ISLAND')),
			JHtml::_('select.option', 12, JText::_('UTC__12_00__AUCKLAND__WELLINGTON__FIJI__KAMCHATKA')),
			JHtml::_('select.option', 12.75, JText::_('UTC__12_45__CHATHAM_ISLAND')),
			JHtml::_('select.option', 13, JText::_('UTC__13_00__TONGA')),
			JHtml::_('select.option', 14, JText::_('UTC__14_00__KIRIBATI')),);

		return JHtml::_('select.genericlist', $timezones, $control_name.'['.$name.']',
			array(
				'id' => $control_name.$name,
				'list.attr' => 'class="inputbox"',
				'list.select' => $value
			)
		);
	}
}
