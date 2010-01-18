<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');

/**
 * Message configuration model.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_messages
 * @since		1.6
 */
class MessagesModelConfig extends JModelForm
{
	/**
	 * Method to auto-populate the model state.
	 */
	protected function _populateState()
	{
		$app	= JFactory::getApplication('administrator');
		$user	= JFactory::getUser();

		$this->setState('user.id', $user->get('id'));

		// Load the parameters.
		$params	= JComponentHelper::getParams('com_messages');
		$this->setState('params', $params);
	}

	/**
	 * Method to get a single record.
	 *
	 * @param	integer	The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 */
	public function &getItem()
	{
		// Initialise variables.
		$item = new JObject;

		$query = new JQuery;
		$query->select('cfg_name, cfg_value');
		$query->from('#__messages_cfg');
		$query->where('user_id = '.(int) $this->getState('user.id'));

		$this->_db->setQuery($query);
		$rows = $this->_db->loadObjectList();

		if ($error = $this->_db->getErrorMsg()) {
			$this->setError($error);
			return false;
		}

		foreach ($rows as $row) {
			$item->set($row->cfg_name, $row->cfg_value);
		}

		return $item;
	}

	/**
	 * Method to get the record form.
	 *
	 * @return	mixed	JForm object on success, false on failure.
	 */
	public function getForm()
	{
		// Initialise variables.
		$app	= JFactory::getApplication();

		// Get the form.
		$form = parent::getForm('config', 'com_messages.config', array('array' => 'jform', 'event' => 'onPrepareForm'));

		// Check for an error.
		if (JError::isError($form)) {
			$this->setError($form->getMessage());
			return false;
		}

		return $form;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param	array	The form data.
	 * @return	boolean	True on success.
	 */
	public function save($data)
	{
		if ($userId = (int) $this->getState('user.id'))
		{
			$this->_db->setQuery(
				'DELETE FROM #__messages_cfg'.
				' WHERE user_id = '. $userId
			);
			$this->_db->query();
			if ($error = $this->_db->getErrorMsg()) {
				$this->setError($error);
				return false;
			}

			$tuples = array();
			foreach ($data as $k => $v) {
				$tuples[] =  '('.$userId.', '.$this->_db->Quote($k).', '.$this->_db->Quote($v).')';
			}

			if ($tuples) {
				$this->_db->setQuery(
					'INSERT INTO #__messages_cfg'.
					' (user_id, cfg_name, cfg_value)'.
					' VALUES '.implode(',', $tuples)
				);
				$this->_db->query();
				if ($error = $this->_db->getErrorMsg()) {
					$this->setError($error);
					return false;
				}
			}
			return true;
		} else {
			$this->setError('Messages_Invalid_user');
			return false;
		}
	}
}