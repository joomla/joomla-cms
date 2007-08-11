<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	Event
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

jimport( 'joomla.event.handler' );

/**
 * JPlugin Class
 *
 * @abstract
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package		Joomla.Framework
 * @subpackage	Event
 * @since		1.5
 */
class JPlugin extends JEventHandler
{
	/**
	 * A JParameter object holding the parameters for the plugin
	 *
	 * @var		A JParameter object
	 * @access	public
	 * @since	1.5
	 */
	var	$params	= null;


	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param object $subject The object to observe
	 * @param object $params  The object that holds the plugin parameters
	 * @since 1.5
	 */
	function JPlugin(& $subject, $params)  {
		parent::__construct($subject);
	}

	/**
	 * Constructor
	 */
	function __construct(& $subject, $params)
	{
		//Set the parameters
		$this->params = $params;

		parent::__construct($subject);
	}

	/**
	 * Loads the plugin language file
	 *
	 * @access	public
	 * @param	string 	$extension 	The extension for which a language file should be loaded
	 * @param	string 	$basePath  	The basepath to use
	 * @return	boolean	True, if the file has successfully loaded.
	 * @since	1.5
	 */
	function loadLanguage($extension = '', $basePath = JPATH_BASE)
	{
		if(empty($extension)) {
			$split_up = preg_split("{(?<=[a-z])(?=[A-Z])}x", get_class($this));
			$extension = 'plg_'.$split_up[1].'_'.$split_up[2];
		}

		$lang =& JFactory::getLanguage();
		return $lang->load( $extension);
	}


}
