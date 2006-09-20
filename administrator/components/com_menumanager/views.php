<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Menus
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

jimport( 'joomla.application.view' );

/**
 * A delete confirmation page. Writes list of the items that have been selected
 * for deletion
 * @package Joomla
 * @subpackage Menus
 */
class JMenuManagerListView extends JView
{
	/**
	 * Toolbar for view
	 */
	function setToolbar() {
		JMenuBar::title( JText::_( 'Menu Manager' ), 'menumgr.png' );
		JMenuBar::customX( 'copyconfirm', 'copy.png', 'copy_f2.png', 'Copy', true );
		JMenuBar::customX( 'deleteconfirm', 'delete.png', 'delete_f2.png', 'Delete', true );
		JMenuBar::editListX();
		JMenuBar::addNewX();
		JMenuBar::help( 'screen.menumanager' );
	}

	/**
	 * Display the view
	 */
	function display( $menus, $page )
	{
		global $mainframe;

		$this->setToolbar();
		$limitstart = JRequest::getVar('limitstart', '0', '', 'int');

		$document	= & JFactory::getDocument();
		$document->addScript('../includes/js/joomla/popup.js');
		$document->addStyleSheet('../includes/js/joomla/popup.css');

		mosCommonHTML::loadOverlib();
		?>
		<?php
	}
}
?>