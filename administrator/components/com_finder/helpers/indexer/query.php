<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;

JLoader::register('FinderIndexerHelper', __DIR__ . '/helper.php');
JLoader::register('FinderIndexerTaxonomy', __DIR__ . '/taxonomy.php');
JLoader::register('FinderHelperRoute', JPATH_SITE . '/components/com_finder/helpers/route.php');
JLoader::register('FinderHelperLanguage', JPATH_ADMINISTRATOR . '/components/com_finder/helpers/language.php');

/**
 * Query class for the Finder indexer package.
 *
 * @since  2.5
 */
class FinderIndexerQuery
{
	/**
	 * Flag to show whether the query can return results.
	 *
	 * @var    boolean
	 * @since  2.5
	 */
	public $search;

	/**
	 * The query input string.
	 *
	 * @var    string
	 * @since  2.5
	 */
	public $input;

	/**
	 * The language of the query.
	 *
	 * @var    string
	 * @since  2.5
	 */
	public $language;

	/**
	 * The query string matching mode.
	 *
	 * @var    string
	 * @since  2.5
	 */
	public $mode;

	/**
	 * The included tokens.
	 *
	 * @var    array
	 * @since  2.5
	 */
	public $included = array();

	/**
	 * The excluded tokens.
	 *
	 * @var    array
	 * @since  2.5
	 */
	public $excluded = array();

	/**
	 * The tokens to ignore because no matches exist.
	 *
	 * @var    array
	 * @since  2.5
	 */
	public $ignored = array();

	/**
	 * The operators used in the query input string.
	 *
	 * @var    array
	 * @since  2.5
	 */
	public $operators = array();

	/**
	 * The terms to highlight as matches.
	 *
	 * @var    array
	 * @since  2.5
	 */
	public $highlight = array();

	/**
	 * The number of matching terms for the query input.
	 *
	 * @var    integer
	 * @since  2.5
	 */
	public $terms;

	/**
	 * The static filter id.
	 *
	 * @var    string
	 * @since  2.5
	 */
	public $filter;

	/**
	 * The taxonomy filters. This is a multi-dimensional array of taxonomy
	 * branches as the first level and then the taxonomy nodes as the values.
	 *
	 * For example:
	 * $filters = array(
	 *     'Type' = array(10, 32, 29, 11, ...);
	 *     'Label' = array(20, 314, 349, 91, 82, ...);
	 *        ...
	 * );
	 *
	 * @var    array
	 * @since  2.5
	 */
	public $filters = array();

	/**
	 * The start date filter.
	 *
	 * @var    string
	 * @since  2.5
	 */
	public $date1;

	/**
	 * The end date filter.
	 *
	 * @var    string
	 * @since  2.5
	 */
	public $date2;

	/**
	 * The start date filter modifier.
	 *
	 * @var    string
	 * @since  2.5
	 */
	public $when1;

	/**
	 * The end date filter modifier.
	 *
	 * @var    string
	 * @since  2.5
	 */
	public $when2;

