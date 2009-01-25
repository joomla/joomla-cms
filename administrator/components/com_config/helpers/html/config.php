<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Config
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
  */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * HTML helper methods for com_config
 *
 * @package		Joomla.Administrator
 * @subpackage	Config
 */
class JHtmlConfig
{
	public static function warnicon()
	{
		$tip = '<img src="'.JURI::root().'media/system/images/warning.png" border="0"  alt="" />';
		return $tip;
	}

	/**
	 * Display a list of configuration PHP error reporting options
	 *
	 * @param	int		The selected value
	 * @param	string	The field name
	 * @return	string
	 */
	public static function errorReporting($selected = -1, $name = 'error_reporting')
	{
		$options = array (
			JHtml::_('select.option',	-1,								JText::_('Config Error System Default')),
			JHtml::_('select.option',	0,								JText::_('Config Error None')),
			JHtml::_('select.option',	E_ERROR | E_WARNING | E_PARSE,	JText::_('Config Error Simple')),
			JHtml::_('select.option',	E_ALL,							JText::_('Config Error Maximum')),
			JHtml::_('select.option',	E_ALL | E_STRICT,				JText::_('Config Error Strict'))
		);
		return JHtml::_(
			'select.genericlist',
			$options,
			$name,
			array('list.attr' => 'class="inputbox" size="1"', 'list.select' => $selected)
		);
	}

	/**
	 * Display a list of configuration timezones
	 *
	 * @param	int		The selected value
	 * @param	string	The field name
	 * @return	string
	 */
	public static function locales($selected = 0, $name = 'offset')
	{
		$zones = array (
			'-12' => JText::_('(UTC -12:00) International Date Line West'),
			'-11' => JText::_('(UTC -11:00) Midway Island, Samoa'),
			'-10' => JText::_('(UTC -10:00) Hawaii'),
			'-9.5' => JText::_('(UTC -09:30) Taiohae, Marquesas Islands'),
			'-9' => JText::_('(UTC -09:00) Alaska'),
			'-8' => JText::_('(UTC -08:00) Pacific Time (US &amp; Canada)'),
			'-7' => JText::_('(UTC -07:00) Mountain Time (US &amp; Canada)'),
			'-6' => JText::_('(UTC -06:00) Central Time (US &amp; Canada), Mexico City'),
			'-5' => JText::_('(UTC -05:00) Eastern Time (US &amp; Canada), Bogota, Lima'),
			'-4' => JText::_('(UTC -04:00) Atlantic Time (Canada), Caracas, La Paz'),
			'-3.5' => JText::_('(UTC -03:30) St. John\'s, Newfoundland, Labrador'),
			'-3' => JText::_('(UTC -03:00) Brazil, Buenos Aires, Georgetown'),
			'-2' => JText::_('(UTC -02:00) Mid-Atlantic'),
			'-1' => JText::_('(UTC -01:00) Azores, Cape Verde Islands'),
			'0' => JText::_('(UTC 00:00) Western Europe Time, London, Lisbon, Casablanca'),
			'1' => JText::_('(UTC +01:00) Amsterdam, Berlin, Brussels, Copenhagen, Madrid, Paris'),
			'2' => JText::_('(UTC +02:00) Istanbul, Jerusalem, Kaliningrad, South Africa'),
			'3' => JText::_('(UTC +03:00) Baghdad, Riyadh, Moscow, St. Petersburg'),
			'3.5' => JText::_('(UTC +03:30) Tehran'),
			'4' => JText::_('(UTC +04:00) Abu Dhabi, Muscat, Baku, Tbilisi'),
			'4.5' => JText::_('(UTC +04:30) Kabul'),
			'5' => JText::_('(UTC +05:00) Ekaterinburg, Islamabad, Karachi, Tashkent'),
			'5.5' => JText::_('(UTC +05:30) Bombay, Calcutta, Madras, New Delhi'),
			'5.75' => JText::_('(UTC +05:45) Kathmandu'),
			'6' => JText::_('(UTC +06:00) Almaty, Dhaka, Colombo'),
			'6.5' => JText::_('(UTC +06:30) Yagoon'),
			'7' => JText::_('(UTC +07:00) Bangkok, Hanoi, Jakarta'),
			'8' => JText::_('(UTC +08:00) Beijing, Perth, Singapore, Hong Kong'),
			'8.75' => JText::_('(UTC +08:45) Western Australia'),
			'9' => JText::_('(UTC +09:00) Tokyo, Seoul, Osaka, Sapporo, Yakutsk'),
			'9.5' => JText::_('(UTC +09:30) Adelaide, Darwin, Yakutsk'),
			'10' => JText::_('(UTC +10:00) Eastern Australia, Guam, Vladivostok'),
			'10.5' => JText::_('(UTC +10:30) Lord Howe Island (Australia)'),
			'11' => JText::_('(UTC +11:00) Magadan, Solomon Islands, New Caledonia'),
			'11.5' => JText::_('(UTC +11:30) Norfolk Island'),
			'12' => JText::_('(UTC +12:00) Auckland, Wellington, Fiji, Kamchatka'),
			'12.75' => JText::_('(UTC +12:45) Chatham Island'),
			'13' => JText::_('(UTC +13:00) Tonga'),
			'14' => JText::_('(UTC +14:00) Kiribati'),
		);
		return JHtml::_(
			'select.genericlist',
			$zones,
			$name,
			array(
				'list.attr' => 'class="inputbox" size="1"',
				'list.select' => $selected,
				'option.key' => null
			)
		);
	}

