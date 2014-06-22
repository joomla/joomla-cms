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
class JControllerDisplay extends JControllerCmsbase
{
	/*
	 * Prefix for the view and model classes
	 *
	 * @var  string
	 */
	public $prefix = 'Content';

	/*
	 * @var boolean  If true, the view output will be cached
	 */

	public $cacheable = false;

	/*
	 * An array of safe url parameters and their variable types
	 *
	 * @var  array
	 * @note  For valid values see JFilterInput::clean().
	 */
	public $urlparams = array();

	/**
	 * The view to display
	 *
	 * @var  JViewCms
	 */
	protected $view;

	/**
	 * Execute the controller.
	 *
	 * @return  boolean  True if controller finished execution, false if the controller did not
	 *                   finish execution. A controller might return false if some precondition for
	 *                   the controller to run has not been satisfied.
	 *
	 * @since   3.4
	 * @throws  RuntimeException
	 */
	public function execute()
	{
		!$this->app->isAdmin() ? : $this->permission = 'core.manage';

		$componentFolder = $this->input->getWord('option', 'com_content');
		$this->viewName     = $this->input->getWord('view', 'articles');
		$viewFormat   = JFactory::getDocument()->getType();

		try
		{
			$model = $this->getModel();
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}

		// Access check.
		if (!empty($this->permission) && !JFactory::getUser()->authorise($this->permission, $model->getState('component.option')))
		{
			$this->app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');

			return false;
		}

		// Initialise the view class.
		$view = $this->getView($model, $this->prefix, $this->viewName, $viewFormat);

		// If in html view then we set the layout
		if ($viewFormat == 'html')
		{
			$layoutName   = $this->input->getWord('layout', 'default');
			$view->setLayout($layoutName);
		}

		// Reply for service requests
		// @todo this shouldn't happen - we need to fix this.
		if ($viewFormat == 'json')
		{
			return $view->render();
		}

		// Render view.
		echo $view->render();

		return true;
	}

	/**
	 * Method to get a view, initiating it if it does not already exist.
	 * This method assumes auto-loading format is $prefix . 'View' . $name . $type
	 * The
	 *
	 * @param   JModelCmsInterface  $model   The model to be injected
	 * @param   string              $prefix  Option prefix exp. com_content
	 * @param   string              $name    Name of the view folder exp. articles
	 * @param   string              $type    Name of the file exp. html = html.php
	 * @param   array               $config  An array of config options
	 *
	 * @throws  RuntimeException
	 * @return  JViewCms
	 */
	protected function getView(JModelCmsInterface $model, $prefix = null, $name = null, $type = null, $config = array())
	{
		// Get the prefix if not given
		if (is_null($prefix))
		{
			$prefix = $this->getPrefix();
		}

		// Get the name if not given
		if (is_null($name))
		{
			$name = $this->config['subject'];
		}

		// Get the document type
		if (is_null($name))
		{
			$type   = JFactory::getDocument()->getType();
		}

		$class = ucfirst($prefix) . 'View' . ucfirst($name) . ucfirst($type);

		if ($this->view instanceof $class)
		{
			return $this->view;
		}

		// If a custom class doesn't exist fall back to the Joomla class if it exists
		if (!class_exists($class))
		{
			$joomlaClass = 'JView' . ucfirst($type);

			if (!class_exists($joomlaClass))
			{
				// @todo convert to a proper language string
				throw new RuntimeException(JText::sprintf('The view %s could not be found', $class));
			}

			// We've found a relevant Joomla class - use it.
			$class = $joomlaClass;
		}

		$this->view = new $class($model, $config);

		return $this->view;
	}

}
