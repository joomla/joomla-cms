<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
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

		if(is_null($file)) {
			$file = dirname(__FILE__) .DS. 'config.php';
		}

		if (!is_object($instance)) {
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

		if (!is_object($instance)) {
			$instance = JFactory::_createLanguage();
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
	 * @access public
	 * @return object JUser
	 */
	function &getUser()
	{
	    jimport('joomla.user.user');
		$session  =& JFactory::getSession();
		$instance =& $session->get('user');

		return $instance;
	}

	/**
	 * Get a cache object
	 *
	 * Returns a reference to the global {@link JCache} object, only creating it
	 * if it doesn't already exist.
	 *
	 * @access public
	 * @param string The cache group name
	 * @param string The cache class name
	 * @return object JCache
	 */
	function &getCache($group='', $handler = 'callback')
	{
		$handler = ($handler == 'function') ? 'callback' : $handler;
		jimport('joomla.cache.cache');

		$conf =& JFactory::getConfig();

		// If we are in the installation application, we don't need to be
		// creating any directories or have caching on
		$options = array(
			'cachebase' 	=> JPATH_CACHE,
			'defaultgroup' 	=> $group,
			'lifetime' 		=> $conf->getValue('config.cachetime')
		);

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

		if (is_object($instance))
			unset($instance);

		$instance = JFactory::_createMailer();

		return $instance;
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

		switch ($type)
		{
			case 'RSS' :
			case 'Atom' :
				if (!is_null( $options['rssUrl'] )) {
					jimport ('simplepie.simplepie');
					$simplepie = new SimplePie();
					$simplepie->feed_url($options['rssUrl']);
					$simplepie->cache_location(JPATH_BASE.DS.'cache');
					$simplepie->init();
					$simplepie->handle_content_type();
					if ($simplepie->data) {
						$doc = $simplepie;
					} else {
					// Raise Error
					}
				}
				break;

			case 'Simple' :
				jimport('joomla.utilities.simplexml');
				$doc = new JSimpleXML();
				break;

			case 'DOM'  :
			default :
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

		require_once( $file );

		// Create the registry with a default namespace of config
		$registry = new JRegistry( 'config');

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
		jimport('joomla.environment.session');

		//get the editor configuration setting
		$conf =& JFactory::getConfig();
		$handler =  $conf->getValue('config.session_handler', 'none');
		$options['expire'] = $conf->getValue('config.lifetime', 15);

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

		$conf =& JFactory::getConfig();

		$host 		= $conf->getValue('config.host');
		$user 		= $conf->getValue('config.user');
		$password 	= $conf->getValue('config.password');
		$db   		= $conf->getValue('config.db');
		$dbprefix 	= $conf->getValue('config.dbprefix');
		$dbtype 	= $conf->getValue('config.dbtype');
		$debug 		= $conf->getValue('config.debug');

		$db =& JDatabase::getInstance( $dbtype, $host, $user, $password, $db, $dbprefix );

		if ($db->getErrorNum() > 0) {
			JError::raiseError('joomla.library:'.$db->getErrorNum(), 'JDatabase::getInstance: Could not connect to database <br/>' . $db->getErrorMsg() );
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
		jimport('joomla.utilities.mail');

		$conf	=& JFactory::getConfig();

		$sendmail 	= $conf->getValue('config.sendmail');
		$smtpauth 	= $conf->getValue('config.smtpauth');
		$smtpuser 	= $conf->getValue('config.smtpuser');
		$smtppass  	= $conf->getValue('config.smtppass');
		$smtphost 	= $conf->getValue('config.smtphost');
		$mailfrom 	= $conf->getValue('config.mailfrom');
		$fromname 	= $conf->getValue('config.fromname');
		$mailer 	= $conf->getValue('config.mailer');

		$mail = new JMail();

		// Set default sender
		$mail->setSender(array ($mailfrom, $fromname));

		// Default mailer is to use PHP's mail function
		switch ($mailer)
		{
			case 'smtp' :
				$mail->useSMTP($smtpauth, $smtphost, $smtpuser, $smtppass);
				break;
			case 'sendmail' :
				$mail->useSendmail();
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
		$tmpl->addGlobalVar( 'itemid', 				$GLOBALS['Itemid'] );
		$tmpl->addGlobalVar( 'REQUEST_URI',			JRequest::getURI() );

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
		jimport('joomla.i18n.language');

		$conf =& JFactory::getConfig();

		$lang =& JLanguage::getInstance($conf->getValue('config.language'));
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
		jimport('joomla.environment.request');

		$lang	=& JFactory::getLanguage();

		//Keep backwards compatibility with Joomla! 1.0
		$raw	= JRequest::getVar( 'no_html', 0, '', 'int' );
		$type	= JRequest::getVar( 'format', $raw ? 'raw' : 'html',  '', 'string'  );

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
