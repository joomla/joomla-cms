<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Media-Action.crop
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\MediaAction\Crop\Extension;

use Joomla\CMS\Application\CMSWebApplicationInterface;
use Joomla\Component\Media\Administrator\Plugin\MediaActionPlugin;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Media Manager Crop Action
 *
 * @since  4.0.0
 */
final class Crop extends MediaActionPlugin implements SubscriberInterface
{
    /**
     * Load the javascript files of the plugin.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    protected function loadJs()
    {
        parent::loadJs();

        if (!$this->getApplication() instanceof CMSWebApplicationInterface) {
            return;
        }

        $this->getApplication()->getDocument()->getWebAssetManager()->useScript('cropper-module');
    }

    /**
     * Load the CSS files of the plugin.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    protected function loadCss()
    {
        parent::loadCss();

        if (!$this->getApplication() instanceof CMSWebApplicationInterface) {
            return;
        }

        $this->getApplication()->getDocument()->getWebAssetManager()->useStyle('cropperjs');
    }
}
