<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Config
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * HTML helper methods for com_config
 *
 * @package		Joomla
 * @subpackage	Config
 */
class JHTMLConfig
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
			JHTML::_('select.option',	-1,								JText::_('Config Error System Default')),
			JHTML::_('select.option',	0,								JText::_('Config Error None')),
			JHTML::_('select.option',	E_ERROR | E_WARNING | E_PARSE,	JText::_('Config Error Simple')),
			JHTML::_('select.option',	E_ALL,							JText::_('Config Error Maximum')),
			JHTML::_('select.option',	E_ALL | E_STRICT,				JText::_('Config Error Strict'))
		);
		return JHTML::_('select.genericlist', $options, $name, 'class="inputbox" size="1"', 'value', 'text', $selected);
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
		$options = array (
			JHTML::_('select.option',	-12,	JText::_('(UTC -12:00) International Date Line West')),
			JHTML::_('select.option',	-11,	JText::_('(UTC -11:00) Midway Island, Samoa')),
			JHTML::_('select.option',	-10,	JText::_('(UTC -10:00) Hawaii')),
			JHTML::_('select.option',	-9.5,	JText::_('(UTC -09:30) Taiohae, Marquesas Islands')),
			JHTML::_('select.option',	-9,		JText::_('(UTC -09:00) Alaska')),
			JHTML::_('select.option',	-8,		JText::_('(UTC -08:00) Pacific Time (US &amp; Canada)')),
			JHTML::_('select.option',	-7,		JText::_('(UTC -07:00) Mountain Time (US &amp; Canada)')),
			JHTML::_('select.option',	-6,		JText::_('(UTC -06:00) Central Time (US &amp; Canada), Mexico City')),
			JHTML::_('select.option',	-5,		JText::_('(UTC -05:00) Eastern Time (US &amp; Canada), Bogota, Lima')),
			JHTML::_('select.option',	-4,		JText::_('(UTC -04:00) Atlantic Time (Canada), Caracas, La Paz')),
			JHTML::_('select.option',	-3.5,	JText::_('(UTC -03:30) St. John\'s, Newfoundland, Labrador')),
			JHTML::_('select.option',	-3,		JText::_('(UTC -03:00) Brazil, Buenos Aires, Georgetown')),
			JHTML::_('select.option',	-2,		JText::_('(UTC -02:00) Mid-Atlantic')),
			JHTML::_('select.option',	-1,		JText::_('(UTC -01:00) Azores, Cape Verde Islands')),
			JHTML::_('select.option',	0,		JText::_('(UTC 00:00) Western Europe Time, London, Lisbon, Casablanca')),
			JHTML::_('select.option',	1,		JText::_('(UTC +01:00) Amsterdam, Berlin, Brussels, Copenhagen, Madrid, Paris')),
			JHTML::_('select.option',	2,		JText::_('(UTC +02:00) Istanbul, Jerusalem, Kaliningrad, South Africa')),
			JHTML::_('select.option',	3,		JText::_('(UTC +03:00) Baghdad, Riyadh, Moscow, St. Petersburg')),
			JHTML::_('select.option',	3.5,	JText::_('(UTC +03:30) Tehran')),
			JHTML::_('select.option',	4,		JText::_('(UTC +04:00) Abu Dhabi, Muscat, Baku, Tbilisi')),
			JHTML::_('select.option',	4.5,	JText::_('(UTC +04:30) Kabul')),
			JHTML::_('select.option',	5,		JText::_('(UTC +05:00) Ekaterinburg, Islamabad, Karachi, Tashkent')),
			JHTML::_('select.option',	5.5,	JText::_('(UTC +05:30) Bombay, Calcutta, Madras, New Delhi')),
			JHTML::_('select.option',	5.75,	JText::_('(UTC +05:45) Kathmandu')),
			JHTML::_('select.option',	6,		JText::_('(UTC +06:00) Almaty, Dhaka, Colombo')),
			JHTML::_('select.option',	6.30,	JText::_('(UTC +06:30) Yagoon')),
			JHTML::_('select.option',	7,		JText::_('(UTC +07:00) Bangkok, Hanoi, Jakarta')),
			JHTML::_('select.option',	8,		JText::_('(UTC +08:00) Beijing, Perth, Singapore, Hong Kong')),
			JHTML::_('select.option',	8.75,	JText::_('(UTC +08:00) Western Australia')),
			JHTML::_('select.option',	9,		JText::_('(UTC +09:00) Tokyo, Seoul, Osaka, Sapporo, Yakutsk')),
			JHTML::_('select.option',	9.5,	JText::_('(UTC +09:30) Adelaide, Darwin, Yakutsk')),
			JHTML::_('select.option',	10,		JText::_('(UTC +10:00) Eastern Australia, Guam, Vladivostok')),
			JHTML::_('select.option',	10.5,	JText::_('(UTC +10:30) Lord Howe Island (Australia)')),
			JHTML::_('select.option',	11,		JText::_('(UTC +11:00) Magadan, Solomon Islands, New Caledonia')),
			JHTML::_('select.option',	11.30,	JText::_('(UTC +11:30) Norfolk Island')),
			JHTML::_('select.option',	12,		JText::_('(UTC +12:00) Auckland, Wellington, Fiji, Kamchatka')),
			JHTML::_('select.option',	12.75,	JText::_('(UTC +12:45) Chatham Island')),
			JHTML::_('select.option',	13,		JText::_('(UTC +13:00) Tonga')),
			JHTML::_('select.option',	14,		JText::_('(UTC +14:00) Kiribati')),
		);
		return JHTML::_('select.genericlist', $options, $name, 'class="inputbox" size="1"', 'value', 'text', $selected);
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
			$options[] = JHTML::_('select.option', $store, JText::_(ucfirst($store)) );
		}
		return JHTML::_('select.genericlist', $options, $name, 'class="inputbox" size="1"', 'value', 'text', $selected);
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
			$options[] = JHTML::_('select.option', $store, JText::_(ucfirst($store)) );
		}
		return JHTML::_('select.genericlist', $options, $name, 'class="inputbox" size="1"', 'value', 'text', $selected);
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
			JHTML::_('select.option',	'mail',		JText::_('PHP mail function')),
			JHTML::_('select.option',	'sendmail',	JText::_('Sendmail')),
			JHTML::_('select.option',	'smtp',		JText::_('SMTP Server'))
		);
		return JHTML::_('select.genericlist', $options, $name, 'class="inputbox" size="1"', 'value', 'text', $selected);
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
		array_unshift($options, JHTML::_('select.option', '', JText::_('local')));
		return JHTML::_('select.genericlist', $options, $name, ' class="inputbox"', 'value', 'text', $selected);
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
			JHTML::_('select.option', 5, 5),
			JHTML::_('select.option', 10, 10),
			JHTML::_('select.option', 15, 15),
			JHTML::_('select.option', 20, 20),
			JHTML::_('select.option', 25, 25),
			JHTML::_('select.option', 30, 30),
			JHTML::_('select.option', 50, 50),
			JHTML::_('select.option', 100, 100),
		);
		return JHTML::_('select.genericlist', $options, $name, 'class="inputbox" size="1"', 'value', 'text', $selected);
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
			.' FROM #__plugins'
			.' WHERE folder = '.$db->Quote('editors')
			.' AND published = 1'
			.' ORDER BY ordering, name'
		);
		$options = $db->loadObjectList();
		return JHTML::_('select.genericlist', $options, $name, 'class="inputbox" size="1"', 'value', 'text', $selected);
	}
}
