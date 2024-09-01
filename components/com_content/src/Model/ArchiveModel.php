<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Site\Model;

use Joomla\CMS\Factory;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;
use Joomla\Component\Content\Site\Helper\QueryHelper;
use Joomla\Database\ParameterType;
use Joomla\Database\QueryInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Content Component Archive Model
 *
 * @since  1.5
 */
class ArchiveModel extends ArticlesModel
{
    /**
     * Model context string.
     *
     * @var     string
     */
    public $_context = 'com_content.archive';

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param   string  $ordering   The field to order on.
     * @param   string  $direction  The direction to order on.
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function populateState($ordering = null, $direction = null)
    {
        parent::populateState();

        $app   = Factory::getApplication();
        $input = $app->getInput();

        // Add archive properties
        $params = $this->state->get('params');

        // Filter on archived articles
        $this->setState('filter.published', ContentComponent::CONDITION_ARCHIVED);

        // Filter on month, year
        $this->setState('filter.month', $input->getInt('month'));
        $this->setState('filter.year', $input->getInt('year'));

        // Optional filter text
        $this->setState('list.filter', $input->getString('filter-search'));

        // Get list limit
        $itemid = $input->get('Itemid', 0, 'int');
        $limit  = $app->getUserStateFromRequest('com_content.archive.list' . $itemid . '.limit', 'limit', $params->get('display_num', 20), 'uint');
        $this->setState('list.limit', $limit);

        // Set the archive ordering
        $articleOrderby   = $params->get('orderby_sec', 'rdate');
        $articleOrderDate = $params->get('order_date');

        // No category ordering
        $secondary = QueryHelper::orderbySecondary($articleOrderby, $articleOrderDate, $this->getDatabase());

        $this->setState('list.ordering', $secondary . ', a.created DESC');
        $this->setState('list.direction', '');
    }

    /**
     * Get the main query for retrieving a list of articles subject to the model state.
     *
     * @return  QueryInterface
     *
     * @since   1.6
     */
    protected function getListQuery()
    {
        $params           = $this->state->params;
        $app              = Factory::getApplication();
        $catids           = $app->getInput()->get('catid', [], 'array');
        $catids           = array_values(array_diff($catids, ['']));

        $articleOrderDate = $params->get('order_date');

        // Create a new query object.
        $db    = $this->getDatabase();
        $query = parent::getListQuery();

        // Add routing for archive
        $query->select(
            [
                $this->getSlugColumn($query, 'a.id', 'a.alias') . ' AS ' . $db->quoteName('slug'),
                $this->getSlugColumn($query, 'c.id', 'c.alias') . ' AS ' . $db->quoteName('catslug'),
            ]
        );

        // Filter on month, year
        // First, get the date field
        $queryDate = QueryHelper::getQueryDate($articleOrderDate, $this->getDatabase());

        if ($month = (int) $this->getState('filter.month')) {
            $query->where($query->month($queryDate) . ' = :month')
                ->bind(':month', $month, ParameterType::INTEGER);
        }

        if ($year = (int) $this->getState('filter.year')) {
            $query->where($query->year($queryDate) . ' = :year')
                ->bind(':year', $year, ParameterType::INTEGER);
        }

        if (\count($catids) > 0) {
            $query->whereIn($db->quoteName('c.id'), $catids);
        }

        return $query;
    }

    /**
     * Method to get the archived article list
     *
     * @access public
     * @return array
     * @deprecated 5.2.0 will be removed in 7.0
     *             Use getItems() instead
     */
    public function getData()
    {
        @trigger_error('ArchiveModel::getData() is deprecated. Use getItems() instead. Will be removed in 7.0.', E_USER_DEPRECATED);

        return $this->getItems();
    }

    /**
     * Gets the archived articles years
     *
     * @return   array
     *
     * @since    3.6.0
     */
    public function getYears()
    {
        $db        = $this->getDatabase();
        $nowDate   = Factory::getDate()->toSql();
        $query     = $db->createQuery();
        $queryDate = QueryHelper::getQueryDate($this->state->params->get('order_date'), $db);
        $years     = $query->year($queryDate);

        $query->select('DISTINCT ' . $years)
            ->from($db->quoteName('#__content', 'a'))
            ->where($db->quoteName('a.state') . ' = ' . ContentComponent::CONDITION_ARCHIVED)
            ->extendWhere(
                'AND',
                [
                    $db->quoteName('a.publish_up') . ' IS NULL',
                    $db->quoteName('a.publish_up') . ' <= :publishUp',
                ],
                'OR'
            )
            ->extendWhere(
                'AND',
                [
                    $db->quoteName('a.publish_down') . ' IS NULL',
                    $db->quoteName('a.publish_down') . ' >= :publishDown',
                ],
                'OR'
            )
            ->bind(':publishUp', $nowDate)
            ->bind(':publishDown', $nowDate)
            ->order('1 ASC');

        $db->setQuery($query);

        return $db->loadColumn();
    }

    /**
     * Generate column expression for slug or catslug.
     *
     * @param   \Joomla\Database\DatabaseQuery  $query  Current query instance.
     * @param   string                          $id     Column id name.
     * @param   string                          $alias  Column alias name.
     *
     * @return  string
     *
     * @since   4.0.0
     */
    private function getSlugColumn($query, $id, $alias)
    {
        $db = $this->getDatabase();

        return 'CASE WHEN '
            . $query->charLength($db->quoteName($alias), '!=', '0')
            . ' THEN '
            . $query->concatenate([$query->castAsChar($db->quoteName($id)), $db->quoteName($alias)], ':')
            . ' ELSE '
            . $query->castAsChar($id) . ' END';
    }
}
