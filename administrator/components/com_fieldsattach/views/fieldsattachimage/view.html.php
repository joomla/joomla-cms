<?php
/**
 * @version		$Id: view.html.php 15 2011-09-02 18:37:15Z cristian $
 * @package		fieldsattach
 * @subpackage		Components
 * @copyright		Copyright (C) 2011 - 2020 Open Source Cristian Grañó, Inc. All rights reserved.
 * @author		Cristian Grañó
 * @link		http://joomlacode.org/gf/project/fieldsattach_1_6/
 * @license		License GNU General Public License version 2 or later
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla view library
jimport('joomla.application.component.view');

/**
 * fieldsattach View
 */
class fieldsattachViewfieldsattachimage extends JView
{
	/**
	 * display method of Hello view
	 * @return void
	 */
	public function display($tpl = null) 
	{
		// get the Data
		// Initialiase variables.
		$this->form		= $this->get('Form');
		$this->item		= $this->get('Item');
		$this->script	= $this->get('Script'); 

		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
		  

		// Set the toolbar
		$this->addToolBar();

		// Display the template
		parent::display($tpl);

		// Set the document
		//$this->setDocument();
	}

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar() 
	{
		JRequest::setVar('hidemainmenu', true);
		$user = JFactory::getUser();
		$userId = $user->id;
		$isNew = $this->item->id == 0;
		$canDo = fieldsattachHelper::getActions($this->item->id);
		JToolBarHelper::title($isNew ? JText::_('COM_fieldsattach_MANAGER_fieldsattach_NEW') : JText::_('COM_fieldsattach_MANAGER_fieldsattach_EDIT'), 'fieldsattach');
		// Built the actions for new and existing records.
		if ($isNew) 
		{
			// For new records, check the create permission.
			if ($canDo->get('core.create')) 
			{
				JToolBarHelper::apply('fieldsattachgroup.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('fieldsattachgroup.save', 'JTOOLBAR_SAVE');
				JToolBarHelper::custom('fieldsattachgroup.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
			}
			JToolBarHelper::cancel('cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			if ($canDo->get('core.edit'))
			{
				// We can save the new record
				JToolBarHelper::apply('fieldsattachgroup.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('fieldsattachgroup.save', 'JTOOLBAR_SAVE');

				// We can save this record, but check the create permission to see if we can return to make a new one.
				if ($canDo->get('core.create')) 
				{
					JToolBarHelper::custom('fieldsattachgroup.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
				}
			}
			/*if ($canDo->get('core.create'))
			{
				JToolBarHelper::custom('fieldsattachgroup.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
			}*/
			JToolBarHelper::cancel('fieldsattachgroup.cancel', 'JTOOLBAR_CLOSE');
		}
	}
	/**
	 * Method to set up the document properties
	 *
	 * @return void
	 */
	protected function setDocument() 
	{
                
		if ($this->item->id == 0)  {$isNew=0;};
		$document = JFactory::getDocument();
		$document->setTitle($isNew ? JText::_('COM_FIELDSATTACH_FIELDSATTACH_CREATING') : JText::_('COM_FIELDSATTACH_FIELDSATTACH_EDITING'));
		$document->addScript(JURI::root() . $this->script);
		$document->addScript(JURI::root() . "/administrator/components/com_fieldsattach/views/fieldsattachgroup/submitbutton.js");
		JText::script('COM_FIELDSATTACH_FIELDSATTACH_ERROR_UNACCEPTABLE');
	}
}
