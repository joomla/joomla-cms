<?php
/**
 * @version     1.0.0
 * @package     com_remoterwdtester
 * @copyright   Copyright (C) Joostrap 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Philip Locke <fastnetwebdesign@gmail.com> - http://www.joostrap.com
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View class for a list of Remoterwdtester.
 */
class RemoterwdtesterViewControlpanels extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$this->state		= $this->get('State');
		
		

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			throw new Exception(implode("\n", $errors));
		}
        
		RemoterwdtesterHelper::addSubmenu('controlpanels');
        
		$this->addToolbar();
        
        $this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		require_once JPATH_COMPONENT.'/helpers/remoterwdtester.php';

		$state	= $this->get('State');
		$canDo	= RemoterwdtesterHelper::getActions($state->get('filter.category_id'));

		JToolBarHelper::title(JText::_('COM_REMOTERWDTESTER_TITLE_CONTROLPANELS'), 'controlpanels.png');

        //Check if the form exists before showing the add/edit buttons
        $formPath = JPATH_COMPONENT_ADMINISTRATOR.'/views/controlpanel';
        if (file_exists($formPath)) {

            if ($canDo->get('core.create')) {
			    JToolBarHelper::addNew('controlpanel.add','JTOOLBAR_NEW');
		    }

		    if ($canDo->get('core.edit') && isset($this->items[0])) {
			    JToolBarHelper::editList('controlpanel.edit','JTOOLBAR_EDIT');
		    }

        }

		if ($canDo->get('core.edit.state')) {

            if (isset($this->items[0]->state)) {
			    JToolBarHelper::divider();
			    JToolBarHelper::custom('controlpanels.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
			    JToolBarHelper::custom('controlpanels.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
            } else if (isset($this->items[0])) {
                //If this component does not use state then show a direct delete button as we can not trash
                JToolBarHelper::deleteList('', 'controlpanels.delete','JTOOLBAR_DELETE');
            }

            if (isset($this->items[0]->state)) {
			    JToolBarHelper::divider();
			    JToolBarHelper::archiveList('controlpanels.archive','JTOOLBAR_ARCHIVE');
            }
            if (isset($this->items[0]->checked_out)) {
            	JToolBarHelper::custom('controlpanels.checkin', 'checkin.png', 'checkin_f2.png', 'JTOOLBAR_CHECKIN', true);
            }
		}
        
        //Show trash and delete for components that uses the state field
        if (isset($this->items[0]->state)) {
		    if ($state->get('filter.state') == -2 && $canDo->get('core.delete')) {
			    JToolBarHelper::deleteList('', 'controlpanels.delete','JTOOLBAR_EMPTY_TRASH');
			    JToolBarHelper::divider();
		    } else if ($canDo->get('core.edit.state')) {
			    JToolBarHelper::trash('controlpanels.trash','JTOOLBAR_TRASH');
			    JToolBarHelper::divider();
		    }
        }

		if ($canDo->get('core.admin')) {
			JToolBarHelper::preferences('com_remoterwdtester');
		}
        
        //Set sidebar action - New in 3.0
		JHtmlSidebar::setAction('index.php?option=com_remoterwdtester&view=controlpanels');
        
        $this->extra_sidebar = '';
        //
        
	}
    
	protected function getSortFields()
	{
		return array(
		);
	}

    
}
