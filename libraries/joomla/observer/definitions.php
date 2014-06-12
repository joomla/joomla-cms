<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Observer
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * ObserverDefinitions Class implementation
 * 
 */
class JObserverDefinitions
{
	/**
	 * Database driver
	 *
	 * @var JDatabaseDriver
	 */
	protected $db;

	/**
	 * Cache controller (if any)
	 *
	 * @var JCacheController
	 */
	protected $cache;

	/**
	 * Constructor (DI-ready style)
	 *
	 * @param   JDatabaseInterface  $db     A database connector object
	 * @param   JCacheController    $cache  Cache to use
	 */
	public function __construct(JDatabaseInterface $db, JCacheController $cache = null)
	{
		$this->db = $db;
		$this->cache = $cache;
	}

	/**
	 * Loads Observers Mappings from JContentTypes (#__content_types table) and maps them
	 *
	 * @param   boolean             $forceCacheRefresh  Force cache refresh
	 * @return  void
	 *
	 * @throws  RuntimeException
	 */
	public function loadObserversMapping($forceCacheRefresh = true)
	{
		// Load cached mappers if cache exists and does not need to be refreshed:
		if ($this->cache && !$forceCacheRefresh)
		{
			$cached = $this->cache->get('maps', 'observers');

			if ($cached)
			{
				$this->addMappers(json_decode($cached));
				return;
			}
		}

		// Reload mappers from database (this is typically done in the administration area, specially after installations):
		$mappings = $this->reloadMappers();

		// Cache reloaded mappers if cache exists:
		if ($this->cache)
		{
			$this->cache->store(json_encode($mappings), 'maps', 'observers');
		}
	}

	/**
	 * Reloads observers mappings from the Content Types table and from the from the Extensions table
	 *
	 * @return array
	 */
	protected function reloadMappers()
	{
		// Add mappers from the Content Types table:

		/** @var JTableContenttype $contentType */
		$contentType = JTable::getInstance('contenttype', 'JTable', array('dbo' => $this->db));

		$contentMappings = $contentType->loadObserversMapping();

		// Add mappers from the Extensions table:

		/** @var JTableContenttype $contentType */
		$extension = JTable::getInstance('extension', 'JTable', array('dbo' => $this->db));

		$extensionsMappings = $extension->loadObserversMapping();

		$mappings = array_merge($contentMappings, $extensionsMappings);

		$this->addMappers($mappings);

		return $mappings;
	}

	/**
	 * Adds Observer $mappings to the Observer Mapper.
	 *
	 * @param   array  $mappings
	 * @return  void
	 */
	protected function addMappers($mappings)
	{
		foreach ( $mappings as $map )
		{
			// JObserverMapper::addObserverClassToClass('JTableObserverContenthistory', 'JTableContent', array('typeAlias' => 'com_content.article'));
			JObserverMapper::addObserverClassToClass($map->observerClass, $map->observableClass, $map->params);
		}
	}
}
