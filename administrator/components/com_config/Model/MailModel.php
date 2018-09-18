<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Config\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Object\CMSObject;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Item Model for a Mail template.
 *
 * @since  __DEPLOY_VERSION__
 */
class MailModel extends AdminModel
{
	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $text_prefix = 'COM_CONFIG';

	/**
	 * The type alias for this content type (for example, 'com_content.article').
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public $typeAlias = 'com_config.mail';

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 * 
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission set in the component.
	 * 
	 * @since   __DEPLOY_VERSION__
	 */
	protected function canDelete($record)
	{
		return false;
	}

	/**
	 * Method to test whether a record can have its state changed.
	 *
	 * @param   object  $record  A record object.
	 * 
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission set in the component.
	 * 
	 * @since   __DEPLOY_VERSION__
	 */
	protected function canEditState($record)
	{
		return true;
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 * 
	 * @return  JForm  A JForm object on success, false on failure
	 * 
	 * @since   __DEPLOY_VERSION__
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_config.mail', 'mail', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  CMSObject|boolean  Object on success, false on failure.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getItem($pk = null)
	{
		$mail_id = $this->getState($this->getName() . '.mail_id');
		$language = $this->getState($this->getName() . '.language');
		$table = $this->getTable();

		if ($mail_id != '' && $language != '')
		{
			// Attempt to load the row.
			$return = $table->load(array('mail_id' => $mail_id, 'language' => $language));

			// Check for a table object error.
			if ($return === false && $table->getError())
			{
				$this->setError($table->getError());

				return false;
			}
		}

		// Convert to the CMSObject before adding other data.
		$properties = $table->getProperties(1);
		$item = ArrayHelper::toObject($properties, CMSObject::class);

		if (property_exists($item, 'params'))
		{
			$registry = new Registry($item->params);
			$item->params = $registry->toArray();
		}

		if (!$item->mail_id)
		{
			$item->mail_id = $mail_id;
		}

		if (!$item->language)
		{
			$item->language = $language;
		}

		return $item;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  CMSObject|boolean  Object on success, false on failure.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getMaster($pk = null)
	{
		$mail_id = $this->getState($this->getName() . '.mail_id');
		$table = $this->getTable();

		if ($mail_id != '')
		{
			// Attempt to load the row.
			$return = $table->load(array('mail_id' => $mail_id, 'language' => ''));

			// Check for a table object error.
			if ($return === false && $table->getError())
			{
				$this->setError($table->getError());

				return false;
			}
		}

		// Convert to the CMSObject before adding other data.
		$properties = $table->getProperties(1);
		$item = ArrayHelper::toObject($properties, CMSObject::class);

		if (property_exists($item, 'params'))
		{
			$registry = new Registry($item->params);
			$item->params = $registry->toArray();
		}

		return $item;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 * 
	 * @since   __DEPLOY_VERSION__
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState('com_adminmails.edit.adminmail.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function save($data)
	{
		$table      = $this->getTable();
		$context    = $this->option . '.' . $this->name;

		$key = $table->getKeyName();
		$mail_id = (!empty($data['mail_id'])) ? $data['mail_id'] : $this->getState($this->getName() . '.mail_id');
		$language = (!empty($data['language'])) ? $data['language'] : $this->getState($this->getName() . '.language');
		$isNew = true;

		// Include the plugins for the save events.
		\JPluginHelper::importPlugin($this->events_map['save']);

		// Allow an exception to be thrown.
		try
		{
			// Load the row if saving an existing record.
			$table->load(array('mail_id' => $mail_id, 'language' => $language));

			if ($table->subject)
			{
				$isNew = false;
			}

			// Bind the data.
			if (!$table->bind($data))
			{
				$this->setError($table->getError());

				return false;
			}

			// Prepare the row for saving
			$this->prepareTable($table);

			// Check the data.
			if (!$table->check())
			{
				$this->setError($table->getError());

				return false;
			}

			// Trigger the before save event.
			$result = Factory::getApplication()->triggerEvent($this->event_before_save, array($context, $table, $isNew, $data));

			if (in_array(false, $result, true))
			{
				$this->setError($table->getError());

				return false;
			}

			// Store the data.
			if (!$table->store())
			{
				$this->setError($table->getError());

				return false;
			}

			// Clean the cache.
			$this->cleanCache();

			// Trigger the after save event.
			Factory::getApplication()->triggerEvent($this->event_after_save, array($context, $table, $isNew, $data));
		}
		catch (\Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		$this->setState($this->getName() . '.new', $isNew);

		return true;
	}

	/**
	 * Stock method to auto-populate the model state.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function populateState()
	{
		parent::populateState();

		$mail_id = Factory::getApplication()->input->getCmd('mail_id');
		$this->setState($this->getName() . '.mail_id', $mail_id);

		$language = Factory::getApplication()->input->getCmd('language');
		$this->setState($this->getName() . '.language', $language);
	}
}
