<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * Joomla Framework Factory class
 *
 * @static
 * @package		Joomla.Framework
 * @since	1.5
 */
abstract class JFactory
{
	public static $application = null;
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
	 * @param	mixed	$id		A client identifier or name.
	 * @param	array	$config	An optional associative array of configuration settings.
	 * @return object JApplication
	 */
	public static function getApplication($id = null, $config = array(), $prefix='J')
	{
		if (!is_object(JFactory::$application))
		{
			jimport('joomla.application.application');

			if (!$id) {
				JError::raiseError(500, 'Application Instantiation Error');
			}

			JFactory::$application = JApplication::getInstance($id, $config, $prefix);
		}

		return JFactory::$application;
	}

	/**
	 * Get a configuration object
	 *
	 * Returns the global {@link JRegistry} object, only creating it
	 * if it doesn't already exist.
	 *
	 * @param string	The path to the configuration file
	 * @param string	The type of the configuration file
	 * @return object JRegistry
	 */
	public static function getConfig($file = null, $type = 'PHP')
	{
		if (!is_object(JFactory::$config))
		{
			if ($file === null) {
				$file = dirname(__FILE__).DS.'config.php';
			}

			JFactory::$config = JFactory::_createConfig($file, $type);
		}

		return JFactory::$config;
	}

	/**
	 * Get a session object
	 *
	 * Returns the global {@link JSession} object, only creating it
	 * if it doesn't already exist.
	 *
	 * @param array An array containing session options
	 * @return object JSession
	 */
	public static function getSession($options = array())
	{
		if (!is_object(JFactory::$session)) {
			JFactory::$session = JFactory::_createSession($options);
		}

		return JFactory::$session;
	}

	/**
	 * Get a language object
	 *
	 * Returns the global {@link JLanguage} object, only creating it
	 * if it doesn't already exist.
	 *
	 * @return object JLanguage
	 */
	public static function getLanguage()
	{
		if (!is_object(JFactory::$language))
		{
			//get the debug configuration setting
			$conf = &JFactory::getConfig();
			$debug = $conf->getValue('config.debug_lang');

			JFactory::$language = JFactory::_createLanguage();
			JFactory::$language->setDebug($debug);
		}

		return JFactory::$language;
	}

	/**
	 * Get a document object
	 *
	 * Returns the global {@link JDocument} object, only creating it
	 * if it doesn't already exist.
	 *
	 * @return object JDocument
	 */
	public static function getDocument()
	{
		if (!is_object(JFactory::$document)) {
			JFactory::$document = JFactory::_createDocument();
		}

		return JFactory::$document;
	}

	/**
	 * Get an user object
	 *
	 * Returns the global {@link JUser} object, only creating it
	 * if it doesn't already exist.
	 *
	 * @param	int	$id	The user to load - Can be an integer or string - If string, it is converted to ID automatically.
	 *
	 * @return object JUser
	 */
	public static function getUser($id = null)
	{
		jimport('joomla.user.user');

		if (is_null($id))
		{
			$instance = JFactory::getSession()->get('user');
			if (!$instance INSTANCEOF JUser) {
				$instance = &JUser::getInstance();
			}
		}
		else
		{
			$instance = &JUser::getInstance($id);
		}

		return $instance;
	}

	/**
	 * Get a cache object
	 *
	 * Returns the global {@link JCache} object
	 *
	 * @param string The cache group name
	 * @param string The handler to use
	 * @param string The storage method
	 * @return object JCache
	 */
	public static function getCache($group = '', $handler = 'callback', $storage = null)
	{
		$handler = ($handler == 'function') ? 'callback' : $handler;

		$conf = &JFactory::getConfig();

		if (!isset($storage)) {
			$storage = $conf->getValue('config.cache_handler', 'file');
		}

		$options = array(
			'defaultgroup'	=> $group,
			'cachebase'		=> $conf->getValue('config.cache_path'),
			'lifetime'		=> $conf->getValue('config.cachetime') * 60,	// minutes to seconds
			'language'		=> $conf->getValue('config.language'),
			'storage'		=> $storage
		);

		jimport('joomla.cache.cache');

		$cache = &JCache::getInstance($handler, $options);
		$cache->setCaching($conf->getValue('config.caching'));
		return $cache;
	}

