<?php

/**
 * @package       Joomla.Administrator
 * @subpackage    com_guidedtours
 *
 * @copyright     (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Guidedtours\Administrator\Controller;

use Joomla\CMS\MVC\Controller\FormController;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Controller for a single step
 *
 * @since 4.3.0
 */
class StepController extends FormController
{
    /**
     * Gets the URL arguments to append to a list redirect.
     *
     * @return  string  The arguments to append to the redirect URL.
     *
     * @since  5.2.2
     */
    protected function getRedirectToListAppend()
    {
        $append = parent::getRedirectToListAppend();
        $tourId = $this->app->getUserState('com_guidedtours.tour_id');
        if (!empty($tourId)) {
            $append .= '&tour_id=' . $tourId;
        }

        return $append;
    }
}
