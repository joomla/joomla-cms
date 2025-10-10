<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
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
 * @since  2.5
 */
class Manage
{
    /**
     * Returns a published state on a grid.
     *
     * @param   integer  $value     The state value.
     * @param   integer  $i         The row index.
     * @param   boolean  $enabled   An optional setting for access control on the action.
     * @param   string   $checkbox  An optional prefix for checkboxes.
     *
     * @return  string        The Html code
     *
     * @see JHtmlJGrid::state
     *
     * @since   2.5
     */
    public function state($value, $i, $enabled = true, $checkbox = 'cb')
    {
        $states = [
            2 => [
                '',
                'COM_INSTALLER_EXTENSION_PROTECTED',
                '',
                'COM_INSTALLER_EXTENSION_PROTECTED',
                true,
                'protected',
                'protected',
            ],
            1 => [
                'unpublish',
                'COM_INSTALLER_EXTENSION_ENABLED',
                'COM_INSTALLER_EXTENSION_DISABLE',
                'COM_INSTALLER_EXTENSION_ENABLED',
                true,
                'publish',
                'publish',
            ],
            0 => [
                'publish',
                'COM_INSTALLER_EXTENSION_DISABLED',
                'COM_INSTALLER_EXTENSION_ENABLE',
                'COM_INSTALLER_EXTENSION_DISABLED',
                true,
                'unpublish',
                'unpublish',
            ],
        ];

        return HTMLHelper::_('jgrid.state', $states, $value, $i, 'manage.', $enabled, true, $checkbox);
    }
}
