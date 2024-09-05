<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_finder
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Finder\Site\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Finder\Administrator\Indexer\Query;
use Joomla\String\StringHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Search model class for the Finder package.
 *
 * @since  2.5
 */
class SearchModel extends ListModel
{
    /**
     * Context string for the model type
     *
     * @var    string
     * @since  2.5
     */
    protected $context = 'com_finder.search';

    /**
     * The query object is an instance of Query which contains and
     * models the entire search query including the text input; static and
     * dynamic taxonomy filters; date filters; etc.
     *
     * @var    Query
     * @since  2.5
     */
    protected $searchquery;

    /**
     * Maps each sorting field with a text label.
     *
     * @var string[]
     *
     * @since 4.3.0
     */
    protected $sortOrderFieldsLabels = [
        'relevance.asc'   => 'COM_FINDER_SORT_BY_RELEVANCE_ASC',
        'relevance.desc'  => 'COM_FINDER_SORT_BY_RELEVANCE_DESC',
        'title.asc'       => 'JGLOBAL_TITLE_ASC',
        'title.desc'      => 'JGLOBAL_TITLE_DESC',
        'date.asc'        => 'JDATE_ASC',
        'date.desc'       => 'JDATE_DESC',
        'price.asc'       => 'COM_FINDER_SORT_BY_PRICE_ASC',
        'price.desc'      => 'COM_FINDER_SORT_BY_PRICE_DESC',
        'sale_price.asc'  => 'COM_FINDER_SORT_BY_SALES_PRICE_ASC',
        'sale_price.desc' => 'COM_FINDER_SORT_BY_SALES_PRICE_DESC',
    ];

    /**
     * An array of all excluded terms ids.
     *
     * @var    array
     * @since  2.5
     */
    protected $excludedTerms = [];

    /**
     * An array of all included terms ids.
     *
     * @var    array
     * @since  2.5
     */
    protected $includedTerms = [];

    /**
     * An array of all required terms ids.
     *
     * @var    array
     * @since  2.5
     */
    protected $requiredTerms = [];

    /**
     * Method to get the results of the query.
     *
     * @return  array  An array of Result objects.
     *
     * @since   2.5
     * @throws  \Exception on database error.
     */
    public function getItems()
    {
        $items = parent::getItems();

        // Check the data.
        if (empty($items)) {
            return null;
        }

        $results = [];

        // Convert the rows to result objects.
        foreach ($items as $rk => $row) {
            // Build the result object.
            if (is_resource($row->object)) {
                $result = unserialize(stream_get_contents($row->object));
            } else {
                $result = unserialize($row->object);
            }

            $result->cleanURL = $result->route;

            // Add the result back to the stack.
            $results[] = $result;
        }

        // Return the results.
        return $results;
    }

    /**
     * Method to get the query object.
     *
     * @return  Query  A query object.
     *
     * @since   2.5
     */
    public function getQuery()
    {
        // Return the query object.
        return $this->searchquery;
    }

