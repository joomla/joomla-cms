<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modelitem');
jimport('joomla.database.query');

/**
 * Content Component Article Model
 *
 * @package		Joomla.Site
 * @subpackage	com_content
 * @since 1.5
 */
class ContentModelArticle extends JModelItem
{
	/**
	 * Model context string.
	 *
	 * @var		string
	 */
	 protected $_context = 'com_content.item';

	/**
	 * Method to auto-populate the model state.
	 *
	 * @return	void
	 */
	protected function _populateState()
	{
		$app = &JFactory::getApplication('site');

		// Load state from the request.
		$pk = JRequest::getInt('id');
		$this->setState('article.id', $pk);

		$offset = JRequest::getInt('limitstart');
		$this->setState('list.offset', $offset);

		// Load the parameters.
		$params	= $app->getParams();
		$this->setState('params', $params);
	}

	/**
	 * Method to get article data.
	 *
	 * @param	integer	The id of the article.
	 *
	 * @return	mixed	Menu item data object on success, false on failure.
	 */
	public function &getItem($pk = null)
	{
		// Initialize variables.
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('article.id');

		if ($this->_item === null) {
			$this->_item = array();
		}

		if (!isset($this->_item[$pk]))
		{
			try
			{
				$query = new JQuery;

				$query->select($this->getState('item.select', 'a.*'));
				$query->from('#__content AS a');

				// Join on category table.
				$query->select('c.title AS category_title, a.alias AS category_alias, c.access AS category_access');
				$query->join('LEFT', '#__categories AS c on c.id = a.catid');

				// Join on user table.
				$query->select('u.name AS author');
				$query->join('LEFT', '#__users AS u on u.id = a.created_by');

				$query->where('a.id = '.(int) $pk);

				// Filter by published state.
				$published = $this->getState('filter.published');
				if (is_numeric($published)) {
					$query->where('a.state = '.(int) $published);
				}

				// Filter by access level.
				if ($access = $this->getState('filter.access'))
				{
					$user	= &JFactory::getUser();
					$groups	= implode(',', $user->authorisedLevels());
					$query->where('a.access IN ('.$groups.')');
					$query->where('(c.access IS NULL OR c.access IN ('.$groups.'))');
				}

				$this->_db->setQuery($query);

				$data = $this->_db->loadObject();

				if ($error = $this->_db->getErrorMsg()) {
					throw new Exception($error);
				}

				if (empty($data)) {
					throw new Exception(JText::_('Content_Error_Article_not_found'));
				}

				// Check for published state if filter set.
				if (is_numeric($published) && $data->state != $published) {
					throw new Exception(JText::_('Content_Error_Article_not_found'));
				}

				// Convert parameter fields to objects.
				$registry = new JRegistry;
				$registry->loadJSON($data->attribs);
				$data->params = clone $this->getState('params');
				$data->params->merge($registry);

				$registry = new JRegistry;
				$registry->loadJSON($data->metadata);
				$data->metadata = $registry;

				// Compute access permissions.
				if ($access)
				{
					// If the access filter has been set, we already know this user can view.
					$data->params->set('access-view', true);
				}
				else
				{
					// If no access filter is set, the layout takes some responsibility for display of limited information.
					$user	= &JFactory::getUser();
					$groups	= $user->authorisedLevels();

					if ($data->catid == 0 || $data->category_access === null) {
						$data->params->set('access-view', in_array($data->access, $groups));
					}
					else {
						$data->params->set('access-view', in_array($data->access, $groups) && in_array($data->category_access, $groups));
					}
				}
				// TODO: Type 2 permission checks?

				$this->_item[$pk] = $data;
			}
			catch (Exception $e)
			{
				$this->setError($e->getMessage());
				$this->_item[$pk] = false;
			}
		}

		return $this->_item[$pk];
	}

	/**
	 * Increment the hit counter for the article.
	 *
	 * @param	int		Optional primary key of the article to increment.
	 *
	 * @return	boolean	True if successful; false otherwise and internal error set.
	 */
	public function hit($pk = 0)
	{
		// Initialize variables.
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('article.id');

		$this->_db->setQuery(
			'UPDATE #__content' .
			' SET hits = hits + 1' .
			' WHERE id = '.(int) $pk
		);

		if (!$this->_db->query())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		return true;
	}
}