	/**
	 * Get an authorization object
	 *
	 * Returns the global {@link JACL} object, only creating it
	 * if it doesn't already exist.
	 *
	 * @return object JACL
	 */
	public static function getACL()
	{
		if (!is_object(JFactory::$acl)) {
			jimport('joomla.access.access');

			JFactory::$acl = new JAccess();
		}

		return JFactory::$acl;
	}

	/**
	 * Get a database object
	 *
	 * Returns the global {@link JDatabase} object, only creating it
	 * if it doesn't already exist.
	 *
	 * @return object JDatabase
	 */
	public static function getDbo()
	{
		if (!is_object(self::$database))
		{
			//get the debug configuration setting
			$conf = &self::getConfig();
			$debug = $conf->getValue('config.debug');

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
	 * @return object JMail
	 */
	public static function getMailer()
	{
		if (! is_object(JFactory::$mailer)) {
			JFactory::$mailer = JFactory::_createMailer();
		}
		$copy	= clone JFactory::$mailer;
		return $copy;
	}

	/**
	 * Get a parsed XML Feed Source
	 *
	 * @since: 1.6
	 * @static
	 * @param string url for feed source
	 * @param int time to cache feed for (using internal cache mechanism)
	 * @return mixed Parsed SimplePie object on success, false on failure
	 */
	public static function getFeedParser($url, $cache_time = 0)
	{
		jimport('simplepie.simplepie');
		if (!is_writable(JPATH_CACHE)) {
			$cache_time = 0;
		}
		$simplepie = new SimplePie($url, JPATH_CACHE, $cache_time);
		$simplepie->force_feed(true);
		if ($simplepie->init()) {
			return $simplepie;
		} else {
			JError::raiseWarning('SOME_ERROR_CODE', JText::_('JERROR_LOADING_FEED_DATA'));
		}

		return false;
	}

	/**
	 * Get an XML document
	 *
	 * @param string The type of xml parser needed 'DOM', 'RSS' or 'Simple'
	 * @param array:
	 *		string  ['rssUrl'] the rss url to parse when using "RSS"
	 *		string	['cache_time'] with 'RSS' - feed cache time. If not defined defaults to 3600 sec
	 * @return object Parsed XML document object
	 * @deprecated
	 */
	public static function getXMLParser($type = '', $options = array())
	{
		$doc = null;

		switch (strtolower($type))
		{
			case 'rss' :
			case 'atom' :
				{
					$cache_time = isset($options['cache_time']) ? $options['cache_time'] : 0;
					$doc = JFactory::getFeedParser($options['rssUrl'], $cache_time);
				}	break;

			case 'simple':
				// JError::raiseWarning('SOME_ERROR_CODE', 'JSimpleXML is deprecated. Use JFactory::getXML instead');
				jimport('joomla.utilities.simplexml');
				$doc = new JSimpleXML();
				break;

			case 'dom':
				JError::raiseWarning('SOME_ERROR_CODE', 'DommitDocument is deprecated.  Use DomDocument instead');
				$doc = null;
				break;

				throw new JException('DommitDocument is deprecated.  Use DomDocument instead');
			default :
				$doc = null;
		}

		return $doc;
	}

	/**
	 * Reads a XML file.
	 *
	 * @todo This may go in a separate class - error reporting may be improved.
	 *
	 * @param string $data Full path and file name.
	 * @param boolean $isFile true to load a file | false to load a string.
	 *
	 * @return mixed JXMLElement on success | false on error.
	 */
	public static function getXML($data, $isFile = true)
	{
		jimport('joomla.utilities.xmlelement');

		// Disable libxml errors and allow to fetch error information as needed
		libxml_use_internal_errors(true);

		if($isFile)
		{
			// Try to load the xml file
			$xml = simplexml_load_file($data, 'JXMLElement');
		}
		else
		{
			// Try to load the xml string
			$xml = simplexml_load_string($data, 'JXMLElement');
		}

		if( ! $xml)
		{
			// There was an error
			JError::raiseWarning(100, JText::_('Failed loading XML file'));

			if($isFile)
			{
				JError::raiseWarning(100, $data);
			}

			foreach(libxml_get_errors() as $error)
			{
				JError::raiseWarning(100, 'XML: '.$error->message);
			}
		}

		return $xml ;
	}

	/**
	 * Get an editor object
	 *
	 * @param string $editor The editor to load, depends on the editor plugins that are installed
	 * @return object JEditor
	 */
	public static function getEditor($editor = null)
	{
		jimport('joomla.html.editor');

		//get the editor configuration setting
		if (is_null($editor))
		{
			$conf = &JFactory::getConfig();
			$editor = $conf->getValue('config.editor');
		}

		return JEditor::getInstance($editor);
	}

	/**
	 * Return a reference to the {@link JURI} object
	 *
	 * @return object JURI
	 * @since 1.5
	 */
	public static function getURI($uri = 'SERVER')
	{
		jimport('joomla.environment.uri');

		return JURI::getInstance($uri);
	}

	/**
	 * Return the {@link JDate} object
	 *
	 * @param mixed $time The initial time for the JDate object
	 * @param mixed $tzOffset The timezone offset.
	 * @return object JDate
	 * @since 1.5
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

		$language = &JFactory::getLanguage();
		$locale = $language->getTag();

		if (!isset($classname) || $locale != $mainLocale) {
			//Store the locale for future reference
			$mainLocale = $locale;
			$localePath = JPATH_ROOT . DS . 'language' . DS . $mainLocale . DS . $mainLocale . '.date.php';
			if ($mainLocale !== false && file_exists($localePath)) {
				$classname = 'JDate'.str_replace('-', '_', $mainLocale);
				JLoader::register($classname,  $localePath);
				if (!class_exists($classname)) {
					//Something went wrong.  The file exists, but the class does not, default to JDate
					$classname = 'JDate';
				}
			} else {
				//No file, so default to JDate
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
	 * @param string	The path to the configuration file
	 * @param string	The type of the configuration file
	 * @return object JRegistry
	 * @since 1.5
	 */
	private static function _createConfig($file, $type = 'PHP')
	{
		jimport('joomla.registry.registry');

		require_once $file;

		// Create the registry with a default namespace of config
		$registry = new JRegistry('config');

		// Create the JConfig object
		$config = new JFrameworkConfig();

		// Load the configuration values into the registry
		$registry->loadObject($config);

		return $registry;
	}

	/**
	 * Create a session object
	 *
	 * @param array $options An array containing session options
	 * @return object JSession
	 * @since 1.5
	 */
	private static function _createSession($options = array())
	{
		jimport('joomla.session.session');

		//get the editor configuration setting
		$conf = &JFactory::getConfig();
		$handler =  $conf->getValue('config.session_handler', 'none');

		// config time is in minutes
		$options['expire'] = ($conf->getValue('config.lifetime')) ? $conf->getValue('config.lifetime') * 60 : 900;

		$session = JSession::getInstance($handler, $options);
		if ($session->getState() == 'expired') {
			$session->restart();
		}

		return $session;
	}

	/**
	 * Create an database object
	 *
	 * @return object JDatabase
	 * @since 1.5
	 */
	private static function _createDbo()
	{
		jimport('joomla.database.database');
		jimport('joomla.database.table');

		$conf = &JFactory::getConfig();

		$host		= $conf->getValue('config.host');
		$user		= $conf->getValue('config.user');
		$password	= $conf->getValue('config.password');
		$database	= $conf->getValue('config.db');
		$prefix	= $conf->getValue('config.dbprefix');
		$driver	= $conf->getValue('config.dbtype');
		$debug		= $conf->getValue('config.debug');

		$options	= array ('driver' => $driver, 'host' => $host, 'user' => $user, 'password' => $password, 'database' => $database, 'prefix' => $prefix);

		$db = &JDatabase::getInstance($options);

		if (JError::isError($db)) {
			jexit('Database Error: ' . (string)$db);
		}

		if ($db->getErrorNum() > 0) {
			JError::raiseError(500 , 'JDatabase::getInstance: Could not connect to database <br />' . 'joomla.library:'.$db->getErrorNum().' - '.$db->getErrorMsg());
		}

		$db->debug($debug);
		return $db;
	}

	/**
	 * Create a mailer object
	 *
	 * @access private
	 * @return object JMail
	 * @since 1.5
	 */
	function _createMailer()
	{
		jimport('joomla.mail.mail');

		$conf	= &JFactory::getConfig();

		$sendmail	= $conf->getValue('config.sendmail');
		$smtpauth	= $conf->getValue('config.smtpauth');
		$smtpuser	= $conf->getValue('config.smtpuser');
		$smtppass	= $conf->getValue('config.smtppass');
		$smtphost	= $conf->getValue('config.smtphost');
		$smtpsecure	= $conf->getValue('config.smtpsecure');
		$smtpport	= $conf->getValue('config.smtpport');
		$mailfrom	= $conf->getValue('config.mailfrom');
		$fromname	= $conf->getValue('config.fromname');
		$mailer		= $conf->getValue('config.mailer');

		// Create a JMail object
		$mail		= &JMail::getInstance();

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
	 * @return object JLanguage
	 * @since 1.5
	 */
	private static function _createLanguage()
	{
		jimport('joomla.language.language');

		$conf	= &JFactory::getConfig();
		$locale	= $conf->getValue('config.language');
		$lang	= &JLanguage::getInstance($locale);
		$lang->setDebug($conf->getValue('config.debug_lang'));

		return $lang;
	}

	/**
	 * Create a document object
	 *
	 * @return object JDocument
	 * @since 1.5
	 */
	private static function _createDocument()
	{
		jimport('joomla.document.document');

		$lang	= &JFactory::getLanguage();

		//Keep backwards compatibility with Joomla! 1.0
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
	 * @param boolean Prefix the connections for writing
	 * @param boolean Use network if available for writing; use false to disable (e.g. FTP, SCP)
	 * @param string UA User agent to use
	 * @param boolean User agent masking (prefix Mozilla)
	 */
	function getStream($use_prefix=true, $use_network=true,$ua=null, $uamask=false) {
		jimport('joomla.filesystem.stream');
		// Setup the context; Joomla! UA and overwrite
		$context = Array();
		$version = new JVersion();
		// set the UA for HTTP and overwrite for FTP
		$context['http']['user_agent'] = $version->getUserAgent($ua, $uamask);
		$context['ftp']['overwrite'] = true;
		if($use_prefix) {
			jimport('joomla.client.helper');
			$FTPOptions = JClientHelper::getCredentials('ftp');
			$SCPOptions = JClientHelper::getCredentials('scp');
			if ($FTPOptions['enabled'] == 1 && $use_network) {
				$prefix = 'ftp://'. $FTPOptions['user'] .':'. $FTPOptions['pass'] .'@'. $FTPOptions['host'];
				$prefix .= $FTPOptions['port'] ? ':'. $FTPOptions['port'] : '';
				$prefix .= $FTPOptions['root'];
			} else if($SCPOptions['enabled'] == 1 && $use_network) {
				$prefix = 'ssh2.sftp://'. $SCPOptions['user'] .':'. $SCPOptions['pass'] .'@'. $SCPOptions['host'];
				$prefix .= $SCPOptions['port'] ? ':'. $SCPOptions['port'] : '';
				$prefix .= $SCPOptions['root'];
			} else {
				$prefix = JPATH_ROOT.DS;
			}
			$retval = new JStream($prefix, JPATH_ROOT, $context);
		} else {
			$retval = new JStream('','',$context);
		}
		return $retval;
	}
}
