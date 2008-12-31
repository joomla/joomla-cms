<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Newsfeeds
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the Newsfeeds component
 *
 * @static
 * @package		Joomla
 * @subpackage	Newsfeeds
 * @since 1.0
 */
class NewsfeedsViewNewsfeeds extends JView
{
	function display($tpl = null)
	{
		global $mainframe, $option;

		$user				=& JFactory::getUser();

		// Set toolbar items for the page
		JToolBarHelper::title(  JText::_( 'Newsfeed Manager' ) );
		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();
		JToolBarHelper::deleteList();
		JToolBarHelper::editListX();
		JToolBarHelper::addNewX();
		JToolBarHelper::preferences( 'com_newsfeeds', 390 );
		JToolBarHelper::help( 'screen.newsfeeds' );

		// Get data from the model
		$items		= & $this->get( 'Data');
		$pagination = & $this->get( 'Pagination' );
		$filter		= & $this->get( 'Filter');

		$cache_path = JPATH_SITE.DS.'cache';
		$cache_writable = is_writable( $cache_path );

		// Is cache directory writable?
		// check to hide certain paths if not super admin
		// TODO: Change this when ACLs more solid
		$cache_folder = '';
		if ( $user->get('gid') == 25 ) {
			$cache_folder = $cache_path;
		}

		$this->assignRef('user',		$user);
		$this->assignRef('cache_folder',	$cache_folder);
		$this->assignRef('cache_writable',	$cache_writable);
		$this->assignRef('items',		$items);
		$this->assignRef('pagination',	$pagination);
		$this->assignRef('filter',		$filter);

		parent::display($tpl);
	}
}