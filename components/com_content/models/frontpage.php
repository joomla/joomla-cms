<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

require_once dirname(__FILE__).DS.'articles.php';

/**
 * Frontpage Component Model
 *
 * @package		Joomla.Site
 * @subpackage	com_content
 * @since 1.5
 */
class ContentModelFrontpage extends ContentModelArticles
{
	/**
	 * Model context string.
	 *
	 * @var		string
	 */
	public $_context = 'com_content.frontpage';

	/**
	 * Method to auto-populate the model state.
	 *
	 * @since	1.6
	 */
	protected function _populateState()
	{
		parent::_populateState();

		$this->setState('filter.frontpage', true);
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param	string		$id	A prefix for the store id.
	 *
	 * @return	string		A store id.
	 */
	protected function _getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= $this->getState('filter.frontpage');

		return parent::_getStoreId($id);
	}

	/**
	 * @return	JQuery
	 */
	function _getListQuery()
	{
		// Create a new query object.
		$query = parent::_getListQuery();

		// Filter by frontpage.
		if ($this->getState('filter.frontpage')) {
			$query->join('INNER', '#__content_frontpage AS fp ON fp.content_id = a.id');
		}

		//echo nl2br(str_replace('#__','jos_',$query));
		return $query;
	}
}
