<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Categories\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Versioning\VersionableControllerTrait;
use Joomla\Input\Input;
use Joomla\Registry\Registry;

/**
 * The Category Controller
 *
 * @since  1.6
 */
class CategoryController extends FormController
{
	use VersionableControllerTrait;

	/**
	 * The extension for which the categories apply.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $extension;

	/**
	 * Constructor.
	 *
	 * @param   array                     $config   An optional associative array of configuration settings.
	 * @param   MVCFactoryInterface|null  $factory  The factory.
	 * @param   CMSApplication|null       $app      The JApplication for the dispatcher
	 * @param   Input|null                $input    Input
	 *
	 * @since  1.6
	 * @throws \Exception
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null, CMSApplication $app = null, Input $input = null)
	{
		parent::__construct($config, $factory, $app, $input);

		if (empty($this->extension))
		{
			$this->extension = $this->input->get('extension', 'com_content');
		}
	}

	/**
	 * Method to check if you can add a new record.
	 *
	 * @param   array  $data  An array of input data.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	protected function allowAdd($data = array())
	{
		$user = $this->app->getIdentity();

		return ($user->authorise('core.create', $this->extension) || count($user->getAuthorisedCategories($this->extension, 'core.create')));
	}

	/**
	 * Method to check if you can edit a record.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	protected function allowEdit($data = array(), $key = 'parent_id')
	{
		$recordId = (int) isset($data[$key]) ? $data[$key] : 0;
		$user = $this->app->getIdentity();

		// Check "edit" permission on record asset (explicit or inherited)
		if ($user->authorise('core.edit', $this->extension . '.category.' . $recordId))
		{
			return true;
		}

		// Check "edit own" permission on record asset (explicit or inherited)
		if ($user->authorise('core.edit.own', $this->extension . '.category.' . $recordId))
		{
			// Need to do a lookup from the model to get the owner
			$record = $this->getModel()->getItem($recordId);

			if (empty($record))
			{
				return false;
			}

			$ownerId = $record->created_user_id;

			// If the owner matches 'me' then do the test.
			if ($ownerId == $user->id)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Override parent save method to store form data with right key as expected by edit category page
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if successful, false otherwise.
	 *
	 * @since   3.10.3
	 */
	public function save($key = null, $urlVar = null)
	{
		$result = parent::save($key, $urlVar);

		$oldKey = $this->option . '.edit.category.data';
		$newKey = $this->option . '.edit.category.' . substr($this->extension, 4) . '.data';
		$this->app->setUserState($newKey, $this->app->getUserState($oldKey));

		return $result;
	}

	/**
	 * Override cancel method to clear form data for a failed edit action
	 *
	 * @param   string  $key  The name of the primary key of the URL variable.
	 *
	 * @return  boolean  True if access level checks pass, false otherwise.
	 *
	 * @since   3.10.3
	 */
	public function cancel($key = null)
	{
		$result = parent::cancel($key);

		$newKey = $this->option . '.edit.category.' . substr($this->extension, 4) . '.data';
		$this->app->setUserState($newKey, null);

		return $result;
	}

	/**
	 * Method to run batch operations.
	 *
	 * @param   object|null  $model  The model.
	 *
	 * @return  boolean  True if successful, false otherwise and internal error is set.
	 *
	 * @since   1.6
	 */
	public function batch($model = null)
	{
		$this->checkToken();

		/** @var \Joomla\Component\Categories\Administrator\Model\CategoryModel $model */
		$model = $this->getModel('Category');

		// Preset the redirect
		$this->setRedirect('index.php?option=com_categories&view=categories&extension=' . $this->extension);

		return parent::batch($model);
	}

	/**
	 * Gets the URL arguments to append to an item redirect.
	 *
	 * @param   integer|null  $recordId  The primary key id for the item.
	 * @param   string        $urlVar    The name of the URL variable for the id.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 *
	 * @since   1.6
	 */
	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
	{
		$append = parent::getRedirectToItemAppend($recordId);

		// In case extension is not passed in the URL, get it directly from category instead of default to com_content
		if (!$this->input->exists('extension') && $recordId > 0)
		{
			$table = $this->getModel('Category')->getTable();

			if ($table->load($recordId))
			{
				$this->extension = $table->extension;
			}
		}

		$append .= '&extension=' . $this->extension;

		return $append;
	}

	/**
	 * Gets the URL arguments to append to a list redirect.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 *
	 * @since   1.6
	 */
	protected function getRedirectToListAppend()
	{
		$append = parent::getRedirectToListAppend();
		$append .= '&extension=' . $this->extension;

		return $append;
	}

	/**
	 * Function that allows child controller access to model data after the data has been saved.
	 *
	 * @param   \Joomla\CMS\MVC\Model\BaseDatabaseModel  $model      The data model object.
	 * @param   array                                    $validData  The validated data.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	protected function postSaveHook(BaseDatabaseModel $model, $validData = array())
	{
		$item = $model->getItem();

		if (isset($item->params) && is_array($item->params))
		{
			$registry = new Registry($item->params);
			$item->params = (string) $registry;
		}

		if (isset($item->metadata) && is_array($item->metadata))
		{
			$registry = new Registry($item->metadata);
			$item->metadata = (string) $registry;
		}
	}
}
