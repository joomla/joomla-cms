<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Joomla.Libraries
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Base Display Controller
 *
 * @package     Joomla.Libraries
 * @subpackage  controller
 * @since       3.2
*/
class JControllerDisplayform extends JControllerDisplay
{

	protected $item;

	protected $form;


	/**
	 * @return  mixed  A rendered view or true
	 *
	 * @since   3.2
	 */
	public function execute()
	{
		// Get the application
		$app = $this->getApplication();
		$app->redirect('index.php?option=' . $this->input->get('option', 'com_cpanel') . '&view=' . $this->option .'&layout=edit');

		// Get the document object.
		$document     = JFactory::getDocument();

		$componentFolder = $this->input->getWord('option', 'com_content');
		if (empty($this->option))
		{
			$viewName     = $this->input->getWord('view', 'articles');
		}
		else
		{
			$viewName = $this->option;
		}
		$viewFormat   = $document->getType();
		$layoutName   = $this->input->getWord('layout', 'edit');

		$paths = $this->registerPaths($componentFolder, $this->option);

		$viewClass  = $this->prefix . 'View' . ucfirst($viewName) . ucfirst($viewFormat);
		$modelClass = $this->prefix . 'Model' . ucfirst($viewName);

		if (class_exists($viewClass))
		{
			$model = new $modelClass;

			// Access check.
			if (!JFactory::getUser()->authorise('core.edit', $model->getState('component.option')))
			{
				$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');

				return;

			}

			$view = new $viewClass($model, $paths);

			$view->setLayout($layoutName);

			$context = $componentFolder . 'edit' . $viewName;
			$this->editCheck($app, $context);

			// Push document object into the view.
			$view->document = $document;

			// Reply for service requests
			if ($viewFormat == 'json')
			{

				return $view->render();
			}

			// Render view.
			echo $view->render();
		}

		return true;
	}

	/*
	 * Method to check if the user has permission to edit this item
	 *
	 * @param   JApplication  $app  The aplication
	 *
	 * @return  boolean
	 *
	 * @since 3.2
	 */
	protected function editCheck($app, $context)
	{
		$id     = $this->input->getInt('id', 0);
		// Check for edit form.
		if (!$this->checkEditId($context, $id))
		{
			// Somehow the person just went to the form - we don't allow that.
			$app->enqueueMessage((JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id)), 'error');
			$app->setRedirect(JRoute::_('index.php?option=com_cpanel', false));

			return false;
		}

		return true;
	}

	/**
	 * Method to check whether an ID is in the edit list.
	 *
	 * @param   string   $context  The context for the session storage.
	 * @param   integer  $id       The ID of the record to add to the edit list.
	 *
	 * @return  boolean  True if the ID is in the edit list.
	 *
	 * @since   12.2
	 */
	protected function checkEditId($context, $id)
	{
		if ($id)
		{
			$app = JFactory::getApplication();
			$values = (array) $app->getUserState($context . '.id');

			$result = in_array((int) $id, $values);

			if (defined('JDEBUG') && JDEBUG)
			{
				JLog::add(
				sprintf(
				'Checking edit ID %s.%s: %d %s',
				$context,
				$id,
				(int) $result,
				str_replace("\n", ' ', print_r($values, 1))
				),
				JLog::INFO,
				'controller'
						);
			}

			return $result;
		}
		else
		{
			// No id for a new item.
			return true;
		}
	}

	/*
	 * Method to register paths for the layouts
	 *
	 * @param   string  $componentFolder  Folder name for the paths, defauts to the request option.
	 * @param   string  $viewName         Folder containing the view.
	 *
	 * @return  SplPriorityQueue  Priority queue of paths to search for layouts
	 *
	 * @since  3.2
	 */
	public function registerPaths($componentFolder, $viewName)
	{
		// Register the layout paths for the view
		$paths = new SplPriorityQueue;
		$paths->insert(JPATH_ADMINISTRATOR . '/components/' . $componentFolder . '/view/' . $viewName . '/tmpl', 'normal');

		return $paths;
	}
}