<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_actionlogs
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\Utilities\ArrayHelper;

JLoader::register('ActionlogsHelper', JPATH_COMPONENT . '/helpers/actionlogs.php');

/**
 * Actionlogs list controller class.
 *
 * @since  __DEPLOY_VERSION__
 */
class ActionlogsControllerActionlogs extends JControllerAdmin
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(array $config = array())
	{
		parent::__construct($config);

		$this->registerTask('exportSelectedLogs', 'exportLogs');
	}

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
	public function getModel($name = 'Actionlogs', $prefix = 'ActionlogsModel', $config = array('ignore_request' => true))
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

		// Get selected logs
		$pks = ArrayHelper::toInteger($this->input->post->get('cid', array(), 'array'));

		// Get the logs data
		$data = $this->getModel()->getLogsData($pks);

		if (count($data))
		{
			$rows = ActionlogsHelper::getCsvData($data);
			$filename     = 'logs_' . JFactory::getDate()->format('Y-m-d_His') . 'UTC';
			$csvDelimiter = ComponentHelper::getComponent('com_actionlogs')->getParams()->get('csv_delimiter', ',');

			$app = JFactory::getApplication();
			$app->setHeader('Content-Type', 'application/csv', true)
				->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '.csv"', true)
				->setHeader('Cache-Control', 'must-revalidate', true)
				->sendHeaders();

			$output = fopen("php://output", "w");

			foreach ($rows as $row)
			{
				fputcsv($output, $row, $csvDelimiter);
			}

			fclose($output);

			$app->close();
		}
		else
		{
			$this->setMessage(JText::_('COM_ACTIONLOGS_NO_LOGS_TO_EXPORT'));
			$this->setRedirect(JRoute::_('index.php?option=com_actionlogs&view=actionlogs', false));
		}
	}

	/**
	 * Clean out the logs
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function purge()
	{
		$model = $this->getModel();

		if ($model->purge())
		{
			$message = JText::_('COM_ACTIONLOGS_PURGE_SUCCESS');
		}
		else
		{
			$message = JText::_('COM_ACTIONLOGS_PURGE_FAIL');
		}

		$this->setRedirect(JRoute::_('index.php?option=com_actionlogs&view=actionlogs', false), $message);
	}
}
