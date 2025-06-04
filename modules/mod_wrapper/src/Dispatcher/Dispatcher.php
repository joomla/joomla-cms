<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_wrapper
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Wrapper\Site\Dispatcher;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Dispatcher class for mod_wrapper
 *
 * @since  5.1.0
 */
class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    /**
     * Returns the layout data.
     *
     * @return  array
     *
     * @since   5.1.0
     */
    protected function getLayoutData()
    {
        $data = parent::getLayoutData();

        $params = $this->getHelperFactory()->getHelper('WrapperHelper')->getParamsWrapper($data['params'], $this->getApplication());

        $data['load']        = $params->get('load');
        $data['url']         = htmlspecialchars($params->get('url', ''), ENT_COMPAT, 'UTF-8');
        $data['target']      = htmlspecialchars($params->get('target', ''), ENT_COMPAT, 'UTF-8');
        $data['width']       = htmlspecialchars($params->get('width', ''), ENT_COMPAT, 'UTF-8');
        $data['height']      = htmlspecialchars($params->get('height', ''), ENT_COMPAT, 'UTF-8');
        $data['ititle']      = $this->module->title;
        $data['id']          = $this->module->id;
        $data['lazyloading'] = $params->get('lazyloading', 'lazy');

        return $data;
    }
}
