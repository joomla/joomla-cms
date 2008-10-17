<?php

/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Installation
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
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
	public $data		= array();

	/**
	 * Array used to store user input created during the installation process
	 *
	 * @var		Array
	 * @access	protected
	 * @since	1.5
	 */
	public $vars		= array();

	public $test;

	/**
	 * Constructor
	 */
	function __construct($config = array())
	{
		$this->_state = new JStdClass();
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
		$appl = JFactory::getApplication();

		$vars	=& $this->getVars();

		jimport('joomla.language.helper');
		$native = JLanguageHelper::detectLanguage();
		$forced = $appl->getLocalise();

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
		$appl = JFactory::getApplication();

		$vars	=& $this->getVars();

		if (!isset ($vars['DBPrefix'])) {
			$vars['DBPrefix'] = 'jos_';
		}

		$lists	= array ();
		$files	= array ('mysql', 'mysqli',);
		$db		= isset($vars['DBtype']) ? $vars['DBtype'] : JInstallationHelper::detectDB();
		foreach ($files as $file)
		{
			$option = array ();
			$option['text'] = $file;
			if (strcasecmp($option['text'], $db) == 0)
			{
				$option['selected'] = 'selected="selected"';
			}
			$lists['dbTypes'][] = $option;
		}

		$doc = JFactory::getDocument();

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
		$appl = JFactory::getApplication();

		$vars	=& $this->getVars();

		$vars['siteUrl']	= JURI::root();
		$vars['adminUrl']	= $vars['siteUrl'].'administrator/';

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
		$appl = JFactory::getApplication();

		$vars	=& $this->getVars();

		// Require the xajax library
		require_once JPATH_BASE.DS.'includes'.DS.'xajax'.DS.'xajax.inc.php';

		// Instantiate the xajax object and register the function
		$xajax = new xajax(JURI::base().'installer/jajax.php');
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
	 * Get the local PHP settings
	 *
	 * @param	$val Value to get
	 * @return	Mixed
	 * @access	protected
	 * @since	1.5
	 */
	function getPhpSetting($val) {
		$r =  (ini_get($val) == '1' ? 1 : 0);
		return $r ? 'ON' : 'OFF';
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
	function makeDB($vars = false)
	{
		$appl = JFactory::getApplication();

		// Initialize variables
		if ($vars === false) {
			$vars	= $this->getVars();
		}

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
		if (!preg_match( '#^[a-zA-Z]+[a-zA-Z0-9_]*$#', $DBPrefix )) {
			$this->setError(JText::_('MYSQLPREFIXINVALIDCHARS'));
			$this->setData('back', 'dbconfig');
			$this->setData('errors', $errors);
			return false;
		}
		if (strlen($DBPrefix) > 15) {
			$this->setError(JText::_('MYSQLPREFIXTOOLONG'));
			$this->setData('back', 'dbconfig');
			$this->setData('errors', $errors);
			return false;
		}
		if (strlen($DBname) > 64) {
			$this->setError(JText::_('MYSQLDBNAMETOOLONG'));
			$this->setData('back', 'dbconfig');
			$this->setData('errors', $errors);
			return false;
		}

		if (!$DBcreated)
		{
			$DBselect	= false;
			$db = & JInstallationHelper::getDBO($DBtype, $DBhostname, $DBuserName, $DBpassword, null, $DBPrefix, $DBselect);

			if ( JError::isError($db) ) {
				// connection failed
				$this->setError(JText::sprintf('WARNNOTCONNECTDB', $db->toString()));
				$this->setData('back', 'dbconfig');
				$this->setData('errors', $db->toString());
				return false;
			}

			if ($err = $db->getErrorNum()) {
				// connection failed
				$this->setError(JText::sprintf('WARNNOTCONNECTDB', $err ) );
				$this->setData('back', 'dbconfig');
				$this->setData('errors', $db->getErrorMsg());
				return false;
			}

			//Check utf8 support of database
			$DButfSupport = $db->hasUTF();

			try
			{
				$db->select($DBname);
				JInstallationHelper::setDBCharset($db, $DBname);
			}
			catch (JException $e)
			{
				try
				{
					JInstallationHelper::createDatabase($db, $DBname, $DButfSupport);
					$db->select($DBname);
				}
				catch (JException $e)
				{
					// make the new connection to the new database
					//$db = NULL;
					//$db = & JInstallationHelper::getDBO($DBtype, $DBhostname, $DBuserName, $DBpassword, $DBname, $DBPrefix);
					$info = $e->get('info');
					$this->setError(JText::sprintf('WARNCREATEDB', $DBname));
					$this->setData('back',		'dbconfig');
					$this->setData('errors',	$info['errorMsg']);
					return false;
					//return JInstallationView::error($vars, array (JText::sprintf('WARNCREATEDB', $DBname)), 'dbconfig', $error);
				}
			}

			/*
			// Try to select the database
			if ( ! $db->select($DBname) )
			{
				if (JInstallationHelper::createDatabase($db, $DBname, $DButfSupport))
				{
					$db->select($DBname);
					// make the new connection to the new database
					//$db = NULL;
					//$db = & JInstallationHelper::getDBO($DBtype, $DBhostname, $DBuserName, $DBpassword, $DBname, $DBPrefix);
				} else {
					$this->setError(JText::sprintf('WARNCREATEDB', $DBname));
					$this->setData('back', 'dbconfig');
					$this->setData('errors', $db->getErrorMsg());
					return false;
					//return JInstallationView::error($vars, array (JText::sprintf('WARNCREATEDB', $DBname)), 'dbconfig', $error);
				}
			} else {

				// pre-existing database - need to set character set to utf8
				// will only affect MySQL 4.1.2 and up
				JInstallationHelper::setDBCharset($db, $DBname);
			}
			*/

			$db = & JInstallationHelper::getDBO($DBtype, $DBhostname, $DBuserName, $DBpassword, $DBname, $DBPrefix);

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

			// For the install the mysql type will be used
			$type = 'mysql';

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

			// Load the localise.sql for translating the data in joomla.sql/joomla_backwards.sql
			// This feature is available for localized version of Joomla! 1.5
			jimport('joomla.filesystem.file');
			$dblocalise = 'sql'.DS.$type.DS.'localise.sql';
			if(JFile::exists($dblocalise)) {
				if(JInstallationHelper::populateDatabase($db, $dblocalise, $errors) > 0) {
					$this->setError(JText::_('WARNPOPULATINGDB'));
					$this->setData('back', 'dbconfig');
					$this->setData('errors', JInstallationHelper::errors2string($errors));
					return false;
				}
			}

			// Handle default backend language setting. This feature is available for
			// localized versions of Joomla! 1.5.
			$langfiles = $appl->getLocaliseAdmin();
			if (in_array($lang, $langfiles['admin']) || in_array($lang, $langfiles['site'])) {
				// Determine the language settings
				$param[] = Array();
				if (in_array($lang, $langfiles['admin'])) {
					$langparam[] = "administrator=$lang";
				}

				if (in_array($lang, $langfiles['site'])) {
					$langparam[] = "site=$lang";
				}
				$langparams = implode("\n", $langparam);

				// Because database config has not yet been set we just
				// do the trick by a plain update of the proper record.
				$where[] = "`option`='com_languages'";
				$where = (count($where) ? ' WHERE '.implode(' AND ', $where) : '');

				$query = "UPDATE #__components " .
						"SET params='$langparams'" .
						$where;

				$db->setQuery($query);
				if (!$db->query()) {
					return false;
				}
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
		$appl = JFactory::getApplication();

		$vars	=& $this->getVars();

		// get ftp configuration into registry for use in case of safe mode
		if($vars['ftpEnable']) {
			JInstallationHelper::setFTPCfg( $vars );
		}

		// Check a few directories are writeable as this may cause issues
		if(!is_writeable(JPATH_SITE.DS.'tmp') || !is_writeable(JPATH_SITE.DS.'installation'.DS.'sql'.DS.'migration')) {
			$vars['dircheck'] = JText::_('Some paths may be unwritable');
		}

		// Require the xajax library
		require_once JPATH_BASE.DS.'includes'.DS.'xajax'.DS.'xajax.inc.php';

		// Instantiate the xajax object and register the function
		$xajax = new xajax(JURI::base().'installer/jajax.php');
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
		if ((JRequest::getVar( 'migrationupload', 0, 'post', 'int' ) == 1) && (JRequest::getVar( 'migrationUploaded', 0, 'post', 'int' ) == 0))
		{
			jexit(print_r(JRequest::getVar( 'migrationUploaded', 0, 'post', 'int' )));
			$vars['migresponse'] = JInstallationHelper::uploadSql( $vars, true );
			$vars['dataloaded'] = '1';
			$vars['loadchecked'] = 2;
		}
		if(JRequest::getVar( 'migrationUploaded',0,'post','int') == 1) {
			$vars['migresponse'] = JInstallationHelper::findMigration( $vars );
			$vars['dataloaded'] = '1';
			$vars['loadchecked'] = 2;
		}

		//		$strip = get_magic_quotes_gpc();

		if (isset ($vars['siteName']))
		{
			$vars['siteName'] = stripslashes(stripslashes($vars['siteName']));
		}

		/*
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

		//Now lets make sure we have permissions set on the appropriate folders
		foreach ($folders as $folder)
		{
			if (!JInstallationHelper::setDirPerms( $folder, $vars ))
			{
				$lists['folderPerms'][] = $folder;
			}
		}
		*/

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
			'label' => JText::_('PHP version').' >= 5.2',
			'state' => phpversion() < '5.2' ? 'No' : 'Yes'
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
			'label' => JText::_('Session path writable'),
			'state' => is_writable($sp) ? 'Yes' : 'No'
			);*/
		$cW = (@ file_exists('../configuration.php') && @ is_writable('../configuration.php')) || is_writable('../');
		$phpOptions[] = array (
			'label' => 'configuration.php '.JText::_('writable'),
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
			'OFF'
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
				'actual' => $this->getPhpSetting( $setting[1] ),
				'state' => $this->getPhpSetting($setting[1]) == $setting[2] ? 'Yes' : 'No'
			);
		}

		$this->setData('lists', $lists);

		return true;
	}

	/**
	 * Remove directory messages
	 *
	 * @return	Boolean True if successful
	 * @access	public
	 * @since	1.5
	 */
	function removedir()
	{
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
		$appl	= JFactory::getApplication();
		$vars	=& $this->getVars();
		$lang	= JFactory::getLanguage();
		$config	= new JRegistry('config');

		// Import authentication library
		jimport( 'joomla.user.helper' );

		$data	= new JstdClass();
		$data->dbtype 		= $vars['DBtype'];
		$data->host 		= $vars['DBhostname'];
		$data->user 		= $vars['DBuserName'];
		$data->password 	= $vars['DBpassword'];
		$data->db 			= $vars['DBname'];
		$data->dbprefix 	= $vars['DBPrefix'];
		$data->ftp_host 	= $vars['ftpHost'];
		$data->ftp_port 	= $vars['ftpPort'];
		$data->ftp_user 	= $vars['ftpUser'];
		$data->ftp_pass 	= $vars['ftpPassword'];
		$data->ftp_root 	= rtrim($vars['ftpRoot'], '/');
		$data->ftp_enable	 = $vars['ftpEnable'];
		$data->tmp_path		= JPATH_ROOT.DS.'tmp';
		$data->log_path		= JPATH_ROOT.DS.'logs';
		$data->mailer 		= 'mail';
		$data->mailfrom 	= $vars['adminEmail'];
		$data->fromname 	= $vars['siteName'];
		$data->sendmail 	= '/usr/sbin/sendmail';
		$data->smtpauth 	= '0';
		$data->smtpuser 	= '';
		$data->smtppass 	= '';
		$data->smtphost 	= 'localhost';
		$data->debug 		= 0;
		$data->caching 		= '0';
		$data->cachetime	= '900';
		$data->language  	= $vars['lang'];
		$data->secret		= JUserHelper::genRandomPassword(16);
		$data->editor		= 'none';
		$data->offset		= 0;
		$data->lifetime		= 15;

		$data->list_limit	= 30;
		$data->debug_lang 	= 0;
		$data->gzip 		= 0;
		$data->xmlrpc_server	= 0;
		$data->cache_handler	= 'file';
		$data->MetaAuthor 	= '';
		$data->MetaTitle	= '';
		$data->sef		= 0;
		$data->sef_rewrite	= 0;
		$data->sef_suffix 	= 0;
		$data->feed_limit 	= 0;
		$data->session_handler	= 'database';

		$data->MetaDesc			= JText::_( 'STDMETADESC' );
		$data->MetaKeys			= JText::_( 'STDMETAKEYS' );
		$data->offline 		= 0;
		$data->offline_message	= JText::_( 'STDOFFLINEMSG' );
		// @todo: change to -1 before release
		$data->error_reporting	= '2047';
		$data->helpurl			= 'http://help.joomla.org';

		$config->loadObject($data);

		// Update the credentials with the new settings
		if ( $data->ftp_enable )
		{
			jimport('joomla.client.helper');
			$oldconfig =& JFactory::getConfig();
			$oldconfig->setValue('config.ftp_enable', $data->ftp_enable);
			$oldconfig->setValue('config.ftp_host', $data->ftp_host);
			$oldconfig->setValue('config.ftp_port', $data->ftp_port);
			$oldconfig->setValue('config.ftp_user', $data->ftp_user);
			$oldconfig->setValue('config.ftp_pass', $data->ftp_pass);
			$oldconfig->setValue('config.ftp_root', $data->ftp_root);
			JClientHelper::getCredentials('ftp', true);
		}

		/**
		 * Write the configuration file
		 */
		$fname		= JPATH_CONFIGURATION.DS.'configuration.php';
		$written	= NULL;

		// Get the config registry in PHP class format and write it to configuation.php
		jimport('joomla.filesystem.file');
		$written = JFile::write($fname, $config->toString('PHP', 'config', array('class' => 'JConfig')));

		if ( ! $written )
		{
			return false;
		}

		JInstallationHelper::createAdminUser($vars);

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

	function dumpLoad() {
		include (JPATH_BASE . '/includes/bigdump.php');
	}

	function checkUpload() {
		// pie
		$vars	=& $this->getVars();
		//print_r($vars);
		$sqlFile	= JRequest::getVar('sqlFile', '', 'files', 'array');
		if(JRequest::getVar( 'sqlUploaded', 0, 'post', 'bool' ) == false) {
			/*
			 * Move uploaded file
			 */
			// Set permissions for tmp dir
			JInstallationHelper::_chmod(JPATH_SITE.DS.'tmp', 0777);
			jimport('joomla.filesystem.file');
			$uploaded = JFile::upload($sqlFile['tmp_name'], JPATH_SITE.DS.'tmp'.DS.$sqlFile['name']);
			if(!$uploaded) {
				$this->setError(JText::_('WARNUPLOADFAILURE'));
				return false;
			}

			if( !eregi('.sql$', $sqlFile['name']) )
			{
				$archive = JPATH_SITE.DS.'tmp'.DS.$sqlFile['name'];
			}
			else
			{
				$script = JPATH_SITE.DS.'tmp'.DS.$sqlFile['name'];
			}

			// unpack archived sql files
			if (isset($archive) && $archive )
			{
				$package = JInstallationHelper::unpack( $archive, $vars );
				if ( $package === false )
				{
					$this->setError(JText::_('WARNUNPACK'));
					return false;
				}
				$script = $package['folder'].DS.$package['script'];
			}
		} else {
			$script = JPATH_BASE . DS . 'sql' . DS . 'migration' . DS . 'migrate.sql';
		}
		$migration = JRequest::getVar( 'migration', 0, 'post', 'bool' );
		/*
		 * If migration perform manipulations on script file before population
		 */
		if ($migration == true) {
					$db = & JInstallationHelper::getDBO($vars['DBtype'], $vars['DBhostname'], $vars['DBuserName'], $vars['DBpassword'], $vars['DBname'], $vars['DBPrefix']);
		$script = JInstallationHelper::preMigrate($script, $vars, $db);
		if ( $script == false )
		{
			$this->setError(JText::_( 'Script operations failed' ));
			return false;
		}
		} // Disable in testing */
		// Ensure the script is always in the same location
		if($script != JPATH_BASE . DS . 'sql' . DS . 'migration' . DS . 'migrate.sql') {
			JFile::move($script, JPATH_BASE . DS . 'sql' . DS . 'migration' . DS . 'migrate.sql');
		}
		//$this->setData('scriptpath',$script);
		$vars['dataloaded'] = '1';
		$vars['loadchecked'] = '1';
		$vars['migration'] = $migration;
		return true;
	}


	function postMigrate() {
		$migErrors = null;
		$args =& $this->getVars();
		$db = & JInstallationHelper::getDBO($args['DBtype'], $args['DBhostname'], $args['DBuserName'], $args['DBpassword'], $args['DBname'], $args['DBPrefix']);
		$migResult = JInstallationHelper::postMigrate( $db, $migErrors, $args );
		if(!$migResult) echo JText::_("Migration Successful");
			else {
				echo '<div id="installer">';
				echo '<p>'.JText::_('Migration failed').':</p>';
				foreach($migErrors as $error) echo '<p>'.$error['msg'].'</p>';
				echo '</div>';
			}
		return $migResult;
	}
}
