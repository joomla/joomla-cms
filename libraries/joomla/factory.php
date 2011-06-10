<?php
/**
 * @package     Joomla.Platform
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Joomla Framework Factory class
 *
 * @package Joomla.Platform
 * @since   11.1
 */
abstract class JFactory
{
	public static $application = null;
	public static $cache = null;
	public static $config = null;
	public static $session = null;
	public static $language = null;
	public static $document = null;
	public static $acl = null;
	public static $database = null;
	public static $mailer = null;

	/**
	 * Get a application object
	 *
	 * Returns the global {@link JApplication} object, only creating it
	 * if it doesn't already exist.
	 *
	 * @param   mixed   $id     A client identifier or name.
	 * @param   array   $config An optional associative array of configuration settings.
	 * @param   string  $prefix application prefix
	 *
	 * @return JApplication	object
	 *
	 * @see    JApplication
	 */
	public static function getApplication($id = null, $config = array(), $prefix='J')
	{
		if (!self::$application) {
			jimport('joomla.application.application');

			if (!$id) {
				JError::raiseError(500, 'Application Instantiation Error');
			}

			self::$application = JApplication::getInstance($id, $config, $prefix);
		}

		return self::$application;
	}

	/**
	 * Get a configuration object
	 *
	 * Returns the global {@link JRegistry} object, only creating it
	 * if it doesn't already exist.
	 *
	 * @param string $file The path to the configuration file
	 * @param string $type The type of the configuration file
	 *
	 * @see JRegistry
	 *
	 * @return JRegistry object
	 */
	public static function getConfig($file = null, $type = 'PHP')
	{
		if (!self::$config) {
			if ($file === null) {
				$file = JPATH_PLATFORM.'/config.php';
			}

			self::$config = self::_createConfig($file, $type);
		}

		return self::$config;
	}

	/**
	 * Get a session object
	 *
	 * Returns the global {@link JSession} object, only creating it
	 * if it doesn't already exist.
	 *
	 * @param   array  $options  An array containing session options
	 *
	 * @see JSession
	 *
	 * @return JSession object
	 */
	public static function getSession($options = array())
	{
		if (!self::$session) {
			self::$session = self::_createSession($options);
		}

		return self::$session;
	}

	/**
	 * Get a language object
	 *
	 * Returns the global {@link JLanguage} object, only creating it
	 * if it doesn't already exist.
	 *
	 * @see JLanguage
	 *
	 * @return JLanguage object
	 */
	public static function getLanguage()
	{
		if (!self::$language) {
			self::$language = self::_createLanguage();
		}

		return self::$language;
	}

	/**
	 * Get a document object
	 *
	 * Returns the global {@link JDocument} object, only creating it
	 * if it doesn't already exist.
	 *
	 * @return JDocument object
	 */
	public static function getDocument()
	{
		if (!self::$document) {
			self::$document = self::_createDocument();
		}

		return self::$document;
	}

	/**
	 * Get an user object
	 *
	 * Returns the global {@link JUser} object, only creating it
	 * if it doesn't already exist.
	 *
	 * @param   integer  $id  The user to load - Can be an integer or string - If string, it is converted to ID automatically.
	 *
	 * @see JUser
	 * @return JUser object
	 */
	public static function getUser($id = null)
	{
		jimport('joomla.user.user');

		if (is_null($id)) {
			$instance = self::getSession()->get('user');
			if (!($instance instanceof JUser)) {
				$instance = JUser::getInstance();
			}
		}
		else {
			$instance = JUser::getInstance($id);
		}

		return $instance;
	}

	/**
	 * Get a cache object
	 *
	 * Returns the global {@link JCache} object
	 *
	 * @param   string  $group    The cache group name
	 * @param   string  $handler  The handler to use
	 * @param   string  $storage  The storage method
	 *
	 * @return  JCache object
	 *
	 * @see     JCache
	 */
	public static function getCache($group = '', $handler = 'callback', $storage = null)
	{
		$hash = md5($group.$handler.$storage);
		if (isset(self::$cache[$hash])) {
			return self::$cache[$hash];
		}
		$handler = ($handler == 'function') ? 'callback' : $handler;

		$conf = self::getConfig();

		$options = array('defaultgroup'	=> $group );

		if (isset($storage)) {
			$options['storage'] = $storage;
		}

		jimport('joomla.cache.cache');

		$cache = JCache::getInstance($handler, $options);

		self::$cache[$hash] = $cache;

		return self::$cache[$hash];
	}

	/**
	 * Get an authorization object
	 *
	 * Returns the global {@link JACL} object, only creating it
	 * if it doesn't already exist.
	 *
	 * @return JACL object
	 */
	public static function getACL()
	{
		if (!self::$acl) {
			jimport('joomla.access.access');
			self::$acl = new JAccess();
		}

		return self::$acl;
	}

