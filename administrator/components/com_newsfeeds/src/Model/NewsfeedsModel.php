<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_newsfeeds
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Newsfeeds\Administrator\Model;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;
use Joomla\Utilities\ArrayHelper;

/**
 * Methods supporting a list of newsfeed records.
 *
 * @since  1.6
 */
class NewsfeedsModel extends ListModel
{
    /**
     * Constructor.
     *
     * @param   array                $config   An optional associative array of configuration settings.
     * @param   MVCFactoryInterface  $factory  The factory.
     *
     * @see    \Joomla\CMS\MVC\Model\BaseDatabaseModel
     * @since   3.2
     */
    public function __construct($config = array(), MVCFactoryInterface $factory = null)
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'name', 'a.name',
                'alias', 'a.alias',
                'checked_out', 'a.checked_out',
                'checked_out_time', 'a.checked_out_time',
                'catid', 'a.catid', 'category_id', 'category_title',
                'published', 'a.published',
                'access', 'a.access', 'access_level',
                'created', 'a.created',
                'created_by', 'a.created_by',
                'ordering', 'a.ordering',
                'language', 'a.language', 'language_title',
                'publish_up', 'a.publish_up',
                'publish_down', 'a.publish_down',
                'cache_time', 'a.cache_time',
                'numarticles',
                'tag',
                'level', 'c.level',
                'tag',
            );

            if (Associations::isEnabled()) {
                $config['filter_fields'][] = 'association';
            }
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
     * @return  void
     *
     * @since   1.6
     */
    protected function populateState($ordering = 'a.name', $direction = 'asc')
    {
        $app = Factory::getApplication();

        $forcedLanguage = $app->input->get('forcedLanguage', '', 'cmd');

        // Adjust the context to support modal layouts.
        if ($layout = $app->input->get('layout')) {
            $this->context .= '.' . $layout;
        }

        // Adjust the context to support forced languages.
        if ($forcedLanguage) {
            $this->context .= '.' . $forcedLanguage;
        }

        // Load the parameters.
        $params = ComponentHelper::getParams('com_newsfeeds');
        $this->setState('params', $params);

        // List state information.
        parent::populateState($ordering, $direction);

        // Force a language.
        if (!empty($forcedLanguage)) {
            $this->setState('filter.language', $forcedLanguage);
        }
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
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.published');
        $id .= ':' . $this->getState('filter.category_id');
        $id .= ':' . $this->getState('filter.access');
        $id .= ':' . $this->getState('filter.language');
        $id .= ':' . $this->getState('filter.level');
        $id .= ':' . serialize($this->getState('filter.tag'));

        return parent::getStoreId($id);
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return  \Joomla\Database\DatabaseQuery
     */
    protected function getListQuery()
    {
        // Create a new query object.
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);
        $user  = Factory::getUser();

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                [
                    $db->quoteName('a.id'),
                    $db->quoteName('a.name'),
                    $db->quoteName('a.alias'),
                    $db->quoteName('a.checked_out'),
                    $db->quoteName('a.checked_out_time'),
                    $db->quoteName('a.catid'),
                    $db->quoteName('a.numarticles'),
                    $db->quoteName('a.cache_time'),
                    $db->quoteName('a.created_by'),
                    $db->quoteName('a.published'),
                    $db->quoteName('a.access'),
                    $db->quoteName('a.ordering'),
                    $db->quoteName('a.language'),
                    $db->quoteName('a.publish_up'),
                    $db->quoteName('a.publish_down'),
                ]
            )
        )
            ->select(
                [
                    $db->quoteName('l.title', 'language_title'),
                    $db->quoteName('l.image', 'language_image'),
                    $db->quoteName('uc.name', 'editor'),
                    $db->quoteName('ag.title', 'access_level'),
                    $db->quoteName('c.title', 'category_title'),
                ]
            )
            ->from($db->quoteName('#__newsfeeds', 'a'))
            ->join('LEFT', $db->quoteName('#__languages', 'l'), $db->quoteName('l.lang_code') . ' = ' . $db->quoteName('a.language'))
            ->join('LEFT', $db->quoteName('#__users', 'uc'), $db->quoteName('uc.id') . ' = ' . $db->quoteName('a.checked_out'))
            ->join('LEFT', $db->quoteName('#__viewlevels', 'ag'), $db->quoteName('ag.id') . ' = ' . $db->quoteName('a.access'))
            ->join('LEFT', $db->quoteName('#__categories', 'c'), $db->quoteName('c.id') . ' = ' . $db->quoteName('a.catid'));

        // Join over the associations.
        if (Associations::isEnabled()) {
            $subQuery = $db->getQuery(true)
                ->select('COUNT(' . $db->quoteName('asso1.id') . ') > 1')
                ->from($db->quoteName('#__associations', 'asso1'))
                ->join('INNER', $db->quoteName('#__associations', 'asso2'), $db->quoteName('asso1.key') . ' = ' . $db->quoteName('asso2.key'))
                ->where(
                    [
                        $db->quoteName('asso1.id') . ' = ' . $db->quoteName('a.id'),
                        $db->quoteName('asso1.context') . ' = ' . $db->quote('com_newsfeeds.item'),
                    ]
                );

            $query->select('(' . $subQuery . ') AS ' . $db->quoteName('association'));
        }

        // Filter by access level.
        if ($access = (int) $this->getState('filter.access')) {
            $query->where($db->quoteName('a.access') . ' = :access')
                ->bind(':access', $access, ParameterType::INTEGER);
        }

        // Implement View Level Access
        if (!$user->authorise('core.admin')) {
            $query->whereIn($db->quoteName('a.access'), $user->getAuthorisedViewLevels());
        }

        // Filter by published state.
        $published = (string) $this->getState('filter.published');

        if (is_numeric($published)) {
            $published = (int) $published;
            $query->where($db->quoteName('a.published') . ' = :published')
                ->bind(':published', $published, ParameterType::INTEGER);
        } elseif ($published === '') {
            $query->where($db->quoteName('a.published') . ' IN (0, 1)');
        }

        // Filter by category.
        $categoryId = $this->getState('filter.category_id');

        if (is_numeric($categoryId)) {
            $categoryId = (int) $categoryId;
            $query->where($db->quoteName('a.catid') . ' = :categoryId')
                ->bind(':categoryId', $categoryId, ParameterType::INTEGER);
        }

        // Filter on the level.
        if ($level = (int) $this->getState('filter.level')) {
            $query->where($db->quoteName('c.level') . ' <= :level')
                ->bind(':level', $level, ParameterType::INTEGER);
        }

        // Filter by search in title
        if ($search = $this->getState('filter.search')) {
            if (stripos($search, 'id:') === 0) {
                $search = (int) substr($search, 3);
                $query->where($db->quoteName('a.id') . ' = :search')
                    ->bind(':search', $search, ParameterType::INTEGER);
            } else {
                $search = '%' . str_replace(' ', '%', trim($search)) . '%';
                $query->where('(' . $db->quoteName('a.name') . ' LIKE :search1 OR ' . $db->quoteName('a.alias') . ' LIKE :search2)')
                    ->bind([':search1', ':search2'], $search);
            }
        }

        // Filter on the language.
        if ($language = $this->getState('filter.language')) {
            $query->where($db->quoteName('a.language') . ' = :language')
                ->bind(':language', $language);
        }

        // Filter by a single or group of tags.
        $tag = $this->getState('filter.tag');

        // Run simplified query when filtering by one tag.
        if (\is_array($tag) && \count($tag) === 1) {
            $tag = $tag[0];
        }

        if ($tag && \is_array($tag)) {
            $tag = ArrayHelper::toInteger($tag);

            $subQuery = $db->getQuery(true)
                ->select('DISTINCT ' . $db->quoteName('content_item_id'))
                ->from($db->quoteName('#__contentitem_tag_map'))
                ->where(
                    [
                        $db->quoteName('tag_id') . ' IN (' . implode(',', $query->bindArray($tag)) . ')',
                        $db->quoteName('type_alias') . ' = ' . $db->quote('com_newsfeeds.newsfeed'),
                    ]
                );

            $query->join(
                'INNER',
                '(' . $subQuery . ') AS ' . $db->quoteName('tagmap'),
                $db->quoteName('tagmap.content_item_id') . ' = ' . $db->quoteName('a.id')
            );
        } elseif ($tag = (int) $tag) {
            $query->join(
                'INNER',
                $db->quoteName('#__contentitem_tag_map', 'tagmap'),
                $db->quoteName('tagmap.content_item_id') . ' = ' . $db->quoteName('a.id')
            )
                ->where(
                    [
                        $db->quoteName('tagmap.tag_id') . ' = :tag',
                        $db->quoteName('tagmap.type_alias') . ' = ' . $db->quote('com_newsfeeds.newsfeed'),
                    ]
                )
                ->bind(':tag', $tag, ParameterType::INTEGER);
        }

        // Add the list ordering clause.
        $orderCol  = $this->state->get('list.ordering', 'a.name');
        $orderDirn = $this->state->get('list.direction', 'ASC');

        if ($orderCol == 'a.ordering' || $orderCol == 'category_title') {
            $ordering = [
                $db->quoteName('c.title') . ' ' . $db->escape($orderDirn),
                $db->quoteName('a.ordering') . ' ' . $db->escape($orderDirn),
            ];
        } else {
            $ordering = $db->escape($orderCol) . ' ' . $db->escape($orderDirn);
        }

        $query->order($ordering);

        return $query;
    }
}
