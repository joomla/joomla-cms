<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_finder
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Finder\Site\Helper;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Finder\Administrator\Indexer\Query;
use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Finder module helper.
 *
 * @since  2.5
 */
class FinderHelper implements DatabaseAwareInterface
{
    use DatabaseAwareTrait;

    /**
     * Method to get hidden input fields for a get form so that control variables
     * are not lost upon form submission.
     *
     * @param   string   $route      The route to the page. [optional]
     *
     * @return  string  A string of hidden input form fields
     *
     * @since   5.4.0
     */
    public function getHiddenFields($route = null): string
    {
        $fields = [];
        $uri    = Uri::getInstance(Route::_($route));
        $uri->delVar('q');

        // Create hidden input elements for each part of the URI.
        foreach ($uri->getQuery(true) as $n => $v) {
            $fields[] = '<input type="hidden" name="' . $n . '" value="' . $v . '">';
        }

        return implode('', $fields);
    }

    /**
     * Get Smart Search query object.
     *
     * @param   Registry                 $params    Module parameters.
     * @param   CMSApplicationInterface  $app       The application
     *
     * @return  Query object
     *
     * @since   5.4.0
     */
    public function getSearchQuery(Registry $params, CMSApplicationInterface $app): Query
    {
        $request = $app->getInput()->request;
        $filter  = InputFilter::getInstance();

        // Get the static taxonomy filters.
        $options           = [];
        $options['filter'] = ($request->get('f', 0, 'int') !== 0) ? $request->get('f', '', 'int') : $params->get('searchfilter');
        $options['filter'] = $filter->clean($options['filter'], 'int');

        // Get the dynamic taxonomy filters.
        $options['filters'] = $request->get('t', '', 'array');
        $options['filters'] = $filter->clean($options['filters'], 'array');
        $options['filters'] = ArrayHelper::toInteger($options['filters']);

        // Instantiate a query object.
        return new Query($options, $this->getDatabase());
    }

    /**
     * Method to get hidden input fields for a get form so that control variables
     * are not lost upon form submission.
     *
     * @param   string   $route      The route to the page. [optional]
     * @param   integer  $paramItem  The menu item ID. (@since 3.1) [optional]
     *
     * @return  string  A string of hidden input form fields
     *
     * @since   2.5
     *
     * @deprecated 5.4.0 will be removed in 7.0
     *             Use the non-static method getFields
     *             Example: Factory::getApplication()->bootModule('mod_finder', 'site')
     *                          ->getHelper('FinderHelper')
     *                          ->getHiddenFields($route, $paramItem)
     */
    public static function getGetFields($route = null, $paramItem = 0)
    {
        return (new self())->getHiddenFields($route);
    }

    /**
     * Get Smart Search query object.
     *
     * @param   \Joomla\Registry\Registry  $params  Module parameters.
     *
     * @return  Query object
     *
     * @since   2.5
     *
     * @deprecated 5.4.0 will be removed in 7.0
     *             Use the non-static method getSearchQuery
     *             Example: Factory::getApplication()->bootModule('mod_finder', 'site')
     *                          ->getHelper('FinderHelper')
     *                          ->getSearchQuery($params, Factory::getApplication())
     */
    public static function getQuery($params)
    {
        $app = Factory::getApplication();

        return $app->bootModule('mod_finder', 'site')
                   ->getHelper('FinderHelper')
                   ->getSearchQuery($params, $app);
    }
}
