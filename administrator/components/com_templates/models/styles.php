<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');
jimport('joomla.database.query');

/**
 * Methods supporting a list of template style records.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_templates
 * @since		1.6
 */
class TemplatesModelStyles extends JModelList
{
	/**
	 * Model context string.
	 *
	 * @var		string
	 */
	protected $_context = 'com_templates.styles';

	/**
	 * Method to auto-populate the model state.
	 */
	protected function _populateState()
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->_context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$template = $app->getUserStateFromRequest($this->_context.'.filter.template', 'filter_template', '0', 'word');
		$this->setState('filter.template', $template);

		$clientId = $app->getUserStateFromRequest($this->_context.'.filter.client_id', 'filter_client_id', null);
		$this->setState('filter.client_id', $clientId);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_templates');
		$this->setState('params', $params);

		// List state information.
		parent::_populateState('a.template', 'asc');
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
		$id	.= ':'.$this->getState('filter.search');
		$id	.= ':'.$this->getState('filter.template');
		$id	.= ':'.$this->getState('filter.client_id');

		return parent::_getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return	JQuery
	 */
	protected function _getListQuery()
	{
		// Create a new query object.
		$query = new JQuery;

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.id, a.template, a.title, a.home, a.client_id'
			)
		);
		$query->from('`#__template_styles` AS a');

		// Join on menus.
		$query->select('COUNT(m.template_style_id) AS assigned');
		$query->leftjoin('#__menu AS m ON m.template_style_id = a.id');
		$query->group('a.id');

		// Filter by template.
		if ($template = $this->getState('filter.template'))
		{
			$query->where('a.template = '.$this->_db->quote($template));
		}

		// Filter by client.
		$clientId = $this->getState('filter.client_id');
		if (is_numeric($clientId)) {
			$query->where('a.client_id = '.(int) $clientId);
		}

		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0) {
				$query->where('a.id = '.(int) substr($search, 3));
			}
			else
			{
				$search = $this->_db->Quote('%'.$this->_db->getEscaped($search, true).'%');
				$query->where('a.template LIKE '.$search.' OR a.title LIKE '.$search);
			}
		}

		// Add the list ordering clause.
		$query->order($this->_db->getEscaped($this->getState('list.ordering', 'a.name')).' '.$this->_db->getEscaped($this->getState('list.direction', 'ASC')));

		//echo nl2br(str_replace('#__','jos_',$query));
		return $query;
	}
}
