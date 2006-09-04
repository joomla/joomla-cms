<?php
/**
 * @version $Id$
 * @package Joomla
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

/**
 * Base class for a Joomla View
 *
 * @abstract
 * @package		Joomla.Framework
 * @subpackage	Application
 * @author		Louis Landry, Andrew Eddie
 * @since		1.5
 */
class JView extends JObject
{

	/**
	 * Name of the view.  Defined in subclasses
	 *
	 * @var	string
	 * @access protected
	 */
	var $_viewName = null;

	/**
	 * Registered models
	 *
	 * @var		array
	 * @access protected
	 */
	var $_models = null;

	/**
	 * The template path
	 * 
	 * @var	string
	 * @access protected
	 */
	var $_templatePath = null;

	/**
	 * Constructor
	 *
	 * @access	protected
	 * @since	1.5
	 */
	function __construct() {
		$this->_vardata = array();
	}

	/**
	 * Method to get data from a registered model
	 *
	 * @access	public
	 * @param	string	The name of the method to call on the model
	 * @param	string	The name of the model to reference [optional]
	 * @return mixed	The return value of the method
	 * @since	1.5
	 */
	function &get( $method, $model = null )
	{
		$false = false;

		// If $model is null we use the default model
		if (is_null($model))
		{
			$model = $this->_defaultModel;
		}
		// First check to make sure the model requested exists
		if (isset( $this->_models[$model] ))
		{
			// Model exists, lets build the method name
			$method = 'get'.ucfirst($method);

			// Does the method exist?
			if (method_exists($this->_models[$model], $method))
			{
				// The method exists, lets call it and return what we get
				$data = $this->_models[$model]->$method();
				return $data;
			}
			else
			{
				// Method wasn't found... throw a warning and return false
				JError::raiseWarning( 0, "Unknown Method $model::$method() was not found");
				return $false;
			}
		}
		else
		{
			// Model wasn't found, return throw a warning and return false
			JError::raiseWarning( 0, 'Unknown Model', "$model model was not found");
			return $false;
		}
	}

	/**
	 * Method to add a model to the view.  We support a multiple model single
	 * view system by which models are referenced by classname.  A caveat to the
	 * classname referencing is that any classname prepended by JModel will be
	 * referenced by the name without JModel, eg. JModelCategory is just
	 * Category.
	 *
	 * @access	public
	 * @param	object	$model		The model to add to the view.
	 * @param	boolean	$default	Is this the default model?
	 * @return	object				The added model
	 * @since	1.5
	 */
	function &setModel( &$model, $default = false )
	{
		$name = strtolower(get_class($model));
		$this->_models[$name] = &$model;
		if ($default)
		{
			$this->_defaultModel = $name;
		}
		return $model;
	}

	/**
	 * Method to get the model object
	 *
	 * @access	public
	 * @param	string	$name	The name of the model (optional)
	 * @return	mixed			JModel object
	 * @since	1.5
	 */
	function &getModel( $name = null )
	{
		if ($name === null)
		{
			$name = $this->_defaultModel;
		}
		return $this->_models[strtolower( $name )];
	}

	/**
	 * Method to set the current template path
	 *
	 * @access	public
	 * @param	string	$path	Template file base directory
	 * @return	string	Template file base directory
	 * @since	1.5
	 */
	function setTemplatePath( $path )
	{
		$this->_templatePath = $path.DS;
		return $this->_templatePath;
	}

	/**
	 * Method to get the current template path
	 *
	 * @access	public
	 * @return	string	Template file base directory
	 * @since	1.5
	 */
	function getTemplatePath()
	{
		return $this->_templatePath;
	}

	/**
	 * Load a template file -- first look in the templates folder for an override
	 *
	 * @access	protected
	 * @param	string		$template	Template file name to load
	 * @return	mixed		Boolean true on success or JError object on fail
	 * @since	1.5
	 */
	function _loadTemplate( $template )
	{
		static $paths;
		global $mainframe, $Itemid, $option;

		if (!$paths)
		{
			$paths = array();
		}

		// Initialize variables
		$return = true;

		// If a template override exists in the theme folder, then we include it, otherwise we use the base.
		$tPath = JPATH_BASE.DS.'templates'.DS.$mainframe->getTemplate().DS.'html'.DS.$option.DS.$this->_viewName.DS.strtolower($template).'.php';
		if (!isset( $paths[$tPath] ))
		{
			$paths[$tPath] = file_exists( $tPath );
		}
		if ($paths[$tPath])
		{
			require( $tPath );
		}
		else
		{
			// Build the path to the default view based upon a supplied base path
			$path = $this->_templatePath.strtolower($template).'.php';

			// If the default view file exists include it and try to instantiate the object
			if (!isset( $paths[$path] ))
			{
				$paths[$path] = file_exists( $path );
			}
			if ($paths[$path])
			{
				require( $path );
			}
			else
			{
				$return = JError::raiseWarning( 500, 'Template '.$template.' not supported in '.get_class( $this ).'. File not found.' );
			}
		}
		return $return;
	}

	/**
	 * String representation
	 *
	 * @access	public
	 * @return	string
	 * @since	1.5
	 */
	function __toString()
	{
		$result = get_class( $this );
		$result .= "\nModels:";
		foreach ($this->_models as $model) {
			$result .= '&nbsp;'.$model->__toString();
		}
		return $result;
	}
}
?>