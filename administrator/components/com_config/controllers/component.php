<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Config
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

jimport('joomla.application.component.controller');

/**
 * Note: this view is intended only to be opened in a popup
 * @package		Joomla.Administrator
 * @subpackage	Config
 */
class ConfigControllerComponent extends JController
{
	/**
	 * Custom Constructor
	 */
	public function __construct( $default = array())
	{
		parent::__construct( $default );

		$this->registerTask('edit', 'display');
		$this->registerTask('apply', 'save');
	}

	public function display()
	{
		$component = JRequest::getCmd('component');
		$this->setRedirect('index.php?option=com_config&view=component&tmpl=component&component='.$component);
	}

	/**
	 * Save the configuration
	 */
	public function save()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$component = JRequest::getCmd( 'component' );

		$table =& JTable::getInstance('component');
		if (!$table->loadByOption( $component ))
		{
			JError::raiseWarning( 500, 'Not a valid component' );
			return false;
		}

		$post = JRequest::get( 'post' );
		$post['option'] = $component;
		$table->bind( $post );

		// pre-save checks
		if (!$table->check()) {
			JError::raiseWarning( 500, $table->getError() );
			return false;
		}

		// save the changes
		if (!$table->store()) {
			JError::raiseWarning( 500, $table->getError() );
			return false;
		}

		$this->display();
	}

	/**
	 * Cancel operation
	 */
	public function cancel()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$this->setRedirect('index.php');
	}
}