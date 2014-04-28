<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  View
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

class JViewAdmin extends JViewCms
{
	//Edit Properties
	protected $item;
	protected $form;

	// List Properties
	protected $items;
	protected $pagination;
	protected $sidebar;

	//Shared Properties
	protected $keyName;
	protected $formUrl;
	protected $editUrl;
	protected $state;


	public function render($tpl = null)
	{
		//get the unit count for each course
		$model = $this->getModel();
		$this->state = $model->getState();
		$config = $this->config;

		$this->keyName = $model->getKeyName();
		
		$this->formUrl = 'index.php?option='.$config['option'];
		$this->editUrl = $this->formUrl.'&task=edit.'.$config['subject'].'&'.$this->keyName.'=';

		if ($config['layout'] == 'edit')
		{
			JFactory::getApplication()->input->set('hidemainmenu', true);
			// load standard properties
			$this->item	= $model->getItem();
			$this->form	= $model->getForm();
		}
		else
		{
			// load standard properties
			$this->items = $model->getItems();
			$this->pagination = $model->getPagination();

			$prefix = substr($config['option'], 4);
			$sideBarHelper = ucfirst($prefix).'Helper';
			if (class_exists($sideBarHelper))
			{
				$sideBarHelper::addSubmenu($config['subject']);
				$this->addFilters();
				$this->sidebar = JHtmlSidebar::render();
			}
		}

		
		

		// load toolbar
		$this->addToolbar();

		return parent::render($tpl);
	}
	
	protected function addToolbar()
	{
		$config = $this->config;
		
		$title = strtoupper($config['option'].'_header_'.$config['subject'].'_'.$config['layout']);
		$icon =  strtolower(substr($config['option'], 4)).'.png';
		
		JToolbarHelper::title($title, $icon);
	
	}
	
	protected function addFilters()
	{
	
	}
	
	protected function getSortFields()
	{
		return array();
	}
}