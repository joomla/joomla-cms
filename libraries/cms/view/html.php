<?php
/**
 * Html view class
 * 
 * @package     Joomla.JCms
 * @subpackage  ViewHtml
 * @author	Ossolution Team
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die();
jimport('joomla.filesystem.path');

class JCmsViewHtml extends JCmsView
{

	/**
	 * Prefix of the language items used in the view
	 *
	 * @var string
	 */
	protected $languagePrefix;

	/**
	 * The view layout.
	 *
	 * @var string
	 */
	protected $layout = 'default';

	/**
	 * The paths queue.
	 *
	 * @var SplPriorityQueue
	 */
	protected $paths = array();

	/**
	 * Default Itemid variable value for the links in the view
	 *
	 * @var int
	 */
	public $Itemid;

	/**
	 * This is a front-end or back-end view.
	 * We need this field to determine whether we need to addToolbar or build the filter
	 *
	 * @var boolean
	 */
	protected $isAdminView = false;

	/**
	 * Method to instantiate the view.
	 *
	 * @param $config A
	 *        	named configuration array for object construction
	 *        	
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
		
		if (isset($config['layout']))
		{
			$this->layout = $config['layout'];
		}
		if (isset($config['language_prefix']))
		{
			$this->languagePrefix = $config['language_prefix'];
		}
		else
		{
			$this->languagePrefix = strtoupper($this->option);
		}
		if (isset($config['paths']))
		{
			$this->paths = $config['paths'];
		}
		else
		{
			$this->paths = new SplPriorityQueue();
		}				
		if (!empty($config['is_admin_view']))
		{
			$this->isAdminView = $config['is_admin_view'];
		}
		if (!empty($config['Itemid']))
		{
			$this->Itemid = $config['Itemid'];
		}
	}

	/**
	 * Display the view
	 */
	public function display()
	{
		echo $this->render();
	}

	/**
	 * Magic toString method that is a proxy for the render method.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->render();
	}

	/**
	 * Method to escape output.
	 *
	 * @param string $output
	 *        	The output to escape.
	 *        	
	 * @return string The escaped output.
	 */
	public function escape($output)
	{
		return htmlspecialchars($output, ENT_COMPAT, 'UTF-8');
	}

	/**
	 * Method to get the view layout.
	 *
	 * @return string The layout name.
	 */
	public function getLayout()
	{
		return $this->layout;
	}

	/**
	 * Method to get the layout path.
	 *
	 * @param string $layout
	 *        	The layout name.
	 *        	
	 * @return mixed The layout file name if found, false otherwise.
	 */
	public function getPath($layout)
	{
		// Get the layout file name.
		$file = JPath::clean($layout . '.php');						
		// Find the layout file path.
		$path = JPath::find(clone ($this->paths), $file);		
		return $path;
	}

	/**
	 * Method to get the view paths.
	 *
	 * @return SplPriorityQueue The paths queue.
	 *        
	 */
	public function getPaths()
	{
		return $this->paths;
	}

	/**
	 * Method to render the view.
	 *
	 * @return string The rendered view.
	 *        
	 * @throws RuntimeException
	 */
	public function render()
	{
		// Get the layout path.
		$path = $this->getPath($this->getLayout());
		// Check if the layout path was found.
		if (!$path)
		{
			throw new RuntimeException('Layout Path Not Found');
		}
		// Start an output buffer.
		ob_start();
		
		// Load the layout.
		include $path;
		
		// Get the layout contents.
		$output = ob_get_clean();
		
		return $output;
	}

	/**
	 * Load sub-template for the current layout
	 *
	 * @param string $template        	
	 *
	 * @throws RuntimeException
	 * @return string The output of sub-layout
	 */
	public function loadTemplate($template, $data = array())
	{
		// Get the layout path.
		$path = $this->getPath($this->getLayout() . '_' . $template);
		// Check if the layout path was found.
		if (!$path)
		{
			throw new RuntimeException('Layout Path Not Found');
		}
		extract($data);
		// Start an output buffer.
		ob_start();
		// Load the layout.
		include $path;
		// Get the layout contents.
		$output = ob_get_clean();
		return $output;
	}

	/**
	 * Method to set the view layout.
	 *
	 * @param string $layout
	 *        	The layout name.
	 *        	
	 * @return JCmsViewHtml Method supports chaining.
	 *        
	 */
	public function setLayout($layout)
	{
		$this->layout = $layout;
		
		return $this;
	}

	/**
	 * Method to set the view paths.
	 *
	 * @param $paths The
	 *        	paths queue.
	 *        	
	 * @return JCmsViewHtml Method supports chaining.
	 *        
	 */
	public function setPaths($paths)
	{
		$this->paths = $paths;
		return $this;
	}
}
