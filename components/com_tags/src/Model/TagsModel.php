<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_tags
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Tags\Site\Model;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;
use Joomla\Database\QueryInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * This models supports retrieving a list of tags.
 *
 * @since  3.1
 */
class TagsModel extends ListModel
{
    /**
     * Model context string.
     *
     * @var    string
     * @since  3.1
     */
    public $_context = 'com_tags.tags';

    /**
     * Method to auto-populate the model state.
     *
     * @param   string  $ordering   An optional ordering field.
     * @param   string  $direction  An optional direction (asc|desc).
     *
     * @return  void
     *
     * @note Calling getState in this method will result in recursion.
     *
     * @since   3.1
     */
    protected function populateState($ordering = null, $direction = null)
    {
        $app = Factory::getApplication();

        // Load state from the request.
        $pid = $app->input->getInt('parent_id');
        $this->setState('tag.parent_id', $pid);

        $language = $app->input->getString('tag_list_language_filter');
        $this->setState('tag.language', $language);

        $offset = $app->input->get('limitstart', 0, 'uint');
        $this->setState('list.offset', $offset);
        $app = Factory::getApplication();

        $params = $app->getParams();
        $this->setState('params', $params);

        $this->setState('list.limit', $params->get('maximum', 200));

        $this->setState('filter.published', 1);
        $this->setState('filter.access', true);

        $user = Factory::getUser();

        if ((!$user->authorise('core.edit.state', 'com_tags')) &&  (!$user->authorise('core.edit', 'com_tags'))) {
            $this->setState('filter.published', 1);
        }

        // Optional filter text
        $itemid = $pid . ':' . $app->input->getInt('Itemid', 0);
        $filterSearch = $app->getUserStateFromRequest('com_tags.tags.list.' . $itemid . '.filter_search', 'filter-search', '', 'string');
        $this->setState('list.filter', $filterSearch);
    }

    /**
     * Method to build an SQL query to load the list data.
     *
     * @return  QueryInterface  An SQL query
     *
     * @since   1.6
     */
    protected function getListQuery()
    {
        $app            = Factory::getApplication();
        $user           = Factory::getUser();
        $groups         = $user->getAuthorisedViewLevels();
        $pid            = (int) $this->getState('tag.parent_id');
        $orderby        = $this->state->params->get('all_tags_orderby', 'title');
        $published      = (int) $this->state->params->get('published', 1);
        $orderDirection = $this->state->params->get('all_tags_orderby_direction', 'ASC');
        $language       = $this->getState('tag.language');

        // Create a new query object.
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        // Select required fields from the tags.
        $query->select('a.*, u.name as created_by_user_name, u.email')
            ->from($db->quoteName('#__tags', 'a'))
            ->join('LEFT', $db->quoteName('#__users', 'u'), $db->quoteName('a.created_user_id') . ' = ' . $db->quoteName('u.id'))
            ->whereIn($db->quoteName('a.access'), $groups);

        if (!empty($pid)) {
            $query->where($db->quoteName('a.parent_id') . ' = :pid')
                ->bind(':pid', $pid, ParameterType::INTEGER);
        }

        // Exclude the root.
        $query->where($db->quoteName('a.parent_id') . ' <> 0');

        // Optionally filter on language
        if (empty($language)) {
            $language = ComponentHelper::getParams('com_tags')->get('tag_list_language_filter', 'all');
        }

        if ($language !== 'all') {
            if ($language === 'current_language') {
                $language = ContentHelper::getCurrentLanguage();
            }

            $query->whereIn($db->quoteName('language'), [$language, '*'], ParameterType::STRING);
        }

        // List state information
        $format = $app->input->getWord('format');

        if ($format === 'feed') {
            $limit = $app->get('feed_limit');
        } else {
            if ($this->state->params->get('show_pagination_limit')) {
                $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->get('list_limit'), 'uint');
            } else {
                $limit = $this->state->params->get('maximum', 20);
            }
        }

        $this->setState('list.limit', $limit);

        $offset = $app->input->get('limitstart', 0, 'uint');
        $this->setState('list.start', $offset);

        // Optionally filter on entered value
        if ($this->state->get('list.filter')) {
            $title = '%' . $this->state->get('list.filter') . '%';
            $query->where($db->quoteName('a.title') . ' LIKE :title')
                ->bind(':title', $title);
        }

        $query->where($db->quoteName('a.published') . ' = :published')
            ->bind(':published', $published, ParameterType::INTEGER);

        $query->order($db->quoteName($orderby) . ' ' . $orderDirection . ', a.title ASC');

        return $query;
    }
}