	/**
	 * Display a list of configuration session handlers
	 *
	 * @param	int		The selected value
	 * @param	string	The field name
	 * @return	string
	 */
	public static function sessionHandlers($selected = null, $name = 'session_handler')
	{
		$options = array();
		foreach (JSession::getStores() as $store) {
			$options[] = JHtml::_('select.option', $store, JText::_(ucfirst($store)) );
		}
		return JHtml::_(
			'select.genericlist',
			$options,
			$name,
			array('list.attr' => 'class="inputbox" size="1"', 'list.select' => $selected)
		);
	}

	/**
	 * Display a list of configuration cache handlers
	 *
	 * @param	int		The selected value
	 * @param	string	The field name
	 * @return	string
	 */
	public static function cacheHandlers($selected = null, $name = 'cache_handler')
	{
		jimport('joomla.cache.cache');
		$options = array();
		foreach(JCache::getStores() as $store) {
			$options[] = JHtml::_('select.option', $store, JText::_(ucfirst($store)) );
		}
		return JHtml::_(
			'select.genericlist',
			$options,
			$name,
			array('list.attr' => 'class="inputbox" size="1"', 'list.select' => $selected)
		);
	}

	/**
	 * Display a list of configuration mail handlers
	 *
	 * @param	int		The selected value
	 * @param	string	The field name
	 * @return	string
	 */
	public static function mailHandlers($selected = 'mail', $name = 'mailer')
	{
		$options = array (
			'mail' => JText::_('PHP mail function'),
			'sendmail' => JText::_('Sendmail'),
			'smtp' => JText::_('SMTP Server')
		);
		return JHtml::_(
			'select.genericlist',
			$options,
			$name,
			array(
				'list.attr' => 'class="inputbox" size="1"',
				'list.select' => $selected,
				'option.key' => null
			)
		);
	}

	/**
	 * Display a list of configuration help sites
	 *
	 * @param	int		The selected value
	 * @param	string	The field name
	 * @return	string
	 */
	public static function helpSites($selected = null, $name = 'helpurl')
	{
		jimport('joomla.language.help');
		$options = JHelp::createSiteList(JPATH_BASE.DS.'help'.DS.'helpsites-15.xml', $selected);
		array_unshift($options, JHtml::_('select.option', '', JText::_('local')));
		return JHtml::_(
			'select.genericlist',
			$options,
			$name,
			array('list.attr' => 'class="inputbox"', 'list.select' => $selected)
		);
	}

	/**
	 * Display a list of configuration list limits
	 *
	 * @param	int		The selected value
	 * @param	string	The field name
	 * @return	string
	 */
	public static function listLimits($selected = 50, $name = 'list_limit')
	{
		$options = array(
			JHtml::_('select.option', 5, 5),
			JHtml::_('select.option', 10, 10),
			JHtml::_('select.option', 15, 15),
			JHtml::_('select.option', 20, 20),
			JHtml::_('select.option', 25, 25),
			JHtml::_('select.option', 30, 30),
			JHtml::_('select.option', 50, 50),
			JHtml::_('select.option', 100, 100),
		);
		return JHtml::_(
			'select.genericlist',
			$options,
			$name,
			array('list.attr' => 'class="inputbox" size="1"', 'list.select' => $selected)
		);
	}

	/**
	 * Display a list of configuration editors
	 *
	 * @param	int		The selected value
	 * @param	string	The field name
	 * @return	string
	 */
	public static function editors($selected = '', $name = 'editor')
	{
		// compile list of the editors
		$db = &JFactory::getDBO();
		$db->setQuery(
			'SELECT element AS value, name AS text'
			.' FROM #__extensions'
			.' WHERE folder = '.$db->Quote('editors')
			.' AND type = "plugin" '
			.' AND enabled = 1'
			.' ORDER BY ordering, name'
		);
		$options = $db->loadObjectList();
		return JHtml::_(
			'select.genericlist',
			$options,
			$name,
			array('list.attr' => 'class="inputbox" size="1"', 'list.select' => $selected)
		);
	}
}
