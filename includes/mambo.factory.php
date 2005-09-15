<?php
/**
* @version $Id: mambo.factory.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Mambo
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
 * The Joomla! Factory class
 * @package Mambo
 */
class mosFactory {
	/**
	* Load language files
	* The function will load the common language file of the system and the
	* special files for the actual component.
	* The module related files will be loaded automatically
	*
	* @subpackage Language
	* @param string		actual component which files should be loaded
	* @param boolean	admin languages to be loaded?
	*/
	function &getLanguage( $option=null, $isAdmin=false ) {
		global $mosConfig_absolute_path, $mainframe;
		global $mosConfig_lang, $my;

		mosFS::load ( 'includes/mambo.language.php' );

		$mosConfig_admin_path = $mosConfig_absolute_path .'/administrator';
		$path = $mosConfig_absolute_path . '/language/';
		$lang = $mosConfig_lang;

		if ($my && isset( $my->params ) && $userLang = $my->params->get( 'language', $lang )) {
			// if admin && special lang?
			if( $mainframe && $mainframe->isAdmin() ) {
				$userLang = $my->params->get( 'admin_language', $lang );
			}

			if( $userLang != '' && $userLang != '0' ) {
				$lang = $userLang;
			}
		}

		// Checks if the session does have different values
		if ($mainframe) {
			$lang = $mainframe->getUserState( 'lang', $lang );
		}

		// loads english language file by default
		if ($lang == '') {
			$lang = 'english';
		}

		// load the site language file (the old way - to be deprecated)
		$file = $path . $lang .'.php';
		if (file_exists( $file )) {
			require_once( $path . $lang .'.php' );
		} else {
			$file = $path .'english.php';
			if (file_exists( $file )) {
				require_once( $file );
			}
		}

		$_LANG = new mosLanguage( $lang );
		$_LANG->loadAll( $option, 0 );
		if ($isAdmin) {
			$_LANG->loadAll( $option, 1 );
		}

		// backward compatibility for templates
		if ( !defined( '_ISO') ) {
			define( '_ISO', 'charset=' . $_LANG->iso() );
		}

		// make sure the locale setting is correct
		setlocale( LC_ALL, $_LANG->locale() );

		// In case of frontend modify the config value in order to keep backward compatiblitity
		if( $mainframe && !$mainframe->isAdmin() ) {
			$mosConfig_lang = $lang;
		}

		return $_LANG;
	}

	/**
	 * @param array An array of additional template files to load
	 * @param boolean True to use caching
	 */
	function &getPatTemplate( $files=null ) {
		global $mainframe;

		// For some reason on PHP4 the singleton does not clone deep enough
		// The Reader object is not behaving itself and causing problems
		// $tmpl =& mosSingleton::getInstance('patTemplate', 'mosFactory::_createPatTemplate');
		$tmpl =& mosFactory::_createPatTemplate();

		//set template cache prefix
		$prefix = '';
		if($mainframe->isAdmin()) {
			$prefix .= 'administrator__';
		}
		$prefix .= $GLOBALS['option'].'__';
		$tmpl->setTemplateCachePrefix($prefix);


		if ( is_array( $files ) ) {
			foreach ( $files as $file ) {
				$tmpl->readTemplatesFromInput( $file );
			}
		}

		return $tmpl;
	}

	/**
	 * Creates an access control object
	 * @param object A Joomla! database object
	 * @return object
	 */
	function &getACL( &$database ) {
		$acl =& mosSingleton::getInstance('mambo_acl_api', 'mosFactory::_createACL');
		return $acl;
	}


