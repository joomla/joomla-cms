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

		$show_hide		= array (JHTML::makeOption(1, JText::_('Hide')), JHTML::makeOption(0, JText::_('Show')),);

		$show_hide_r 	= array (JHTML::makeOption(0, JText::_('Hide')), JHTML::makeOption(1, JText::_('Show')),);

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
		$lists['offline'] = JHTML::yesnoRadioList('offline', 'class="inputbox"', $row->offline);
		if (!$row->editor) {
			$row->editor = '';
		}
		// build the html select list
		$lists['editor'] 		= JHTML::selectList($edits, 'editor', 'class="inputbox" size="1"', 'value', 'text', $row->editor);
		$listLimit 				= array (JHTML::makeOption(5, 5), JHTML::makeOption(10, 10), JHTML::makeOption(15, 15), JHTML::makeOption(20, 20), JHTML::makeOption(25, 25), JHTML::makeOption(30, 30), JHTML::makeOption(50, 50), JHTML::makeOption(100, 100),);
		$lists['list_limit'] 	= JHTML::selectList($listLimit, 'list_limit', 'class="inputbox" size="1"', 'value', 'text', ($row->list_limit ? $row->list_limit : 50));

		jimport('joomla.i18n.help');
		$helpsites 				= array ();
		$helpsites 				= JHelp::createSiteList(JPATH_BASE.DS.'help'.DS.'helpsites-15.xml', $row->helpurl);
		array_unshift($helpsites, JHTML::makeOption('', JText::_('local')));
		$lists['helpsites'] 	= JHTML::selectList($helpsites, 'helpurl', ' class="inputbox"', 'value', 'text', $row->helpurl);

		// DEBUG
		$lists['debug'] 		= JHTML::yesnoRadioList('debug', 'class="inputbox"', $row->debug);
		$lists['debug_db'] 		= JHTML::yesnoRadioList('debug_db', 'class="inputbox"', $row->debug_db);
		$lists['debug_lang'] 	= JHTML::yesnoRadioList('debug_lang', 'class="inputbox"', $row->debug_lang);

		// DATABASE SETTINGS

		// SERVER SETTINGS
		$lists['gzip'] 			= JHTML::yesnoRadioList('gzip', 'class="inputbox"', $row->gzip);
		$errors 				= array (JHTML::makeOption(-1, JText::_('System Default')), JHTML::makeOption(0, JText::_('None')), JHTML::makeOption(E_ERROR | E_WARNING | E_PARSE, JText::_('Simple')), JHTML::makeOption(E_ALL, JText::_('Maximum')));
		$lists['xmlrpc_server'] = JHTML::yesnoRadioList('xmlrpc_server', 'class="inputbox"', $row->xmlrpc_server);
		$lists['error_reporting'] = JHTML::selectList($errors, 'error_reporting', 'class="inputbox" size="1"', 'value', 'text', $row->error_reporting);
		$lists['enable_ftp'] 	= JHTML::yesnoRadioList('ftp_enable', 'class="inputbox"', intval($row->ftp_enable));
		$lists['legacy']		= JHTML::yesnoRadioList('legacy', 'class="inputbox"', $row->legacy);

		// LOCALE SETTINGS
		$timeoffset = array (	JHTML::makeOption(-12, JText::_('(UTC -12:00) International Date Line West')),
								JHTML::makeOption(-11, JText::_('(UTC -11:00) Midway Island, Samoa')),
								JHTML::makeOption(-10, JText::_('(UTC -10:00) Hawaii')),
								JHTML::makeOption(-9.5, JText::_('(UTC -09:30) Taiohae, Marquesas Islands')),
								JHTML::makeOption(-9, JText::_('(UTC -09:00) Alaska')),
								JHTML::makeOption(-8, JText::_('(UTC -08:00) Pacific Time (US &amp; Canada)')),
								JHTML::makeOption(-7, JText::_('(UTC -07:00) Mountain Time (US &amp; Canada)')),
								JHTML::makeOption(-6, JText::_('(UTC -06:00) Central Time (US &amp; Canada), Mexico City')),
								JHTML::makeOption(-5, JText::_('(UTC -05:00) Eastern Time (US &amp; Canada), Bogota, Lima')),
								JHTML::makeOption(-4, JText::_('(UTC -04:00) Atlantic Time (Canada), Caracas, La Paz')),
								JHTML::makeOption(-3.5, JText::_('(UTC -03:30) St. John`s, Newfoundland and Labrador')),
								JHTML::makeOption(-3, JText::_('(UTC -03:00) Brazil, Buenos Aires, Georgetown')),
								JHTML::makeOption(-2, JText::_('(UTC -02:00) Mid-Atlantic')),
								JHTML::makeOption(-1, JText::_('(UTC -01:00) Azores, Cape Verde Islands')),
								JHTML::makeOption(0, JText::_('(UTC 00:00) Western Europe Time, London, Lisbon, Casablanca')),
								JHTML::makeOption(1, JText::_('(UTC +01:00) Amsterdam, Berlin, Brussels, Copenhagen, Madrid, Paris')),
								JHTML::makeOption(2, JText::_('(UTC +02:00) Jerusalem, Kaliningrad, South Africa')),
								JHTML::makeOption(3, JText::_('(UTC +03:00) Baghdad, Riyadh, Moscow, St. Petersburg')),
								JHTML::makeOption(3.5, JText::_('(UTC +03:30) Tehran')),
								JHTML::makeOption(4, JText::_('(UTC +04:00) Abu Dhabi, Muscat, Baku, Tbilisi')),
								JHTML::makeOption(4.5, JText::_('(UTC +04:30) Kabul')),
								JHTML::makeOption(5, JText::_('(UTC +05:00) Ekaterinburg, Islamabad, Karachi, Tashkent')),
								JHTML::makeOption(5.5, JText::_('(UTC +05:30) Bombay, Calcutta, Madras, New Delhi')),
								JHTML::makeOption(5.75, JText::_('(UTC +05:45) Kathmandu')),
								JHTML::makeOption(6, JText::_('(UTC +06:00) Almaty, Dhaka, Colombo')),
								JHTML::makeOption(6.30, JText::_('(UTC +06:30) Yagoon')),
								JHTML::makeOption(7, JText::_('(UTC +07:00) Bangkok, Hanoi, Jakarta')),
								JHTML::makeOption(8, JText::_('(UTC +08:00) Beijing, Perth, Singapore, Hong Kong')),
								JHTML::makeOption(8.75, JText::_('(UTC +08:00) Western Australia')),
								JHTML::makeOption(9, JText::_('(UTC +09:00) Tokyo, Seoul, Osaka, Sapporo, Yakutsk')),
								JHTML::makeOption(9.5, JText::_('(UTC +09:30) Adelaide, Darwin, Yakutsk')),
								JHTML::makeOption(10, JText::_('(UTC +10:00) Eastern Australia, Guam, Vladivostok')),
								JHTML::makeOption(10.5, JText::_('(UTC +10:30) Lord Howe Island (Australia)')),
								JHTML::makeOption(11, JText::_('(UTC +11:00) Magadan, Solomon Islands, New Caledonia')),
								JHTML::makeOption(11.30, JText::_('(UTC +11:30) Norfolk Island')),
								JHTML::makeOption(12, JText::_('(UTC +12:00) Auckland, Wellington, Fiji, Kamchatka')),
								JHTML::makeOption(12.75, JText::_('(UTC +12:45) Chatham Island')),
								JHTML::makeOption(13, JText::_('(UTC +13:00) Tonga')),
								JHTML::makeOption(14, JText::_('(UTC +14:00) Kiribati')),);
		$lists['offset'] 		= JHTML::selectList($timeoffset, 'offset', 'class="inputbox" size="1"', 'value', 'text', $row->offset);

		// MAIL SETTINGS
		$mailer 				= array (JHTML::makeOption('mail', JText::_('PHP mail function')), JHTML::makeOption('sendmail', JText::_('Sendmail')), JHTML::makeOption('smtp', JText::_('SMTP Server')));
		$lists['mailer'] 		= JHTML::selectList($mailer, 'mailer', 'class="inputbox" size="1"', 'value', 'text', $row->mailer);
		$lists['smtpauth'] 		= JHTML::yesnoRadioList('smtpauth', 'class="inputbox"', $row->smtpauth);

		// CACHE SETTINGS
		$lists['caching'] 		= JHTML::yesnoRadioList('caching', 'class="inputbox"', $row->caching);
		$lists['caching_tmpl'] 		= JHTML::yesnoRadioList('caching_tmpl', 'class="inputbox"', $row->caching_tmpl);
		$lists['caching_page'] 		= JHTML::yesnoRadioList('caching_page', 'class="inputbox"', $row->caching_page);

		// USER SETTINGS
		$lists['allowUserRegistration'] = JHTML::yesnoRadioList('allowUserRegistration', 'class="inputbox"', $row->allowUserRegistration);
		$lists['useractivation'] 		= JHTML::yesnoRadioList('useractivation', 'class="inputbox"', $row->useractivation);
		$lists['shownoauth'] 			= JHTML::yesnoRadioList('shownoauth', 'class="inputbox"', $row->shownoauth);
		$lists['frontend_userparams'] 	= JHTML::yesnoRadioList('frontend_userparams', 'class="inputbox"', $row->frontend_userparams);
		$new_usertype = array (
			JHTML::makeOption('Registered', 	JText::_('Registered')),
			JHTML::makeOption('Author', 		JText::_('Author')),
			JHTML::makeOption('Editor', 		JText::_('Editor')),
			JHTML::makeOption('Publisher', 	JText::_('Publisher')),
		);
		$lists['new_usertype']			= JHTML::selectList($new_usertype, 'new_usertype', 'class="inputbox" size="1"', 'value', 'text', $row->new_usertype);

		// META SETTINGS
		$lists['MetaAuthor'] 	= JHTML::yesnoRadioList('MetaAuthor', 'class="inputbox"', $row->MetaAuthor);
		$lists['MetaTitle'] 	= JHTML::yesnoRadioList('MetaTitle', 'class="inputbox"', $row->MetaTitle);

		// STATISTICS SETTINGS
		$lists['log_searches'] 	= JHTML::yesnoRadioList('enable_log_searches', 'class="inputbox"', $row->enable_log_searches);
		$lists['enable_stats'] 	= JHTML::yesnoRadioList('enable_stats', 'class="inputbox"', $row->enable_stats);
		$lists['log_items'] 	= JHTML::yesnoRadioList('enable_log_items', 'class="inputbox"', $row->enable_log_items);

		// SEO SETTINGS
		$lists['sef'] 			= JHTML::yesnoRadioList('sef', 'class="inputbox"', $row->sef);

		// CONTENT SETTINGS
		$lists['link_titles'] 	= JHTML::yesnoRadioList('link_titles', 'class="inputbox"', $row->link_titles);
		$lists['readmore'] 		= JHTML::RadioList($show_hide_r, 'readmore', 'class="inputbox"', $row->readmore, 'value', 'text');
		$lists['vote'] 			= JHTML::RadioList($show_hide_r, 'vote', 'class="inputbox"', $row->vote, 'value', 'text');
		$lists['hideAuthor'] 	= JHTML::RadioList($show_hide, 'hideAuthor', 'class="inputbox"', $row->hideAuthor, 'value', 'text');
		$lists['hideCreateDate'] = JHTML::RadioList($show_hide, 'hideCreateDate', 'class="inputbox"', $row->hideCreateDate, 'value', 'text');
		$lists['hideModifyDate'] = JHTML::RadioList($show_hide, 'hideModifyDate', 'class="inputbox"', $row->hideModifyDate, 'value', 'text');
		$lists['hits'] 			= JHTML::RadioList($show_hide_r, 'hits', 'class="inputbox"', $row->hits, 'value', 'text');
		if (is_writable(JPATH_SITE.DS.'tmp'.DS)) {
			$lists['hidePdf'] = JHTML::RadioList($show_hide, 'hidePdf', 'class="inputbox"', $row->hidePdf, 'value', 'text');
		} else {
			$lists['hidePdf'] = '<input type="hidden" name="hidePdf" value="1" /><strong>Hide</strong>';
		}
		$lists['hidePrint'] 	= JHTML::RadioList($show_hide, 'hidePrint', 'class="inputbox"', $row->hidePrint, 'value', 'text');
		$lists['hideEmail'] 	= JHTML::RadioList($show_hide, 'hideEmail', 'class="inputbox"', $row->hideEmail, 'value', 'text');
		$lists['icons'] 		= JHTML::yesnoRadioList('icons', 'class="inputbox"', $row->icons, 'icons', 'text');
		$lists['ml_support'] 	= JHTML::yesnoRadioList('multilingual_support', 'class="inputbox" onclick="if (document.adminForm.multilingual_support[1].checked) { alert(\''.JText::_('Remember to install the MambelFish component.', true).'\') }"', $row->multilingual_support);

		// FEED SETTINGS
		$formats  = array (JHTML::makeOption('RSS2.0', JText::_('RSS')), JHTML::makeOption('Atom', JText::_('Atom')));
		$summary = array (JHTML::makeOption(1, JText::_('Full Text')), JHTML::makeOption(0, JText::_('Intro Text')),);
		$lists['feed_limit']   = JHTML::selectList($listLimit, 'feed_limit', 'class="inputbox" size="1"', 'value', 'text', ($row->feed_limit ? $row->feed_limit : 10));
		$lists['feed_excerpt'] = JHTML::RadioList($summary, 'feed_summary', 'class="inputbox"', $row->feed_excerpt);

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