	/**
	 * Method to instantiate the query object.
	 *
	 * @param   array  $options  An array of query options.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	public function __construct($options)
	{
		// Get the input string.
		$this->input = isset($options['input']) ? $options['input'] : null;

		// Get the empty query setting.
		$this->empty = isset($options['empty']) ? (bool) $options['empty'] : false;

		// Get the input language.
		$this->language = !empty($options['language']) ? $options['language'] : FinderIndexerHelper::getDefaultLanguage();
		$this->language = FinderIndexerHelper::getPrimaryLanguage($this->language);

		// Get the matching mode.
		$this->mode = 'AND';

		// Initialize the temporary date storage.
		$this->dates = new Registry;

		// Populate the temporary date storage.
		if (isset($options['date1']) && !empty($options['date1']))
		{
			$this->dates->set('date1', $options['date1']);
		}

		if (isset($options['date2']) && !empty($options['date1']))
		{
			$this->dates->set('date2', $options['date2']);
		}

		if (isset($options['when1']) && !empty($options['date1']))
		{
			$this->dates->set('when1', $options['when1']);
		}

		if (isset($options['when2']) && !empty($options['date1']))
		{
			$this->dates->set('when2', $options['when2']);
		}

		// Process the static taxonomy filters.
		if (isset($options['filter']) && !empty($options['filter']))
		{
			$this->processStaticTaxonomy($options['filter']);
		}

		// Process the dynamic taxonomy filters.
		if (isset($options['filters']) && !empty($options['filters']))
		{
			$this->processDynamicTaxonomy($options['filters']);
		}

		// Get the date filters.
		$d1 = $this->dates->get('date1');
		$d2 = $this->dates->get('date2');
		$w1 = $this->dates->get('when1');
		$w2 = $this->dates->get('when2');

		// Process the date filters.
		if (!empty($d1) || !empty($d2))
		{
			$this->processDates($d1, $d2, $w1, $w2);
		}

		// Process the input string.
		$this->processString($this->input, $this->language, $this->mode);

		// Get the number of matching terms.
		foreach ($this->included as $token)
		{
			$this->terms += count($token->matches);
		}

		// Remove the temporary date storage.
		unset($this->dates);

		/*
		 * Lastly, determine whether this query can return a result set.
		 */
		// Check if we have a query string.
		if (!empty($this->input))
		{
			$this->search = true;
		}
		// Check if we can search without a query string.
		elseif ($this->empty && (!empty($this->filter) || !empty($this->filters) || !empty($this->date1) || !empty($this->date2)))
		{
			$this->search = true;
		}
		// We do not have a valid search query.
		else
		{
			$this->search = false;
		}
	}

	/**
	 * Method to convert the query object into a URI string.
	 *
	 * @param   string  $base  The base URI. [optional]
	 *
	 * @return  string  The complete query URI.
	 *
	 * @since   2.5
	 */
	public function toUri($base = null)
	{
		// Set the base if not specified.
		if (empty($base))
		{
			$base = 'index.php?option=com_finder&view=search';
		}

		// Get the base URI.
		$uri = JUri::getInstance($base);

		// Add the static taxonomy filter if present.
		if (!empty($this->filter))
		{
			$uri->setVar('f', $this->filter);
		}

		// Get the filters in the request.
		$t = JFactory::getApplication()->input->request->get('t', array(), 'array');

		// Add the dynamic taxonomy filters if present.
		if (!empty($this->filters))
		{
			foreach ($this->filters as $nodes)
			{
				foreach ($nodes as $node)
				{
					if (!in_array($node, $t))
					{
						continue;
					}

					$uri->setVar('t[]', $node);
				}
			}
		}

		// Add the input string if present.
		if (!empty($this->input))
		{
			$uri->setVar('q', $this->input);
		}

		// Add the start date if present.
		if (!empty($this->date1))
		{
			$uri->setVar('d1', $this->date1);
		}

		// Add the end date if present.
		if (!empty($this->date2))
		{
			$uri->setVar('d2', $this->date2);
		}

		// Add the start date modifier if present.
		if (!empty($this->when1))
		{
			$uri->setVar('w1', $this->when1);
		}

		// Add the end date modifier if present.
		if (!empty($this->when2))
		{
			$uri->setVar('w2', $this->when2);
		}

		// Add a menu item id if one is not present.
		if (!$uri->getVar('Itemid'))
		{
			// Get the menu item id.
			$query = array(
				'view' => $uri->getVar('view'),
				'f' => $uri->getVar('f'),
				'q' => $uri->getVar('q')
			);
			$item = FinderHelperRoute::getItemid($query);

			// Add the menu item id if present.
			if ($item !== null)
			{
				$uri->setVar('Itemid', $item);
			}
		}

		return $uri->toString(array('path', 'query'));
	}

	/**
	 * Method to get a list of excluded search term ids.
	 *
	 * @return  array  An array of excluded term ids.
	 *
	 * @since   2.5
	 */
	public function getExcludedTermIds()
	{
		$results = array();

		// Iterate through the excluded tokens and compile the matching terms.
		for ($i = 0, $c = count($this->excluded); $i < $c; $i++)
		{
			$results = array_merge($results, $this->excluded[$i]->matches);
		}

		// Sanitize the terms.
		$results = array_unique($results);

		return ArrayHelper::toInteger($results);
	}

	/**
	 * Method to get a list of included search term ids.
	 *
	 * @return  array  An array of included term ids.
	 *
	 * @since   2.5
	 */
	public function getIncludedTermIds()
	{
		$results = array();

		// Iterate through the included tokens and compile the matching terms.
		for ($i = 0, $c = count($this->included); $i < $c; $i++)
		{
			// Check if we have any terms.
			if (empty($this->included[$i]->matches))
			{
				continue;
			}

			// Get the term.
			$term = $this->included[$i]->term;

			// Prepare the container for the term if necessary.
			if (!array_key_exists($term, $results))
			{
				$results[$term] = array();
			}

			// Add the matches to the stack.
			$results[$term] = array_merge($results[$term], $this->included[$i]->matches);
		}

		// Sanitize the terms.
		foreach ($results as $key => $value)
		{
			$results[$key] = array_unique($results[$key]);
			$results[$key] = ArrayHelper::toInteger($results[$key]);
		}

		return $results;
	}

	/**
	 * Method to get a list of required search term ids.
	 *
	 * @return  array  An array of required term ids.
	 *
	 * @since   2.5
	 */
	public function getRequiredTermIds()
	{
		$results = array();

		// Iterate through the included tokens and compile the matching terms.
		for ($i = 0, $c = count($this->included); $i < $c; $i++)
		{
			// Check if the token is required.
			if ($this->included[$i]->required)
			{
				// Get the term.
				$term = $this->included[$i]->term;

				// Prepare the container for the term if necessary.
				if (!array_key_exists($term, $results))
				{
					$results[$term] = array();
				}

				// Add the matches to the stack.
				$results[$term] = array_merge($results[$term], $this->included[$i]->matches);
			}
		}

		// Sanitize the terms.
		foreach ($results as $key => $value)
		{
			$results[$key] = array_unique($results[$key]);
			$results[$key] = ArrayHelper::toInteger($results[$key]);
		}

		return $results;
	}

	/**
	 * Method to process the static taxonomy input. The static taxonomy input
	 * comes in the form of a pre-defined search filter that is assigned to the
	 * search form.
	 *
	 * @param   integer  $filterId  The id of static filter.
	 *
	 * @return  boolean  True on success, false on failure.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	protected function processStaticTaxonomy($filterId)
	{
		// Get the database object.
		$db = JFactory::getDbo();

		// Initialize user variables
		$groups = implode(',', JFactory::getUser()->getAuthorisedViewLevels());

		// Load the predefined filter.
		$query = $db->getQuery(true)
			->select('f.data, f.params')
			->from($db->quoteName('#__finder_filters') . ' AS f')
			->where('f.filter_id = ' . (int) $filterId);

		$db->setQuery($query);
		$return = $db->loadObject();

		// Check the returned filter.
		if (empty($return))
		{
			return false;
		}

		// Set the filter.
		$this->filter = (int) $filterId;

		// Get a parameter object for the filter date options.
		$registry = new Registry($return->params);
		$params = $registry;

		// Set the dates if not already set.
		$this->dates->def('d1', $params->get('d1'));
		$this->dates->def('d2', $params->get('d2'));
		$this->dates->def('w1', $params->get('w1'));
		$this->dates->def('w2', $params->get('w2'));

		// Remove duplicates and sanitize.
		$filters = explode(',', $return->data);
		$filters = array_unique($filters);
		$filters = ArrayHelper::toInteger($filters);

		// Remove any values of zero.
		if (array_search(0, $filters, true) !== false)
		{
			unset($filters[array_search(0, $filters, true)]);
		}

		// Check if we have any real input.
		if (empty($filters))
		{
			return true;
		}

		/*
		 * Create the query to get filters from the database. We do this for
		 * two reasons: one, it allows us to ensure that the filters being used
		 * are real; two, we need to sort the filters by taxonomy branch.
		 */
		$query->clear()
			->select('t1.id, t1.title, t2.title AS branch')
			->from($db->quoteName('#__finder_taxonomy') . ' AS t1')
			->join('INNER', $db->quoteName('#__finder_taxonomy') . ' AS t2 ON t2.id = t1.parent_id')
			->where('t1.state = 1')
			->where('t1.access IN (' . $groups . ')')
			->where('t1.id IN (' . implode(',', $filters) . ')')
			->where('t2.state = 1')
			->where('t2.access IN (' . $groups . ')');

		// Load the filters.
		$db->setQuery($query);
		$results = $db->loadObjectList();

		// Sort the filter ids by branch.
		foreach ($results as $result)
		{
			$this->filters[$result->branch][$result->title] = (int) $result->id;
		}

		return true;
	}

	/**
	 * Method to process the dynamic taxonomy input. The dynamic taxonomy input
	 * comes in the form of select fields that the user chooses from. The
	 * dynamic taxonomy input is processed AFTER the static taxonomy input
	 * because the dynamic options can be used to further narrow a static
	 * taxonomy filter.
	 *
	 * @param   array  $filters  An array of taxonomy node ids.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	protected function processDynamicTaxonomy($filters)
	{
		// Initialize user variables
		$groups = implode(',', JFactory::getUser()->getAuthorisedViewLevels());

		// Remove duplicates and sanitize.
		$filters = array_unique($filters);
		$filters = ArrayHelper::toInteger($filters);

		// Remove any values of zero.
		if (array_search(0, $filters, true) !== false)
		{
			unset($filters[array_search(0, $filters, true)]);
		}

		// Check if we have any real input.
		if (empty($filters))
		{
			return true;
		}

		// Get the database object.
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		/*
		 * Create the query to get filters from the database. We do this for
		 * two reasons: one, it allows us to ensure that the filters being used
		 * are real; two, we need to sort the filters by taxonomy branch.
		 */
		$query->select('t1.id, t1.title, t2.title AS branch')
			->from($db->quoteName('#__finder_taxonomy') . ' AS t1')
			->join('INNER', $db->quoteName('#__finder_taxonomy') . ' AS t2 ON t2.id = t1.parent_id')
			->where('t1.state = 1')
			->where('t1.access IN (' . $groups . ')')
			->where('t1.id IN (' . implode(',', $filters) . ')')
			->where('t2.state = 1')
			->where('t2.access IN (' . $groups . ')');

		// Load the filters.
		$db->setQuery($query);
		$results = $db->loadObjectList();

		// Cleared filter branches.
		$cleared = array();

		/*
		 * Sort the filter ids by branch. Because these filters are designed to
		 * override and further narrow the items selected in the static filter,
		 * we will clear the values from the static filter on a branch by
		 * branch basis before adding the dynamic filters. So, if the static
		 * filter defines a type filter of "articles" and three "category"
		 * filters but the user only limits the category further, the category
		 * filters will be flushed but the type filters will not.
		 */
		foreach ($results as $result)
		{
			// Check if the branch has been cleared.
			if (!in_array($result->branch, $cleared))
			{
				// Clear the branch.
				$this->filters[$result->branch] = array();

				// Add the branch to the cleared list.
				$cleared[] = $result->branch;
			}

			// Add the filter to the list.
			$this->filters[$result->branch][$result->title] = (int) $result->id;
		}

		return true;
	}

	/**
	 * Method to process the query date filters to determine start and end
	 * date limitations.
	 *
	 * @param   string  $date1  The first date filter.
	 * @param   string  $date2  The second date filter.
	 * @param   string  $when1  The first date modifier.
	 * @param   string  $when2  The second date modifier.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 */
	protected function processDates($date1, $date2, $when1, $when2)
	{
		// Clean up the inputs.
		$date1 = trim(StringHelper::strtolower($date1));
		$date2 = trim(StringHelper::strtolower($date2));
		$when1 = trim(StringHelper::strtolower($when1));
		$when2 = trim(StringHelper::strtolower($when2));

		// Get the time offset.
		$offset = JFactory::getApplication()->get('offset');

		// Array of allowed when values.
		$whens = array('before', 'after', 'exact');

		// The value of 'today' is a special case that we need to handle.
		if ($date1 === StringHelper::strtolower(JText::_('COM_FINDER_QUERY_FILTER_TODAY')))
		{
			$date1 = JFactory::getDate('now', $offset)->format('%Y-%m-%d');
		}

		// Try to parse the date string.
		$date = JFactory::getDate($date1, $offset);

		// Check if the date was parsed successfully.
		if ($date->toUnix() !== null)
		{
			// Set the date filter.
			$this->date1 = $date->toSql();
			$this->when1 = in_array($when1, $whens) ? $when1 : 'before';
		}

		// The value of 'today' is a special case that we need to handle.
		if ($date2 === StringHelper::strtolower(JText::_('COM_FINDER_QUERY_FILTER_TODAY')))
		{
			$date2 = JFactory::getDate('now', $offset)->format('%Y-%m-%d');
		}

		// Try to parse the date string.
		$date = JFactory::getDate($date2, $offset);

		// Check if the date was parsed successfully.
		if ($date->toUnix() !== null)
		{
			// Set the date filter.
			$this->date2 = $date->toSql();
			$this->when2 = in_array($when2, $whens) ? $when2 : 'before';
		}

		return true;
	}

	/**
	 * Method to process the query input string and extract required, optional,
	 * and excluded tokens; taxonomy filters; and date filters.
	 *
	 * @param   string  $input  The query input string.
	 * @param   string  $lang   The query input language.
	 * @param   string  $mode   The query matching mode.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	protected function processString($input, $lang, $mode)
	{
		// Clean up the input string.
		$input = html_entity_decode($input, ENT_QUOTES, 'UTF-8');
		$input = StringHelper::strtolower($input);
		$input = preg_replace('#\s+#mi', ' ', $input);
		$input = trim($input);
		$debug = JFactory::getConfig()->get('debug_lang');

		/*
		 * First, we need to handle string based modifiers. String based
		 * modifiers could potentially include things like "category:blah" or
		 * "before:2009-10-21" or "type:article", etc.
		 */
		$patterns = array(
			'before' => JText::_('COM_FINDER_FILTER_WHEN_BEFORE'),
			'after' => JText::_('COM_FINDER_FILTER_WHEN_AFTER')
		);

		// Add the taxonomy branch titles to the possible patterns.
		foreach (FinderIndexerTaxonomy::getBranchTitles() as $branch)
		{
			// Add the pattern.
			$patterns[$branch] = StringHelper::strtolower(JText::_(FinderHelperLanguage::branchSingular($branch)));
		}

		// Container for search terms and phrases.
		$terms = array();
		$phrases = array();

		// Cleared filter branches.
		$cleared = array();

		/*
		 * Compile the suffix pattern. This is used to match the values of the
		 * filter input string. Single words can be input directly, multi-word
		 * values have to be wrapped in double quotes.
		 */
		$quotes = html_entity_decode('&#8216;&#8217;&#39;', ENT_QUOTES, 'UTF-8');
		$suffix = '(([\w\d' . $quotes . '-]+)|\"([\w\d\s' . $quotes . '-]+)\")';

		/*
		 * Iterate through the possible filter patterns and search for matches.
		 * We need to match the key, colon, and a value pattern for the match
		 * to be valid.
		 */
		foreach ($patterns as $modifier => $pattern)
		{
			$matches = array();

			if ($debug)
			{
				$pattern = substr($pattern, 2, -2);
			}

			// Check if the filter pattern is in the input string.
			if (preg_match('#' . $pattern . '\s*:\s*' . $suffix . '#mi', $input, $matches))
			{
				// Get the value given to the modifier.
				$value = isset($matches[3]) ? $matches[3] : $matches[1];

				// Now we have to handle the filter string.
				switch ($modifier)
				{
					// Handle a before and after date filters.
					case 'before':
					case 'after':
					{
						// Get the time offset.
						$offset = JFactory::getApplication()->get('offset');

						// Array of allowed when values.
						$whens = array('before', 'after', 'exact');

						// The value of 'today' is a special case that we need to handle.
						if ($value === StringHelper::strtolower(JText::_('COM_FINDER_QUERY_FILTER_TODAY')))
						{
							$value = JFactory::getDate('now', $offset)->format('%Y-%m-%d');
						}

						// Try to parse the date string.
						$date = JFactory::getDate($value, $offset);

						// Check if the date was parsed successfully.
						if ($date->toUnix() !== null)
						{
							// Set the date filter.
							$this->date1 = $date->toSql();
							$this->when1 = in_array($modifier, $whens) ? $modifier : 'before';
						}

						break;
					}

					// Handle a taxonomy branch filter.
					default:
						{
						// Try to find the node id.
						$return = FinderIndexerTaxonomy::getNodeByTitle($modifier, $value);

						// Check if the node id was found.
						if ($return)
						{
							// Check if the branch has been cleared.
							if (!in_array($modifier, $cleared))
							{
								// Clear the branch.
								$this->filters[$modifier] = array();

								// Add the branch to the cleared list.
								$cleared[] = $modifier;
							}

							// Add the filter to the list.
							$this->filters[$modifier][$return->title] = (int) $return->id;
						}

						break;
						}
				}

				// Clean up the input string again.
				$input = str_replace($matches[0], '', $input);
				$input = preg_replace('#\s+#mi', ' ', $input);
				$input = trim($input);
			}
		}

		/*
		 * Extract the tokens enclosed in double quotes so that we can handle
		 * them as phrases.
		 */
		if (StringHelper::strpos($input, '"') !== false)
		{
			$matches = array();

			// Extract the tokens enclosed in double quotes.
			if (preg_match_all('#\"([^"]+)\"#mi', $input, $matches))
			{
				/*
				 * One or more phrases were found so we need to iterate through
				 * them, tokenize them as phrases, and remove them from the raw
				 * input string before we move on to the next processing step.
				 */
				foreach ($matches[1] as $key => $match)
				{
					// Find the complete phrase in the input string.
					$pos = StringHelper::strpos($input, $matches[0][$key]);
					$len = StringHelper::strlen($matches[0][$key]);

					// Add any terms that are before this phrase to the stack.
					if (trim(StringHelper::substr($input, 0, $pos)))
					{
						$terms = array_merge($terms, explode(' ', trim(StringHelper::substr($input, 0, $pos))));
					}

					// Strip out everything up to and including the phrase.
					$input = StringHelper::substr($input, $pos + $len);

					// Clean up the input string again.
					$input = preg_replace('#\s+#mi', ' ', $input);
					$input = trim($input);

					// Get the number of words in the phrase.
					$parts = explode(' ', $match);

					// Check if the phrase is longer than three words.
					if (count($parts) > 3)
					{
						/*
						 * If the phrase is longer than three words, we need to
						 * break it down into smaller chunks of phrases that
						 * are less than or equal to three words. We overlap
						 * the chunks so that we can ensure that a match is
						 * found for the complete phrase and not just portions
						 * of it.
						 */
						for ($i = 0, $c = count($parts); $i < $c; $i += 2)
						{
							// Set up the chunk.
							$chunk = array();

							// The chunk has to be assembled based on how many
							// pieces are available to use.
							switch ($c - $i)
							{
								/*
								 * If only one word is left, we can break from
								 * the switch and loop because the last word
								 * was already used at the end of the last
								 * chunk.
								 */
								case 1:
									break 2;

								// If there words are left, we use them both as
								// the last chunk of the phrase and we're done.
								case 2:
									$chunk[] = $parts[$i];
									$chunk[] = $parts[$i + 1];
									break;

								// If there are three or more words left, we
								// build a three word chunk and continue on.
								default:
									$chunk[] = $parts[$i];
									$chunk[] = $parts[$i + 1];
									$chunk[] = $parts[$i + 2];
									break;
							}

							// If the chunk is not empty, add it as a phrase.
							if (count($chunk))
							{
								$phrases[] = implode(' ', $chunk);
								$terms[] = implode(' ', $chunk);
							}
						}
					}
					else
					{
						// The phrase is <= 3 words so we can use it as is.
						$phrases[] = $match;
						$terms[] = $match;
					}
				}
			}
		}

		// Add the remaining terms if present.
		if (!empty($input))
		{
			$terms = array_merge($terms, explode(' ', $input));
		}

		// An array of our boolean operators. $operator => $translation
		$operators = array(
			'AND' => StringHelper::strtolower(JText::_('COM_FINDER_QUERY_OPERATOR_AND')),
			'OR' => StringHelper::strtolower(JText::_('COM_FINDER_QUERY_OPERATOR_OR')),
			'NOT' => StringHelper::strtolower(JText::_('COM_FINDER_QUERY_OPERATOR_NOT'))
		);

		// If language debugging is enabled you need to ignore the debug strings in matching.
		if (JDEBUG)
		{
			$debugStrings = array('**', '??');
			$operators = str_replace($debugStrings, '', $operators);
		}

		/*
		 * Iterate through the terms and perform any sorting that needs to be
		 * done based on boolean search operators. Terms that are before an
		 * and/or/not modifier have to be handled in relation to their operator.
		 */
		for ($i = 0, $c = count($terms); $i < $c; $i++)
		{
			// Check if the term is followed by an operator that we understand.
			if (isset($terms[$i + 1]) && in_array($terms[$i + 1], $operators))
			{
				// Get the operator mode.
				$op = array_search($terms[$i + 1], $operators);

				// Handle the AND operator.
				if ($op === 'AND' && isset($terms[$i + 2]))
				{
					// Tokenize the current term.
					$token = FinderIndexerHelper::tokenize($terms[$i], $lang, true);
					$token = $this->getTokenData($token);

					// Set the required flag.
					$token->required = true;

					// Add the current token to the stack.
					$this->included[] = $token;
					$this->highlight = array_merge($this->highlight, array_keys($token->matches));

					// Skip the next token (the mode operator).
					$this->operators[] = $terms[$i + 1];

					// Tokenize the term after the next term (current plus two).
					$other = FinderIndexerHelper::tokenize($terms[$i + 2], $lang, true);
					$other = $this->getTokenData($other);

					// Set the required flag.
					$other->required = true;

					// Add the token after the next token to the stack.
					$this->included[] = $other;
					$this->highlight = array_merge($this->highlight, array_keys($other->matches));

					// Remove the processed phrases if possible.
					if (($pk = array_search($terms[$i], $phrases)) !== false)
					{
						unset($phrases[$pk]);
					}

					if (($pk = array_search($terms[$i + 2], $phrases)) !== false)
					{
						unset($phrases[$pk]);
					}

					// Remove the processed terms.
					unset($terms[$i]);
					unset($terms[$i + 1]);
					unset($terms[$i + 2]);

					// Adjust the loop.
					$i += 2;
					continue;
				}
				// Handle the OR operator.
				elseif ($op === 'OR' && isset($terms[$i + 2]))
				{
					// Tokenize the current term.
					$token = FinderIndexerHelper::tokenize($terms[$i], $lang, true);
					$token = $this->getTokenData($token);

					// Set the required flag.
					$token->required = false;

					// Add the current token to the stack.
					if (count($token->matches))
					{
						$this->included[] = $token;
						$this->highlight = array_merge($this->highlight, array_keys($token->matches));
					}
					else
					{
						$this->ignored[] = $token;
					}

					// Skip the next token (the mode operator).
					$this->operators[] = $terms[$i + 1];

					// Tokenize the term after the next term (current plus two).
					$other = FinderIndexerHelper::tokenize($terms[$i + 2], $lang, true);
					$other = $this->getTokenData($other);

					// Set the required flag.
					$other->required = false;

					// Add the token after the next token to the stack.
					if (count($other->matches))
					{
						$this->included[] = $other;
						$this->highlight = array_merge($this->highlight, array_keys($other->matches));
					}
					else
					{
						$this->ignored[] = $other;
					}

					// Remove the processed phrases if possible.
					if (($pk = array_search($terms[$i], $phrases)) !== false)
					{
						unset($phrases[$pk]);
					}

					if (($pk = array_search($terms[$i + 2], $phrases)) !== false)
					{
						unset($phrases[$pk]);
					}

					// Remove the processed terms.
					unset($terms[$i]);
					unset($terms[$i + 1]);
					unset($terms[$i + 2]);

					// Adjust the loop.
					$i += 2;
					continue;
				}
			}
			// Handle an orphaned OR operator.
			elseif (isset($terms[$i + 1]) && array_search($terms[$i], $operators) === 'OR')
			{
				// Skip the next token (the mode operator).
				$this->operators[] = $terms[$i];

				// Tokenize the next term (current plus one).
				$other = FinderIndexerHelper::tokenize($terms[$i + 1], $lang, true);
				$other = $this->getTokenData($other);

				// Set the required flag.
				$other->required = false;

				// Add the token after the next token to the stack.
				if (count($other->matches))
				{
					$this->included[] = $other;
					$this->highlight = array_merge($this->highlight, array_keys($other->matches));
				}
				else
				{
					$this->ignored[] = $other;
				}

				// Remove the processed phrase if possible.
				if (($pk = array_search($terms[$i + 1], $phrases)) !== false)
				{
					unset($phrases[$pk]);
				}

				// Remove the processed terms.
				unset($terms[$i]);
				unset($terms[$i + 1]);

				// Adjust the loop.
				$i++;
				continue;
			}
			// Handle the NOT operator.
			elseif (isset($terms[$i + 1]) && array_search($terms[$i], $operators) === 'NOT')
			{
				// Skip the next token (the mode operator).
				$this->operators[] = $terms[$i];

				// Tokenize the next term (current plus one).
				$other = FinderIndexerHelper::tokenize($terms[$i + 1], $lang, true);
				$other = $this->getTokenData($other);

				// Set the required flag.
				$other->required = false;

				// Add the next token to the stack.
				if (count($other->matches))
				{
					$this->excluded[] = $other;
				}
				else
				{
					$this->ignored[] = $other;
				}

				// Remove the processed phrase if possible.
				if (($pk = array_search($terms[$i + 1], $phrases)) !== false)
				{
					unset($phrases[$pk]);
				}

				// Remove the processed terms.
				unset($terms[$i]);
				unset($terms[$i + 1]);

				// Adjust the loop.
				$i++;
				continue;
			}
		}

		/*
		 * Iterate through any search phrases and tokenize them. We handle
		 * phrases as autonomous units and do not break them down into two and
		 * three word combinations.
		 */
		for ($i = 0, $c = count($phrases); $i < $c; $i++)
		{
			// Tokenize the phrase.
			$token = FinderIndexerHelper::tokenize($phrases[$i], $lang, true);
			$token = $this->getTokenData($token);

			// Set the required flag.
			$token->required = true;

			// Add the current token to the stack.
			$this->included[] = $token;
			$this->highlight = array_merge($this->highlight, array_keys($token->matches));

			// Remove the processed term if possible.
			if (($pk = array_search($phrases[$i], $terms)) !== false)
			{
				unset($terms[$pk]);
			}

			// Remove the processed phrase.
			unset($phrases[$i]);
		}

		/*
		 * Handle any remaining tokens using the standard processing mechanism.
		 */
		if (!empty($terms))
		{
			// Tokenize the terms.
			$terms = implode(' ', $terms);
			$tokens = FinderIndexerHelper::tokenize($terms, $lang, false);

			// Make sure we are working with an array.
			$tokens = is_array($tokens) ? $tokens : array($tokens);

			// Get the token data and required state for all the tokens.
			foreach ($tokens as $token)
			{
				// Get the token data.
				$token = $this->getTokenData($token);

				// Set the required flag for the token.
				$token->required = $mode === 'AND' ? ($token->phrase ? false : true) : false;

				// Add the token to the appropriate stack.
				if (count($token->matches) || $token->required)
				{
					$this->included[] = $token;
					$this->highlight = array_merge($this->highlight, array_keys($token->matches));
				}
				else
				{
					$this->ignored[] = $token;
				}
			}
		}

		return true;
	}

	/**
	 * Method to get the base and similar term ids and, if necessary, suggested
	 * term data from the database. The terms ids are identified based on a
	 * 'like' match in MySQL and/or a common stem. If no term ids could be
	 * found, then we know that we will not be able to return any results for
	 * that term and we should try to find a similar term to use that we can
	 * match so that we can suggest the alternative search query to the user.
	 *
	 * @param   FinderIndexerToken  $token  A FinderIndexerToken object.
	 *
	 * @return  FinderIndexerToken  A FinderIndexerToken object.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	protected function getTokenData($token)
	{
		// Get the database object.
		$db = JFactory::getDbo();

		// Create a database query to build match the token.
		$query = $db->getQuery(true)
			->select('t.term, t.term_id')
			->from('#__finder_terms AS t');

		/*
		 * If the token is a phrase, the lookup process is fairly simple. If
		 * the token is a word, it is a little more complicated. We have to
		 * create two queries to lookup the term and the stem respectively,
		 * then union the result sets together. This is MUCH faster than using
		 * an or condition in the database query.
		 */
		if ($token->phrase)
		{
			// Add the phrase to the query.
			$query->where('t.term = ' . $db->quote($token->term))
				->where('t.phrase = 1');
		}
		else
		{
			// Add the term to the query.
			$query->where('t.term = ' . $db->quote($token->term))
				->where('t.phrase = 0');

			// Clone the query, replace the WHERE clause.
			$sub = clone $query;
			$sub->clear('where');
			$sub->where('t.stem = ' . $db->quote($token->stem));
			$sub->where('t.phrase = 0');

			// Union the two queries.
			$query->union($sub);
		}

		// Get the terms.
		$db->setQuery($query);
		$matches = $db->loadObjectList();

		// Setup the container.
		$token->matches = array();

		// Check the matching terms.
		if (!empty($matches))
		{
			// Add the matches to the token.
			for ($i = 0, $c = count($matches); $i < $c; $i++)
			{
				$token->matches[$matches[$i]->term] = (int) $matches[$i]->term_id;
			}
		}

		// If no matches were found, try to find a similar but better token.
		if (empty($token->matches))
		{
			// Create a database query to get the similar terms.
			// TODO: PostgreSQL doesn't support SOUNDEX out of the box
			$query->clear()
				->select('DISTINCT t.term_id AS id, t.term AS term')
				->from('#__finder_terms AS t')
				// ->where('t.soundex = ' . soundex($db->quote($token->term)))
				->where('t.soundex = SOUNDEX(' . $db->quote($token->term) . ')')
				->where('t.phrase = ' . (int) $token->phrase);

			// Get the terms.
			$db->setQuery($query);
			$results = $db->loadObjectList();

			// Check if any similar terms were found.
			if (empty($results))
			{
				return $token;
			}

			// Stack for sorting the similar terms.
			$suggestions = array();

			// Get the levnshtein distance for all suggested terms.
			foreach ($results as $sk => $st)
			{
				// Get the levenshtein distance between terms.
				$distance = levenshtein($st->term, $token->term);

				// Make sure the levenshtein distance isn't over 50.
				if ($distance < 50)
				{
					$suggestions[$sk] = $distance;
				}
			}

			// Sort the suggestions.
			asort($suggestions, SORT_NUMERIC);

			// Get the closest match.
			$keys = array_keys($suggestions);
			$key = $keys[0];

			// Add the suggested term.
			$token->suggestion = $results[$key]->term;
		}

		return $token;
	}
}
