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
 * ${NAMESPACE}\ObserverDefinitions Class implementation
 * 
 */
class JObserverDefinitions
{
	/**
	 * @var JDatabaseDriver
	 */
	public $db;

	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  $db  A database connector object
	 */
	public function __construct( $db )
	{
		$this->db = $db;
	}

	/**
	 * Loads Observers Mappings from JContentTypes (#__content_types table) and maps them
	 *
	 * @return  void
	 *
	 * @throws  RuntimeException
	 */
	public function loadObserversMapping()
	{
		/** @var JTableContenttype $contentType */
		$contentType = JTable::getInstance('contenttype', 'JTable', array('dbo' => $this->db));

		$mappings = $contentType->loadObserversMapping();

		foreach ( $mappings as $map )
		{
			// JObserverMapper::addObserverClassToClass('JTableObserverContenthistory', 'JTableContent', array('typeAlias' => 'com_content.article'));
			JObserverMapper::addObserverClassToClass($map->observerClass, $map->observableClass, $map->params);
		}
	}
}
