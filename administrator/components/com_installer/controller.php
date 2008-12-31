<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Installer
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.controller');
jimport('joomla.client.helper');

/**
 * Installer Controller
 *
 * @package		Joomla
 * @subpackage	Installer
 * @since		1.5
 */
class InstallerController extends JController
{
	/**
	 * Display the extension installer form
	 *
	 * @access	public
	 * @return	void
	 * @since	1.5
	 */
	function installform()
	{
		$model	= &$this->getModel( 'Install' );
		$view	= &$this->getView( 'Install');

		$ftp =& JClientHelper::setCredentialsFromRequest('ftp');
		$view->assignRef('ftp', $ftp);

		$view->setModel( $model, true );
		$view->display();
	}

	/**
	 * Install an extension
	 *
	 * @access	public
	 * @return	void
	 * @since	1.5
	 */
	function doInstall()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$model	= &$this->getModel( 'Install' );
		$view	= &$this->getView( 'Install' );

		$ftp =& JClientHelper::setCredentialsFromRequest('ftp');
		$view->assignRef('ftp', $ftp);

		if ($model->install()) {
			$cache = &JFactory::getCache('mod_menu');
			$cache->clean();
		}

		$view->setModel( $model, true );
		$view->display();
	}

	/**
	 * Manage an extension type (List extensions of a given type)
	 *
	 * @access	public
	 * @return	void
	 * @since	1.5
	 */
	function manage()
	{
		$type	= JRequest::getWord('type', 'components');
		$model	= &$this->getModel( $type );
		$view	= &$this->getView( $type );

		$ftp =& JClientHelper::setCredentialsFromRequest('ftp');
		$view->assignRef('ftp', $ftp);

		$view->setModel( $model, true );
		$view->display();
	}

	/**
	 * Discover handler
	 */
	function discover() {
		$model = &$this->getModel('discover');
		$view = &$this->getView( 'discover' );
		$model->discover();
		$ftp =& JClientHelper::setCredentialsFromRequest('ftp');
		$view->assignRef('ftp', $ftp);
		$view->setModel( $model, true );
		$view->display();
	}

	function discover_install() {
		$model = &$this->getModel('discover');
		$view = &$this->getView( 'discover' );
		$model->discover_install();
		$ftp =& JClientHelper::setCredentialsFromRequest('ftp');
		$view->assignRef('ftp', $ftp);
		$view->setModel( $model, true );
		$view->display();
	}

	function discover_purge() {
		$model = &$this->getModel('discover');
		$model->purge();
		$this->setRedirect('index.php?option=com_installer&task=manage&type=discover', $model->_message);
	}

	/**
	 * Enable an extension (If supported)
	 *
	 * @access	public
	 * @return	void
	 * @since	1.5
	 */
	function enable()
	{
		// Check for request forgeries
		JRequest::checkToken( 'request' ) or jexit( 'Invalid Token' );

		$type	= JRequest::getWord('type', 'components');
		$model	= &$this->getModel( $type );
		$view	= &$this->getView( $type );

		$ftp =& JClientHelper::setCredentialsFromRequest('ftp');
		$view->assignRef('ftp', $ftp);

		if (method_exists($model, 'enable')) {
			$eid = JRequest::getVar('eid', array(), '', 'array');
			JArrayHelper::toInteger($eid, array());
			$model->enable($eid);
		}

		$view->setModel( $model, true );
		$view->display();
	}

	/**
	 * Disable an extension (If supported)
	 *
	 * @access	public
	 * @return	void
	 * @since	1.5
	 */
	function disable()
	{
		// Check for request forgeries
		JRequest::checkToken( 'request' ) or jexit( 'Invalid Token' );

		$type	= JRequest::getWord('type', 'components');
		$model	= &$this->getModel( $type );
		$view	= &$this->getView( $type );

		$ftp =& JClientHelper::setCredentialsFromRequest('ftp');
		$view->assignRef('ftp', $ftp);

		if (method_exists($model, 'disable')) {
			$eid = JRequest::getVar('eid', array(), '', 'array');
			JArrayHelper::toInteger($eid, array());
			$model->disable($eid);
		}

		$view->setModel( $model, true );
		$view->display();
	}

	/**
	 * Remove an extension (Uninstall)
	 *
	 * @access	public
	 * @return	void
	 * @since	1.5
	 */
	function remove() {
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$type	= JRequest::getWord('type', 'components');
		$model	= &$this->getModel( $type );
		$view	= &$this->getView( $type );

		$ftp =& JClientHelper::setCredentialsFromRequest('ftp');
		$view->assignRef('ftp', $ftp);

		$eid = JRequest::getVar('eid', array(), '', 'array');

		// Update to handle components radio box
		// Checks there is only one extensions, we're uninstalling components
		// and then checks that the zero numbered item is set (shouldn't be a zero
		// if the eid is set to the proper format)
		if((count($eid) == 1) && ($type == 'components') && (isset($eid[0]))) $eid = array($eid[0] => 0);

		JArrayHelper::toInteger($eid, array());
		$result = $model->remove($eid);

		$view->setModel( $model, true );
		$view->display();
	}

	function refresh() {
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$type	= JRequest::getWord('type', 'manage');
		$model	= &$this->getModel( $type );
		$view	= &$this->getView( $type );

		$ftp =& JClientHelper::setCredentialsFromRequest('ftp');
		$view->assignRef('ftp', $ftp);

		$uid = JRequest::getVar('eid', array(), '', 'array');

		JArrayHelper::toInteger($uid, array());
		$result = $model->refresh($uid);

		$view->setModel( $model, true );
		$view->display();
	}

	// Should probably use multiple controllers here
	function update()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$type	= JRequest::getWord('type', 'components');
		$model	= &$this->getModel( $type );
		$view	= &$this->getView( $type );

		$ftp =& JClientHelper::setCredentialsFromRequest('ftp');
		$view->assignRef('ftp', $ftp);

		$uid = JRequest::getVar('uid', array(), '', 'array');

		JArrayHelper::toInteger($uid, array());
		$result = $model->update($uid);

		$view->setModel( $model, true );
		$view->display();
	}

	function update_find() {
		// Find updates
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$type	= JRequest::getWord('type', 'components');
		$model	= &$this->getModel( $type );
		$view	= &$this->getView( $type );

		$ftp =& JClientHelper::setCredentialsFromRequest('ftp');
		$view->assignRef('ftp', $ftp);
		$model->purge();
		$result = $model->findUpdates();

		$view->setModel( $model, true );
		$view->display();
	}

	function update_purge() {
		// Purge updates
		$model = &$this->getModel('update');
		$model->purge();
		$this->setRedirect('index.php?option=com_installer&task=manage&type=update', $model->_message);
	}
}