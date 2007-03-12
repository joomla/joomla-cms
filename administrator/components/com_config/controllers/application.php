<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Config
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

require_once( JPATH_COMPONENT.DS.'views'.DS.'application'.DS.'view.php' );

/**
 * @package		Joomla
 * @subpackage	Config
 */
class ConfigControllerApplication extends ConfigController
{
	/**
	 * Custom Constructor
	 */
	function __construct( $default = array() )
	{
		$default['default_task'] = 'showConfig';
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
		$query = 'SELECT element AS value, name AS text'
				.' FROM #__plugins'
				.' WHERE folder = "editors"'
				.' AND published = 1'
				.' ORDER BY ordering, name'
				;
		$db->setQuery($query);
		$edits = $db->loadObjectList();

		// -- Show/Hide --

		$show_hide		= array (JHTMLSelect::option(1, JText::_('Hide')), JHTMLSelect::option(0, JText::_('Show')),);

		$show_hide_r 	= array (JHTMLSelect::option(0, JText::_('Hide')), JHTMLSelect::option(1, JText::_('Show')),);

		// -- menu items --

		$query = 'SELECT id AS value, name AS text FROM #__menu'
				.' WHERE ( type="content_section" OR type="components" OR type="content_typed" )'
				.' AND published = 1'
				.' AND access = 0'
				.' ORDER BY name'
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

		// META SETTINGS
		$lists['MetaAuthor'] 	= JHTMLSelect::yesnoList('MetaAuthor', 'class="inputbox"', $row->MetaAuthor);
		$lists['MetaTitle'] 	= JHTMLSelect::yesnoList('MetaTitle', 'class="inputbox"', $row->MetaTitle);

		// SEO SETTINGS
		$lists['sef'] 			= JHTMLSelect::yesnoList('sef', 'class="inputbox"', $row->sef);
		$lists['sef_rewrite'] 	= JHTMLSelect::yesnoList('sef_rewrite', 'class="inputbox"', $row->sef_rewrite);

		// FEED SETTINGS
		$formats	= array (JHTMLSelect::option('RSS2.0', JText::_('RSS')), JHTMLSelect::option('Atom', JText::_('Atom')));
		$summary	= array (JHTMLSelect::option(1, JText::_('Full Text')), JHTMLSelect::option(0, JText::_('Intro Text')),);
		$lists['feed_limit']	= JHTMLSelect::genericList($listLimit, 'feed_limit', 'class="inputbox" size="1"', 'value', 'text', ($row->feed_limit ? $row->feed_limit : 10));
		$lists['feed_summary']	= JHTMLSelect::radioList($summary, 'feed_summary', 'class="inputbox"', $row->feed_summary);

		// SESSION SETTINGS
		$stores = JSession::getStores();
		$options = array();
		foreach($stores as $store) {
			$options[] = JHTMLSelect::option($store, $store);
		}
		$lists['session_handlers'] = JHTMLSelect::genericList($options, 'session_handler', 'class="inputbox" size="1"', 'value', 'text', $row->session_handler);

		// SHOW EDIT FORM
		ConfigApplicationView::showConfig($row, $lists);
	}