    /**
     * Method to build a database query to load the list data.
     *
     * @return  \Joomla\Database\DatabaseQuery  A database query.
     *
     * @since   2.5
     */
    protected function getListQuery()
    {
        // Create a new query object.
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'l.link_id, l.object'
            )
        );

        $query->from('#__finder_links AS l');

        $user   = $this->getCurrentUser();
        $groups = $this->getState('user.groups', $user->getAuthorisedViewLevels());
        $query->whereIn($db->quoteName('l.access'), $groups)
            ->where('l.state = 1')
            ->where('l.published = 1');

        // Get the current date, minus seconds.
        $nowDate = $db->quote(substr_replace(Factory::getDate()->toSql(), '00', -2));

        // Add the publish up and publish down filters.
        $query->where('(l.publish_start_date IS NULL OR l.publish_start_date <= ' . $nowDate . ')')
            ->where('(l.publish_end_date IS NULL OR l.publish_end_date >= ' . $nowDate . ')');

        $query->group('l.link_id');
        $query->group('l.object');

        /*
         * Add the taxonomy filters to the query. We have to join the taxonomy
         * map table for each group so that we can use AND clauses across
         * groups. Within each group there can be an array of values that will
         * use OR clauses.
         */
        if (!empty($this->searchquery->filters)) {
            // Convert the associative array to a numerically indexed array.
            $groups     = array_values($this->searchquery->filters);
            $taxonomies = call_user_func_array('array_merge', array_values($this->searchquery->filters));

            $query->join('INNER', $db->quoteName('#__finder_taxonomy_map') . ' AS t ON t.link_id = l.link_id')
                ->where('t.node_id IN (' . implode(',', array_unique($taxonomies)) . ')');

            // Iterate through each taxonomy group.
            for ($i = 0, $c = count($groups); $i < $c; $i++) {
                $query->having('SUM(CASE WHEN t.node_id IN (' . implode(',', $groups[$i]) . ') THEN 1 ELSE 0 END) > 0');
            }
        }

        // Add the start date filter to the query.
        if (!empty($this->searchquery->date1)) {
            // Escape the date.
            $date1 = $db->quote($this->searchquery->date1);

            // Add the appropriate WHERE condition.
            if ($this->searchquery->when1 === 'before') {
                $query->where($db->quoteName('l.start_date') . ' <= ' . $date1);
            } elseif ($this->searchquery->when1 === 'after') {
                $query->where($db->quoteName('l.start_date') . ' >= ' . $date1);
            } else {
                $query->where($db->quoteName('l.start_date') . ' = ' . $date1);
            }
        }

        // Add the end date filter to the query.
        if (!empty($this->searchquery->date2)) {
            // Escape the date.
            $date2 = $db->quote($this->searchquery->date2);

            // Add the appropriate WHERE condition.
            if ($this->searchquery->when2 === 'before') {
                $query->where($db->quoteName('l.start_date') . ' <= ' . $date2);
            } elseif ($this->searchquery->when2 === 'after') {
                $query->where($db->quoteName('l.start_date') . ' >= ' . $date2);
            } else {
                $query->where($db->quoteName('l.start_date') . ' = ' . $date2);
            }
        }

        // Filter by language
        if ($this->getState('filter.language')) {
            $query->where('l.language IN (' . $db->quote(Factory::getLanguage()->getTag()) . ', ' . $db->quote('*') . ')');
        }

        // Get the result ordering and direction.
        $ordering  = $this->getState('list.ordering', 'm.weight');
        $direction = $this->getState('list.direction', 'DESC');

        /*
         * If we are ordering by relevance we have to add up the relevance
         * scores that are contained in the ordering field.
         */
        if ($ordering === 'm.weight') {
            // Get the base query and add the ordering information.
            $query->select('SUM(' . $db->escape($ordering) . ') AS ordering');
        } else {
            /**
             * If we are not ordering by relevance, we just have to add
             * the unique items to the set.
             */
            // Get the base query and add the ordering information.
            $query->select($db->escape($ordering) . ' AS ordering');
        }

        $query->order('ordering ' . $db->escape($direction));

        /*
         * Prevent invalid records from being returned in the final query.
         * This can happen if the search results are queried while the indexer is running.
         */
        $query->where($db->quoteName('object') . ' != ' . $db->quote(''));

        /*
         * If there are no optional or required search terms in the query, we
         * can get the results in one relatively simple database query.
         */
        if (empty($this->includedTerms) && $this->searchquery->empty && $this->searchquery->input == '') {
            // Return the results.
            return $query;
        }

        /*
         * If there are no optional or required search terms in the query and
         * empty searches are not allowed, we return an empty query.
         * If the search term is not empty and empty searches are allowed,
         * but no terms were found, we return an empty query as well.
         */
        if (
            empty($this->includedTerms)
            && (!$this->searchquery->empty || ($this->searchquery->empty && $this->searchquery->input != ''))
        ) {
            // Since we need to return a query, we simplify this one.
            $query->clear('join')
                ->clear('where')
                ->clear('bounded')
                ->clear('having')
                ->clear('group')
                ->where('false');

            return $query;
        }

        $included = call_user_func_array('array_merge', array_values($this->includedTerms));
        $query->join('INNER', $db->quoteName('#__finder_links_terms') . ' AS m ON m.link_id = l.link_id')
            ->where('m.term_id IN (' . implode(',', $included) . ')');

        // Check if there are any excluded terms to deal with.
        if (count($this->excludedTerms)) {
            $query2 = $db->getQuery(true);
            $query2->select('e.link_id')
                ->from($db->quoteName('#__finder_links_terms', 'e'))
                ->where('e.term_id IN (' . implode(',', $this->excludedTerms) . ')');
            $query->where('l.link_id NOT IN (' . $query2 . ')');
        }

        /*
         * The query contains required search terms.
         */
        if (count($this->requiredTerms)) {
            foreach ($this->requiredTerms as $terms) {
                if (count($terms)) {
                    $query->having('SUM(CASE WHEN m.term_id IN (' . implode(',', $terms) . ') THEN 1 ELSE 0 END) > 0');
                } else {
                    $query->where('false');
                    break;
                }
            }
        }

        return $query;
    }

    /**
     * Method to get the available sorting fields.
     *
     * @return  array   The sorting field objects.
     *
     * @throws  \Exception
     *
     * @since   4.3.0
     */
    public function getSortOrderFields()
    {
        $sortOrderFields      = [];
        $directions           = ['asc', 'desc'];
        $app                  = Factory::getApplication();
        $params               = $app->getParams();
        $sortOrderFieldValues = $params->get('shown_sort_order', [], 'array');

        if ($params->get('show_sort_order', 0, 'uint') && !empty($sortOrderFieldValues)) {
            $defaultSortFieldValue = $params->get('sort_order', '', 'cmd');
            $queryUri              = Uri::getInstance($this->getQuery()->toUri());

            // If the default field is not included in the shown sort fields, add it.
            if (!in_array($defaultSortFieldValue, $sortOrderFieldValues)) {
                array_unshift($sortOrderFieldValues, $defaultSortFieldValue);
            }

            foreach ($sortOrderFieldValues as $sortOrderFieldValue) {
                foreach ($directions as $direction) {
                    // The relevance has only descending direction. Except if ascending is set in the parameters.
                    if ($sortOrderFieldValue === 'relevance' && $direction === 'asc' && $app->getParams()->get('sort_direction', 'desc') === 'desc') {
                        continue;
                    }

                    $sortOrderFields[] = $this->getSortField($sortOrderFieldValue, $direction, $queryUri);
                }
            }
        }

        // Import Finder plugins
        PluginHelper::importPlugin('finder');

        // Trigger an event, in case a plugin wishes to change the order fields.
        $app->triggerEvent('onFinderSortOrderFields', [&$sortOrderFields]);

        return $sortOrderFields;
    }

    /**
     * Method to generate and return a sorting field
     *
     * @param   string  $value      The value based on which the results will be sorted.
     * @param   string  $direction  The sorting direction ('asc' or 'desc').
     * @param   Uri     $queryUri   The uri of the search query.
     *
     * @return  \stdClass   The sorting field object.
     *
     * @throws  \Exception
     *
     * @since   4.3.0
     */
    protected function getSortField(string $value, string $direction, Uri $queryUri)
    {
        $sortField = new \stdClass();
        $app       = Factory::getApplication();

        // We have to clone the query uri. Otherwise the next elements will use the same.
        $queryUri = clone $queryUri;
        $queryUri->setVar('o', $value);
        $currentOrderingDirection = $app->getInput()->getWord('od', $app->getParams()->get('sort_direction', 'desc'));

        // Validate the sorting direction and add it only if it is different than the set in the params.
        if (in_array($direction, ['asc', 'desc']) && $direction != $app->getParams()->get('sort_direction', 'desc')) {
            $queryUri->setVar('od', StringHelper::strtolower($direction));
        }

        $label = '';

        if (isset($this->sortOrderFieldsLabels[$value . '.' . $direction])) {
            $label = Text::_($this->sortOrderFieldsLabels[$value . '.' . $direction]);
        }

        $sortField->label      = $label;
        $sortField->url        = $queryUri->toString();
        $currentSortOrderField = $app->getInput()->getWord('o', $app->getParams()->get('sort_order', 'relevance'));
        $sortField->active     = false;

        if ($value === StringHelper::strtolower($currentSortOrderField) && $direction === $currentOrderingDirection) {
            $sortField->active = true;
        }

        return $sortField;
    }

    /**
     * Method to get a store id based on model the configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param   string   $id    An identifier string to generate the store id. [optional]
     * @param   boolean  $page  True to store the data paged, false to store all data. [optional]
     *
     * @return  string  A store id.
     *
     * @since   2.5
     */
    protected function getStoreId($id = '', $page = true)
    {
        // Get the query object.
        $query = $this->getQuery();

        // Add the search query state.
        $id .= ':' . $query->input;
        $id .= ':' . $query->language;
        $id .= ':' . $query->filter;
        $id .= ':' . serialize($query->filters);
        $id .= ':' . $query->date1;
        $id .= ':' . $query->date2;
        $id .= ':' . $query->when1;
        $id .= ':' . $query->when2;

        if ($page) {
            // Add the list state for page specific data.
            $id .= ':' . $this->getState('list.start');
            $id .= ':' . $this->getState('list.limit');
            $id .= ':' . $this->getState('list.ordering');
            $id .= ':' . $this->getState('list.direction');
        }

        return parent::getStoreId($id);
    }

    /**
     * Method to auto-populate the model state.  Calling getState in this method will result in recursion.
     *
     * @param   string  $ordering   An optional ordering field. [optional]
     * @param   string  $direction  An optional direction. [optional]
     *
     * @return  void
     *
     * @since   2.5
     */
    protected function populateState($ordering = null, $direction = null)
    {
        // Get the configuration options.
        $app      = Factory::getApplication();
        $input    = $app->getInput();
        $params   = $app->getParams();
        $user     = $this->getCurrentUser();
        $language = $app->getLanguage();

        $this->setState('filter.language', Multilanguage::isEnabled());

        $request = $input->request;
        $options = [];

        // Get the empty query setting.
        $options['empty'] = $params->get('allow_empty_query', 0);

        // Get the static taxonomy filters.
        $options['filter'] = $request->getInt('f', $params->get('f', ''));

        // Get the dynamic taxonomy filters.
        $options['filters'] = $request->get('t', $params->get('t', []), 'array');

        // Get the query string.
        $options['input'] = $request->getString('q', $params->get('q', ''));

        // Get the query language.
        $options['language'] = $request->getCmd('l', $params->get('l', $language->getTag()));

        // Set the word match mode
        $options['word_match'] = $params->get('word_match', 'exact');

        // Get the start date and start date modifier filters.
        $options['date1'] = $request->getString('d1', $params->get('d1', ''));
        $options['when1'] = $request->getString('w1', $params->get('w1', ''));

        // Get the end date and end date modifier filters.
        $options['date2'] = $request->getString('d2', $params->get('d2', ''));
        $options['when2'] = $request->getString('w2', $params->get('w2', ''));

        // Load the query object.
        $this->searchquery = new Query($options, $this->getDatabase());

        // Load the query token data.
        $this->excludedTerms = $this->searchquery->getExcludedTermIds();
        $this->includedTerms = $this->searchquery->getIncludedTermIds();
        $this->requiredTerms = $this->searchquery->getRequiredTermIds();

        // Load the list state.
        $this->setState('list.start', $input->get('limitstart', 0, 'uint'));
        $this->setState('list.limit', $input->get('limit', $params->get('list_limit', $app->get('list_limit', 20)), 'uint'));

        /*
         * Load the sort ordering.
         * Currently this is 'hard' coded via menu item parameter but may not satisfy a users need.
         * More flexibility was way more user friendly. So we allow the user to pass a custom value
         * from the pool of fields that are indexed like the 'title' field.
         * Also, we allow this parameter to be passed in either case (lower/upper).
         */
        $order = $input->getWord('o', $params->get('sort_order', 'relevance'));
        $order = StringHelper::strtolower($order);
        $this->setState('list.raworder', $order);

        switch ($order) {
            case 'date':
                $this->setState('list.ordering', 'l.start_date');
                break;

            case 'price':
                $this->setState('list.ordering', 'l.list_price');
                break;

            case 'sale_price':
                $this->setState('list.ordering', 'l.sale_price');
                break;

            case ($order === 'relevance' && !empty($this->includedTerms)):
                $this->setState('list.ordering', 'm.weight');
                break;

            case 'title':
                $this->setState('list.ordering', 'l.title');
                break;

            default:
                $this->setState('list.ordering', 'l.link_id');
                $this->setState('list.raworder');
                break;
        }

        /*
         * Load the sort direction.
         * Currently this is 'hard' coded via menu item parameter but may not satisfy a users need.
         * More flexibility was way more user friendly. So we allow to be inverted.
         * Also, we allow this parameter to be passed in either case (lower/upper).
         */
        $dirn = $input->getWord('od', $params->get('sort_direction', 'desc'));
        $dirn = StringHelper::strtolower($dirn);

        switch ($dirn) {
            case 'asc':
                $this->setState('list.direction', 'ASC');
                break;

            default:
                $this->setState('list.direction', 'DESC');
                break;
        }

        // Set the match limit.
        $this->setState('match.limit', 1000);

        // Load the parameters.
        $this->setState('params', $params);

        // Load the user state.
        $this->setState('user.id', (int) $user->get('id'));
        $this->setState('user.groups', $user->getAuthorisedViewLevels());
    }
}
