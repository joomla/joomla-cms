<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_banners
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Banners\Site\Dispatcher;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;
use Joomla\Component\Banners\Administrator\Helper\BannersHelper as BannersComponentHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Dispatcher class for mod_banners
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
    protected function getLayoutData(): array
    {
        BannersComponentHelper::updateReset();

        $data = parent::getLayoutData();

        $data['headerText'] = trim($data['params']->get('header_text', ''));
        $data['footerText'] = trim($data['params']->get('footer_text', ''));
        $data['list']       = $this->getHelperFactory()->getHelper('BannersHelper')->getBanners($data['params'], $this->getApplication());

        return $data;
    }
}
