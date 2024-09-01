<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_tags
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Tags\Administrator\Model;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Tag\TagServiceInterface;
use Joomla\Database\ParameterType;
use Joomla\Database\QueryInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Tags Component Tags Model
 *
 * @since  3.1
 */
class TagsModel extends ListModel
{
    /**
     * Constructor.
     *
     * @param   array                 $config   An optional associative array of configuration settings.
     * @param   ?MVCFactoryInterface  $factory  The factory.
     *
     * @since   1.6
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id',
                'a.id',
                'title',
                'a.title',
                'alias',
                'a.alias',
                'published',
                'a.published',
                'access',
                'a.access',
                'access_level',
                'language',
                'a.language',
                'checked_out',
                'a.checked_out',
                'checked_out_time',
                'a.checked_out_time',
                'created_time',
                'a.created_time',
                'created_user_id',
                'a.created_user_id',
                'lft',
                'a.lft',
                'rgt',
                'a.rgt',
                'level',
                'a.level',
                'path',
                'a.path',
                'countTaggedItems',
            ];
        }

        parent::__construct($config, $factory);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param   string  $ordering   An optional ordering field.
     * @param   string  $direction  An optional direction (asc|desc).
     *
     * @return    void
     *
     * @since    3.1
     */
    protected function populateState($ordering = 'a.lft', $direction = 'asc')
    {
        $extension = $this->getUserStateFromRequest($this->context . '.filter.extension', 'extension', 'com_content', 'cmd');

        $this->setState('filter.extension', $extension);
        $parts = explode('.', $extension);

        // Extract the component name
        $this->setState('filter.component', $parts[0]);

        // Extract the optional section name
        $this->setState('filter.section', (\count($parts) > 1) ? $parts[1] : null);

        // Load the parameters.
        $params = ComponentHelper::getParams('com_tags');
        $this->setState('params', $params);

        // List state information.
        parent::populateState($ordering, $direction);
    }

    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param   string  $id  A prefix for the store id.
     *
     * @return  string  A store id.
     *
     * @since   3.1
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.extension');
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.level');
        $id .= ':' . $this->getState('filter.access');
        $id .= ':' . $this->getState('filter.published');
        $id .= ':' . $this->getState('filter.language');

