<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	Content
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.model');

/**
 * Newsfeeds Component Newsfeed Model
 *
 * @package		Joomla.Site
 * @subpackage	Newsfeeds
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
	function __construct()
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
	function setId($id)
	{
		// Set weblink id and wipe data
		$this->_id	 = $id;
		$this->_data = null;
	}

	/**
	 * Method to get the newsfeed data
	 *
	 * @since 1.5
	 */
	function &getData()
	{
		// Load the weblink data
		if ($this->_loadData())
		{
			// Initialize some variables
			$user	= &JFactory::getUser();
			$groups	= $user->authorisedLevels();

			// Make sure the category is published
			if (!$this->_data->published) {
				JError::raiseError(404, JText::_("Resource Not Found"));
				return false;
			}

			// Check to see if the category is published
			if (!$this->_data->cat_pub) {
				JError::raiseError(404, JText::_("Resource Not Found"));
				return;
			}

			// Check whether category access level allows access
			if (!in_array($this->_data->cat_access, $groups)) {
				JError::raiseError(403, JText::_('ALERTNOTAUTH'));
				return;
			}

		}

		return $this->_data;
	}

	/**
	 * Method to load newsfeed data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function _loadData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$query = 'SELECT f.*, cc.title AS category,'.
					' cc.published AS cat_pub, cc.access AS cat_access,'.
					' CASE WHEN CHAR_LENGTH(cc.alias) THEN CONCAT_WS(\':\', cc.id, cc.alias) ELSE cc.id END as catslug'.
					' FROM #__newsfeeds AS f' .
					' LEFT JOIN #__categories AS cc ON cc.id = f.catid' .
					' WHERE f.id = '.$this->_id;
			$this->_db->setQuery($query);
			$this->_data = $this->_db->loadObject();
			return (boolean) $this->_data;
		}
		return true;
	}

}
?>
