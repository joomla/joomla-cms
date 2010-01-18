<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// No direct access
defined('_JEXEC') or die;

class InstallerControllerManage extends JController {

	/**
	 * Enable an extension (If supported)
	 *
	 * @return	void
	 * @since	1.5
	 */
	public function enable()
	{
		// Check for request forgeries
		JRequest::checkToken('request') or jexit(JText::_('JInvalid_Token'));
		$model	= &$this->getModel('manage');

		$eid = JRequest::getVar('eid', array(), '', 'array');
		JArrayHelper::toInteger($eid, array());
		$model->enable($eid);
		$this->setRedirect('index.php?option=com_installer&view=manage');
	}

	/**
	 * Disable an extension (If supported)
	 *
	 * @return	void
	 * @since	1.5
	 */
	public function disable()
	{
		// Check for request forgeries
		JRequest::checkToken('request') or jexit(JText::_('JInvalid_Token'));
		$model	= &$this->getModel('manage');
		$eid = JRequest::getVar('eid', array(), '', 'array');
		JArrayHelper::toInteger($eid, array());
		$model->disable($eid);
		$this->setRedirect('index.php?option=com_installer&view=manage');
	}

	/**
	 * Remove an extension (Uninstall)
	 *
	 * @return	void
	 * @since	1.5
	 */
	public function remove()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));

		$model	= &$this->getModel('manage');
		$eid = JRequest::getVar('eid', array(), '', 'array');

		JArrayHelper::toInteger($eid, array());
		$result = $model->remove($eid);
		$model->saveState(); // Save the state because this is where our messages are stored
		$this->setRedirect('index.php?option=com_installer&view=manage');
	}

	/**
	 * Refreshes the cached metadata about an extension
	 * Useful for debugging and testing purposes when the XML file might change
	 */
	function refresh()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));

		$model	= &$this->getModel('manage');
		$uid = JRequest::getVar('eid', array(), '', 'array');
		JArrayHelper::toInteger($uid, array());
		$result = $model->refresh($uid);
		$this->setRedirect('index.php?option=com_installer&view=manage');
	}
}