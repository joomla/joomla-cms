<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Installation
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
* @package Joomla
* @subpackage Installation
*/
class JInstallationController
{
	/**
	 * @param patTemplate A template object
	 */
	function chooseLanguage($vars)
	{
		$native = JLanguageHelper::detectLanguage();

		$lists = array ();
		$lists['langs'] = JLanguageHelper::createLanguageList($native);

		return JInstallationView::chooseLanguage($lists);
	}

	/**
	 * @param patTemplate A template object
	 */
	function preInstall($vars)
	{
		$lists = array ();

		$phpOptions[] = array (
			'label' => JText::_('PHP version').' >= 4.3.0',
			'state' => phpversion() < '4.3' ? 'No' : 'Yes'
		);
		$phpOptions[] = array (
			'label' => '- '.JText::_('zlib compression support'),
			'state' => extension_loaded('zlib') ? 'Yes' : 'No'
		);
		$phpOptions[] = array (
			'label' => '- '.JText::_('XML support'),
			'state' => extension_loaded('xml') ? 'Yes' : 'No',
			'statetext' => extension_loaded('xml') ? 'Yes' : 'No'
		);
		$phpOptions[] = array (
			'label' => '- '.JText::_('MySQL support'),
			'state' => (function_exists('mysql_connect') || function_exists('mysqli_connect')) ? 'Yes' : 'No'
		);
		if (extension_loaded( 'mbstring' )) {
			$mbDefLang = strtolower( ini_get( 'mbstring.language' ) ) == 'neutral';
			$phpOptions[] = array (
				'label' => JText::_( 'MB language is default' ),
				'state' => $mbDefLang ? 'Yes' : 'No',
				'notice' => $mbDefLang ? '' : JText::_( 'NOTICEMBLANGNOTDEFAULT' )
			);
			$mbOvl = ini_get('mbstring.func_overload') != 0;
			$phpOptions[] = array (
				'label' => JText::_('MB string overload off'),
				'state' => !$mbOvl ? 'Yes' : 'No',
				'notice' => $mbOvl ? JText::_('NOTICEMBSTRINGOVERLOAD') : ''
			);
		}
		$sp = '';
		/*$phpOptions[] = array (
			'label' => JText::_('Session path set'),
			'state' => ($sp = ini_get('session.save_path')) ? 'Yes' : 'No'
		);
		$phpOptions[] = array (
			'label' => JText::_('Session path writeable'),
			'state' => is_writable($sp) ? 'Yes' : 'No'
		);*/
		$cW = (@ file_exists('../configuration.php') && @ is_writable('../configuration.php')) || is_writable('..');
		$phpOptions[] = array (
			'label' => 'configuration.php '.JText::_('writeable'),
			'state' => $cW ? 'Yes' : 'No',
			'notice' => $cW ? '' : JText::_('NOTICEYOUCANSTILLINSTALL')
		);
		$lists['phpOptions'] = & $phpOptions;

		$phpRecommended = array (
			array (
				JText::_('Safe Mode'),
				'safe_mode',
				'OFF'
			),
			array (
				JText::_('Display Errors'),
				'display_errors',
				'ON'
			),
			array (
				JText::_('File Uploads'),
				'file_uploads',
				'ON'
			),
			array (
				JText::_('Magic Quotes GPC'),
				'magic_quotes_gpc',
				'ON'
			),
			array (
				JText::_('Magic Quotes Runtime'),
				'magic_quotes_runtime',
				'OFF'
			),
			array (
				JText::_('Register Globals'),
				'register_globals',
				'OFF'
			),
			array (
				JText::_('Output Buffering'),
				'output_buffering',
				'OFF'
			),
			array (
				JText::_('Session auto start'),
				'session.auto_start',
				'OFF'
			),
		);

		foreach ($phpRecommended as $setting) {
			$lists['phpSettings'][] = array (
				'label' => $setting[0],
				'setting' => $setting[2],
				'actual' => get_php_setting( $setting[1] ),
				'state' => get_php_setting($setting[1]) == $setting[2] ? 'Yes' : 'No'
			);
		}

		return JInstallationView::preInstall( $vars, $lists );
	}

	/**
	 * Gets the parameters for database creation
	 */
	function license($vars)
	{
		return JInstallationView::license($vars);
	}

	/**
	 * Gets the parameters for database creation
	 */
	function dbConfig($vars)
	{
		global $mainframe;

		// Require the xajax library
		require_once( JPATH_BASE.DS.'includes'.DS.'xajax'.DS.'xajax.inc.php' );

		/*
		 * Instantiate the xajax object and register the function
		 */
		$xajax = new xajax($mainframe->getBaseURL().'includes/jajax.php');
		$xajax->registerFunction(array('getCollations', 'JAJAXHandler', 'dbcollate'));
		//$xajax->debugOn();

		if (!isset ($vars['DBPrefix'])) {
			$vars['DBPrefix'] = 'jos_';
		}

		$lists = array ();
		$files = array ('mysql', 'mysqli',);
		$db = JInstallationHelper::detectDB();
		foreach ($files as $file) {
			$option = array ();
			$option['text'] = $file;
			if (strcasecmp($option['text'], $db) == 0) {
				$option['selected'] = 'selected="true"';
			}
			$lists['dbTypes'][] = $option;
		}

		$doc =& $mainframe->getDocument();
		$doc->addCustomTag($xajax->getJavascript('', 'includes/js/xajax.js', 'includes/js/xajax.js'));

		return JInstallationView::dbConfig($vars, $lists);
	}

