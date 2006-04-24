<?php
/**
 * @version $Id$
 * @package Joomla
 * @subpackage Config
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/*
 * Make sure the user is authorized to view this page
 */
$user = & $mainframe->getUser();
if (!$user->authorize( 'com_config', 'manage' )) {
	josRedirect('index2.php?', JText::_('ALERTNOTAUTH'));
}

require_once (JApplicationHelper::getPath('admin_html'));

switch ($task) {
	case 'apply' :
	case 'save' :
		JConfigController::saveConfig($task);
		break;

	case 'cancel' :
		josRedirect('index2.php');
		break;

	default :
		JConfigController::showConfig($option);
		break;
}

class JConfigController {

	/**
	 * Show the configuration edit form
	 * @param string The URL option
	 */
	function showConfig($option)
	{
		global $mainframe;

		/*
		 * Initialize some variables
		 */
		$db = & $mainframe->getDBO();
		$row = new JConfig();

		// compile list of the languages
		$langs 		= array ();
		$menuitems 	= array ();
		$lists 		= array ();

		// PRE-PROCESS SOME LIST

		// -- Editors --

		// compile list of the editors
		$query = "SELECT element AS value, name AS text"
				."\n FROM #__plugins"
				."\n WHERE folder = 'editors'"
				."\n AND published = 1"
				."\n ORDER BY ordering, name"
				;
		$db->setQuery($query);
		$edits = $db->loadObjectList();

		// -- Show/Hide --

		$show_hide		= array (mosHTML::makeOption(1, JText::_('Hide')), mosHTML::makeOption(0, JText::_('Show')),);

		$show_hide_r 	= array (mosHTML::makeOption(0, JText::_('Hide')), mosHTML::makeOption(1, JText::_('Show')),);

		// -- menu items --

		$query = "SELECT id AS value, name AS text FROM #__menu"
				."\n WHERE ( type='content_section' OR type='components' OR type='content_typed' )"
				."\n AND published = 1"
				."\n AND access = 0"
				."\n ORDER BY name"
				;
		$db->setQuery($query);
		$menuitems = array_merge($menuitems, $db->loadObjectList());

		// SITE SETTINGS

		$lists['offline'] = mosHTML::yesnoRadioList('offline', 'class="inputbox"', $row->offline);

		if (!$row->editor) {
			$row->editor = '';
		}
		// build the html select list
		$lists['editor'] 		= mosHTML::selectList($edits, 'editor', 'class="inputbox" size="1"', 'value', 'text', $row->editor);

		$listLimit 				= array (mosHTML::makeOption(5, 5), mosHTML::makeOption(10, 10), mosHTML::makeOption(15, 15), mosHTML::makeOption(20, 20), mosHTML::makeOption(25, 25), mosHTML::makeOption(30, 30), mosHTML::makeOption(50, 50), mosHTML::makeOption(100, 100),);

		$lists['list_limit'] 	= mosHTML::selectList($listLimit, 'list_limit', 'class="inputbox" size="1"', 'value', 'text', ($row->list_limit ? $row->list_limit : 50));

		jimport('joomla.i18n.help');
		$helpsites 				= array ();
		$helpsites 				= JHelp::createSiteList('http://help.joomla.org/helpsites-11.xml', $row->helpurl);
		array_unshift($helpsites, mosHTML::makeOption('', JText::_('local')));
		$lists['helpsites'] 	= mosHTML::selectList($helpsites, 'helpurl', ' class="inputbox"', 'value', 'text', $row->helpurl);

		// DEBUG

		$lists['debug'] 		= mosHTML::yesnoRadioList('debug', 'class="inputbox"', $row->debug);
		$lists['debug_db'] 		= mosHTML::yesnoRadioList('debug_db', 'class="inputbox"', $row->debug_db);
		$lists['log'] 			= mosHTML::yesnoRadioList('log', 'class="inputbox"', $row->log);
		$lists['log_db'] 		= mosHTML::yesnoRadioList('log_db', 'class="inputbox"', $row->log_db);

		// DATABASE SETTINGS

		// SERVER SETTINGS

		$lists['gzip'] 			= mosHTML::yesnoRadioList('gzip', 'class="inputbox"', $row->gzip);

		$errors 				= array (mosHTML::makeOption(-1, JText::_('System Default')), mosHTML::makeOption(0, JText::_('None')), mosHTML::makeOption(E_ERROR | E_WARNING | E_PARSE, JText::_('Simple')), mosHTML::makeOption(E_ALL, JText::_('Maximum')));

		$lists['xmlrpc_server'] = mosHTML::yesnoRadioList('xmlrpc_server', 'class="inputbox"', $row->xmlrpc_server);

		$lists['error_reporting'] = mosHTML::selectList($errors, 'error_reporting', 'class="inputbox" size="1"', 'value', 'text', $row->error_reporting);

		$lists['enable_ftp'] 	= mosHTML::yesnoRadioList('ftp_enable', 'class="inputbox"', intval($row->ftp_enable));


		// LOCALE SETTINGS

		$timeoffset = array (	mosHTML::makeOption(-12, JText::_('(UTC -12:00) International Date Line West')),
								mosHTML::makeOption(-11, JText::_('(UTC -11:00) Midway Island, Samoa')),
								mosHTML::makeOption(-10, JText::_('(UTC -10:00) Hawaii')),
								mosHTML::makeOption(-9.5, JText::_('(UTC -09:30) Taiohae, Marquesas Islands')),
								mosHTML::makeOption(-9, JText::_('(UTC -09:00) Alaska')),
								mosHTML::makeOption(-8, JText::_('(UTC -08:00) Pacific Time (US &amp; Canada)')),
								mosHTML::makeOption(-7, JText::_('(UTC -07:00) Mountain Time (US &amp; Canada)')),
								mosHTML::makeOption(-6, JText::_('(UTC -06:00) Central Time (US &amp; Canada), Mexico City')),
								mosHTML::makeOption(-5, JText::_('(UTC -05:00) Eastern Time (US &amp; Canada), Bogota, Lima')),
								mosHTML::makeOption(-4, JText::_('(UTC -04:00) Atlantic Time (Canada), Caracas, La Paz')),
								mosHTML::makeOption(-3.5, JText::_('(UTC -03:30) St. John`s, Newfoundland and Labrador')),
								mosHTML::makeOption(-3, JText::_('(UTC -03:00) Brazil, Buenos Aires, Georgetown')),
								mosHTML::makeOption(-2, JText::_('(UTC -02:00) Mid-Atlantic')),
								mosHTML::makeOption(-1, JText::_('(UTC -01:00) Azores, Cape Verde Islands')),
								mosHTML::makeOption(0, JText::_('(UTC 00:00) Western Europe Time, London, Lisbon, Casablanca')),
								mosHTML::makeOption(1, JText::_('(UTC +01:00) Amsterdam, Berlin, Brussels, Copenhagen, Madrid, Paris')),
								mosHTML::makeOption(2, JText::_('(UTC +02:00) Jerusalem, Kaliningrad, South Africa')),
								mosHTML::makeOption(3, JText::_('(UTC +03:00) Baghdad, Riyadh, Moscow, St. Petersburg')),
								mosHTML::makeOption(3.5, JText::_('(UTC +03:30) Tehran')),
								mosHTML::makeOption(4, JText::_('(UTC +04:00) Abu Dhabi, Muscat, Baku, Tbilisi')),
								mosHTML::makeOption(4.5, JText::_('(UTC +04:30) Kabul')),
								mosHTML::makeOption(5, JText::_('(UTC +05:00) Ekaterinburg, Islamabad, Karachi, Tashkent')),
								mosHTML::makeOption(5.5, JText::_('(UTC +05:30) Bombay, Calcutta, Madras, New Delhi')),
								mosHTML::makeOption(5.75, JText::_('(UTC +05:45) Kathmandu')),
								mosHTML::makeOption(6, JText::_('(UTC +06:00) Almaty, Dhaka, Colombo')),
								mosHTML::makeOption(6.30, JText::_('(UTC +06:30) Yagoon')),
								mosHTML::makeOption(7, JText::_('(UTC +07:00) Bangkok, Hanoi, Jakarta')),
								mosHTML::makeOption(8, JText::_('(UTC +08:00) Beijing, Perth, Singapore, Hong Kong')),
								mosHTML::makeOption(8.75, JText::_('(UTC +08:00) Western Australia')),
								mosHTML::makeOption(9, JText::_('(UTC +09:00) Tokyo, Seoul, Osaka, Sapporo, Yakutsk')),
								mosHTML::makeOption(9.5, JText::_('(UTC +09:30) Adelaide, Darwin, Yakutsk')),
								mosHTML::makeOption(10, JText::_('(UTC +10:00) Eastern Australia, Guam, Vladivostok')),
								mosHTML::makeOption(10.5, JText::_('(UTC +10:30) Lord Howe Island (Australia)')),
								mosHTML::makeOption(11, JText::_('(UTC +11:00) Magadan, Solomon Islands, New Caledonia')),
								mosHTML::makeOption(11.30, JText::_('(UTC +11:30) Norfolk Island')),
								mosHTML::makeOption(12, JText::_('(UTC +12:00) Auckland, Wellington, Fiji, Kamchatka')),
								mosHTML::makeOption(12.75, JText::_('(UTC +12:45) Chatham Island')),
								mosHTML::makeOption(13, JText::_('(UTC +13:00) Tonga')),
								mosHTML::makeOption(14, JText::_('(UTC +14:00) Kiribati')),);

		$lists['offset'] 		= mosHTML::selectList($timeoffset, 'offset_user', 'class="inputbox" size="1"', 'value', 'text', $row->offset_user);

		// MAIL SETTINGS

		$mailer 				= array (mosHTML::makeOption('mail', JText::_('PHP mail function')), mosHTML::makeOption('sendmail', JText::_('Sendmail')), mosHTML::makeOption('smtp', JText::_('SMTP Server')));
		$lists['mailer'] 		= mosHTML::selectList($mailer, 'mailer', 'class="inputbox" size="1"', 'value', 'text', $row->mailer);

		$lists['smtpauth'] 		= mosHTML::yesnoRadioList('smtpauth', 'class="inputbox"', $row->smtpauth);

		// CACHE SETTINGS

		$lists['caching'] 		= mosHTML::yesnoRadioList('caching', 'class="inputbox"', $row->caching);

		// USER SETTINGS

		$lists['allowUserRegistration'] = mosHTML::yesnoRadioList('allowUserRegistration', 'class="inputbox"', $row->allowUserRegistration);

		$lists['useractivation'] = mosHTML::yesnoRadioList('useractivation', 'class="inputbox"', $row->useractivation);

		$lists['uniquemail'] 	= mosHTML::yesnoRadioList('uniquemail', 'class="inputbox"', $row->uniquemail);

		$lists['shownoauth'] 	= mosHTML::yesnoRadioList('shownoauth', 'class="inputbox"', $row->shownoauth);

		// META SETTINGS

		$lists['MetaAuthor'] 	= mosHTML::yesnoRadioList('MetaAuthor', 'class="inputbox"', $row->MetaAuthor);

		$lists['MetaTitle'] 	= mosHTML::yesnoRadioList('MetaTitle', 'class="inputbox"', $row->MetaTitle);

		// STATISTICS SETTINGS

		$lists['log_searches'] 	= mosHTML::yesnoRadioList('enable_log_searches', 'class="inputbox"', $row->enable_log_searches);

		$lists['enable_stats'] 	= mosHTML::yesnoRadioList('enable_stats', 'class="inputbox"', $row->enable_stats);

		$lists['log_items'] 	= mosHTML::yesnoRadioList('enable_log_items', 'class="inputbox"', $row->enable_log_items);

		// SEO SETTINGS

		$lists['sef'] 			= mosHTML::yesnoRadioList('sef', 'class="inputbox" onclick="javascript: if (document.adminForm.sef[1].checked) { alert(\''.JText::_('Remember to rename htaccess.txt to .htaccess', true).'\') }"', $row->sef);

		// CONTENT SETTINGS

		$lists['link_titles'] 	= mosHTML::yesnoRadioList('link_titles', 'class="inputbox"', $row->link_titles);

		$lists['readmore'] 		= mosHTML::RadioList($show_hide_r, 'readmore', 'class="inputbox"', $row->readmore, 'value', 'text');

		$lists['vote'] 			= mosHTML::RadioList($show_hide_r, 'vote', 'class="inputbox"', $row->vote, 'value', 'text');

		$lists['hideAuthor'] 	= mosHTML::RadioList($show_hide, 'hideAuthor', 'class="inputbox"', $row->hideAuthor, 'value', 'text');

		$lists['hideCreateDate'] = mosHTML::RadioList($show_hide, 'hideCreateDate', 'class="inputbox"', $row->hideCreateDate, 'value', 'text');

		$lists['hideModifyDate'] = mosHTML::RadioList($show_hide, 'hideModifyDate', 'class="inputbox"', $row->hideModifyDate, 'value', 'text');

		$lists['hits'] 			= mosHTML::RadioList($show_hide_r, 'hits', 'class="inputbox"', $row->hits, 'value', 'text');

		if (is_writable(JPATH_SITE.DS.'tmp'.DS)) {
			$lists['hidePdf'] = mosHTML::RadioList($show_hide, 'hidePdf', 'class="inputbox"', $row->hidePdf, 'value', 'text');
		} else {
			$lists['hidePdf'] = '<input type="hidden" name="hidePdf" value="1" /><strong>Hide</strong>';
		}

		$lists['hidePrint'] 	= mosHTML::RadioList($show_hide, 'hidePrint', 'class="inputbox"', $row->hidePrint, 'value', 'text');

		$lists['hideEmail'] 	= mosHTML::RadioList($show_hide, 'hideEmail', 'class="inputbox"', $row->hideEmail, 'value', 'text');

		$lists['icons'] 		= mosHTML::yesnoRadioList('icons', 'class="inputbox"', $row->icons, 'icons', 'text');

		$lists['ml_support'] 	= mosHTML::yesnoRadioList('multilingual_support', 'class="inputbox" onclick="javascript: if (document.adminForm.multilingual_support[1].checked) { alert(\''.JText::_('Remember to install the MambelFish component.', true).'\') }"', $row->multilingual_support);

		// FEED SETTINGS
		$formats  = array (mosHTML::makeOption('RSS2.0', JText::_('RSS')), mosHTML::makeOption('Atom', JText::_('Atom')));
		$summary = array (mosHTML::makeOption(1, JText::_('Full Text')), mosHTML::makeOption(0, JText::_('Intro Text')),);


		$lists['feed_format']  = mosHTML::selectList($formats, 'feed_format', 'class="inputbox" size="1"', 'value', 'text', $row->feed_format);

		$lists['feed_limit']   = mosHTML::selectList($listLimit, 'list_limit', 'class="inputbox" size="1"', 'value', 'text', ($row->feed_limit ? $row->feed_limit : 10));

		$lists['feed_excerpt'] = mosHTML::RadioList($summary, 'feed_summary', 'class="inputbox"', $row->feed_excerpt);

		// SHOW EDIT FORM

		JConfigView::showConfig($row, $lists, $option);
	}

	/**
	 * Save the configuration
	 */
	function saveConfig($task)
	{
		global $mainframe;

		$mainframe->_registry->loadArray($_POST);

		/*
		 * Handle the server time offset
		 */
		$server_time = date('O') / 100;
		$offset = JRequest::getVar('offset_user', 0, 'post', 'int') - $server_time;
		$mainframe->_registry->setValue('config.offset', $offset);

		// Get the path of the configuration file
		$fname = JPATH_CONFIGURATION.'/configuration.php';

		/*
		 * Now we get the config registry in PHP class format and write it to
		 * configuation.php then redirect appropriately.
		 */
		jimport('joomla.filesystem.file');
		if (JFile::write($fname, $mainframe->_registry->toString('PHP', 'config', array('class' => 'JConfig')))) {

			$msg = JText::_('The Configuration Details have been updated');

			switch ($task) {
				case 'apply' :
					josRedirect('index2.php?option=com_config', $msg);
					break;

				case 'save' :
				default :
					josRedirect('index2.php', $msg);
					break;
			}
		} else {
			josRedirect('index2.php', JText::_('ERRORCONFIGFILE'));
		}
	}
}
?>