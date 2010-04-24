<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * Templates manager master display controller.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_templates
 * @since		1.6
 */
class TemplatesController extends JController
{
	/**
	 * @var		string	The default view.
	 * @since	1.6
	 */
	protected $default_view = 'styles';

	/**
	 * Method to display a view.
	 */
	public function display()
	{
		require_once JPATH_COMPONENT.'/helpers/templates.php';

		parent::display();

		// Load the submenu.
		TemplatesHelper::addSubmenu(JRequest::getWord('view', 'styles'));
	}

	/**
	* Preview Template
	*/
	function preview()
	{
		JRequest::setVar('view', 'prevuuw');
		parent::display();
	}
}