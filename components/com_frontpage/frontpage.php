<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Content
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

global $mainframe;

$lang =& $mainframe->getLanguage();
$lang->load('com_content');

// require the frontpage html view
require_once (JApplicationHelper::getPath('front_html', 'com_frontpage'));
/**
 * Frontpage Component Controller
 *
 * @static
 * @package Joomla
 * @subpackage Frontpage
 * @since 1.5
 */
class JFrontpageController
{
	function show()
	{
		global $mainframe, $Itemid;

		/*
		 * Initialize some variables
		 */
		$db			= & $mainframe->getDBO();
		$user		= & $mainframe->getUser();
		$gid		= $user->get('gid');

		/*
		 * Create a user access object for the user
		 */
		$access					= new stdClass();
		$access->canEdit		= $user->authorize('action', 'edit', 'content', 'all');
		$access->canEditOwn		= $user->authorize('action', 'edit', 'content', 'own');
		$access->canPublish		= $user->authorize('action', 'publish', 'content', 'all');

		// Parameters
		$menu = JMenu::getInstance();
		$menu = $menu->getItem($Itemid);
		$params = new JParameter($menu->params);

		require_once (dirname(__FILE__).DS.'model'.DS.'frontpage.php');
		$model = new JModelFrontpage($db, $params);

		// Dynamic Page Title
		$mainframe->SetPageTitle($menu->name);

//		$cache = & JFactory::getCache('com_frontpage', 'output');
//		if (!$cache->start('theData', 'com_frontpage')) {
//			JViewFrontpageHTML::show( $model, $access, $menu );
//			$cache->end();
//		}
		JViewFrontpage::show( $model, $access, $menu );
	}
}

/*
 * Show the frontpage
 */
JFrontpageController::show();
?>