<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_redirect
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Invalid Request.');

jimport('joomla.application.component.controlleradmin');

/**
 * Redirect link list controller class.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_redirect
 * @since		1.6
 */
class RedirectControllerLinks extends JControllerAdmin
{
	protected $_context = 'com_redirect.links';
	
	/**
	 * Constructor.
	 *
	 * @param	array An optional associative array of configuration settings.
	 * @see		JController
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->registerTask('unpublish',	'publish');
		$this->registerTask('archive',		'publish');
		$this->registerTask('trash',		'publish');
		$this->setURL('index.php?option=com_redirect&view=links');
	}

	/**
	 * Display is not supported by this class.
	 */
	public function display()
	{
	}

	/**
	 * Proxy for getModel.
	 */
	public function &getModel($name = 'Link', $prefix = 'RedirectModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}

	/**
	 * Method to remove a record.
	 */
	public function delete()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$ids	= JRequest::getVar('cid', array(), '', 'array');

		if (empty($ids)) {
			JError::raiseWarning(500, JText::_('COM_REDIRECT_NO_LINK_SELECTED'));
		}
		else {
			// Get the model.
			$model = $this->getModel();

			// Remove the items.
			if (!$model->delete($ids)) {
				JError::raiseWarning(500, $model->getError());
			}
			else {
				$this->setMessage(JText::sprintf((count($ids) == 1) ? 'COM_REDIRECT_LINK_DELETED' : 'COM_REDIRECT_N_LINKS_DELETED', count($ids)));
			}
		}

		$this->setRedirect('index.php?option=com_redirect&view=links');
	}

	/**
	 * Method to update a record.
	 */
	public function activate()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$ids		= JRequest::getVar('cid', array(), '', 'array');
		$newUrl		= JRequest::getString('new_url');
		$comment	= JRequest::getString('comment');

		if (empty($ids)) {
			JError::raiseWarning(500, JText::_('COM_REDIRECT_NO_LINK_SELECTED'));
		}
		else {
			// Get the model.
			$model = $this->getModel();

			// Remove the items.
			if (!$model->activate($ids, $newUrl, $comment)) {
				JError::raiseWarning(500, $model->getError());
			}
			else {
				count($ids) == 1 ?
				$this->setMessage(JText::_('COM_REDIRECT_LINK_UPDATED')) : $this->setMessage(JText::sprintf('COM_REDIRECT_N_LINKS_UPDATED', count($ids)));
			}
		}

		$this->setRedirect('index.php?option=com_redirect&view=links');
	}
}