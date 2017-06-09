<?php
/**
 * @package     Joomla.Installation
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
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
	 * @return  string  The rendered view.
	 *
	 * @since   3.1
	 */
	public function execute()
	{
		// Get the application
		/* @var InstallationApplicationWeb $app */
		$app = $this->getApplication();

		// Get the document object.
		$document = $app->getDocument();

		// Set the default view name and format from the request.
		if (file_exists(JPATH_CONFIGURATION . '/configuration.php') && (filesize(JPATH_CONFIGURATION . '/configuration.php') > 10)
			&& file_exists(JPATH_INSTALLATION . '/index.php'))
		{
			$defaultView = 'remove';
		}
		else
		{
			$defaultView = 'site';
		}

		$vName   = $this->input->getWord('view', $defaultView);
		$vFormat = $document->getType();
		$lName   = $this->input->getWord('layout', 'default');

		if (strcmp($vName, $defaultView) == 0)
		{
			$this->input->set('view', $defaultView);
		}

		switch ($vName)
		{
			case 'preinstall':
				$model        = new InstallationModelSetup;
				$sufficient   = $model->getPhpOptionsSufficient();
				$checkOptions = false;
				$options      = $model->getOptions();

				if ($sufficient)
				{
					$app->redirect('index.php');
				}

				break;

			case 'languages':
			case 'defaultlanguage':
				$model        = new InstallationModelLanguages;
				$checkOptions = false;
				$options      = array();

				break;

			default:
				$model        = new InstallationModelSetup;
				$sufficient   = $model->getPhpOptionsSufficient();
				$checkOptions = true;
				$options      = $model->getOptions();

				if (!$sufficient)
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

		/* @var JViewHtml $view */
		$view = new $vClass($model, $paths);
		$view->setLayout($lName);

		// Render our view and return it to the application.
		return $view->render();
	}
}
