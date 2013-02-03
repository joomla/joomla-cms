<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

// Register dependent classes.
JLoader::register('FinderIndexerHelper', dirname(__FILE__) . '/helper.php');
JLoader::register('FinderIndexerParser', dirname(__FILE__) . '/parser.php');
JLoader::register('FinderIndexerStemmer', dirname(__FILE__) . '/stemmer.php');
JLoader::register('FinderIndexerTaxonomy', dirname(__FILE__) . '/taxonomy.php');
JLoader::register('FinderIndexerToken', dirname(__FILE__) . '/token.php');

jimport('joomla.filesystem.file');

/**
 * Main indexer class for the Finder indexer package.
 *
 * The indexer class provides the core functionality of the Finder
 * search engine. It is responsible for adding and updating the
 * content links table; extracting and scoring tokens; and maintaining
 * all referential information for the content.
 *
 * Note: All exceptions thrown from within this class should be caught
 * by the controller.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 * @since       2.5
 */
class FinderIndexer
{
	/**
	 * The title context identifier.
	 *
	 * @var    integer
	 * @since  2.5
	 */
	const TITLE_CONTEXT = 1;

	/**
	 * The text context identifier.
	 *
	 * @var    integer
	 * @since  2.5
	 */
	const TEXT_CONTEXT = 2;

	/**
	 * The meta context identifier.
	 *
	 * @var    integer
	 * @since  2.5
	 */
	const META_CONTEXT = 3;

	/**
	 * The path context identifier.
	 *
	 * @var    integer
	 * @since  2.5
	 */
	const PATH_CONTEXT = 4;

	/**
	 * The misc context identifier.
	 *
	 * @var    integer
	 * @since  2.5
	 */
	const MISC_CONTEXT = 5;

	/**
	 * The indexer state object.
	 *
	 * @var    object
	 * @since  2.5
	 */
	public static $state;

	/**
	 * The indexer profiler object.
	 *
	 * @var    object
	 * @since  2.5
	 */
	public static $profiler;

	/**
	 * Method to get the indexer state.
	 *
	 * @return  object  The indexer state object.
	 *
	 * @since   2.5
	 */
	public static function getState()
	{
		// First, try to load from the internal state.
		if (!empty(self::$state))
		{
			return self::$state;
		}

		// If we couldn't load from the internal state, try the session.
		$session = JFactory::getSession();
		$data = $session->get('_finder.state', null);

		// If the state is empty, load the values for the first time.
		if (empty($data))
		{
			$data = new JObject;

			// Load the default configuration options.
			$data->options = JComponentHelper::getParams('com_finder');

			// Setup the weight lookup information.
			$data->weights = array(
				self::TITLE_CONTEXT	=> round($data->options->get('title_multiplier', 1.7), 2),
				self::TEXT_CONTEXT	=> round($data->options->get('text_multiplier', 0.7), 2),
				self::META_CONTEXT	=> round($data->options->get('meta_multiplier', 1.2), 2),
				self::PATH_CONTEXT	=> round($data->options->get('path_multiplier', 2.0), 2),
				self::MISC_CONTEXT	=> round($data->options->get('misc_multiplier', 0.3), 2)
			);

			// Set the current time as the start time.
			$data->startTime = JFactory::getDate()->toSQL();

			// Set the remaining default values.
			$data->batchSize = (int) $data->options->get('batch_size', 50);
			$data->batchOffset = 0;
			$data->totalItems = 0;
			$data->pluginState = array();
		}

		// Setup the profiler if debugging is enabled.
		if (JFactory::getApplication()->getCfg('debug'))
		{
			jimport('joomla.error.profiler');
			self::$profiler = JProfiler::getInstance('FinderIndexer');
		}

		// Setup the stemmer.
		if ($data->options->get('stem', 1) && $data->options->get('stemmer', 'porter_en'))
		{
			FinderIndexerHelper::$stemmer = FinderIndexerStemmer::getInstance($data->options->get('stemmer', 'porter_en'));
		}

		// Set the state.
		self::$state = $data;

		return self::$state;
	}

	/**
	 * Method to set the indexer state.
	 *
	 * @param   object  $data  A new indexer state object.
	 *
	 * @return  boolean  True on success, false on failure.
	 *
	 * @since   2.5
	 */
	public static function setState($data)
	{
		// Check the state object.
		if (empty($data) || !$data instanceof JObject)
		{
			return false;
		}

		// Set the new internal state.
		self::$state = $data;

		// Set the new session state.
		$session = JFactory::getSession();
		$session->set('_finder.state', $data);

		return true;
	}

