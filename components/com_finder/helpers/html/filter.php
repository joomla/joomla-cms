<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

JLoader::register('FinderHelperLanguage', JPATH_ADMINISTRATOR . '/components/com_finder/helpers/language.php');

/**
 * Filter HTML Behaviors for Finder.
 *
 * @since  2.5
 */
abstract class JHtmlFilter
{
	/**
	 * Method to generate filters using the slider widget and decorated
	 * with the FinderFilter JavaScript behaviors.
	 *
	 * @param   array  $options  An array of configuration options. [optional]
	 *
	 * @return  mixed  A rendered HTML widget on success, null otherwise.
	 *
	 * @since   2.5
	 */
	public static function slider($options = array())
	{
		$db     = JFactory::getDbo();
		$query  = $db->getQuery(true);
		$user   = JFactory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$html   = '';
		$filter = null;

		// Get the configuration options.
		$filterId    = array_key_exists('filter_id', $options) ? $options['filter_id'] : null;
		$activeNodes = array_key_exists('selected_nodes', $options) ? $options['selected_nodes'] : array();
		$classSuffix = array_key_exists('class_suffix', $options) ? $options['class_suffix'] : '';

		// Load the predefined filter if specified.
		if (!empty($filterId))
		{
			$query->select('f.data, f.params')
				->from($db->quoteName('#__finder_filters') . ' AS f')
				->where('f.filter_id = ' . (int) $filterId);

			// Load the filter data.
			$db->setQuery($query);

			try
			{
				$filter = $db->loadObject();
			}
			catch (RuntimeException $e)
			{
				return null;
			}

			// Initialize the filter parameters.
			if ($filter)
			{
				$filter->params = new Registry($filter->params);
			}
		}

		// Build the query to get the branch data and the number of child nodes.
		$query->clear()
			->select('t.*, count(c.id) AS children')
			->from($db->quoteName('#__finder_taxonomy') . ' AS t')
			->join('INNER', $db->quoteName('#__finder_taxonomy') . ' AS c ON c.parent_id = t.id')
			->where('t.parent_id = 1')
			->where('t.state = 1')
			->where('t.access IN (' . $groups . ')')
			->group('t.id, t.parent_id, t.state, t.access, t.ordering, t.title, c.parent_id')
			->order('t.ordering, t.title');

		// Limit the branch children to a predefined filter.
		if ($filter)
		{
			$query->where('c.id IN(' . $filter->data . ')');
		}

		// Load the branches.
		$db->setQuery($query);

		try
		{
			$branches = $db->loadObjectList('id');
		}
		catch (RuntimeException $e)
		{
			return null;
		}

		// Check that we have at least one branch.
		if (count($branches) === 0)
		{
			return null;
		}

		$branch_keys = array_keys($branches);
		$html .= JHtml::_('bootstrap.startAccordion', 'accordion', array('parent' => true, 'active' => 'accordion-' . $branch_keys[0])
		);

		// Load plugin language files.
		FinderHelperLanguage::loadPluginLanguage();

		// Iterate through the branches and build the branch groups.
		foreach ($branches as $bk => $bv)
		{
			// If the multi-lang plugin is enabled then drop the language branch.
			if ($bv->title == 'Language' && JLanguageMultilang::isEnabled())
			{
				continue;
			}

			// Build the query to get the child nodes for this branch.
			$query->clear()
				->select('t.*')
				->from($db->quoteName('#__finder_taxonomy') . ' AS t')
				->where('t.parent_id = ' . (int) $bk)
				->where('t.state = 1')
				->where('t.access IN (' . $groups . ')')
				->order('t.ordering, t.title');

			// Self-join to get the parent title.
			$query->select('e.title AS parent_title')
				->join('LEFT', $db->quoteName('#__finder_taxonomy', 'e') . ' ON ' . $db->quoteName('e.id') . ' = ' . $db->quoteName('t.parent_id'));

			// Load the branches.
			$db->setQuery($query);

			try
			{
				$nodes = $db->loadObjectList('id');
			}
			catch (RuntimeException $e)
			{
				return null;
			}

			// Translate node titles if possible.
			$lang = JFactory::getLanguage();
			foreach ($nodes as $nk => $nv)
			{
				if (trim($nv->parent_title, '**') == 'Language')
				{
					$title = FinderHelperLanguage::branchLanguageTitle($nv->title);
				}
				else
				{
					$key = FinderHelperLanguage::branchPlural($nv->title);
					$title = $lang->hasKey($key) ? JText::_($key) : $nv->title;
				}
				$nodes[$nk]->title = $title;
			}

			// Adding slides
			$html .= JHtml::_('bootstrap.addSlide',
				'accordion',
				JText::sprintf('COM_FINDER_FILTER_BRANCH_LABEL',
					JText::_(FinderHelperLanguage::branchSingular($bv->title)) . ' - ' . count($nodes)
				),
				'accordion-' . $bk
			);

			// Populate the toggle button.
			$html .= '<button class="btn" type="button" class="jform-rightbtn" onclick="jQuery(\'[id="tax-'
				. $bk . '"]\').each(function(){this.click();});"><span class="icon-checkbox-partial"></span> '
				. JText::_('JGLOBAL_SELECTION_INVERT') . '</button><hr/>';

			// Populate the group with nodes.
			foreach ($nodes as $nk => $nv)
			{
				// Determine if the node should be checked.
				$checked = in_array($nk, $activeNodes) ? ' checked="checked"' : '';

				// Build a node.
				$html .= '<div class="control-group">';
				$html .= '<div class="controls">';
				$html .= '<label class="checkbox" tax-'
					. $bk . '>';
				$html .= '<input type="checkbox" class="selector filter-node' . $classSuffix . '" value="' . $nk . '" name="t[]" id="tax-'
					. $bk . '"' . $checked . ' />';
				$html .= $nv->title;
				$html .= '</label>';
				$html .= '</div>';
				$html .= '</div>';
			}
			$html .= JHtml::_('bootstrap.endSlide');
		}
		$html .= JHtml::_('bootstrap.endAccordion');

		return $html;
	}

