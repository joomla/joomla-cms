<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_actionlogs
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\Utilities\ArrayHelper;

JLoader::register('ActionlogsHelper', JPATH_ADMINISTRATOR . '/components/com_actionlogs/helpers/actionlogs.php');

/**
 * Actionlogs list controller class.
 *
 * @since  3.9.0
 */
class ActionlogsControllerActionlogs extends JControllerAdmin
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   3.9.0
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
	 * @since   3.9.0
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
	 * @since   3.9.0
	 */
	public function exportLogs()
	{
		// Check for request forgeries.
		$this->checkToken();

		$task = $this->getTask();

		$pks = array();

		if ($task == 'exportSelectedLogs')
		{
			// Get selected logs
			$pks = ArrayHelper::toInteger(explode(',', $this->input->post->getString('cids')));
		}

		/** @var ActionlogsModelActionlogs $model */
		$model = $this->getModel();

		// Get the logs data
		$data = $model->getLogDataAsIterator($pks);

		if (count($data))
		{

			try
			{
				$rows = ActionlogsHelper::getCsvData($data);
			}
			catch (InvalidArgumentException $exception)
			{
				$this->setMessage(JText::_('COM_ACTIONLOGS_ERROR_COULD_NOT_EXPORT_DATA'), 'error');
				$this->setRedirect(JRoute::_('index.php?option=com_actionlogs&view=actionlogs', false));

				return;
			}

			// Destroy the iterator now
			unset($data);

			$date     = new JDate('now', new DateTimeZone('UTC'));
			$filename = 'logs_' . $date->format('Y-m-d_His_T');

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
	 * @since   3.9.0
	 */
	public function purge()
	{
		// Check for request forgeries.
		$this->checkToken();

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
