<?php
/**
 *  @package     FrameworkOnFramework
 *  @subpackage  autoloader
 *  @copyright   Copyright (C) 2010 - 2015 Nicholas K. Dionysopoulos / Akeeba Ltd. All rights reserved.
 *  @license     GNU General Public License version 2, or later
 */

defined('FOF_INCLUDED') or die();

/**
 * An autoloader for FOF-powered components. It allows the autoloading of
 * various classes related to the operation of a component, from Controllers
 * and Models to Helpers and Fields. If a class doesn't exist, it will be
 * created on the fly.
 *
 * @package  FrameworkOnFramework
 * @subpackage  autoloader
 * @since    2.1
 */
class FOFAutoloaderComponent
{
	/**
	 * An instance of this autoloader
	 *
	 * @var   FOFAutoloaderComponent
	 */
	public static $autoloader = null;

	/**
	 * The path to the FOF root directory
	 *
	 * @var   string
	 */
	public static $fofPath = null;

	/**
	 * An array holding component names and their FOF-ness status
	 *
	 * @var   array
	 */
	protected static $fofComponents = array();

	/**
	 * Initialise this autoloader
	 *
	 * @return  FOFAutoloaderComponent
	 */
	public static function init()
	{
		if (self::$autoloader == null)
		{
			self::$autoloader = new self;
		}

		return self::$autoloader;
	}

	/**
	 * Public constructor. Registers the autoloader with PHP.
	 */
	public function __construct()
	{
		self::$fofPath = realpath(__DIR__ . '/../');

		spl_autoload_register(array($this,'autoload_fof_controller'));
		spl_autoload_register(array($this,'autoload_fof_model'));
		spl_autoload_register(array($this,'autoload_fof_view'));
		spl_autoload_register(array($this,'autoload_fof_table'));
		spl_autoload_register(array($this,'autoload_fof_helper'));
		spl_autoload_register(array($this,'autoload_fof_toolbar'));
		spl_autoload_register(array($this,'autoload_fof_field'));
	}

	/**
	 * Returns true if this is a FOF-powered component, i.e. if it has a fof.xml
	 * file in its main directory.
	 *
	 * @param   string  $component  The component's name
	 *
	 * @return  boolean
	 */
	public function isFOFComponent($component)
	{
		if (!isset($fofComponents[$component]))
		{
			$componentPaths = FOFPlatform::getInstance()->getComponentBaseDirs($component);
			$fofComponents[$component] = file_exists($componentPaths['admin'] . '/fof.xml');
		}

		return $fofComponents[$component];
	}

	/**
	 * Creates class aliases. On systems where eval() is enabled it creates a
	 * real class. On other systems it merely creates an alias. The eval()
	 * method is preferred as class_aliases result in the name of the class
	 * being instanciated not being available, making it impossible to create
	 * a class instance without passing a $config array :(
	 *
	 * @param   string   $original  The name of the original (existing) class
	 * @param   string   $alias     The name of the new (aliased) class
	 * @param   boolean  $autoload  Should I try to autoload the $original class?
	 *
	 * @return  void
	 */
	private function class_alias($original, $alias, $autoload = true)
	{
		static $hasEval = null;

		if (is_null($hasEval))
		{
			$hasEval = false;

			if (function_exists('ini_get'))
			{
				$disabled_functions = ini_get('disabled_functions');

				if (!is_string($disabled_functions))
				{
					$hasEval = true;
				}
				else
				{
					$disabled_functions = explode(',', $disabled_functions);
					$hasEval = !in_array('eval', $disabled_functions);
				}
			}
		}

		if (!class_exists($original, $autoload))
		{
			return;
		}

		if ($hasEval)
		{
			$phpCode = "class $alias extends $original {}";
			eval($phpCode);
		}
		else
		{
			class_alias($original, $alias, $autoload);
		}
	}

