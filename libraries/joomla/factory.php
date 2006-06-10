<?php
/**
 * @version $Id$
 * @package Joomla.Framework
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
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
 * @package Joomla.Framework
 * @since	1.5
 */
class JFactory
{
	/**
	 * Get an framework configuration object
	 * 
	 * Returns a reference to the global JRegistry object, only creating it
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
	 * Get a cache object
	 * 
	 * Returns a reference to the global JCache object, only creating it
	 * if it doesn't already exist.
	 *
	 * @access public
	 * @param string The cache group name
	 * @param string The cache class name
	 * @return object
	 */
	function &getCache($group='', $handler = 'function')
	{
		jimport('joomla.cache.cache');
		
		$registry =& JFactory::getConfig();

		/*
		 * If we are in the installation application, we don't need to be
		 * creating any directories or have caching on
		 */
		$options = array(
			'cacheDir' 		=> JPATH_BASE.DS.'cache'.DS,
			'caching' 		=> $registry->getValue('config.caching'),
			'defaultGroup' 	=> $group,
			'lifeTime' 		=> $registry->getValue('config.cachetime'),
			'fileNameProtection' => false
		);

		$cache =& JCache::getInstance( $handler, $options );

		return $cache;
	}

	/**
	 * Get the authorization object
	 * 
	 * Returns a reference to the global JAuthorization object, only creating it
	 * if it doesn't already exist.
	 *
	 * @access public
	 * @return object
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
	 * Get the database object
	 * 
	 * Returns a reference to the global JDatabase object, only creating it
	 * if it doesn't already exist.
	 * 
	 * @return object JDatabase based object
	 */
	function &getDBO()
	{
		static $instance;

		if (!is_object($instance)) {
			// TODO: Don't know how to do this better from within the static call
			global $mainframe;
			$instance = JFactory::_createDBO();
			$instance->debug( $mainframe->getCfg( 'debug_db' ));
		}

		return $instance;
	}

	/**
	 * Get mailer object
	 * 
	 * Returns a reference to the global Mailer object, only creating it
	 * if it doesn't already exist
	 *
	 * @access public
	 * @return object
	 */
	function &getMailer( )
	{
		static $instance;

		if (!is_object($instance)) {
			$instance = JFactory::_createMailer();
		}

		return $instance;
	}

	/**
	 * Get a XML document
	 *
	 * @access public
	 * @return object
	 * $param string The type of xml parser needed 'DOM', 'RSS' or 'Simple'
	 * @param array:
	 * 		boolean ['lite'] When using 'DOM' if true or not defined then domit_lite is used
	 * 		string  ['rssUrl'] the rss url to parse when using "RSS"
	 * 		string	['cache_time'] with 'RSS' - feed cache time. If not defined defaults to 3600 sec
	 */

	 function &getXMLParser( $type = 'DOM', $options = array())
	 {
		$doc = null;

		switch($type)
		{
			case 'RSS' :
			{
				if( is_null( $options['rssUrl']) ) {
					return false;
				}
				define('MAGPIE_OUTPUT_ENCODING', 'UTF-8');
				define('MAGPIE_CACHE_ON', true);
				define('MAGPIE_CACHE_DIR',JPATH_BASE.DS.'cache');
				if( !is_null( $options['cache_time'])){
					define('MAGPIE_CACHE_AGE', $options['cache_time']);
				}

				jimport('magpierss.rss_fetch');
				$doc = fetch_rss( $options['rssUrl'] );

			} break;

			case 'Simple' :
			{
				jimport('joomla.utilities.simplexml');
				$doc = new JSimpleXML();
			} break;

			case 'DOM'  :
			default :
			{
				if( !isset($options['lite']) || $options['lite']) {
					jimport('domit.xml_domit_lite_include');
					$doc = new DOMIT_Lite_Document();
				} else {
					jimport('domit.xml_domit_include');
					$doc = new DOMIT_Document();
				}
			}

		}
		// needed for php4
		$reference = & $doc;
		return $reference;
	}
	