	/**
	 * Get a database object
	 *
	 * Returns the global {@link JDatabase} object, only creating it
	 * if it doesn't already exist.
	 *
	 * @return JDatabase object
	 */
	public static function getDbo()
	{
		if (!self::$database) {
			//get the debug configuration setting
			$conf	= self::getConfig();
			$debug	= $conf->get('debug');

			self::$database = self::_createDbo();
			self::$database->debug($debug);
		}

		return self::$database;
	}

	/**
	 * Get a mailer object
	 *
	 * Returns the global {@link JMail} object, only creating it
	 * if it doesn't already exist
	 *
	 * @see JMail
	 *
	 * @return JMail object
	 */
	public static function getMailer()
	{
		if (!self::$mailer) {
			self::$mailer = self::_createMailer();
		}
		$copy	= clone self::$mailer;

		return $copy;
	}

	/**
	 * Get a parsed XML Feed Source
	 *
	 * @param   string   $url         url for feed source
	 * @param   integer  $cache_time  time to cache feed for (using internal cache mechanism)
	 *
	 * @return  mixed  SimplePie parsed object on success, false on failure
	 * @since   11.1
	 */
	public static function getFeedParser($url, $cache_time = 0)
	{
		jimport('simplepie.simplepie');

		$cache = self::getCache('feed_parser', 'callback');

		if ($cache_time > 0) {
			$cache->setLifeTime($cache_time);
		}

		$simplepie = new SimplePie(null, null, 0);

		$simplepie->enable_cache(false);
		$simplepie->set_feed_url($url);
		$simplepie->force_feed(true);

		$contents =  $cache->get(array($simplepie, 'init'), null, false, false);

		if ($contents) {
			return $simplepie;
		}
		else {
			JError::raiseWarning('SOME_ERROR_CODE', JText::_('JLIB_UTIL_ERROR_LOADING_FEED_DATA'));
		}

		return false;
	}

	/**
	 * Get an XML document
	 *
	 * @param   string  $type     The type of XML parser needed 'DOM', 'RSS' or 'Simple'
	 * @param   array   $options  ['rssUrl'] the rss url to parse when using "RSS", ['cache_time'] with 'RSS' - feed cache time. If not defined defaults to 3600 sec
	 *
	 * @return  object  Parsed XML document object
	 * @deprecated
	 */
	public static function getXMLParser($type = '', $options = array())
	{
		$doc = null;

		switch (strtolower($type))
		{
			case 'rss' :
			case 'atom' :
				$cache_time = isset($options['cache_time']) ? $options['cache_time'] : 0;
				$doc = self::getFeedParser($options['rssUrl'], $cache_time);
				break;

			case 'simple':
				// JError::raiseWarning('SOME_ERROR_CODE', 'JSimpleXML is deprecated. Use self::getXML instead');
				jimport('joomla.utilities.simplexml');
				$doc = new JSimpleXML();
				break;

			case 'dom':
				JError::raiseWarning('SOME_ERROR_CODE', JText::_('JLIB_UTIL_ERROR_DOMIT'));
				$doc = null;
				break;

			default :
				$doc = null;
		}

		return $doc;
	}

	/**
	 * Reads a XML file.
	 *
	 * @param   string  $data   Full path and file name.
	 * @param   boolean  $isFile true to load a file | false to load a string.
	 *
	 * @return  mixed    JXMLElement on success | false on error.
	 * @todo This may go in a separate class - error reporting may be improved.
	 */
	public static function getXML($data, $isFile = true)
	{
		jimport('joomla.utilities.xmlelement');

		// Disable libxml errors and allow to fetch error information as needed
		libxml_use_internal_errors(true);

		if ($isFile) {
			// Try to load the XML file
			$xml = simplexml_load_file($data, 'JXMLElement');
		}
		else {
			// Try to load the XML string
			$xml = simplexml_load_string($data, 'JXMLElement');
		}

		if (empty($xml)) {
			// There was an error
			JError::raiseWarning(100, JText::_('JLIB_UTIL_ERROR_XML_LOAD'));

			if ($isFile) {
				JError::raiseWarning(100, $data);
			}

			foreach (libxml_get_errors() as $error)
			{
				JError::raiseWarning(100, 'XML: '.$error->message);
			}
		}

		return $xml ;
	}

	/**
	 * Get an editor object
	 *
	 * @param   string  $editor The editor to load, depends on the editor plugins that are installed
	 *
	 * @return JEditor object
	 */
	public static function getEditor($editor = null)
	{
		jimport('joomla.html.editor');

		//get the editor configuration setting
		if (is_null($editor)) {
			$conf	= self::getConfig();
			$editor	= $conf->get('editor');
		}

		return JEditor::getInstance($editor);
	}

