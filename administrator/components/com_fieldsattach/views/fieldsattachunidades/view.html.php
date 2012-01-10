<?php
/**
 * @version		$Id: view.hmtl.php 15 2011-09-02 18:37:15Z cristian $
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
 * fieldsattachs View
 */
class fieldsattachViewfieldsattachunidades extends JView
{
	/**
	 * fieldsattachs view display method
	 * @return void
	 */
	function display($tpl = null) 
	{
		// Get data from the model
		//$items = $this->get('Items');
		//$pagination = $this->get('Pagination');
                // Assign data to the view
		$this->items            =  $this->get('Items');
		$this->pagination       = $this->get('Pagination');
                $this->state		= $this->get('State');
                $this->categories	= $this->get('CategoryOrders');
                

		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
		

		// Set the toolbar
		$this->addToolBar();

                $this->state->set('fieldsattachimages.articleid', JRequest::getVar("articleid"));

		// Display the template
		parent::display($tpl);

		// Set the document
		//$this->setDocument();
	}

        protected function create_filter()
        {
                $db =& JFactory::getDBO();
                // Filter ********************************************************************************************
		$filter_catid= 0;

		$javascript		= 'onchange=" this.form.submit();;"';
                $groupid=  JRequest::getVar( 'groupid' , -1);

                //if($groupid>-1) $this->state->set('groupid', $groupid);
                //echo "GROUPID:: ". $this->escape($this->state->get('groupid'));

                $query = 'SELECT * FROM #__fieldsattach_groups as a ORDER BY  a.title ';
		$db->setQuery($query);
                //echo $query;

		$articleslist[] = JHTML::_('select.option',  '-1', JText::_( '- Choose group -' ), 'id', 'title' );
                $articleslist = array_merge( $articleslist, $db->loadObjectList() );

		 $lists_familylist  = JHTML::_('select.genericlist', $articleslist, 'groupid',  $javascript, 'id', 'title' ,  $this->state->get('groupid')   );
                //$lists_familylist  = JHtml::_('select.options', $articleslist, 'id', 'title', $this->state->get('filter.groupid'));
                return $lists_familylist;
        }

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar() 
	{
		$canDo = fieldsattachHelper::getActions();
		JToolBarHelper::title(JText::_('COM_FIELDATTACH_MANAGER_FIELDATTACHUNIDADES'), 'weblinks');
		if ($canDo->get('core.create')) 
		{
			JToolBarHelper::addNew('fieldsattachunidad.add', 'JTOOLBAR_NEW');
		}
		if ($canDo->get('core.edit')) 
		{
			JToolBarHelper::editList('fieldsattachunidad.edit', 'JTOOLBAR_EDIT');
		}
		if ($canDo->get('core.delete')) 
		{
			JToolBarHelper::deleteList('', 'fieldsattachunidad.delete', 'JTOOLBAR_DELETE');
		}
		if ($canDo->get('core.admin')) 
		{
			JToolBarHelper::divider();
			JToolBarHelper::preferences('com_fieldsattach');
		}
	}
	/**
	 * Method to set up the document properties
	 *
	 * @return void
	 */
	protected function setDocument() 
	{
		//$document = JFactory::getDocument();
		//$document->setTitle(JText::_('COM_FIELDSATTACH_ADMINISTRATION'));
	}
}
