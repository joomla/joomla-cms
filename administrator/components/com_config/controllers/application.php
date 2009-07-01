<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Config
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

require_once(JPATH_COMPONENT.DS.'views'.DS.'application'.DS.'view.php');

/**
 * @package		Joomla.Administrator
 * @subpackage	Config
 */
class ConfigControllerApplication extends ConfigController
{
	/**
	 * Custom Constructor
	 */
	function __construct($default = array())
	{
		$default['default_task'] = 'showConfig';
		parent::__construct($default);

		$this->registerTask('apply', 'save');
	}

	/**
	 * Show the configuration edit form
	 * @param string The URL option
	 */
	function showConfig()
	{
		// Initialize some variables
		$db = &JFactory::getDbo();
		$row = new JConfig();

		// compile list of the languages
		$langs 		= array ();
		$menuitems 	= array ();
		$lists 		= array ();

		// PRE-PROCESS SOME LIST

		// -- Editors --

		// compile list of the editors
		$query = 'SELECT element AS value, name AS text'
				.' FROM #__extensions'
				.' WHERE folder = "editors"'
				.' AND type = "plugin"'
				.' AND enabled = 1'
				.' ORDER BY ordering, name'
				;
		$db->setQuery($query);
		$edits = $db->loadObjectList();

		// -- Show/Hide --

		$show_hide		= array (JHtml::_('select.option', 1, JText::_('Hide')), JHtml::_('select.option', 0, JText::_('Show')),);

		$show_hide_r 	= array (JHtml::_('select.option', 0, JText::_('Hide')), JHtml::_('select.option', 1, JText::_('Show')),);

		// -- menu items --

		$query = 'SELECT id AS value, title FROM #__menu'
				.' WHERE (type="content_section" OR type="components" OR type="content_typed")'
				.' AND published = 1'
				.' AND access = 0'
				.' ORDER BY title'
				;
		$db->setQuery($query);
		$menuitems = array_merge($menuitems, $db->loadObjectList());

		// SITE SETTINGS
		$lists['offline'] = JHtml::_('select.booleanlist', 'offline', 'class="inputbox"', $row->offline);
		if (!$row->editor) {
			$row->editor = '';
		}
		// build the html select list
		$lists['editor'] 		= JHtml::_('select.genericlist',  $edits, 'editor', 'class="inputbox" size="1"', 'value', 'text', $row->editor);
		$lists['access']		= JHtml::_('access.assetgroups', 'access', $row->access, 'class="inputbox" size="1"');
		$listLimit 				= array (JHtml::_('select.option', 5, 5), JHtml::_('select.option', 10, 10), JHtml::_('select.option', 15, 15), JHtml::_('select.option', 20, 20), JHtml::_('select.option', 25, 25), JHtml::_('select.option', 30, 30), JHtml::_('select.option', 50, 50), JHtml::_('select.option', 100, 100),);
		$lists['list_limit'] 	= JHtml::_('select.genericlist',  $listLimit, 'list_limit', 'class="inputbox" size="1"', 'value', 'text', ($row->list_limit ? $row->list_limit : 50));

		jimport('joomla.language.help');
		$helpsites 				= array ();
		$helpsites 				= JHelp::createSiteList(JPATH_BASE.DS.'help'.DS.'helpsites-15.xml', $row->helpurl);
		array_unshift($helpsites, JHtml::_('select.option', '', JText::_('local')));
		$lists['helpsites'] 	= JHtml::_('select.genericlist',  $helpsites, 'helpurl', ' class="inputbox"', 'value', 'text', $row->helpurl);

		// DEBUG
		$lists['debug'] 		= JHtml::_('select.booleanlist', 'debug', 'class="inputbox"', $row->debug);
		$lists['debug_lang'] 	= JHtml::_('select.booleanlist', 'debug_lang', 'class="inputbox"', $row->debug_lang);
		$lists['debug_modules'] = JHtml::_('select.booleanlist', 'debug_modules', 'class="inputbox"', @$row->debug_modules, 'JEnabled', 'JDisabled');

		// DATABASE SETTINGS

		// SERVER SETTINGS
		$lists['gzip'] 			= JHtml::_('select.booleanlist', 'gzip', 'class="inputbox"', $row->gzip);
		$errors 				= array (JHtml::_('select.option', -1, JText::_('System Default')), JHtml::_('select.option', 0, JText::_('None')), JHtml::_('select.option', E_ERROR | E_WARNING | E_PARSE, JText::_('Simple')), JHtml::_('select.option', E_ALL, JText::_('Maximum')));
		$lists['xmlrpc_server'] = JHtml::_('select.booleanlist', 'xmlrpc_server', 'class="inputbox"', $row->xmlrpc_server);
		$lists['error_reporting'] = JHtml::_('select.genericlist',  $errors, 'error_reporting', 'class="inputbox" size="1"', 'value', 'text', $row->error_reporting);
		$lists['enable_ftp'] 	= JHtml::_('select.booleanlist', 'ftp_enable', 'class="inputbox"', intval($row->ftp_enable));

		$forceSSL = array(
								JHtml::_('select.option', 0, JText::_('None')),
								JHtml::_('select.option', 1, JText::_('Administrator Only')),
								JHtml::_('select.option', 2, JText::_('Entire Site')),
						);
		$lists['force_ssl'] = JHtml::_('select.genericlist', $forceSSL, 'force_ssl', 'class="inputbox" size="1"', 'value', 'text', @$row->force_ssl);

		// LOCALE SETTINGS
		$timeoffset = array (	JHtml::_('select.option', -12, JText::_('(UTC -12:00) International Date Line West')),
								JHtml::_('select.option', -11, JText::_('(UTC -11:00) Midway Island, Samoa')),
								JHtml::_('select.option', -10, JText::_('(UTC -10:00) Hawaii')),
								JHtml::_('select.option', -9.5, JText::_('(UTC -09:30) Taiohae, Marquesas Islands')),
								JHtml::_('select.option', -9, JText::_('(UTC -09:00) Alaska')),
								JHtml::_('select.option', -8, JText::_('(UTC -08:00) Pacific Time (US &amp; Canada)')),
								JHtml::_('select.option', -7, JText::_('(UTC -07:00) Mountain Time (US &amp; Canada)')),
								JHtml::_('select.option', -6, JText::_('(UTC -06:00) Central Time (US &amp; Canada), Mexico City')),
								JHtml::_('select.option', -5, JText::_('(UTC -05:00) Eastern Time (US &amp; Canada), Bogota, Lima')),
								JHtml::_('select.option', -4.5, JText::_('(UTC -04:30) Venezuela')),
								JHtml::_('select.option', -4, JText::_('(UTC -04:00) Atlantic Time (Canada), Caracas, La Paz')),
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
								JHtml::_('select.option', 5.5, JText::_('(UTC +05:30) Bombay, Calcutta, Madras, New Delhi, Colombo')),
								JHtml::_('select.option', 5.75, JText::_('(UTC +05:45) Kathmandu')),
								JHtml::_('select.option', 6, JText::_('(UTC +06:00) Almaty, Dhaka')),
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
								JHtml::_('select.option', 14, JText::_('(UTC +14:00) Kiribati')),);
		$lists['offset'] 		= JHtml::_('select.genericlist',  $timeoffset, 'offset', 'class="inputbox" size="1"', 'value', 'text', $row->offset);

		// MAIL SETTINGS
		$mailer 				= array (JHtml::_('select.option', 'mail', JText::_('PHP mail function')), JHtml::_('select.option', 'sendmail', JText::_('Sendmail')), JHtml::_('select.option', 'smtp', JText::_('SMTP Server')));
		$lists['mailer'] 		= JHtml::_('select.genericlist',  $mailer, 'mailer', 'class="inputbox" size="1"', 'value', 'text', $row->mailer);
		$lists['smtpauth'] 		= JHtml::_('select.booleanlist', 'smtpauth', 'class="inputbox"', $row->smtpauth);

		// CACHE SETTINGS
		$lists['caching'] 		= JHtml::_('select.booleanlist', 'caching', 'class="inputbox"', $row->caching);
		jimport('joomla.cache.cache');
		$stores = JCache::getStores();
		$options = array();
		foreach($stores as $store) {
			$options[] = JHtml::_('select.option', $store, JText::_(ucfirst($store)));
		}
		$lists['cache_handlers'] = JHtml::_('select.genericlist',  $options, 'cache_handler', 'class="inputbox" size="1"', 'value', 'text', $row->cache_handler);

		// MEMCACHE SETTINGS
		if (!empty($row->memcache_settings) && !is_array($row->memcache_settings)) {
			$row->memcache_settings = unserialize(stripslashes($row->memcache_settings));
		}
		$lists['memcache_persist'] = JHtml::_('select.booleanlist', 'memcache_settings[persistent]', 'class="inputbox"', @$row->memcache_settings['persistent']);
		$lists['memcache_compress'] = JHtml::_('select.booleanlist', 'memcache_settings[compression]', 'class="inputbox"', @$row->memcache_settings['compression']);

		// META SETTINGS
		$lists['MetaAuthor'] 	= JHtml::_('select.booleanlist', 'MetaAuthor', 'class="inputbox"', $row->MetaAuthor);
		$lists['MetaTitle'] 	= JHtml::_('select.booleanlist', 'MetaTitle', 'class="inputbox"', $row->MetaTitle);

		// SEO SETTINGS
		$lists['sef'] 			= JHtml::_('select.booleanlist', 'sef', 'class="inputbox"', $row->sef);
		$lists['sef_rewrite'] 	= JHtml::_('select.booleanlist', 'sef_rewrite', 'class="inputbox"', $row->sef_rewrite);
		$lists['sef_suffix'] 	= JHtml::_('select.booleanlist', 'sef_suffix', 'class="inputbox"', $row->sef_suffix);

		// FEED SETTINGS
		$formats	= array (JHtml::_('select.option', 'RSS2.0', JText::_('RSS')), JHtml::_('select.option', 'Atom', JText::_('Atom')));
		$summary	= array (JHtml::_('select.option', 1, JText::_('Full Text')), JHtml::_('select.option', 0, JText::_('Intro Text')),);
		$lists['feed_limit']	= JHtml::_('select.genericlist',  $listLimit, 'feed_limit', 'class="inputbox" size="1"', 'value', 'text', ($row->feed_limit ? $row->feed_limit : 10));
		$emailOptions = array (	JHtml::_('select.option', 'author', JText::_('Author Email')),
								JHtml::_('select.option', 'site', JText::_('Site Email')));
		$lists['feed_email'] = JHtml::_('select.genericlist', $emailOptions, 'feed_email', 'class="inputbox" size="1"', 'value', 'text', (@$row->feed_email) ? $row->feed_email : 'author');

		// SESSION SETTINGS
		$stores = JSession::getStores();
		$options = array();
		foreach($stores as $store) {
			$options[] = JHtml::_('select.option', $store, JText::_(ucfirst($store)));
		}
		$lists['session_handlers'] = JHtml::_('select.genericlist',  $options, 'session_handler', 'class="inputbox" size="1"', 'value', 'text', $row->session_handler);

		// SHOW EDIT FORM
		ConfigApplicationView::showConfig($row, $lists);
	}

