<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Articles list controller class.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_content
 * @since       1.6
 */
class ContentControllerArticles extends JControllerAdmincontent
{
	/*
	 * @var  $redirectUrl  Url for redirection after featuring
	 * @since  3.1
	 */
	protected $redirectUrl = 'index.php?option=com_content&view=articles';

	/**
	 * The URL option for the component.
	 *
	 * @var    string
	 * @since  3.1
	 */
	protected $option = 'com_content';

	/*
	 * @var  string Model name
	 * @since  3.1
	 */
	protected $name = 'Article';

	/*
	 * @var  string   Model prefix
	 * @since  3.1
	 */
	protected $prefix = 'ContentModel';

	/**
	 * Constructor.
	 *
	 * @param   array  $config	An optional associative array of configuration settings.

	 * @return  ContentControllerArticles
	 * @see     JController
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		// Articles default form can come from the articles or featured view.
		// Adjust the redirect view on the value of 'view' in the request.
		if ($this->input->get('view') == 'featured')
		{
			$this->view_list = 'featured';
		}
	}

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The name of the model.
	 * @param   string  $prefix  The prefix for the PHP class name.
	 * @param   string  $config  Array of configuration options
	 *
	 * @return  JModel
	 * @since   1.6
	 */
	public function getModel($name = 'Article', $prefix = 'ContentModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}
	/**
	 * Method to toggle the featured setting of a list of articles.
	 *
	 * @return  void
	 * @since   1.6
	 */
	public function featured()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$user   = JFactory::getUser();
		$ids    = $this->input->get('cid', array(), 'array');
		$values = array('featured' => 1, 'unfeatured' => 0);
		$task   = $this->getTask();
		$value  = JArrayHelper::getValue($values, $task, 0, 'int');

		// Access checks.
		foreach ($ids as $i => $id)
		{
			if (!$user->authorise('core.edit.state', 'com_content.article.'.(int) $id))
			{
				// Prune items that you can't change.
				unset($ids[$i]);
				JError::raiseNotice(403, JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
			}
		}

		if (empty($ids))
		{
			JError::raiseWarning(500, JText::_('JERROR_NO_ITEMS_SELECTED'));
		}
		else
		{
			// Get the model.
			$model = $this->getModel();

			// Publish the items.
			if (!$model->featured($ids, $value))
			{
				JError::raiseWarning(500, $model->getError());
			}
		}

		$this->setRedirect('index.php?option=com_content&view=articles');
	}
}
