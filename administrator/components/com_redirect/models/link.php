<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_redirect
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Redirect link model.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_redirect
 * @since       1.6
 */
class RedirectModelLink extends JModelAdmin
{
	/**
	 * @var        string    The prefix to use with controller messages.
	 * @since   1.6
	 */
	protected $text_prefix = 'COM_REDIRECT';

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object    $record    A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission set in the component.
	 * @since   1.6
	 */
	protected function canDelete($record)
	{

		if ($record->published != -2)
		{
			return false;
		}
		$user = JFactory::getUser();
		return $user->authorise('core.admin', 'com_redirect');
	}

	/**
	 * Method to test whether a record can have its state edited.
	 *
	 * @param   object    $record    A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission set in the component.
	 * @since   1.6
	 */
	protected function canEditState($record)
	{
		$user = JFactory::getUser();

		// Check the component since there are no categories or other assets.
		return $user->authorise('core.admin', 'com_redirect');
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   type      The table type to instantiate
	 * @param   string    A prefix for the table class name. Optional.
	 * @param   array     Configuration array for model. Optional.
	 * @return  JTable    A database object
	 * @since   1.6
	 */
	public function getTable($type = 'Link', $prefix = 'RedirectTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array      $data        Data for the form.
	 * @param   boolean    $loadData    True if the form is to load its own data (default case), false if not.
	 * @return  JForm    A JForm object on success, false on failure
	 * @since   1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_redirect.link', 'link', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}

		// Modify the form based on access controls.
		if ($this->canEditState((object) $data) != true)
		{
			// Disable fields for display.
			$form->setFieldAttribute('published', 'disabled', 'true');

			// Disable fields while saving.
			// The controller has already verified this is a record you can edit.
			$form->setFieldAttribute('published', 'filter', 'unset');
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 * @since   1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_redirect.edit.link.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		$this->preprocessData('com_redirect.link', $data);

		return $data;
	}

	/**
	 * Method to activate links.
	 *
	 * @param   array     An array of link ids.
	 * @param   string    The new URL to set for the redirect.
	 * @param   string    A comment for the redirect links.
	 * @return  boolean  Returns true on success, false on failure.
	 * @since   1.6
	 */
	public function activate(&$pks, $url, $comment = null)
	{
		$user = JFactory::getUser();
		$db = $this->getDbo();

		// Sanitize the ids.
		$pks = (array) $pks;
		JArrayHelper::toInteger($pks);

		// Populate default comment if necessary.
		$comment = (!empty($comment)) ? $comment : JText::sprintf('COM_REDIRECT_REDIRECTED_ON', JHtml::_('date', time()));

		// Access checks.
		if (!$user->authorise('core.admin', 'com_redirect'))
		{
			$pks = array();
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'));
			return false;
		}

		if (!empty($pks))
		{
			// Update the link rows.
			$query = $db->getQuery(true)
				->update($db->quoteName('#__redirect_links'))
				->set($db->quoteName('new_url') . ' = ' . $db->quote($url))
				->set($db->quoteName('published') . ' = ' . $db->quote(1))
				->set($db->quoteName('comment') . ' = ' . $db->quote($comment))
				->where($db->quoteName('id') . ' IN (' . implode(',', $pks) . ')');
			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (RuntimeException $e)
			{
				$this->setError($e->getMessage());
				return false;
			}
		}
		return true;
	}
}