	/**
	 * Autoload Controllers
	 *
	 * @param   string  $class_name  The name of the class to load
	 *
	 * @return  void
	 */
	public function autoload_fof_controller($class_name)
	{
        FOFPlatform::getInstance()->logDebug(__METHOD__ . "() autoloading $class_name");

		static $isCli = null, $isAdmin = null;

		if (is_null($isCli) && is_null($isAdmin))
		{
			list($isCli, $isAdmin) = FOFDispatcher::isCliAdmin();
		}

		if (strpos($class_name, 'Controller') === false)
		{
			return;
		}

		// Change from camel cased into a lowercase array
		$class_modified = preg_replace('/(\s)+/', '_', $class_name);
		$class_modified = strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $class_modified));
		$parts = explode('_', $class_modified);

		// We need three parts in the name
		if (count($parts) != 3)
		{
			return;
		}

		// We need the second part to be "controller"
		if ($parts[1] != 'controller')
		{
			return;
		}

		// Get the information about this class
		$component_raw  = $parts[0];
		$component = 'com_' . $parts[0];
		$view = $parts[2];

		// Is this an FOF 2.1 or later component?
		if (!$this->isFOFComponent($component))
		{
			return;
		}

		// Get the alternate view and class name (opposite singular/plural name)
		$alt_view = FOFInflector::isSingular($view) ? FOFInflector::pluralize($view) : FOFInflector::singularize($view);
		$alt_class = FOFInflector::camelize($component_raw . '_controller_' . $alt_view);

		// Get the component's paths
		$componentPaths = FOFPlatform::getInstance()->getComponentBaseDirs($component);

		// Get the proper and alternate paths and file names
		$file = "/controllers/$view.php";
		$altFile = "/controllers/$alt_view.php";
		$path = $componentPaths['main'];
		$altPath = $componentPaths['alt'];

		// Try to find the proper class in the proper path
		if (file_exists($path . $file))
		{
			@include_once $path . $file;
		}

		// Try to find the proper class in the alternate path
		if (!class_exists($class_name) && file_exists($altPath . $file))
		{
			@include_once $altPath . $file;
		}

		// Try to find the alternate class in the proper path
		if (!class_exists($alt_class) && file_exists($path . $altFile))
		{
			@include_once $path . $altFile;
		}

		// Try to find the alternate class in the alternate path
		if (!class_exists($alt_class) && file_exists($altPath . $altFile))
		{
			@include_once $altPath . $altFile;
		}

		// If the alternate class exists just map the class to the alternate
		if (!class_exists($class_name) && class_exists($alt_class))
		{
			$this->class_alias($alt_class, $class_name);
		}

		// No class found? Map to FOFController
		elseif (!class_exists($class_name))
		{
			if ($view != 'default')
			{
				$defaultClass = FOFInflector::camelize($component_raw . '_controller_default');
				$this->class_alias($defaultClass, $class_name);
			}
			else
			{
				$this->class_alias('FOFController', $class_name);
			}
		}
	}

	/**
	 * Autoload Models
	 *
	 * @param   string  $class_name  The name of the class to load
	 *
	 * @return  void
	 */
	public function autoload_fof_model($class_name)
	{
        FOFPlatform::getInstance()->logDebug(__METHOD__ . "() autoloading $class_name");

		static $isCli = null, $isAdmin = null;

		if (is_null($isCli) && is_null($isAdmin))
		{
			list($isCli, $isAdmin) = FOFDispatcher::isCliAdmin();
		}

		if (strpos($class_name, 'Model') === false)
		{
			return;
		}

		// Change from camel cased into a lowercase array
		$class_modified = preg_replace('/(\s)+/', '_', $class_name);
		$class_modified = strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $class_modified));
		$parts = explode('_', $class_modified);

		// We need three parts in the name
		if (count($parts) != 3)
		{
			return;
		}

		// We need the second part to be "model"
		if ($parts[1] != 'model')
		{
			return;
		}

		// Get the information about this class
		$component_raw  = $parts[0];
		$component = 'com_' . $parts[0];
		$view = $parts[2];

		// Is this an FOF 2.1 or later component?
		if (!$this->isFOFComponent($component))
		{
			return;
		}

		// Get the alternate view and class name (opposite singular/plural name)
		$alt_view = FOFInflector::isSingular($view) ? FOFInflector::pluralize($view) : FOFInflector::singularize($view);
		$alt_class = FOFInflector::camelize($component_raw . '_model_' . $alt_view);

		// Get the proper and alternate paths and file names
		$componentPaths = FOFPlatform::getInstance()->getComponentBaseDirs($component);

		$file = "/models/$view.php";
		$altFile = "/models/$alt_view.php";
		$path = $componentPaths['main'];
		$altPath = $componentPaths['alt'];

		// Try to find the proper class in the proper path
		if (file_exists($path . $file))
		{
			@include_once $path . $file;
		}

		// Try to find the proper class in the alternate path
		if (!class_exists($class_name) && file_exists($altPath . $file))
		{
			@include_once $altPath . $file;
		}

		// Try to find the alternate class in the proper path
		if (!class_exists($alt_class) && file_exists($path . $altFile))
		{
			@include_once $path . $altFile;
		}

		// Try to find the alternate class in the alternate path
		if (!class_exists($alt_class) && file_exists($altPath . $altFile))
		{
			@include_once $altPath . $altFile;
		}

		// If the alternate class exists just map the class to the alternate
		if (!class_exists($class_name) && class_exists($alt_class))
		{
			$this->class_alias($alt_class, $class_name);
		}

		// No class found? Map to FOFModel
		elseif (!class_exists($class_name))
		{
			if ($view != 'default')
			{
				$defaultClass = FOFInflector::camelize($component_raw . '_model_default');
				$this->class_alias($defaultClass, $class_name);
			}
			else
			{
				$this->class_alias('FOFModel', $class_name, true);
			}
		}
	}

	/**
	 * Autoload Views
	 *
	 * @param   string  $class_name  The name of the class to load
	 *
	 * @return  void
	 */
	public function autoload_fof_view($class_name)
	{
        FOFPlatform::getInstance()->logDebug(__METHOD__ . "() autoloading $class_name");

		static $isCli = null, $isAdmin = null;

		if (is_null($isCli) && is_null($isAdmin))
		{
			list($isCli, $isAdmin) = FOFDispatcher::isCliAdmin();
		}

		if (strpos($class_name, 'View') === false)
		{
			return;
		}

		// Change from camel cased into a lowercase array
		$class_modified = preg_replace('/(\s)+/', '_', $class_name);
		$class_modified = strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $class_modified));
		$parts = explode('_', $class_modified);

		// We need at least three parts in the name

		if (count($parts) < 3)
		{
			return;
		}

		// We need the second part to be "view"

		if ($parts[1] != 'view')
		{
			return;
		}

		// Get the information about this class
		$component_raw  = $parts[0];
		$component = 'com_' . $parts[0];
		$view = $parts[2];

		if (count($parts) > 3)
		{
			$format = $parts[3];
		}
		else
		{
			$input = new FOFInput;
			$format = $input->getCmd('format', 'html', 'cmd');
		}

		// Is this an FOF 2.1 or later component?
		if (!$this->isFOFComponent($component))
		{
			return;
		}

		// Get the alternate view and class name (opposite singular/plural name)
		$alt_view = FOFInflector::isSingular($view) ? FOFInflector::pluralize($view) : FOFInflector::singularize($view);
		$alt_class = FOFInflector::camelize($component_raw . '_view_' . $alt_view);

		// Get the proper and alternate paths and file names
		$componentPaths = FOFPlatform::getInstance()->getComponentBaseDirs($component);

		$protoFile = "/models/$view";
		$protoAltFile = "/models/$alt_view";
		$path = $componentPaths['main'];
		$altPath = $componentPaths['alt'];

		$formats = array($format);

		if ($format != 'html')
		{
			$formats[] = 'raw';
		}

		foreach ($formats as $currentFormat)
		{
			$file = $protoFile . '.' . $currentFormat . '.php';
			$altFile = $protoAltFile . '.' . $currentFormat . '.php';

			// Try to find the proper class in the proper path
			if (!class_exists($class_name) && file_exists($path . $file))
			{
				@include_once $path . $file;
			}

			// Try to find the proper class in the alternate path
			if (!class_exists($class_name) && file_exists($altPath . $file))
			{
				@include_once $altPath . $file;
			}

			// Try to find the alternate class in the proper path
			if (!class_exists($alt_class) && file_exists($path . $altFile))
			{
				@include_once $path . $altFile;
			}

			// Try to find the alternate class in the alternate path
			if (!class_exists($alt_class) && file_exists($altPath . $altFile))
			{
				@include_once $altPath . $altFile;
			}
		}

		// If the alternate class exists just map the class to the alternate
		if (!class_exists($class_name) && class_exists($alt_class))
		{
			$this->class_alias($alt_class, $class_name);
		}

		// No class found? Map to FOFModel
		elseif (!class_exists($class_name))
		{
			if ($view != 'default')
			{
				$defaultClass = FOFInflector::camelize($component_raw . '_view_default');
				$this->class_alias($defaultClass, $class_name);
			}
			else
			{
				if (!file_exists(self::$fofPath . '/view/' . $format . '.php'))
				{
					$default_class = 'FOFView';
				}
				else
				{
					$default_class = 'FOFView' . ucfirst($format);
				}

				$this->class_alias($default_class, $class_name, true);
			}
		}
	}

	/**
	 * Autoload Tables
	 *
	 * @param   string  $class_name  The name of the class to load
	 *
	 * @return  void
	 */
	public function autoload_fof_table($class_name)
	{
        FOFPlatform::getInstance()->logDebug(__METHOD__ . "() autoloading $class_name");

		static $isCli = null, $isAdmin = null;

		if (is_null($isCli) && is_null($isAdmin))
		{
			list($isCli, $isAdmin) = FOFDispatcher::isCliAdmin();
		}

		if (strpos($class_name, 'Table') === false)
		{
			return;
		}

		// Change from camel cased into a lowercase array
		$class_modified = preg_replace('/(\s)+/', '_', $class_name);
		$class_modified = strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $class_modified));
		$parts = explode('_', $class_modified);

		// We need three parts in the name

		if (count($parts) != 3)
		{
			return;
		}

		// We need the second part to be "model"
		if ($parts[1] != 'table')
		{
			return;
		}

		// Get the information about this class
		$component_raw  = $parts[0];
		$component = 'com_' . $parts[0];
		$view = $parts[2];

		// Is this an FOF 2.1 or later component?
		if (!$this->isFOFComponent($component))
		{
			return;
		}

		// Get the alternate view and class name (opposite singular/plural name)
		$alt_view = FOFInflector::isSingular($view) ? FOFInflector::pluralize($view) : FOFInflector::singularize($view);
		$alt_class = FOFInflector::camelize($component_raw . '_table_' . $alt_view);

		// Get the proper and alternate paths and file names
		$componentPaths = FOFPlatform::getInstance()->getComponentBaseDirs($component);

		$file = "/tables/$view.php";
		$altFile = "/tables/$alt_view.php";
		$path = $componentPaths['admin'];

		// Try to find the proper class in the proper path
		if (file_exists($path . $file))
		{
			@include_once $path . $file;
		}

		// Try to find the alternate class in the proper path
		if (!class_exists($alt_class) && file_exists($path . $altFile))
		{
			@include_once $path . $altFile;
		}

		// If the alternate class exists just map the class to the alternate
		if (!class_exists($class_name) && class_exists($alt_class))
		{
			$this->class_alias($alt_class, $class_name);
		}

		// No class found? Map to FOFModel
		elseif (!class_exists($class_name))
		{
			if ($view != 'default')
			{
				$defaultClass = FOFInflector::camelize($component_raw . '_table_default');
				$this->class_alias($defaultClass, $class_name);
			}
			else
			{
				$this->class_alias('FOFTable', $class_name, true);
			}
		}
	}

	/**
	 * Autoload Helpers
	 *
	 * @param   string  $class_name  The name of the class to load
	 *
	 * @return  void
	 */
	public function autoload_fof_helper($class_name)
	{
        FOFPlatform::getInstance()->logDebug(__METHOD__ . "() autoloading $class_name");

		static $isCli = null, $isAdmin = null;

		if (is_null($isCli) && is_null($isAdmin))
		{
			list($isCli, $isAdmin) = FOFDispatcher::isCliAdmin();
		}

		if (strpos($class_name, 'Helper') === false)
		{
			return;
		}

		// Change from camel cased into a lowercase array
		$class_modified = preg_replace('/(\s)+/', '_', $class_name);
		$class_modified = strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $class_modified));
		$parts = explode('_', $class_modified);

		// We need three parts in the name
		if (count($parts) != 3)
		{
			return;
		}

		// We need the second part to be "model"
		if ($parts[1] != 'helper')
		{
			return;
		}

		// Get the information about this class
		$component_raw  = $parts[0];
		$component = 'com_' . $parts[0];
		$view = $parts[2];

		// Is this an FOF 2.1 or later component?
		if (!$this->isFOFComponent($component))
		{
			return;
		}

		// Get the alternate view and class name (opposite singular/plural name)
		$alt_view = FOFInflector::isSingular($view) ? FOFInflector::pluralize($view) : FOFInflector::singularize($view);
		$alt_class = FOFInflector::camelize($component_raw . '_helper_' . $alt_view);

		// Get the proper and alternate paths and file names
		$componentPaths = FOFPlatform::getInstance()->getComponentBaseDirs($component);

		$file = "/helpers/$view.php";
		$altFile = "/helpers/$alt_view.php";
		$path = $componentPaths['main'];
		$altPath = $componentPaths['alt'];

		// Try to find the proper class in the proper path
		if (file_exists($path . $file))
		{
			@include_once $path . $file;
		}

		// Try to find the proper class in the alternate path
		if (!class_exists($class_name) && file_exists($altPath . $file))
		{
			@include_once $altPath . $file;
		}

		// Try to find the alternate class in the proper path
		if (!class_exists($alt_class) && file_exists($path . $altFile))
		{
			@include_once $path . $altFile;
		}

		// Try to find the alternate class in the alternate path
		if (!class_exists($alt_class) && file_exists($altPath . $altFile))
		{
			@include_once $altPath . $altFile;
		}

		// If the alternate class exists just map the class to the alternate
		if (!class_exists($class_name) && class_exists($alt_class))
		{
			$this->class_alias($alt_class, $class_name);
		}
	}

	/**
	 * Autoload Toolbars
	 *
	 * @param   string  $class_name  The name of the class to load
	 *
	 * @return  void
	 */
	public function autoload_fof_toolbar($class_name)
	{
        FOFPlatform::getInstance()->logDebug(__METHOD__ . "() autoloading $class_name");

		static $isCli = null, $isAdmin = null;

		if (is_null($isCli) && is_null($isAdmin))
		{
			list($isCli, $isAdmin) = FOFDispatcher::isCliAdmin();
		}

		if (strpos($class_name, 'Toolbar') === false)
		{
			return;
		}

		// Change from camel cased into a lowercase array
		$class_modified = preg_replace('/(\s)+/', '_', $class_name);
		$class_modified = strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $class_modified));
		$parts = explode('_', $class_modified);

		// We need two parts in the name
		if (count($parts) != 2)
		{
			return;
		}

		// We need the second part to be "model"
		if ($parts[1] != 'toolbar')
		{
			return;
		}

		// Get the information about this class
		$component_raw  = $parts[0];
		$component = 'com_' . $parts[0];

        $platformDirs = FOFPlatform::getInstance()->getPlatformBaseDirs();

		// Get the proper and alternate paths and file names
		$file    = "/components/$component/toolbar.php";
		$path    = ($isAdmin || $isCli) ? $platformDirs['admin'] : $platformDirs['public'];
		$altPath = ($isAdmin || $isCli) ? $platformDirs['public'] : $platformDirs['admin'];

		// Try to find the proper class in the proper path

		if (file_exists($path . $file))
		{
			@include_once $path . $file;
		}

		// Try to find the proper class in the alternate path
		if (!class_exists($class_name) && file_exists($altPath . $file))
		{
			@include_once $altPath . $file;
		}

		// No class found? Map to FOFToolbar
		if (!class_exists($class_name))
		{
			$this->class_alias('FOFToolbar', $class_name, true);
		}
	}

	/**
	 * Autoload Fields
	 *
	 * @param   string  $class_name  The name of the class to load
	 *
	 * @return  void
	 */
	public function autoload_fof_field($class_name)
	{
        FOFPlatform::getInstance()->logDebug(__METHOD__ . "() autoloading $class_name");

		// @todo
	}
}
