<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

/**
 * Articles list controller class.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_content
 * @since	1.6
 */
class ContentControllerArticles extends JControllerAdmin
{
	/**
	 * Constructor.
	 *
	 * @param	array An optional associative array of configuration settings.
	 * @see		JController
	 * @since	1.6
	 */
	public function __construct($config = array())
	{
		// Articles default form can come from the articles or featured view.
		// Adjust the redirect view on the value of 'view' in the request.
		if (JRequest::getCmd('view') == 'featured') {
			$this->view_list = 'featured';
		}
		parent::__construct($config);

		$this->registerTask('unfeatured',	'featured');
	}

	/**
	 * Method to toggle the featured setting of a list of articles.
	 * @since	1.6
	 */
	function featured()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));

		// Initialise variables.
		$user	= JFactory::getUser();
		$ids	= JRequest::getVar('cid', array(), '', 'array');
		$values	= array('featured' => 1, 'unfeatured' => 0);
		$task	= $this->getTask();
		$value	= JArrayHelper::getValue($values, $task, 0, 'int');

		// Access checks.
		foreach ($ids as $i => $id)
		{
			if (!$user->authorise('core.edit.state', 'com_content.article.'.(int) $id))
			{
				// Prune items that you can't change.
				unset($ids[$i]);
				JError::raiseNotice(403, JText::_('JLIB_APPLICATION_ERROR_EDIT_STATE_NOT_PERMITTED'));
			}
		}

		if (empty($ids)) {
			JError::raiseWarning(500, JText::_('JError_No_items_selected'));
		} else {
			// Get the model.
			$model = $this->getModel();

			// Publish the items.
			if (!$model->featured($ids, $value)) {
				JError::raiseWarning(500, $model->getError());
			}
		}

		$this->setRedirect('index.php?option=com_content&view=articles');
	}

	/**
	 * Proxy for getModel.
	 * @since	1.6
	 */
	public function &getModel($name = 'Article', $prefix = 'ContentModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
}
