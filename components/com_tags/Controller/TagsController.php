<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_tags
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Tags\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Table\Table;
use Joomla\Utilities\ArrayHelper;

/**
 * The Tags List Controller
 *
 * @since  3.1
 */
class TagsController extends BaseController
{
	/**
	 * Method to search tags with A\JAX
	 *
	 * @return  void
	 */
	public function searchAjax()
	{
		$user = $this->app->getIdentity();

		// Receive request data
		$filters = array(
			'like'      => trim($this->input->get('like', null, 'string')),
			'title'     => trim($this->input->get('title', null, 'string')),
			'flanguage' => $this->input->get('flanguage', null, 'word'),
			'published' => $this->input->get('published', 1, 'int'),
			'parent_id' => $this->input->get('parent_id', 0, 'int'),
			'access'    => $user->getAuthorisedViewLevels(),
		);

		if ((!$user->authorise('core.edit.state', 'com_tags')) && (!$user->authorise('core.edit', 'com_tags')))
		{
			// Filter on published for those who do not have edit or edit.state rights.
			$filters['published'] = 1;
		}

		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('a.id AS value')
			->select('a.path AS text')
			->select('a.path')
			->from('#__tags AS a')
			->join('LEFT', $db->quoteName('#__tags', 'b') . ' ON a.lft > b.lft AND a.rgt < b.rgt');

		// Filter language
		if (!empty($filters['flanguage']))
		{
			$query->where('a.language IN (' . $db->quote($filters['flanguage']) . ',' . $db->quote('*') . ') ');
		}

		// Do not return root
		$query->where($db->quoteName('a.alias') . ' <> ' . $db->quote('root'));

		// Search in title or path
		if (!empty($filters['like']))
		{
			$query->where(
				'(' . $db->quoteName('a.title') . ' LIKE ' . $db->quote('%' . $filters['like'] . '%')
				. ' OR ' . $db->quoteName('a.path') . ' LIKE ' . $db->quote('%' . $filters['like'] . '%') . ')'
			);
		}

		// Filter title
		if (!empty($filters['title']))
		{
			$query->where($db->quoteName('a.title') . ' = ' . $db->quote($filters['title']));
		}

		// Filter on the published state
		if (isset($filters['published']) && is_numeric($filters['published']))
		{
			$query->where('a.published = ' . (int) $filters['published']);
		}

		// Filter on the access level
		if (isset($filters['access']) && is_array($filters['access']) && count($filters['access']))
		{
			$groups = ArrayHelper::toInteger($filters['access']);
			$query->where('a.access IN (' . implode(",", $groups) . ')');
		}

		// Filter by parent_id
		if (isset($filters['parent_id']) && is_numeric($filters['parent_id']))
		{
			Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tags/tables');
			$tagTable = Table::getInstance('Tag', 'TagsTable');

			if ($children = $tagTable->getTree($filters['parent_id']))
			{
				foreach ($children as $child)
				{
					$childrenIds[] = $child->id;
				}

				$query->where('a.id IN (' . implode(',', $childrenIds) . ')');
			}
		}

		$query->group('a.id, a.title, a.level, a.lft, a.rgt, a.parent_id, a.published, a.path')
			->order('a.lft ASC');

		// Get the options.
		$db->setQuery($query);

		try
		{
			$results = $db->loadObjectList();
		}
		catch (\RuntimeException $e)
		{
			$results = array();
		}

		// We will replace path aliases with tag names
		$results = TagsHelper::convertPathsToNames($results);

		if ($results)
		{
			// Output a JSON object
			echo json_encode($results);
		}

		$this->app->close();
	}
}