	/**
	 * Method to generate filters using select box dropdown controls.
	 *
	 * @param   FinderIndexerQuery  $idxQuery  A FinderIndexerQuery object.
	 * @param   array               $options   An array of options.
	 *
	 * @return  mixed  A rendered HTML widget on success, null otherwise.
	 *
	 * @since   2.5
	 */
	public static function select($idxQuery, $options)
	{
		$user   = JFactory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$filter = null;

		// Get the configuration options.
		$classSuffix = $options->get('class_suffix', null);
		$showDates   = $options->get('show_date_filters', false);

		// Try to load the results from cache.
		$cache   = JFactory::getCache('com_finder', '');
		$cacheId = 'filter_select_' . serialize(array($idxQuery->filter, $options, $groups, JFactory::getLanguage()->getTag()));

		// Check the cached results.
		if ($cache->contains($cacheId))
		{
			$branches = $cache->get($cacheId);
		}
		else
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			// Load the predefined filter if specified.
			if (!empty($idxQuery->filter))
			{
				$query->select('f.data, ' . $db->quoteName('f.params'))
					->from($db->quoteName('#__finder_filters') . ' AS f')
					->where('f.filter_id = ' . (int) $idxQuery->filter);

				// Load the filter data.
				$db->setQuery($query);

				try
				{
					$filter = $db->loadObject();
				}
				catch (RuntimeException $e)
				{
					return null;
				}

				// Initialize the filter parameters.
				if ($filter)
				{
					$filter->params = new Registry($filter->params);
				}
			}

			// Build the query to get the branch data and the number of child nodes.
			$query->clear()
				->select('t.*, count(c.id) AS children')
				->from($db->quoteName('#__finder_taxonomy') . ' AS t')
				->join('INNER', $db->quoteName('#__finder_taxonomy') . ' AS c ON c.parent_id = t.id')
				->where('t.parent_id = 1')
				->where('t.state = 1')
				->where('t.access IN (' . $groups . ')')
				->where('c.state = 1')
				->where('c.access IN (' . $groups . ')')
				->group($db->quoteName('t.id'))
				->order('t.ordering, t.title');

			// Limit the branch children to a predefined filter.
			if (!empty($filter->data))
			{
				$query->where('c.id IN(' . $filter->data . ')');
			}

			// Load the branches.
			$db->setQuery($query);

			try
			{
				$branches = $db->loadObjectList('id');
			}
			catch (RuntimeException $e)
			{
				return null;
			}

			// Check that we have at least one branch.
			if (count($branches) === 0)
			{
				return null;
			}

			// Iterate through the branches and build the branch groups.
			foreach ($branches as $bk => $bv)
			{
				// If the multi-lang plugin is enabled then drop the language branch.
				if ($bv->title == 'Language' && JLanguageMultilang::isEnabled())
				{
					continue;
				}

				// Build the query to get the child nodes for this branch.
				$query->clear()
					->select('t.*')
					->from($db->quoteName('#__finder_taxonomy') . ' AS t')
					->where('t.parent_id = ' . (int) $bk)
					->where('t.state = 1')
					->where('t.access IN (' . $groups . ')')
					->order('t.ordering, t.title');

				// Self-join to get the parent title.
				$query->select('e.title AS parent_title')
					->join('LEFT', $db->quoteName('#__finder_taxonomy', 'e') . ' ON ' . $db->quoteName('e.id') . ' = ' . $db->quoteName('t.parent_id'));

				// Limit the nodes to a predefined filter.
				if (!empty($filter->data))
				{
					$query->where('t.id IN(' . $filter->data . ')');
				}

				// Load the branches.
				$db->setQuery($query);

				try
				{
					$branches[$bk]->nodes = $db->loadObjectList('id');
				}
				catch (RuntimeException $e)
				{
					return null;
				}

				// Translate branch nodes if possible.
				$language = JFactory::getLanguage();
				foreach ($branches[$bk]->nodes as $node_id => $node)
				{
					if (trim($node->parent_title, '**') == 'Language')
					{
						$title = FinderHelperLanguage::branchLanguageTitle($node->title);
					}
					else
					{
						$key = FinderHelperLanguage::branchPlural($node->title);
						$title = $language->hasKey($key) ? JText::_($key) : $node->title;
					}
					$branches[$bk]->nodes[$node_id]->title = $title;
				}

				// Add the Search All option to the branch.
				array_unshift($branches[$bk]->nodes, array('id' => null, 'title' => JText::_('COM_FINDER_FILTER_SELECT_ALL_LABEL')));
			}

			// Store the data in cache.
			$cache->store($branches, $cacheId);
		}
		$html = '';

