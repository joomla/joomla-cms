<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/media.php';

/**
 * Item Model for an Article.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_media
 * @since       1.6
 */
class MediaModelInformation extends JModelDatabase
{
	/**
	 * @var        string    The prefix to use with controller messages.
	 * @since   1.6
	 */
	protected $text_prefix = 'COM_MEDIA';

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object $record    A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission set in the component.
	 * @since   1.6
	 */
	protected function canDelete($record)
	{
		if (!empty($record->id))
		{
			if ($record->state != -2)
			{
				return;
			}

			$user = JFactory::getUser();

			return $user->authorise('core.delete', 'com_media.media.' . (int) $record->id);
		}
	}

	/**
	 * Returns a Table object, always creating it.
	 *
	 * @param   type      The table type to instantiate
	 * @param   string    A prefix for the table class name. Optional.
	 * @param   array     Configuration array for model. Optional.
	 *
	 * @return  JTable    A database object
	 */
	public function getTable($type = 'Media', $prefix = 'JTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer    The id of the primary key.
	 *
	 * @return  mixed  Object on success, false on failure.
	 */
	public function getItem($pk = null)
	{
		$input = JFactory::getApplication()->input;
		$path = $input->get('editing', '', 'STRING');
		$path = str_replace('/', '\\', $path);
		$path = $this->fixPath($path);

		$query = $this->db->getQuery(true);
		$columns = array('content_id', 'type_alias', 'title', 'alias', 'body', 'checked_out_time', 'checked_out', 'access', 'params',
			'metadata', 'created_user_id', 'created_by_alias', 'created_time', 'modified_user_id', 'modified_time',
			'language', 'content_item_id', 'asset_id', 'urls', 'catid', 'type_id');
		$query->select($columns)
			->from($this->db->quoteName("#__ucm_media"))
			->where("urls = " . $this->db->quote($path));
		$this->db->setQuery($query);
		$result = $this->db->loadObjectList();
		$row = empty($result) ? array() : $result[0];

		return $row;
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array   $data        Data for the form.
	 * @param   boolean $loadData    True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 * @since   1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = new JForm('editor');
		$form->loadFile(JPATH_ADMINISTRATOR . '/components/com_media/model/forms/editor.xml');

		if (empty($form))
		{
			return false;
		}

		$form->setFieldAttribute('catid', 'action', 'core.edit');
		$form->setFieldAttribute('catid', 'action', 'core.edit.own');
		$this->pushDataToForm($form);

		return $form;
	}

	/**
	 * A protected method to get a set of ordering conditions.
	 *
	 * @param   object    A record object.
	 *
	 * @return  array  An array of conditions to add to add to ordering queries.
	 * @since   1.6
	 */
	protected function getReorderConditions($table)
	{
		$condition = array();
		$condition[] = 'catid = ' . (int) $table->catid;

		return $condition;
	}

	/**
	 * A protected method to get a set of ordering conditions.
	 *
	 * @param   object    A record object.
	 *
	 * @return  array  An array of conditions to add to add to ordering queries.
	 * @since   1.6
	 */
	protected function pushDataToForm($form)
	{
		$row = $this->getItem();
		$form->setValue('title', null, $this->checkData('title', $row, 'title'));
		$form->setValue('alias', null, $this->checkData('alias', $row, 'alias'));
		$form->setValue('id', null, $this->checkData('id', $row, 'content_id'));
		$form->setValue('created_by', null, $this->checkData('created_by', $row, 'created_user_id'));
		$form->setValue('created_by_alias', null, $this->checkData('created_by_alias', $row, 'created_by_alias'));
		$form->setValue('created', null, $this->checkData('created', $row, 'created_time'));
		$form->setValue('catid', null, $this->checkData('catid', $row, 'catid'));
		$form->setValue('asset_id', null, $this->checkData('asset_id', $row, 'asset_id'));
		$form->setValue('access', null, $this->checkData('access', $row, 'access'));

		return $form;
	}

	/**
	 * Get Value of correct form field
	 *
	 * @since 3.2
	 */
	protected function checkData($dataFromUser, $row, $field)
	{
		$input = JFactory::getApplication()->input;
		$value = $input->get($dataFromUser, '', 'STRING');

		if ($value == '')
		{
			$value = $row->$field;
		}

		return $value;
	}

	/**
	 * Save informations to database
	 *
	 *
	 * @since 3.2
	 */
	public function saveData()
	{
		$input = JFactory::getApplication()->input;
		$db = $this->db;
		$id = $input->get('id');
		$editing = $input->get('editing', '', 'STRING');
		$title = $input->get('title', '', 'STRING');
		$alias = $input->get('alias', '', 'STRING');
		$created_by = $input->get('created_by', '', 'STRING');
		$created_by_alias = $input->get('created_by_alias', '', 'STRING');
		$created = $input->get('created', '', 'DATE');
		$catid = $input->get('catid');
		$access = $input->get('access', 0, 'INT');
		$ruleValue = $input->get('rules', array(), 'ARRAY');

		foreach ($ruleValue as $key => $rule)
		{
			foreach ($rule as $keyItem => $ruleItem)
			{
				if ($ruleItem != '')
				{
					$rules[$key][$keyItem] = $ruleItem;
				}
			}
		}

		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_media/tables');
		$row =& JTable::getInstance('media', 'mediaTable');
		$columns = array('content_id', 'title', 'alias', 'created_user_id', 'created_by_alias', 'created_time', 'catid', 'access', 'rules');
		$value = array($id, $title, $alias, $created_by, $created_by_alias, $created, $catid, $access, $rules);
		$bindValues = array_combine($columns, $value);
		$row->bind($bindValues);
		$row->store();

		return true;
	}

	/**
	 * Custom clean the cache of com_media and content modules
	 *
	 * @since   1.6
	 */
	protected function cleanCache($group = null, $client_id = 0)
	{
		parent::cleanCache('com_media');
	}

	/**
	 * Fix the editing path
	 *
	 *
	 * @since 3.2
	 */
	protected function fixPath($path = null)
	{
		$path = str_replace('/', '\\', $path);
		$path = trim($path, "\\");
		return $path;
	}
}
