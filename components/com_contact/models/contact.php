<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	Contact
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modelitem');

/**
 * @package		Joomla.Site
 * @subpackage	Contact
 */
class ContactModelContact extends JModelItem
{
	/**
	 * Model context string.
	 *
	 * @var		string
	 */
	protected $_context = 'com_contact.contact';

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication('site');

		// Load state from the request.
		$pk = JRequest::getInt('id');
		$this->setState('contact.id', $pk);

		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);

		// TODO: Tune these values based on other permissions.
		$this->setState('filter.published', 1);
		$this->setState('filter.archived', 2);
	}

	/**
	 * Gets a list of contacts
	 * @param array
	 * @return mixed Object or null
	 */
	public function &getItem($pk = null)
	{
		// Initialise variables.
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('contact.id');

		if ($this->_item === null) {
			$this->_item = array();
		}

		if (!isset($this->_item[$pk])) {
			try
			{
				$db = $this->getDbo();
				$query = $db->getQuery(true);

				$query->select($this->getState('item.select', 'a.*'));
				$query->from('#__contact_details AS a');

				// Filter by start and end dates.
				$nullDate = $db->Quote($db->getNullDate());
				$nowDate = $db->Quote(JFactory::getDate()->toMySQL());

				$query->where('(a.publish_up = ' . $nullDate . ' OR a.publish_up <= ' . $nowDate . ')');
				$query->where('(a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')');

				// Join on category table.
				$query->select('c.title AS category_title, c.alias AS category_alias, c.access AS category_access');
				$query->join('LEFT', '#__categories AS c on c.id = a.catid');


				// Join over the categories to get parent category titles
				$query->select('parent.title as parent_title, parent.id as parent_id, parent.path as parent_route, parent.alias as parent_alias');
				$query->join('LEFT', '#__categories as parent ON parent.id = c.parent_id');

				$query->where('a.id = ' . (int) $pk);

				// Filter by published state.
				$published = $this->getState('filter.published');
				$archived = $this->getState('filter.archived');
				if (is_numeric($published)) {
					$query->where('(a.published = ' . (int) $published . ' OR a.published =' . (int) $archived . ')');
				}

				$db->setQuery($query);

				$data = $db->loadObject();

				if ($error = $db->getErrorMsg()) {
					throw new JException($error);
				}

				if (empty($data)) {
					throw new JException(JText::_('COM_CONTACT_ERROR_CONTACT_NOT_FOUND'), 404);
				}


				// Convert parameter fields to objects.
				$registry = new JRegistry;
				$registry->loadJSON($data->params);
				$data->params = clone $this->getState('params');
				$data->params->merge($registry);

				// Compute access permissions.
				if ($access = $this->getState('filter.access')) {
					// If the access filter has been set, we already know this user can view.
					$data->params->set('access-view', true);
				}
				else {
					// If no access filter is set, the layout takes some responsibility for display of limited information.
					$user = JFactory::getUser();
					$groups = $user->authorisedLevels();

					if ($data->catid == 0 || $data->category_access === null) {
						$data->params->set('access-view', in_array($data->access, $groups));
					}
					else {
						$data->params->set('access-view', in_array($data->access, $groups) && in_array($data->category_access, $groups));
					}
				}

				$this->_item[$pk] = $data;
			}
			catch (JException $e)
			{
				$this->setError($e);
				$this->_item[$pk] = false;
			}
		}

		return $this->_item[$pk];
	}
}

/**
 *
	function _getContactQuery($pk = null)
	{
		// TODO: Cache on the fingerprint of the arguments
		$db		= $this->getDbo();
		$user	= JFactory::getUser();
		$pk		= (!empty($pk)) ? $pk : (int) $this->getState('contact.id');

		$query	= $db->getQuery(true);
		if ($pk) {
			$query->select('a.*, cc.access as category_access, cc.title as category_name, '
			. ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug, '
			. ' CASE WHEN CHAR_LENGTH(cc.alias) THEN CONCAT_WS(\':\', cc.id, cc.alias) ELSE cc.id END AS catslug ');

			$query->from('#__contact_details AS a');

			$query->join('INNER', '#__categories AS cc on cc.id = a.catid');

			$query->where('a.id = ' . (int) $pk);
			$query->where('a.published = 1');
			$query->where('cc.published = 1');
			$groups		= implode(',', $user->authorisedLevels());
			$query->where('a.access IN ('.implode(',', $user->authorisedLevels()).')');
		}
		return $query;
	}

		$db		= $this->getDbo();
		$query	= $this->_getContactQuery($pk);
		try {
			$db->setQuery($query);
			$result = $db->loadObject();

			if ($error = $db->getErrorMsg()) {
				throw new Exception($error);
			}

			if (empty($result)) {
				throw new Exception(JText::_('Contact_Error_Contact_not_found'), 404);
			}

			// If we are showing a contact list, then the contact parameters take priority
			// So merge the contact parameters with the merged parameters
			if ($this->getState('params')->get('show_contact_list')) {
				$registry = new JRegistry;
				$registry->loadJSON($result->params);
				$this->getState('params')->merge($registry);
			}
		} catch (Exception $e) {
			$this->setError($e);
			return false;
		}

		if ($result) {
			$user	= JFactory::getUser();
			$groups	= implode(',', $user->authorisedLevels());
			//get the content by the linked user
			$query = 'SELECT id, title, state, access, created' .
				' FROM #__content' .
				' WHERE created_by = '.(int)$result->user_id .
				' AND access IN ('. $groups . ')' .
				' ORDER BY state DESC, created DESC' ;
			$db->setQuery($query, 0, 10);
			$articles = $db->loadObjectList();
			$result->articles = $articles;

			//get the profile information for the linked user
			$query = 'SELECT user_id, profile_key, profile_value, ordering' .
				' FROM #__user_profiles' .
				' WHERE user_id = '.(int)$result->user_id .
				' ORDER BY ordering ASC' ;

			$db->setQuery($query, 0, 10);
			$profile = $db->loadObjectList();
			$result->profile = $profile;
		}

		return $result;

 */
