<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Installation
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
  */

define('_JEXEC', 1);
define('JPATH_BASE', dirname(dirname(__FILE__)));
define('DS', DIRECTORY_SEPARATOR);
define('JXPATH_BASE', JPATH_BASE.DS.'includes');

require_once JPATH_BASE .DS.'includes'.DS.'defines.php';
require_once JPATH_BASE .DS.'includes'.DS.'framework.php';

// create the mainframe object
$mainframe = JFactory::getApplication('installation');

// Make sure that Joomla! is not yet installed
if (file_exists(JPATH_CONFIGURATION.DS.'configuration.php') && (filesize(JPATH_CONFIGURATION.DS.'configuration.php') > 10)) {
	header('Location: ../../index.php');
	exit();
}

// System includes
require_once JPATH_LIBRARIES		.DS.'joomla'.DS.'import.php';

require_once JPATH_BASE . DS. 'installer' . DS . 'helper.php';
// Require the xajax library
require_once JXPATH_BASE.DS.'xajax'.DS.'xajax.inc.php';
$xajax = new xajax();
$xajax->errorHandlerOn();

$xajax->registerFunction(array('getFtpRoot', 'JAJAXHandler', 'ftproot'));
$xajax->registerFunction(array('FTPVerify', 'JAJAXHandler', 'ftpverify'));
$xajax->registerFunction(array('instDefault', 'JAJAXHandler', 'sampledata'));

JError::setErrorHandling(E_ERROR, 'callback', array('JAJAXHandler','handleError'));
JError::setErrorHandling(E_WARNING, 'callback', array('JAJAXHandler','handleError'));
JError::setErrorHandling(E_NOTICE, 'callback', array('JAJAXHandler','handleError'));
jimport('joomla.utilities.compat.compat');



// initialuse the application
$mainframe->initialise();

/**
 * AJAX Task handler class
 *
 * @static
 * @package		Joomla
 * @subpackage	Installer
 * @since 1.5
 */
class JAJAXHandler
{
	function & _getVars()
	{
		static $vars;

		if (! $vars)
		{
			$session	= JFactory::getSession();
			$registry	= $session->get('registry');
			$vars	=& $registry->toArray('application');
		}

		return $vars;
	}

	/**
	 * Method to get the path from the FTP root to the Joomla root directory
	 */
	function ftproot($args)
	{
		jimport('joomla.application.application');
		jimport('joomla.registry.registry');

		$objResponse = new xajaxResponse();
		$args = $args['vars'];

		$root = JInstallationHelper::findFtpRoot($args['ftpUser'], $args['ftpPassword'], $args['ftpHost'], $args['ftpPort']);
		if (JError::isError($root)) {
			$objResponse->addScript('document.getElementById(\'ftpdisable\').checked = true;');
			$objResponse->addAlert(JText::_($root->get('message')));
		} else {
			$objResponse->addAssign('ftproot', 'value', $root);
			$objResponse->addAssign('rootPath', 'style.display', '');
			$objResponse->addScript('document.getElementById(\'verifybutton\').click();');
		}

		return $objResponse;
	}

	/**
	 * Method to verify the ftp values are valid
	 */
	function ftpverify($args)
	{
		jimport('joomla.application.application');
		jimport('joomla.registry.registry');

		$objResponse = new xajaxResponse();
		$args = $args['vars'];

		$status =  JInstallationHelper::FTPVerify($args['ftpUser'], $args['ftpPassword'], $args['ftpRoot'], $args['ftpHost'], $args['ftpPort']);
		if (JError::isError($status)) {
			if (($msg = $status->get('message')) != 'INVALIDROOT') {
				$msg = JText::_('INVALIDFTP') ."\n". JText::_($msg);
			} else {
				$msg = JText::_($msg);
			}
			$objResponse->addScript('document.getElementById(\'ftpdisable\').checked = true;');
			$objResponse->addAlert($msg);
		} else {
			$objResponse->addScript('document.getElementById(\'ftpenable\').checked = true;');
			$objResponse->addAlert(JText::_('VALIDFTP'));
		}

		return $objResponse;
	}

	/**
	 * Method to load and execute a sql script
	 */
	function sampledata($args)
	{
		jimport('joomla.database.database');
		jimport('joomla.language.language');
		jimport('joomla.registry.registry');

		$errors = null;
		$msg = '';
		$objResponse = new xajaxResponse();

		$vars	= JAJAXHandler::_getVars();

		/*
		 * execute the default sample data file
		 */
		$type = $vars['DBtype'];
		if ($type == 'mysqli') {
			$type = 'mysql';
		}
		$dbsample = '../sql'.DS.$type.DS.'sample_data.sql';

		$db = & JInstallationHelper::getDBO($vars['DBtype'], $vars['DBhostname'], $vars['DBuserName'], $vars['DBpassword'], $vars['DBname'], $vars['DBPrefix']);
		$result = JInstallationHelper::populateDatabase($db, $dbsample, $errors);

		/*
		 * prepare sql error messages if returned from populate
		 */
		if (!is_null($errors)){
			foreach($errors as $error){
				$msg .= stripslashes($error['msg']);
				$msg .= chr(13)."-------------".chr(13);
				$txt = '<textarea cols="35" rows="5" name="instDefault" readonly="readonly" >'.JText::_('Database Errors Reported').chr(13).$msg.'</textarea>';
			}
		} else {
			// consider other possible errors from populate
			$msg = $result == 0 ? JText::_("Sample data installed successfully") : JText::_("Error installing SQL script") ;
			$txt = '<input size="35" name="instDefault" value="'.$msg.'" readonly="readonly" />';
		}

		$objResponse->addAssign("theDefault", "innerHTML", $txt);
		return $objResponse;
	}

	/**
	 * Handle a raised error : for now just silently return
	 *
	 * @access	private
	 * @param	object	$error	JError object
	 * @return	object	$error	JError object
	 * @since	1.5
	 */
	function &handleError(&$error)
	{
		return $error;
	}
}

/*
 * Process the AJAX requests
 */
$xajax->cleanBufferOff(); //Needed for suPHP compilance
$xajax->processRequests();