	/**
	 * Determines db version (for utf-8 support) and gets desired collation
	 *
	 * FUNCTIONALITY MOVED TO JAJAX.PHP
	 *
	 * @return boolean True if successful
	 */
//	function dbCollation($vars)
//	{
//		$DBcreated = mosGetParam($vars, 'DBcreated', '0');
//
//		$DBtype = mosGetParam($vars, 'DBtype', 'mysql');
//		$DBhostname = mosGetParam($vars, 'DBhostname', '');
//		$DBuserName = mosGetParam($vars, 'DBuserName', '');
//		$DBpassword = mosGetParam($vars, 'DBpassword', '');
//		$DBname = mosGetParam($vars, 'DBname', '');
//		$DBPrefix = mosGetParam($vars, 'DBPrefix', 'jos_');
//		$DBDel = mosGetParam($vars, 'DBDel', 0);
//		$DBBackup = mosGetParam($vars, 'DBBackup', 0);
//		$DBSample = mosGetParam($vars, 'DBSample', 1);
//
//		$DButfSupport = intval(mosGetParam($vars, 'DButfSupport', 0));
//		$DBcollation = mosGetParam($vars, 'DBcollation', '');
//		$DBversion = mosGetParam($vars, 'DBversion', '');
//
//		if ($DBtype == '') {
//			JInstallationView::error($vars, JText::_('validType'), 'dbconfig');
//			return false;
//		}
//		if (!$DBhostname || !$DBuserName || !$DBname) {
//			JInstallationView::error($vars, JText::_('validDBDetails'), 'dbconfig');
//			return false;
//		}
//		if ($DBname == '') {
//			JInstallationView::error($vars, JText::_('emptyDBName'), 'dbconfig');
//			return false;
//		}
//
//		$database = & JDatabase::getInstance($DBtype, $DBhostname, $DBuserName, $DBpassword );
//
//		if ($err = $database->getErrorNum()) {
//			if ($err != 3) {
//				// connection failed
//				//JInstallationView::error( $vars, array( 'Could not connect to the database.  Connector returned', $database->getErrorNum() ), 'dbconfig', $database->getErrorMsg() );
//				JInstallationView::error($vars, array (sprintf(JText::_('WARNNOTCONNECTDB'), $database->getErrorNum())), 'dbconfig', $database->getErrorMsg());
//				return false;
//			}
//		}
//
//		$collations = array();
//
//		// determine db version, utf support and available collations
//		$vars['DBversion'] = $database->getVersion();
//		$verParts = explode( '.', $vars['DBversion'] );
//		$vars['DButfSupport'] = ($verParts[0] == 5 || ($verParts[0] == 4 && $verParts[1] == 1 && (int) $verParts[2] >= 2));
//		if ($vars['DButfSupport']) {
//			$query = "SHOW COLLATION LIKE 'utf8%'";
//			$database->setQuery( $query );
//			$collations = $database->loadAssocList();
//		} else {
//			// backward compatibility - utf-8 data in non-utf database
//			// collation does not really have effect so default charset and collation is set
//			$collations[0]['Collation'] = 'latin1';
//		}
//		return JInstallationView::dbCollation( $vars, $collations );
//	}

	/**
	 * Gets the parameters for database creation
	 * @return boolean True if successful
	 */
	function makeDB($vars)
	{
		// Initialize variables
		$errors = null;

		$lang = mosGetParam($vars, 'lang', 'en-GB');
		$DBcreated = mosGetParam($vars, 'DBcreated', '0');

		$DBtype = mosGetParam($vars, 'DBtype', 'mysql');
		$DBhostname = mosGetParam($vars, 'DBhostname', '');
		$DBuserName = mosGetParam($vars, 'DBuserName', '');
		$DBpassword = mosGetParam($vars, 'DBpassword', '');
		$DBname = mosGetParam($vars, 'DBname', '');
		$DBPrefix = mosGetParam($vars, 'DBPrefix', 'jos_');
		$DBOld = mosGetParam($vars, 'DBOld', 'bu');
//		$DBSample = mosGetParam($vars, 'DBSample', 1);
		$DButfSupport = intval(mosGetParam($vars, 'DButfSupport', 0));
		$DBcollation = mosGetParam($vars, 'DBcollation', '');
		$DBversion = mosGetParam($vars, 'DBversion', '');

		if ($DBtype == '') {
			JInstallationView::error($vars, JText::_('validType'), 'dbconfig');
			return false;
		}
		if (!$DBhostname || !$DBuserName || !$DBname) {
			JInstallationView::error($vars, JText::_('validDBDetails'), 'dbconfig');
			return false;
		}
		if ($DBname == '') {
			JInstallationView::error($vars, JText::_('emptyDBName'), 'dbconfig');
			return false;
		}

		if (!$DBcreated) {

			jimport('joomla.database.database');
			$database = & JDatabase::getInstance($DBtype, $DBhostname, $DBuserName, $DBpassword, $DBname, $DBPrefix);

			if ($err = $database->getErrorNum()) {
				if ($err == 3) {
					// connection ok, need to create database
					if (JInstallationHelper::createDatabase($database, $DBname, $DButfSupport, $DBcollation)) {
						// make the new connection to the new database
						$database = NULL;
						$database = & JDatabase::getInstance($DBtype, $DBhostname, $DBuserName, $DBpassword, $DBname, $DBPrefix);
					} else {
						$error = $database->getErrorMsg();
						JInstallationView::error($vars, array (sprintf(JText::_('WARNCREATEDB'), $DBname)), 'dbconfig', $error);
						return false;
					}
				} else {
					// connection failed
					//JInstallationView::error( $vars, array( 'Could not connect to the database.  Connector returned', $database->getErrorNum() ), 'dbconfig', $database->getErrorMsg() );
					JInstallationView::error($vars, array (sprintf(JText::_('WARNNOTCONNECTDB'), $database->getErrorNum())), 'dbconfig', $database->getErrorMsg());
					return false;
				}
			} else {
				// pre-existing database - need to set character set to utf8
				// will only affect MySQL 4.1.2 and up
				JInstallationHelper::setDBCharset($database, $DBname, $DBcollation);
			}

			$database = & JDatabase::getInstance($DBtype, $DBhostname, $DBuserName, $DBpassword, $DBname, $DBPrefix);

			if ($DBOld == 'rm') {
				if (JInstallationHelper::deleteDatabase($database, $DBname, $DBPrefix, $errors)) {
					JInstallationView::error($vars, JText::_('WARNDELETEDB'), 'dbconfig', JInstallationHelper::errors2string($errors));
					return false;
				}
			} else
			{
				/*
				 * We assume since we aren't deleting the database that we need
				 * to back it up :)
				 */
				if (JInstallationHelper::backupDatabase($database, $DBname, $DBPrefix, $errors)) {
					JInstallationView::error($vars, JText::_('WARNBACKINGUPDB'), 'dbconfig', JInstallationHelper::errors2string($errors));
					return false;
				}
			}

			// set collation and use utf-8 compatibile script if appropriate
			if ($DButfSupport) {
				$dbscheme = 'sql'.DS.'joomla.sql';
			} else {
				$dbscheme = 'sql'.DS.'joomla_backward.sql';
			}

			if (JInstallationHelper::populateDatabase($database, $dbscheme, $errors, ($DButfSupport) ? $DBcollation : '') > 0) {
				JInstallationView::error($vars, JText::_('WARNPOPULATINGDB'), 'dbconfig', JInstallationHelper::errors2string($errors));
				return false;
			}

//			if ($DBSample) {
//				$dbsample = 'language/en-GB/sample_data.sql';
//				// Checks for language depended files
//				if (is_file('language'.DS.$lang.DS.'sample_data.sql')) {
//					$dbsample = 'language'.DS.$lang.DS.'sample_data.sql';
//				}
//				JInstallationHelper::populateDatabase($database, $dbsample, $errors);
//			}
		}

		return true;
	}

