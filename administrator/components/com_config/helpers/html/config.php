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
		return JHtml::_('select.genericlist', $options, $name, 'class="inputbox" size="1"', 'value', 'text', $selected);
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
			JHtml::_('select.option',	-12,	JText::_('(UTC -12:00) International Date Line West')),
			JHtml::_('select.option',	-11,	JText::_('(UTC -11:00) Midway Island, Samoa')),
			JHtml::_('select.option',	-10,	JText::_('(UTC -10:00) Hawaii')),
			JHtml::_('select.option',	-9.5,	JText::_('(UTC -09:30) Taiohae, Marquesas Islands')),
			JHtml::_('select.option',	-9,		JText::_('(UTC -09:00) Alaska')),
			JHtml::_('select.option',	-8,		JText::_('(UTC -08:00) Pacific Time (US &amp; Canada)')),
			JHtml::_('select.option',	-7,		JText::_('(UTC -07:00) Mountain Time (US &amp; Canada)')),
			JHtml::_('select.option',	-6,		JText::_('(UTC -06:00) Central Time (US &amp; Canada), Mexico City')),
			JHtml::_('select.option',	-5,		JText::_('(UTC -05:00) Eastern Time (US &amp; Canada), Bogota, Lima')),
			JHtml::_('select.option',	-4,		JText::_('(UTC -04:00) Atlantic Time (Canada), Caracas, La Paz')),
			JHtml::_('select.option',	-3.5,	JText::_('(UTC -03:30) St. John\'s, Newfoundland, Labrador')),
			JHtml::_('select.option',	-3,		JText::_('(UTC -03:00) Brazil, Buenos Aires, Georgetown')),
			JHtml::_('select.option',	-2,		JText::_('(UTC -02:00) Mid-Atlantic')),
			JHtml::_('select.option',	-1,		JText::_('(UTC -01:00) Azores, Cape Verde Islands')),
			JHtml::_('select.option',	0,		JText::_('(UTC 00:00) Western Europe Time, London, Lisbon, Casablanca')),
			JHtml::_('select.option',	1,		JText::_('(UTC +01:00) Amsterdam, Berlin, Brussels, Copenhagen, Madrid, Paris')),
			JHtml::_('select.option',	2,		JText::_('(UTC +02:00) Istanbul, Jerusalem, Kaliningrad, South Africa')),
			JHtml::_('select.option',	3,		JText::_('(UTC +03:00) Baghdad, Riyadh, Moscow, St. Petersburg')),
			JHtml::_('select.option',	3.5,	JText::_('(UTC +03:30) Tehran')),
			JHtml::_('select.option',	4,		JText::_('(UTC +04:00) Abu Dhabi, Muscat, Baku, Tbilisi')),
			JHtml::_('select.option',	4.5,	JText::_('(UTC +04:30) Kabul')),
			JHtml::_('select.option',	5,		JText::_('(UTC +05:00) Ekaterinburg, Islamabad, Karachi, Tashkent')),
			JHtml::_('select.option',	5.5,	JText::_('(UTC +05:30) Bombay, Calcutta, Madras, New Delhi')),
			JHtml::_('select.option',	5.75,	JText::_('(UTC +05:45) Kathmandu')),
			JHtml::_('select.option',	6,		JText::_('(UTC +06:00) Almaty, Dhaka, Colombo')),
			JHtml::_('select.option',	6.30,	JText::_('(UTC +06:30) Yagoon')),
			JHtml::_('select.option',	7,		JText::_('(UTC +07:00) Bangkok, Hanoi, Jakarta')),
			JHtml::_('select.option',	8,		JText::_('(UTC +08:00) Beijing, Perth, Singapore, Hong Kong')),
			JHtml::_('select.option',	8.75,	JText::_('(UTC +08:00) Western Australia')),
			JHtml::_('select.option',	9,		JText::_('(UTC +09:00) Tokyo, Seoul, Osaka, Sapporo, Yakutsk')),
			JHtml::_('select.option',	9.5,	JText::_('(UTC +09:30) Adelaide, Darwin, Yakutsk')),
			JHtml::_('select.option',	10,		JText::_('(UTC +10:00) Eastern Australia, Guam, Vladivostok')),
			JHtml::_('select.option',	10.5,	JText::_('(UTC +10:30) Lord Howe Island (Australia)')),
			JHtml::_('select.option',	11,		JText::_('(UTC +11:00) Magadan, Solomon Islands, New Caledonia')),
			JHtml::_('select.option',	11.30,	JText::_('(UTC +11:30) Norfolk Island')),
			JHtml::_('select.option',	12,		JText::_('(UTC +12:00) Auckland, Wellington, Fiji, Kamchatka')),
			JHtml::_('select.option',	12.75,	JText::_('(UTC +12:45) Chatham Island')),
			JHtml::_('select.option',	13,		JText::_('(UTC +13:00) Tonga')),
			JHtml::_('select.option',	14,		JText::_('(UTC +14:00) Kiribati')),
		);
		return JHtml::_('select.genericlist', $options, $name, 'class="inputbox" size="1"', 'value', 'text', $selected);
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
		return JHtml::_('select.genericlist', $options, $name, 'class="inputbox" size="1"', 'value', 'text', $selected);
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
		return JHtml::_('select.genericlist', $options, $name, 'class="inputbox" size="1"', 'value', 'text', $selected);
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
			JHtml::_('select.option',	'mail',		JText::_('PHP mail function')),
			JHtml::_('select.option',	'sendmail',	JText::_('Sendmail')),
			JHtml::_('select.option',	'smtp',		JText::_('SMTP Server'))
		);
		return JHtml::_('select.genericlist', $options, $name, 'class="inputbox" size="1"', 'value', 'text', $selected);
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
		return JHtml::_('select.genericlist', $options, $name, ' class="inputbox"', 'value', 'text', $selected);
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
		return JHtml::_('select.genericlist', $options, $name, 'class="inputbox" size="1"', 'value', 'text', $selected);
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
		return JHtml::_('select.genericlist', $options, $name, 'class="inputbox" size="1"', 'value', 'text', $selected);
	}
}
