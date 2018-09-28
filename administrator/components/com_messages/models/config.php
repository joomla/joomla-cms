<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_messages
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Message configuration model.
 *
 * @since  1.6
 */
class MessagesModelConfig extends JModelForm
{
	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function populateState()
	{
		$user = JFactory::getUser();

		$this->setState('user.id', $user->get('id'));

		// Load the parameters.
		$params = JComponentHelper::getParams('com_messages');
		$this->setState('params', $params);
	}

	/**
	 * Method to get a single record.
	 *
	 * @return  mixed  Object on success, false on failure.
	 *
	 * @since   1.6
	 */
	public function &getItem()
	{
		$item = new JObject;

		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->select('cfg_name, cfg_value')
			->from('#__messages_cfg')
			->where($db->quoteName('user_id') . ' = ' . (int) $this->getState('user.id'));

		$db->setQuery($query);

		try
		{
			$rows = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		foreach ($rows as $row)
		{
			$item->set($row->cfg_name, $row->cfg_value);
		}

		$this->preprocessData('com_messages.config', $item);

		return $item;
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  JForm	 A JForm object on success, false on failure
	 *
	 * @since   1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_messages.config', 'config', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.6
	 */
	public function save($data)
	{
		$db = $this->getDbo();

		if ($userId = (int) $this->getState('user.id'))
		{
			$query = $db->getQuery(true)
				->delete($db->quoteName('#__messages_cfg'))
				->where($db->quoteName('user_id') . '=' . (int) $userId);
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

			if (count($data))
			{
				$query = $db->getQuery(true)
					->insert($db->quoteName('#__messages_cfg'))
					->columns($db->quoteName(array('user_id', 'cfg_name', 'cfg_value')));

				foreach ($data as $k => $v)
				{
					$query->values($userId . ', ' . $db->quote($k) . ', ' . $db->quote($v));
				}

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
		else
		{
			$this->setError('COM_MESSAGES_ERR_INVALID_USER');

			return false;
		}
	}
}
