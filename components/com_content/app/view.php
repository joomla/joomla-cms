<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
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
 * @since		1.5
 */
class JView extends JObject {

	/**
	 * Name of the view.  Defined in subclasses
	 *
	 * @access	private
	 * @var		string
	 */
	var $_viewName = null;

	/**
	 * Registered models
	 *
	 * @access	private
	 * @var		array
	 */
	var $_models = null;

	/**
	 * Registered controller
	 *
	 * @access	private
	 * @var		object
	 */
	var $_controller = null;

	/**
	 * Constructor
	 *
	 * @access	protected
	 * @param	object	$controller	The view's controller
	 * @since	1.5
	 */
	function __construct( &$controller ) {
		$this->_controller = & $controller;
	}

	/**
	 * Method to set the name of the view.  In most cases this will not be used, but is provided as a public
	 * method for flexibility.
	 *
	 * @access	public
	 * @param	string	$name	New view name
	 * @return	string	New view name
	 * @since	1.5
	 */
	function setViewName( $name ) {
		// Clean and set the view name
		$this->_viewName = preg_replace( '#\W#', '', $name );
		return $this->_viewName;
	}

	/**
	 * Method to get the name of the view.
	 *
	 * @access	public
	 * @return	string	The name of the view
	 * @since	1.5
	 */
	function getViewName() {
		return $this->_viewName;
	}

	/**
	 * Method to set the controller object for the view.  In most cases this
	 * will only be used by the constructor, but is provided as a public method
	 * for flexibility.
	 *
	 * @access	public
	 * @param	object	$controller	The view's controller
	 * @return	object	The controller
	 * @since	1.5
	 */
	function &setController( &$controller ) {
		$this->_controller = &$controller;
		return $controller;
	}

	/**
	 * Method to get the view's controller object.
	 *
	 * @access	public
	 * @return	object	The controller
	 * @since	1.5
	 */
	function &getController() {
		return $this->_controller;
	}

	/**
	 * Method to add a model to the view.  We support a multiple model single
	 * view system by which models are referenced by classname.  A caveat to the
	 * classname referencing is that any classname prepended by JModel will be
	 * referenced by the name without JModel, eg. JModelCategory is just
	 * Category.
	 *
	 * @access	public
	 * @param	object		$model		The model to add to the view.
	 * @param	boolean	$default	Is this the default model?
	 * @return	object		The added model
	 * @since	1.5
	 */
	function &setModel( &$model, $default = false ) {
		$name = strtolower(get_class($model));
		if (substr($name, 0, 6) ==  'jmodel') {
			$pos = 6;
		} else {
			$pos = 0;
		}
		$name = substr($name, $pos);
		$this->_models[$name] = &$model;
		if ($default) {
			$this->_defaultModel = $name;
		}
		return $model;
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
	function &get( $method, $model = null ) {

		// If $model is null we use the default model
		if (is_null($model)) {
			$model = $this->_defaultModel;
		}
		// First check to make sure the model requested exists
		if (isset( $this->_models[$model] )) {
			// Model exists, lets build the method name
			$method = 'get'.ucfirst($method);

			// Does the method exist?
			if (method_exists($this->_models[$model], $method)) {
				// The method exists, lets call it and return what we get
				$data = $this->_models[$model]->$method();
				return $data;
			} else {
				// Method wasn't found... throw a warning and return false
				JError::raiseWarning( 0, 'Unknown Method', "$model::$method() was not found");
				return false;
			}
		} else {
			// Model wasn't found, return throw a warning and return false
			JError::raiseWarning( 0, 'Unknown Model', "$model model was not found");
			return false;
		}
	}
}
?>