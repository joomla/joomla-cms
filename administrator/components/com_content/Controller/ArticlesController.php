<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\MVC\Controller\AdminController;

/**
 * Articles list controller class.
 *
 * @since  1.6
 */
class ArticlesController extends AdminController
{
	/**
	 * Constructor.
	 *
	 * @param   array                $config   An optional associative array of configuration settings.
	 * Recognized key values include 'name', 'default_task', 'model_path', and
	 * 'view_path' (this list is not meant to be comprehensive).
	 * @param   MVCFactoryInterface  $factory  The factory.
	 * @param   CmsApplication       $app      The JApplication for the dispatcher
	 * @param   \JInput              $input    Input
	 *
	 * @since   3.0
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null, $app = null, $input = null)
	{
		parent::__construct($config, $factory, $app, $input);

		// Articles default form can come from the articles or featured view.
		// Adjust the redirect view on the value of 'view' in the request.
		if ($this->input->get('view') == 'featured')
		{
			$this->view_list = 'featured';
		}

		$this->registerTask('unfeatured', 'featured');
	}

	/**
	 * Method to toggle the featured setting of a list of articles.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function featured()
	{
		// Check for request forgeries
		\JSession::checkToken() or jexit(\JText::_('JINVALID_TOKEN'));

		$user   = \JFactory::getUser();
		$ids    = $this->input->get('cid', array(), 'array');
		$values = array('featured' => 1, 'unfeatured' => 0);
		$task   = $this->getTask();
		$value  = ArrayHelper::getValue($values, $task, 0, 'int');

		// Access checks.
		foreach ($ids as $i => $id)
		{
			if (!$user->authorise('core.edit.state', 'com_content.article.' . (int) $id))
			{
				// Prune items that you can't change.
				unset($ids[$i]);
				\JFactory::getApplication()->enqueueMessage(\JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), 'notice');
			}
		}

		if (empty($ids))
		{
			\JFactory::getApplication()->enqueueMessage(\JText::_('JERROR_NO_ITEMS_SELECTED'), 'error');
		}
		else
		{
			// Get the model.
			/** @var \Joomla\Component\Content\Administrator\Model\ArticleModel $model */
			$model = $this->getModel();

			// Publish the items.
			if (!$model->featured($ids, $value))
			{
				\JFactory::getApplication()->enqueueMessage($model->getError(), 'error');
			}

			if ($value == 1)
			{
				$message = \JText::plural('COM_CONTENT_N_ITEMS_FEATURED', count($ids));
			}
			else
			{
				$message = \JText::plural('COM_CONTENT_N_ITEMS_UNFEATURED', count($ids));
			}
		}

		$view = $this->input->get('view', '');

		if ($view == 'featured')
		{
			$this->setRedirect(\JRoute::_('index.php?option=com_content&view=featured', false), $message);
		}
		else
		{
			$this->setRedirect(\JRoute::_('index.php?option=com_content&view=articles', false), $message);
		}
	}

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  The array of possible config values. Optional.
	 *
	 * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel
	 *
	 * @since   1.6
	 */
	public function getModel($name = 'Article', $prefix = 'Administrator', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}
}
