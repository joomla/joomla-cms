<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Installer Update Sites Controller
 *
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 * @since       3.4
 */
class InstallerControllerUpdatesites extends JControllerLegacy
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JController
	 * @since   3.4
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
	 * @since   3.4
	 *
	 * @throws  Exception on error
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
			throw new Exception(JText::_('COM_INSTALLER_ERROR_NO_UPDATESITES_SELECTED'), 500);
		}

		// Get the model.
		$model = $this->getModel('Updatesites');

		// Change the state of the records.
		if (!$model->publish($ids, $value))
		{
			throw new Exception(implode('<br />', $model->getErrors()), 500);
		}

		$ntext = ($value == 0) ? 'COM_INSTALLER_N_UPDATESITES_UNPUBLISHED' : 'COM_INSTALLER_N_UPDATESITES_PUBLISHED';

		$this->setMessage(JText::plural($ntext, count($ids)));

		$this->setRedirect(JRoute::_('index.php?option=com_installer&view=updatesites', false));
	}
}
