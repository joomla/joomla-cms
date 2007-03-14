<?php

/**
 * @version		$Id: classes.php 6774 2007-03-05 02:42:09Z friesengeist $
 * @package		Joomla
 * @subpackage	Installation
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * @package		Joomla
 * @subpackage	Installation
 */

jimport('joomla.application.component.model');

class JInstallationModel extends JModel
{
	/**
	 * Array used to store data between model and view
	 * 
	 * @var		Array
	 * @access	protected
	 * @since	1.5
	 */
	var	$data		= array();
	
	/**
	 * Array used to store user input created during the installation process
	 * 
	 * @var		Array
	 * @access	protected
	 * @since	1.5
	 */
	var	$vars		= array();
	
	/**
	 * Constructor
	 */
	function __construct($config = array())
	{

		$this->_state = new JObject();

		//set the view name
		if (empty( $this->_name ))
		{
			if (isset($config['name']))  {
				$this->_name = $config['name'];
			}
			else
			{
				$r = null;
				if (!preg_match('/Model(.*)/i', get_class($this), $r)) {
					JError::raiseError (500, "JModel::__construct() : Can't get or parse class name.");
				}
				$this->_name = strtolower( $r[1] );
			}
		}

	}
        	
	/**
	 * Generate a panel of language choices for the user to select their language
	 *
	 * @return	boolean True if successful
	 * @access	public
	 * @since	1.5
	 */
	function chooseLanguage()
	{
		global $mainframe;
		
		$vars	=& $this->getVars();
		
		$native = JLanguageHelper::detectLanguage();
		$forced = $mainframe->getLocalise();
		
		if ( !empty( $forced['lang'] ) ){
			$native = $forced['lang'];
		}
		
		$lists = array ();
		$lists['langs'] = JLanguageHelper::createLanguageList($native);

		$this->setData('lists', $lists);
		
		return true;
	}

	/**
	 * Gets the parameters for database creation
	 *
	 * @return	boolean True if successful
	 * @access	public
	 * @since	1.5
	 */
	function dbConfig()
	{
		global $mainframe;

		$vars	=& $this->getVars();

		if (!isset ($vars['DBPrefix'])) {
			$vars['DBPrefix'] = 'jos_';
		}

		$lists	= array ();
		$files	= array ('mysql', 'mysqli',);
		$db		= JInstallationHelper::detectDB();
		foreach ($files as $file)
		{
			$option = array ();
			$option['text'] = $file;
			if (strcasecmp($option['text'], $db) == 0)
			{
				$option['selected'] = 'selected="true"';
			}
			$lists['dbTypes'][] = $option;
		}

		$doc =& JFactory::getDocument();
		
		$this->setData('lists', $lists);
		
		return true;
	}
	
	/**
	 * Displays the finish screen
	 *
	 * @return	boolean True if successful
	 * @access	public
	 * @since	1.5
	 */
	function finish()
	{
		global $mainframe;

		$vars	=& $this->getVars();

		$vars['siteurl']	= $mainframe->getSiteURL();
		$vars['adminurl']	= $vars['siteurl'].'administrator/';

		return true;
	}

	/**
	 * Gets ftp configuration parameters
	 *
	 * @return	boolean True if successful
	 * @access	public
	 * @since	1.5
	 */
	function ftpConfig($DBcreated = '0')
	{
		global $mainframe;

		$vars	=& $this->getVars();

		// Require the xajax library
		require_once( JPATH_BASE.DS.'includes'.DS.'xajax'.DS.'xajax.inc.php' );

		// Instantiate the xajax object and register the function
		$xajax = new xajax(JURI::base().'includes/jajax.php');
		$xajax->registerFunction(array('getFtpRoot', 'JAJAXHandler', 'ftproot'));
		$xajax->registerFunction(array('FTPVerify', 'JAJAXHandler', 'ftpverify'));
		//$xajax->debugOn();

		$vars['DBcreated'] = JArrayHelper::getValue($vars, 'DBcreated', $DBcreated);
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

		$doc =& JFactory::getDocument();
		$doc->addCustomTag($xajax->getJavascript('', 'includes/js/xajax.js', 'includes/js/xajax.js'));

		return true;
	}

	/**
	 * Get data for later use
	 *
	 * @return	string
	 * @access	public
	 * @since	1.5
	 */
	function & getData($key){
		
		if ( ! array_key_exists($key, $this->data) )
		{
			$null = null;
			return $null;
		}
		
		return $this->data[$key];
	}
	
