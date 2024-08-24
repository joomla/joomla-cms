<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_guidedtours
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\GuidedTours\Administrator\Helper;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Language\Multilanguage;
use Joomla\Registry\Registry;
use Joomla\Uri\Uri;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Helper for mod_guidedtours
 *
 * @since  4.3.0
 */
class GuidedToursHelper
{
    /**
     * Get a list of tours from a specific context.
     *
     * @param   Registry                  $params  Object holding the module parameters
     * @param   AdministratorApplication  $app     The application
     *
     * @return  mixed
     *
     * @since   4.3.0
     */
    public function getTours(Registry $params, AdministratorApplication $app)
    {
        $factory = $app->bootComponent('com_guidedtours')->getMVCFactory();

        $user = $app->getIdentity();

        // Get an instance of the guided tour model
        $tourModel = $factory->createModel('Tours', 'Administrator', ['ignore_request' => true]);

        $tourModel->setState('filter.published', 1);
        $tourModel->setState('filter.access', $app->getIdentity()->getAuthorisedViewLevels());

        if (Multilanguage::isEnabled()) {
            $tourModel->setState('filter.language', ['*', $app->getLanguage()->getTag()]);
        }

        $items = $tourModel->getItems();

        foreach ($items as $key => $item) {
            // The user can only see the tours of extensions that are allowed.
            $uri = new Uri($item->url);

            if ($extension = $uri->getVar('option')) {
                if (!$user->authorise('core.manage', $extension)) {
                    unset($items[$key]);
                }
            }
        }

        return $items;
    }
}
