<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Base controller class for the Joomla Core Installer.
 *
 * @package  Joomla.Installation
 * @since    1.6
 */
class InstallationController extends JControllerLegacy
{
	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JController  This object to support chaining.
	 *
	 * @since   1.5
	 */
	public function display($cachable = false, $urlparams = array())
	{
		// Get the current URI to redirect to.
		$uri      = JUri::getInstance();
		$redirect = base64_encode($uri);

		// Get the document object.
		$document = JFactory::getDocument();

		// Set the default view name and format from the Request.
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

		$view = $this->getView($vName, $vFormat);

		if ($view)
		{
			$checkOptions = null;
			switch ($vName)
			{
				case 'preinstall':
					$model        = $this->getModel('Setup', 'InstallationModel', array('dbo' => null));
					$sufficient   = $model->getPhpOptionsSufficient();
					$checkOptions = false;

					if ($sufficient)
					{
						$this->setRedirect('index.php');
					}
					break;

				case 'languages':
				case 'defaultlanguage':
					$model = $this->getModel('Languages', 'InstallationModel', array('dbo' => null));
					break;

				default:
					$model        = $this->getModel('Setup', 'InstallationModel', array('dbo' => null));
					$sufficient   = $model->getPhpOptionsSufficient();
					$checkOptions = true;
					if (!$sufficient)
					{
						$this->setRedirect('index.php?view=preinstall');
					}
					break;
			}

			$options = $model->getOptions();

			if ($vName != $default_view && ($checkOptions && empty($options)))
			{
				$this->setRedirect('index.php');
			}

			// Push the model into the view (as default).
			$view->setModel($model, true);
			$view->setLayout($lName);

			// Push document object into the view.
			$view->document = $document;

			// Include the component HTML helpers.
			JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

			$view->display();
		}

		return $this;
	}
}