	/**
	 * Method to reset the indexer state.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public static function resetState()
	{
		// Reset the internal state to null.
		self::$state = null;

		// Reset the session state to null.
		$session = JFactory::getSession();
		$session->set('_finder.state', null);
	}

	/**
	 * Method to index a content item.
	 *
	 * @param   FinderIndexerResult  $item    The content item to index.
	 * @param   string               $format  The format of the content. [optional]
	 *
	 * @return  integer  The ID of the record in the links table.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	public static function index($item, $format = 'html')
	{
		// Mark beforeIndexing in the profiler.
		self::$profiler ? self::$profiler->mark('beforeIndexing') : null;
		$db = JFactory::getDBO();
		$nd = $db->getNullDate();

		// Check if the item is in the database.
		$query = $db->getQuery(true);
		$query->select($db->quoteName('link_id') . ', ' . $db->quoteName('md5sum'));
		$query->from($db->quoteName('#__finder_links'));
		$query->where($db->quoteName('url') . ' = ' . $db->quote($item->url));

		// Load the item  from the database.
		$db->setQuery($query);
		$link = $db->loadObject();

		// Check for a database error.
		if ($db->getErrorNum())
		{
			// Throw database error exception.
			throw new Exception($db->getErrorMsg(), 500);
		}

		// Get the indexer state.
		$state = FinderIndexer::getState();

		// Get the signatures of the item.
		$curSig = self::getSignature($item);
		$oldSig = isset($link->md5sum) ? $link->md5sum : null;

		// Get the other item information.
		$linkId = empty($link->link_id) ? null : $link->link_id;
		$isNew = empty($link->link_id) ? true : false;

		// Check the signatures. If they match, the item is up to date.
		if (!$isNew && $curSig == $oldSig)
		{
			return $linkId;
		}

		/*
		 * If the link already exists, flush all the term maps for the item.
		 * Maps are stored in 16 tables so we need to iterate through and flush
		 * each table one at a time.
		 */
		if (!$isNew)
		{
			for ($i = 0; $i <= 15; $i++)
			{
				// Flush the maps for the link.
				$query->clear();
				$query->delete();
				$query->from($db->quoteName('#__finder_links_terms' . dechex($i)));
				$query->where($db->quoteName('link_id') . ' = ' . (int) $linkId);
				$db->setQuery($query);
				$db->query();

				// Check for a database error.
				if ($db->getErrorNum())
				{
					// Throw database error exception.
					throw new Exception($db->getErrorMsg(), 500);
				}
			}

			// Remove the taxonomy maps.
			FinderIndexerTaxonomy::removeMaps($linkId);
		}

		// Mark afterUnmapping in the profiler.
		self::$profiler ? self::$profiler->mark('afterUnmapping') : null;

		// Perform cleanup on the item data.
		$item->publish_start_date = intval($item->publish_start_date) != 0 ? $item->publish_start_date : $nd;
		$item->publish_end_date = intval($item->publish_end_date) != 0 ? $item->publish_end_date : $nd;
		$item->start_date = intval($item->start_date) != 0 ? $item->start_date : $nd;
		$item->end_date = intval($item->end_date) != 0 ? $item->end_date : $nd;

		// Prepare the item description.
		$item->description = FinderIndexerHelper::parse($item->summary);

		/*
		 * Now, we need to enter the item into the links table. If the item
		 * already exists in the database, we need to use an UPDATE query.
		 * Otherwise, we need to use an INSERT to get the link id back.
		 */

		if ($isNew)
		{
			$columnsArray = array(
				$db->quoteName('url'), $db->quoteName('route'), $db->quoteName('title'), $db->quoteName('description'),
				$db->quoteName('indexdate'), $db->quoteName('published'), $db->quoteName('state'), $db->quoteName('access'),
				$db->quoteName('language'), $db->quoteName('type_id'), $db->quoteName('object'), $db->quoteName('publish_start_date'),
				$db->quoteName('publish_end_date'), $db->quoteName('start_date'), $db->quoteName('end_date'), $db->quoteName('list_price'),
				$db->quoteName('sale_price')
			);

			// Insert the link.
			$query->clear();
			$query->insert($db->quoteName('#__finder_links'));
			$query->columns($columnsArray);
			$query->values(
				$db->quote($item->url) . ', '
				. $db->quote($item->route) . ', '
				. $db->quote($item->title) . ', '
				. $db->quote($item->description) . ', '
				. $query->currentTimestamp() . ', '
				. '1, '
				. (int) $item->state . ', '
				. (int) $item->access . ', '
				. $db->quote($item->language) . ', '
				. (int) $item->type_id . ', '
				. $db->quote(serialize($item)) . ', '
				. $db->quote($item->publish_start_date) . ', '
				. $db->quote($item->publish_end_date) . ', '
				. $db->quote($item->start_date) . ', '
				. $db->quote($item->end_date) . ', '
				. $db->quote($item->list_price) . ', '
				. $db->quote($item->sale_price)
			);
			$db->setQuery($query);
			$db->query();

			// Check for a database error.
			if ($db->getErrorNum())
			{
				// Throw database error exception.
				throw new Exception($db->getErrorMsg(), 500);
			}

			// Get the link id.
			$linkId = (int) $db->insertid();
		}
		else
		{
			// Update the link.
			//@TODO: Implement this
			$query->clear();
			$query->update($db->qn('#__finder_links'));
			$query->set($db->qn('route') . ' = ' . $db->quote($item->route));
			$query->set($db->qn('title') . ' = ' . $db->quote($item->title));
			$query->set($db->qn('description') . ' = ' . $db->quote($item->description));
			$query->set($db->qn('indexdate') . ' = ' . $query->currentTimestamp());
			$query->set($db->qn('state') . ' = ' . (int) $item->state);
			$query->set($db->qn('access') . ' = ' . (int) $item->access);
			$query->set($db->qn('language') . ' = ' . $db->quote($item->language));
			$query->set($db->qn('type_id') . ' = ' . (int) $item->type_id);
			$query->set($db->qn('object') . ' = ' . $db->quote(serialize($item)));
			$query->set($db->qn('publish_start_date') . ' = ' . $db->quote($item->publish_start_date));
			$query->set($db->qn('publish_end_date') . ' = ' . $db->quote($item->publish_end_date));
			$query->set($db->qn('start_date') . ' = ' . $db->quote($item->start_date));
			$query->set($db->qn('end_date') . ' = ' . $db->quote($item->end_date));
			$query->set($db->qn('list_price') . ' = ' . $db->quote($item->list_price));
			$query->set($db->qn('sale_price') . ' = ' . $db->quote($item->sale_price));
			$query->where('link_id = ' . (int) $linkId);
			$db->setQuery($query);
			$db->query();

			// Check for a database error.
			if ($db->getErrorNum())
			{
				// Throw database error exception.
				throw new Exception($db->getErrorMsg(), 500);
			}
		}

