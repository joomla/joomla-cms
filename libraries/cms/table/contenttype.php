<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Table
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Tags table
 *
 * @package     Joomla.Libraries
 * @subpackage  Table
 * @since       3.1
 */
class JTableContenttype extends JTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  $db  A database connector object
	 *
	 * @since   3.1
	 */
	public function __construct($db)
	{
		parent::__construct('#__content_types', 'type_id', $db);
	}

	/**
	 * Overloaded check method to ensure data integrity.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.1
	 * @throws  UnexpectedValueException
	 */
	public function check()
	{
		// Check for valid name.
		if (trim($this->type_title) == '')
		{
			throw new UnexpectedValueException(sprintf('The title is empty'));
		}

		$this->type_title = ucfirst($this->type_title);

		if (empty($this->type_alias))
		{
			throw new UnexpectedValueException(sprintf('The type_alias is empty'));
		}

		return true;
	}

	/**
	 * Overridden JTable::store.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.1
	 */
	public function store($updateNulls = false)
	{
		// Verify that the alias is unique
		$table = JTable::getInstance('Contenttype', 'JTable');

		if ($table->load(array('type_alias' => $this->type_alias)) && ($table->type_id != $this->type_id || $this->type_id == 0))
		{
			$this->setError(JText::_('COM_TAGS_ERROR_UNIQUE_ALIAS'));

			return false;
		}

		return parent::store($updateNulls);
	}

	/**
	 * Method to expand the field mapping
	 *
	 * @param   boolean  $assoc  True to return an associative array.
	 *
	 * @return  mixed  Array or object with field mappings. Defaults to object.
	 *
	 * @since   3.1
	 */
	public function fieldmapExpand($assoc = true)
	{
		return $this->fieldmap = json_decode($this->fieldmappings, $assoc);
	}

	/**
	 * Method to get the id given the type alias
	 *
	 * @param   string  $typeAlias  Content type alias (for example, 'com_content.article').
	 *
	 * @return  mixed  type_id for this alias if successful, otherwise null.
	 *
	 * @since   3.2
	 */
	public function getTypeId($typeAlias)
	{
		$db = $this->_db;
		$query = $db->getQuery(true);
		$query->select($db->quoteName('type_id'))
			->from($db->quoteName('#__content_types'))		//TODO: use $this->_tbl instead of hardcoded table-name
			->where($db->quoteName('type_alias') . ' = ' . $db->quote($typeAlias));
		$db->setQuery($query);

		return $db->loadResult($query);						//TODO: remove that $query as loadResult() does not take any param
	}

	/**
	 * Loads Observers Mappings from #__content_types table and maps them
	 *
	 * @return  array
	 *
	 * @throws  RuntimeException
	 *
	 * @since 3.3.1
	 */
	public function loadObserversMapping()
	{
		$db = $this->_db;
		$query = $db->getQuery(true);
		$query->select($db->quoteName('observers'))
			->select($db->quoteName('type_alias'))
			->from($db->quoteName($this->_tbl))
			->where($db->quoteName('observers') . ' <> ' . $db->quote(''));
		$db->setQuery($query);

		try
		{
			$observers = $db->loadColumn('type_alias');
		}
		catch (RuntimeException $e)
		{
			// The TableContentType table has not yet been updated, let's keep the site working: Register Observers:
			// This is from 3.1 and 3.2

			return array(
				// Add Tags to Content, Contact, NewsFeeds, WebLinks and Categories: (this is the only link between them here!):
				(object) array('observerClass' => 'JTableObserverTags', 'observableClass' => 'JTableContent', 'params' => array('typeAlias' => 'com_content.article')),
				(object) array('observerClass' => 'JTableObserverTags', 'observableClass' => 'ContactTableContact', 'params' => array('typeAlias' => 'com_contact.contact')),
				(object) array('observerClass' => 'JTableObserverTags', 'observableClass' => 'NewsfeedsTableNewsfeed', 'params' => array('typeAlias' => 'com_newsfeeds.newsfeed')),
				(object) array('observerClass' => 'JTableObserverTags', 'observableClass' => 'WeblinksTableWeblink', 'params' => array('typeAlias' => 'com_weblinks.weblink')),
				(object) array('observerClass' => 'JTableObserverTags', 'observableClass' => 'JTableCategory', 'params' => array('typeAlias' => '{extension}.category')),

				// Register Observers for Version History
				(object) array('observerClass' => 'JTableObserverContenthistory', 'observableClass' => 'ContactTableContact', 'params' => array('typeAlias' => 'com_contact.contact')),
				(object) array('observerClass' => 'JTableObserverContenthistory', 'observableClass' => 'JTableContent', 'params' => array('typeAlias' => 'com_content.article')),
				(object) array('observerClass' => 'JTableObserverContenthistory', 'observableClass' => 'JTableCategory', 'params' => array('typeAlias' => '{extension}.category')),
				(object) array('observerClass' => 'JTableObserverContenthistory', 'observableClass' => 'NewsfeedsTableNewsfeed', 'params' => array('typeAlias' => 'com_newsfeeds.newsfeed')),
				(object) array('observerClass' => 'JTableObserverContenthistory', 'observableClass' => 'WeblinksTableWeblink', 'params' => array('typeAlias' => 'com_weblinks.weblink')),
				(object) array('observerClass' => 'JTableObserverContenthistory', 'observableClass' => 'BannersTableBanner', 'params' => array('typeAlias' => 'com_banners.banner')),
				(object) array('observerClass' => 'JTableObserverContenthistory', 'observableClass' => 'BannersTableClient', 'params' => array('typeAlias' => 'com_banners.client')),
				(object) array('observerClass' => 'JTableObserverContenthistory', 'observableClass' => 'TagsTableTag', 'params' => array('typeAlias' => 'com_tags.tag')),
				(object) array('observerClass' => 'JTableObserverContenthistory', 'observableClass' => 'UsersTableNote', 'params' => array('typeAlias' => 'com_users.note'))
			);
		}

		$observersMapping = array();

		foreach ( $observers as $typeAlias => $mapping ) {
			$map = json_decode($mapping);

			if (!property_exists($map, 'params'))
			{
				$map->params = array();
			}

			if (!array_key_exists('typeAlias', $map->params))
			{
				$map->params['typeAlias'] = $typeAlias;
			}

			$observersMapping[] = $map;
		}

		return $observers;
	}

	/**
	 * Method to get the JTable object for the content type from the table object.
	 *
	 * @return  mixed  JTable object on success, otherwise false.
	 *
	 * @since   3.2
	 */
	public function getContentTable()
	{
		$result = false;
		$tableInfo = json_decode($this->table);

		if (is_object($tableInfo) && isset($tableInfo->special))
		{
			if (is_object($tableInfo->special) && isset($tableInfo->special->type) && isset($tableInfo->special->prefix))
			{
				$result = JTable::getInstance($tableInfo->special->type, $tableInfo->special->prefix);
			}
		}

		return $result;
	}
}
