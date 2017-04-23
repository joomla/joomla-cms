<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Display Controller for module editing
 *
 * @package     Joomla.Site
 * @subpackage  com_config
 * @since       3.2
*/
class ConfigControllerModulesDisplay extends ConfigControllerDisplay
{
	/**
	 * Method to display module editing.
	 *
	 * @return  bool	True on success, false on failure.
	 *
	 * @since   3.2
	 */
	public function execute()
	{
		// Get the application
		$app = $this->getApplication();

		// Get the document object.
		$document     = JFactory::getDocument();

		$viewName     = 'modules';
		$viewFormat   = $document->getType();
		$layoutName   = $this->input->getWord('layout', 'default');
		$returnUri    = $this->input->get->get('return', null, 'base64');

		// Construct redirect URI
		if (!empty($returnUri))
		{
			$redirect = base64_decode(urldecode($returnUri));

			// Don't redirect to an external URL.
			if (!JUri::isInternal($redirect))
			{
				$redirect = JUri::base();
			}
		}
		else
		{
			$redirect = JUri::base();
		}

		JLoader::register('ModulesModelModule', JPATH_ADMINISTRATOR . '/components/com_modules/models/module.php');

		$moduleData = (new ModulesModelModule)->getItem($this->input->getInt('id'));

		if (!$moduleData)
		{
			$app->redirect($redirect);
		}

		$serviceData = $moduleData->getProperties();
		unset($serviceData['xml']);

		// Register the layout paths for the view
		$paths = new SplPriorityQueue;
		$paths->insert(JPATH_COMPONENT . '/view/' . $viewName . '/tmpl', 'normal');

		$viewClass  = 'ConfigView' . ucfirst($viewName) . ucfirst($viewFormat);
		$modelClass = 'ConfigModel' . ucfirst($viewName);

		if (class_exists($viewClass))
		{

			$model = new $modelClass;

			// Access check.
			$user = JFactory::getUser();

			if (!$user->authorise('module.edit.frontend', 'com_modules.module.' . $serviceData['id'])
				&& !$user->authorise('module.edit.frontend', 'com_modules'))
			{
				$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
				$app->redirect($redirect);

			}

			// Need to add module name to the state of model
			$model->getState()->set('module.name', $serviceData['module']);

			$view = new $viewClass($model, $paths);

			$view->setLayout($layoutName);

			// Push document object into the view.
			$view->document = $document;

			// Load form and bind data
			$form = $model->getForm();

			if ($form)
			{
				$form->bind($serviceData);
			}

			// Set form and data to the view
			$view->form = &$form;
			$view->item = &$serviceData;

			// Render view.
			echo $view->render();
		}

		return true;
	}
}
