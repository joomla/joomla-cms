<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

class JControllerDisplay extends JControllerCms
{
	/**
	 * View to display
	 *
	 * @var JViewCms
	 */
	protected $view;

	/**
	 * Output buffer content from the views.
	 * @var
	 */
	public $output;

	/**
	 * Should we echo the output or not?
	 * @var bool
	 */
	public $echoOutput = true;

	public function __construct(JInput $input = null, JApplicationBase $app = null, $config = array())
	{
		if (!isset($config['viewType']))
		{
			$document           = JFactory::getDocument();
			$viewType           = $document->getType();
			$config['viewType'] = $viewType;
		}

		parent::__construct($input, $app, $config);
	}

	/**
	 * Display a view.
	 *
	 * If $this->view is not set, load it and set the default model.
	 *
	 * @return bool
	 * @throws ErrorException
	 */
	public function execute()
	{
		if (empty($this->view))
		{
			$config = $this->config;
			$view   = $this->getView($config['view']);

			if ($model = $this->getModel($config['resource']))
			{
				$id = $this->input->get('id', 0, 'int');

				if ($id !== 0)
				{
					$context = $model->getContext();
					$model->setState($context . '.id', $id);
				}

				// Push the model into the view (as default)
				$view->setModel($config['resource'], $model, true);
			}

			$this->view = $view;
		}

		//check view level
		$this->view->canView();


		$output = $this->view->render();

		$this->output = $output;

		JPluginHelper::importPlugin('content');
		$dispatcher = JEventDispatcher::getInstance();
		$dispatcher->trigger('onContentPrepareOutput', array($this));

		if ($this->echoOutput)
		{
			echo $this->output;
		}

		return $this->executeController();
	}

	/**
	 * Method to get a view, initiating it if it does not already exist.
	 * This method assumes auto-loading
	 * format is $prefix.'View'.$name.$type
	 * $type is used for the file name which is a deviation from the traditional
	 * Joomla naming convention.
	 *
	 * @param string $name   name of the view folder exp. articles
	 * @param string $prefix option prefix exp. com_content
	 * @param string $type   name of the file exp. html = html.php
	 * @param array  $config settings
	 *
	 * @throws ErrorException
	 * @return JViewCms
	 */
	protected function getView($name, $prefix = null, $type = null, $config = array())
	{
		$config = $this->normalizeConfig($config);

		if (is_null($prefix))
		{
			$prefix = $config['prefix'];
		}

		if (is_null($type))
		{
			$type = $config['viewType'];
		}

		$class = ucfirst($prefix) . 'View' . ucfirst($name) . ucfirst($type);

		if ($this->view instanceof $class)
		{
			return $this->view;
		}

		if (!class_exists($class))
		{
			$path = 'com_' . strtolower($prefix) . '/view';
			$path .= '/' . strtolower($name) . '/';

			if (!JFactory::getUser()->authorise('core.manage'))
			{
				$class = strtolower($name) . '.' . strtolower($type);
				$path  = JUri::base();
			}

			throw new ErrorException(JText::sprintf('JLIB_APPLICATION_ERROR_VIEW_CLASS_NOT_FOUND', $class, $path), 500);
		}

		$this->view = new $class($config);

		return $this->view;
	}
}