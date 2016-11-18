<?php
/**
 * @package     Joomla.Installation
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Default controller class for the Joomla Installer.
 *
 * @since  3.1
 */
class InstallationControllerDefault extends JControllerBase
{
	/**
	 * Execute the controller.
	 *
	 * @return  boolean  True if controller finished execution.
	 *
	 * @since   3.1
	 */
	public function execute()
	{
		// Get the application
		/** @var InstallationApplicationWeb $app */
		$app = $this->getApplication();

		$defaultView = 'site';

		// If the app has already been installed, default to the remove view
		if (file_exists(JPATH_CONFIGURATION . '/configuration.php') && (filesize(JPATH_CONFIGURATION . '/configuration.php') > 10)
			&& file_exists(JPATH_INSTALLATION . '/index.php'))
		{
			$defaultView = 'remove';
		}

		$vName   = $this->input->getWord('view', $defaultView);
		$vFormat = $app->getDocument()->getType();
		$lName   = $this->input->getWord('layout', 'default');

		if (strcmp($vName, $defaultView) == 0)
		{
			$this->input->set('view', $defaultView);
		}

		switch ($vName)
		{
			case 'preinstall':
				$model        = new InstallationModelSetup;
				$checkOptions = false;
				$options      = $model->getOptions();

				if ($model->getPhpOptionsSufficient())
				{
					$app->redirect('index.php');
				}

				break;

			case 'languages':
			case 'defaultlanguage':
				$model        = new InstallationModelLanguages;
				$checkOptions = false;
				$options      = [];

				break;

			default:
				$model        = new InstallationModelSetup;
				$checkOptions = true;
				$options      = $model->getOptions();

				if (!$model->getPhpOptionsSufficient())
				{
					$app->redirect('index.php?view=preinstall');
				}

				break;
		}

		if ($vName != $defaultView && ($checkOptions && empty($options)))
		{
			$app->redirect('index.php');
		}

		// Register the layout paths for the view
		$paths = new SplPriorityQueue;
		$paths->insert(JPATH_INSTALLATION . '/view/' . $vName . '/tmpl', 'normal');

		$vClass = 'InstallationView' . ucfirst($vName) . ucfirst($vFormat);

		if (!class_exists($vClass))
		{
			$vClass = 'InstallationViewDefault';
		}

		/** @var JViewHtml $view */
		$view = new $vClass($model, $paths);
		$view->setLayout($lName);

		// Render our view and set it to the document.
		$app->getDocument()->setBuffer($view->render(), 'component');

		return true;
	}
}
