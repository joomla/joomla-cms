<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_contact
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Contact\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Input\Input;
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
	 * @param   CMSApplication       $app      The Application for the dispatcher
	 * @param   Input                $input    Input
	 *
	 * @since   3.0
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null, $app = null, $input = null)
	{
		parent::__construct($config, $factory, $app, $input);

		$this->registerTask('unfeatured', 'featured');
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
		$this->checkToken();

		$ids    = (array) $this->input->get('cid', array(), 'int');
		$values = array('featured' => 1, 'unfeatured' => 0);
		$task   = $this->getTask();
		$value  = ArrayHelper::getValue($values, $task, 0, 'int');

		// Get the model.
		/** @var \Joomla\Component\Contact\Administrator\Model\ContactModel $model */
		$model  = $this->getModel();

		// Access checks.
		foreach ($ids as $i => $id)
		{
			// Remove zero value resulting from input filter
			if ($id === 0)
			{
				unset($ids[$i]);

				continue;
			}

			$item = $model->getItem($id);

			if (!$this->app->getIdentity()->authorise('core.edit.state', 'com_contact.category.' . (int) $item->catid))
			{
				// Prune items that you can't change.
				unset($ids[$i]);
				$this->app->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), 'notice');
			}
		}

		if (empty($ids))
		{
			$message = null;

			$this->app->enqueueMessage(Text::_('COM_CONTACT_NO_ITEM_SELECTED'), 'warning');
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
				$message = Text::plural('COM_CONTACT_N_ITEMS_FEATURED', count($ids));
			}
			else
			{
				$message = Text::plural('COM_CONTACT_N_ITEMS_UNFEATURED', count($ids));
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
