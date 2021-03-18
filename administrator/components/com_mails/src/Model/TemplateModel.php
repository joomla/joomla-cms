<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_mails
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Mails\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Item Model for a Mail template.
 *
 * @since  4.0.0
 */
class TemplateModel extends AdminModel
{
	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $text_prefix = 'COM_MAILS';

	/**
	 * The type alias for this content type (for example, 'com_content.article').
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	public $typeAlias = 'com_mails.template';

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission set in the component.
	 *
	 * @since   4.0.0
	 */
	protected function canDelete($record)
	{
		return false;
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  \Joomla\CMS\Form\Form|bool  A JForm object on success, false on failure
	 *
	 * @since   4.0.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_mails.template', 'template', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		$params = ComponentHelper::getParams('com_mails');

		if ($params->get('mail_style', 'plaintext') == 'plaintext')
		{
			$form->removeField('htmlbody');
			$form->removeField('htmlbody_switcher');
		}

		if ($params->get('mail_style', 'plaintext') == 'html')
		{
			$form->removeField('body');
			$form->removeField('body_switcher');
		}

		if (!$params->get('alternative_mailconfig', '0'))
		{
			$form->removeField('alternative_mailconfig', 'params');
			$form->removeField('mailfrom', 'params');
			$form->removeField('fromname', 'params');
			$form->removeField('replyto', 'params');
			$form->removeField('replytoname', 'params');
			$form->removeField('mailer', 'params');
			$form->removeField('sendmail', 'params');
			$form->removeField('smtphost', 'params');
			$form->removeField('smtpport', 'params');
			$form->removeField('smtpsecure', 'params');
			$form->removeField('smtpauth', 'params');
			$form->removeField('smtpuser', 'params');
			$form->removeField('smtppass', 'params');
		}

		if (!$params->get('copy_mails'))
		{
			$form->removeField('copyto', 'params');
		}

		if (!$params->get('attachment_folder') || !is_dir(JPATH_ROOT . '/' . $params->get('attachment_folder')))
		{
			$form->removeField('attachments');
		}
		else
		{
			$field = $form->getField('attachments');
			$subform = new \SimpleXmlElement($field->formsource);
			$files = $subform->xpath('field[@name="file"]');
			$files[0]->addAttribute('directory', JPATH_ROOT . '/' . $params->get('attachment_folder'));
			$form->load('<form><field name="attachments" type="subform" '
				. 'label="COM_MAILS_FIELD_ATTACHMENTS_LABEL" multiple="true" '
				. 'layout="joomla.form.field.subform.repeatable-table">'
				. str_replace('<?xml version="1.0"?>', '', $subform->asXML())
				. '</field></form>'
			);
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
	 * @since   4.0.0
	 */
	public function getItem($pk = null)
	{
		$template_id = $this->getState($this->getName() . '.template_id');
		$language = $this->getState($this->getName() . '.language');
		$table = $this->getTable('Template', 'Table');

		if ($template_id != '' && $language != '')
		{
			// Attempt to load the row.
			$return = $table->load(array('template_id' => $template_id, 'language' => $language));

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

		if (!$item->template_id)
		{
			$item->template_id = $template_id;
		}

		if (!$item->language)
		{
			$item->language = $language;
		}

		return $item;
	}

	/**
	 * Get the master data for a mail template.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  CMSObject|boolean  Object on success, false on failure.
	 *
	 * @since   4.0.0
	 */
	public function getMaster($pk = null)
	{
		$template_id = $this->getState($this->getName() . '.template_id');
		$table = $this->getTable('Template', 'Table');

		if ($template_id != '')
		{
			// Attempt to load the row.
			$return = $table->load(array('template_id' => $template_id, 'language' => ''));

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
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $name     The table name. Optional.
	 * @param   string  $prefix   The class prefix. Optional.
	 * @param   array   $options  Configuration array for model. Optional.
	 *
	 * @return  Table  A JTable object
	 *
	 * @since   4.0.0
	 * @throws  \Exception
	 */
	public function getTable($name = 'Template', $prefix = 'Administrator', $options = array())
	{
		return parent::getTable($name, $prefix, $options);
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   4.0.0
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$app = Factory::getApplication();
		$data = $app->getUserState('com_mails.edit.template.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		$this->preprocessData('com_mails.template', $data);

		return $data;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 *
	 * @since   4.0.0
	 */
	public function save($data)
	{
		$table      = $this->getTable();
		$context    = $this->option . '.' . $this->name;

		$key = $table->getKeyName();
		$template_id = (!empty($data['template_id'])) ? $data['template_id'] : $this->getState($this->getName() . '.template_id');
		$language = (!empty($data['language'])) ? $data['language'] : $this->getState($this->getName() . '.language');
		$isNew = true;

		// Include the plugins for the save events.
		\JPluginHelper::importPlugin($this->events_map['save']);

		// Allow an exception to be thrown.
		try
		{
			// Load the row if saving an existing record.
			$table->load(array('template_id' => $template_id, 'language' => $language));

			if ($table->subject)
			{
				$isNew = false;
			}

			// Load the default row
			$table->load(array('template_id' => $template_id, 'language' => ''));

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
	 * Prepare and sanitise the table data prior to saving.
	 *
	 * @param   Table  $table  A reference to a \JTable object.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	protected function prepareTable($table)
	{

	}

	/**
	 * Stock method to auto-populate the model state.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	protected function populateState()
	{
		parent::populateState();

		$template_id = Factory::getApplication()->input->getCmd('template_id');
		$this->setState($this->getName() . '.template_id', $template_id);

		$language = Factory::getApplication()->input->getCmd('language');
		$this->setState($this->getName() . '.language', $language);
	}
}
