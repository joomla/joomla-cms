<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_wrapper
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * Content Component Controller
 *
 * @package		Joomla.Site
 * @subpackage	com_content
 * @since		1.5
 */
class WrapperController extends JController
{
	/**
	 * Display the view
	 */
	function display()
	{
		$cachable = true;

		// Set the default view name and format from the Request.
		$vName		= JRequest::getWord('view', 'wrapper');
		JRequest::setVar('view', $vName);

		parent::display($cachable,array('Itemid'=>'INT'));

	}
}
