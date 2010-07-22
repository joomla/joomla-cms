<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.model');

/**
 * Newsfeeds Component Newsfeed Model
 *
 * @package		Joomla.Site
 * @subpackage	com_newsfeeds
 * @since 1.5
 */
class NewsfeedsModelNewsfeed extends JModel
{
	/**
	 * Newsfeed id
	 *
	 * @var int
	 */
	var $_id = null;

	/**
	 * Newsfeed data
	 *
	 * @var array
	 */
	var $_data = null;

	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	public function __construct()
	{
		parent::__construct();

		$id = JRequest::getVar('id', 0, '', 'int');
		$this->setId((int)$id);
	}

	/**
	 * Method to set the newsfeed identifier
	 *
	 * @access	public
	 * @param	int Newsfeed identifier
	 */
	public function setId($id)
	{
		// Set newsfeed id and wipe data
		$this->_id		= $id;
		$this->_data	= null;
	}

	/**
	 * Method to get the newsfeed data
	 *
	 * @since 1.5
	 */
	public function &getData()
	{
		// Load the newsfeed data
		if ($this->_loadData()) {

			// Initialise some variables
			$user = JFactory::getUser();

			// Make sure the category is published
			if (!$this->_data->published) {
				JError::raiseError(404, JText::_("JGLOBAL_RESOURCE_NOT_FOUND"));
				return false;
			}

			// Check to see if the category is published
			if (!$this->_data->cat_pub) {
				JError::raiseError(404, JText::_("JGLOBAL_RESOURCE_NOT_FOUND"));
				return;
			}

			// Check whether category access level allows access
			if (!in_array($this->_data->cat_access, $user->authorisedLevels())) {
				JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
				return;
			}

		}

		return $this->_data;
	}

	/**
	 * Method to load newsfeed data
	 *
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	protected function _loadData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data)) {

			$db = $this->getDbo();
			$query = $db->getQuery(true);
			$query->select('f.*');
			$query->select('cc.title AS category, cc.published AS cat_pub, cc.access AS cat_access');
			$query->select('CASE WHEN CHAR_LENGTH(cc.alias) THEN CONCAT_WS(\':\', cc.id, cc.alias) ELSE cc.id END as catslug');
			$query->from('#__newsfeeds AS f');
			$query->leftJoin('#__categories AS cc ON cc.id = f.catid');
			$query->where('f.id = '.(int) $this->_id);

			// Filter by start and end dates.
			$nullDate = $db->quote($db->getNullDate());
			$nowDate = $db->quote(JFactory::getDate()->toMySQL());

			$query->where('(f.publish_up = '.$nullDate . ' OR f.publish_up <= '.$nowDate.')');
			$query->where('(f.publish_down = '.$nullDate . ' OR f.publish_down >= '.$nowDate.')');

			$db->setQuery($query);
			$this->_data = $db->loadObject();

			// Convert metadata fields to objects.
			$registry = new JRegistry;
			$registry->loadJSON($this->_data->metadata);
			$this->_data->metadata = $registry;

			return (boolean) $this->_data;
		}

		return true;
	}
}