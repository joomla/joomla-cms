<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * Extension Manager Abstract Extension Model.
 *
 * @since  1.5
 */
class InstallerModel extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JController
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'name',
				'client_id',
				'client', 'client_translated',
				'enabled',
				'type', 'type_translated',
				'folder', 'folder_translated',
				'extension_id',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Returns an object list
	 *
	 * @param   string  $query       The query
	 * @param   int     $limitstart  Offset
	 * @param   int     $limit       The number of records
	 *
	 * @return  array
	 */
	protected function _getList($query, $limitstart = 0, $limit = 0)
	{
		$listOrder = $this->getState('list.ordering', 'name');
		$listDirn  = $this->getState('list.direction', 'asc');

		// Replace slashes so preg_match will work
		$search = $this->getState('filter.search');
		$search = str_replace('/', ' ', $search);
		$db     = $this->getDbo();

		// Define which fields have to be processed in a custom way because of translation.
		$customOrderFields = array('name', 'client_translated', 'type_translated', 'folder_translated');

		// Process searching, ordering and pagination for fields that need to be translated.
		if (in_array($listOrder, $customOrderFields) || (!empty($search) && stripos($search, 'id:') !== 0))
		{
			// Get results from database and translate them.
			$db->setQuery($query);
			$result = $db->loadObjectList();
			$this->translate($result);

			// Process searching.
			if (!empty($search) && stripos($search, 'id:') !== 0)
			{
				$escapedSearchString = $this->refineSearchStringToRegex($search, '/');

				// By default search only the extension name field.
				$searchFields = array('name');

				// If in update sites view search also in the update site name field.
				if ($this instanceof InstallerModelUpdatesites)
				{
					$searchFields[] = 'update_site_name';
				}

				foreach ($result as $i => $item)
				{
					// Check if search string exists in any of the fields to be searched.
					$found = 0;

					foreach ($searchFields as $key => $field)
					{
						if (!$found && preg_match('/' . $escapedSearchString . '/i', $item->{$field}))
						{
							$found = 1;
						}
					}

					// If search string was not found in any of the fields searched remove it from results array.
					if (!$found)
					{
						unset($result[$i]);
					}
				}
			}

			// Process ordering.
			// Sort array object by selected ordering and selected direction. Sort is case insensative and using locale sorting.
			$result = ArrayHelper::sortObjects($result, $listOrder, strtolower($listDirn) == 'desc' ? -1 : 1, false, true);

			// Process pagination.
			$total = count($result);
			$this->cache[$this->getStoreId('getTotal')] = $total;

			if ($total < $limitstart)
			{
				$limitstart = 0;
				$this->setState('list.start', 0);
			}

			return array_slice($result, $limitstart, $limit ?: null);
		}

		// Process searching, ordering and pagination for regular database fields.
		$query->order($db->quoteName($listOrder) . ' ' . $db->escape($listDirn));
		$result = parent::_getList($query, $limitstart, $limit);
		$this->translate($result);

		return $result;
	}

	/**
	 * Translate a list of objects
	 *
	 * @param   array  $items  The array of objects
	 *
	 * @return  array The array of translated objects
	 */
	protected function translate(&$items)
	{
		$lang = JFactory::getLanguage();

		foreach ($items as &$item)
		{
			if (strlen($item->manifest_cache) && $data = json_decode($item->manifest_cache))
			{
				foreach ($data as $key => $value)
				{
					if ($key == 'type')
					{
						// Ignore the type field
						continue;
					}

					$item->$key = $value;
				}
			}

			$item->author_info       = @$item->authorEmail . '<br />' . @$item->authorUrl;
			$item->client            = $item->client_id ? JText::_('JADMINISTRATOR') : JText::_('JSITE');
			$item->client_translated = $item->client;
			$item->type_translated   = JText::_('COM_INSTALLER_TYPE_' . strtoupper($item->type));
			$item->folder_translated = @$item->folder ? $item->folder : JText::_('COM_INSTALLER_TYPE_NONAPPLICABLE');

			$path = $item->client_id ? JPATH_ADMINISTRATOR : JPATH_SITE;

			switch ($item->type)
			{
				case 'component':
					$extension = $item->element;
					$source = JPATH_ADMINISTRATOR . '/components/' . $extension;
						$lang->load("$extension.sys", JPATH_ADMINISTRATOR, null, false, true)
					||	$lang->load("$extension.sys", $source, null, false, true);
				break;
				case 'file':
					$extension = 'files_' . $item->element;
						$lang->load("$extension.sys", JPATH_SITE, null, false, true);
				break;
				case 'library':
					$extension = 'lib_' . $item->element;
						$lang->load("$extension.sys", JPATH_SITE, null, false, true);
				break;
				case 'module':
					$extension = $item->element;
					$source = $path . '/modules/' . $extension;
						$lang->load("$extension.sys", $path, null, false, true)
					||	$lang->load("$extension.sys", $source, null, false, true);
				break;
				case 'plugin':
					$extension = 'plg_' . $item->folder . '_' . $item->element;
					$source = JPATH_PLUGINS . '/' . $item->folder . '/' . $item->element;
						$lang->load("$extension.sys", JPATH_ADMINISTRATOR, null, false, true)
					||	$lang->load("$extension.sys", $source, null, false, true);
				break;
				case 'template':
					$extension = 'tpl_' . $item->element;
					$source = $path . '/templates/' . $item->element;
						$lang->load("$extension.sys", $path, null, false, true)
					||	$lang->load("$extension.sys", $source, null, false, true);
				break;
				case 'package':
				default:
					$extension = $item->element;
						$lang->load("$extension.sys", JPATH_SITE, null, false, true);
				break;
			}

			// Translate the extension name if possible
			$item->name = JText::_($item->name);

			settype($item->description, 'string');

			if (!in_array($item->type, array('language')))
			{
				$item->description = JText::_($item->description);
			}
		}
	}
}
