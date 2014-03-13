<?php
/**
 * @package    PatchTester
 *
 * @copyright  Copyright (C) 2011 - 2012 Ian MacLennan, Copyright (C) 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

/**
 * Methods supporting a list of pull requests.
 *
 * @package  PatchTester
 * @since    1.0
 */
class PatchtesterModelPulls extends JModelList
{
	/**
	 * Github object
	 *
	 * @var    PTGithub
	 * @since  2.0
	 */
	protected $github;

	/**
	 * Object containing the rate limit data
	 *
	 * @var    object
	 * @since  2.0
	 */
	protected $rate;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JController
	 * @since   1.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'id',
				'title', 'title',
				'updated_at', 'updated_at',
				'user', 'user'
			);
		}

		parent::__construct($config);

		// Set up the Github object
		$params = JComponentHelper::getParams('com_patchtester');

		$options = new JRegistry;

		// Set the username and password if set in the params
		if ($params->get('gh_user', '') && $params->get('gh_password'))
		{
			$options->set('api.username', $params->get('gh_user', ''));
			$options->set('api.password', $params->get('gh_password', ''));
		}
		else
		{
			// Display a message about the lowered API limit without credentials
			JFactory::getApplication()->enqueueMessage(JText::_('COM_PATCHTESTER_NO_CREDENTIALS'), 'notice');
		}

		$this->github = new PTGithub($options);

		// Store the rate data for reuse during this request cycle
		$this->rate = $this->github->account->getRateLimit()->rate;

		// Check the API rate limit, display a message if over
		if ($this->rate->remaining == 0)
		{
			JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_PATCHTESTER_API_LIMIT_LIST', JFactory::getDate($this->rate->reset)), 'notice');
		}
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @note    Calling getState() in this method will result in recursion.
	 * @since   1.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '');
		$this->setState('filter.search', $search);

		$searchId = $this->getUserStateFromRequest($this->context . '.filter.searchid', 'filter_searchid', '');
		$this->setState('filter.searchid', $searchId);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_patchtester');

		$this->setState('params', $params);
		$this->setState('github_user', $params->get('org', 'joomla'));
		$this->setState('github_repo', $params->get('repo', 'joomla-cms'));

		// List state information.
		parent::populateState('number', 'desc');

		// GitHub's default list limit is 30
		$this->setState('list.limit', 30);
	}

	/**
	 * Retrieves a list of applied patches
	 *
	 * @return  mixed
	 *
	 * @since   1.0
	 */
	public function getAppliedPatches()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from('#__patchtester_tests')
			->where('applied = 1');

		$db->setQuery($query);

		try
		{
			$tests = $db->loadObjectList('pull_id');

			return $tests;
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   1.0
	 */
	public function getItems()
	{
		if ($this->getState('github_user') == '' || $this->getState('github_repo') == '')
		{
			return array();
		}

		$this->ordering = $this->getState('list.ordering', 'title');
		$this->orderDir = $this->getState('list.direction', 'asc');

		try
		{
			$cacheFile  = JPATH_CACHE . '/patchtester-page-' . $this->getPagination()->pagesCurrent . '.json';
			$params     = $this->getState('params');
			$searchId   = $this->getState('filter.searchid');
			$searchWord = $this->getState('filter.search');

			// Check if caching is enabled or that we aren't filtering
			if ($params->get('cache', 1) == 1 && $searchId != '' && $searchWord != '')
			{
				// Fetch cache time from component parameters and convert to seconds
				$cacheTime = $params->get('cache_lifetime', 60);
				$cacheTime = $cacheTime * 60;

				// Cache files expired?
				if (!file_exists($cacheFile) || (time() - @filemtime($cacheFile) > $cacheTime))
				{
					// Do a request to the GitHub API for new data
					$pulls = $this->requestFromGithub();
				}
				else
				{
					// Render from the cached data
					$pulls = json_decode(file_get_contents($cacheFile));
				}
			}
			else
			{
				// No caching, request from GitHub
				$pulls = $this->requestFromGithub();
			}

			return $pulls;
		}
		catch (Exception $e)
		{
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');

			return array();
		}
	}

	/**
	 * Method to request new data from GitHub
	 *
	 * @return  array  Pull request data
	 *
	 * @since   2.0
	 */
	protected function requestFromGithub()
	{
		// If over the API limit, we can't build this list
		if ($this->rate->remaining > 0)
		{
			$page     = $this->getPagination()->pagesCurrent;
			$search   = $this->getState('filter.search');
			$searchId = $this->getState('filter.searchid');

			// Check if we're searching for a single PR
			if ($searchId != '' && $search == '')
			{
				$pulls = array();
				$pulls[0] = $this->github->pulls->get($this->getState('github_user'), $this->getState('github_repo'), $searchId);
			}
			else
			{
				$pulls = $this->github->pulls->getList($this->getState('github_user'), $this->getState('github_repo'), 'open', $page);
				usort($pulls, array($this, 'sortItems'));
			}

			foreach ($pulls as $i => &$pull)
			{
				if ($search && false === strpos($pull->title, $search))
				{
					unset($pulls[$i]);
					continue;
				}

				// Try to find a Joomlacode issue number
				$pulls[$i]->joomlacode_issue = 0;

				$matches = array();

				preg_match('#\[\#([0-9]+)\]#', $pull->title, $matches);

				if (isset($matches[1]))
				{
					$pulls[$i]->joomlacode_issue = (int) $matches[1];
				}
				else
				{
					preg_match('#(http://joomlacode[-\w\./\?\S]+)#', $pull->body, $matches);

					if (isset($matches[1]))
					{
						preg_match('#tracker_item_id=([0-9]+)#', $matches[1], $matches);

						if (isset($matches[1]))
						{
							$pulls[$i]->joomlacode_issue = (int) $matches[1];
						}
					}
				}
			}

			// If caching is enabled, save the request data
			$params = $this->getState('params');

			if ($params->get('cache', 1) == 1 && $searchId != '' && $search != '')
			{
				$data = json_encode($pulls);
				file_put_contents(JPATH_CACHE . '/patchtester-page-' . $this->getPagination()->pagesCurrent . '.json', $data);
			}
		}
		else
		{
			$pulls = array();
		}

		return $pulls;
	}

	/**
	 * Method to sort the items array
	 *
	 * @param   object  $a  First sort object
	 * @param   object  $b  Second sort object
	 *
	 * @return  mixed
	 *
	 * @since   1.0
	 */
	public function sortItems($a, $b)
	{
		switch ($this->ordering)
		{
			case 'title' :
				return ($this->orderDir == 'asc') ? strcasecmp($a->title, $b->title) : strcasecmp($b->title, $a->title);

			case 'number' :
			default :
				return ($this->orderDir == 'asc') ? $b->number < $a->number : $b->number > $a->number;
		}
	}

	/**
	 * Method to get the total number of items for the data set.
	 *
	 * @return  integer  The total number of items available in the data set.
	 *
	 * @since   2.0
	 */
	public function getTotal()
	{
		if ($this->rate->remaining > 0)
		{
			return $this->github->repos->get('joomla', 'joomla-cms')->open_issues_count;
		}
		else
		{
			return 0;
		}
	}
}