	/**
	 * Save the configuration
	 */
	function save()
	{
		global $mainframe;

		// Check for request forgeries
		JRequest::checkToken() or jexit('Invalid Token');

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');
		$ftp = JClientHelper::getCredentials('ftp');

		//Save user and media manager settings
		$table = &JTable::getInstance('component');

		$userpost['params'] = JRequest::getVar('userparams', array(), 'post', 'array');
		$userpost['option'] = 'com_users';
		$table->loadByOption('com_users');
		$table->bind($userpost);

		// pre-save checks
		if (!$table->check()) {
			JError::raiseWarning(500, $table->getError());
			return false;
		}

		// save the changes
		if (!$table->store()) {
			JError::raiseWarning(500, $table->getError());
			return false;
		}

		$mediapost['params'] = JRequest::getVar('mediaparams', array(), 'post', 'array');
		$mediapost['option'] = 'com_media';
		//Sanitize $file_path and $image_path
		$file_path = $mediapost['params']['file_path'];
		$image_path = $mediapost['params']['image_path'];
		if (strpos($file_path, '/') === 0 || strpos($file_path, '\\') === 0) {
			//Leading slash.  Kill it and default to /media
			$file_path = 'images';
		}
		if (strpos($image_path, '/') === 0 || strpos($image_path, '\\') === 0) {
			//Leading slash.  Kill it and default to /media
			$image_path = 'images/stories';
		}
		if (strpos($file_path, '..') !== false) {
			//downward directories.  Kill it and default to images/
			$file_path = 'images';
		}
		if (strpos($image_path, '..') !== false) {
			//downward directories  Kill it and default to images/stories
			$image_path = 'images/stories';
		}
		$mediapost['params']['file_path'] = $file_path;
		$mediapost['params']['image_path'] = $image_path;

		$table->loadByOption('com_media');
		$table->bind($mediapost);

		// pre-save checks
		if (!$table->check()) {
			JError::raiseWarning(500, $table->getError());
			return false;
		}

		// save the changes
		if (!$table->store()) {
			JError::raiseWarning(500, $table->getError());
			return false;
		}

		$config = new JRegistry('config');
		$config_array = array();

		// SITE SETTINGS
		$config_array['offline']	= JRequest::getVar('offline', 0, 'post', 'int');
		$config_array['editor']		= JRequest::getVar('editor', 'tinymce', 'post', 'cmd');
		$config_array['access']		= JRequest::getVar('access', '1', 'post', 'int');
		$config_array['list_limit']	= JRequest::getVar('list_limit', 20, 'post', 'int');
		$config_array['helpurl']	= JRequest::getVar('helpurl', 'http://help.joomla.org', 'post', 'string');
		$config_array['root_user']	= JRequest::getVar('root_user', '42', 'post', 'int');

		// DEBUG
		$config_array['debug']			= JRequest::getInt('debug', 0, 'post');
		$config_array['debug_lang']		= JRequest::getInt('debug_lang', 0, 'post');
		$config_array['debug_modules']	= JRequest::getInt('debug_modules', 1, 'post');

		// SEO SETTINGS
		$config_array['sef']			= JRequest::getVar('sef', 0, 'post', 'int');
		$config_array['sef_rewrite']	= JRequest::getVar('sef_rewrite', 0, 'post', 'int');
		$config_array['sef_suffix']		= JRequest::getVar('sef_suffix', 0, 'post', 'int');

		// FEED SETTINGS
		$config_array['feed_limit']		= JRequest::getVar('feed_limit', 10, 'post', 'int');
		$config_array['feed_email']		= JRequest::getVar('feed_email', 'author', 'post', 'word');

		// SERVER SETTINGS
		$config_array['secret']				= JRequest::getVar('secret', 0, 'post', 'string');
		$config_array['gzip']				= JRequest::getVar('gzip', 0, 'post', 'int');
		$config_array['error_reporting']	= JRequest::getVar('error_reporting', -1, 'post', 'int');
		$config_array['xmlrpc_server']		= JRequest::getVar('xmlrpc_server', 0, 'post', 'int');
		$config_array['log_path']			= JRequest::getVar('log_path', JPATH_ROOT.DS.'logs', 'post', 'string');
		$config_array['tmp_path']			= JRequest::getVar('tmp_path', JPATH_ROOT.DS.'tmp', 'post', 'string');
		$config_array['live_site'] 			= rtrim(JRequest::getVar('live_site','','post','string'), '/\\');
		$config_array['force_ssl'] 			= JRequest::getVar('force_ssl', 0, 'post', 'int');

		// LOCALE SETTINGS
		$config_array['offset']				= JRequest::getVar('offset', 0, 'post', 'float');

		// CACHE SETTINGS
		$config_array['caching']			= JRequest::getVar('caching', 0, 'post', 'int');
		$config_array['cachetime']			= JRequest::getVar('cachetime', 900, 'post', 'int');
		$config_array['cache_handler']		= JRequest::getVar('cache_handler', 'file', 'post', 'word');
		$config_array['memcache_settings']	= JRequest::getVar('memcache_settings', array(), 'post');

		// FTP SETTINGS
		$config_array['ftp_enable']	= JRequest::getVar('ftp_enable', 0, 'post', 'int');
		$config_array['ftp_host']	= JRequest::getVar('ftp_host', '', 'post', 'string');
		$config_array['ftp_port']	= JRequest::getVar('ftp_port', '', 'post', 'int');
		$config_array['ftp_user']	= JRequest::getVar('ftp_user', '', 'post', 'string');
		$config_array['ftp_pass']	= JRequest::getVar('ftp_pass', '', 'post', 'string', JREQUEST_ALLOWRAW);
		$config_array['ftp_root']	= JRequest::getVar('ftp_root', '', 'post', 'string');

		// DATABASE SETTINGS
		$config_array['dbtype']		= JRequest::getVar('dbtype', 'mysql', 'post', 'word');
		$config_array['host']		= JRequest::getVar('host', 'localhost', 'post', 'string');
		$config_array['user']		= JRequest::getVar('user', '', 'post', 'string');
		$config_array['db']			= JRequest::getVar('db', '', 'post', 'string');
		$config_array['dbprefix']	= JRequest::getVar('dbprefix', 'jos_', 'post', 'string');

		// MAIL SETTINGS
		$config_array['mailer']		= JRequest::getVar('mailer', 'mail', 'post', 'word');
		$config_array['mailfrom']	= JRequest::getVar('mailfrom', '', 'post', 'string');
		$config_array['fromname']	= JRequest::getVar('fromname', 'Joomla 1.5', 'post', 'string');
		$config_array['sendmail']	= JRequest::getVar('sendmail', '/usr/sbin/sendmail', 'post', 'string');
		$config_array['smtpauth']	= JRequest::getVar('smtpauth', 0, 'post', 'int');
		$config_array['smtpuser']	= JRequest::getVar('smtpuser', '', 'post', 'string');
		$config_array['smtppass']	= JRequest::getVar('smtppass', '', 'post', 'string', JREQUEST_ALLOWRAW);
		$config_array['smtphost']	= JRequest::getVar('smtphost', '', 'post', 'string');

		// META SETTINGS
		$config_array['MetaAuthor']	= JRequest::getVar('MetaAuthor', 1, 'post', 'int');
		$config_array['MetaTitle']	= JRequest::getVar('MetaTitle', 1, 'post', 'int');

		// SESSION SETTINGS
		$config_array['lifetime']			= JRequest::getVar('lifetime', 0, 'post', 'int');
		$config_array['session_handler']	= JRequest::getVar('session_handler', 'none', 'post', 'word');

		//LANGUAGE SETTINGS
		//$config_array['lang']				= JRequest::getVar('lang', 'none', 'english', 'cmd');
		//$config_array['language']			= JRequest::getVar('language', 'en-GB', 'post', 'cmd');

		$config->loadArray($config_array);

		//override any possible database password change
		$config->setValue('config.password', $mainframe->getCfg('password'));

		// handling of special characters
		$sitename			= htmlspecialchars(JRequest::getVar('sitename', '', 'post', 'string'), ENT_COMPAT, 'UTF-8');
		$config->setValue('config.sitename', $sitename);

		$MetaDesc			= htmlspecialchars(JRequest::getVar('MetaDesc', '', 'post', 'string'),  ENT_COMPAT, 'UTF-8');
		$config->setValue('config.MetaDesc', $MetaDesc);

		$MetaKeys			= htmlspecialchars(JRequest::getVar('MetaKeys', '', 'post', 'string'),  ENT_COMPAT, 'UTF-8');
		$config->setValue('config.MetaKeys', $MetaKeys);

		// handling of quotes (double and single) and amp characters
		// htmlspecialchars not used to preserve ability to insert other html characters
		$offline_message	= JRequest::getVar('offline_message', '', 'post', 'string');
		$offline_message	= JFilterOutput::ampReplace($offline_message);
		$offline_message	= str_replace('"', '&quot;', $offline_message);
		$offline_message	= str_replace("'", '&#039;', $offline_message);
		$config->setValue('config.offline_message', $offline_message);

		//purge the database session table (only if we are changing to a db session store)
		if ($mainframe->getCfg('session_handler') != 'database' && $config->getValue('session_handler') == 'database')
		{
			$table = &JTable::getInstance('session');
			$table->purge(-1);
		}

		// Get the path of the configuration file
		$fname = JPATH_CONFIGURATION.DS.'configuration.php';

		// Update the credentials with the new settings
		$oldconfig = &JFactory::getConfig();
		$oldconfig->setValue('config.ftp_enable', $config_array['ftp_enable']);
		$oldconfig->setValue('config.ftp_host', $config_array['ftp_host']);
		$oldconfig->setValue('config.ftp_port', $config_array['ftp_port']);
		$oldconfig->setValue('config.ftp_user', $config_array['ftp_user']);
		$oldconfig->setValue('config.ftp_pass', $config_array['ftp_pass']);
		$oldconfig->setValue('config.ftp_root', $config_array['ftp_root']);
		JClientHelper::getCredentials('ftp', true);

		if (!$config->get('caching') && $oldconfig->get('caching')) {
			$cache = JFactory::getCache();
			$cache->clean();
		}

		// Try to make configuration.php writeable
		jimport('joomla.filesystem.path');
		if (!$ftp['enabled'] && JPath::isOwner($fname) && !JPath::setPermissions($fname, '0644')) {
			JError::raiseNotice('SOME_ERROR_CODE', 'Could not make configuration.php writable');
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
		//if (!$ftp['enabled'] && JPath::isOwner($fname) && !JPath::setPermissions($fname, '0444')) {
		if ($config_array['ftp_enable']==0 && !$ftp['enabled'] && JPath::isOwner($fname) && !JPath::setPermissions($fname, '0444')) {
			JError::raiseNotice('SOME_ERROR_CODE', 'Could not make configuration.php unwritable');
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

		$this->setRedirect('index.php');
	}

	function refreshHelp()
	{
		jimport('joomla.filesystem.file');

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');

		if (($data = file_get_contents('http://help.joomla.org/helpsites-15.xml')) === false) {
			$this->setRedirect('index.php?option=com_config', JText::_('HELPREFRESH ERROR FETCH'), 'error');
		} else if (!JFile::write(JPATH_BASE.DS.'help'.DS.'helpsites-15.xml', $data)) {
			$this->setRedirect('index.php?option=com_config', JText::_('HELPREFRESH ERROR STORE'), 'error');
		} else {
			$this->setRedirect('index.php?option=com_config', JText::_('HELPREFRESH SUCCESS'));
		}
	}
}
