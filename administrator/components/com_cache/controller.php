<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Cache
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
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