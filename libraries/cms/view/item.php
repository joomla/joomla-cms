<?php
/**
 * @package     Joomla.CMS
 * @subpackage  View
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die();

/**
 * Joomla CMS Item View Class. This class is used to display details information of an item
 * or display form allow add/editing items
 *
 * @package     Joomla.CMS
 * @subpackage  View
 * @since       3.5
 */
class JCmsViewItem extends JCmsViewHtml
{

	/**
	 * The model state
	 *
	 * @var JCmsModelState
	 */
	protected $state;

	/**
	 * The record which is being added/edited
	 *
	 * @var Object
	 */
	protected $item;

	/**
	 * Hold actions which can be performed
	 * 
	 * @var JObject
	 */
	protected $canDo;

	/**
	 * Method to display the view
	 * 
	 * @see JCmsViewHtml::display()
	 */
	public function display()
	{
		$this->prepareView();
		parent::display();
	}

	/**
	 * Method to prepare all the data for the view before it is displayed
	 */
	protected function prepareView()
	{
		$this->state = $this->model->getState();
		$this->item = $this->model->getData();
		if ($this->isAdminView)
		{
			$this->form = $this->model->getForm();
			$this->getActions($this->state);
			$this->addToolbar();
		}
	}

	/**
	 * Get actions which users can perform
	 *
	 * @param JCmsModelState $state
	 */
	protected function getActions($state)
	{
		$helperClass = $this->classPrefix . 'Helper';
		if (is_callable($helperClass . '::getActions'))
		{
			$this->canDo = call_user_func(array($helperClass, 'getActions'), $this->name, $state);
		}
		else
		{
			$this->canDo = call_user_func(array('JCmsComponentHelper', 'getActions'), $this->option, $this->name, $state);
		}
	}

	/**
	 * Add the page title and toolbar.
	 */
	protected function addToolbar()
	{
		$user = JFactory::getUser();
		$isNew = ($this->item->id == 0);
		if (isset($this->item->checked_out))
		{
			$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		}
		else
		{
			$checkedOut = false;
		}
		$canDo = $this->canDo;
		if ($this->item->id)
		{
			$toolbarTitle = $this->languagePrefix . '_' . $this->name . '_EDIT';
		}
		else
		{
			$toolbarTitle = $this->languagePrefix . '_' . $this->name . '_NEW';
		}
		JToolBarHelper::title(JText::_(strtoupper($toolbarTitle)));
		// If not checked out, can save the item.
		if (!$checkedOut && ($canDo->get('core.edit') || ($canDo->get('core.create'))))
		{
			
			JToolBarHelper::apply('apply', 'JTOOLBAR_APPLY');
			JToolBarHelper::save('save', 'JTOOLBAR_SAVE');
		}
		
		if (!$checkedOut && ($canDo->get('core.create')))
		{
			JToolBarHelper::custom('save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
		}
		
		if (!$isNew && ($canDo->get('core.create')))
		{
			JToolbarHelper::save2copy('save2copy');
		}
		
		if (empty($this->item->id))
		{
			JToolBarHelper::cancel('cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			JToolBarHelper::cancel('cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
