<?php
/**
 * @package		 Joomla.Administrator
 * @subpackage	com_installer
 *
 * @copyright	 Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license		 GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Installer controller for Joomla! installer class.
 *
 * @since	1.5
 */
class InstallerControllerInstaller extends JControllerLegacy
{

	public function __construct(){
		$this->registerTask( 'finalise', 'finalize' );
		parent::__construct();
	}

	/**
	 * We are going to create a standalone installer, then load a dialog
	 * to monitor that installer.
	 *
	 * @return [type] [description]
	 */
	public function install()
	{

		// Check for request forgeries.
			JSession::checkToken('get') or jexit(JText::_('JINVALID_TOKEN'));

		// Stage
			$app = JFactory::getApplication();
			$model = $this->getModel('installer');
			$view = $this->getView('installer', 'html');
			$queue = $app->getUserState('com_installer.queue');
			$package = $app->getUserState('com_installer.installer.package');

		// If Queue, let's get a pending package
			if ($queue)
			{

				// We loop the queue looking for pending items
					$package = null;
					for ($i=0; $i<count($queue); $i++)
					{
						$queueItem =& $queue[$i];
						if (in_array($queueItem['status'], array('pending', 'active')))
						{
							if (empty($queueItem['package']))
							{
								$installModel = $this->getModel('install');
								$modelInput = new JInput();
								$modelInput->set('installtype', 'update');
								$modelInput->set('update_id', $queueItem['update_id']);
								if (!$installModel->initialize( $input ))
								{
									$app->enqueueMessage(JText::_('COM_INSTALLER_UNABLE_TO_INITIALIZE_INSTALLER'), 'error');
									$this->setRedirect(JRoute::_('index.php?option=com_installer', false));
									return false;
								}
								$queueItem['package'] = $installModel->getState('package');
							}
							if (!empty($queueItem['package']))
							{
								$package = $queueItem['package'];
								$queueItem['status'] = 'active';
							}
							if ($package)
							{
								break;
							}
						}
					}

				// Update the queue to prevent duplicate work
					$app->setUserState('com_installer.queue', $queue);

				// No Packages? We must be finished
					if (empty($package))
					{
						$this->setRedirect(JRoute::_('index.php?option=com_installer&task=installer.finalize', false));
						return false;
					}

			}

		// Verify Package
			if (empty($package) || empty($package['type']))
			{
				$app->enqueueMessage(JText::_('COM_INSTALLER_UNABLE_TO_FIND_INSTALL_PACKAGE'), 'error');
				$this->setRedirect(JRoute::_('index.php?option=com_installer', false));
				return false;
			}

		// Initialize Installer
			if (!$model->initialize( array('package' => $package) ))
			{
				$app->enqueueMessage(JText::_('COM_INSTALLER_UNABLE_TO_INITIALIZE_INSTALLER'), 'error');
				$this->setRedirect(JRoute::_('index.php?option=com_installer', false));
				return false;
			}

		// Stage to View
			$view->setModel($model, true);

		// Trigger View
			$view->display();

	}

	/**
	 * We are going to create a queue for the batch install request, then forward
	 * to the queue installer.
	 *
	 * @return [type] [description]
	 */
	public function batchInstall()
	{

		// Check for request forgeries.
			JSession::checkToken('get') or jexit(JText::_('JINVALID_TOKEN'));

		// Stage
			$app = JFactory::getApplication();
			$model = $this->getModel('installer');
			$view = $this->getView('installer', 'html');
			$uid = array_filter($app->input->get('uid', array()), 'intval');
			$minimum_stability = $app->input->getInt('minimum_stability', JUpdater::STABILITY_STABLE);

		// Reset
			$model->reset();

		// Validate Batch
			if (!$uid)
			{
				$app->enqueueMessage(JText::_('COM_INSTALLER_UNABLE_TO_INITIALIZE_INSTALLER'), 'error');
				$this->setRedirect(JRoute::_('index.php?option=com_installer', false));
				return false;
			}

		// Prepare Batch Queue
			$queue = array();
			foreach ($uid AS $id)
			{
				$queue[] = array(
					'update_id' => $id,
					'status'		=> 'pending',
					'result'		=> null,
					'message'	 => null,
					'package'	 => null
					);
			}
			JFactory::getApplication()->setUserState('com_installer.queue', $queue);
			$this->setRedirect(JRoute::_('index.php?' . http_build_query(array(
				'option'								 => 'com_installer',
				'task'									 => 'installer.install',
				JSession::getFormToken() => '1'
				)), false));
			return true;

	}

	/**
	 * The package installer has finished and we are now going to cleanup.
	 * NO package specific post-processing can be performed here.
	 * If a queue remains we will direct back to the installer.
	 *
	 * This operation requires a successful return to the previous session,
	 * which could be broken by the update.
	 *
	 * @return [type] [description]
	 */
	public function finalize()
	{

		// Stage
			$app		 = JFactory::getApplication();
			$model	 = $this->getModel('installer');
			$success = $app->input->getInt('success') ? 1 : 0;
			$message = $app->input->getVar('message');
			$queue	 = $app->getUserState('com_installer.queue');
			$package = $app->getUserState('com_installer.installer.package');

		// Finalize Model
			$model->finalize( array(
				'success' => $success,
				'message' => $message
				) );

		// Update Queue
			$queue_pending = 0;
			if ($queue)
			{

				// Find Active Queue
					for ($i=0; $i<count($queue); $i++)
					{
						$queueItem =& $queue[$i];
						if ($queueItem['status'] == 'active')
						{
							$queueItem['status']	= 'complete';
							$queueItem['result']	= ($success ? 'success' : 'error');
							$queueItem['message'] = $message;
						}
						if ($queueItem['status'] == 'pending')
						{
							$queue_pending++;
						}
					}

				// Update the queue to prevent duplicate work
					$app->setUserState('com_installer.queue', $queue);

			}

		// Failed
			if (!$success)
			{
				$app->enqueueMessage(sprintf(JText::_('COM_INSTALLER_INSTALL_ERROR'), $message), 'error');
				$this->setRedirect(JRoute::_('index.php?option=com_installer', false));
				return false;
			}

		// Package Name
			$package_name = ucfirst($package['type']);

		// Success, Queue Pending
			if ($queue_pending)
			{
				$this->setRedirect(JRoute::_('index.php?' . http_build_query(array(
					'option'								 => 'com_installer',
					'task'									 => 'installer.install',
					JSession::getFormToken() => '1'
					)), false));
				return true;
			}

		// Success, Complete
			$app->enqueueMessage(sprintf(JText::_('COM_INSTALLER_INSTALL_SUCCESS'), $package_name), 'message');
			$this->setRedirect(JRoute::_('index.php?option=com_installer', false));
			return true;

	}

}