		// Add the dates if enabled.
		if ($showDates)
		{
			$html .= JHtml::_('filter.dates', $idxQuery, $options);
		}

		$html .= '<div class="filter-branch' . $classSuffix . ' control-group clearfix">';

		// Iterate through all branches and build code.
		foreach ($branches as $bk => $bv)
		{
			// If the multi-lang plugin is enabled then drop the language branch.
			if ($bv->title == 'Language' && JLanguageMultilang::isEnabled())
			{
				continue;
			}
			$active = null;

			// Check if the branch is in the filter.
			if (array_key_exists($bv->title, $idxQuery->filters))
			{
				// Get the request filters.
				$temp   = JFactory::getApplication()->input->request->get('t', array(), 'array');

				// Search for active nodes in the branch and get the active node.
				$active = array_intersect($temp, $idxQuery->filters[$bv->title]);
				$active = count($active) === 1 ? array_shift($active) : null;
			}

			// Build a node.
			$html .= '<div class="controls finder-selects">';
			$html .= '<label for="tax-' . JFilterOutput::stringUrlSafe($bv->title) . '" class="control-label">';
			$html .= JText::sprintf('COM_FINDER_FILTER_BRANCH_LABEL', JText::_(FinderHelperLanguage::branchSingular($bv->title)));
			$html .= '</label>';
			$html .= '<br />';
			$html .= JHtml::_(
				'select.genericlist',
				$branches[$bk]->nodes, 't[]', 'class="inputbox advancedSelect"', 'id', 'title', $active,
				'tax-' . JFilterOutput::stringUrlSafe($bv->title)
			);
			$html .= '</div>';
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * Method to generate fields for filtering dates
	 *
	 * @param   FinderIndexerQuery  $idxQuery  A FinderIndexerQuery object.
	 * @param   array               $options   An array of options.
	 *
	 * @return  mixed  A rendered HTML widget on success, null otherwise.
	 *
	 * @since   2.5
	 */
	public static function dates($idxQuery, $options)
	{
		$html = '';

		// Get the configuration options.
		$classSuffix = $options->get('class_suffix', null);
		$loadMedia   = $options->get('load_media', true);
		$showDates   = $options->get('show_date_filters', false);

		if (!empty($showDates))
		{
			// Build the date operators options.
			$operators   = array();
			$operators[] = JHtml::_('select.option', 'before', JText::_('COM_FINDER_FILTER_DATE_BEFORE'));
			$operators[] = JHtml::_('select.option', 'exact', JText::_('COM_FINDER_FILTER_DATE_EXACTLY'));
			$operators[] = JHtml::_('select.option', 'after', JText::_('COM_FINDER_FILTER_DATE_AFTER'));

			// Load the CSS/JS resources.
			if ($loadMedia)
			{
				JHtml::_('stylesheet', 'com_finder/dates.css', array('version' => 'auto', 'relative' => true));
			}

			// Open the widget.
			$html .= '<ul id="finder-filter-select-dates">';

			// Start date filter.
			$attribs['class'] = 'input-medium';
			$html .= '<li class="filter-date' . $classSuffix . '">';
			$html .= '<label for="filter_date1" class="hasTooltip" title ="' . JText::_('COM_FINDER_FILTER_DATE1_DESC') . '">';
			$html .= JText::_('COM_FINDER_FILTER_DATE1');
			$html .= '</label>';
			$html .= '<br />';
			$html .= JHtml::_(
				'select.genericlist',
				$operators, 'w1', 'class="inputbox filter-date-operator advancedSelect"', 'value', 'text', $idxQuery->when1, 'finder-filter-w1'
			);
			$html .= JHtml::calendar($idxQuery->date1, 'd1', 'filter_date1', '%Y-%m-%d', $attribs);
			$html .= '</li>';

			// End date filter.
			$html .= '<li class="filter-date' . $classSuffix . '">';
			$html .= '<label for="filter_date2" class="hasTooltip" title ="' . JText::_('COM_FINDER_FILTER_DATE2_DESC') . '">';
			$html .= JText::_('COM_FINDER_FILTER_DATE2');
			$html .= '</label>';
			$html .= '<br />';
			$html .= JHtml::_(
				'select.genericlist',
				$operators, 'w2', 'class="inputbox filter-date-operator advancedSelect"', 'value', 'text', $idxQuery->when2, 'finder-filter-w2'
			);
			$html .= JHtml::calendar($idxQuery->date2, 'd2', 'filter_date2', '%Y-%m-%d', $attribs);
			$html .= '</li>';

			// Close the widget.
			$html .= '</ul>';
		}

		return $html;
	}
}