		// Set up the variables we will need during processing.
		$tokens = array();
		$count = 0;

		// Mark afterLinking in the profiler.
		self::$profiler ? self::$profiler->mark('afterLinking') : null;

		// Truncate the tokens tables.
		$db->truncateTable('#__finder_tokens');

		// Check for a database error.
		if ($db->getErrorNum())
		{
			// Throw database error exception.
			throw new Exception($db->getErrorMsg(), 500);
		}

		// Truncate the tokens aggregate table.
		$db->truncateTable('#__finder_tokens_aggregate');

		// Check for a database error.
		if ($db->getErrorNum())
		{
			// Throw database error exception.
			throw new Exception($db->getErrorMsg(), 500);
		}

		/*
		 * Process the item's content. The items can customize their
		 * processing instructions to define extra properties to process
		 * or rearrange how properties are weighted.
		 */
		foreach ($item->getInstructions() as $group => $properties)
		{
			// Iterate through the properties of the group.
			foreach ($properties as $property)
			{
				// Check if the property exists in the item.
				if (empty($item->$property))
				{
					continue;
				}

				// Tokenize the property.
				if (is_array($item->$property))
				{
					// Tokenize an array of content and add it to the database.
					foreach ($item->$property as $ip)
					{
						// If the group is path, we need to a few extra processing
						// steps to strip the extension and convert slashes and dashes
						// to spaces.
						if ($group === self::PATH_CONTEXT)
						{
							$ip = JFile::stripExt($ip);
							$ip = str_replace('/', ' ', $ip);
							$ip = str_replace('-', ' ', $ip);
						}

						// Tokenize a string of content and add it to the database.
						$count += FinderIndexer::tokenizeToDB($ip, $group, $item->language, $format);

						// Check if we're approaching the memory limit of the token table.
						if ($count > self::$state->options->get('memory_table_limit', 30000))
						{
							FinderIndexer::toggleTables(false);
						}
					}
				}
				else
				{
					// If the group is path, we need to a few extra processing
					// steps to strip the extension and convert slashes and dashes
					// to spaces.
					if ($group === self::PATH_CONTEXT)
					{
						$item->$property = JFile::stripExt($item->$property);
						$item->$property = str_replace('/', ' ', $item->$property);
						$item->$property = str_replace('-', ' ', $item->$property);
					}

					// Tokenize a string of content and add it to the database.
					$count += FinderIndexer::tokenizeToDB($item->$property, $group, $item->language, $format);

					// Check if we're approaching the memory limit of the token table.
					if ($count > self::$state->options->get('memory_table_limit', 30000))
					{
						FinderIndexer::toggleTables(false);
					}
				}
			}
		}

		/*
		 * Process the item's taxonomy. The items can customize their
		 * taxonomy mappings to define extra properties to map.
		 */
		foreach ($item->getTaxonomy() as $branch => $nodes)
		{
			// Iterate through the nodes and map them to the branch.
			foreach ($nodes as $node)
			{
				// Add the node to the tree.
				$nodeId = FinderIndexerTaxonomy::addNode($branch, $node->title, $node->state, $node->access);

				// Add the link => node map.
				FinderIndexerTaxonomy::addMap($linkId, $nodeId);

				// Tokenize the node title and add them to the database.
				$count += FinderIndexer::tokenizeToDB($node->title, self::META_CONTEXT, $item->language, $format);
			}
		}

		// Mark afterProcessing in the profiler.
		self::$profiler ? self::$profiler->mark('afterProcessing') : null;

