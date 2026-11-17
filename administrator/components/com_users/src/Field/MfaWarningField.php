<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Administrator\Field;

use Joomla\CMS\Form\Field\NoteField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Displays a warning when no MFA plugins are enabled.
 *
 * @since __DEPLOY_VERSION__
 */
class MfaWarningField extends NoteField
{
    /**
     * @var string
     */
    protected $type = 'MfaWarning';

    /**
     * @inheritDoc
     */
    protected function getLabel()
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    protected function getInput()
    {
        if (PluginHelper::isEnabled('multifactorauth')) {
            return '';
        }

        $url  = Route::_('index.php?option=com_plugins&view=plugins&filter[folder]=multifactorauth');
        $link = '<a href="' . $url . '">' . Text::_('COM_USERS_CONFIG_MULTIFACTORAUTH_PLUGINS') . '</a>';

        return '<div class="alert alert-danger" role="alert">'
            . Text::sprintf('COM_USERS_CONFIG_MULTIFACTORAUTH_NO_PLUGINS_WARNING', $link)
            . '</div>';
    }
}
