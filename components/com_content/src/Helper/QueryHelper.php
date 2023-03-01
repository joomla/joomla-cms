<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   (C) 2007 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Site\Helper;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\DatabaseInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Content Component Query Helper
 *
 * @since  1.5
 */
class QueryHelper
{
    /**
     * Translate an order code to a field for category ordering.
     *
     * @param   string  $orderby  The ordering code.
     *
     * @return  string  The SQL field(s) to order by.
     *
     * @since   1.5
     */
    public static function orderbyPrimary($orderby)
    {
        switch ($orderby) {
            case 'alpha':
                $orderby = 'c.path, ';
                break;

            case 'ralpha':
                $orderby = 'c.path DESC, ';
                break;

            case 'order':
                $orderby = 'c.lft, ';
                break;

            default:
                $orderby = '';
                break;
        }

        return $orderby;
    }

    /**
     * Translate an order code to a field for article ordering.
     *
     * @param   string             $orderby    The ordering code.
     * @param   string             $orderDate  The ordering code for the date.
     * @param   DatabaseInterface  $db         The database
     *
     * @return  string  The SQL field(s) to order by.
     *
     * @since   1.5
     */
    public static function orderbySecondary($orderby, $orderDate = 'created', DatabaseInterface $db = null)
    {
        $db = $db ?: Factory::getDbo();

        $queryDate = self::getQueryDate($orderDate, $db);

        switch ($orderby) {
            case 'date':
                $orderby = $queryDate;
                break;

            case 'rdate':
                $orderby = $queryDate . ' DESC ';
                break;

            case 'alpha':
                $orderby = 'a.title';
                break;

            case 'ralpha':
                $orderby = 'a.title DESC';
                break;

            case 'hits':
                $orderby = 'a.hits DESC';
                break;

            case 'rhits':
                $orderby = 'a.hits';
                break;

            case 'rorder':
                $orderby = 'a.ordering DESC';
                break;

            case 'author':
                $orderby = 'author';
                break;

            case 'rauthor':
                $orderby = 'author DESC';
                break;

            case 'front':
                $orderby = 'a.featured DESC, fp.ordering, ' . $queryDate . ' DESC ';
                break;

            case 'random':
                $orderby = $db->getQuery(true)->rand();
                break;

            case 'vote':
                $orderby = 'a.id DESC ';

                if (PluginHelper::isEnabled('content', 'vote')) {
                    $orderby = 'rating_count DESC ';
                }
                break;

            case 'rvote':
                $orderby = 'a.id ASC ';

                if (PluginHelper::isEnabled('content', 'vote')) {
                    $orderby = 'rating_count ASC ';
                }
                break;

            case 'rank':
                $orderby = 'a.id DESC ';

                if (PluginHelper::isEnabled('content', 'vote')) {
                    $orderby = 'rating DESC ';
                }
                break;

            case 'rrank':
                $orderby = 'a.id ASC ';

                if (PluginHelper::isEnabled('content', 'vote')) {
                    $orderby = 'rating ASC ';
                }
                break;

            default:
                $orderby = 'a.ordering';
                break;
        }

        return $orderby;
    }

    /**
     * Translate an order code to a field for date ordering.
     *
     * @param   string             $orderDate  The ordering code.
     * @param   DatabaseInterface  $db         The database
     *
     * @return  string  The SQL field(s) to order by.
     *
     * @since   1.6
     */
    public static function getQueryDate($orderDate, DatabaseInterface $db = null)
    {
        $db = $db ?: Factory::getDbo();

        switch ($orderDate) {
            case 'modified':
                $queryDate = ' CASE WHEN a.modified IS NULL THEN a.created ELSE a.modified END';
                break;

            // Use created if publish_up is not set
            case 'published':
                $queryDate = ' CASE WHEN a.publish_up IS NULL THEN a.created ELSE a.publish_up END ';
                break;

            case 'unpublished':
                $queryDate = ' CASE WHEN a.publish_down IS NULL THEN a.created ELSE a.publish_down END ';
                break;
            case 'created':
            default:
                $queryDate = ' a.created ';
                break;
        }

        return $queryDate;
    }

    /**
     * Get join information for the voting query.
     *
     * @param   \Joomla\Registry\Registry  $params  An options object for the article.
     *
     * @return  array  A named array with "select" and "join" keys.
     *
     * @since   1.5
     *
     * @deprecated  5.0  Deprecated without replacement, not used in core
     */
    public static function buildVotingQuery($params = null)
    {
        if (!$params) {
            $params = ComponentHelper::getParams('com_content');
        }

        $voting = $params->get('show_vote');

        if ($voting) {
            // Calculate voting count
            $select = ' , ROUND(v.rating_sum / v.rating_count) AS rating, v.rating_count';
            $join = ' LEFT JOIN #__content_rating AS v ON a.id = v.content_id';
        } else {
            $select = '';
            $join = '';
        }

        return ['select' => $select, 'join' => $join];
    }
}
