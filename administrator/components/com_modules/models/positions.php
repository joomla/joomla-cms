<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Modules Component Positions Model
 *
 * @package		Joomla.Administrator
 * @subpackage	com_modules
 * @since		1.6
 */
class ModulesModelPositions extends JModelList
{
	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState()
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$state = $app->getUserStateFromRequest($this->context.'.filter.state', 'filter_state', '', 'string');
		$this->setState('filter.state', $state);

		$clientId = JRequest::getInt('client_id',0);
		$this->setState('filter.client_id',$clientId);

		$template = $app->getUserStateFromRequest($this->context.'.filter.template', 'filter_template', '', 'string');
		$this->setState('filter.template', $template);

		$type = $app->getUserStateFromRequest($this->context.'.filter.type', 'filter_type', '', 'string');
		$this->setState('filter.type', $type);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_modules');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('position', 'asc');
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return	mixed	An array of data items on success, false on failure.
	 * @since	1.6
	 */
	public function getItems()
	{
		if (!isset($this->items))
		{
			$lang				= JFactory::getLanguage();
			$search				= $this->getState('filter.search');
			$state				= $this->getState('filter.state');
			$clientId			= $this->getState('filter.client_id');
			$filter_template	= $this->getState('filter.template');
			$type				= $this->getState('filter.type');
			$ordering			= $this->getState('list.ordering');
			$direction			= $this->getState('list.direction');
			$limitstart			= $this->getState('list.start');
			$limit				= $this->getState('list.limit');
			$client				= JApplicationHelper::getClientInfo($clientId);

			if ($type!='template')
			{
				// Get the database object and a new query object.
				$query	= $this->_db->getQuery(true);
				$query->select('DISTINCT(position)');
				$query->from('#__modules');
				$query->where('`client_id` = '.(int) $clientId);
				if ($search) {
					$query->where('position LIKE '.$this->_db->Quote('%'.$this->_db->getEscaped($search, true).'%'));
				}

				$this->_db->setQuery($query);
				$positions = $this->_db->loadObjectList('position');
				// Check for a database error.
				if ($error = $this->_db->getErrorMsg()) {
					$this->setError($error);
					return false;
				}
				foreach ($positions as $position) {
					$position->templates = array();
				}
			}
			else
			{
				$positions=array();
			}

			// Load the positions from the installed templates.
			foreach (ModulesHelper::getTemplates($clientId) as $template)
			{
				$path = JPath::clean($client->path.'/templates/'.$template->element.'/templateDetails.xml');

				if (file_exists($path))
				{
					$xml = simplexml_load_file($path);
					if (isset($xml->positions[0]))
					{
						$lang->load('tpl_'.$template->element.'.sys', $client->path, null, false, false)
					||	$lang->load('tpl_'.$template->element.'.sys', $client->path.'/templates/'.$template->element, null, false, false)
					||	$lang->load('tpl_'.$template->element.'.sys', $client->path, $lang->getDefault(), false, false)
					||	$lang->load('tpl_'.$template->element.'.sys', $client->path.'/templates/'.$template->element, $lang->getDefault(), false, false);
						foreach ($xml->positions[0] as $position)
						{
							$position = (string)$position;
							if ($type=='user' || ($state!='' && $state!=$template->enabled)) {
								unset($positions[$position]);
							}
							elseif (preg_match(chr(1).$search.chr(1).'i', $position) && ($filter_template=='' || $filter_template==$template->element)) {
								if (!isset($positions[$position])) {
									$positions[$position] = new StdClass;
								}
								if (!isset($positions[$position]->templates)) {
									$positions[$position]->templates = array();
								}
								if (!isset($positions[$position]->position)) {
									$positions[$position]->position = $position;
								}
								$positions[$position]->templates[]=$template;
							}
						}
					}
				}
			}
			$this->total = count($positions);
			JArrayHelper::sortObjects($positions ,array($ordering,'position'), array($direction == 'desc' ? -1 : 1, 1));
			$this->items = array_slice($positions, $limitstart, $limit ? $limit : null);;
		}
		return $this->items;
	}

	/**
	 * Method to get the total number of items.
	 *
	 * @return	int	The total number of items.
	 * @since	1.6
	 */
	public function getTotal()
	{
		if (!isset($this->total))
		{
			$this->getItems();
		}
		return $this->total;
	}
}
