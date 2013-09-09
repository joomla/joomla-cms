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
 * Media Component Manager Model
 *
 * @package     Joomla.Administrator
 * @subpackage  com_media
 * @since       3.2
 */
class MediaModelMedia extends JModelBase
{
	/**
	 * Get State, set trivial case
	 *
	 * @since 3.2
	 */
	public function getState()
	{
		$input = JFactory::getApplication()->input;
		$state = $this->state;
		$folder = $input->get('folder', '', 'path');
		$state->set('folder', $folder);

		$fieldid = $input->get('fieldid', '');
		$state->set('field.id', $fieldid);
		$state->set('filter.search', $input->get('filter_search', '', 'STRING'));
		$state->set('filter.category.id', $input->get('category', '', 'STRING'));
		$state->set('filter.access', $input->get('access', '', 'STRING'));
		$state->set('ordering', $input->get('sortTable', '', 'STRING'));
		$state->set('direction', $input->get('directionTable', '', 'STRING'));

		$parent = str_replace("\\", "/", dirname($folder));
		$parent = ($parent == '.') ? null : $parent;
		$state->set('parent', $parent);
		$set = true;

		return $this->state;
	}

	/**
	 * Get Folder List
	 *
	 * @param   string $base  base path
	 *
	 * @since 3,2
	 */
	function getFolderList($base = null)
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
		$options[] = JHtml::_('select.option', "", "/");

		foreach ($folders as $folder)
		{
			$folder = str_replace($com_media_base_uni, "", str_replace(DIRECTORY_SEPARATOR, '/', $folder));
			$value = substr($folder, 1);
			$text = str_replace(DIRECTORY_SEPARATOR, "/", $folder);
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
		$author = $input->get('author', 0, 'integer');

		// Create the drop-down folder select list
		$list = JHtml::_('select.genericlist', $options, 'folderlist',
			'class="inputbox" size="1" onchange="ImageManager.setFolder(this.options[this.selectedIndex].value, '
			. $asset . ', ' . $author . ')" ', 'value', 'text', $base
		);

		return $list;
	}

	/**
	 * Get Folder Tree
	 *
	 *
	 * @since 3.2
	 */
	function getFolderTree($base = null)
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
			$folder = str_replace(DIRECTORY_SEPARATOR, '/', $folder);
			$name = substr($folder, strrpos($folder, '/') + 1);
			$relative = str_replace($mediaBase, '', $folder);
			$absolute = $folder;
			$path = explode('/', $relative);
			$node = (object) array('name' => $name, 'relative' => $relative, 'absolute' => $absolute);

			$tmp = & $tree;

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
					$tmp = & $tmp['children'][$key];
				}
			}
		}

		$tree['data'] = (object) array('name' => JText::_('COM_MEDIA_MEDIA'), 'relative' => '', 'absolute' => $base);

		return $tree;
	}
}