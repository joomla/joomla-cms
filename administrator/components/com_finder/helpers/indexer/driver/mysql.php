<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.filesystem.file');

/**
 * Indexer class supporting MySQL(i) for the Finder indexer package.
 *
 * The indexer class provides the core functionality of the Finder
 * search engine. It is responsible for adding and updating the
 * content links table; extracting and scoring tokens; and maintaining
 * all referential information for the content.
 *
 * Note: All exceptions thrown from within this class should be caught
 * by the controller.
 *
 * @since  3.0
 */
class FinderIndexerDriverMysql extends FinderIndexer
{
	/**
	 * Method to index a content item.
	 *
	 * @param   FinderIndexerResult  $item    The content item to index.
	 * @param   string               $format  The format of the content. [optional]
	 *
	 * @return  integer  The ID of the record in the links table.
	 *
	 * @since   3.0
	 * @throws  Exception on database error.
	 */
	public function index($item, $format = 'html')
	{
		// Mark beforeIndexing in the profiler.
		static::$profiler ? static::$profiler->mark('beforeIndexing') : null;
		$db = $this->db;
		$nd = $db->getNullDate();

		// Check if the item is in the database.
		$query = $db->getQuery(true)
			->select($db->quoteName('link_id') . ', ' . $db->quoteName('md5sum'))
			->from($db->quoteName('#__finder_links'))
			->where($db->quoteName('url') . ' = ' . $db->quote($item->url));

		// Load the item  from the database.
		$db->setQuery($query);
		$link = $db->loadObject();

		// Get the indexer state.
		$state = static::getState();

		// Get the signatures of the item.
		$curSig = static::getSignature($item);
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
				$query->clear()
					->delete($db->quoteName('#__finder_links_terms' . dechex($i)))
					->where($db->quoteName('link_id') . ' = ' . (int) $linkId);
				$db->setQuery($query);
				$db->execute();
			}

