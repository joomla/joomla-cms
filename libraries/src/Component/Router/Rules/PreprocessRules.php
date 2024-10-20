<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Component\Router\Rules;

use Joomla\CMS\Component\Router\RouterViewConfiguration;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Rule to prepare the query and add missing information
 *
 * This rule adds the alias to an ID query parameter and the
 * category ID if either of them is missing. This requires that
 * the db table contains an alias column.
 * This fixes sloppy URLs in the code, but doesn't mean you can
 * simply drop the alias from the &id= in the future. Cleaning up
 * every request with this would mean a significant performance impact
 *
 * @since  __DEPLOY_VERSION__
 */
class PreprocessRules implements RulesInterface
{
    use DatabaseAwareTrait;

    /**
     * View to prepare
     *
     * @var   RouterViewConfiguration
     * @since __DEPLOY_VERSION__
     */
    protected $view;

    /**
     * DB Table to read the information from
     *
     * @var   string
     * @since __DEPLOY_VERSION__
     */
    protected $table;

    /**
     * ID column in the table to read the information from
     *
     * @var   string
     * @since __DEPLOY_VERSION__
     */
    protected $key;

    /**
     * Parent ID column in the table to read the information from
     *
     * @var   string
     * @since __DEPLOY_VERSION__
     */
    protected $parent_key;

    /**
     * Class constructor.
     *
     * @param   RouterViewConfiguration  $view        View to act on
     * @param   string                   $table       Table name for the views information
     * @param   string                   $key         Key in the table to get the information
     * @param   string                   $parent_key  Column name of the parent key
     *
     * @since   __DEPLOY_VERSION__
     */
    public function __construct(RouterViewConfiguration $view, $table, $key, $parent_key = null)
    {
        $this->view       = $view;
        $this->table      = $table;
        $this->key        = $key;
        $this->parent_key = $parent_key;
    }

    /**
     * Finds the correct Itemid for this query
     *
     * @param   array  &$query  The query array to process
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function preprocess(&$query)
    {
        // We only work for URLs with the view we have been setup for
        if (!isset($query['view']) || $query['view'] != $this->view->name) {
            return;
        }

        $key        = $this->view->key;
        $parent_key = $this->view->parent_key;

        // We have to have at least the ID or something to repair
        if (!isset($query[$key]) || (strpos($query[$key], ':') && isset($query[$parent_key]))) {
            return;
        }

        $dbquery = $this->getDatabase()->getQuery(true);

        $dbquery->select($dbquery->quoteName('alias'))
            ->from($this->table)
            ->where($dbquery->quoteName($this->key) . ' = :key')
            ->bind(':key', $query[$key], ParameterType::INTEGER);

        // Do we have a parent key?
        if ($parent_key && $this->parent_key) {
            $dbquery->select($dbquery->quoteName($this->parent_key));
        }

        $obj = $this->getDatabase()->setQuery($dbquery)->loadObject();

        // We haven't found the item in the database. Abort.
        if (!$obj) {
            return;
        }

        // Lets fix the slug (id:alias)
        if (!strpos($query[$key], ':')) {
            $query[$key] .= ':' . $obj->alias;
        }

        // If we have a parent key and it is missing, lets add it
        if ($parent_key && $this->parent_key && !isset($query[$parent_key])) {
            $query[$parent_key] = $obj->{$this->parent_key};
        }
    }

    /**
     * Dummy method to fulfil the interface requirements
     *
     * @param   array  &$segments  The URL segments to parse
     * @param   array  &$vars      The vars that result from the segments
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @codeCoverageIgnore
     */
    public function parse(&$segments, &$vars)
    {
    }

    /**
     * Dummy method to fulfil the interface requirements
     *
     * @param   array  &$query     The vars that should be converted
     * @param   array  &$segments  The URL segments to create
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @codeCoverageIgnore
     */
    public function build(&$query, &$segments)
    {
    }
}
