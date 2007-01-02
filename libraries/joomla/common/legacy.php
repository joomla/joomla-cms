<?php
/**
* @version $Id$
* @package Joomla.Legacy
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Import library dependencies
jimport( 'joomla.common.legacy.classes' );
jimport( 'joomla.common.legacy.functions' );

/**
 * Legacy define, _ISO defined not used anymore. All output is forced as utf-8
 *
 * @deprecated	As of version 1.5
 */
DEFINE('_ISO','charset=utf-8');

/**
 * Legacy constant, use _JEXEC instead
 *
 * @deprecated	As of version 1.5
 */
define( '_VALID_MOS', 1 );

/**
 * Legacy constant, use _JEXEC instead
 *
 * @deprecated	As of version 1.5
 */
define( '_MOS_MAMBO_INCLUDED', 1 );

/**
 * Legacy constant, use DATE_FORMAT_LC instead
 *
 * @deprecated	As of version 1.5
 */
DEFINE('_DATE_FORMAT_LC',"%A, %d %B %Y"); //Uses PHP's strftime Command Format

/**
 * Legacy constant, use DATE_FORMAT_LC2 instead
 *
 * @deprecated	As of version 1.5
 */
DEFINE('_DATE_FORMAT_LC2',"%A, %d %B %Y %H:%M");

/**
 * Legacy global, use JVersion->getLongVersion() instead
 *
 * @name $_VERSION
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 */
 $GLOBALS['_VERSION']	= new JVersion();
 $version				= $GLOBALS['_VERSION']->getLongVersion();

/**
 * Legacy global, use JFactory::getDBO() instead
 *
 * @name $database
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 */
$conf =& JFactory::getConfig();
$GLOBALS['database'] = new database($conf->getValue('config.host'), $conf->getValue('config.user'), $conf->getValue('config.password'), $conf->getValue('config.db'), $conf->getValue('config.dbprefix'));
$GLOBALS['database']->debug($conf->getValue('config.debug'));

/**
 * Legacy global, use JFactory::getUser() [JUser object] instead
 *
 * @name $my
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 */
$user			= & JFactory::getUser();
$GLOBALS['my']	= $user->getTable();

/**
 * Legacy global, use JApplication::getTemplate() instead
 *
 * @name $cur_template
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 */
global $mainframe;
$GLOBALS['cur_template']	= $mainframe->getTemplate();


/**
 * Legacy global, use JFactory::getUser() instead
 *
 * @name $acl
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 */
$GLOBALS['acl'] =& JFactory::getACL();

/**
 * Legacy global
 *
 * @name $task
 * @deprecated	As of version 1.5
 * @package		Joomla.Legacy
 */
$GLOBALS['task'] = JRequest::getVar('task');

/**
 * Load the site language file (the old way - to be deprecated)
 *
 * @deprecated	As of version 1.5
 */
global $mosConfig_lang;
$file = JPATH_SITE .'/language/' . $mosConfig_lang .'.php';
if (file_exists( $file )) {
	require_once( $file);
} else {
	$file = JPATH_SITE .'/language/english.php';
	if (file_exists( $file )) {
		require_once( $file );
	}
}

/**
 *  Legacy global
 * 	use JApplicaiton->registerEvent and JApplication->triggerEvent for event handling
 *  use JPlugingHelper::importPlugin to load bot code
 *  @deprecated As of version 1.5
 */
$GLOBALS['_MAMBOTS'] = new mosMambotHandler();
?>