		/*
		 * At this point, all of the item's content has been parsed, tokenized
		 * and inserted into the #__finder_tokens table. Now, we need to
		 * aggregate all the data into that table into a more usable form. The
		 * aggregated data will be inserted into #__finder_tokens_aggregate
		 * table.
		 */
		$query	= 'INSERT INTO ' . $db->quoteName('#__finder_tokens_aggregate') .
				' (' . $db->quoteName('term_id') .
				', ' . $db->quoteName('term') .
				', ' . $db->quoteName('stem') .
				', ' . $db->quoteName('common') .
				', ' . $db->quoteName('phrase') .
				', ' . $db->quoteName('term_weight') .
				', ' . $db->quoteName('context') .
				', ' . $db->quoteName('context_weight') . ')' .
				' SELECT' .
				' t.term_id, t1.term, t1.stem, t1.common, t1.phrase, t1.weight, t1.context,' .
				' ROUND( t1.weight * COUNT( t2.term ) * %F, 8 ) AS context_weight' .
				' FROM (' .
				'   SELECT DISTINCT t1.term, t1.stem, t1.common, t1.phrase, t1.weight, t1.context' .
				'   FROM ' . $db->quoteName('#__finder_tokens') . ' AS t1' .
				'   WHERE t1.context = %d' .
				' ) AS t1' .
				' JOIN ' . $db->quoteName('#__finder_tokens') . ' AS t2 ON t2.term = t1.term' .
				' LEFT JOIN ' . $db->quoteName('#__finder_terms') . ' AS t ON t.term = t1.term' .
				' WHERE t2.context = %d' .
				' GROUP BY t1.term' .
				' ORDER BY t1.term DESC';

		// Iterate through the contexts and aggregate the tokens per context.
		foreach ($state->weights as $context => $multiplier)
		{
			// Run the query to aggregate the tokens for this context..
			$db->setQuery(sprintf($query, $multiplier, $context, $context));
			$db->query();

			// Check for a database error.
			if ($db->getErrorNum())
			{
				// Throw database error exception.
				throw new Exception($db->getErrorMsg(), 500);
			}
		}

		// Mark afterAggregating in the profiler.
		self::$profiler ? self::$profiler->mark('afterAggregating') : null;

		/*
		 * When we pulled down all of the aggregate data, we did a LEFT JOIN
		 * over the terms table to try to find all the term ids that
		 * already exist for our tokens. If any of the rows in the aggregate
		 * table have a term of 0, then no term record exists for that
		 * term so we need to add it to the terms table.
		 */
		//@TODO: PostgreSQL doesn't support SOUNDEX out of the box

		/* This edit is causing the indexer to fail.
		$queryInsIgn = 'INSERT INTO ' . $db->quoteName('#__finder_terms') .
						' (' . $db->quoteName('term') .
						', ' . $db->quoteName('stem') .
						', ' . $db->quoteName('common') .
						', ' . $db->quoteName('phrase') .
						', ' . $db->quoteName('weight') .
						', ' . $db->quoteName('soundex') . ')' .
						' SELECT ta.term, ta.stem, ta.common, ta.phrase, ta.term_weight, SOUNDEX(ta.term)' .
						' FROM ' . $db->quoteName('#__finder_tokens_aggregate') . ' AS ta' .
						' WHERE 1 NOT IN ' .
								'( SELECT 1 FROM ' . $db->quoteName('#__finder_terms') .
								' WHERE ta.term_id = 0 )' .
						' AND ta.term_id = 0' .
						' GROUP BY ta.term';

		$db->setQuery($queryInsIgn);
		$db->query();

		// Check for a database error.
		if ($db->getErrorNum())
		{
			//@TODO: PostgreSQL doesn't support SOUNDEX out of the box
			$query->clear();
			$query->select('ta.term, ta.stem, ta.common, ta.phrase, ta.term_weight, SOUNDEX(ta.term)')
					->from($db->quoteName('#__finder_tokens_aggregate') . ' AS ta')
					->where('ta.term_id = 0');
			$db->setQuery($query);
			$subQuVal = $db->loadObject();

			$quRepl_p1 = 'UPDATE ' . $db->quoteName('#__finder_terms') . ' AS ta' .
							' SET ' .
								' (' . $db->quoteName('term') .
								', ' . $db->quoteName('stem') .
								', ' . $db->quoteName('common') .
								', ' . $db->quoteName('phrase') .
								', ' . $db->quoteName('weight') .
								', ' . $db->quoteName('soundex') . ')' .
							' = ' .
								' (' . $db->quote($subQuVal->term) .
								', ' . $db->quote($subQuVal->stem) .
								', ' . $db->quote($subQuVal->common) .
								', ' . $db->quote($subQuVal->phrase) .
								', ' . $db->quote($subQuVal->weight) .
								', ' . $db->quote($subQuVal->soundex) . ')' .
							' WHERE ' .
									$db->quoteName('term') . ' = ' . $db->quote($subQuVal->term) . ' AND ' .
									$db->quoteName('stem') . ' = ' . $db->quote($subQuVal->stem) . ' AND ' .
									$db->quoteName('common') . ' = ' . $db->quote($subQuVal->common) . ' AND ' .
									$db->quoteName('phrase') . ' = ' . $db->quote($subQuVal->phrase) . ' AND ' .
									$db->quoteName('weight') . ' = ' . $db->quote($subQuVal->weight) . ' AND ' .
									$db->quoteName('soundex') . ' = ' . $db->quote($subQuVal->soundex);

			$db->setQuery($quRepl_p1);
			$db->query();

			$quRepl_p2 = 'INSERT INTO ' . $db->quoteName('#__finder_terms') .
						' (' . $db->quoteName('term') .
								', ' . $db->quoteName('stem') .
								', ' . $db->quoteName('common') .
								', ' . $db->quoteName('phrase') .
								', ' . $db->quoteName('weight') .
								', ' . $db->quoteName('soundex') . ')' .
						' SELECT ta.term, ta.stem, ta.common, ta.phrase, ta.term_weight, SOUNDEX(ta.term)' .
						' FROM ' . $db->quoteName('#__finder_tokens_aggregate') . ' AS ta' .
						' WHERE 1 NOT IN ' .
								'( SELECT 1 FROM ' . $db->quoteName('#__finder_terms') .
								' WHERE ta.term_id = 0 )' .
						' AND ta.term_id = 0' .
						' GROUP BY ta.term';

			$db->setQuery($quRepl_p2);
			$db->query();

			// Check for a database error.
			if ($db->getErrorNum())
			{
				throw new Exception($db->getErrorMsg(), 500);
			}
		}
		End of failing edit */

