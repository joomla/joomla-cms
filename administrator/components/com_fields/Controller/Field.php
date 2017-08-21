<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Fields\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Controller\Form;
use Joomla\CMS\Model\Model;
use Joomla\CMS\Mvc\Factory\MvcFactoryInterface;
use Joomla\Registry\Registry;

/**
 * The Field controller
 *
 * @since  3.7.0
 */
class Field extends Form
{
	private $internalContext;

	private $component;

	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string

	 * @since   3.7.0
	 */
	protected $text_prefix = 'COM_FIELDS_FIELD';

	/**
	 * Constructor.
	 *
	 * @param   array                $config   An optional associative array of configuration settings.
	 * Recognized key values include 'name', 'default_task', 'model_path', and
	 * 'view_path' (this list is not meant to be comprehensive).
	 * @param   MvcFactoryInterface  $factory  The factory.
	 * @param   CMSApplication       $app      The JApplication for the dispatcher
	 * @param   \JInput              $input    Input
	 *
	 * @since   3.7.0
	 */
	public function __construct($config = array(), MvcFactoryInterface $factory = null, $app = null, $input = null)
	{
		parent::__construct($config, $factory, $app, $input);

		$this->internalContext = \JFactory::getApplication()->getUserStateFromRequest('com_fields.fields.context', 'context', 'com_content.article', 'CMD');
		$parts = \FieldsHelper::extract($this->internalContext);
		$this->component = $parts ? $parts[0] : null;
	}

	/**
	 * Method override to check if you can add a new record.
	 *
	 * @param   array  $data  An array of input data.
	 *
	 * @return  boolean
	 *
	 * @since   3.7.0
	 */
	protected function allowAdd($data = array())
	{
		return \JFactory::getUser()->authorise('core.create', $this->component);
	}

	/**
	 * Method override to check if you can edit an existing record.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key.
	 *
	 * @return  boolean
	 *
	 * @since   3.7.0
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		$recordId = (int) isset($data[$key]) ? $data[$key] : 0;
		$user     = \JFactory::getUser();

		// Check general edit permission first.
		if ($user->authorise('core.edit', $this->component))
		{
			return true;
		}

		// Check edit on the record asset (explicit or inherited)
		if ($user->authorise('core.edit', $this->component . '.field.' . $recordId))
		{
			return true;
		}

		// Check edit own on the record asset (explicit or inherited)
		if ($user->authorise('core.edit.own', $this->component . '.field.' . $recordId))
		{
			// Existing record already has an owner, get it
			$record = $this->getModel()->getItem($recordId);

			if (empty($record))
			{
				return false;
			}

			// Grant if current user is owner of the record
			return $user->id == $record->created_by;
		}

		return false;
	}

	/**
	 * Method to run batch operations.
	 *
	 * @param   object  $model  The model.
	 *
	 * @return  boolean   True if successful, false otherwise and internal error is set.
	 *
	 * @since   3.7.0
	 */
	public function batch($model = null)
	{
		\JSession::checkToken() or jexit(\JText::_('JINVALID_TOKEN'));

		// Set the model
		$model = $this->getModel('Field');

		// Preset the redirect
		$this->setRedirect('index.php?option=com_fields&view=fields&context=' . $this->internalContext);

		return parent::batch($model);
	}

	/**
	 * Gets the URL arguments to append to an item redirect.
	 *
	 * @param   integer  $recordId  The primary key id for the item.
	 * @param   string   $urlVar    The name of the URL variable for the id.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 *
	 * @since   3.7.0
	 */
	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
	{
		return parent::getRedirectToItemAppend($recordId) . '&context=' . $this->internalContext;
	}

	/**
	 * Gets the URL arguments to append to a list redirect.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 *
	 * @since   3.7.0
	 */
	protected function getRedirectToListAppend()
	{
		return parent::getRedirectToListAppend() . '&context=' . $this->internalContext;
	}

	/**
	 * Function that allows child controller access to model data after the data has been saved.
	 *
	 * @param   Model  $model      The data model object.
	 * @param   array  $validData  The validated data.
	 *
	 * @return  void
	 *
	 * @since   3.7.0
	 */
	protected function postSaveHook(Model $model, $validData = array())
	{
		$item = $model->getItem();

		if (isset($item->params) && is_array($item->params))
		{
			$registry = new Registry;
			$registry->loadArray($item->params);
			$item->params = (string) $registry;
		}

		return;
	}
}
