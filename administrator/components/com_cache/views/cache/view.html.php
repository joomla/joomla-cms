<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Cache
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the Cache component
 *
 * @static
 * @package		Joomla
 * @subpackage	Cache
 * @since 1.0
 */
class CacheViewCache extends JView
{
	protected $pagination;
	protected $client;
	protected $rows;
	function display($tpl = null)
	{
		global $mainframe, $option;

		JToolBarHelper::title( JText::_( 'Cache Manager' ), 'cache.png' );
		JToolBarHelper::custom( 'delete', 'delete.png', 'delete_f2.png', 'Delete', true );
		JToolBarHelper::help( 'screen.cache' );

		$submenu = JRequest::getVar('client', '0', '', 'int');
		$client	 =& JApplicationHelper::getClientInfo($submenu);
		if ($submenu == 1) {
			JSubMenuHelper::addEntry(JText::_('Site'), 'index.php?option=com_cache&client=0');
			JSubMenuHelper::addEntry(JText::_('Administrator'), 'index.php?option=com_cache&client=1', true);
		} else {
			JSubMenuHelper::addEntry(JText::_('Site'), 'index.php?option=com_cache&client=0', true);
			JSubMenuHelper::addEntry(JText::_('Administrator'), 'index.php?option=com_cache&client=1');
		}

		//$cmData = new CacheData($client->path.DS.'cache');

		// Set the model path
		$model =& $this->getModel();
		$model->setPath($client->path.DS.'cache');

		// Get data from the model
		$rows		= & $this->get( 'Data');
		$total		= & $this->get( 'Total');
		$pagination = & $this->get( 'Pagination' );

		$this->assignRef('rows',		$rows);
		$this->assignRef('client',		$client);
		$this->assignRef('pagination',	$pagination);

		parent::display($tpl);
	}
}
