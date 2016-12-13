<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * The Group controller
 *
 * @since  __DEPLOY_VERSION__
 */
class FieldsControllerGroup extends JControllerForm
{
	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string

	 * @since   __DEPLOY_VERSION__
	 */
	protected $text_prefix = 'COM_FIELDS_GROUP';

	/**
	 * The extension for which the group applies.
	 *
	 * @var    string
	 * @since   __DEPLOY_VERSION__
	 */
	private $extension;

	/**
	 * Class constructor.
	 *
	 * @param   array  $config  A named array of configuration variables.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->extension = $this->input->getCmd('extension');
	}

	/**
	 * Method to run batch operations.
	 *
	 * @param   object  $model  The model.
	 *
	 * @return  boolean   True if successful, false otherwise and internal error is set.
	 *
	 * @since   __DEPLOY_VERSION__
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
	 * @since   __DEPLOY_VERSION__
	 */
	protected function allowAdd($data = array())
	{
		return JFactory::getUser()->authorise('core.create', $this->extension);
	}

	/**
	 * Method override to check if you can edit an existing record.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function allowEdit($data = array(), $key = 'parent_id')
	{
		$recordId = (int) isset($data[$key]) ? $data[$key] : 0;
		$user = JFactory::getUser();

		// Check general edit permission first.
		if ($user->authorise('core.edit', $this->extension))
		{
			return true;
		}

		// Check edit on the record asset (explicit or inherited)
		if ($user->authorise('core.edit', $this->extension . '.fieldgroup.' . $recordId))
		{
			return true;
		}

		// Check edit own on the record asset (explicit or inherited)
		if ($user->authorise('core.edit.own', $this->extension . '.fieldgroup.' . $recordId) || $user->authorise('core.edit.own', $this->extension))
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
	 * @since   __DEPLOY_VERSION__
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
