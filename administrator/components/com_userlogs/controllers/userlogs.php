<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_userlogs
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
JLoader::register('UserlogsHelper', JPATH_COMPONENT . '/helpers/userlogs.php');

use Joomla\Utilities\ArrayHelper;

/**
 * Userlogs list controller class.
 *
 * @since  __DEPLOY_VERSION__
 */
class UserlogsControllerUserlogs extends JControllerAdmin
{
	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  object  The model.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getModel($name = 'Userlogs', $prefix = 'UserlogsModel',
		$config = array('ignore_request' => true))
	{

		// Return the model
		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * Method to export logs
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function exportLogs()
	{
		// Check for request forgeries.
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		// Get the logs data
		$data = $this->getModel('userlogs')->getLogsData();

		// Export data to CSV file
		UserlogsHelper::dataToCsv($data);
	}

	/**
	 * Method to delete logs
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function delete()
	{
		if (!JFactory::getUser()->authorise('core.delete', $this->option))
		{
			JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'));

			return;
		}

		parent::delete();
	}

	/**
	 * Method to export selected logs
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function exportSelectedLogs()
	{
		// Check for request forgeries.
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		// Get selected logs
		$pks = ArrayHelper::toInteger($this->input->post->get('cid', array(), 'array'));

		// Get the logs data
		$data = $this->getModel('userlogs')->getLogsData($pks);

		// Export data to CSV file
		UserlogsHelper::dataToCsv($data);
	}
}
