<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Site\Service;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Component\Router\RouterView;
use Joomla\CMS\Component\Router\RouterViewConfiguration;
use Joomla\CMS\Component\Router\Rules\MenuRules;
use Joomla\CMS\Component\Router\Rules\NomenuRules;
use Joomla\CMS\Component\Router\Rules\StandardRules;
use Joomla\CMS\Menu\AbstractMenu;

/**
 * Routing class from com_users
 *
 * @since  3.2
 */
class Router extends RouterView
{
    /**
     * Users Component router constructor
     *
     * @param   SiteApplication  $app   The application object
     * @param   AbstractMenu     $menu  The menu object to work with
     */
    public function __construct(SiteApplication $app, AbstractMenu $menu)
    {
        $this->registerView(new RouterViewConfiguration('login'));
        $profile = new RouterViewConfiguration('profile');
        $profile->addLayout('edit');
        $this->registerView($profile);
        $this->registerView(new RouterViewConfiguration('registration'));
        $this->registerView(new RouterViewConfiguration('remind'));
        $this->registerView(new RouterViewConfiguration('reset'));
        $this->registerView(new RouterViewConfiguration('callback'));
        $this->registerView(new RouterViewConfiguration('captive'));
        $this->registerView(new RouterViewConfiguration('methods'));

        $method = new RouterViewConfiguration('method');
        $method->setKey('id');
        $this->registerView($method);

        parent::__construct($app, $menu);

        $this->attachRule(new MenuRules($this));
        $this->attachRule(new StandardRules($this));
        $this->attachRule(new NomenuRules($this));
    }

    /**
     * Get the method ID from a URL segment
     *
     * @param   string  $segment  The URL segment
     * @param   array   $query    The URL query parameters
     *
     * @return integer
     * @since 4.2.0
     */
    public function getMethodId($segment, $query)
    {
        return (int) $segment;
    }

    /**
     * Get a segment from a method ID
     *
     * @param   integer  $id     The method ID
     * @param   array    $query  The URL query parameters
     *
     * @return int[]
     * @since 4.2.0
     */
    public function getMethodSegment($id, $query)
    {
        return [$id => (int) $id];
    }
}
