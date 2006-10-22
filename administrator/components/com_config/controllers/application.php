<?php
/**
 * @version $Id$
 * @package Joomla
 * @subpackage Config
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

jimport( 'joomla.application.component.controller' );

/**
 * @package Joomla
 * @subpackage Config
 */
class ConfigControllerApplication extends JController
{
	/**
	 * Custom Constructor
	 */
	function __construct( $default = array() )
	{
		parent::__construct( $default );
		$this->registerTask( 'apply', 'save' );
	}

	/**
	 * Show the configuration edit form
	 * @param string The URL option
	 */
	function showConfig()
	{
		// Initialize some variables
		$db =& JFactory::getDBO();
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

		$show_hide		= array (JHTMLSelect::option(1, JText::_('Hide')), JHTMLSelect::option(0, JText::_('Show')),);

		$show_hide_r 	= array (JHTMLSelect::option(0, JText::_('Hide')), JHTMLSelect::option(1, JText::_('Show')),);

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
		$lists['offline'] = JHTMLSelect::yesnoList('offline', 'class="inputbox"', $row->offline);
		if (!$row->editor) {
			$row->editor = '';
		}
		// build the html select list
		$lists['editor'] 		= JHTMLSelect::genericList($edits, 'editor', 'class="inputbox" size="1"', 'value', 'text', $row->editor);
		$listLimit 				= array (JHTMLSelect::option(5, 5), JHTMLSelect::option(10, 10), JHTMLSelect::option(15, 15), JHTMLSelect::option(20, 20), JHTMLSelect::option(25, 25), JHTMLSelect::option(30, 30), JHTMLSelect::option(50, 50), JHTMLSelect::option(100, 100),);
		$lists['list_limit'] 	= JHTMLSelect::genericList($listLimit, 'list_limit', 'class="inputbox" size="1"', 'value', 'text', ($row->list_limit ? $row->list_limit : 50));

		jimport('joomla.i18n.help');
		$helpsites 				= array ();
		$helpsites 				= JHelp::createSiteList(JPATH_BASE.DS.'help'.DS.'helpsites-15.xml', $row->helpurl);
		array_unshift($helpsites, JHTMLSelect::option('', JText::_('local')));
		$lists['helpsites'] 	= JHTMLSelect::genericList($helpsites, 'helpurl', ' class="inputbox"', 'value', 'text', $row->helpurl);

		// DEBUG
		$lists['debug'] 		= JHTMLSelect::yesnoList('debug', 'class="inputbox"', $row->debug);
		$lists['debug_db'] 		= JHTMLSelect::yesnoList('debug_db', 'class="inputbox"', $row->debug_db);
		$lists['debug_lang'] 	= JHTMLSelect::yesnoList('debug_lang', 'class="inputbox"', $row->debug_lang);

		// DATABASE SETTINGS

		// SERVER SETTINGS
		$lists['gzip'] 			= JHTMLSelect::yesnoList('gzip', 'class="inputbox"', $row->gzip);
		$errors 				= array (JHTMLSelect::option(-1, JText::_('System Default')), JHTMLSelect::option(0, JText::_('None')), JHTMLSelect::option(E_ERROR | E_WARNING | E_PARSE, JText::_('Simple')), JHTMLSelect::option(E_ALL, JText::_('Maximum')));
		$lists['xmlrpc_server'] = JHTMLSelect::yesnoList('xmlrpc_server', 'class="inputbox"', $row->xmlrpc_server);
		$lists['error_reporting'] = JHTMLSelect::genericList($errors, 'error_reporting', 'class="inputbox" size="1"', 'value', 'text', $row->error_reporting);
		$lists['enable_ftp'] 	= JHTMLSelect::yesnoList('ftp_enable', 'class="inputbox"', intval($row->ftp_enable));
		$lists['legacy']		= JHTMLSelect::yesnoList('legacy', 'class="inputbox"', $row->legacy);

		// LOCALE SETTINGS
		$timeoffset = array (	JHTMLSelect::option(-12, JText::_('(UTC -12:00) International Date Line West')),
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
		$lists['offset'] 		= JHTMLSelect::genericList($timeoffset, 'offset', 'class="inputbox" size="1"', 'value', 'text', $row->offset);

		// MAIL SETTINGS
		$mailer 				= array (JHTMLSelect::option('mail', JText::_('PHP mail function')), JHTMLSelect::option('sendmail', JText::_('Sendmail')), JHTMLSelect::option('smtp', JText::_('SMTP Server')));
		$lists['mailer'] 		= JHTMLSelect::genericList($mailer, 'mailer', 'class="inputbox" size="1"', 'value', 'text', $row->mailer);
		$lists['smtpauth'] 		= JHTMLSelect::yesnoList('smtpauth', 'class="inputbox"', $row->smtpauth);

		// CACHE SETTINGS
		$lists['caching'] 		= JHTMLSelect::yesnoList('caching', 'class="inputbox"', $row->caching);
		$lists['caching_tmpl'] 		= JHTMLSelect::yesnoList('caching_tmpl', 'class="inputbox"', $row->caching_tmpl);
		$lists['caching_page'] 		= JHTMLSelect::yesnoList('caching_page', 'class="inputbox"', $row->caching_page);

		// USER SETTINGS
		$lists['allowUserRegistration'] = JHTMLSelect::yesnoList('allowUserRegistration', 'class="inputbox"', $row->allowUserRegistration);
		$lists['useractivation'] 		= JHTMLSelect::yesnoList('useractivation', 'class="inputbox"', $row->useractivation);
		$lists['shownoauth'] 			= JHTMLSelect::yesnoList('shownoauth', 'class="inputbox"', $row->shownoauth);
		$lists['frontend_userparams'] 	= JHTMLSelect::yesnoList('frontend_userparams', 'class="inputbox"', $row->frontend_userparams);
		$new_usertype = array (
			JHTMLSelect::option('Registered', 	JText::_('Registered')),
			JHTMLSelect::option('Author', 		JText::_('Author')),
			JHTMLSelect::option('Editor', 		JText::_('Editor')),
			JHTMLSelect::option('Publisher', 	JText::_('Publisher')),
		);
		$lists['new_usertype']			= JHTMLSelect::genericList($new_usertype, 'new_usertype', 'class="inputbox" size="1"', 'value', 'text', $row->new_usertype);

		// META SETTINGS
		$lists['MetaAuthor'] 	= JHTMLSelect::yesnoList('MetaAuthor', 'class="inputbox"', $row->MetaAuthor);
		$lists['MetaTitle'] 	= JHTMLSelect::yesnoList('MetaTitle', 'class="inputbox"', $row->MetaTitle);

		// STATISTICS SETTINGS
		$lists['log_searches'] 	= JHTMLSelect::yesnoList('enable_log_searches', 'class="inputbox"', $row->enable_log_searches);
		$lists['enable_stats'] 	= JHTMLSelect::yesnoList('enable_stats', 'class="inputbox"', $row->enable_stats);
		$lists['log_items'] 	= JHTMLSelect::yesnoList('enable_log_items', 'class="inputbox"', $row->enable_log_items);

		// SEO SETTINGS
		$lists['sef'] 			= JHTMLSelect::yesnoList('sef', 'class="inputbox"', $row->sef);

		// CONTENT SETTINGS
		$lists['link_titles'] 	= JHTMLSelect::yesnoList('link_titles', 'class="inputbox"', $row->link_titles);
		$lists['readmore'] 		= JHTMLSelect::radioList($show_hide_r, 'readmore', 'class="inputbox"', $row->readmore, 'value', 'text');
		$lists['vote'] 			= JHTMLSelect::radioList($show_hide_r, 'vote', 'class="inputbox"', $row->vote, 'value', 'text');
		$lists['hideAuthor'] 	= JHTMLSelect::radioList($show_hide, 'hideAuthor', 'class="inputbox"', $row->hideAuthor, 'value', 'text');
		$lists['hideCreateDate'] = JHTMLSelect::radioList($show_hide, 'hideCreateDate', 'class="inputbox"', $row->hideCreateDate, 'value', 'text');
		$lists['hideModifyDate'] = JHTMLSelect::radioList($show_hide, 'hideModifyDate', 'class="inputbox"', $row->hideModifyDate, 'value', 'text');
		$lists['hits'] 			= JHTMLSelect::radioList($show_hide_r, 'hits', 'class="inputbox"', $row->hits, 'value', 'text');
		if (is_writable(JPATH_SITE.DS.'tmp'.DS)) {
			$lists['hidePdf'] = JHTMLSelect::radioList($show_hide, 'hidePdf', 'class="inputbox"', $row->hidePdf, 'value', 'text');
		} else {
			$lists['hidePdf'] = '<input type="hidden" name="hidePdf" value="1" /><strong>Hide</strong>';
		}
		$lists['hidePrint'] 	= JHTMLSelect::radioList($show_hide, 'hidePrint', 'class="inputbox"', $row->hidePrint, 'value', 'text');
		$lists['hideEmail'] 	= JHTMLSelect::radioList($show_hide, 'hideEmail', 'class="inputbox"', $row->hideEmail, 'value', 'text');
		$lists['icons'] 		= JHTMLSelect::yesnoList('icons', 'class="inputbox"', $row->icons, 'icons', 'text');
		$lists['ml_support'] 	= JHTMLSelect::yesnoList('multilingual_support', 'class="inputbox" onclick="if (document.adminForm.multilingual_support[1].checked) { alert(\''.JText::_('Remember to install the MambelFish component.', true).'\') }"', $row->multilingual_support);

		// FEED SETTINGS
		$formats  = array (JHTMLSelect::option('RSS2.0', JText::_('RSS')), JHTMLSelect::option('Atom', JText::_('Atom')));
		$summary = array (JHTMLSelect::option(1, JText::_('Full Text')), JHTMLSelect::option(0, JText::_('Intro Text')),);
		$lists['feed_limit']   = JHTMLSelect::genericList($listLimit, 'feed_limit', 'class="inputbox" size="1"', 'value', 'text', ($row->feed_limit ? $row->feed_limit : 10));
		$lists['feed_excerpt'] = JHTMLSelect::radioList($summary, 'feed_summary', 'class="inputbox"', $row->feed_excerpt);

		// SHOW EDIT FORM
		ConfigApplicationView::showConfig($row, $lists);
	}

	/**
	 * Save the configuration
	 */
	function save()
	{
		global $mainframe;

		$config =& JFactory::getConfig();
		$config->loadArray(JRequest::get( 'post' ));
		
		//override any possible database password change
		$config->setValue('config.password', $mainframe->getCfg('password'));

		// handling of special characters
		$sitename			= htmlspecialchars( JRequest::getVar( 'sitename', '', 'post' ) );
		$config->setValue('config.sitename', $sitename);

		$MetaDesc			= htmlspecialchars( JRequest::getVar( 'MetaDesc', '', 'post' ) );
		$config->setValue('config.MetaDesc', $MetaDesc);

		$MetaKeys			= htmlspecialchars( JRequest::getVar( 'MetaKeys', '', 'post' ) );
		$config->setValue('config.MetaKeys', $MetaKeys);

		// handling of quotes (double and single) and amp characters
		// htmlspecialchars not used to preserve ability to insert other html characters
		$offline_message	= JRequest::getVar( 'offline_message', '', 'post' );
		$offline_message	= ampReplace( $offline_message );
		$offline_message	= str_replace( '"', '&quot;', $offline_message );
		$offline_message	= str_replace( "'", '&#039;', $offline_message );
		$config->setValue('config.offline_message', $offline_message);

		// Get the path of the configuration file
		$fname = JPATH_CONFIGURATION.'/configuration.php';

		/*
		 * Now we get the config registry in PHP class format and write it to
		 * configuation.php then redirect appropriately.
		 */
		jimport('joomla.filesystem.file');
		if (JFile::write($fname, $config->toString('PHP', 'config', array('class' => 'JConfig')))) {

			$msg = JText::_('The Configuration Details have been updated');

			$task = $this->getTask();
			switch ($task) {
				case 'apply' :
					$this->setRedirect('index.php?option=com_config', $msg);
					break;

				case 'save' :
				default :
					$this->setRedirect('index.php', $msg);
					break;
			}
		} else {
			$this->setRedirect('index.php', JText::_('ERRORCONFIGFILE'));
		}
	}

	/**
	 * Cancel operation
	 */
	function cancel()
	{
		$this->setRedirect( 'index.php' );
	}

	function refreshHelp()
	{
		jimport('joomla.filesystem.file');

		$data = file_get_contents('http://help.joomla.org/helpsites-15.xml');
		JFile::write(JPATH_BASE.DS.'help'.DS.'helpsites-15.xml', $data);

		$msg = JText::_('The help sites list has been refreshed');
		$this->setRedirect('index.php?option=com_config', $msg);
	}
}
?>