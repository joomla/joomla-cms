<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Media Component Manager Model
 *
 * @since  1.5
 */
class MediaModelManager extends JModelLegacy
{
	/**
	 * Method to get model state variables
	 *
	 * @param   string  $property  Optional parameter name
	 * @param   mixed   $default   Optional default value
	 *
	 * @return  object  The property where specified, the state object where omitted
	 *
	 * @since   1.5
	 */
	public function getState($property = null, $default = null)
	{
		static $set;

		if (!$set)
		{
			$input = JFactory::getApplication()->input;

			$folder = $input->get('folder', '', 'path');
			$this->setState('folder', $folder);

			$fieldid = $input->get('fieldid', '');
			$this->setState('field.id', $fieldid);

			$parent = str_replace("\\", '/', dirname($folder));
			$parent = ($parent == '.') ? null : $parent;
			$this->setState('parent', $parent);
			$set = true;
		}

		return parent::getState($property, $default);
	}

	/**
	 * Get a select field with a list of available folders
	 *
	 * @param   string  $base  The image directory to display
	 *
	 * @return  html
	 *
	 * @since 1.5
	 */
	public function getFolderList($base = null)
	{
		// Get some paths from the request
		if (empty($base))
		{
			$base = COM_MEDIA_BASE;
		}
		// Corrections for windows paths
		$base = str_replace(DIRECTORY_SEPARATOR, '/', $base);
		$com_media_base_uni = str_replace(DIRECTORY_SEPARATOR, '/', COM_MEDIA_BASE);

		// Get the list of folders
		jimport('joomla.filesystem.folder');
		$folders = JFolder::folders($base, '.', true, true);

		$document = JFactory::getDocument();
		$document->setTitle(JText::_('COM_MEDIA_INSERT_IMAGE'));

		// Build the array of select options for the folder list
		$options[] = JHtml::_('select.option', '', '/');

		foreach ($folders as $folder)
		{
			$folder    = str_replace($com_media_base_uni, '', str_replace(DIRECTORY_SEPARATOR, '/', $folder));
			$value     = substr($folder, 1);
			$text      = str_replace(DIRECTORY_SEPARATOR, '/', $folder);
			$options[] = JHtml::_('select.option', $value, $text);
		}

		// Sort the folder list array
		if (is_array($options))
		{
			sort($options);
		}

		// Get asset and author id (use integer filter)
		$input = JFactory::getApplication()->input;
		$asset = $input->get('asset', 0, 'integer');

		// For new items the asset is a string. JAccess always checks type first
		// so both string and integer are supported.
		if ($asset == 0)
		{
			$asset = htmlspecialchars(json_encode(trim($input->get('asset', 0, 'cmd'))), ENT_COMPAT, 'UTF-8');
		}

		$author = $input->get('author', 0, 'integer');

		// Create the dropdown folder select list
		$attribs = 'size="1" onchange="ImageManager.setFolder(this.options[this.selectedIndex].value, ' . $asset . ', ' . $author . ')" ';
		$list = JHtml::_('select.genericlist', $options, 'folderlist', $attribs, 'value', 'text', $base);

		return $list;
	}

	/**
	 * Get the folder tree
	 *
	 * @param   mixed  $base  Base folder | null for using base media folder
	 *
	 * @return  array
	 *
	 * @since   1.5
	 */
	public function getFolderTree($base = null)
	{
		// Get some paths from the request
		if (empty($base))
		{
			$base = COM_MEDIA_BASE;
		}

		$mediaBase = str_replace(DIRECTORY_SEPARATOR, '/', COM_MEDIA_BASE . '/');

		// Get the list of folders
		jimport('joomla.filesystem.folder');
		$folders = JFolder::folders($base, '.', true, true);

		$tree = array();

		foreach ($folders as $folder)
		{
			$folder   = str_replace(DIRECTORY_SEPARATOR, '/', $folder);
			$name     = substr($folder, strrpos($folder, '/') + 1);
			$relative = str_replace($mediaBase, '', $folder);
			$absolute = $folder;
			$path     = explode('/', $relative);
			$node     = (object) array('name' => $name, 'relative' => $relative, 'absolute' => $absolute);
			$tmp      = &$tree;

			for ($i = 0, $n = count($path); $i < $n; $i++)
			{
				if (!isset($tmp['children']))
				{
					$tmp['children'] = array();
				}

				if ($i == $n - 1)
				{
					// We need to place the node
					$tmp['children'][$relative] = array('data' => $node, 'children' => array());

					break;
				}

				if (array_key_exists($key = implode('/', array_slice($path, 0, $i + 1)), $tmp['children']))
				{
					$tmp = &$tmp['children'][$key];
				}
			}
		}

		$tree['data'] = (object) array('name' => JText::_('COM_MEDIA_MEDIA'), 'relative' => '', 'absolute' => $base);

		return $tree;
	}
}