        return parent::getStoreId($id);
    }

    /**
     * Method to create a query for a list of items.
     *
     * @return  QueryInterface
     *
     * @since  3.1
     */
    protected function getListQuery()
    {
        // Create a new query object.
        $db    = $this->getDatabase();
        $query = $db->createQuery();
        $user  = $this->getCurrentUser();

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.id, a.title, a.alias, a.note, a.published, a.access, a.description' .
                ', a.checked_out, a.checked_out_time, a.created_user_id' .
                ', a.path, a.parent_id, a.level, a.lft, a.rgt' .
                ', a.language'
            )
        );
        $query->from($db->quoteName('#__tags', 'a'))
            ->where($db->quoteName('a.alias') . ' <> ' . $db->quote('root'));

        // Join over the language
        $query->select(
            [
                $db->quoteName('l.title', 'language_title'),
                $db->quoteName('l.image', 'language_image'),
            ]
        )
            ->join('LEFT', $db->quoteName('#__languages', 'l'), $db->quoteName('l.lang_code') . ' = ' . $db->quoteName('a.language'));

        // Join over the users for the checked out user.
        $query->select($db->quoteName('uc.name', 'editor'))
            ->join('LEFT', $db->quoteName('#__users', 'uc'), $db->quoteName('uc.id') . ' = ' . $db->quoteName('a.checked_out'));

        // Join over the users for the author.
        $query->select($db->quoteName('ua.name', 'author_name'))
            ->join('LEFT', $db->quoteName('#__users', 'ua'), $db->quoteName('ua.id') . ' = ' . $db->quoteName('a.created_user_id'))
            ->select($db->quoteName('ug.title', 'access_title'))
            ->join('LEFT', $db->quoteName('#__viewlevels', 'ug'), $db->quoteName('ug.id') . ' = ' . $db->quoteName('a.access'));

        // Count Items
        $subQueryCountTaggedItems = $db->createQuery();
        $subQueryCountTaggedItems
            ->select('COUNT(' . $db->quoteName('tag_map.content_item_id') . ')')
            ->from($db->quoteName('#__contentitem_tag_map', 'tag_map'))
            ->where($db->quoteName('tag_map.tag_id') . ' = ' . $db->quoteName('a.id'));
        $query->select('(' . (string) $subQueryCountTaggedItems . ') AS ' . $db->quoteName('countTaggedItems'));

        // Filter on the level.
        if ($level = (int) $this->getState('filter.level')) {
            $query->where($db->quoteName('a.level') . ' <= :level')
                ->bind(':level', $level, ParameterType::INTEGER);
        }

        // Filter by access level.
        if ($access = (int) $this->getState('filter.access')) {
            $query->where($db->quoteName('a.access') . ' = :access')
                ->bind(':access', $access, ParameterType::INTEGER);
        }

        // Implement View Level Access
        if (!$user->authorise('core.admin')) {
            $groups = $user->getAuthorisedViewLevels();
            $query->whereIn($db->quoteName('a.access'), $groups);
        }

        // Filter by published state
        $published = (string) $this->getState('filter.published');

        if (is_numeric($published)) {
            $published = (int) $published;
            $query->where($db->quoteName('a.published') . ' = :published')
                ->bind(':published', $published, ParameterType::INTEGER);
        } elseif ($published === '') {
            $query->whereIn($db->quoteName('a.published'), [0, 1]);
        }

        // Filter by search in title
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $ids = (int) substr($search, 3);
                $query->where($db->quoteName('a.id') . ' = :id')
                    ->bind(':id', $ids, ParameterType::INTEGER);
            } else {
                $search = '%' . str_replace(' ', '%', trim($search)) . '%';
                $query->extendWhere(
                    'AND',
                    [
                        $db->quoteName('a.title') . ' LIKE :title',
                        $db->quoteName('a.alias') . ' LIKE :alias',
                        $db->quoteName('a.note') . ' LIKE :note',

                    ],
                    'OR'
                );
                $query->bind(':title', $search)
                    ->bind(':alias', $search)
                    ->bind(':note', $search);
            }
        }

        // Filter on the language.
        if ($language = $this->getState('filter.language')) {
            $query->where($db->quoteName('a.language') . ' = :language')
                ->bind(':language', $language);
        }

        // Add the list ordering clause
        $listOrdering = $this->getState('list.ordering', 'a.lft');
        $listDirn     = $db->escape($this->getState('list.direction', 'ASC'));

        if ($listOrdering == 'a.access') {
            $query->order('a.access ' . $listDirn . ', a.lft ' . $listDirn);
        } else {
            $query->order($db->escape($listOrdering) . ' ' . $listDirn);
        }

        return $query;
    }

    /**
     * Method to get an array of data items.
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   3.0.1
     */
    public function getItems()
    {
        $items = parent::getItems();

        if ($items != false) {
            $extension = $this->getState('filter.extension');

            $this->countItems($items, $extension);
        }

        return $items;
    }

    /**
     * Method to load the countItems method from the extensions
     *
     * @param   \stdClass[]  &$items      The category items
     * @param   string        $extension  The category extension
     *
     * @return  void
     *
     * @since   3.5
     */
    public function countItems(&$items, $extension)
    {
        $parts = explode('.', $extension);

        if (\count($parts) < 2) {
            return;
        }

        $component = Factory::getApplication()->bootComponent($parts[0]);

        if ($component instanceof TagServiceInterface) {
            $component->countTagItems($items, $extension);
        }
    }

    /**
     * Manipulate the query to be used to evaluate if this is an Empty State to provide specific conditions for this extension.
     *
     * @return QueryInterface
     *
     * @since 4.0.0
     */
    protected function getEmptyStateQuery()
    {
        $query = parent::getEmptyStateQuery();

        $db = $this->getDatabase();

        $query->where($db->quoteName('alias') . ' != ' . $db->quote('root'));

        return $query;
    }
}
