<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Installer Manage Controller
 *
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 * @since       1.6
 */
class InstallerControllerManage extends JControllerLegacy
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JController
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->registerTask('unpublish', 'publish');
		$this->registerTask('publish',   'publish');
	}

	/**
	 * Enable/Disable an extension (if supported).
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function publish()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$ids    = $this->input->get('cid', array(), 'array');
		$values = array('publish' => 1, 'unpublish' => 0);
		$task   = $this->getTask();
		$value  = JArrayHelper::getValue($values, $task, 0, 'int');

		if (empty($ids))
		{
			JError::raiseWarning(500, JText::_('COM_INSTALLER_ERROR_NO_EXTENSIONS_SELECTED'));
		}
		else
		{
			// Get the model.
			$model	= $this->getModel('manage');

			// Change the state of the records.
			if (!$model->publish($ids, $value))
			{
				JError::raiseWarning(500, implode('<br />', $model->getErrors()));
			}
			else
			{
				if ($value == 1)
				{
					$ntext = 'COM_INSTALLER_N_EXTENSIONS_PUBLISHED';
				}
				elseif ($value == 0)
				{
					$ntext = 'COM_INSTALLER_N_EXTENSIONS_UNPUBLISHED';
				}
				$this->setMessage(JText::plural($ntext, count($ids)));
			}
		}

		$this->setRedirect(JRoute::_('index.php?option=com_installer&view=manage', false));
	}

	/**
	 * Remove an extension (Uninstall).
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public function remove()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$eid   = $this->input->get('cid', array(), 'array');
		$model = $this->getModel('manage');

		JArrayHelper::toInteger($eid, array());
		$model->remove($eid);
		$this->setRedirect(JRoute::_('index.php?option=com_installer&view=manage', false));
	}

	/**
	 * Refreshes the cached metadata about an extension.
	 *
	 * Useful for debugging and testing purposes when the XML file might change.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function refresh()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$uid   = $this->input->get('cid', array(), 'array');
		$model = $this->getModel('manage');

		JArrayHelper::toInteger($uid, array());
		$model->refresh($uid);
		$this->setRedirect(JRoute::_('index.php?option=com_installer&view=manage', false));
	}
}
