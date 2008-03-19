<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

/**
 * Joomla Framework Factory class
 *
 * @static
 * @package		Joomla.Framework
 * @since	1.5
 */
class JFactory
{
	/**
	 * Get a application object
	 *
	 * Returns a reference to the global {@link JApplication} object, only creating it
	 * if it doesn't already exist.
	 *
	 * @access public
	 * @param	mixed	$id 		A client identifier or name.
	 * @param	array	$config 	An optional associative array of configuration settings.
	 * @return object JApplication
	 */
	function &getApplication($id = null, $config = array(), $prefix='J')
	{
		static $instance;

		if (!is_object($instance))
		{
			jimport( 'joomla.application.application' );

			if (!$id) {
				JError::raiseError(500, 'Application Instantiation Error');
			}

			$instance = JApplication::getInstance($id, $config, $prefix);
		}

		return $instance;
	}

	/**
	 * Get a configuration object
	 *
	 * Returns a reference to the global {@link JRegistry} object, only creating it
	 * if it doesn't already exist.
	 *
	 * @access public
	 * @param string	The path to the configuration file
	 * @param string	The type of the configuration file
	 * @return object JRegistry
	 */
	function &getConfig($file = null, $type = 'PHP')
	{
		static $instance;

		if (!is_object($instance))
		{
			if ($file === null) {
				$file = dirname(__FILE__).DS.'config.php';
			}

			$instance = JFactory::_createConfig($file, $type);
		}

		return $instance;
	}

	/**
	 * Get a session object
	 *
	 * Returns a reference to the global {@link JSession} object, only creating it
	 * if it doesn't already exist.
	 *
	 * @access public
	 * @param array An array containing session options
	 * @return object JSession
	 */
	function &getSession($options = array())
	{
		static $instance;

		if (!is_object($instance)) {
			$instance = JFactory::_createSession($options);
		}

		return $instance;
	}

	/**
	 * Get a language object
	 *
	 * Returns a reference to the global {@link JLanguage} object, only creating it
	 * if it doesn't already exist.
	 *
	 * @access public
	 * @return object JLanguage
	 */
	function &getLanguage()
	{
		static $instance;

		if (!is_object($instance))
		{
			//get the debug configuration setting
			$conf =& JFactory::getConfig();
			$debug = $conf->getValue('config.debug_lang');

			$instance = JFactory::_createLanguage();
			$instance->setDebug($debug);
		}

		return $instance;
	}

	/**
	 * Get a document object
	 *
	 * Returns a reference to the global {@link JDocument} object, only creating it
	 * if it doesn't already exist.
	 *
	 * @access public
	 * @return object JLanguage
	 */
	function &getDocument()
	{
		static $instance;

		if (!is_object( $instance )) {
			$instance = JFactory::_createDocument();
		}

		return $instance;
	}

	/**
	 * Get an user object
	 *
	 * Returns a reference to the global {@link JUser} object, only creating it
	 * if it doesn't already exist.
	 *
	 * @param 	int 	$id 	The user to load - Can be an integer or string - If string, it is converted to ID automatically.
	 *
	 * @access public
	 * @return object JUser
	 */
	function &getUser($id = null)
	{
		jimport('joomla.user.user');

		if(is_null($id))
		{
			$session  =& JFactory::getSession();
			$instance =& $session->get('user');
			if (!is_a($instance, 'JUser')) {
				$instance =& JUser::getInstance();
			}
		}
		else
		{
			$instance =& JUser::getInstance($id);
		}

		return $instance;
	}

	/**
	 * Get a cache object
	 *
	 * Returns a reference to the global {@link JCache} object
	 *
	 * @access public
	 * @param string The cache group name
	 * @param string The handler to use
	 * @param string The storage method
	 * @return object JCache
	 */
	function &getCache($group = '', $handler = 'callback', $storage = null)
	{
		$handler = ($handler == 'function') ? 'callback' : $handler;

		$conf =& JFactory::getConfig();

		if(!isset($storage)) {
			$storage = $conf->getValue('config.cache_handler', 'file');
		}

		$options = array(
			'defaultgroup' 	=> $group,
			'cachebase' 	=> $conf->getValue('config.cache_path'),
			'lifetime' 		=> $conf->getValue('config.cachetime') * 60,	// minutes to seconds
			'language' 		=> $conf->getValue('config.language'),
			'storage'		=> $storage
		);

		jimport('joomla.cache.cache');

		$cache =& JCache::getInstance( $handler, $options );
		$cache->setCaching($conf->getValue('config.caching'));
		return $cache;
	}