		//@TODO: PostgreSQL doesn't support INSERT IGNORE INTO
		//@TODO: PostgreSQL doesn't support SOUNDEX out of the box
		$db->setQuery(
			'INSERT IGNORE INTO ' . $db->quoteName('#__finder_terms') .
			' (' . $db->quoteName('term') .
			', ' . $db->quoteName('stem') .
			', ' . $db->quoteName('common') .
			', ' . $db->quoteName('phrase') .
			', ' . $db->quoteName('weight') .
			', ' . $db->quoteName('soundex') . ')' .
			' SELECT ta.term, ta.stem, ta.common, ta.phrase, ta.term_weight, SOUNDEX(ta.term)' .
			' FROM ' . $db->quoteName('#__finder_tokens_aggregate') . ' AS ta' .
			' WHERE ta.term_id = 0' .
			' GROUP BY ta.term'
		);
		$db->query();

		// Check for a database error.
		if ($db->getErrorNum())
		{
			{
				throw new Exception($db->getErrorMsg(), 500);
			}
		}

		/*
		 * Now, we just inserted a bunch of new records into the terms table
		 * so we need to go back and update the aggregate table with all the
		 * new term ids.
		 */
		$query = $db->getQuery(true);
		$query->update($db->quoteName('#__finder_tokens_aggregate') . ' AS ta');
		$query->join('INNER', $db->quoteName('#__finder_terms') . ' AS t ON t.term = ta.term');
		$query->set('ta.term_id = t.term_id');
		$query->where('ta.term_id = 0');
		$db->setQuery($query);
		$db->query();

		// Check for a database error.
		if ($db->getErrorNum())
		{
			// Throw database error exception.
			throw new Exception($db->getErrorMsg(), 500);
		}

		// Mark afterTerms in the profiler.
		self::$profiler ? self::$profiler->mark('afterTerms') : null;

		/*
		 * After we've made sure that all of the terms are in the terms table
		 * and the aggregate table has the correct term ids, we need to update
		 * the links counter for each term by one.
		 */
		$query->clear();
		$query->update($db->quoteName('#__finder_terms') . ' AS t');
		$query->join('INNER', $db->quoteName('#__finder_tokens_aggregate') . ' AS ta ON ta.term_id = t.term_id');
		$query->set('t.' . $db->quoteName('links') . ' = t.links + 1');
		$db->setQuery($query);
		$db->query();

		// Check for a database error.
		if ($db->getErrorNum())
		{
			// Throw database error exception.
			throw new Exception($db->getErrorMsg(), 500);
		}

		// Mark afterTerms in the profiler.
		self::$profiler ? self::$profiler->mark('afterTerms') : null;

		/*
		 * Before we can insert all of the mapping rows, we have to figure out
		 * which mapping table the rows need to be inserted into. The mapping
		 * table for each term is based on the first character of the md5 of
		 * the first character of the term. In php, it would be expressed as
		 * substr(md5(substr($token, 0, 1)), 0, 1)
		 */
		$query->clear();
		$query->update($db->quoteName('#__finder_tokens_aggregate'));
		$query->set($db->quoteName('map_suffix') . ' = SUBSTR(MD5(SUBSTR(' . $db->quoteName('term') . ', 1, 1)), 1, 1)');
		$db->setQuery($query);
		$db->query();

		// Check for a database error.
		if ($db->getErrorNum())
		{
			// Throw database error exception.
			throw new Exception($db->getErrorMsg(), 500);
		}

		/*
		 * At this point, the aggregate table contains a record for each
		 * term in each context. So, we're going to pull down all of that
		 * data while grouping the records by term and add all of the
		 * sub-totals together to arrive at the final total for each token for
		 * this link. Then, we insert all of that data into the appropriate
		 * mapping table.
		 */
		for ($i = 0; $i <= 15; $i++)
		{
			// Get the mapping table suffix.
			$suffix = dechex($i);

			/*
			 * We have to run this query 16 times, one for each link => term
			 * mapping table.
			 */
			//@TODO: Convert to JDatabaseQuery
			$db->setQuery(
				'INSERT INTO ' . $db->quoteName('#__finder_links_terms' . $suffix) .
				' (' . $db->quoteName('link_id') .
				', ' . $db->quoteName('term_id') .
				', ' . $db->quoteName('weight') . ')' .
				' SELECT ' . (int) $linkId . ', ' . $db->quoteName('term_id') . ',' .
				' ROUND(SUM(' . $db->quoteName('context_weight') . '), 8)' .
				' FROM ' . $db->quoteName('#__finder_tokens_aggregate') .
				' WHERE ' . $db->quoteName('map_suffix') . ' = ' . $db->quote($suffix) .
				' GROUP BY ' . $db->quoteName('term') .
				' ORDER BY ' . $db->quoteName('term') . ' DESC'
			);
			$db->query();

			// Check for a database error.
			if ($db->getErrorNum())
			{
				// Throw database error exception.
				throw new Exception($db->getErrorMsg(), 500);
			}
		}

