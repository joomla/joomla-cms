<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Banners
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * @package		Joomla
 * @subpackage	Banners
 */
class BannerViewBannerClients extends JView
{
	function display( $tpl = null )
	{
		// Set toolbar items for the page
		JToolBarHelper::title( JText::_( 'Banner Client Manager' ), 'generic.png' );
		JToolBarHelper::deleteList( '', 'remove' );
		JToolBarHelper::editListX( 'edit' );
		JToolBarHelper::addNewX( 'add' );
		JToolBarHelper::help( 'screen.banners.client' );

		// Get data from the model
		$items		= & $this->get( 'Data');
		$total		= & $this->get( 'Total');
		$pagination = & $this->get( 'Pagination' );
		$filter		= & $this->get( 'Filter');

		$this->assignRef('user',		JFactory::getUser());
		$this->assignRef('items',		$items);
		$this->assignRef('pagination',	$pagination);
		$this->assignRef('filter',		$filter);

		parent::display($tpl);
	}
}