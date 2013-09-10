<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Media Component Manager Default Model
 *
 * @package     Joomla.Administrator
 * @subpackage  com_media
 * @since       3.2
 */
class MediaModelSync extends JModelDatabase
{
	/**
	 * Add image to database
	 *
	 * @since 3.2
	 */
	public function addImage($file)
	{
		$path_parts = pathinfo($file);
		$db = $this->db;
		$query = $db->getQuery(true);

		$columns = array('name', 'path', 'created', 'created_by');

		$values = array($db->quote($path_parts['basename']), $db->quote($file), $db->quote(date('Y:m:d H:i:s')), $db->quote('0'));
		$query
			->insert('#__media')
			->columns($columns)
			->values(implode(',', $values));
		$db->setQuery($query);
		$db->query();
	}

	/**
	 * Add image to database from uploading
	 *
	 * @since 3.2
	 */
	public function addImageFromUploading($file)
	{
		$path_parts = pathinfo($file);
		JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_media/tables');
		$row =& JTable::getInstance('media', 'mediaTable');
		$user = JFactory::getUser();

		$columns = array('type_alias', 'title', 'alias', 'body', 'checked_out_time', 'params',
			'metadata', 'created_user_id', 'created_by_alias', 'created_time', 'modified_time',
			'language', 'publish_up', 'publish_down', 'content_item_id', 'asset_id', 'images', 'urls',
			'metakey', 'metadesc', 'catid', 'xreference', 'type_id');

		$values = array('', $path_parts['filename'], $path_parts['filename'],'',
			'0000-00-00 00:00:00', '', '', $user->id, $user->username, date('Y/m/d h:i:s'),
			date('Y:m:d H:i:s'), '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0', '0',
			'', $file, '', '', '0', '', '0');

		$bindArray = array_combine($columns, $values);
		$actions = JAccess::getActions('com_media', 'media');
		$actionArray = array();
		foreach ($actions as $action) {
			$actionArray[$action->name] = array();
		}
		$bindArray['rules'] = $actionArray;
		$row->bind($bindArray);
		$row->store();
	}

	/**
	 * Delete image from database
	 *
	 * @since 3.2
	 */
	public function deleteImage($file)
	{
		$db = $this->db;
		$query = $db->getQuery(true);
		$conditions = array('urls=' . $db->quote($file));
		$query
			->delete('#__ucm_media')
			->where($conditions);
		$db->setQuery($query);
		$db->query();
	}

	/**
	 * Get media list from database
	 *
	 * @since 3.2
	 */
	function getFilesListFromDatabase()
	{
		$query = $this->db->getQuery(true);
		$query->select(array('title', 'alias', 'body', 'checked_out_time', 'params',
			'metadata', 'created_by_alias', 'created_time', 'modified_time',
			'language', 'publish_up', 'publish_down', 'content_item_id', 'asset_id', 'images', 'urls',
			'metakey', 'metadesc', 'catid', 'xreference', 'type_id'))
			->from('#__ucm_media');
		$this->db->setQuery($query);

		return $this->db->loadObjectList();
	}

	/**
	 * Check file deletion using ftp
	 *
	 * @since 3.2
	 */
	function checkDisc($fileOnDisc, $fileInDatabase)
	{
		// Check deletion using ftp
		for ($i = 0; $i < count($fileInDatabase); $i++)
		{
			$find = $this->binary_search($fileOnDisc, 0, count($fileOnDisc), $fileInDatabase[$i]);

			if (($find < 0) || (strcmp($fileInDatabase[$i], $fileOnDisc[$find]) != 0))
			{
				// Users delete files using ftp
				$this->deleteFromDatabase($fileInDatabase[$i]);
			}
		}
	}

	/**
	 * Check uploads from ftp
	 *
	 * @since 3.2
	 */
	function checkDatabase($fileOnDisc, $fileInDatabase)
	{
		// Check uploads from ftp
		for ($i = 0; $i < count($fileOnDisc); $i++)
		{
			$find = $this->binary_search($fileInDatabase, 0, count($fileInDatabase), $fileOnDisc[$i]);

			if (($find < 0) || (strcmp($fileOnDisc[$i], $fileInDatabase[$find]) != 0))
			{
				// Users upload files through ftp
				$this->insertToDatabase($fileOnDisc[$i]);
			}
		}
	}