	/**
	 * Get an authorization object
	 *
	 * Returns a reference to the global {@link JAuthorization} object, only creating it
	 * if it doesn't already exist.
	 *
	 * @access public
	 * @return object JAuthorization
	 */
	function &getACL( )
	{
		static $instance;

		if (!is_object($instance)) {
			$instance = JFactory::_createACL();
		}

		return $instance;
	}

	/**
	 * Get a template object
	 *
	 * Returns a reference to the global {@link JTemplate} object, only creating it
	 * if it doesn't already exist.
	 *
	 * @access public
	 * @return object JTemplate
	 */
	function &getTemplate( )
	{
		static $instance;

		if (!is_object($instance)) {
			$instance = JFactory::_createTemplate();
		}

		return $instance;
	}

	/**
	 * Get a database object
	 *
	 * Returns a reference to the global {@link JDatabase} object, only creating it
	 * if it doesn't already exist.
	 *
	 * @return object JDatabase
	 */
	function &getDBO()
	{
		static $instance;

		if (!is_object($instance))
		{
			//get the debug configuration setting
			$conf =& JFactory::getConfig();
			$debug = $conf->getValue('config.debug');

			$instance = JFactory::_createDBO();
			$instance->debug($debug);
		}

		return $instance;
	}

	/**
	 * Get a mailer object
	 *
	 * Returns a reference to the global {@link JMail} object, only creating it
	 * if it doesn't already exist
	 *
	 * @access public
	 * @return object JMail
	 */
	function &getMailer( )
	{
		static $instance;

		if ( ! is_object($instance) ) {
			$instance = JFactory::_createMailer();
		}

		// Create a copy of this object - do not return the original because it may be used several times
		// PHP4 copies objects by value whereas PHP5 copies by reference
		$copy	= (PHP_VERSION < 5) ? $instance : clone($instance);

		return $copy;
	}

	/**
	 * Get an XML document
	 *
	 * @access public
	 * @param string The type of xml parser needed 'DOM', 'RSS' or 'Simple'
	 * @param array:
	 * 		boolean ['lite'] When using 'DOM' if true or not defined then domit_lite is used
	 * 		string  ['rssUrl'] the rss url to parse when using "RSS"
	 * 		string	['cache_time'] with 'RSS' - feed cache time. If not defined defaults to 3600 sec
	 * @return object Parsed XML document object
	 */

	 function &getXMLParser( $type = 'DOM', $options = array())
	 {
		$doc = null;

		switch (strtolower( $type ))
		{
			case 'rss' :
			case 'atom' :
			{
				if (!is_null( $options['rssUrl'] ))
				{
					jimport ('simplepie.simplepie');
					$simplepie = new SimplePie($options['rssUrl']);
					$simplepie = new SimplePie(
						$options['rssUrl'],
						JPATH_BASE.DS.'cache',
						isset( $options['cache_time'] ) ? $options['cache_time'] : 0
					);
					$simplepie->handle_content_type();
					if ($simplepie->data) {
						$doc = $simplepie;
					} else {
						JError::raiseWarning( 'SOME_ERROR_CODE', JText::_('ERROR LOADING FEED DATA') );
					}
				}
			}	break;

			case 'simple' :
			{
				jimport('joomla.utilities.simplexml');
				$doc = new JSimpleXML();
			}	break;

			case 'dom'  :
			default :
			{
				if (!isset($options['lite']) || $options['lite'])
				{
					jimport('domit.xml_domit_lite_include');
					$doc = new DOMIT_Lite_Document();
				}
				else
				{
					jimport('domit.xml_domit_include');
					$doc = new DOMIT_Document();
				}
			}
		}

		return $doc;
	}