	/**
	 * Gets ftp configuration parameters
	 */
	function ftpConfig($vars, $DBcreated = '0')
	{
		global $mainframe;

		// Require the xajax library
		require_once( JPATH_BASE.DS.'includes'.DS.'xajax'.DS.'xajax.inc.php' );

		/*
		 * Instantiate the xajax object and register the function
		 */
		$xajax = new xajax($mainframe->getBaseURL().'includes/jajax.php');
		$xajax->registerFunction(array('getFtpRoot', 'JAJAXHandler', 'ftproot'));
		//$xajax->debugOn();

		$vars['DBcreated'] = mosGetParam($vars, 'DBcreated', $DBcreated);
		$strip = get_magic_quotes_gpc();

		if (!isset ($vars['ftpEnable'])) {
			$vars['ftpEnable'] = '1';
		}
		if (!isset ($vars['ftpHost'])) {
			$vars['ftpHost'] = '127.0.0.1';
		}
		if (!isset ($vars['ftpPort'])) {
			$vars['ftpPort'] = '21';
		}
		if (!isset ($vars['ftpUser'])) {
			$vars['ftpUser'] = '';
		}
		if (!isset ($vars['ftpPassword'])) {
			$vars['ftpPassword'] = '';
		}

		$doc =& $mainframe->getDocument();
		$doc->addCustomTag($xajax->getJavascript('', 'includes/js/xajax.js', 'includes/js/xajax.js'));

		return JInstallationView::ftpConfig($vars);
	}

	/**
	 * Finishes configuration parameters
	 */
	function mainConfig($vars)
	{
		global $mainframe;
		
		// get ftp configuration into registry for use in case of safe mode
		JInstallationHelper::setFTPCfg( $vars );
		
		// Require the xajax library
		require_once( JPATH_BASE.DS.'includes'.DS.'xajax'.DS.'xajax.inc.php' );

		/*
		 * Instantiate the xajax object and register the function
		 */
		$xajax = new xajax($mainframe->getBaseURL().'includes/jajax.php');
		$xajax->registerFunction(array('instDefault', 'JAJAXHandler', 'sampledata'));
//		$xajax->debugOn();
		$xajax->errorHandlerOn();
		$doc =& $mainframe->getDocument();
		$doc->addCustomTag($xajax->getJavascript('', 'includes/js/xajax.js', 'includes/js/xajax.js'));

			
		/*
		 * Deal with possible sql script uploads from this stage
		 */
		$vars['loadchecked'] = 0;
		if (JRequest::getVar( 'sqlupload', 0, 'post', 'int' ) == 1) {
			$vars['sqlresponse'] = JInstallationHelper::uploadSql( $vars );
			$vars['dataloaded'] = '1';
			$vars['loadchecked'] = 1;
		}
		if (JRequest::getVar( 'migrationupload', 0, 'post', 'int' ) == 1) {
			$vars['migresponse'] = JInstallationHelper::uploadSql( $vars, true );
			$vars['dataloaded'] = '1';
			$vars['loadchecked'] = 2;
		}


//		$strip = get_magic_quotes_gpc();

		if (isset ($vars['siteName'])) {
			$vars['siteName'] = stripslashes(stripslashes($vars['siteName']));
		}

		/*
		 * Import the authentication library
		 */
		jimport('joomla.application.user.authenticate');

		/*
		 * Generate a random admin password
		 */
		$vars['adminPassword'] = JAuthenticateHelper::genRandomPassword(8);

		$folders = array (
			'administrator/backups',
			'administrator/components',
			'administrator/language',
			'administrator/modules',
			'administrator/templates',
			'cache',
			'components',
			'images',
			'images/banners',
			'images/stories',
			'language',
			'plugins',
			'plugins/content',
			'plugins/editors',
			'plugins/search',
			'plugins/system',
			'tmp',
			'modules',
			'templates',
		);
		
		

		/*
		 * Now lets make sure we have permissions set on the appropriate folders
		 */
//		foreach ($folders as $folder)
//		{
//			if (!JInstallationHelper::setDirPerms( $folder, $vars ))
//			{
//				$lists['folderPerms'][] = $folder;
//			}
//		}

		return JInstallationView::mainConfig($vars);
	}