	/**
	 * Save the configuration
	 */
	function save()
	{
		global $mainframe;

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');
		$ftp = JClientHelper::getCredentials('ftp');

		//Save user and media manager settings
		$table =& JTable::getInstance('component');

		$userpost['params'] = JRequest::getVar('userparams');
		$userpost['option'] = 'com_users';
		$table->loadByOption( 'com_users' );
		$table->bind( $userpost );

		// pre-save checks
		if (!$table->check()) {
			JError::raiseWarning( 500, $table->getError() );
			return false;
		}

		// save the changes
		if (!$table->store()) {
			JError::raiseWarning( 500, $table->getError() );
			return false;
		}

		$mediapost['params'] = JRequest::getVar('mediaparams');
		$mediapost['option'] = 'com_media';
		$table->loadByOption( 'com_media' );
		$table->bind( $mediapost );

		// pre-save checks
		if (!$table->check()) {
			JError::raiseWarning( 500, $table->getError() );
			return false;
		}

		// save the changes
		if (!$table->store()) {
			JError::raiseWarning( 500, $table->getError() );
			return false;
		}

		$config =& JFactory::getConfig();
		$config_array = array();

		// SITE SETTINGS
		$config_array['offline'] 	= JRequest::getVar('offline', 0, 'post');
		$config_array['editor'] 	= JRequest::getVar('editor', 'tinymce', 'post');
		$config_array['list_limit'] 	= JRequest::getVar('list_limit', 20, 'post');
		$config_array['helpurl'] 	= JRequest::getVar('helpurl', 'http://help.joomla.org', 'post');

		// DEBUG
		$config_array['debug'] 		= JRequest::getVar('debug', 0, 'post');
		$config_array['debug_db'] 	= JRequest::getVar('debug_db', 0, 'post');
		$config_array['debug_lang'] 	= JRequest::getVar('debug_lang', 0, 'post');

		// SEO SETTINGS
		$config_array['sef'] 			= JRequest::getVar('sef', 0, 'post');
		$config_array['sef_rewrite'] 	= JRequest::getVar('sef_rewrite', 0, 'post');

		// FEED SETTINGS
		$config_array['feed_limit']		= JRequest::getVar('feed_limit', 10, 'post');
		$config_array['feed_summary']	= JRequest::getVar('feed_summary', 0, 'post');

		// SERVER SETTINGS
		$config_array['gzip'] 		= JRequest::getVar('gzip', 0, 'post');
		$config_array['error_reporting'] = JRequest::getVar('error_reporting', -1, 'post');
		$config_array['xmlrpc_server'] = JRequest::getVar('xmlrpc_server', 0, 'post');
		$config_array['log_path']	= JRequest::getVar('log_path', JPATH_ROOT.DS.'logs', 'post');
		$config_array['tmp_path']	= JRequest::getVar('tmp_path', JPATH_ROOT.DS.'tmp', 'post');

		// LOCALE SETTINGS
		$config_array['offset'] 	= JRequest::getVar('offset', 0, 'post');

		// CACHE SETTINGS
		$config_array['caching'] 	= JRequest::getVar('caching', 0, 'post');

		// FTP SETTINGS
		$config_array['ftp_enable'] 	= JRequest::getVar('ftp_enable', 0, 'post');
		$config_array['ftp_host'] 	= JRequest::getVar('ftp_host', '', 'post');
		$config_array['ftp_port'] 	= JRequest::getVar('ftp_port', '', 'post');
		$config_array['ftp_user'] 	= JRequest::getVar('ftp_user', '', 'post');
		$config_array['ftp_pass'] 	= JRequest::getVar('ftp_pass', '', 'post');
		$config_array['ftp_root'] 	= JRequest::getVar('ftp_root', '', 'post');

		// DATABASE SETTINGS
		$config_array['dbtype'] 		= JRequest::getVar('dbtype', 'mysql', 'post');
		$config_array['host'] 		= JRequest::getVar('host', 'localhost', 'post');
		$config_array['user'] 		= JRequest::getVar('user', '', 'post');
		$config_array['db'] 			= JRequest::getVar('db', '', 'post');
		$config_array['db_prefix'] 	= JRequest::getVar('db_prefix', 'jos_', 'post');

		// MAIL SETTINGS
		$config_array['mailer'] 		= JRequest::getVar('mailer', 'mail', 'post');
		$config_array['mailfrom'] 	= JRequest::getVar('mailfrom', '', 'post');
		$config_array['fromname'] 	= JRequest::getVar('fromname', 'Joomla 1.5', 'post');
		$config_array['sendmail'] 	= JRequest::getVar('sendmail', '/usr/sbin/sendmail', 'post');
		$config_array['smtpauth'] 	= JRequest::getVar('smtpauth', 0, 'post');
		$config_array['smtpuser'] 	= JRequest::getVar('smtpuser', '', 'post');
		$config_array['smtppass'] 	= JRequest::getVar('smtppass', '', 'post');
		$config_array['smtphost'] 	= JRequest::getVar('smtphost', '', 'post');

		// META SETTINGS
		$config_array['MetaAuthor'] 	= JRequest::getVar('MetaAuthor', 1, 'post');
		$config_array['MetaTitle'] 	= JRequest::getVar('MetaTitle', 1, 'post');

		// SESSION SETTINGS
		$config_array['lifetime'] 			= JRequest::getVar('lifetime', 0, 'post');
		$config_array['session_handler'] 	= JRequest::getVar('session_handler', 'none', 'post');

		$config->loadArray($config_array);

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
		jimport('joomla.filter.output');
		$offline_message	= JRequest::getVar( 'offline_message', '', 'post' );
		$offline_message	= JOUtputFilter::ampReplace( $offline_message );
		$offline_message	= str_replace( '"', '&quot;', $offline_message );
		$offline_message	= str_replace( "'", '&#039;', $offline_message );
		$config->setValue('config.offline_message', $offline_message);

		//purge the database session table (only if we are changing to a db session store)
		if($mainframe->getCfg('session_handler') != 'database' && $config->getValue('session_handler') == 'database')
		{
			$table =& JTable::getInstance('session');
			$table->purge(-1);
		}

		// Get the path of the configuration file
		$fname = JPATH_CONFIGURATION.DS.'configuration.php';

		// Try to make configuration.php writeable
		jimport('joomla.filesystem.path');
		if (!$ftp['enabled'] && !JPath::setPermissions($fname, '0755')) {
			JError::raiseNotice('SOME_ERROR_CODE', 'Could not make configuration.php writeable');
		}

		// Get the config registry in PHP class format and write it to configuation.php
		jimport('joomla.filesystem.file');
		if (JFile::write($fname, $config->toString('PHP', 'config', array('class' => 'JConfig')))) {
			$msg = JText::_('The Configuration Details have been updated');
		} else {
			$msg = JText::_('ERRORCONFIGFILE');
		}

		// Redirect appropriately
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

		// Try to make configuration.php unwriteable
		if (!$ftp['enabled'] && !JPath::setPermissions($fname, '0555')) {
			JError::raiseNotice('SOME_ERROR_CODE', 'Could not make configuration.php unwriteable');
		}
	}

	/**
	 * Cancel operation
	 */
	function cancel()
	{
		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');

		$this->setRedirect( 'index.php' );
	}

	function refreshHelp()
	{
		jimport('joomla.filesystem.file');

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');

		if (($data = file_get_contents('http://help.joomla.org/helpsites-15.xml')) === false ) {
			$this->setRedirect('index.php?option=com_config', JText::_('HELPREFRESH ERROR FETCH'), 'error');
		} else if (!JFile::write(JPATH_BASE.DS.'help'.DS.'helpsites-15.xml', $data)) {
			$this->setRedirect('index.php?option=com_config', JText::_('HELPREFRESH ERROR STORE'), 'error');
		} else {
			$this->setRedirect('index.php?option=com_config', JText::_('HELPREFRESH SUCCESS'));
		}
	}
}
?>
