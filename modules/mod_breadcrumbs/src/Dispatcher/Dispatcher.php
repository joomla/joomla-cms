<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_breadcrumbs
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Breadcrumbs\Site\Dispatcher;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Dispatcher class for mod_breadcrumbs
 *
 * @since  4.4.0
 */
class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    /**
     * Returns the layout data.
     *
     * @return  array
     *
     * @since   4.4.0
     */
    protected function getLayoutData(): array
    {
        $data = parent::getLayoutData();

        $data['list']  = $this->getHelperFactory()->getHelper('BreadcrumbsHelper')->getBreadcrumbs($data['params'], $data['app']);
        $data['count'] = \count($data['list']);

        if (!$data['params']->get('showHome', 1)) {
            $data['homeCrumb'] = $this->getHelperFactory()->getHelper('BreadcrumbsHelper')->getHomeItem($data['params'], $data['app']);
        }

        return $data;
    }
}