	function saveConfig(&$vars)
	{
		global $mainframe;

		/*
		 * Import authentication library
		 */
		jimport( 'joomla.application.user.authenticate' );

		/*
		 * Set some needed variables
		 */
		$vars['siteUrl']			= $mainframe->getSiteURL();
		$vars['secret']			= JAuthenticateHelper::genRandomPassword(16);
		$vars['hidePdf']		= intval(!is_writable(JPATH_SITE.DS.'tmp'.DS));
		$vars['cachePath']	= JPATH_SITE.DS.'cache';

		/*
		 * If FTP has not been enabled, set the value to 0
		 */
		if (!isset($vars['ftpEnable']))
		{
			$vars['ftpEnable'] = 0;
		}

		$strip = get_magic_quotes_gpc();
		if (!$strip) {
			$vars['siteName'] = addslashes($vars['siteName']);
		}

		switch ($vars['DBtype']) {
			case 'mssql' :
				$vars['ZERO_DATE'] = '1/01/1990';
				break;
			default :
				$vars['ZERO_DATE'] = '0000-00-00 00:00:00';
				break;
		}

		JInstallationHelper::createAdminUser($vars);

		$tmpl = & JInstallationView::createTemplate();
		$tmpl->readTemplatesFromFile('configuration.html');
		$tmpl->addVars('configuration', $vars, 'var_');

		$buffer = $tmpl->getParsedTemplate('configuration');
		$path = JPATH_CONFIGURATION.DS.'configuration.php';

		if (file_exists($path)) {
			$canWrite = is_writable($path);
		} else {
			$canWrite = is_writable(JPATH_SITE);
		}

		/*
		 * If the file exists but isn't writable OR if the file doesn't exist and the parent directory
		 * is not writable we need to use FTP
		 */
		 $ftpFlag = false;
		if ((file_exists($path) && !is_writable($path)) || (!file_exists($path) && !is_writable(dirname($path)))) {
			$ftpFlag = true;
		}

		// Check for safe mode
		if (ini_get('safe_mode')) {
			$ftpFlag = true;
		}

		// Enable/Disable override
		if (!isset($vars['ftpEnable']) || ($vars['ftpEnable'] != 1)) {
			$ftpFlag = false;
		}

		if ($ftpFlag == true) {

			// Connect the FTP client
			jimport('joomla.connector.ftp');
			jimport('joomla.filesystem.path');

			$ftp = & JFTP::getInstance($vars['ftpHost'], $vars['ftpPort']);
			$ftp->login($vars['ftpUser'], $vars['ftpPassword']);

			//Translate path for the FTP account
			$file = JPath::clean(str_replace(JPATH_SITE, $vars['ftpRoot'], $path), false);

			// Use FTP write buffer to file
			if (!$ftp->write($file, $buffer)) {
				return $buffer;
			}

			$ftp->quit();
			return '';

		} else {
			if ($canWrite) {
				file_put_contents($path, $buffer);
				return '';
			} else {
				return $buffer;
			}
		}
	}

	/**
	 * Displays the finish screen
	 */
	function finish($vars, $buffer = '')
	{
		global $mainframe;

		$vars['siteurl'] = $mainframe->getSiteURL();
		$vars['adminurl'] = $vars['siteurl'].'administrator/';

		return JInstallationView::finish($vars, $buffer);
	}
}

/**
* @package Joomla
* @subpackage Installation
*/
class JInstallationHelper
{
	/**
	 * @return string A guess at the db required
	 */
	function detectDB()
	{
		$map = array ('mysql_connect' => 'mysql', 'mysqli_connect' => 'mysqli', 'mssql_connect' => 'mssql');
		foreach ($map as $f => $db) {
			if (function_exists($f)) {
				return $db;
			}
		}
		return 'mysql';
	}

	/**
	 * @param array
	 * @return string
	 */
	function errors2string(& $errors)
	{
		$buffer = '';
		foreach ($errors as $error) {
			$buffer .= 'SQL='.$error['msg'].":\n- - - - - - - - - -\n".$error['sql']."\n= = = = = = = = = =\n\n";
		}
		return $buffer;
	}
	/**
	 * Creates a new database
	 * @param object Database connector
	 * @param string Database name
	 * @param boolean utf-8 support
	 * @param string Selected collation
	 * @return boolean success
	 */
	function createDatabase(& $database, $DBname, $DButfSupport, $DBcollation)
	{
		if ($DButfSupport) {
			$sql = "CREATE DATABASE `$DBname` CHARACTER SET `utf8` COLLATE `$DBcollation`";
		} else {
			$sql = "CREATE DATABASE `$DBname`";
		}

		$database->setQuery($sql);
		$database->query();
		$result = $database->getErrorNum();

		if ($result != 0) {
			return false;
		}

		return true;
	}

