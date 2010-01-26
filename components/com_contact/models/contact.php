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

jimport('joomla.application.component.model');

/**
 * @package		Joomla.Site
 * @subpackage	Contact
 */
class ContactModelContact extends JModel
{
	/**
	 * Method to auto-populate the model state.
	 *
	 * @return	void
	 */
	protected function _populateState()
	{
		$app = &JFactory::getApplication('site');
		
		// Load state from the request
		$pk = JRequest::getInt('id');
		$this->setState('contact.id',$pk);

		// Load the parameters.
		$globalParams	= $app->getParams('com_contact');
		$this->setState('global_params', $globalParams);
		
		$menu = JSite::getMenu()->getActive();
		if (is_object($menu)) {
			$menuParams = new JParameter($menu->params);
			$this->setState('menu_params', $menuParams);
		}
		
		// merge the global and menu item params (menu item take priority)
		$mergedParams = clone $globalParams;
		$mergedParams->merge($menuParams);
		$this->setState('params', $mergedParams);
		
	}	
	
	/**
	 * Builds the query to select contact items
	 * @param array
	 * @return string
	 * @access protected
	 */
	function _getContactQuery($pk = null)
	{
		// TODO: Cache on the fingerprint of the arguments
		$db			= &JFactory::getDbo();
		$user		= &JFactory::getUser();
		$pk			 = (!empty($pk)) ? $pk : (int) $this->getState('contact.id');
		
		$query = new JQuery();
		if ($pk)
		{
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

	/**
	 * Gets a list of contacts
	 * @param array
	 * @return mixed Object or null
	 */
	function getContact($pk = null)
	{
		$query	= $this->_getContactQuery($pk);
		try
		{
			$this->_db->setQuery($query);
			$result = $this->_db->loadObject();
				
			if ($error = $this->_db->getErrorMsg()) {
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
			
		}
		catch (Exception $e)
		{
			$this->setError($e);
			return false;
		}
		if ($result)
		{
			$user		= &JFactory::getUser();
			$groups	= implode(',', $user->authorisedLevels());
			//get the content by the linked user
			$query = 'SELECT id, title, state, access, created' .
				' FROM #__content' .
				' WHERE created_by = '.(int)$result->user_id .
				' AND access IN ('. $groups . ')' .
				' ORDER BY state DESC, created DESC' ;
			$this->_db->setQuery($query, 0, 10);
			$articles = $this->_db->loadObjectList();
			$contact->articles=$articles;

			//get the profile information for the linked user
						$query = 'SELECT user_id, profile_key, profile_value, ordering' .
				' FROM #__user_profiles' .
				' WHERE user_id = '.(int)$result->user_id .

				' ORDER BY ordering ASC' ;
			$this->_db->setQuery($query, 0, 10);
			$profile = $this->_db->loadObjectList();
			$contact->profile=$profile;
			
		}
		return $result;
	}

}