		// Mark afterMapping in the profiler.
		self::$profiler ? self::$profiler->mark('afterMapping') : null;

		// Update the signature.
		$query->clear();
		$query->update($db->quoteName('#__finder_links'));
		$query->set($db->quoteName('md5sum') . ' = ' . $db->quote($curSig));
		$query->where($db->quoteName('link_id') . ' = ' . $db->quote($linkId));
		$db->setQuery($query);
		$db->query();

		// Check for a database error.
		if ($db->getErrorNum())
		{
			// Throw database error exception.
			throw new Exception($db->getErrorMsg(), 500);
		}

		// Mark afterSigning in the profiler.
		self::$profiler ? self::$profiler->mark('afterSigning') : null;

		// Truncate the tokens tables.
		$db->truncateTable('#__finder_tokens');

		// Check for a database error.
		if ($db->getErrorNum())
		{
			// Throw database error exception.
			throw new Exception($db->getErrorMsg(), 500);
		}

		// Truncate the tokens aggregate table.
		$db->truncateTable('#__finder_tokens_aggregate');

		// Check for a database error.
		if ($db->getErrorNum())
		{
			// Throw database error exception.
			throw new Exception($db->getErrorMsg(), 500);
		}

		// Toggle the token tables back to memory tables.
		FinderIndexer::toggleTables(true);

		// Mark afterTruncating in the profiler.
		self::$profiler ? self::$profiler->mark('afterTruncating') : null;

