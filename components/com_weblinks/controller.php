<?php
/**
 * @version $Id$
 * @package Joomla
 * @subpackage Content
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

jimport('joomla.application.component.controller');

/**
 * Weblinks Component Controller
 *
 * @package Joomla
 * @subpackage Weblinks
 * @since 1.5
 */
class WeblinksController extends JController
{
	/**
	 * Method to show a weblinks view
	 *
	 * @access	public
	 * @since	1.5
	 */
	function display()
	{
		$viewName	= JRequest::getVar( 'view', 'categories' );

		// interceptors to support legacy urls
		switch( $this->getTask())
		{
			//index.php?option=com_weblinks&task=x&catid=xid=x
			case 'view':
			{
				$viewName	= 'weblink';
			} break;

			default:
			{
				if($catid = JRequest::getVar( 'catid', 0)) {
					$viewName = 'category';
					JRequest::setVar('id', $catid);
				}
			}
		}

		JRequest::setVar('view', $viewName);

		//update the hit count for the weblink
		if($view == 'weblink') {
			$model =& $this->getModel('weblink');
			$model->hit();
		}

		parent::display();
	}
}

?>