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
	 * @return  mixed  A rendered view or true
	 *
	 * @since   3.2
	 */
	public function execute()
	{
		!$this->app->isAdmin() ? : $this->permission = 'core.manage';

		$componentFolder = $this->input->getWord('option', 'com_content');
		$this->viewName     = $this->input->getWord('view', 'articles');
		$viewFormat   = JFactory::getDocument()->getType();
		$layoutName   = $this->input->getWord('layout', 'default');

		// Register the layout paths for the view
		$paths = new SplPriorityQueue;
		$jpath = $this->app->isAdmin() ? JPATH_ADMINISTRATOR : JPATH_SITE;

		$paths->insert($jpath . '/templates/html' . $componentFolder . '/' . $this->viewName , '1000');
		$paths->insert($jpath . '/components/' . $componentFolder . '/view/' . $this->viewName . '/tmpl', '950');

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
		$viewClass  = $this->prefix . 'View' . ucfirst($this->viewName) . ucfirst($viewFormat);

		if (!class_exists($viewClass))
		{
			throw new RuntimeException('View not found');
		}

		$view = new $viewClass($model, $paths);

		if ($viewFormat == 'html')
		{
			$view->setLayout($layoutName);
		}

		// Reply for service requests
		if ($viewFormat == 'json')
		{
			return $view->render();
		}

		// Render view.
		echo $view->render();

		return true;
	}
}