	/**
	 * Creates a cache object
	 * @param string The cache group name
	 * @param string The cache class name
	 * @return object
	 */
	function &getCache($group='', $handler = 'mosCache_Function'){
		global $mosConfig_absolute_path, $mosConfig_caching, $mosConfig_cachepath, $mosConfig_cachetime;

		require_once( $mosConfig_absolute_path .'/includes/mambo.cache.php' );

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
	 * Creates a mosSerializer object
	 * @return object
	 */
	function &getSerializer() {
		$serializer =& mosSingleton::getInstance('mosSerialiser', 'mosFactory::_createSerialiser');
		return $serializer;
	}

	function &_createPatTemplate() {
		global $_LANG, $mainframe;
		global $mosConfig_absolute_path, $mosConfig_live_site;

		$path = $mosConfig_absolute_path . '/includes/patTemplate';

		require_once( $path .'/patTemplate.php' );
		$tmpl = new patTemplate;

		// patTemplate
		if ($GLOBALS['mosConfig_tmpl_caching']) {

			$info = array(
				'cacheFolder' 	=> $GLOBALS['mosConfig_cachepath'].'/patTemplate',
				'lifetime' 		=> 'auto',
				'prefix'		=> 'global__',
				'filemode' 		=> 0755
			);
		 	$tmpl->useTemplateCache( 'File', $info );
		}

		$tmpl->setNamespace( 'mos' );

		// load the wrapper and common templates
		$tmpl->setRoot( $path .'/tmpl' );
		$tmpl->readTemplatesFromInput( 'page.html' );
		$tmpl->applyInputFilter('ShortModifiers');

		$tmpl->addGlobalVar( 'option', 				$GLOBALS['option'] );
		$tmpl->addGlobalVar( 'self', 				$_SERVER['PHP_SELF'] );
		$tmpl->addGlobalVar( 'itemid', 				$GLOBALS['Itemid'] );
		$tmpl->addGlobalVar( 'siteurl', 			$mosConfig_live_site );
		$tmpl->addGlobalVar( 'adminurl', 			$mosConfig_live_site .'/administrator' );
		$tmpl->addGlobalVar( 'admintemplateurl', 	$mosConfig_live_site .'/administrator/templates/'. $mainframe->getTemplate() );
		$tmpl->addGlobalVar( 'sitename', 			$GLOBALS['mosConfig_sitename'] );

		$tmpl->addGlobalVar( 'page_encoding', 		$_LANG->iso() );
		$tmpl->addGlobalVar( 'version_copyright', 	$GLOBALS['_VERSION']->COPYRIGHT );
		$tmpl->addGlobalVar( 'version_url', 		$GLOBALS['_VERSION']->URL );

		$tmpl->addVar( 'form', 'formAction', 		$_SERVER['PHP_SELF'] );
		$tmpl->addVar( 'form', 'formName', 			'adminForm' );

		if ($_LANG->iso()) {
			$tmpl->addGlobalVar( 'lang_iso', 		$_LANG->iso() );
			$tmpl->addGlobalVar( 'lang_charset', 	'charset=' . $_LANG->iso() );
		} else {
			// TODO: Try and determine the charset from the browser
			$tmpl->addGlobalVar( 'lang_iso', 		'iso-8859-1' );
			$tmpl->addGlobalVar( 'lang_charset',	'charset=iso-8859-1' );
		}

		if ($_LANG->rtl()) {
			$tmpl->addGlobalVar('treecss', 'dtree_rtl.css');
			$tmpl->addGlobalVar('treeimgfolder', 'img_rtl');
		} else {
			$tmpl->addGlobalVar('treecss', 'dtree.css');
			$tmpl->addGlobalVar('treeimgfolder', 'img');
		}

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

	function &_createACL()	{
		global $database;

		$path = '/includes/phpgacl';

		mosFS::load( $path .'/gacl.class.php' );
		mosFS::load( $path .'/gacl_api.class.php' );
		mosFS::load( $path .'/gacl.mambo.php' );

		$acl = new mambo_acl_api(
			array(
				'db' 				=> &$database->_resource,
				'db_table_prefix' 	=> $database->getPrefix() . 'core_acl_',
				'debug' 			=> 0
			)
		);

		return $acl;
	}

	function &_createSerialiser()	{
		global $mosConfig_absolute_path;

		require_once( $mosConfig_absolute_path . '/includes/mambo.serialize.php' );

		$serialiser = new mosSerializer();
		return $serialiser;
	}
}
?>