<?php
/**
 * @version		$Id: view.html.php 01 2011-08-13 11:37:09Z maverick $
 * @package		CoreJoomla.cjlib
 * @subpackage	Components
 * @copyright	Copyright (C) 2009 - 2011 corejoomla.com. All rights reserved.
 * @author		Maverick
 * @link		http://www.corejoomla.com/
 * @license		License GNU General Public License version 2 or later
 */
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class CjLibViewCountries extends JViewLegacy{
	
	protected $items;
	protected $item;
	protected $pagination;
	protected $state;
	protected $canDo;
	protected $params;
	
	function display($tpl = null) {
		
		$user = JFactory::getUser();
		$app = JFactory::getApplication();
		
		$this->params = JComponentHelper::getParams('com_cjlib');
		
		switch ($this->getLayout()){
			
			default:

				$this->items		= $this->get('Items');
				$this->pagination	= $this->get('Pagination');
				$this->state		= $this->get('State');
				
				// Check for errors.
				if (count($errors = $this->get('Errors'))) {
					
					JError::raiseError(500, implode("\n", $errors));
					return false;
				}
		
				// Set the toolbar
				$this->addToolBar();
				
				break;
		}

		// Set the document
		$this->setDocument();
		
		// Display the template
		parent::display($tpl);
	}
	
	protected function addToolBar(){
		
		$user = JFactory::getUser();
		$this->state = $this->get('State');
		
		if ($user->authorise('core.edit.state', 'com_cjlib')){

			JToolBarHelper::publish('process_queue', 'COM_CJLIB_PROCESS', true);
		}

		if ($user->authorise('core.delete', 'com_cjlib')){
			
			JToolBarHelper::deleteList('', 'delete_queue', 'JTOOLBAR_DELETE');
		}
			
		if ($user->authorise('core.admin', 'com_cjlib')){

			JToolBarHelper::divider();
			JToolBarHelper::preferences('com_cjlib');
		}
	}
	
	protected function setDocument(){
		
		$document = JFactory::getDocument();
		JToolBarHelper::title(JText::_('COM_CJLIB').': <small><small>[ ' . JText::_('COM_CJLIB_COUNTRIES') .' ]</small></small>', 'cjlib.png');
	}
}