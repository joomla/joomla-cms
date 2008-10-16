<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_acl
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

// Helper functions

function aclObjectChecked(&$array, $section, $value)
{
	$values	= @$array[$section];
	return in_array($value, (array) $values) ? 'checked="checked"' : '';
}

function aclGroupChecked(&$array, $value)
{
	return in_array($value, (array) $array) ? 'checked="checked"' : '';
}

/**
 * @package		Joomla.Administrator
 * @subpackage	com_acl
 */
class AccessViewRule extends JView
{
	/**
	 * Display the view
	 *
	 * @access	public
	 */
	function display($tpl = null)
	{
		$state		= $this->get('State');
		$item		= $this->get('ExtendedItem');
		$acl		= $this->get('ACL');
		$aclType	= $state->get('acl_type', 1);

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		//$layout = $this->getLayout();
		if ($state->get('id')) {
			// Existing
		}
		else {
			// New
			$item->section_value	= 'user';
			$item->enabled			= 1;
			$item->allow			= 1;
			$item->acl_type			= $aclType;
		}

		$this->assignRef('state',		$state);
		$this->assignRef('item',		$item);
		$this->assignRef('acl',			$acl);
		$this->assignRef('acos',		$this->get('ACOs'));
		$this->assignRef('aroGroups',	$this->get('AROGroups'));
		if ($aclType == 2) {
			$this->assignRef('axos', $this->get('AXOs'));
		}
		else if ($aclType == 3) {
			$this->assignRef('axoGroups', $this->get('AXOGroups'));
		}

		$this->_setToolBar($aclType);
		parent::display($tpl);
		JRequest::setVar('hidemainmenu',1);
	}

	/**
	 * Display the toolbar
	 *
	 * @access	private
	 */
	function _setToolBar($type)
	{
		$title = empty($this->item->id) ? 'Edit Rule' : 'Add Rule';
		JToolBarHelper::title(JText::_('Access Control: '.$title.' Type '.(int) $type));
		JToolBarHelper::custom('acl.save2new', 'new.png', 'new_f2.png', 'Toolbar Save And New', false,  false);
		JToolBarHelper::save('acl.save');
		JToolBarHelper::apply('acl.apply');
		JToolBarHelper::cancel('acl.cancel');
	}
}