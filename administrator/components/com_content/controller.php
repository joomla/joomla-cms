<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * Component Controller
 *
 * @package		Joomla.Administrator
 * @subpackage	com_content
 */
class ContentController extends JController
{
	/**
	 * @var		string	The default view.
	 * @since	1.6
	 */
	protected $default_view = 'articles';

	/**
	 * Display the view
	 */
	function display()
	{
		require_once JPATH_COMPONENT.'/helpers/content.php';

		parent::display();

		// Load the submenu.
		ContentHelper::addSubmenu(JRequest::getWord('view', 'articles'));
	}
}