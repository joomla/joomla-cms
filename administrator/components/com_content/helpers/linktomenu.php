<?php
/**
 * @version $Id$
 * @package Joomla
 * @subpackage Content
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights
 * reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

/**
 * @package Joomla
 * @subpackage Menus
 * @author Louis Landry <louis.landry@joomla.org>
 */
class JContentHelperLinkToMenu extends JWizardHelper
{
	var $_helperContext	= 'content';

	var $_helperName	= 'linktomenu';

	/**
	 * Step1
	 */
	// TODO: The wizard should be able to fire these custom steps somehow
	function doStep1()
	{
		$cid		= JRequest::getVar( 'cid', array(), 'post', 'array' );
		$values		=& $this->_wizard->getConfirmation();
		$menuItem	= $values['menu_item'];
		$ordering	= (int) $values['ordering'];
		$log		= array();

		$db			= &JFactory::getDBO();

		if (is_int( $menuItem ))
		{
			// link directly to a menu item
			$query = 'SELECT menutype' .
					' FROM #__menu' .
					' WHERE id = ' . $menuItem;
			$db->setQuery( $query );
			$menuType = $db->loadResult();
			if ($menuType == '')
			{
				JError::raiseNotice( '500', 'Invalid menu type for id = ' . $menuItem );
				return $log;
			}
			$parent		= $menuItem;
		}
		else
		{
			// link to the top of a menu type
			$menuType	= $menuItem;
			$parent		= 0;
		}


		$menuItem	=  new JTableMenu( $db );

		foreach ($cid as $id)
		{
			$query = 'SELECT title' .
					' FROM #__content' .
					' WHERE id = ' . (int) $id;
			$db->setQuery( $query );
			$title = $db->loadResult();

			$menuItem->id			= 0;
			$menuItem->type			= 'content_item_link';
			$menuItem->menutype		= $menuType;
			$menuItem->parent 		= $parent;
			$menuItem->name 		= $title;
			$menuItem->published	= 1;
			$menuItem->componentid	= (int) $id;
			$menuItem->link			= 'index.php?option=com_content&task=view&id='. $id;
			$menuItem->ordering		= $ordering;
			if (!$menuItem->check()) {
				$log[] = $menuItem->getError();
				continue;
			}
			if (!$menuItem->store()) {
				$log[] = $menuItem->getError();
				continue;
			}
			$menuItem->checkin();
			$menuItem->reorder( "menutype = '$menuItem->menutype' AND parent = $menuItem->parent" );
			$log[] = 'Added ' . $title . ' to ' . $menuType;
		}
		return $log;
	}

	/**
	 * @return array
	 */
	function &getConfirmation()
	{
		$log = $this->doStep1();

		return $log;
	}
}
?>