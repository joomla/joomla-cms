<?php

/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
 * The Joomla! Factory class
 * @package Joomla
 * @since 1.1
 */
class JFactory 
{	
	/**
	 * Creates a patTemplate oject
	 * @param array An array of additional template files to load
	 * @param boolean True to use caching
	 * @return object
	 * @since 1.1
	 */
	function &getPatTemplate( $files=null ) {
		global $mainframe;

		// For some reason on PHP4 the singleton does not clone deep enough
		// The Reader object is not behaving itself and causing problems
		$tmpl =& JFactory::_createPatTemplate();

		//set template cache prefix
		$prefix = '';
		if($mainframe->isAdmin()) {
			$prefix .= 'administrator__';
		}
		$prefix .= $GLOBALS['option'].'__';
		// TODO next line not working
		//$tmpl->setTemplateCachePrefix($prefix);


		if ( is_array( $files ) ) {
			foreach ( $files as $file ) {
				$tmpl->readTemplatesFromInput( $file );
			}
		}

		return $tmpl;
	}
	
	/**
	 * Creates a database object
	 * @return object
	 * @since 1.1
	 */
	function &getDBO()
	{
		global $mosConfig_host, $mosConfig_user, $mosConfig_password, $mosConfig_db, $mosConfig_dbprefix, $mosConfig_debug, $mosConfig_dbtype;
		
		jimport('joomla.database.'.$mosConfig_dbtype);
		
		/** @global $database */
		$database =& database::getInstance( $mosConfig_host, $mosConfig_user, $mosConfig_password, $mosConfig_db, $mosConfig_dbprefix );
		if ($database->getErrorNum()) {
			$mosSystemError = $database->getErrorNum();
			$basePath = dirname( __FILE__ );
			include $basePath . '/../../configuration.php';
			include $basePath . '/../../offline.php';
			exit();
		}
		$database->debug( $mosConfig_debug );
		return $database;
	}
	
	/**
	 * Creates a cache object
	 * @param string The cache group name
	 * @param string The cache class name
	 * @return object
	 */
	function &getCache($group='', $handler = 'JCache_Function'){
		global $mosConfig_absolute_path, $mosConfig_caching, $mosConfig_cachepath, $mosConfig_cachetime;

		jimport('joomla.cache');

		$options = array(
			'cacheDir' 		=> $mosConfig_cachepath . '/',
			'caching' 		=> $mosConfig_caching,
			'defaultGroup' 	=> $group,
			'lifeTime' 		=> $mosConfig_cachetime,
			'fileNameProtection' => false
		);
		$cache = new $handler( $options );

		return $cache;
	}
	
	/**
	 * Creates an access control object
	 * @return object
	 * $since 1.1
	 */
	function &getACL( ) {
		$acl =& JFactory::_createACL();
		return $acl;
	}
	
	/**
	 * Creates a mailer object
	 * @return object
	 * $since 1.1
	 */
	function &getMailer( ) {
		$mailer =& JFactory::_createMailer();
		return $mailer;
	}
	
	/**
	 * Creates a XML document
	 * @return object
	 * @param boolean If true, include lite version
	 * $since 1.1
	 */
	 
	 function &getXMLParser( $type = 'DOM', $lite =  true) {
		
		$doc = null;
		switch($type)
		{
			case 'DOM'  :
			{
				if($lite) {
					jimport('domit.xml_domit_lite_include');
					$doc = new DOMIT_Lite_Document();
				} else {
					jimport('domit.xml_domit_include');
					$doc = new DOMIT_Document();
				}
			} break;
			
			case 'RSS'  :
			{
				jimport('domit.xml_domit_rss_lite');
				$doc = new xml_domit_rss_document();
			} break;
		}
		 
		return $doc;
	}
	
	/**
	 * @return object
	 * @since 1.1
	 */
	function &_createACL()	{
		global $database;
		jimport( 'joomla.acl' );

		$options = array(
			'db'				=> &$database,
			'db_table_prefix'	=> $database->getPrefix() . 'core_acl_',
			'debug'				=> 0
		);
		$acl = new JACL( $options );

		return $acl;
	}

	/**
	 * @return object
	 * @since 1.1
	 */
	function &_createPatTemplate() {
		global $mainframe;
		global $mosConfig_absolute_path, $mosConfig_live_site;

		$path = $mosConfig_absolute_path . '/libraries/pattemplate';

		require_once( $path .'/patTemplate.php' );
		$tmpl = new patTemplate;

		//TODO : add config var
		if ($GLOBALS['mosConfig_tmpl_caching']) {

			$info = array(
				'cacheFolder' 	=> $GLOBALS['mosConfig_cachepath'].'/pattemplate',
				'lifetime' 		=> 'auto',
				'prefix'		=> 'global__',
				'filemode' 		=> 0755
			);
		 	$tmpl->useTemplateCache( 'File', $info );
		}

		$tmpl->setNamespace( 'jos' );

		// load the wrapper and common templates
		$tmpl->setRoot( $path .'/tmpl' );
		$tmpl->readTemplatesFromInput( 'page.html' );
		$tmpl->applyInputFilter('ShortModifiers');

		$tmpl->addGlobalVar( 'option', 				$GLOBALS['option'] );
		$tmpl->addGlobalVar( 'self', 				$_SERVER['PHP_SELF'] );
		$tmpl->addGlobalVar( 'itemid', 				$GLOBALS['Itemid'] );
		$tmpl->addGlobalVar( 'siteurl', 			$mosConfig_live_site );
		$tmpl->addGlobalVar( 'adminurl', 			$mosConfig_live_site.'/administrator' );
		$tmpl->addGlobalVar( 'admintemplateurl', 	$mosConfig_live_site . '/administrator/templates/'. $mainframe->getTemplate() );
		$tmpl->addGlobalVar( 'sitename', 			$GLOBALS['mosConfig_sitename'] );

		$tmpl->addGlobalVar( 'page_encoding', 		'UTF-8' );
		$tmpl->addGlobalVar( 'version_copyright', 	$GLOBALS['_VERSION']->COPYRIGHT );
		$tmpl->addGlobalVar( 'version_url', 		$GLOBALS['_VERSION']->URL );

		$tmpl->addVar( 'form', 'formAction', 		$_SERVER['PHP_SELF'] );
		$tmpl->addVar( 'form', 'formName', 			'adminForm' );

		$tmpl->addGlobalVar( 'lang_iso', 		'UTF-8' );
		$tmpl->addGlobalVar( 'lang_charset',	'charset=UTF-8' );

		// tabs
		$tpath = mosFS::getNativePath( $mainframe->getTemplatePath() . 'images/tabs' );
		if (is_dir( $tpath )) {
			$turl = $mainframe->getTemplateURL() .'/images/tabs/';
		} else {
			$turl = $mosConfig_live_site .'/includes/js/tabs/';
		}
		$tmpl->addVar( 'includeTabs', 'taburl', $turl );

		return $tmpl;
	}
	
	/**
	 * @return object
	 * @since 1.1
	 */
	function &_createMailer()
	{
		global $mosConfig_sendmail;
		global $mosConfig_smtpauth, $mosConfig_smtpuser;
		global $mosConfig_smtppass, $mosConfig_smtphost;
		global $mosConfig_mailfrom, $mosConfig_fromname, $mosConfig_mailer;
	
		jimport('phpmailer.phpmailer');
		
		$mail = new mosPHPMailer();

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