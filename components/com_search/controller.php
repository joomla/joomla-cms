<?php
/**
 * @version		$Id: controller.php 7682 2007-06-08 16:12:14Z friesengeist $
 * @package		Joomla
 * @subpackage	Content
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.controller');

/**
 * Search Component Controller
 *
 * @package		Joomla
 * @subpackage	Search
 * @since 1.5
 */
class SearchController extends JController
{
	/**
	 * Method to show the search view
	 *
	 * @access	public
	 * @since	1.5
	 */
	function display()
	{
		parent::display();
	}
	
	function search()
	{
		global $mainframe;
		
		$post = JRequest::get('post');
		$post['Itemid'] = JRequest::getVar('Itemid');

		unset($post['task']);
		unset($post['submit']);

		$uri = new JURI();
		$uri->setQuery($post);
		
		$this->setRedirect(JRoute::_('index.php?'.$uri->getQuery(), false));
	}
}