	/**
	 * Sets character set of the database to utf-8 with selected collation
	 * Used in instances of pre-existing database
	 * @param object Database object
	 * @param string Database name
	 * @param string Selected collation
	 * @return boolean success
	 */
	function setDBCharset(& $database, $DBname, $DBcollation)
	{
		if ($database->hasUTF()){
			$sql = "ALTER DATABASE `$DBname` CHARACTER SET `utf8` COLLATE `$DBcollation`";
			$database->setQuery($sql);
			$database->query();
			$result = $database->getErrorNum();
			if ($result != 0) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Backs up existing tables
	 * @param object Database connector
	 * @param array An array of errors encountered
	 */
	function backupDatabase(& $database, $DBname, $DBPrefix, & $errors)
	{
		// Initialize backup prefix variable
		// TODO: Should this be user-defined?
		$BUPrefix = 'bak_';

		$query = "SHOW TABLES FROM `$DBname`";
		$database->setQuery($query);
		$errors = array ();
		if ($tables = $database->loadResultArray()) {
			foreach ($tables as $table) {
				if (strpos($table, $DBPrefix) === 0) {
					$butable = str_replace($DBPrefix, $BUPrefix, $table);
					$query = "DROP TABLE IF EXISTS `$butable`";
					$database->setQuery($query);
					$database->query();
					if ($database->getErrorNum()) {
						$errors[$database->getQuery()] = $database->getErrorMsg();
					}
					$query = "RENAME TABLE `$table` TO `$butable`";
					$database->setQuery($query);
					$database->query();
					if ($database->getErrorNum()) {
						$errors[$database->getQuery()] = $database->getErrorMsg();
					}
				}
			}
		}

		return count($errors);
	}
	/**
	 * Deletes all database tables
	 * @param object Database connector
	 * @param array An array of errors encountered
	 */
	function deleteDatabase(& $database, $DBname, $DBPrefix, & $errors)
	{
		$query = "SHOW TABLES FROM `$DBname`";
		$database->setQuery($query);
		$errors = array ();
		if ($tables = $database->loadResultArray()) {
			foreach ($tables as $table) {
				if (strpos($table, $DBPrefix) === 0) {
					$query = "DROP TABLE IF EXISTS `$table`";
					$database->setQuery($query);
					$database->query();
					if ($database->getErrorNum()) {
						$errors[$database->getQuery()] = $database->getErrorMsg();
					}
				}
			}
		}

		return count($errors);
	}

	/**
	 *
	 */
	function populateDatabase(& $database, $sqlfile, & $errors, $collation = '')
	{
		if( !($buffer = file_get_contents($sqlfile)) ){
			return -1;
		}
		$queries = JInstallationHelper::splitSql($buffer, $collation);

		foreach ($queries as $query) {
			$query = trim($query);
			if ($query != '' && $query {0} != '#') {
				$database->setQuery($query);
				$database->query();
				JInstallationHelper::getDBErrors($errors, $database ); 
			}
		}
		return count($errors);
	}

	/**
	 * @param string
	 * @return array
	 */
	function splitSql($sql, $collation)
	{
		$sql = trim($sql);
		$sql = preg_replace("/\n\#[^\n]*/", '', "\n".$sql);
		if ($collation != '') {
			$sql = str_replace("utf8_general_ci", $collation, $sql);
		}
		$buffer = array ();
		$ret = array ();
		$in_string = false;

		for ($i = 0; $i < strlen($sql) - 1; $i ++) {
			if ($sql[$i] == ";" && !$in_string) {
				$ret[] = substr($sql, 0, $i);
				$sql = substr($sql, $i +1);
				$i = 0;
			}

			if ($in_string && ($sql[$i] == $in_string) && $buffer[1] != "\\") {
				$in_string = false;
			}
			elseif (!$in_string && ($sql[$i] == '"' || $sql[$i] == "'") && (!isset ($buffer[0]) || $buffer[0] != "\\")) {
				$in_string = $sql[$i];
			}
			if (isset ($buffer[1])) {
				$buffer[0] = $buffer[1];
			}
			$buffer[1] = $sql[$i];
		}

		if (!empty ($sql)) {
			$ret[] = $sql;
		}
		return ($ret);
	}

	/**
	 * Calculates the file/dir permissions mask
	 */
	function getFilePerms($input, $type = 'file')
	{
		$perms = '';
		if (mosGetParam($input, $type.'PermsMode', 0)) {
			$action = ($type == 'dir') ? 'Search' : 'Execute';
			$perms = '0'. (mosGetParam($input, $type.'PermsUserRead', 0) * 4 + mosGetParam($input, $type.'PermsUserWrite', 0) * 2 + mosGetParam($input, $type.'PermsUser'.$action, 0)). (mosGetParam($input, $type.'PermsGroupRead', 0) * 4 + mosGetParam($input, $type.'PermsGroupWrite', 0) * 2 + mosGetParam($input, $type.'PermsGroup'.$action, 0)). (mosGetParam($input, $type.'PermsWorldRead', 0) * 4 + mosGetParam($input, $type.'PermsWorldWrite', 0) * 2 + mosGetParam($input, $type.'PermsWorld'.$action, 0));
		}
		return $perms;
	}

	/**
	 * Creates the admin user
	 */
	function createAdminUser(& $vars)
	{
		$DBtype			= mosGetParam($vars, 'DBtype', 'mysql');
		$DBhostname	= mosGetParam($vars, 'DBhostname', '');
		$DBuserName	= mosGetParam($vars, 'DBuserName', '');
		$DBpassword	= mosGetParam($vars, 'DBpassword', '');
		$DBname			= mosGetParam($vars, 'DBname', '');
		$DBPrefix			= mosGetParam($vars, 'DBPrefix', '');

		$adminPassword	= mosGetParam($vars, 'adminPassword', '');
		$adminEmail			= mosGetParam($vars, 'adminEmail', '');

		$cryptpass = md5($adminPassword);
		$vars['adminLogin'] = 'admin';

		jimport('joomla.database.database');
		$database = & JDatabase::getInstance($DBtype, $DBhostname, $DBuserName, $DBpassword, $DBname, $DBPrefix);

		// create the admin user
		$installdate 	= date('Y-m-d H:i:s');
		$nullDate 		= $database->getNullDate();
		$query = "INSERT INTO #__users VALUES (62, 'Administrator', 'admin', ".$database->Quote($adminEmail).", ".$database->Quote($cryptpass).", 'Super Administrator', 0, 1, 25, '$installdate', '$nullDate', '', '')";
		$database->setQuery($query);
		if (!$database->query()) {
			// is there already and existing admin in migrated data
			if ( $database->getErrorNum() == 1062 ) {
				$vars['adminLogin'] = JText::_('Admin login in migrated content was kept');
				$vars['adminPassword'] = JText::_('Admin password in migrated content was kept');
				return;
			} else {
				echo $database->getErrorMsg();
				return;
			}
		}

		// add the ARO (Access Request Object)
		$query = "INSERT INTO #__core_acl_aro VALUES (10,'users','62',0,'Administrator',0)";
		$database->setQuery($query);
		if (!$database->query()) {
			echo $database->getErrorMsg();
			return;
		}

		// add the map between the ARO and the Group
		$query = "INSERT INTO #__core_acl_groups_aro_map VALUES (25,'',10)";
		$database->setQuery($query);
		if (!$database->query()) {
			echo $database->getErrorMsg();
			return;
		}
	}

	/**
	 * Find the ftp filesystem root for a given user/pass pair
	 *
	 * @static
	 * @param string $user Username of the ftp user to determine root for
	 * @param string $pass Password of the ftp user to determine root for
	 * @return string Filesystem root for given FTP user
	 * @since 1.5
	 */
	function findFtpRoot($user, $pass, $host='127.0.0.1', $port='21')
	{
		jimport('joomla.connector.ftp');
		$ftp = & JFTP::getInstance($host, $port);
		if (!$ftp->login($user, $pass)) {
			JError::raiseError('SOME_ERROR_CODE', 'JInstallationHelper::findFtpRoot: Unable to login');
		}

		/*
		 * Get file/folder list in CWD
		 */
		$ftpList = $ftp->nameList();
		$ftp->quit();

		/*
		 * Process the list
		 */
		$ftpList = array_map( 'strtolower', $ftpList );
		$parts = explode(DS, JPATH_SITE);
		$i = 1;
		$numParts = count($parts);
		$ftpPath = $parts[0];
		$thePath = JPATH_SITE;

		for ($i = 1; $i < $numParts; $i ++) {
			if (in_array(strtolower($parts[$i]), $ftpList)) {

				$thePath = $ftpPath;
			}
			$ftpPath .= $parts[$i]."/";
		}

		$thePath = str_replace($thePath, '', JPATH_SITE);

		return ($thePath == '') ? "/" : $thePath."/";
	}

	/**
	 * Set default folder permissions
	 *
	 * @param string $path The full file path
	 * @param string $buffer The buffer to write
	 * @return boolean True on success
	 * @since 1.5
	 */
	function setDirPerms($dir, &$srv)
	{
		jimport('joomla.filesystem.path');

		/*
		 * Initialize variables
		 */
		$ftpFlag = false;
		$ftpRoot = $srv['ftpRoot'];

		/*
		 * First we need to determine if the path is chmodable
		 */
		if (!JPath::canChmod(JPath::clean(JPATH_SITE.DS.$dir, false)))
		{
			$ftpFlag = true;
		}

		// Do NOT use ftp if it is not enabled
		if (!$srv['ftpEnable'])
		{
			$ftpFlag = false;
		}

		if ($ftpFlag == true)
		{
			// Connect the FTP client
			jimport('joomla.connector.ftp');
			$ftp = & JFTP::getInstance($srv['ftpHost'], $srv['ftpPort']);
			$ftp->login($srv['ftpUser'],$srv['ftpPassword']);

			//Translate path for the FTP account
			$path = JPath::clean($ftpRoot."/".$dir, false);

			/*
			 * chmod using ftp
			 */
			if (!$ftp->chmod($path, '0755'))
			{
				$ret = false;
			}

			$ftp->quit();
			$ret = true;
		} else
		{

			$path = JPath::clean(JPATH_SITE.DS.$dir, false);

			if (!@ chmod($path, octdec('0755')))
			{
				$ret = false;
			} else
			{
				$ret = true;
			}
		}

		return $ret;
	}

	/**
	 * Uploads a sql script and executes it. Script can be text file or zip/gz packed
	 *
	 * @static
	 * @param array The installation variables
	 * @param boolean true if the script is a migration script
	 * @return string Success or error messages
	 * @since 1.5
	 */
	function uploadSql( &$args, $migration = false ) {
		global $mainframe;
		$archive = '';
		$script = '';

		/*
		 * extend script max process time to prevent time-out with large imports
		 */
		$oldMaxTime = ini_get( 'max_execution_time' );
		set_time_limit( 300 );
		
		/*
		 * Get the uploaded file information
		 */
		if( $migration ) {
			$sqlFile	= JRequest::getVar('migrationFile', '', 'files', 'array');
		} else {
			$sqlFile	= JRequest::getVar('sqlFile', '', 'files', 'array');
		}

		/*
		 * Make sure that file uploads are enabled in php
		 */
		if (!(bool) ini_get('file_uploads')) {
			return JText::_('WARNINSTALLFILE');
		}

		/*
		 * Make sure that zlib is loaded so that the package can be unpacked
		 */
		if (!extension_loaded('zlib')) {
			return JText::_('WARNINSTALLZLIB');
		}

		/*
		 * If there is no uploaded file, we have a problem...
		 */
		if (!is_array($sqlFile) || $sqlFile['size'] < 1) {
			return JText::_('WARNNOFILE');
		}

		/*
		 * Move uploaded file
		 */
		jimport('joomla.filesystem.file');
		$uploaded = JFile::upload($sqlFile['tmp_name'], JPATH_SITE.DS.'tmp'.DS.$sqlFile['name']);
		if( !eregi('.sql$', $sqlFile['name']) ){
			$archive = JPATH_SITE.DS.'tmp'.DS.$sqlFile['name'];
		} else {
			$script = JPATH_SITE.DS.'tmp'.DS.$sqlFile['name'];
		}

		// unpack archived sql files
		if ($archive ){
			$package = JInstallationHelper::unpack( $archive );
			if ( $package === false ) {
				return JText::_('WARNUNPACK');
			}
			$script = $package['folder'].$package['script'];
		}

		jimport('joomla.database.database');
		$database = & JDatabase::getInstance($args['DBtype'], $args['DBhostname'], $args['DBuserName'], $args['DBpassword'], $args['DBname'], $args['DBPrefix']);
		
		/*
		 * If migration perform manipulations on script file before population
		 */
		if ( $migration ) {
			$script = JInstallationHelper::preMigrate($script, $args, $database);
			if ( $script == false ) {
				// TODO
				return "TODO add error text";
			}
		}

		$errors = null;
		$msg = '';
		$result = JInstallationHelper::populateDatabase($database, $script, $errors);

		/*
		 * If migration, perform post population manipulations (menu table construction)
		 */
		$migErrors = null;
		if ( $migration ) {
			$migResult = JInstallationHelper::postMigrate( $database, $migErrors, $args );
			
			if ( $migResult != 0 ) {
				/*
				 * Merge populate and migrate processing errors
				 */
				if( $result == 0 ){
					$result = $migResult;
					$errors = $migErrors;
				} else {
					$result += $migResult;
					$errors = array_merge( $errors, $migErrors );
				}
			}
		}
		

		/*
		 * prepare sql error messages if returned from populate and migrate
		 */
		if (!is_null($errors)){
			foreach($errors as $error){
				$msg .= stripslashes( $error['msg'] );
				$msg .= chr(13)."-------------".chr(13);
				$txt = '<textarea cols="40" rows="4" name="instDefault" readonly="readonly" >'.JText::_("Database Errors Reported").chr(13).$msg.'</textarea>';
			}
		} else {
			// consider other possible errors from populate
			$msg = $result == 0 ? JText::_('SQL script installed successfully') : JText::_('Error installing SQL script') ;
			$txt = '<input size="50" value="'.$msg.'" readonly="readonly" />';
		}

		
		/*
		 * Clean up
		 */
		if ($archive){
			JFile::delete( $archive );
			JFolder::delete( $package['folder'] );
		} else {
			JFile::delete( $script );
		}
		set_time_limit( $oldMaxTime );

		return $txt;
	}

	/**
	 * Unpacks a compressed script file either as zip or gz/ Assumes single file in archive
	 *
	 * @static
	 * @param string $p_filename The uploaded package filename or install directory
	 * @return unpacked filename on success, False on error
	 * @since 1.5
	 */
	function unpack($p_filename) {
		/*
		 * Initialize variables
		 */
		// Path to the archive
		$archivename = $p_filename;
		// Temporary folder to extract the archive into
		$tmpdir = uniqid('install_');


		// Clean the paths to use for archive extraction
		$extractdir = JPath::clean(dirname($p_filename).DS.$tmpdir);
		$archivename = JPath::clean($archivename, false);

		/*
		 * Are we working with a zipfile?
		 */
		if (eregi('.zip$', $archivename)) {

			/*
			 * Import the zipfile libraries
			 */
			jimport('pcl.pclzip');
			jimport('pcl.pclerror');
			//jimport('pcl.pcltrace');

			/*
			 * Create a zipfile object
			 */
			$zipfile = new PclZip($archivename);

			// Constants used by the zip library
			if (JPATH_ISWIN) {
				define('OS_WINDOWS', 1);
			} else {
				define('OS_WINDOWS', 0);
			}

			/*
			 * Now its time to extract the archive
			 */
			if ($zipfile->extract(PCLZIP_OPT_PATH, $extractdir) == 0)
			{
				// Unable to extract the archive, set an error and fail
				JError::raiseWarning(1, JText::_('Extract Error').' "'.$zipfile->errorName(true).'"');
				return false;
			}
			// Set permissions for extracted dir
			JPath::setPermissions($extractdir, '0666', '0777');

			// Free up PCLZIP memory
			unset ($zipfile);
		} else if( eregi('.gz$', $archivename) ){
			//TODO add error handling
			/*
			 * Create the folder
			 */
			JFolder::create( $extractdir );
			
			/*
			 * read the gz file and write content to regular file
			 */
			 $gzFile = @gzopen( $archivename, 'rb' );
			 $unpacked = fopen( $extractdir.'sqldata.sql', 'w');
			 if ( $gzFile ) {
			     $data = '';
			     while ( !gzeof( $gzFile ) ) {
			         $data = gzread( $gzFile, 1024 );
			         $ret = fwrite( $unpacked, $data, 1024 );
			     }
			     gzclose( $gzFile );
			     fclose( $unpacked );
			 }
			
			// Set permissions for extracted dir
			JPath::setPermissions($extractdir, '0666', '0777');

		} else {
			/*
			 * not an archive we handle 
			 */
			 return false;
		}
		
		/*
		 * return the file found in the extract folder and also folder name
		 */
		if ($handle = opendir( $extractdir )) {
   			while (false !== ($file = readdir($handle))) {
       			if ($file != "." && $file != "..") {
          			 $script = $file;
          			 continue;
       			}
   			}
   			closedir($handle);
		}
		$retval['script'] = $script;
		$retval['folder'] = $extractdir;
		return $retval;

	}
	
	/**
	 * Performs pre-populate conversions on a migration script
	 *
	 * @static
	 * @param string $scriptName The uploaded / unpacked script file
	 * $param array $args The installation varibables
	 * @return converted filename on success, False on error
	 * @since 1.5
	 */
	function preMigrate( $scriptName, &$args, $db ) {
		//TODO add error handling
		$buffer = '';
		$newPrefix = $args['DBPrefix'];
		/*
		 * read script file into buffer
		 */
		$buffer = file_get_contents( $scriptName );
		if(  $buffer == false ) {
			return false;
		}
		
		/*
		 * search and replace table prefixes
		 */
		$oldPrefix = trim( $args['oldPrefix']);
		$oldPrefix = rtrim( $oldPrefix, '_' ) . '_';
		$buffer = str_replace( $oldPrefix, $newPrefix, $buffer );
		
		/*
		 * give temp name to menu and modules tables
		 */
		$buffer = str_replace ( $newPrefix.'modules', $newPrefix.'modules_migration', $buffer );
		$buffer = str_replace ( $newPrefix.'menu', $newPrefix.'menu_migration', $buffer );
		
		/*
		 * Create two empty temporary tables
		 */
		 
		$query = 'DROP TABLE IF EXISTS '.$newPrefix.'modules_migration';
		$db->setQuery( $query );
		$db->query();
		
		$query = 'DROP TABLE IF EXISTS '.$newPrefix.'menu_migration';
		$db->setQuery( $query );
		$db->query(); 
		
		$query = 'CREATE TABLE '.$newPrefix.'modules_migration SELECT * FROM '.$newPrefix.'modules WHERE 0';
		$db->setQuery( $query );
		$db->query();
		
		$query = 'CREATE TABLE '.$newPrefix.'menu_migration SELECT * FROM '.$newPrefix.'menu WHERE 0';
		$db->setQuery( $query );
		$db->query();
		
		/*
		 * rename two aro_acl... field names
		 */
		$buffer = preg_replace ( '/group_id(?!.{15,25}aro_id)/', 'id', $buffer );
		$buffer = preg_replace ( '/aro_id(?=.{1,6}section_value)/', 'id', $buffer );
		
		/*
		 * convert to utf-8
		 */
		$srcEncoding = $args['srcEncoding'];
		$buffer = iconv( $srcEncoding, 'utf-8//TRANSLIT', $buffer );
		/*
		 * write to file
		 */
		$newFile = dirname( $scriptName ).DS.'converted.sql';
		$ret = file_put_contents( $newFile, $buffer );
		$buffer = '';
		JFile::delete( $scriptName );
		return $newFile;
	}
	
	/**
	 * Performs post-populate conversions after importing a migration script
	 * These include constructing an appropriate menu table for core content items
	 * and adding core modules from old site to the modules table
	 *
	 * @static
	 * @param JDatabase
	 * @param array errors (by ref)
	 * @return error count
	 * @since 1.5
	 */
	function postMigrate( $db, & $errors, & $args ) {
		
		$newPrefix = $args['DBPrefix'];
		
		
		/*
		 * Check to see if migration is from 4.5.1
		 */
		$query = "SELECT id, usertype FROM ".$newPrefix."users WHERE id = 62";
		$row = $db->getRow( $query );
		JInstallationHelper::getDBErrors($errors, $db ); 
		
		/*
		 * if it is, then fill usertype field with correct values from aro_group
		 */
		if ( $row[1] == 'superadministrator' ){
			$query = "UPDATE ".$newPrefix."users AS u, ".$newPrefix."core_acl_aro_groups AS g" .
					"\n SET u.usertype = g.value" .
					"\n WHERE u.gid = g.id";
			$db->setQuery($query);
			$db->query();
			JInstallationHelper::getDBErrors($errors, $db ); 
		}
		
		/*
		 * Construct the menu table based on old table references to core items
		 */
		$query = "SELECT DISTINCT `option` FROM ".$newPrefix."components WHERE `option` != ''";
		$db->setQuery( $query );
		JInstallationHelper::getDBErrors($errors, $db ); 
		$lookup = $db->loadResultArray();
		$lookup[] = 'com_user&';
		$lookup[] = 'com_content';
		
		$query = 'SELECT * FROM '.$newPrefix.'menu_migration';
		$db->setQuery( $query );
		JInstallationHelper::getDBErrors($errors, $db ); 
		$oldMenuItems = $db->loadObjectList();
		
		$query = 'SELECT * FROM '.$newPrefix.'menu';
		$db->setQuery( $query );
		JInstallationHelper::getDBErrors($errors, $db ); 
		$newMenuItems = $db->loadObjectList();
		
		
		foreach( $oldMenuItems as $item ) {
			if ( $item->id == 1 ) {
				$newMenuItems[0] = $item;
			} else if ( $item->type == 'url' && $item->link == 'index.php' ) {
				$newMenuItems[] = $item;
			} else if ( $item->type == 'url' && JInstallationHelper::isValidItem( $item->link, $lookup ) ) {
				$newMenuItems[] = $item;
			} else if ( $item->type == 'components' && (int) $item->componentid < 17 ) {
				$newMenuItems[] = $item;
			} else if ( $item->type == 'wrapper' ) {
				$newMenuItems[] = $item;
			} else if ( $item->type == 'content_typed' ) {
				//do nothing - not added
			} else if ( substr($item->type, 0, 7) == 'content') {
				$newMenuItems[] = $item;
			}					
		}
		
		$query = 'DELETE FROM '.$newPrefix.'menu WHERE 1';
		$db->setQuery( $query );
		$db->query();
		JInstallationHelper::getDBErrors($errors, $db ); 
		foreach ( $newMenuItems as $item ) {
			$db->insertObject( $newPrefix.'menu', $item );
			JInstallationHelper::getDBErrors($errors, $db ); 
		}
		
		/*
		 * Add core client modules from old site to modules table as unpublished
		 */
		$query = "SELECT module FROM ".$newPrefix."modules WHERE client_id = 0 AND module != 'mod_mainmenu'";
		$db->setQuery( $query );
		JInstallationHelper::getDBErrors($errors, $db ); 
		$lookup = $db->loadResultArray();
		
		$query = "SELECT MAX(id) FROM ".$newPrefix."modules ";
		$db->setQuery( $query );
		JInstallationHelper::getDBErrors($errors, $db ); 
		$nextId = $db->loadResult();
		
		foreach( $lookup as $module ) {
			$row = null;
			$nextId++;
			$qry = "SELECT * FROM ".$newPrefix."modules_migration WHERE module = '".$module."' AND client_id = 0";
			$db->setQuery( $qry );
			JInstallationHelper::getDBErrors($errors, $db ); 
			if ( $db->loadObject( $row ) ) {
				$row->id = $nextId;
				$row->published = 0;
				$db->insertObject( $newPrefix.'modules', $row );
				JInstallationHelper::getDBErrors($errors, $db ); 
			}
		}
		/*
		 * Clean up
		 */
		
		$query = 'DROP TABLE IF EXISTS '.$newPrefix.'modules_migration';
		$db->setQuery( $query );
		$db->query(); 
		JInstallationHelper::getDBErrors($errors, $db ); 

		$query = 'DROP TABLE IF EXISTS '.$newPrefix.'menu_migration';
		$db->setQuery( $query );
		$db->query();
		JInstallationHelper::getDBErrors($errors, $db ); 
		
		
		
		return count( $errors );
	}
	
	function isValidItem ( $link, $lookup ){
		foreach( $lookup as $component ) {
			if ( strpos( $link, $component ) != false ) {
				return true;
			}
		}
		return false;
	}
	
	function getDBErrors( &$errors, $db ) {
		if ($db->getErrorNum() > 0) {
			$errors[] = array ('msg' => $db->getErrorMsg(), 'sql' => $query);
		}
	}
	
	/**
	 * Inserts ftp variables to mainframe registry
	 * Needed to activate ftp layer for file operations in safe mode
	 * 
	 * @param array The post values
	 */
	function setFTPCfg( $vars ) {
		global $mainframe;
		$arr = array();
		$arr['ftp_enable'] = $vars['ftpEnable'];
		$arr['ftp_user'] = $vars['ftpUser'];
		$arr['ftp_pass'] = $vars['ftpPassword'];
		$arr['ftp_root'] = $vars['ftpRoot'];
		$arr['ftp_host'] = $vars['ftpHost'];
		$arr['ftp_port'] = $vars['ftpPort'];
		
		$mainframe->setCfg( $arr, 'config' );
	}
}
?>