	/**
	* Get an editor object
	*
	* @access public
	* @param string $editor The editor to load, depends on the editor plugins that are installed
	* @return object JEditor
	*/
	function &getEditor($editor = null)
	{
		jimport( 'joomla.html.editor' );

		//get the editor configuration setting
		if(is_null($editor))
		{
			$conf =& JFactory::getConfig();
			$editor = $conf->getValue('config.editor');
		}

		$instance =& JEditor::getInstance($editor);

		return $instance;
	}

	/**
	 * Return a reference to the {@link JURI} object
	 *
	 * @access public
	 * @return object JURI
	 * @since 1.5
	 */
	function &getURI($uri = 'SERVER')
	{
		jimport('joomla.environment.uri');

		$instance =& JURI::getInstance($uri);
		return $instance;
	}

	/**
	 * Return a reference to the {@link JDate} object
	 *
	 * @access public
	 * @param mixed $time The initial time for the JDate object
	 * @param int $tzOffset The timezone offset.
	 * @return object JDate
	 * @since 1.5
	 */
	function &getDate($time = 'now', $tzOffset = 0)
	{
		jimport('joomla.utilities.date');
		static $instances;
		static $classname;
		static $mainLocale;

		if(!isset($instances)) {
			$instances = array();
		}

		$language =& JFactory::getLanguage();
		$locale = $language->getTag();

		if(!isset($classname) || $locale != $mainLocale) {
			//Store the locale for future reference
			$mainLocale = $locale;
			$localePath = JPATH_ROOT . DS . 'language' . DS . $mainLocale . DS . $mainLocale . '.date.php';
			if($mainLocale !== false && file_exists($localePath)) {
				$classname = 'JDate'.str_replace('-', '_', $mainLocale);
				JLoader::register( $classname,  $localePath);
				if(!class_exists($classname)) {
					//Something went wrong.  The file exists, but the class does not, default to JDate
					$classname = 'JDate';
				}
			} else {
				//No file, so default to JDate
				$classname = 'JDate';
			}
		}
		$key = $time . '-' . $tzOffset;

		if(!isset($instances[$classname][$key])) {
			$tmp = new $classname($time, $tzOffset);
			//We need to serialize to break the reference
			$instances[$classname][$key] = serialize($tmp);
			unset($tmp);
		}

		$date = unserialize($instances[$classname][$key]);
		return $date;
	}



