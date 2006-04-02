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
	 * Creates a cache object
	 *
	 * @access public
	 * @param string The cache group name
	 * @param string The cache class name
	 * @return object
	 */
	function &getCache($group='', $handler = 'function')
	{
		global $mainframe;

		jimport('joomla.cache.cache');
		
		$cachePath = $mainframe->getCfg('cachepath').DS;

		/*
		 * If we are in the installation application, we don't need to be
		 * creating any directories or have caching on
		 */
		if ($mainframe->getClientId() != 2)
		{
			/*
			 * Add the application specific subdirectory for cache paths
			 */
			
			$options = array(
				'cacheDir' 		=> $cachePath,
				'caching' 		=> $mainframe->getCfg('caching'),
				'defaultGroup' 	=> $group,
				'lifeTime' 		=> $mainframe->getCfg('cachetime'),
				'fileNameProtection' => false
			);
		} 
		else
		{
			$options = array(
				'cacheDir' 		=> $mainframe->getCfg('cachepath') . '/',
				'caching' 		=> false,
				'defaultGroup' 	=> $group,
				'lifeTime' 		=> $mainframe->getCfg('cachetime'),
				'fileNameProtection' => false
			);
		}

		$cache =& JCache::getInstance( $handler, $options );

		return $cache;
	}

	/**
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
	 * Creates a XML document
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
	 	global $mainframe;
	 	
		$doc = null;

		switch($type)
		{
			case 'DOM'  :
			{
				if( is_null($options['lite']) || $options['lite']) {
					jimport('domit.xml_domit_lite_include');
					$doc = new DOMIT_Lite_Document();
				} else {
					jimport('domit.xml_domit_include');
					$doc = new DOMIT_Document();
				}
			} break;

			
			case 'RSS' :
			{
				if( is_null( $options['rssUrl']) ) {
					return false;
				}
				define('MAGPIE_OUTPUT_ENCODING', 'UTF-8');
				define('MAGPIE_CACHE_ON', true);
				define('MAGPIE_CACHE_DIR',$mainframe->getCfg('cachepath'));
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
			}
		}

		$reference =& $doc;
		return $reference;
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
		global $mainframe;

		jimport( 'joomla.application.user.authorization' );

		$database =&  $mainframe->getDBO();

		$options = array(
			'db'				=> &$database,
			'db_table_prefix'	=> $database->getPrefix() . 'core_acl_',
			'debug'				=> 0
		);
		$acl = new JAuthorization( $options );

		return $acl;
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
		global $mosConfig_sendmail;
		global $mosConfig_smtpauth, $mosConfig_smtpuser;
		global $mosConfig_smtppass, $mosConfig_smtphost;
		global $mosConfig_mailfrom, $mosConfig_fromname, $mosConfig_mailer;

		jimport('phpmailer.phpmailer');

		$mail = new PHPMailer();

		$mail->PluginDir = JPATH_LIBRARIES .'/phpmailer/';
		$mail->SetLanguage( 'en', JPATH_LIBRARIES . '/includes/phpmailer/language/' );
		$mail->CharSet 	= "utf-8";
		$mail->IsMail();
		$mail->From 	= $mosConfig_mailfrom;
		$mail->FromName = $mosConfig_fromname;
		$mail->Mailer 	= $mosConfig_mailer;

		// Add smtp values if needed
		if ( $mosConfig_mailer == 'smtp' ) {
			$mail->SMTPAuth = $mosConfig_smtpauth;
			$mail->Username = $mosConfig_smtpuser;
			$mail->Password = $mosConfig_smtppass;
			$mail->Host 	= $mosConfig_smtphost;
		} else

		// Set sendmail path
		if ( $mosConfig_mailer == 'sendmail' ) {
			if (isset($mosConfig_sendmail))
				$mail->Sendmail = $mosConfig_sendmail;
		} // if

		return $mail;
	}
}
?>
