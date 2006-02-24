<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Installation
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * @package Joomla
 * @subpackage Installation
 */
class JInstallationView 
{
	/**
	 * Static method to create the template object
	 * @param string The name of the body html file
	 * @return patTemplate
	 */
	function &createTemplate( $bodyHtml = null ) 
	{
		jimport('joomla.template.template');

		$tmpl = new JTemplate();

		// load the wrapper and common templates
		$tmpl->setRoot( JPATH_BASE . DS . 'template' . DS. 'tmpl' );
		$tmpl->readTemplatesFromFile( 'page.html' );

		if ($bodyHtml) {
			$tmpl->setAttribute( 'body', 'src', $bodyHtml );
		}

		return $tmpl;
	}

	/**
	 * Shows an error message and back link
	 * @param array Vars to add to the error form
	 * @param mixed A string or array of messages
	 * @param string The name of the step to go back to
	 * @param string An extra message to display in a text area
	 */
	function error( &$vars, $msg, $back, $xmsg='' ) 
	{
		global $steps;

		$tmpl =& JInstallationView::createTemplate( 'error.html' );

		$tmpl->addVars( 'stepbar', $steps, 		'step_' );
		$tmpl->addVar( 'messages', 'message', 	$msg );

		if ($xmsg) {
			$tmpl->addVar( 'xmessages', 'xmessage', $xmsg );
		}

		$tmpl->addVar( 'body', 'back', $back );
		$tmpl->addVars( 'body', $vars, 'var_' );

		return $tmpl->fetch( 'page' );
	}

	/**
	 * The index page
	 * @param array An array of lists
	 */
	function chooseLanguage( &$lists ) 
	{
		global $steps, $mainframe;

		$lang    =& $mainframe->getLanguage();

		$tmpl =& JInstallationView::createTemplate( 'language.html' );

		$steps['lang'] = 'on';

		$tmpl->addVars( 'stepbar', $steps, 'step_' );
		$tmpl->addRows( 'lang-options', $lists['langs'] );
		
		$tmpl->addVar( 'buttons', 'direction', $lang->isRTL() ? 'rtl' : 'ltr');

		return $tmpl->fetch( 'page' );
	}

	/**
	 * The index page
	 * @param array An array of lists
	 */
	function preInstall( $vars, &$lists ) 
	{
		global $steps, $_VERSION, $mainframe;

		$lang    =& $mainframe->getLanguage();

		$tmpl =& JInstallationView::createTemplate( 'preinstall.html' );

		$steps['preinstall'] = 'on';

		$tmpl->addVars( 'stepbar', 	$steps, 	'step_' );
		$tmpl->addVar( 'body', 		'version', 	$_VERSION->getLongVersion() );
		$tmpl->addVars( 'body', 	$vars, 		'var_' );
		
		$tmpl->addVar( 'php-options', 'align', $lang->isRTL() ? 'right' : 'left');
		$tmpl->addRows( 'php-options', 	$lists['phpOptions'] );
		$tmpl->addRows( 'php-settings', $lists['phpSettings'] );

		$tmpl->addVar( 'buttons', 'direction', $lang->isRTL() ? 'rtl' : 'ltr');

		return $tmpl->fetch( 'page' );
	}

	/**
	 * The index page
	 * @param array An array of lists
	 */
	function license( &$vars ) 
	{
		global $steps, $mainframe;

		$lang    =& $mainframe->getLanguage();

		$tmpl =& JInstallationView::createTemplate( 'license.html' );

		$steps['license'] = 'on';

		$tmpl->addVars( 'stepbar', 	$steps, 'step_' );
		$tmpl->addVars( 'body', 	$vars, 	'var_' );
		$tmpl->addVar( 'buttons', 'direction', $lang->isRTL() ? 'rtl' : 'ltr');

		return $tmpl->fetch( 'page' );
	}

	/**
	 * The index page
	 * @param array An array of lists
	 */
	function dbConfig( &$vars, &$lists ) 
	{
		global $steps, $mainframe;

		$lang    =& $mainframe->getLanguage();
		
		$tmpl =& JInstallationView::createTemplate( 'dbconfig.html' );

		$steps['dbconfig'] = 'on';

		$tmpl->addVars( 'stepbar', $steps, 'step_' );
		$tmpl->addVars( 'body', 	$vars, 'var_' );
		$tmpl->addVar( 'buttons', 'direction', $lang->isRTL() ? 'rtl' : 'ltr');
		$tmpl->addRows( 'dbtype-options', $lists['dbTypes'] );

		return $tmpl->fetch( 'page' );
	}

	/**
	 * The index page
	 * @param array An array of lists
	 */
	function dbCollation( &$vars, &$collations ) 
	{
		global $steps;

		$tmpl =& JInstallationView::createTemplate( 'dbcollation.html' );

		$steps['dbcollation'] = 'on';

		$tmpl->addVars( 'stepbar', $steps, 'step_' );
		$tmpl->addVars( 'body', 	$vars, 'var_' );

		if ($vars['DButfSupport']){
			$tmpl->addVar( 'utf_text', 'utfsupport', 'true');
		} else {
			$tmpl->addVar( 'utf_text', 'utfsupport', 'false');
		}

		$tmpl->addRows( 'collation-options', $collations );

		return $tmpl->fetch( 'page' );
	}

	/**
	 * The index page
	 * @param array An array of lists
	 */
	function ftpConfig( &$vars ) 
	{
		global $steps, $mainframe;

		$lang    =& $mainframe->getLanguage();
		
		$tmpl =& JInstallationView::createTemplate( 'ftpconfig.html' );

		$steps['ftpconfig'] = 'on';

		$tmpl->addVars( 'stepbar', $steps, 'step_' );
		$tmpl->addVars( 'body', 	$vars, 'var_' );
		$tmpl->addVar( 'buttons', 'direction', $lang->isRTL() ? 'rtl' : 'ltr');

		return $tmpl->fetch( 'page' );
	}

	/**
	 * The index page
	 * @param array An array of lists
	 */
	function mainConfig( &$vars, &$lists ) 
	{
		global $steps, $mainframe;

		$lang    =& $mainframe->getLanguage();

		$tmpl =& JInstallationView::createTemplate( 'mainconfig.html' );

		$steps['mainconfig'] = 'on';

		$tmpl->addVars( 'stepbar', $steps, 'step_' );
		$tmpl->addVars( 'body', 	$vars, 'var_' );
		$tmpl->addVar( 'buttons', 'direction', $lang->isRTL() ? 'rtl' : 'ltr');
//		$tmpl->addRows( 'folder-perms', $lists['folderPerms'] );

		return $tmpl->fetch( 'page' );
	}

	/**
	 * The finish page for the installer
	 * @param array An array of lists
	 * @param string The configuration file if it could not be saved
	 */
	function finish( &$vars, $buffer ) 
	{
		global $steps, $mainframe;

		$lang    =& $mainframe->getLanguage();

		$tmpl =& JInstallationView::createTemplate( 'finish.html' );

		$steps['finish'] = 'on';

		$tmpl->addVars( 'stepbar', $steps, 'step_' );
		$tmpl->addVars( 'body', 	$vars, 'var_' );
		$tmpl->addVar( 'buttons', 'direction', $lang->isRTL() ? 'rtl' : 'ltr');

		if ($buffer) {
			$tmpl->addVar( 'configuration-error', 'buffer', $buffer );
		}

		return $tmpl->fetch( 'page' );
	}
}
?>