<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Engine\Filter\Stack;

// Protection against direct access
defined('AKEEBAENGINE') || die();

use Akeeba\Engine\Factory;
use Akeeba\Engine\Filter\Base as FilterBase;

/**
 * Date conditional filter
 *
 * It will only backup files modified after a specific date and time
 *
 * @since  3.4.0
 */
class StackFinder extends FilterBase
{
	/** @inheritDoc */
	public function __construct()
	{
		parent::__construct();

		$this->object  = 'dbobject';
		$this->subtype = 'content';
		$this->method  = 'api';
	}

	/**
	 * Extra SQL statements to append to the SQL dump file.
	 *
	 * Joomla 4's #__finder_taxonomy table is a tree. We always need a root node. This adds the root node back to the
	 * tree even though we just excluded it.
	 *
	 * @param   string  $root  The database for which to get the extra SQL statements
	 *
	 * @return  string  Extra SQL statements
	 *
	 * @since   7.2.0
	 */
	public function getExtraSQL(string $root): array
	{
		// Only run on Joomla! 4 and for the main site database
		if (($root != '[SITEDB]') || !version_compare(JVERSION, '3.999.999', 'gt'))
		{
			return [];
		}

		// Get the SQL query, constructed correctly for the DB technology in use.
		$db  = Factory::getDatabase();
		$sql = (string) $db->getQuery(true)
			->insert('#__finder_taxonomy')
			->columns(array_map([$db, 'quoteName'], [
				'id', 'parent_id', 'lft', 'rgt', 'level', 'path', 'title', 'alias', 'state', 'access', 'language',
			]))
			->values(implode(", ", array_map([$db, 'quote'], [
				1, 0, 0, 1, 0, '', 'ROOT', 'root', 1, 1, '*',
			])));

		// Make sure there's a trailing semicolon before returning the SQL query.
		$sql = rtrim(trim($sql), ';') . ';';

		return [$sql];
	}

	/**
	 * This method must be overriden by API-type exclusion filters.
	 *
	 * @param   string  $test  The object to test for exclusion
	 * @param   string  $root  The object's root
	 *
	 * @return  bool    Return true if it matches your filters
	 *
	 * @since   3.4.0
	 */
	protected function is_excluded_by_api($test, $root)
	{
		static $finderTables = [
			/**
			 * Common tables, J3 and J4.
			 *
			 * Note that the taxonomy table contents are removed BUT the root node for Joomla 4 is added back with the
			 * getExtraSQL() method trick.
			 */
			'#__finder_links', '#__finder_taxonomy', '#__finder_taxonomy_map', '#__finder_terms',
			// Joomla 3 only
			'#__finder_links_terms0', '#__finder_links_terms1',
			'#__finder_links_terms2', '#__finder_links_terms3', '#__finder_links_terms4',
			'#__finder_links_terms5', '#__finder_links_terms6', '#__finder_links_terms7',
			'#__finder_links_terms8', '#__finder_links_terms9', '#__finder_links_termsa',
			'#__finder_links_termsb', '#__finder_links_termsc', '#__finder_links_termsd',
			'#__finder_links_termse', '#__finder_links_termsf',
			// Joomla 4 only
			'#__finder_links_terms', '#__finder_logging',
		];

		// Not the site's database? Include the tables
		if ($root != '[SITEDB]')
		{
			return false;
		}

		// Is it one of the blacklisted tables?
		if (in_array($test, $finderTables))
		{
			return true;
		}

		// No match? Just include the file!
		return false;
	}

}
