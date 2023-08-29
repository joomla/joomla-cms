<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_finder
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Finder\Site\Helper;

use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Finder\Administrator\Indexer\Query;
use Joomla\Database\DatabaseInterface;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Finder module helper.
 *
 * @since  2.5
 */
class FinderHelper
{
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
     */
    public static function getGetFields($route = null, $paramItem = 0)
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
     * @param   \Joomla\Registry\Registry  $params  Module parameters.
     *
     * @return  Query object
     *
     * @since   2.5
     */
    public static function getQuery($params)
    {
        $request = Factory::getApplication()->getInput()->request;
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
        return new Query($options, Factory::getContainer()->get(DatabaseInterface::class));
    }
}