	/**
	 * Get the configuration variables for the installation
	 * 
	 * @return	Array Configuration variables
	 * @access	public
	 * @since	1.5
	 */
	function & getVars()
	{
		if ( ! $this->vars )
		{
			// get a recursively slash stripped version of post
			$post		= (array) JRequest::get( 'post' );
			$postVars	= JArrayHelper::getValue( $post, 'vars', array(), 'array' );
			$session	=& JFactory::getSession();
			$registry	=& $session->get('registry');
			$registry->loadArray($postVars, 'application');
			$this->vars	= $registry->toArray('application');
		}

		return $this->vars;
	}

	/**
	 * Gets the parameters for database creation
	 *
	 *
	 * @return	boolean True if successful
	 * @access	public
	 * @since	1.5
	 */
	function license()
	{
		return true;
	}

	/**
	 * Gets the parameters for database creation
	 *
	 * @return	boolean True if successful
	 * @access	public
	 * @since	1.5
	 */
	function makeDB()
	{
		// Initialize variables
		$vars		= $this->getVars();
		$errors 	= null;

		$lang 		= JArrayHelper::getValue($vars, 'lang', 'en-GB');
		$DBcreated	= JArrayHelper::getValue($vars, 'DBcreated', '0');

		$DBtype 	= JArrayHelper::getValue($vars, 'DBtype', 'mysql');
		$DBhostname = JArrayHelper::getValue($vars, 'DBhostname', '');
		$DBuserName = JArrayHelper::getValue($vars, 'DBuserName', '');
		$DBpassword = JArrayHelper::getValue($vars, 'DBpassword', '');
		$DBname 	= JArrayHelper::getValue($vars, 'DBname', '');
		$DBPrefix 	= JArrayHelper::getValue($vars, 'DBPrefix', 'jos_');
		$DBOld 		= JArrayHelper::getValue($vars, 'DBOld', 'bu');
		//		$DBSample = mosGetParam($vars, 'DBSample', 1);
		$DButfSupport 	= intval(JArrayHelper::getValue($vars, 'DButfSupport', 0));
		$DBversion 		= JArrayHelper::getValue($vars, 'DBversion', '');

		// these 3 errors should be caught by the javascript in dbConfig
		if ($DBtype == '')
		{
			$this->setError(JText::_('validType'));
			$this->setData('back', 'dbconfig');
			$this->setData('errors', $errors);
			return false;
			//return JInstallationView::error($vars, JText::_('validType'), 'dbconfig');
		}
		if (!$DBhostname || !$DBuserName || !$DBname)
		{
			$this->setError(JText::_('validDBDetails'));
			$this->setData('back', 'dbconfig');
			$this->setData('errors', $errors);
			return false;
			//return JInstallationView::error($vars, JText::_('validDBDetails'), 'dbconfig');
		}
		if ($DBname == '')
		{
			$this->setError(JText::_('emptyDBName'));
			$this->setData('back', 'dbconfig');
			$this->setData('errors', $errors);
			return false;
			//return JInstallationView::error($vars, JText::_('emptyDBName'), 'dbconfig');
		}

		if (!$DBcreated)
		{
			jimport('joomla.database.database');
			$db = & JDatabase::getInstance($DBtype, $DBhostname, $DBuserName, $DBpassword, $DBname, $DBPrefix);

			if ($err = $db->getErrorNum()) {
				if ($err == 3) {
					// connection ok, need to create database
					if (JInstallationHelper::createDatabase($db, $DBname, $DButfSupport)) {
						// make the new connection to the new database
						$db = NULL;
						$db = & JDatabase::getInstance($DBtype, $DBhostname, $DBuserName, $DBpassword, $DBname, $DBPrefix);
					} else {
						$this->setError(JText::sprintf('WARNCREATEDB', $DBname));
						$this->setData('back', 'dbconfig');
						$this->setData('errors', $db->getErrorMsg());
						return false;
						//return JInstallationView::error($vars, array (JText::sprintf('WARNCREATEDB', $DBname)), 'dbconfig', $error);
					}
				} else {
					// connection failed
					$this->setError(JText::sprintf('WARNNOTCONNECTDB', $db->getErrorNum()));
					$this->setData('back', 'dbconfig');
					$this->setData('errors', $db->getErrorMsg());
					return false;
					//return JInstallationView::error($vars, array (JText::sprintf('WARNNOTCONNECTDB', $db->getErrorNum())), 'dbconfig', $db->getErrorMsg());
				}
			} else {
				// pre-existing database - need to set character set to utf8
				// will only affect MySQL 4.1.2 and up
				JInstallationHelper::setDBCharset($db, $DBname);
			}

			$db = & JDatabase::getInstance($DBtype, $DBhostname, $DBuserName, $DBpassword, $DBname, $DBPrefix);

			if ($DBOld == 'rm') {
				if (JInstallationHelper::deleteDatabase($db, $DBname, $DBPrefix, $errors)) {
					$this->setError(JText::_('WARNDELETEDB'));
					$this->setData('back', 'dbconfig');
					$this->setData('errors', $errors);
					return false;
					//return JInstallationView::error($vars, , 'dbconfig', JInstallationHelper::errors2string($errors));
				}
			}
			else
			{
				/*
				 * We assume since we aren't deleting the database that we need
				 * to back it up :)
				 */
				if (JInstallationHelper::backupDatabase($db, $DBname, $DBPrefix, $errors)) {
					$this->setError(JText::_('WARNBACKINGUPDB'));
					$this->setData('back', 'dbconfig');
					$this->setData('errors', JInstallationHelper::errors2string($errors));
					return false;
					//return JInstallationView::error($vars, JText::_('WARNBACKINGUPDB'), 'dbconfig', JInstallationHelper::errors2string($errors));
				}
			}

			$type = $DBtype;
			if ($type == 'mysqli') {
				$type = 'mysql';
			}

			// set collation and use utf-8 compatibile script if appropriate
			if ($DButfSupport) {
				$dbscheme = 'sql'.DS.$type.DS.'joomla.sql';
			} else {
				$dbscheme = 'sql'.DS.$type.DS.'joomla_backward.sql';
			}

			if (JInstallationHelper::populateDatabase($db, $dbscheme, $errors) > 0)
			{
				$this->setError(JText::_('WARNPOPULATINGDB'));
				$this->setData('back', 'dbconfig');
				$this->setData('errors', JInstallationHelper::errors2string($errors));
				return false;
				//return JInstallationView::error($vars, JText::_('WARNPOPULATINGDB'), 'dbconfig', JInstallationHelper::errors2string($errors));
			}

		}

		return true;
	}

