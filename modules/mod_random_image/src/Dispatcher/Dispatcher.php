<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_random_image
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\RandomImage\Site\Dispatcher;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Dispatcher class for mod_random_image
 *
 * @since  5.4.0
 */
class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    /**
     * Returns the layout data.
     *
     * @return  array
     *
     * @since   5.4.0
     */
    protected function getLayoutData()
    {
        $data = parent::getLayoutData();

        $helper         = $this->getHelperFactory()->getHelper('RandomImageHelper');
        $data['link']   = $data['params']->get('link');
        $folder         = $helper->getSanitizedFolder($data['params']);
        $data['images'] = $helper->getImagesFromFolder($data['params'], $folder);
        $data['image']  = $helper->getImage($data['params'], $data['images']);

        return $data;
    }
}
