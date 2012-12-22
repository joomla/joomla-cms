<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_tags
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * This models supports retrieving a list of tags.
 *
 * @package     Joomla.Site
 * @subpackage  com_tags
 * @since       3.1
 */
class TagsModelTags extends JModelLegacy
{
	/**
	 * Model context string.
	 *
	 * @var		string
	 */
	public $_context = 'com_tags.tags';

	private $_items = null;

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since  3.1
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication();

		// Get the parent id if defined.
		$parentId = $app->input->getInt('id');
		$this->setState('filter.parentId', $parentId);

		$params = $app->getParams();
		$this->setState('params', $params);

		$this->setState('filter.published',	1);
		$this->setState('filter.access',	true);
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string   $id   A prefix for the store id.
	 *
	 * @return  string   A store id.
	 *
	 * @since   3.1
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':'.$this->getState('filter.published');
		$id	.= ':'.$this->getState('filter.access');
		$id	.= ':'.$this->getState('filter.parentId');

		return parent::getStoreId($id);
	}

	/**
	 * Redefine the function and add some properties to make the styling more easy
	 *
	 * @return mixed An array of data items on success, false on failure.
	 *
	 * @since   3.1
	 */
	public function getItems()
	{
		if(!count($this->_items))
		{
			$app = JFactory::getApplication();
			$menu = $app->getMenu();
			$active = $menu->getActive();
			$params = new JRegistry;
			if($active)
			{
				$params->loadString($active->params);
			}

			$options = array();
		}

		return $this->_items;
	}
}
