<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * A command line cron job to run the Smart Search indexer.
 *
 * @since  3.5
 */
class FinderCommandIndex extends JControllerBase
{
	/**
	 * Start time for the index process
	 *
	 * @var    string
	 * @since  3.5
	 */
	private $time = null;

	/**
	 * Start time for each batch
	 *
	 * @var    string
	 * @since  3.5
	 */
	private $qtime = null;

	/**
	 * Static filters information.
	 *
	 * @var    array
	 * @since  3.3
	 */
	private $filters = array();

	/**
	 * Execute the controller.
	 *
	 * @return  boolean
	 *
	 * @since   3.5
	 */
	public function execute()
	{
		// Load Library language
		$lang = JFactory::getLanguage();

		// Try the finder_cli file in the current language (without allowing the loading of the file in the default language)
		$lang->load('finder_cli', JPATH_SITE, null, false, false)
		// Fallback to the finder_cli file in the default language
		|| $lang->load('finder_cli', JPATH_SITE, null, true);

		// Print a blank line.
		$this->getApplication()->out(JText::_('FINDER_CLI'));
		$this->getApplication()->out('============================');

		// Initialize the time value.
		$this->time = microtime(true);

		// Remove the script time limit.
		@set_time_limit(0);

		// Fool the system into thinking we are running as JApplicationSite with Smart Search as the active component.
		$_SERVER['HTTP_HOST'] = 'domain.com';
		JFactory::$application = JApplicationCms::getInstance('site');
		defined('JPATH_COMPONENT_ADMINISTRATOR') or define('JPATH_COMPONENT_ADMINISTRATOR', JPATH_ADMINISTRATOR . '/components/com_finder');

		// Purge before indexing if --purge on the command line.
		if ($this->getInput()->getString('purge', false))
		{
			// Taxonomy ids will change following a purge/index, so save filter information first.
			$this->getFilters();

			// Purge the index.
			$this->purge();

			// Run the indexer.
			$this->index();

			// Restore the filters again.
			$this->putFilters();
		}
		else
		{
			// Run the indexer.
			$this->index();
		}

		// Total reporting.
		$this->getApplication()->out(JText::sprintf('FINDER_CLI_PROCESS_COMPLETE', round(microtime(true) - $this->time, 3)), true);

		// Print a blank line at the end.
		$this->getApplication()->out();
	}

	/**
	 * Run the indexer.
	 *
	 * @return  void
	 *
	 * @since   3.5
	 */
	private function index()
	{
		require_once JPATH_ADMINISTRATOR . '/components/com_finder/helpers/indexer/indexer.php';

		// Disable caching.
		$config = JFactory::getConfig();
		$config->set('caching', 0);
		$config->set('cache_handler', 'file');

		// Reset the indexer state.
		FinderIndexer::resetState();

		// Import the finder plugins.
		JPluginHelper::importPlugin('finder');

		// Starting Indexer.
		$this->getApplication()->out(JText::_('FINDER_CLI_STARTING_INDEXER'), true);

		// Trigger the onStartIndex event.
		JEventDispatcher::getInstance()->trigger('onStartIndex');

		// Remove the script time limit.
		@set_time_limit(0);

		// Get the indexer state.
		$state = FinderIndexer::getState();

		// Setting up plugins.
		$this->getApplication()->out(JText::_('FINDER_CLI_SETTING_UP_PLUGINS'), true);

		// Trigger the onBeforeIndex event.
		JEventDispatcher::getInstance()->trigger('onBeforeIndex');

		// Startup reporting.
		$this->getApplication()->out(JText::sprintf('FINDER_CLI_SETUP_ITEMS', $state->totalItems, round(microtime(true) - $this->time, 3)), true);

		// Get the number of batches.
		$t = (int) $state->totalItems;
		$c = (int) ceil($t / $state->batchSize);
		$c = $c === 0 ? 1 : $c;

		try
		{
			// Process the batches.
			for ($i = 0; $i < $c; $i++)
			{
				// Set the batch start time.
				$this->qtime = microtime(true);

				// Reset the batch offset.
				$state->batchOffset = 0;

				// Trigger the onBuildIndex event.
				JEventDispatcher::getInstance()->trigger('onBuildIndex');

				// Batch reporting.
				$this->getApplication()->out(JText::sprintf('FINDER_CLI_BATCH_COMPLETE', ($i + 1), round(microtime(true) - $this->qtime, 3)), true);
			}
		}
		catch (Exception $e)
		{
			// Display the error
			$this->getApplication()->out($e->getMessage(), true);

			// Reset the indexer state.
			FinderIndexer::resetState();

			// Close the app
			$this->getApplication()->close($e->getCode());
		}

		// Reset the indexer state.
		FinderIndexer::resetState();
	}

