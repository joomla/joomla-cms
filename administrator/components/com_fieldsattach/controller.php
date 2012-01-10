<?php

/**
 * @version		$Id: controller.php 15 2011-09-02 18:37:15Z cristian $
 * @package		fieldsattach
 * @subpackage		Components
 * @copyright		Copyright (C) 2011 - 2020 Open Source Cristian Grañó, Inc. All rights reserved.
 * @author		Cristian Grañó
 * @link		http://joomlacode.org/gf/project/fieldsattach_1_6/
 * @license		License GNU General Public License version 2 or later
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla controller library
jimport('joomla.application.component.controller');

/**
 * General Controller of fieldsattach component
 */
class fieldsattachController extends JController
{
	/**
	 * display task
	 *
	 * @return void
	 */
	function display($cachable = false) 
	{
		// set default view if not set
		JRequest::setVar('view', JRequest::getCmd('view', 'fieldsattachs'));

                $view	= JRequest::getCmd('view', 'contacts');
		$layout = JRequest::getCmd('layout', 'default'); 

		// call parent behavior
		parent::display($cachable); 

                // Load the submenu.
		fieldsattachHelper::addSubmenu(JRequest::getCmd('view', 'fieldsattach'));
            
	}
}
