<?php
/**
 * @version		$Id: view.html.php 20196 2011-01-09 02:40:25Z ian $
 * @package		Joomla.Administrator
 * @subpackage	com_config
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'component.php');
/**
 * @package		Joomla.Administrator
 * @subpackage	com_config
 */
class Aa4jViewComponent extends JView
{
	/**
	 * Display the view
	 */
	function display($tpl = null)
	{
		 $model = $this->getModel('component');
		 $userdata=$model->getComponent();
		 $option =JRequest::getCmd('component', 'all');
	//	 var_dump('usrd:'.$option);
		/*
		//$form		= $this->get('Form');
		$component	= $this->get('Component');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		// Bind the form to the data.
		if ($form && $component->params) {
			$form->bind($component->params);
		}

		$this->assignRef('form',		$form);
	
		$this->assignRef('component',	$component);
    */
    $this->state		= $this->get('State');
    $this->assignRef('option',		$option);
    $this->assignRef('userdata',		$userdata);
		$this->document->setTitle(JText::_('JGLOBAL_EDIT_PREFERENCES'));
    JToolBarHelper::title(JText::_('COM_AA4J_VIEW_GEO_TITLE'), 'config.png');
    JSubMenuHelper::addEntry(
			JText::_('COM_AA4J_CONTROL_PANEL'),
			'index.php?option=com_aa4j&view=application'
		);
			JSubMenuHelper::addEntry(
			JText::_('COM_AA4J_VIEW_USERS_TITLE'),
			'index.php?option=com_aa4j&view=users',
			'ausers'
		);
		parent::display($tpl);
		JRequest::setVar('hidemainmenu', false);
	}
}