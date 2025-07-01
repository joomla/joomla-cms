<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Extension;
use Joomla\Component\Joomlaupdate\Administrator\Enum\AutoupdateRegisterState;
use Joomla\Component\Joomlaupdate\Administrator\Enum\AutoupdateState;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Post-installation message about the new Automated Update: condition check.
 *
 * Returns true it is disabled.
 *
 * @return  bool
 * @since   5.4.0
 */
function com_joomlaupdate_postinstall_autoupdate_condition(): bool
{
    return AutoupdateState::tryFrom(ComponentHelper::getParams('com_joomlaupdate')->get('autoupdate', '0')) === AutoupdateState::Disabled;
}

/**
 * Post-installation message about the new Automated Update: action.
 *
 * Enables the Automated Update.
 *
 * @return  void
 * @since   5.4.0
 */
function com_joomlaupdate_postinstall_autoupdate_action(): void
{
    $db = Factory::getContainer()->get(DatabaseInterface::class);

    // Get extension row
    $extension   = new Extension($db);
    $extensionId = $extension->find(['element' => 'com_joomlaupdate']);
    $extension->load($extensionId);

    // Set new update registration state
    $params = new Registry($extension->params);
    $params->set('autoupdate', AutoupdateState::Enabled);
    $params->set('autoupdate_status', AutoupdateRegisterState::Subscribe);

    $extension->params = $params->toString();

    if (!$extension->store()) {
        throw new \RuntimeException($extension->getError());
    }
}
