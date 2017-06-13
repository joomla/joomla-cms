<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * The Group controller
 *
 * @since  3.7.0
 */
class FieldsControllerGroup extends JControllerForm
{
	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string

	 * @since   3.7.0
	 */
	protected $text_prefix = 'COM_FIELDS_GROUP';

	/**
	 * The component for which the group applies.
	 *
	 * @var    string
	 * @since   3.7.0
	 */
	private $component = '';

	/**
	 * Class constructor.
	 *
	 * @param   array  $config  A named array of configuration variables.
	 *
	 * @since   3.7.0
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$parts = FieldsHelper::extract($this->input->getCmd('context'));

		if ($parts)
		{
			$this->component = $parts[0];
		}
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
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Set the model
		$model = $this->getModel('Group');

		// Preset the redirect
		$this->setRedirect('index.php?option=com_fields&view=groups');

		return parent::batch($model);
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
		return JFactory::getUser()->authorise('core.create', $this->component);
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
	protected function allowEdit($data = array(), $key = 'parent_id')
	{
		$recordId = (int) isset($data[$key]) ? $data[$key] : 0;
		$user = JFactory::getUser();

		// Check general edit permission first.
		if ($user->authorise('core.edit', $this->component))
		{
			return true;
		}

		// Check edit on the record asset (explicit or inherited)
		if ($user->authorise('core.edit', $this->component . '.fieldgroup.' . $recordId))
		{
			return true;
		}

		// Check edit own on the record asset (explicit or inherited)
		if ($user->authorise('core.edit.own', $this->component . '.fieldgroup.' . $recordId) || $user->authorise('core.edit.own', $this->component))
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
	 * Function that allows child controller access to model data after the data has been saved.
	 *
	 * @param   JModelLegacy  $model      The data model object.
	 * @param   array         $validData  The validated data.
	 *
	 * @return  void
	 *
	 * @since   3.7.0
	 */
	protected function postSaveHook(JModelLegacy $model, $validData = array())
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
