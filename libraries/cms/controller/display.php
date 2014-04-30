<?php
/**
 * @package     Joomla.Libraries
 * @subpackage Controller
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

class JControllerDisplay extends JControllerCms
{
	/**
	 * View to display
	 * @var JViewBase
	 */
	protected $view;
	
	/**
	 * Instantiate the controller.
	 *
	 * @param   JInput            $input  The input object.
	 * @param   JApplicationBase  $app    The application object.
	 * @param   array             $config Configuration
	 * @since  12.1
	 */
	public function __construct(JInput $input, $app = null, $config = array())
	{
		$viewName = $input->get('view', $config['subject']);
		$layout = $input->get('layout', 'default', 'string');
		
		$document = JFactory::getDocument();
		$viewType = $document->getType();
		
		$config['viewName'] = $viewName;
		$config['layout'] = $layout;
		$config['viewType'] = $viewType;
		
		
		parent::__construct($input, $app, $config);
		
		$task = $input->get('task', 'display', 'cmd');
		
		//check to make sure the user isn't accessing a form 
		//without using the task controller
		if ($layout == 'edit' && $task == 'display')
		{
			$id = $input->get('id', 0, 'int');
				
			$url = 'index.php?option='.$this->config['option'];
			$url .= '&task=display.'.$config['subject'];
							
			$msg = $this->translate('JLIB_APPLICATION_ERROR_DIRECT_ACCESS_DENIED');
			$this->setRedirect($url, $msg, 'error', true);
			$this->redirect();
		}
	}
	
	/**
	 * (non-PHPdoc)
	 * @see JController::execute()
	 */
	public function execute()
	{
		$config = $this->config;
		$viewName = $config['viewName'];
		$viewLayout = $config['layout'];
		
		$document = JFactory::getDocument();
		$viewType = $document->getType();
		$prefix = $this->getPrefix();	
		
		if (empty($this->view))
		{
			$view = $this->getView($prefix, $viewName, $viewType, $config);
		
			$subject = $this->config['subject'];
		
			// Get/Create the model
			if ($model = $this->getModel($prefix, $subject, $config))
			{
				// Push the model into the view (as default)
				$view->setModel($model, true);
			}
		
			$this->view = $view;
		}
		
		$output = $this->view->render();
		
		if (!($output instanceof Exception))
		{
			// wrap the output in an object to allow it to be modified by plugins
			$outputObj = new stdClass();
			$outputObj->output = $output;
			
			JPluginHelper::importPlugin('content');
			
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onContentPrepareToEcho', array($outputObj));
			
			echo $outputObj->output;
			
			return true; // finished executing
		}
		
		return false; // problem displaying the view
	}
	
	/**
	 * Method to get a view, initiating it if it doesn't already exist.
	 * This method assumes autoloading
	 * format is $prefix.'View'.$name.$type
	 * $type is used for the file name which is a diviation from the traditional 
	 * Joomla! naming convention.
	 * @param string $prefix option prefix exp. com_content
	 * @param string $name name of the view folder exp. articles
	 * @param string $type name of the file exp. html = html.php
	 * @param array $config settings
	 * @throws ErrorException
	 * @return JViewBase
	 */
	protected function getView($prefix, $name, $type, $config = array())
	{
		$class = ucfirst($prefix).'View'.ucfirst($name).ucfirst($type);
		
		if ($this->view instanceof $class)
		{
			return $this->view;
		}
		
		if (!class_exists($class)) 
		{
			$path = 'com_'.strtolower($prefix).'/view';
			$path .= '/'.strtolower($name).'/';
			throw new ErrorException(JText::sprintf('JLIB_APPLICATION_ERROR_VIEW_CLASS_NOT_FOUND', $class, $path), 500);
		}
		
		$config = $this->normalizeConfig($config);
		
		$this->view = new $class($config);
		return $this->view;
	}	
}