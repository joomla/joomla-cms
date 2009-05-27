<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
require_once dirname(__FILE__).DS.'list.php';

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		1.6
 */
class JFormFieldTimezones extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	public $type = 'Timezones';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array		An array of JHtml options.
	 */
	protected function _getOptions()
	{
		if (strlen($this->value) == 0) {
			$conf = &JFactory::getConfig();
			$value = $conf->getValue('config.offset');
		}

		// LOCALE SETTINGS
		$options = array (
			JHtml::_('select.option', -12, JText::_('(UTC -12:00) International Date Line West')),
			JHtml::_('select.option', -11, JText::_('(UTC -11:00) Midway Island, Samoa')),
			JHtml::_('select.option', -10, JText::_('(UTC -10:00) Hawaii')),
			JHtml::_('select.option', -9.5, JText::_('(UTC -09:30) Taiohae, Marquesas Islands')),
			JHtml::_('select.option', -9, JText::_('(UTC -09:00) Alaska')),
			JHtml::_('select.option', -8, JText::_('(UTC -08:00) Pacific Time (US &amp; Canada)')),
			JHtml::_('select.option', -7, JText::_('(UTC -07:00) Mountain Time (US &amp; Canada)')),
			JHtml::_('select.option', -6, JText::_('(UTC -06:00) Central Time (US &amp; Canada), Mexico City')),
			JHtml::_('select.option', -5, JText::_('(UTC -05:00) Eastern Time (US &amp; Canada), Bogota, Lima')),
			JHtml::_('select.option', -4, JText::_('(UTC -04:00) Atlantic Time (Canada), Caracas, La Paz')),
			JHtml::_('select.option', -4.5, JText::_('(UTC -04:30) Venezuela')),
			JHtml::_('select.option', -3.5, JText::_('(UTC -03:30) St. John\'s, Newfoundland, Labrador')),
			JHtml::_('select.option', -3, JText::_('(UTC -03:00) Brazil, Buenos Aires, Georgetown')),
			JHtml::_('select.option', -2, JText::_('(UTC -02:00) Mid-Atlantic')),
			JHtml::_('select.option', -1, JText::_('(UTC -01:00) Azores, Cape Verde Islands')),
			JHtml::_('select.option', 0, JText::_('(UTC 00:00) Western Europe Time, London, Lisbon, Casablanca')),
			JHtml::_('select.option', 1, JText::_('(UTC +01:00) Amsterdam, Berlin, Brussels, Copenhagen, Madrid, Paris')),
			JHtml::_('select.option', 2, JText::_('(UTC +02:00) Istanbul, Jerusalem, Kaliningrad, South Africa')),
			JHtml::_('select.option', 3, JText::_('(UTC +03:00) Baghdad, Riyadh, Moscow, St. Petersburg')),
			JHtml::_('select.option', 3.5, JText::_('(UTC +03:30) Tehran')),
			JHtml::_('select.option', 4, JText::_('(UTC +04:00) Abu Dhabi, Muscat, Baku, Tbilisi')),
			JHtml::_('select.option', 4.5, JText::_('(UTC +04:30) Kabul')),
			JHtml::_('select.option', 5, JText::_('(UTC +05:00) Ekaterinburg, Islamabad, Karachi, Tashkent')),
			JHtml::_('select.option', 5.5, JText::_('(UTC +05:30) Bombay, Calcutta, Madras, New Delhi')),
			JHtml::_('select.option', 5.75, JText::_('(UTC +05:45) Kathmandu')),
			JHtml::_('select.option', 6, JText::_('(UTC +06:00) Almaty, Dhaka, Colombo')),
			JHtml::_('select.option', 6.30, JText::_('(UTC +06:30) Yagoon')),
			JHtml::_('select.option', 7, JText::_('(UTC +07:00) Bangkok, Hanoi, Jakarta')),
			JHtml::_('select.option', 8, JText::_('(UTC +08:00) Beijing, Perth, Singapore, Hong Kong')),
			JHtml::_('select.option', 8.75, JText::_('(UTC +08:00) Western Australia')),
			JHtml::_('select.option', 9, JText::_('(UTC +09:00) Tokyo, Seoul, Osaka, Sapporo, Yakutsk')),
			JHtml::_('select.option', 9.5, JText::_('(UTC +09:30) Adelaide, Darwin, Yakutsk')),
			JHtml::_('select.option', 10, JText::_('(UTC +10:00) Eastern Australia, Guam, Vladivostok')),
			JHtml::_('select.option', 10.5, JText::_('(UTC +10:30) Lord Howe Island (Australia)')),
			JHtml::_('select.option', 11, JText::_('(UTC +11:00) Magadan, Solomon Islands, New Caledonia')),
			JHtml::_('select.option', 11.30, JText::_('(UTC +11:30) Norfolk Island')),
			JHtml::_('select.option', 12, JText::_('(UTC +12:00) Auckland, Wellington, Fiji, Kamchatka')),
			JHtml::_('select.option', 12.75, JText::_('(UTC +12:45) Chatham Island')),
			JHtml::_('select.option', 13, JText::_('(UTC +13:00) Tonga')),
			JHtml::_('select.option', 14, JText::_('(UTC +14:00) Kiribati')),
		);

		$options	= array_merge(
						parent::_getOptions(),
						$options
					);

		return $options;
	}
}