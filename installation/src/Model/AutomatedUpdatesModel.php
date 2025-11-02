<?php

/**
 * @package     Joomla.Installation
 * @subpackage  Model
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installation\Model;

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Automated Updates model for the Joomla Core Installer.
 *
 * @since  5.4.0
 */
class AutomatedUpdatesModel extends BaseInstallationModel implements DatabaseAwareInterface
{
    use DatabaseAwareTrait;

    /**
     * @since  5.4.0
     */
    public function __construct()
    {
        // Overrides application config and set the configuration.php file so tokens and database works.
        if (file_exists(JPATH_BASE . '/configuration.php')) {
            /** @phpstan-ignore class.notFound */
            Factory::getApplication()->setConfiguration(new Registry(new \JConfig()));
        }

        parent::__construct();
    }

    /**
     * Opt out from automated Updates
     *
     * @since  5.4.0
     */
    public function disable()
    {
        // Get the params of com_joomlaupdate
        $db        = $this->getDatabase();
        $query     = $db->getQuery(true);

        $query->select('params')
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('element') . ' = ' . $db->quote('com_joomlaupdate'));

        $params = new Registry($db->setQuery($query)->loadResult());
        $params->set('autoupdate', 0);
        $params->set('autoupdate_status', 0);

        // Update the language settings in the language manager.
        $query->clear()
            ->update($db->quoteName('#__extensions'))
            ->set($db->quoteName('params') . ' = ' . $db->quote($params->toString()))
            ->where($db->quoteName('element') . ' = ' . $db->quote('com_joomlaupdate'));
        $db->setQuery($query)->execute();

        return true;
    }
}