	/**
	 * Return a reference to the {@link JURI} object
	 *
	 * @param   string  $uri uri name
	 *
	 * @see JURI
	 *
	 * @return JURI object
	 * @since   11.1
	 */
	public static function getURI($uri = 'SERVER')
	{
		jimport('joomla.environment.uri');

		return JURI::getInstance($uri);
	}

	/**
	 * Return the {@link JDate} object
	 *
	 * @param   mixed  $time     The initial time for the JDate object
	 * @param   mixed  $tzOffset The timezone offset.
	 *
	 * @see JDate
	 *
	 * @return JDate object
	 * @since   11.1
	 */
	public static function getDate($time = 'now', $tzOffset = null)
	{
		jimport('joomla.utilities.date');
		static $instances;
		static $classname;
		static $mainLocale;

		if (!isset($instances)) {
			$instances = array();
		}

		$language	= self::getLanguage();
		$locale		= $language->getTag();

		if (!isset($classname) || $locale != $mainLocale) {
			//Store the locale for future reference
			$mainLocale = $locale;

			if ($mainLocale !== false) {
				$classname = str_replace('-', '_', $mainLocale).'Date';

				if (!class_exists($classname)) {
					//The class does not exist, default to JDate
					$classname = 'JDate';
				}
			}
			else {
				//No tag, so default to JDate
				$classname = 'JDate';
			}
		}
		$key = $time . '-' . $tzOffset;

		//		if (!isset($instances[$classname][$key])) {
		$tmp = new $classname($time, $tzOffset);
		//We need to serialize to break the reference
		//			$instances[$classname][$key] = serialize($tmp);
		//			unset($tmp);
		//		}

		//		$date = unserialize($instances[$classname][$key]);
		//		return $date;
		return $tmp;
	}



	/**
	 * Create a configuration object
	 *
	 * @param   string  $file       The path to the configuration file.
	 * @param   string  $type       The type of the configuration file.
	 * @param   string  $namespace  The namespace of the configuration file.
	 *
	 * @return  JRegistry
	 *
	 * @since   11.1
	 */
	protected static function _createConfig($file, $type = 'PHP', $namespace = '')
	{
		jimport('joomla.registry.registry');

		if (is_file($file)) {
			include_once $file;
		}

		// Create the registry with a default namespace of config
		$registry = new JRegistry();

		// Sanitize the namespace.
		$namespace = ucfirst((string) preg_replace('/[^A-Z_]/i', '', $namespace));

		// Build the config name.
		$name = 'JConfig'.$namespace;

		// Handle the PHP configuration type.
		if ($type == 'PHP' && class_exists($name)) {
			// Create the JConfig object
			$config = new $name();

			// Load the configuration values into the registry
			$registry->loadObject($config);
		}

		return $registry;
	}

	/**
	 * Create a session object
	 *
	 * @param   array  $options An array containing session options
	 *
	 * @return JSession object
	 * @since   11.1
	 */
	protected static function _createSession($options = array())
	{
		jimport('joomla.session.session');

		// Get the editor configuration setting
		$conf		= self::getConfig();
		$handler	= $conf->get('session_handler', 'none');

		// Config time is in minutes
		$options['expire'] = ($conf->get('lifetime')) ? $conf->get('lifetime') * 60 : 900;

		$session = JSession::getInstance($handler, $options);
		if ($session->getState() == 'expired') {
			$session->restart();
		}

		return $session;
	}

	/**
	 * Create an database object
	 *
	 * @see JDatabase
	 *
	 * @return JDatabase object
	 *
	 * @since   11.1
	 */
	protected static function _createDbo()
	{
		jimport('joomla.database.database');
		jimport('joomla.database.table');

		$conf = self::getConfig();

		$host		= $conf->get('host');
		$user		= $conf->get('user');
		$password	= $conf->get('password');
		$database	= $conf->get('db');
		$prefix		= $conf->get('dbprefix');
		$driver		= $conf->get('dbtype');
		$debug		= $conf->get('debug');

		$options	= array ('driver' => $driver, 'host' => $host, 'user' => $user, 'password' => $password, 'database' => $database, 'prefix' => $prefix);

		$db = JDatabase::getInstance($options);

		if (JError::isError($db)) {
			header('HTTP/1.1 500 Internal Server Error');
			jexit('Database Error: ' . (string) $db);
		}

		if ($db->getErrorNum() > 0) {
			JError::raiseError(500, JText::sprintf('JLIB_UTIL_ERROR_CONNECT_DATABASE', $db->getErrorNum(), $db->getErrorMsg()));
		}

		$db->debug($debug);
		return $db;
	}

