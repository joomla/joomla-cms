<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Media-Action.crop
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt

 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Factory;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Media Manager Crop Action
 *
 * @since  4.0.0
 */
class PlgMediaActionCrop extends \Joomla\Component\Media\Administrator\Plugin\MediaActionPlugin
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

        Factory::getApplication()->getDocument()->getWebAssetManager()->useScript('cropperjs');
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

        Factory::getApplication()->getDocument()->getWebAssetManager()->useStyle('cropperjs');
    }
}
