<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_guidedtours
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\GuidedTours\Administrator\Helper;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Helper for mod_login
 *
 * @since  __DEPLOY_VERSION__
 */
abstract class GuidedToursHelper
{
    /**
     * Get a list of tours from a specific context
     *
     * @param   \Joomla\Registry\Registry  &$params  object holding the module parameters
     *
     * @return  mixed
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function getList(&$params)
    {
        $app = Factory::getApplication();

        $factory = $app->bootComponent('com_guidedtours')->getMVCFactory();

        // Get an instance of the guided tour model
        $tours = $factory->createModel('Tours', 'Administrator', ['ignore_request' => true]);

        $tours->setState('filter.published', 1);

        $items = $tours->getItems();

        return $items;
    }
}