	/**
	 * Finishes configuration parameters
	 * 
	 * @return	boolean True if successful
	 * @access	public
	 * @since	1.5
	 */
	function mainConfig()
	{
		global $mainframe;

		$vars	=& $this->getVars();
		
		// get ftp configuration into registry for use in case of safe mode
		if($vars['ftpEnable']) {
			JInstallationHelper::setFTPCfg( $vars );
		}

		// Require the xajax library
		require_once( JPATH_BASE.DS.'includes'.DS.'xajax'.DS.'xajax.inc.php' );

		// Instantiate the xajax object and register the function
		$xajax = new xajax(JURI::base().'includes/jajax.php');
		$xajax->registerFunction(array('instDefault', 'JAJAXHandler', 'sampledata'));
		//		$xajax->debugOn();
		$xajax->errorHandlerOn();
		$doc =& JFactory::getDocument();
		$doc->addCustomTag($xajax->getJavascript('', 'includes/js/xajax.js', 'includes/js/xajax.js'));

		// Deal with possible sql script uploads from this stage
		$vars['loadchecked'] = 0;
		if (JRequest::getVar( 'sqlupload', 0, 'post', 'int' ) == 1)
		{
			$vars['sqlresponse'] = JInstallationHelper::uploadSql( $vars );
			$vars['dataloaded'] = '1';
			$vars['loadchecked'] = 1;
		}
		if (JRequest::getVar( 'migrationupload', 0, 'post', 'int' ) == 1)
		{
			$vars['migresponse'] = JInstallationHelper::uploadSql( $vars, true );
			$vars['dataloaded'] = '1';
			$vars['loadchecked'] = 2;
		}

		//		$strip = get_magic_quotes_gpc();

		if (isset ($vars['siteName']))
		{
			$vars['siteName'] = stripslashes(stripslashes($vars['siteName']));
		}

		$folders = array (
			'administrator/backups',
			'administrator/cache',
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

		// Now lets make sure we have permissions set on the appropriate folders
		//		foreach ($folders as $folder)
		//		{
		//			if (!JInstallationHelper::setDirPerms( $folder, $vars ))
		//			{
		//				$lists['folderPerms'][] = $folder;
		//			}
		//		}

		return true;
	}

	/**
	 * Perform a preinstall check
	 *
	 * @return	boolean True if successful
	 * @access	public
	 * @since	1.5
	 */
	function preInstall()
	{
		$vars	=& $this->getVars();
		$lists	= array ();

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

		foreach ($phpRecommended as $setting)
		{
			$lists['phpSettings'][] = array (
				'label' => $setting[0],
				'setting' => $setting[2],
				'actual' => get_php_setting( $setting[1] ),
				'state' => get_php_setting($setting[1]) == $setting[2] ? 'Yes' : 'No'
			);
		}
		
		$this->setData('lists', $lists);

		return true;
	}

	/**
	 * Save the configuration information
	 *
	 * @return	boolean True if successful
	 * @access	public
	 * @since	1.5
	 */
	function saveConfig()
	{
		global $mainframe;

		$vars	=& $this->getVars();
		$lang	=& JFactory::getLanguage();

		// Import authentication library
		jimport( 'joomla.user.helper' );

		// Set some needed variables
		$vars['siteUrl']		= $mainframe->getSiteURL();
		$vars['secret']			= JUserHelper::genRandomPassword(16);

		$vars['offline']		= JText::_( 'STDOFFLINEMSG' );
		$vars['errormsg']		= JText::_( 'STDERRORMSG' );
		$vars['metadesc']		= JText::_( 'STDMETADESC' );
		$vars['metakeys']		= JText::_( 'STDMETAKEYS' );
		$vars['tmp_path']		= JPATH_ROOT.DS.'tmp';
		$vars['log_path']		= JPATH_ROOT.DS.'logs';

		// set default language
		$forced = $mainframe->getLocalise();
		if ( empty($forced['lang']) ) {
			$vars['deflang'] = 'en-GB';
			$vars['bclang'] = 'english';
		} else {
			$vars['deflang'] = $forced['lang'];
			$vars['bclang'] = $lang->getBackwardLang();
		}

		if ( empty( $forced['helpurl'] ) ) {
			$vars['helpurl'] = 'http://help.joomla.org';
		} else {
			$vars['helpurl'] = $forced['helpurl'];
		}

		// If FTP has not been enabled, set the value to 0
		if (!isset($vars['ftpEnable']))
		{
			$vars['ftpEnable'] = 0;
		}

		/*
		 * Trim the last slash from the FTP root, as the FTP root usually replaces JPATH_ROOT.
		 * If the path had a trailing slash, this would lead to double slashes, like "/joomla//configuration.php"
		 */
		if (isset($vars['ftpRoot'])) {
			$vars['ftpRoot'] = rtrim($vars['ftpRoot'], '/');
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

		/**
		 * Write the configuration file
		 */
		jimport('joomla.template.template');

		$tmpl = new JTemplate();
		$tmpl->applyInputFilter('ShortModifiers');

		// load the wrapper and common templates
		$tmpl->setRoot( JPATH_BASE . DS . 'template' . DS. 'tmpl' );

		$tmpl->readTemplatesFromFile('configuration.html');
		$tmpl->addVars('configuration', $vars, 'var_');

		if (empty($vars['ftpSavePass'])) {
			$tmpl->addVar('configuration', 'var_ftpuser', '');
			$tmpl->addVar('configuration', 'var_ftppassword', '');
		}

		$buffer = $tmpl->getParsedTemplate('configuration');
		$path = JPATH_CONFIGURATION.DS.'configuration.php';

		if (file_exists($path)) {
			$canWrite = is_writable($path);
		} else {
			$canWrite = is_writable(JPATH_CONFIGURATION);
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
		if (ini_get('safe_mode'))
		{
			$ftpFlag = true;
		}

		// Enable/Disable override
		if (!isset($vars['ftpEnable']) || ($vars['ftpEnable'] != 1))
		{
			$ftpFlag = false;
		}

		if ($ftpFlag == true)
		{
			// Connect the FTP client
			jimport('joomla.client.ftp');
			jimport('joomla.filesystem.path');

			$ftp = & JFTP::getInstance($vars['ftpHost'], $vars['ftpPort']);
			$ftp->login($vars['ftpUser'], $vars['ftpPassword']);

			// Translate path for the FTP account
			$file = JPath::clean(str_replace(JPATH_CONFIGURATION, $vars['ftpRoot'], $path), '/');

			// Use FTP write buffer to file
			if (!$ftp->write($file, $buffer)) {
				$this->setData('buffer', $buffer);
				return false;
			}

			$ftp->quit();

		}
		else
		{
			if ($canWrite) {
				file_put_contents($path, $buffer);
			} else {
				$this->setData('buffer', $buffer);
				return false;
			}
		}
		
		return true;
	}

	/**
	 * Set data for later use
	 * 
	 * @param	string $key Data key
	 * @param	Mixed data
	 * @access	public
	 * @since	1.5
	 */
	function setData($key, $value){
		$this->data[$key]	= $value;
	}

}

?>
