<?php
/**
 * @package     Joomla.Installation
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Installation\Controller;

defined('_JEXEC') or die;

use JHtml,
	JForm,
	JControllerBase;
use Installation\Model\SetupModel,
	Installation\Model\LanguagesModel;

/**
 * Default controller class for the Joomla Installer.
 *
 * @package     Joomla.Installation
 * @subpackage  Controller
 * @since       3.1
 */
class DefaultController extends JControllerBase
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
		/* @var \Installation\Application\WebApplication $app */
		$app = $this->getApplication();

		// Get the document object.
		$document = $app->getDocument();

		// Set the default view name and format from the request.
		if (file_exists(JPATH_CONFIGURATION . '/configuration.php') && (filesize(JPATH_CONFIGURATION . '/configuration.php') > 10)
			&& file_exists(JPATH_INSTALLATION . '/index.php'))
		{
			$default_view = 'remove';
		}
		else
		{
			$default_view = 'site';
		}

		$vName   = $this->input->getWord('view', $default_view);
		$vFormat = $document->getType();
		$lName   = $this->input->getWord('layout', 'default');

		if (strcmp($vName, $default_view) == 0)
		{
			$this->input->set('view', $default_view);
		}

		switch ($vName)
		{
			case 'preinstall':
				$model        = new SetupModel;
				$sufficient   = $model->getPhpOptionsSufficient();
				$checkOptions = false;
				$options = $model->getOptions();

				if ($sufficient)
				{
					$app->redirect('index.php');
				}

				break;

			case 'languages':
			case 'defaultlanguage':
				$model = new LanguagesModel;
				$checkOptions = false;
				$options = array();
				break;

			default:
				$model        = new SetupModel;
				$sufficient   = $model->getPhpOptionsSufficient();
				$checkOptions = true;
				$options = $model->getOptions();

				if (!$sufficient)
				{
					$app->redirect('index.php?view=preinstall');
				}

				break;
		}

		if ($vName != $default_view && ($checkOptions && empty($options)))
		{
			$this->setRedirect('index.php');
		}

		// Register the layout paths for the view
		$paths = new \SplPriorityQueue;
		$paths->insert(JPATH_INSTALLATION . '/src/Installation/View/' . ucfirst($vName) . '/tmpl', 'normal');

		$vClass = 'Installation\\View\\' . ucfirst($vName) . '\\' . ucfirst($vFormat);

		if (!class_exists($vClass))
		{
			$vClass = 'Installation\\View\\DefaultView';
		}

		/* @var \JViewHtml $view */
		$view = new $vClass($model, $paths);
		$view->setLayout($lName);

		// Render our view and return it to the application.
		return $view->render();
	}
}