		return $linkId;
	}

	/**
	 * Method to remove a link from the index.
	 *
	 * @param   integer  $linkId  The id of the link.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	public static function remove($linkId)
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		// Get the indexer state.
		$state = FinderIndexer::getState();

		// Update the link counts and remove the mapping records.
		for ($i = 0; $i <= 15; $i++)
		{
			// Update the link counts for the terms.
			$query->update($db->quoteName('#__finder_terms') . ' AS t');
			$query->join('INNER', $db->quoteName('#__finder_links_terms' . dechex($i)) . ' AS m ON m.term_id = t.term_id');
			$query->set($db->quoteName('t'). '.' . $db->quoteName('links') . ' ='.  $db->quoteName('t') .'.' . $db->quoteName('links') . ' - 1');
			$query->where($db->quoteName('m') . '.' . $db->quoteName('link_id') . ' = ' . $db->quote((int) $linkId));
			$db->setQuery($query);
			$db->query();

			// Check for a database error.
			if ($db->getErrorNum())
			{
				// Throw database error exception.
				throw new Exception($db->getErrorMsg(), 500);
			}

			// Remove all records from the mapping tables.
			$query->clear();
			$query->delete();
			$query->from($db->quoteName('#__finder_links_terms' . dechex($i)));
			$query->where($db->quoteName('link_id') . ' = ' . (int) $linkId);
			$db->setQuery($query);
			$db->query();

			// Check for a database error.
			if ($db->getErrorNum())
			{
				// Throw database error exception.
				throw new Exception($db->getErrorMsg(), 500);
			}
		}

		// Delete all orphaned terms.
		$query->clear();
		$query->delete();
		$query->from($db->quoteName('#__finder_terms'));
		$query->where($db->quoteName('links') . ' <= 0');
		$db->setQuery($query);
		$db->query();

		// Check for a database error.
		if ($db->getErrorNum())
		{
			// Throw database error exception.
			throw new Exception($db->getErrorMsg(), 500);
		}

		// Delete the link from the index.
		$query->clear();
		$query->delete();
		$query->from($db->quoteName('#__finder_links'));
		$query->where($db->quoteName('link_id') . ' = ' . $db->quote((int) $linkId));
		$db->setQuery($query);
		$db->query();

		// Check for a database error.
		if ($db->getErrorNum())
		{
			// Throw database error exception.
			throw new Exception($db->getErrorMsg(), 500);
		}

		// Remove the taxonomy maps.
		FinderIndexerTaxonomy::removeMaps($linkId);

		// Remove the orphaned taxonomy nodes.
		FinderIndexerTaxonomy::removeOrphanNodes();

		return true;
	}

	/**
	 * Method to optimize the index. We use this method to remove unused terms
	 * and any other optimizations that might be necessary.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	public static function optimize()
	{
		// Get the indexer state.
		$state = FinderIndexer::getState();

		// Get the database object.
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		// Delete all orphaned terms.
		$query->delete();
		$query->from($db->quoteName('#__finder_terms'));
		$query->where($db->quoteName('links') . ' <= 0');
		$db->setQuery($query);
		$db->query();

		// Check for a database error.
		if ($db->getErrorNum())
		{
			// Throw database error exception.
			throw new Exception($db->getErrorMsg(), 500);
		}

		// Optimize the links table.
		//@TODO: PostgreSQL doesn't support OPTIMIZE TABLE
		// Temporary workaround for non-MySQL solutions
		if (strpos($db->name, 'mysql') === 0)
		{
			$db->setQuery('OPTIMIZE TABLE ' . $db->quoteName('#__finder_links'));
			$db->query();

			// Check for a database error.
			if ($db->getErrorNum())
			{
				// Throw database error exception.
				throw new Exception($db->getErrorMsg(), 500);
			}
		}

		//@TODO: PostgreSQL doesn't support OPTIMIZE TABLE
		// Temporary workaround for non-MySQL solutions
		if (strpos($db->name, 'mysql') === 0)
		{
			for ($i = 0; $i <= 15; $i++)
			{
				// Optimize the terms mapping table.
				$db->setQuery('OPTIMIZE TABLE ' . $db->quoteName('#__finder_links_terms' . dechex($i)));
				$db->query();

				// Check for a database error.
				if ($db->getErrorNum())
				{
					// Throw database error exception.
					throw new Exception($db->getErrorMsg(), 500);
				}
			}
		}

		// Optimize the terms mapping table.
		//@TODO: PostgreSQL doesn't support OPTIMIZE TABLE
		// Temporary workaround for non-MySQL solutions
		if (strpos($db->name, 'mysql') === 0)
		{
			$db->setQuery('OPTIMIZE TABLE ' . $db->quoteName('#__finder_links_terms'));
			$db->query();

			// Check for a database error.
			if ($db->getErrorNum())
			{
				// Throw database error exception.
				throw new Exception($db->getErrorMsg(), 500);
			}
		}

		// Remove the orphaned taxonomy nodes.
		FinderIndexerTaxonomy::removeOrphanNodes();

		// Optimize the taxonomy mapping table.
		//@TODO: PostgreSQL doesn't support OPTIMIZE TABLE
		// Temporary workaround for non-MySQL solutions
		if (strpos($db->name, 'mysql') === 0)
		{
			$db->setQuery('OPTIMIZE TABLE ' . $db->quoteName('#__finder_taxonomy_map'));
			$db->query();

			// Check for a database error.
			if ($db->getErrorNum())
			{
				// Throw database error exception.
				throw new Exception($db->getErrorMsg(), 500);
			}
		}

		return true;
	}

	/**
	 * Method to get a content item's signature.
	 *
	 * @param   object  $item  The content item to index.
	 *
	 * @return  string  The content item's signature.
	 *
	 * @since   2.5
	 */
	protected static function getSignature($item)
	{
		// Get the indexer state.
		$state = FinderIndexer::getState();

		// Get the relevant configuration variables.
		$config = array();
		$config[] = $state->weights;
		$config[] = $state->options->get('stem', 1);
		$config[] = $state->options->get('stemmer', 'porter_en');

		return md5(serialize(array($item, $config)));
	}

	/**
	 * Method to parse input, tokenize it, and then add it to the database.
	 *
	 * @param   mixed    $input    String or resource to use as input. A resource
	 *                             input will automatically be chunked to conserve
	 *                             memory. Strings will be chunked if longer than
	 *                             2K in size.
	 * @param   integer  $context  The context of the input. See context constants.
	 * @param   string   $lang     The language of the input.
	 * @param   string   $format   The format of the input.
	 *
	 * @return  integer  The number of tokens extracted from the input.
	 *
	 * @since   2.5
	 */
	protected static function tokenizeToDB($input, $context, $lang, $format)
	{
		$count = 0;
		$buffer = null;

		// If the input is a resource, batch the process out.
		if (is_resource($input))
		{
			// Batch the process out to avoid memory limits.
			while (!feof($input))
			{
				// Read into the buffer.
				$buffer .= fread($input, 2048);

				// If we haven't reached the end of the file, seek to the last
				// space character and drop whatever is after that to make sure
				// we didn't truncate a term while reading the input.
				if (!feof($input))
				{
					// Find the last space character.
					$ls = strrpos($buffer, ' ');

					// Adjust string based on the last space character.
					if ($ls)
					{
						// Truncate the string to the last space character.
						$string = substr($buffer, 0, $ls);

						// Adjust the buffer based on the last space for the
						// next iteration and trim.
						$buffer = JString::trim(substr($buffer, $ls));
					}
					// No space character was found.
					else
					{
						$string = $buffer;
					}
				}
				// We've reached the end of the file, so parse whatever remains.
				else
				{
					$string = $buffer;
				}

				// Parse the input.
				$string = FinderIndexerHelper::parse($string, $format);

				// Check the input.
				if (empty($string))
				{
					continue;
				}

				// Tokenize the input.
				$tokens = FinderIndexerHelper::tokenize($string, $lang);

				// Add the tokens to the database.
				$count += FinderIndexer::addTokensToDB($tokens, $context);

				// Check if we're approaching the memory limit of the token table.
				if ($count > self::$state->options->get('memory_table_limit', 30000))
				{
					FinderIndexer::toggleTables(false);
				}

				unset($string);
				unset($tokens);
			}
		}
		// If the input is greater than 2K in size, it is more efficient to
		// batch out the operation into smaller chunks of work.
		elseif (strlen($input) > 2048)
		{
			$start = 0;
			$end = strlen($input);
			$chunk = 2048;

			// As it turns out, the complex regular expressions we use for
			// sanitizing input are not very efficient when given large
			// strings. It is much faster to process lots of short strings.
			while ($start < $end)
			{
				// Setup the string.
				$string = substr($input, $start, $chunk);

				// Find the last space character if we aren't at the end.
				$ls = (($start + $chunk) < $end ? strrpos($string, ' ') : false);

				// Truncate to the last space character.
				if ($ls !== false)
				{
					$string = substr($string, 0, $ls);
				}

				// Adjust the start position for the next iteration.
				$start += ($ls !== false ? ($ls + 1 - $chunk) + $chunk : $chunk);

				// Parse the input.
				$string = FinderIndexerHelper::parse($string, $format);

				// Check the input.
				if (empty($string))
				{
					continue;
				}

				// Tokenize the input.
				$tokens = FinderIndexerHelper::tokenize($string, $lang);

				// Add the tokens to the database.
				$count += FinderIndexer::addTokensToDB($tokens, $context);

				// Check if we're approaching the memory limit of the token table.
				if ($count > self::$state->options->get('memory_table_limit', 30000))
				{
					FinderIndexer::toggleTables(false);
				}
			}
		}
		else
		{
			// Parse the input.
			$input = FinderIndexerHelper::parse($input, $format);

			// Check the input.
			if (empty($input))
			{
				return $count;
			}

			// Tokenize the input.
			$tokens = FinderIndexerHelper::tokenize($input, $lang);

			// Add the tokens to the database.
			$count = FinderIndexer::addTokensToDB($tokens, $context);
		}

		return $count;
	}

	/**
	 * Method to add a set of tokens to the database.
	 *
	 * @param   mixed  $tokens   An array or single FinderIndexerToken object.
	 * @param   mixed  $context  The context of the tokens. See context constants. [optional]
	 *
	 * @return  integer  The number of tokens inserted into the database.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	protected static function addTokensToDB($tokens, $context = '')
	{
		// Get the database object.
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		// Force tokens to an array.
		$tokens = is_array($tokens) ? $tokens : array($tokens);

		// Count the number of token values.
		$values = 0;

		// Iterate through the tokens to create SQL value sets.
		foreach ($tokens as $token)
		{
			$query->values(
				$db->quote($token->term) . ', '
				. $db->quote($token->stem) . ', '
				. (int) $token->common . ', '
				. (int) $token->phrase . ', '
				. (float) $token->weight . ', '
				. (int) $context
			);
			$values++;
		}

		// Insert the tokens into the database.
		$query->insert($db->quoteName('#__finder_tokens'));
		$query->columns(
					array(
						$db->quoteName('term'),
						$db->quoteName('stem'),
						$db->quoteName('common'),
						$db->quoteName('phrase'),
						$db->quoteName('weight'),
						$db->quoteName('context')
					)
		);
		$db->setQuery($query);
		$db->query();

		// Check for a database error.
		if ($db->getErrorNum())
		{
			// Throw database error exception.
			throw new Exception($db->getErrorMsg(), 500);
		}

		return $values;
	}

	/**
	 * Method to switch the token tables from Memory tables to MyISAM tables
	 * when they are close to running out of memory.
	 *
	 * @param   boolean  $memory  Flag to control how they should be toggled.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 * @todo    PostgreSQL doesn't support setting ENGINEs, determine how to handle setting tables
	 */
	protected static function toggleTables($memory)
	{
		static $state;

		// Get the database adapter.
		$db = JFactory::getDBO();

		// Temporary workaround for non-MySQL solutions
		if (strpos($db->name, 'mysql') !== 0)
		{
			return true;
		}

		// Check if we are setting the tables to the Memory engine.
		if ($memory === true && $state !== true)
		{
			// Set the tokens table to Memory.
			$db->setQuery('ALTER TABLE ' . $db->quoteName('#__finder_tokens') . ' ENGINE = MEMORY');
			$db->query();

			// Check for a database error.
			if ($db->getErrorNum())
			{
				// Throw database error exception.
				throw new Exception($db->getErrorMsg(), 500);
			}

			// Set the tokens aggregate table to Memory.
			$db->setQuery('ALTER TABLE ' . $db->quoteName('#__finder_tokens_aggregate') . ' ENGINE = MEMORY');
			$db->query();

			// Check for a database error.
			if ($db->getErrorNum())
			{
				// Throw database error exception.
				throw new Exception($db->getErrorMsg(), 500);
			}

			// Set the internal state.
			$state = $memory;
		}
		// We must be setting the tables to the MyISAM engine.
		elseif ($memory === false && $state !== false)
		{
			// Set the tokens table to MyISAM.
			$db->setQuery('ALTER TABLE ' . $db->quoteName('#__finder_tokens') . ' ENGINE = MYISAM');
			$db->query();

			// Check for a database error.
			if ($db->getErrorNum())
			{
				// Throw database error exception.
				throw new Exception($db->getErrorMsg(), 500);
			}

			// Set the tokens aggregate table to MyISAM.
			$db->setQuery('ALTER TABLE ' . $db->quoteName('#__finder_tokens_aggregate') . ' ENGINE = MYISAM');
			$db->query();

			// Check for a database error.
			if ($db->getErrorNum())
			{
				// Throw database error exception.
				throw new Exception($db->getErrorMsg(), 500);
			}

			// Set the internal state.
			$state = $memory;
		}

		return true;
	}
}
