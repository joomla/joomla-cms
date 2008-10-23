<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Cache
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.controller' );

/**
 * Cache Controller
 *
 * @package		Joomla
 * @subpackage	Cache
 * @since 1.6
 */
class CacheController extends JController
{
	function delete()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$cid = JRequest::getVar( 'cid', array(0), 'post', 'array' );

		$client	=& JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));

		$model = $this->getModel('cache');
		$model->setPath($client->path.DS.'cache');
		$model->cleanlist( $cid );

		$this->display();
	}
}