	/**
	 * Create a mailer object
	 *
	 * @return  JMail object
	 * @since   11.1
	 */
	protected static function _createMailer()
	{
		jimport('joomla.mail.mail');

		$conf	= self::getConfig();

		$sendmail	= $conf->get('sendmail');
		$smtpauth	=  ($conf->get('smtpauth') == 0) ? null : 1;
		$smtpuser	= $conf->get('smtpuser');
		$smtppass	= $conf->get('smtppass');
		$smtphost	= $conf->get('smtphost');
		$smtpsecure	= $conf->get('smtpsecure');
		$smtpport	= $conf->get('smtpport');
		$mailfrom	= $conf->get('mailfrom');
		$fromname	= $conf->get('fromname');
		$mailer		= $conf->get('mailer');

		// Create a JMail object
		$mail		= JMail::getInstance();

		// Set default sender
		$mail->setSender(array ($mailfrom, $fromname));

		// Default mailer is to use PHP's mail function
		switch ($mailer)
		{
			case 'smtp' :
				$mail->useSMTP($smtpauth, $smtphost, $smtpuser, $smtppass, $smtpsecure, $smtpport);
				break;

			case 'sendmail' :
				$mail->IsSendmail();
				break;

			default :
				$mail->IsMail();
				break;
		}

		return $mail;
	}

	/**
	 * Create a language object
	 *
	 * @see JLanguage
	 *
	 * @return JLanguage object
	 * @since   11.1
	 */
	protected static function _createLanguage()
	{
		jimport('joomla.language.language');

		$conf	= self::getConfig();
		$locale	= $conf->get('language');
		$debug	= $conf->get('debug_lang');
		$lang	= JLanguage::getInstance($locale, $debug);

		return $lang;
	}

	/**
	 * Create a document object
	 *
	 * @see JDocument
	 *
	 * @return JDocument object
	 * @since   11.1
	 */
	protected static function _createDocument()
	{
		jimport('joomla.document.document');

		$lang	= self::getLanguage();

		// Keep backwards compatibility with Joomla! 1.0
		$raw	= JRequest::getBool('no_html');
		$type	= JRequest::getWord('format', $raw ? 'raw' : 'html');

		$attributes = array (
			'charset'	=> 'utf-8',
			'lineend'	=> 'unix',
			'tab'		=> '  ',
			'language'	=> $lang->getTag(),
			'direction'	=> $lang->isRTL() ? 'rtl' : 'ltr'
		);

		return JDocument::getInstance($type, $attributes);
	}

	/**
	 * Creates a new stream object with appropriate prefix
	 *
	 * @param   boolean  $use_prefix		Prefix the connections for writing
	 * @param   boolean  $use_network	Use network if available for writing; use false to disable (e.g. FTP, SCP)
	 * @param   string   $ua				UA User agent to use
	 * @param   boolean  $uamask			User agent masking (prefix Mozilla)
	 *
	 * @see JStream
	 *
	 * @return  JStream
	 * @since   11.1
	 */
	public static function getStream($use_prefix=true, $use_network=true,$ua=null, $uamask=false)
	{
		jimport('joomla.filesystem.stream');

		// Setup the context; Joomla! UA and overwrite
		$context = array();
		$version = new JVersion;
		// set the UA for HTTP and overwrite for FTP
		$context['http']['user_agent'] = $version->getUserAgent($ua, $uamask);
		$context['ftp']['overwrite'] = true;

		if ($use_prefix) {
			jimport('joomla.client.helper');
			$FTPOptions = JClientHelper::getCredentials('ftp');
			$SCPOptions = JClientHelper::getCredentials('scp');

			if ($FTPOptions['enabled'] == 1 && $use_network) {
				$prefix = 'ftp://'. $FTPOptions['user'] .':'. $FTPOptions['pass'] .'@'. $FTPOptions['host'];
				$prefix .= $FTPOptions['port'] ? ':'. $FTPOptions['port'] : '';
				$prefix .= $FTPOptions['root'];
			}
			else if ($SCPOptions['enabled'] == 1 && $use_network) {
				$prefix = 'ssh2.sftp://'. $SCPOptions['user'] .':'. $SCPOptions['pass'] .'@'. $SCPOptions['host'];
				$prefix .= $SCPOptions['port'] ? ':'. $SCPOptions['port'] : '';
				$prefix .= $SCPOptions['root'];
			}
			else {
				$prefix = JPATH_ROOT . '/';
			}

			$retval = new JStream($prefix, JPATH_ROOT, $context);
		}
		else {
			$retval = new JStream('', '', $context);
		}

		return $retval;
	}
}
