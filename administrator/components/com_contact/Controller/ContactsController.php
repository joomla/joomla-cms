<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Contact\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Utilities\ArrayHelper;

/**
 * Contacts list controller class.
 *
 * @since  1.6
 */
class ContactsController extends AdminController
{
	/**
	 * Constructor.
	 *
	 * @param   array                $config   An optional associative array of configuration settings.
	 * Recognized key values include 'name', 'default_task', 'model_path', and
	 * 'view_path' (this list is not meant to be comprehensive).
	 * @param   MVCFactoryInterface  $factory  The factory.
	 * @param   CMSApplication       $app      The JApplication for the dispatcher
	 * @param   \JInput              $input    Input
	 *
	 * @since   3.0
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null, $app = null, $input = null)
	{
		parent::__construct($config, $factory, $app, $input);

		$this->registerTask('unfeatured',	'featured');
	}

	/**
	 * Method to toggle the featured setting of a list of contacts.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function featured()
	{
		// Check for request forgeries
		\JSession::checkToken() or jexit(\JText::_('JINVALID_TOKEN'));

		$ids    = $this->input->get('cid', array(), 'array');
		$values = array('featured' => 1, 'unfeatured' => 0);
		$task   = $this->getTask();
		$value  = ArrayHelper::getValue($values, $task, 0, 'int');

		// Get the model.
		/** @var \Joomla\Component\Contact\Administrator\Model\ContactModel $model */
		$model  = $this->getModel();

		// Access checks.
		foreach ($ids as $i => $id)
		{
			$item = $model->getItem($id);

			if (!\JFactory::getUser()->authorise('core.edit.state', 'com_contact.category.' . (int) $item->catid))
			{
				// Prune items that you can't change.
				unset($ids[$i]);
				$this->app->enqueueMessage(\JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), 'notice');
			}
		}

		if (empty($ids))
		{
			$this->app->enqueueMessage(\JText::_('COM_CONTACT_NO_ITEM_SELECTED'), 'warning');
		}
		else
		{
			// Publish the items.
			if (!$model->featured($ids, $value))
			{
				$this->app->enqueueMessage($model->getError(), 'warning');
			}

			if ($value == 1)
			{
				$message = JText::plural('COM_CONTACT_N_ITEMS_FEATURED', count($ids));
			}
			else
			{
				$message = JText::plural('COM_CONTACT_N_ITEMS_UNFEATURED', count($ids));
			}
		}

		$this->setRedirect('index.php?option=com_contact&view=contacts', $message);
	}

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The name of the model.
	 * @param   string  $prefix  The prefix for the PHP class name.
	 * @param   array   $config  Array of configuration parameters.
	 *
	 * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel
	 *
	 * @since   1.6
	 */
	public function getModel($name = 'Contact', $prefix = 'Administrator', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}
}
