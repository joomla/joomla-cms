<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_acl
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * @package		Joomla.Administrator
 * @subpackage	com_acl
 */
class AccessViewRule extends JView
{
	public $item;
	public $acos;
	public $aroGroups;
	public $axos;
	public $axoGroups;

	/**
	 * Display the view
	 *
	 * @access	public
	 */
	function display($tpl = null)
	{
		$state		= $this->get('State');
		$item		= $this->get('Item');

		$acos		= $this->get('ACOs');
		$aroGroups	= $this->get('AROGroups');
		$axos		= $this->get('AXOs');
		$axoGroups	= $this->get('AXOGroups');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->assignRef('state',		$state);
		$this->assignRef('item',		$item);
		$this->assignRef('acos',		$acos);
		$this->assignRef('aroGroups',	$aroGroups);
		$this->assignRef('axos',		$axos);
		$this->assignRef('axoGroups',	$axoGroups);

		$this->_setToolBar($item->acl_type);
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