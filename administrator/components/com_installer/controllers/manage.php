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
	 * Constructor.
	 *
	 * @param	array An optional associative array of configuration settings.
	 * @see		JController
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->registerTask('unpublish',		'publish');
		$this->registerTask('publish',			'publish');
	}
	/**
	 * Enable/Disable an extension (If supported)
	 */
	public function publish()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));

		// Initialise variables.
		$user	= JFactory::getUser();
		$ids	= JRequest::getVar('cid', array(), '', 'array');
		$values	= array('publish' => 1, 'unpublish' => 0);
		$task	= $this->getTask();
		$value	= JArrayHelper::getValue($values, $task, 0, 'int');

		if (empty($ids)) {
			JError::raiseWarning(500, JText::_('JError_No_items_selected'));
		}
		else
		{
			// Get the model.
			$model	= $this->getModel('manage');

			// Change the state of the records.
			if (!$model->publish($ids, $value)) {
				JError::raiseWarning(500, implode('<br />', $model->getErrors()));
			}
			else
			{
				if ($value == 1) {
					$text = 'JSuccess_N_Items_published';
				}
				else {
					$text = 'JSuccess_N_Items_unpublished';
				}
				$this->setMessage(JText::sprintf($text, count($ids)));
			}
		}

		$this->setRedirect(JRoute::_('index.php?option=com_installer&view=manage',false));
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
		$eid = JRequest::getVar('cid', array(), '', 'array');

		JArrayHelper::toInteger($eid, array());
		$result = $model->remove($eid);
		$this->setRedirect(JRoute::_('index.php?option=com_installer&view=manage',false));
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
		$uid = JRequest::getVar('cid', array(), '', 'array');
		JArrayHelper::toInteger($uid, array());
		$result = $model->refresh($uid);
		$this->setRedirect(JRoute::_('index.php?option=com_installer&view=manage',false));
	}
}