	/**
	 * Add image to database
	 *
	 * @since 3.2
	 */
	function insertToDatabase($file)
	{
		$path_parts = pathinfo($file);
		$db = $this->db;
		$query = $db->getQuery(true);

		$columns = array('type_alias', 'title', 'alias', 'body', 'checked_out_time', 'params',
		'metadata', 'created_by_alias', 'created_time', 'modified_time',
		'language', 'publish_up', 'publish_down', 'content_item_id', 'asset_id', 'images', 'urls',
		'metakey', 'metadesc', 'catid', 'xreference', 'type_id');

		$values = array($db->quote(''), $db->quote($path_parts['filename']), $db->quote($path_parts['filename']),$db->quote(''),
			$db->quote('0000-00-00 00:00:00'), $db->quote(''), $db->quote(''), $db->quote('ftp'), $db->quote(date('Y/m/d h:i:s')),
			$db->quote(date('Y:m:d H:i:s')), $db->quote(''), $db->quote('0000-00-00 00:00:00'), $db->quote('0000-00-00 00:00:00'), $db->quote('0'), $db->quote('0'),
			$db->quote(''), $db->quote($file), $db->quote(''), $db->quote(''), $db->quote('0'), $db->quote(''), $db->quote('0'));
		$query
			->insert('#__ucm_media')
			->columns($columns)
			->values(implode(',', $values));
		$db->setQuery($query);
		$db->query();
	}

	/**
	 * Delete image from database
	 *
	 * @since 3.2
	 */
	function deleteFromDatabase($file)
	{
		$db = $this->db;
		$query = $db->getQuery(true);
		$conditions = array('urls=' . $db->quote($file));
		$query
			->delete('#__ucm_media')
			->where($conditions);
		$db->setQuery($query);
		$db->query();
	}

	/**
	 * Rename image from database
	 *
	 * @since 3.2
	 */
	function renameDatabase($fileBeforeRename, $fileAfterRename)
	{
		$fileBeforeRename = str_replace('/', '\\', $fileBeforeRename);
		$fileAfterRename = str_replace('/', '\\', $fileAfterRename);
		$db = $this->db;
		$query = $db->getQuery(true);
		$pathInfoBeforeRename = pathinfo($fileBeforeRename);
		$pathInfoAfterRename = pathinfo($fileAfterRename);
		$user = JFactory::getUser();

		$setValue = array('urls='. $db->quote($fileAfterRename),
			'alias='. $db->quote($pathInfoAfterRename['filename']), 'modified_user_id='. $db->quote($user->id),
			'modified_time='. $db->quote(date('Y:m:d H:i:s')));
		$conditions = array('urls=' . $db->quote($fileBeforeRename));
		$query
			->update('#__ucm_media')
			->set($setValue)
			->where($conditions);
		$db->setQuery($query);
		$db->query();
	}

	/**
	 * Binary search
	 *
	 * @since 3.2
	 */
	function binary_search(array $a, $first, $last, $key)
	{
		$lo = $first;
		$hi = $last - 1;

		while ($lo <= $hi)
		{
			$mid = (int) (($hi - $lo) / 2) + $lo;
			$cmp = strcmp($a[$mid], $key);

			if ($cmp < 0)
			{
				$lo = $mid + 1;
			}
			elseif ($cmp > 0)
			{
				$hi = $mid - 1;
			}
			else
			{
				return $mid;
			}
		}

		return -($lo + 1);
	}

	/**
	 * Check if the item is checked out
	 *
	 * @since 3.2
	 */
	public function isCheckedOut($path)
	{
		$path = $this->fixPath($path);
		$user = JFactory::getUser();
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_media/tables');
		$row =& JTable::getInstance('media', 'mediaTable');
		$id = $this->getImageInfo()->content_id;
		$row->load($id);

		return $row->isCheckedOut($user->id);
	}

	/**
	 * Get Image Information
	 *@since 3.2
	 *
	 * @return $row
	 */
	public function getImageInfo()
	{
		$input = JFactory::getApplication()->input;
		$path = $input->get('editing', '', 'STRING');
		$path = str_replace('/', '\\', $path);
		$db = $this->db;

		$query = $this->db->getQuery(true);
		$columns = array('content_id', 'type_alias', 'title', 'alias', 'body', 'checked_out_time', 'params',
			'metadata', 'created_user_id', 'created_by_alias', 'created_time', 'modified_time',
			'language', 'publish_up', 'publish_down', 'content_item_id', 'asset_id', 'images', 'urls',
			'metakey', 'metadesc', 'catid', 'xreference', 'type_id');
		$query->select($columns)
			->from("#__ucm_media")
			->where("urls = " . $db->quote($path));
		$db->setQuery($query);
		$result = $db->loadObjectList();
		$row = empty($result) ? array() : $result[0];

		return $row;
	}
}
