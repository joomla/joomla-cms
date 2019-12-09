<?php
/*
 * @package     acorn.Framework
 * @subpackage  acorn - installer script
 *
 * @copyright   Copyright (C) 2015 Troy T. Hall All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * @since 1.0
 * Script file of acorn framework
 */
class acornInstallerScript {

	/**
	 * method to install the component
	 *
	 * @return void
	 *@since 1.0
	 */
	function install($parent) {
		// $parent is the class calling this method
	// check for custom.css file existence and create if not present
		$this->customCssCheck($parent);
		$this->customJsCheck($parent);
	}

	/**
	 * method to uninstall the component
	 * @return void
	 *@since 1.0
	 */
	function uninstall($parent) {
		
	}

	/**
	 * method to update the component
	 * @return void
	 *@since 1.0
	 */
	function update($parent) {
	// check for custom.css file existence and create if not present
		$this->customCssCheck($parent);
		$this->customJsCheck($parent);
	}

	/**
	 * method to run before an install/update/uninstall method
	 * @return void
	 *@since 1.0
	 */
	function preflight($type, $parent) {
		
	}

	/**
	 * method to run after an install/update/uninstall method
	 * @return void
	 *@since 1.0
	 */
	function postflight($type, $parent) {
		
	}

		/**
	 * method to update the component
	 * @return void
		 *@since 1.0
	 */
	function customCssCheck($parent) {
		// $parent is the class calling this method
		// Check for existence of custom.css and create if not present
		$file = JPath::clean(JPATH_ROOT . '/templates/' . $parent->getElement() . '/css/custom.css');
		$buffer = '/*
*  This file is for your custom css changes to the template.
* you should never make changes to any css file but this one.
* it is automatically protected from being overwritten when
* upgrading the template.
*/';
		if (!JFile::exists($file)) {
			JFile::write($file, $buffer);
		}
		return;
	}
	
	/* check for custom.js folder existence and if not present create it */
		function customJsCheck($parent) {
		// $parent is the class calling this method
		// Check for existence of custom.css and create if not present
		$path = JPath::clean(JPATH_ROOT . '/templates/' . $parent->getElement() . '/js/custom.js');
		
		if (!JFolder::exists($path)) {
			JFolder::create($path);
		}
		return;
	}
}
