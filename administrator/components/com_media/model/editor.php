<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

/**
 * Media Component Image Editor Model
 *
 * @package     Joomla.Administrator
 * @subpackage  com_media
 * @since       3.2
 */

class MediaModelEditor extends JModelDatabase
{
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

	/**
	 * Check in image
	 *
	 * @return boolean
	 *
	 * @since 3.2
	 */
	public function checkIn($path)
	{
		$path = $this->fixPath($path);
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_media/tables');
		$row =& JTable::getInstance('media', 'mediaTable');
		$id = $this->getImageInfo()->content_id;

		return $row->checkin($id);
	}

	/**
	 * Check in media in bulk
	 *
	 * @since 3.2
	 */
	public function checkInBulk($id = 0)
	{
		$input = JFactory::getApplication()->input;
		$paths = $input->get('rm', array(), 'array');
		$folder = $input ->get('folder', '', 'STRING');

		foreach ($paths as $path)
		{
			if ($this->isImage($path))
			{
				$input->set('editing', $path);
				$this->checkIn($folder . '/' . $path);
			}
		}
	}

	/**
	 * Check in all images
	 *
	 * @since 3.2
	 */
	public function checkInAll($id = 0)
	{
		$query = $this->db->getQuery(true);
		$query->update($this->db->quoteName('#__media'))
			->set('check_in = ' . $this->db->quote($id));
		$this->db->setQuery($query);
		$this->db->execute();
	}

	/**
	 * Check out image
	 *
	 * @since 3.2
	 */
	public function checkOut($path)
	{
		$path = $this->fixPath($path);
		$user = JFactory::getUser();
		$input = JFactory::getApplication()->input;
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_media/tables');
		$row =& JTable::getInstance('media', 'mediaTable');
		$input->set('editing', $path);
		$id = $this->getImageInfo()->content_id;

		return $row->checkout($user->id, $id);
	}

	/**
	 * Check out image
	 *
	 * @since 3.2
	 */
	public function checkOutBulk($id = 0)
	{
		$input = JFactory::getApplication()->input;
		$paths = $input->get('rm', array(), 'array');
		$folder = $input ->get('folder', '', 'STRING');

		foreach ($paths as $path)
		{
			if ($this->isImage($path))
			{
				$input->set('editing', $path);
				$this->checkOut($folder . '/' . $path, $id);
			}
		}
	}

	/**
	 * Add image to database
	 *
	 * @since 3.2
	 */
	public function addImage($file)
	{
		$file = $this->fixPath($file);
		$path_parts = pathinfo($file);
		$db = $this->db;
		$query = $db->getQuery(true);
		$user = JFactory::getUser();

		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_media/tables');
		$row =& JTable::getInstance('media', 'mediaTable');

		$columns = array('type_alias', 'title', 'alias', 'body', 'checked_out_time', 'params',
			'metadata', 'created_user_id', 'created_by_alias', 'created_time', 'modified_time',
			'language', 'publish_up', 'publish_down', 'content_item_id', 'asset_id', 'images', 'urls',
			'metakey', 'metadesc', 'catid', 'xreference', 'type_id');

		$values = array($db->quote(''), $db->quote($path_parts['filename']), $db->quote($path_parts['filename']),$db->quote(''),
			$db->quote('0000-00-00 00:00:00'), $db->quote(''), $db->quote(''), $db->quote($user->id), $db->quote($user->username), $db->quote(date('Y/m/d h:i:s')),
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
	 * Check if media is image
	 *
	 * @since 3.2
	 */
	public static function isImage($fileName)
	{
		static $imageTypes = 'xcf|odg|gif|jpg|png|bmp';

		return preg_match("/\.(?:$imageTypes)$/i", $fileName);
	}

	/**
	 * Get JForm
	 *
	 * @since 3.2
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$form = new JForm('editor');
		$form->loadFile(JPATH_ADMINISTRATOR . '/components/com_media/model/forms/editor.xml');

		return $form;
	}

	/**
	 * Fix the editing path
	 *
	 * @since 3.2
	 */
	public function fixPath($path = null)
	{
		$path = str_replace('/', '\\', $path);
		$path = trim($path, "\\");

		return $path;
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
}