			// Remove the taxonomy maps.
			FinderIndexerTaxonomy::removeMaps($linkId);
		}

		// Mark afterUnmapping in the profiler.
		static::$profiler ? static::$profiler->mark('afterUnmapping') : null;

		// Perform cleanup on the item data.
		$item->publish_start_date = (int) $item->publish_start_date != 0 ? $item->publish_start_date : $nd;
		$item->publish_end_date = (int) $item->publish_end_date != 0 ? $item->publish_end_date : $nd;
		$item->start_date = (int) $item->start_date != 0 ? $item->start_date : $nd;
		$item->end_date = (int) $item->end_date != 0 ? $item->end_date : $nd;

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
			$query->clear()
				->insert($db->quoteName('#__finder_links'))
				->columns($columnsArray)
				->values(
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
					. (double) ($item->list_price ?: 0) . ', '
					. (double) ($item->sale_price ?: 0)
				);
			$db->setQuery($query);
			$db->execute();

			// Get the link id.
			$linkId = (int) $db->insertid();
		}
		else
		{
			// Update the link.
			$query->clear()
				->update($db->quoteName('#__finder_links'))
				->set($db->quoteName('route') . ' = ' . $db->quote($item->route))
				->set($db->quoteName('title') . ' = ' . $db->quote($item->title))
				->set($db->quoteName('description') . ' = ' . $db->quote($item->description))
				->set($db->quoteName('indexdate') . ' = ' . $query->currentTimestamp())
				->set($db->quoteName('state') . ' = ' . (int) $item->state)
				->set($db->quoteName('access') . ' = ' . (int) $item->access)
				->set($db->quoteName('language') . ' = ' . $db->quote($item->language))
				->set($db->quoteName('type_id') . ' = ' . (int) $item->type_id)
				->set($db->quoteName('object') . ' = ' . $db->quote(serialize($item)))
				->set($db->quoteName('publish_start_date') . ' = ' . $db->quote($item->publish_start_date))
				->set($db->quoteName('publish_end_date') . ' = ' . $db->quote($item->publish_end_date))
				->set($db->quoteName('start_date') . ' = ' . $db->quote($item->start_date))
				->set($db->quoteName('end_date') . ' = ' . $db->quote($item->end_date))
				->set($db->quoteName('list_price') . ' = ' . (double) ($item->list_price ?: 0))
				->set($db->quoteName('sale_price') . ' = ' . (double) ($item->sale_price ?: 0))
				->where('link_id = ' . (int) $linkId);
			$db->setQuery($query);
			$db->execute();
		}

		// Set up the variables we will need during processing.
		$count = 0;

		// Mark afterLinking in the profiler.
		static::$profiler ? static::$profiler->mark('afterLinking') : null;

		// Truncate the tokens tables.
		$db->truncateTable('#__finder_tokens');

		// Truncate the tokens aggregate table.
		$db->truncateTable('#__finder_tokens_aggregate');

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
						/*
						 * If the group is path, we need to a few extra processing
						 * steps to strip the extension and convert slashes and dashes
						 * to spaces.
						 */
						if ($group === static::PATH_CONTEXT)
						{
							$ip = JFile::stripExt($ip);
							$ip = str_replace('/', ' ', $ip);
							$ip = str_replace('-', ' ', $ip);
						}

						// Tokenize a string of content and add it to the database.
						$count += $this->tokenizeToDb($ip, $group, $item->language, $format);

						// Check if we're approaching the memory limit of the token table.
						if ($count > static::$state->options->get('memory_table_limit', 30000))
						{
							$this->toggleTables(false);
						}
					}
				}
				else
				{
					/*
					 * If the group is path, we need to a few extra processing
					 * steps to strip the extension and convert slashes and dashes
					 * to spaces.
					 */
					if ($group === static::PATH_CONTEXT)
					{
						$item->$property = JFile::stripExt($item->$property);
						$item->$property = str_replace('/', ' ', $item->$property);
						$item->$property = str_replace('-', ' ', $item->$property);
					}

					// Tokenize a string of content and add it to the database.
					$count += $this->tokenizeToDb($item->$property, $group, $item->language, $format);

					// Check if we're approaching the memory limit of the token table.
					if ($count > static::$state->options->get('memory_table_limit', 30000))
					{
						$this->toggleTables(false);
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
				$count += $this->tokenizeToDb($node->title, static::META_CONTEXT, $item->language, $format);
			}
		}

		// Mark afterProcessing in the profiler.
		static::$profiler ? static::$profiler->mark('afterProcessing') : null;

		/*
		 * At this point, all of the item's content has been parsed, tokenized
		 * and inserted into the #__finder_tokens table. Now, we need to
		 * aggregate all the data into that table into a more usable form. The
		 * aggregated data will be inserted into #__finder_tokens_aggregate
		 * table.
		 */
		$query = 'INSERT INTO ' . $db->quoteName('#__finder_tokens_aggregate') .
			' (' . $db->quoteName('term_id') .
			', ' . $db->quoteName('map_suffix') .
				', ' . $db->quoteName('term') .
			', ' . $db->quoteName('stem') .
			', ' . $db->quoteName('common') .
			', ' . $db->quoteName('phrase') .
			', ' . $db->quoteName('term_weight') .
			', ' . $db->quoteName('context') .
			', ' . $db->quoteName('context_weight') .
			', ' . $db->quoteName('total_weight') .
				', ' . $db->quoteName('language') . ')' .
			' SELECT' .
			' COALESCE(t.term_id, 0), \'\', t1.term, t1.stem, t1.common, t1.phrase, t1.weight, t1.context,' .
			' ROUND( t1.weight * COUNT( t2.term ) * %F, 8 ) AS context_weight, 0, t1.language' .
			' FROM (' .
			'   SELECT DISTINCT t1.term, t1.stem, t1.common, t1.phrase, t1.weight, t1.context, t1.language' .
			'   FROM ' . $db->quoteName('#__finder_tokens') . ' AS t1' .
			'   WHERE t1.context = %d' .
			' ) AS t1' .
			' JOIN ' . $db->quoteName('#__finder_tokens') . ' AS t2 ON t2.term = t1.term' .
			' LEFT JOIN ' . $db->quoteName('#__finder_terms') . ' AS t ON t.term = t1.term' .
			' WHERE t2.context = %d' .
			' GROUP BY t1.term, t.term_id, t1.term, t1.stem, t1.common, t1.phrase, t1.weight, t1.context, t1.language' .
			' ORDER BY t1.term DESC';

		// Iterate through the contexts and aggregate the tokens per context.
		foreach ($state->weights as $context => $multiplier)
		{
			// Run the query to aggregate the tokens for this context..
			$db->setQuery(sprintf($query, $multiplier, $context, $context));
			$db->execute();
		}

		// Mark afterAggregating in the profiler.
		static::$profiler ? static::$profiler->mark('afterAggregating') : null;

		/*
		 * When we pulled down all of the aggregate data, we did a LEFT JOIN
		 * over the terms table to try to find all the term ids that
		 * already exist for our tokens. If any of the rows in the aggregate
		 * table have a term of 0, then no term record exists for that
		 * term so we need to add it to the terms table.
		 */
		$db->setQuery(
			'INSERT IGNORE INTO ' . $db->quoteName('#__finder_terms') .
			' (' . $db->quoteName('term') .
			', ' . $db->quoteName('stem') .
			', ' . $db->quoteName('common') .
			', ' . $db->quoteName('phrase') .
			', ' . $db->quoteName('weight') .
			', ' . $db->quoteName('soundex') .
			', ' . $db->quoteName('language') . ')' .
			' SELECT ta.term, ta.stem, ta.common, ta.phrase, ta.term_weight, SOUNDEX(ta.term), ta.language' .
			' FROM ' . $db->quoteName('#__finder_tokens_aggregate') . ' AS ta' .
			' WHERE ta.term_id = 0' .
			' GROUP BY ta.term, ta.stem, ta.common, ta.phrase, ta.term_weight, SOUNDEX(ta.term), ta.language'
		);
		$db->execute();

		/*
		 * Now, we just inserted a bunch of new records into the terms table
		 * so we need to go back and update the aggregate table with all the
		 * new term ids.
		 */
		$query = $db->getQuery(true)
			->update($db->quoteName('#__finder_tokens_aggregate') . ' AS ta')
			->join('INNER', $db->quoteName('#__finder_terms') . ' AS t ON t.term = ta.term')
			->set('ta.term_id = t.term_id')
			->where('ta.term_id = 0');
		$db->setQuery($query);
		$db->execute();

		// Mark afterTerms in the profiler.
		static::$profiler ? static::$profiler->mark('afterTerms') : null;

		/*
		 * After we've made sure that all of the terms are in the terms table
		 * and the aggregate table has the correct term ids, we need to update
		 * the links counter for each term by one.
		 */
		$query->clear()
			->update($db->quoteName('#__finder_terms') . ' AS t')
			->join('INNER', $db->quoteName('#__finder_tokens_aggregate') . ' AS ta ON ta.term_id = t.term_id')
			->set('t.' . $db->quoteName('links') . ' = t.links + 1');
		$db->setQuery($query);
		$db->execute();

		// Mark afterTerms in the profiler.
		static::$profiler ? static::$profiler->mark('afterTerms') : null;

		/*
		 * Before we can insert all of the mapping rows, we have to figure out
		 * which mapping table the rows need to be inserted into. The mapping
		 * table for each term is based on the first character of the md5 of
		 * the first character of the term. In php, it would be expressed as
		 * substr(md5(substr($token, 0, 1)), 0, 1)
		 */
		$query->clear()
			->update($db->quoteName('#__finder_tokens_aggregate'))
			->set($db->quoteName('map_suffix') . ' = SUBSTR(MD5(SUBSTR(' . $db->quoteName('term') . ', 1, 1)), 1, 1)');
		$db->setQuery($query);
		$db->execute();

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
			$db->setQuery(
				'INSERT INTO ' . $db->quoteName('#__finder_links_terms' . $suffix) .
				' (' . $db->quoteName('link_id') .
				', ' . $db->quoteName('term_id') .
				', ' . $db->quoteName('weight') . ')' .
				' SELECT ' . (int) $linkId . ', ' . $db->quoteName('term_id') . ',' .
				' ROUND(SUM(' . $db->quoteName('context_weight') . '), 8)' .
				' FROM ' . $db->quoteName('#__finder_tokens_aggregate') .
				' WHERE ' . $db->quoteName('map_suffix') . ' = ' . $db->quote($suffix) .
				' GROUP BY ' . $db->quoteName('term') . ', ' . $db->quoteName('term_id') .
				' ORDER BY ' . $db->quoteName('term') . ' DESC'
			);
			$db->execute();
		}

		// Mark afterMapping in the profiler.
		static::$profiler ? static::$profiler->mark('afterMapping') : null;

		// Update the signature.
		$query->clear()
			->update($db->quoteName('#__finder_links'))
			->set($db->quoteName('md5sum') . ' = ' . $db->quote($curSig))
			->where($db->quoteName('link_id') . ' = ' . $db->quote($linkId));
		$db->setQuery($query);
		$db->execute();

		// Mark afterSigning in the profiler.
		static::$profiler ? static::$profiler->mark('afterSigning') : null;

		// Truncate the tokens tables.
		$db->truncateTable('#__finder_tokens');

		// Truncate the tokens aggregate table.
		$db->truncateTable('#__finder_tokens_aggregate');

		// Toggle the token tables back to memory tables.
		$this->toggleTables(true);

		// Mark afterTruncating in the profiler.
		static::$profiler ? static::$profiler->mark('afterTruncating') : null;

		return $linkId;
	}

	/**
	 * Method to optimize the index. We use this method to remove unused terms
	 * and any other optimizations that might be necessary.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.0
	 * @throws  Exception on database error.
	 */
	public function optimize()
	{
		// Get the database object.
		$db = $this->db;
		$query = $db->getQuery(true);

		// Delete all orphaned terms.
		$query->delete($db->quoteName('#__finder_terms'))
			->where($db->quoteName('links') . ' <= 0');
		$db->setQuery($query);
		$db->execute();

		// Optimize the links table.
		$db->setQuery('OPTIMIZE TABLE ' . $db->quoteName('#__finder_links'));
		$db->execute();

		for ($i = 0; $i <= 15; $i++)
		{
			// Optimize the terms mapping table.
			$db->setQuery('OPTIMIZE TABLE ' . $db->quoteName('#__finder_links_terms' . dechex($i)));
			$db->execute();
		}

		// Optimize the filters table.
		$db->setQuery('OPTIMIZE TABLE ' . $db->quoteName('#__finder_filters'));
		$db->execute();

		// Optimize the terms common table.
		$db->setQuery('OPTIMIZE TABLE ' . $db->quoteName('#__finder_terms_common'));
		$db->execute();

		// Optimize the types table.
		$db->setQuery('OPTIMIZE TABLE ' . $db->quoteName('#__finder_types'));
		$db->execute();

		// Remove the orphaned taxonomy nodes.
		FinderIndexerTaxonomy::removeOrphanNodes();

		// Optimize the taxonomy mapping table.
		$db->setQuery('OPTIMIZE TABLE ' . $db->quoteName('#__finder_taxonomy_map'));
		$db->execute();

		// Optimize the taxonomy table.
		$db->setQuery('OPTIMIZE TABLE ' . $db->quoteName('#__finder_taxonomy'));
		$db->execute();

		return true;
	}


	/**
	 * Method to switch the token tables from Memory tables to MyISAM tables
	 * when they are close to running out of memory.
	 *
	 * @param   boolean  $memory  Flag to control how they should be toggled.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.0
	 * @throws  Exception on database error.
	 */
	protected function toggleTables($memory)
	{
		static $state;

		// Get the database adapter.
		$db = $this->db;

		// Check if we are setting the tables to the Memory engine.
		if ($memory === true && $state !== true)
		{
			// Set the tokens table to Memory.
			$db->setQuery('ALTER TABLE ' . $db->quoteName('#__finder_tokens') . ' ENGINE = MEMORY');
			$db->execute();

			// Set the tokens aggregate table to Memory.
			$db->setQuery('ALTER TABLE ' . $db->quoteName('#__finder_tokens_aggregate') . ' ENGINE = MEMORY');
			$db->execute();

			// Set the internal state.
			$state = $memory;
		}
		// We must be setting the tables to the MyISAM engine.
		elseif ($memory === false && $state !== false)
		{
			// Set the tokens table to MyISAM.
			$db->setQuery('ALTER TABLE ' . $db->quoteName('#__finder_tokens') . ' ENGINE = MYISAM');
			$db->execute();

			// Set the tokens aggregate table to MyISAM.
			$db->setQuery('ALTER TABLE ' . $db->quoteName('#__finder_tokens_aggregate') . ' ENGINE = MYISAM');
			$db->execute();

			// Set the internal state.
			$state = $memory;
		}

		return true;
	}
}