	/**
	 * Purge the index.
	 *
	 * @return  void
	 *
	 * @since   3.5
	 */
	private function purge()
	{
		$this->getApplication()->out(JText::_('FINDER_CLI_INDEX_PURGE'));

		// Load the model.
		JModelLegacy::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/models', 'FinderModel');
		$model = JModelLegacy::getInstance('Index', 'FinderModel');

		// Attempt to purge the index.
		$return = $model->purge();

		// If unsuccessful then abort.
		if (!$return)
		{
			$message = JText::_('FINDER_CLI_INDEX_PURGE_FAILED', $model->getError());
			$this->getApplication()->out($message);
			exit();
		}

		$this->getApplication()->out(JText::_('FINDER_CLI_INDEX_PURGE_SUCCESS'));
	}

	/**
	 * Restore static filters.
	 *
	 * Using the saved filter information, update the filter records
	 * with the new taxonomy ids.
	 *
	 * @return  void
	 *
	 * @since   3.5
	 */
	private function putFilters()
	{
		$this->getApplication()->out(JText::_('FINDER_CLI_RESTORE_FILTERS'));

		$db = JFactory::getDbo();

		// Use the temporary filter information to update the filter taxonomy ids.
		foreach ($this->filters as $filter_id => $filter)
		{
			$tids = array();

			foreach ($filter as $element)
			{
				// Look for the old taxonomy in the new taxonomy table.
				$query = $db->getQuery(true);
				$query
					->select('t.id')
					->from($db->qn('#__finder_taxonomy') . ' AS t')
					->leftjoin($db->qn('#__finder_taxonomy') . ' AS p ON p.id = t.parent_id')
					->where($db->qn('t.title') . ' = ' . $db->q($element['title']))
					->where($db->qn('p.title') . ' = ' . $db->q($element['parent']));
				$taxonomy = $db->setQuery($query)->loadResult();

				// If we found it then add it to the list.
				if ($taxonomy)
				{
					$tids[] = $taxonomy;
				}
				else
				{
					$this->getApplication()->out(JText::sprintf('FINDER_CLI_FILTER_RESTORE_WARNING', $element['parent'], $element['title'], $element['filter']));
				}
			}

			// Construct a comma-separated string from the taxonomy ids.
			$taxonomyIds = empty($tids) ? '' : implode(',', $tids);

			// Update the filter with the new taxonomy ids.
			$query = $db->getQuery(true)
				->update($db->qn('#__finder_filters'))
				->set($db->qn('data') . ' = ' . $db->q($taxonomyIds))
				->where($db->qn('filter_id') . ' = ' . (int) $filter_id);
			$db->setQuery($query)->execute();
		}

		$this->getApplication()->out(JText::sprintf('FINDER_CLI_RESTORE_FILTER_COMPLETED', count($this->filters)));
	}

	/**
	 * Save static filters.
	 *
	 * Since a purge/index cycle will cause all the taxonomy ids to change,
	 * the static filters need to be updated with the new taxonomy ids.
	 * The static filter information is saved prior to the purge/index
	 * so that it can later be used to update the filters with new ids.
	 *
	 * @return  void
	 *
	 * @since   3.5
	 */
	private function getFilters()
	{
		$this->getApplication()->out(JText::_('FINDER_CLI_SAVE_FILTERS'));

		// Get the taxonomy ids used by the filters.
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('filter_id, title, data')
			->from($db->qn('#__finder_filters'));
		$filters = $db->setQuery($query)->loadObjectList();

		// Get the name of each taxonomy and the name of its parent.
		foreach ($filters as $filter)
		{
			// Skip empty filters.
			if ($filter->data == '')
			{
				continue;
			}

			// Get taxonomy records.
			$query = $db->getQuery(true)
				->select('t.title, p.title AS parent')
				->from($db->qn('#__finder_taxonomy') . ' AS t')
				->leftjoin($db->qn('#__finder_taxonomy') . ' AS p ON p.id = t.parent_id')
				->where($db->qn('t.id') . ' IN (' . $filter->data . ')');
			$taxonomies = $db->setQuery($query)->loadObjectList();

			// Construct a temporary data structure to hold the filter information.
			foreach ($taxonomies as $taxonomy)
			{
				$this->filters[$filter->filter_id][] = array(
					'filter'	=> $filter->title,
					'title'		=> $taxonomy->title,
					'parent'	=> $taxonomy->parent,
				);
			}
		}

		$this->getApplication()->out(JText::sprintf('FINDER_CLI_SAVE_FILTER_COMPLETED', count($filters)));
	}
}