	/**
	 * Create a configuration object
	 *
	 * @access private
	 * @param string	The path to the configuration file
	 * @param string	The type of the configuration file
	 * @return object
	 * @since 1.5
	 */
	function &_createConfig($file, $type = 'PHP')
	{
		jimport('joomla.registry.registry');
		
		require_once( $file );

		// Create the registry with a default namespace of config which is read only
		$registry = new JRegistry( 'config');
		
		// Create the JConfig object
		$config = new JFrameworkConfig();

		// Load the configuration values into the registry
		$registry->loadObject($config);
		
		return $registry;
	}

	/**
	 * Create an ACL object
	 *
	 * @access private
	 * @return object
	 * @since 1.5
	 */
	function &_createACL()
	{
		//TODO :: take the authorization class out of the application package
		jimport( 'joomla.application.user.authorization' );

		$database =&  JFactory::getDBO();

		$options = array(
			'db'				=> &$database,
			'db_table_prefix'	=> $database->getPrefix() . 'core_acl_',
			'debug'				=> 0
		);
		$acl = new JAuthorization( $options );

		return $acl;
	}
	
	/**
	 * Create an database object
	 *
	 * @access private
	 * @return object
	 * @since 1.5
	 */
	function &_createDBO()
	{
		$conf =& JFactory::getConfig();
		
		$host 		= $conf->getValue('config.host');
		$user 		= $conf->getValue('config.user');
		$password 	= $conf->getValue('config.password');
		$db   		= $conf->getValue('config.db');
		$dbprefix 	= $conf->getValue('config.dbprefix');
		$dbtype 	= $conf->getValue('config.dbtype');
		$debug 		= $conf->getValue('config.debug');

		jimport('joomla.database.database');
		$database =& JDatabase::getInstance( $dbtype, $host, $user, $password, $db, $dbprefix );

		if ($database->getErrorNum() > 2) {
			JError::raiseError('joomla.library:'.$database->getErrorNum(), 'JDatabase::getInstance: Could not connect to database <br/>' . $database->getErrorMsg() );
		}
		$database->debug( $debug );
		return $database;
	}

	/**
	 * Create a mailer object
	 *
	 * @access private
	 * @return object
	 * @since 1.5
	 */
	function &_createMailer()
	{
		jimport('phpmailer.phpmailer');
		
		$conf =& JFactory::getConfig();
		
		$sendmail 	= $conf->getValue('config.sendmail');
		$smtpauth 	= $conf->getValue('config.smtpauth');
		$smtpuser 	= $conf->getValue('config.smtpuser');
		$smtppass  	= $conf->getValue('config.smtppass');
		$smtphost 	= $conf->getValue('config.smtphost');
		$mailfrom 	= $conf->getValue('config.mailfrom');
		$fromname 	= $conf->getValue('config.fromname');
		$mailer 	= $conf->getValue('config.mailer');

		$mail = new PHPMailer();

		$mail->PluginDir = JPATH_LIBRARIES .'/phpmailer/';
		$mail->SetLanguage( 'en', JPATH_LIBRARIES . '/includes/phpmailer/language/' );
		$mail->CharSet 	= "utf-8";
		$mail->IsMail();
		$mail->From 	= $mailfrom;
		$mail->FromName = $fromname;
		$mail->Mailer 	= $mailer;

		// Add smtp values if needed
		if ( $mailer == 'smtp' ) {
			$mail->SMTPAuth = $smtpauth;
			$mail->Username = $smtpuser;
			$mail->Password = $smtppass;
			$mail->Host 	= $smtphost;
		} else

		// Set sendmail path
		if ( $mailer == 'sendmail' ) {
			if (isset($sendmail))
				$mail->Sendmail = $sendmail;
		} // if

		return $mail;
	}
}
?>
