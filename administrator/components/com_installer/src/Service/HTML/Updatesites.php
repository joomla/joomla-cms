<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Installer\Administrator\Service\HTML;

use Joomla\CMS\HTML\HTMLHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Installer HTML class.
 *
 * @since  3.5
 */
class Updatesites
{
    /**
     * Returns a published state on a grid.
     *
     * @param   integer  $value     The state value.
     * @param   integer  $i         The row index.
     * @param   boolean  $enabled   An optional setting for access control on the action.
     * @param   string   $checkbox  An optional prefix for checkboxes.
     *
     * @return  string   The HTML code
     *
     * @see     JHtmlJGrid::state()
     * @since   3.5
     */
    public function state($value, $i, $enabled = true, $checkbox = 'cb')
    {
        $states = [
            1 => [
                'unpublish',
                'COM_INSTALLER_UPDATESITE_ENABLED',
                'COM_INSTALLER_UPDATESITE_DISABLE',
                'COM_INSTALLER_UPDATESITE_ENABLED',
                true,
                'publish',
                'publish',
            ],
            0 => [
                'publish',
                'COM_INSTALLER_UPDATESITE_DISABLED',
                'COM_INSTALLER_UPDATESITE_ENABLE',
                'COM_INSTALLER_UPDATESITE_DISABLED',
                true,
                'unpublish',
                'unpublish',
            ],
        ];

        return HTMLHelper::_('jgrid.state', $states, $value, $i, 'updatesites.', $enabled, true, $checkbox);
    }
}
