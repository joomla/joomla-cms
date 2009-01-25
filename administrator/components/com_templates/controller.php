<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Templates
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
  */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.controller' );

class TemplatesController extends JController
{
	/**
	 * Constructor
	 */
	function __construct( $config = array() )
	{
		parent::__construct( $config );

		// Register Extra tasks
		$this->registerTask( 'apply', 			'save' );
		$this->registerTask( 'apply_source',	'save_source' );
		$this->registerTask( 'apply_css',		'save_css' );
		$this->registerTask( 'default', 		'publish' );
	}

	/**
	* Edit Template
	*/
	function edit()
	{
		JRequest::setVar( 'hidemainmenu', 1 );
		JRequest::setVar( 'view', 'template');
		parent::display();
	}

	/**
	* Save Template
	*/
	function save()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$option		= JRequest::getVar('option', '', '', 'cmd');
		$params		= JRequest::getVar('params', array(), 'post', 'array');

		$model = $this->getModel('template');
		$client		=& $model->getClient();
		$template	=& $model->getTemplate();

		if (!$template) {
			$this->setRedirect('index.php?option='.$option.'&client='.$client->id, JText::_('Operation Failed').': '.JText::_('No template specified.'));
			return;
		}

		if ($model->store($params)) {
			$msg = JText::_( 'Template Saved' );
		} else {
			$msg = JText::_( 'Error Saving Template' ) . $model->getError();
		}

		$task = JRequest::getCmd('task');
		if($task == 'apply') {
			$this->setRedirect('index.php?option='.$option.'&task=edit&cid[]='.$template.'&client='.$client->id, $msg);
		} else {
			$this->setRedirect('index.php?option='.$option.'&client='.$client->id, $msg);
		}
	}

	function cancel()
	{
		// Initialize some variables
		$option	= JRequest::getCmd('option');
		$client	=& JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));

		$this->setRedirect('index.php?option='.$option.'&client='.$client->id);
	}

	/**
	* Sets default Template
	*/
	function publish()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		// Initialize some variables
		$db		= & JFactory::getDBO();
		$cid	= JRequest::getVar('cid', array(), 'method', 'array');
		$cid	= array(JFilterInput::clean(@$cid[0], 'cmd'));
		$option	= JRequest::getCmd('option');
		$model	= $this->getModel('templates');
		$client	= $model->getClient();

		if ($cid[0])
		{
			$model->setDefault($cid[0]);
		}

		$this->setRedirect('index.php?option='.$option.'&client='.$client->id);
	}

	/**
	* Preview Template
	*/
	function preview()
	{
		JRequest::setVar( 'view', 'prevuuw');
		parent::display();
	}

	/**
	* Edit Template Source
	*/
	function edit_source()
	{
		JRequest::setVar( 'hidemainmenu', 1 );
		JRequest::setVar( 'view', 'source');
		parent::display();
	}

	/**
	* Save Template Source
	*/
	function save_source()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		// Initialize some variables
		$option			= JRequest::getCmd('option');
		$filecontent	= JRequest::getVar('filecontent', '', 'post', 'string', JREQUEST_ALLOWRAW);

		$model = $this->getModel('source');
		$client		=& $model->getClient();
		$template	=& $model->getTemplate();

		if (!$template) {
			$this->setRedirect('index.php?option='.$option.'&client='.$client->id, JText::_('Operation Failed').': '.JText::_('No template specified.'));
			return;
		}

		if (!$filecontent) {
			$this->setRedirect('index.php?option='.$option.'&client='.$client->id, JText::_('Operation Failed').': '.JText::_('Content empty.'));
			return;
		}

		if ($model->store($filecontent)) {
			$msg = JText::_( 'Template source saved' );
		} else {
			$msg = $model->getError();
		}

		$task = JRequest::getCmd('task');
		switch($task)
		{
			case 'apply_source':
				$this->setRedirect('index.php?option='.$option.'&client='.$client->id.'&task=edit_source&id='.$template, $msg);
				break;

			case 'save_source':
			default:
				$this->setRedirect('index.php?option='.$option.'&client='.$client->id.'&task=edit&cid[]='.$template, $msg);
				break;
		}
	}

	/**
	* Choose Template CSS
	*/
	function choose_css()
	{
		JRequest::setVar( 'hidemainmenu', 1 );
		JRequest::setVar( 'view', 'csschoose');
		parent::display();
	}

	/**
	* Edit Template CSS
	*/
	function edit_css()
	{
		JRequest::setVar( 'hidemainmenu', 1 );
		JRequest::setVar( 'view', 'cssedit');
		parent::display();
	}

	/**
	* Save Template CSS
	*/
	function save_css()
	{
		global $mainframe;

		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		// Initialize some variables
		$option			= JRequest::getCmd('option');
		$filecontent	= JRequest::getVar('filecontent', '', 'post', 'string', JREQUEST_ALLOWRAW);

		$model = $this->getModel('cssedit');
		$client		=& $model->getClient();
		$template	=& $model->getTemplate();
		$filename	=& $model->getFilename();

		if (!$template) {
			$this->setRedirect('index.php?option='.$option.'&client='.$client->id, JText::_('Operation Failed').': '.JText::_('No template specified.'));
			return;
		}

		if (!$filecontent) {
			$this->setRedirect('index.php?option='.$option.'&client='.$client->id, JText::_('Operation Failed').': '.JText::_('Content empty.'));
			return;
		}

		if ($model->store($filecontent)) {
			$msg = JText::_('File Saved');
		} else {
			$msg = $model->getError();
		}

		$task = JRequest::getCmd('task');
		switch($task)
		{
			case 'apply_css':
				$this->setRedirect('index.php?option='.$option.'&client='.$client->id.'&task=edit_css&id='.$template.'&filename='.$filename, $msg );
				break;

			case 'save_css':
			default:
				$this->setRedirect('index.php?option='.$option.'&client='.$client->id.'&task=edit&cid[]='.$template, $msg);
				break;
		}
	}
}