	/**
	 * Create a configuration object
	 *
	 * @access private
	 * @param string	The path to the configuration file
	 * @param string	The type of the configuration file
	 * @return object JRegistry
	 * @since 1.5
	 */
	function &_createConfig($file, $type = 'PHP')
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
	 * @access private
	 * @param array $options An array containing session options
	 * @return object JSession
	 * @since 1.5
	 */
	function &_createSession( $options = array())
	{
		jimport('joomla.session.session');

		//get the editor configuration setting
		$conf =& JFactory::getConfig();
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
	 * Create an ACL object
	 *
	 * @access private
	 * @return object JAuthorization
	 * @since 1.5
	 */
	function &_createACL()
	{
		//TODO :: take the authorization class out of the application package
		jimport( 'joomla.user.authorization' );

		$db =&  JFactory::getDBO();

		$options = array(
			'db'				=> &$db,
			'db_table_prefix'	=> $db->getPrefix() . 'core_acl_',
			'debug'				=> 0
		);
		$acl = new JAuthorization( $options );

		return $acl;
	}

	/**
	 * Create an database object
	 *
	 * @access private
	 * @return object JDatabase
	 * @since 1.5
	 */
	function &_createDBO()
	{
		jimport('joomla.database.database');
		jimport( 'joomla.database.table' );

		$conf =& JFactory::getConfig();

		$host 		= $conf->getValue('config.host');
		$user 		= $conf->getValue('config.user');
		$password 	= $conf->getValue('config.password');
		$database	= $conf->getValue('config.db');
		$prefix 	= $conf->getValue('config.dbprefix');
		$driver 	= $conf->getValue('config.dbtype');
		$debug 		= $conf->getValue('config.debug');

		$options	= array ( 'driver' => $driver, 'host' => $host, 'user' => $user, 'password' => $password, 'database' => $database, 'prefix' => $prefix );

		$db =& JDatabase::getInstance( $options );

		if ( JError::isError($db) ) {
			jexit('Database Error: ' . $db->toString() );
		}

		if ($db->getErrorNum() > 0) {
			JError::raiseError(500 , 'JDatabase::getInstance: Could not connect to database <br/>' . 'joomla.library:'.$db->getErrorNum().' - '.$db->getErrorMsg() );
		}

		$db->debug( $debug );
		return $db;
	}

	/**
	 * Create a mailer object
	 *
	 * @access private
	 * @return object JMail
	 * @since 1.5
	 */
	function &_createMailer()
	{
		jimport('joomla.mail.mail');

		$conf	=& JFactory::getConfig();

		$sendmail 	= $conf->getValue('config.sendmail');
		$smtpauth 	= $conf->getValue('config.smtpauth');
		$smtpuser 	= $conf->getValue('config.smtpuser');
		$smtppass  	= $conf->getValue('config.smtppass');
		$smtphost 	= $conf->getValue('config.smtphost');
		$mailfrom 	= $conf->getValue('config.mailfrom');
		$fromname 	= $conf->getValue('config.fromname');
		$mailer 	= $conf->getValue('config.mailer');

		// Create a JMail object
		$mail 		=& JMail::getInstance();

		// Set default sender
		$mail->setSender(array ($mailfrom, $fromname));

		// Default mailer is to use PHP's mail function
		switch ($mailer)
		{
			case 'smtp' :
				$mail->useSMTP($smtpauth, $smtphost, $smtpuser, $smtppass);
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
	 * Create a template object
	 *
	 * @access private
	 * @param array An array of support template files to load
	 * @return object JTemplate
	 * @since 1.5
	 */
	function &_createTemplate($files = array())
	{
		jimport('joomla.template.template');

		$conf =& JFactory::getConfig();

		$tmpl = new JTemplate;

		// patTemplate
		if ($conf->getValue('config.caching')) {
			 $tmpl->enableTemplateCache( 'File', JPATH_BASE.DS.'cache'.DS);
		}

		$tmpl->setNamespace( 'jtmpl' );

		// load the wrapper and common templates
		$tmpl->readTemplatesFromFile( 'page.html' );
		$tmpl->applyInputFilter('ShortModifiers');

		// load the stock templates
		if (is_array( $files ))
		{
			foreach ($files as $file) {
				$tmpl->readTemplatesFromInput( $file );
			}
		}

		$tmpl->addGlobalVar( 'option', 				$GLOBALS['option'] );
		$tmpl->addGlobalVar( 'self', 				$_SERVER['PHP_SELF'] );
		$tmpl->addGlobalVar( 'uri_query', 			$_SERVER['QUERY_STRING'] );
		$tmpl->addGlobalVar( 'REQUEST_URI',			JRequest::getURI() );
		if (isset($GLOBALS['Itemid'])) {
			$tmpl->addGlobalVar( 'itemid', $GLOBALS['Itemid'] );
		}

		return $tmpl;
	}

	/**
	 * Create a language object
	 *
	 * @access private
	 * @return object JLanguage
	 * @since 1.5
	 */
	function &_createLanguage()
	{
		jimport('joomla.language.language');

		$conf	=& JFactory::getConfig();
		$locale	= $conf->getValue('config.language');
		$lang	=& JLanguage::getInstance($locale);
		$lang->setDebug($conf->getValue('config.debug_lang'));

		return $lang;
	}

	/**
	 * Create a document object
	 *
	 * @access private
	 * @return object JDocument
	 * @since 1.5
	 */
	function &_createDocument()
	{
		jimport('joomla.document.document');

		$lang	=& JFactory::getLanguage();

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

		$doc =& JDocument::getInstance($type, $attributes);
		return $doc;
	}
}
