<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_finder
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Finder\Site\Service;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Component\Router\RouterView;
use Joomla\CMS\Component\Router\RouterViewConfiguration;
use Joomla\CMS\Component\Router\Rules\MenuRules;
use Joomla\CMS\Component\Router\Rules\NomenuRules;
use Joomla\CMS\Menu\AbstractMenu;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Routing class from com_finder
 *
 * @since  3.3
 */
class Router extends RouterView
{
    /**
     * Finder Component router constructor
     *
     * @param   SiteApplication  $app   The application object
     * @param   AbstractMenu     $menu  The menu object to work with
     */
    public function __construct(SiteApplication $app, AbstractMenu $menu)
    {
        $search = new RouterViewConfiguration('search');
        $this->registerView($search);

        parent::__construct($app, $menu);

        $this->attachRule(new MenuRules($this));
        $this->attachRule(new NomenuRules($this));
    }

    /**
     * Build method for URLs
     *
     * @param   array  &$query  Array of query elements
     *
     * @return  array  Array of URL segments
     *
     * @since   __DEPLOY_VERSION__
     */
    public function build(&$query)
    {
        $segments = [];

        // Process the parsed variables based on custom defined rules
        foreach ($this->rules as $rule) {
            $rule->build($query, $segments);
        }

        if (isset($query['Itemid'])) {
            $item = $this->menu->getItem($query['Itemid']);

            if ($query['option'] == 'com_finder' && isset($query['f']) && $query['f'] == $item->query['f']) {
                unset($query['f']);
            }
        }

        unset($query['view']);

        return $segments;
    }
}
