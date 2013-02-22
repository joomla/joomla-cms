<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Joomla.CMS
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Content list controller class.
 *
 * @package     Joomla.CMS
 * @subpackage  controller
 * @since       3.1
 */
class JControllerAdmincontent extends JControllerAdmin
{
	/*
	 * @var  string Model name Example Weblinks
	 */
	protected $name;

	/*
	 * @var  string   Model prefix Example: WeblinksModel
	 * @since  3.1
	 */
	protected $prefix;

	/*
	 * @var  $redirectUrl  Url for redirection after featuring
	 */
	protected $redirectUrl;

	/**
	 * @var    string  The URL option for the component. Example com_content
	 * @since  3.1
	 */
	protected $option;

	/**
	 * @var    string  The comoponent and section for featuring. This class assumes that
	 *                 the context is category with and the item has a property of $catid.
	 *                 To check individual assets or for other structures this must be overriden
	 * @since  3.1
	 */
	protected $featureContext;
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @return  JControllerAdmincontent
	 * @see     JController
	 * @since   3.1
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->registerTask('unfeatured', 'featured');
	}

	/**
	 * Proxy for getModel.
	 * @since   1.6
	 */
	public function getModel($name = '', $prefix = '', $config = array())
	{
		$config = array('ignore_request' => true);
		$model = parent::getModel($this->name, $this->prefix, $config);
		return $model;
	}

	/**
	 * Method to save the submitted ordering values for records via AJAX.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function saveOrderAjax()
	{
		// Get the input
		$input = JFactory::getApplication()->input;
		$pks = $input->post->get('cid', array(), 'array');
		$order = $input->post->get('order', array(), 'array');

		// Sanitize the input
		JArrayHelper::toInteger($pks);
		JArrayHelper::toInteger($order);

		// Get the model
		$model = $this->getModel($name, $prefix, array('ignore_request' => true));

		// Save the ordering
		$return = $model->saveorder($pks, $order);

		if ($return)
		{
			echo "1";
		}

		// Close the application
		JFactory::getApplication()->close();
	}

	/**
	 * Method to toggle the featured setting of a list of items.
	 *
	 * @return  void
	 * @since   3.2
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

		// Get the  model.
		$model  = $this->getModel();

		// Access checks. If tracking individual assets this needs to be overridden.
		foreach ($ids as $i => $id)
		{
			$item = $model->getItem($id);

			if (!$user->authorise('core.edit.state', $this->featureContext.(int) $item->catid))
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
			// Feature the items.
			if (!$model->featured($ids, $value))
			{
				JError::raiseWarning(500, $model->getError());
			}
		}

		$this->setRedirect($this->redirectUrl);
	}

}