<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScriptInterface;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla Update Component installer script.
 *
 * @since       __DEPLOY_VERSION__
 *
 * @deprecated  5.1.2 will be removed in 6.0
 */
return new class () implements ServiceProviderInterface {
    public function register(Container $container)
    {
        $container->set(
            InstallerScriptInterface::class,
            new class (
                $container->get(AdministratorApplication::class),
                $container->get(DatabaseInterface::class)
            ) implements InstallerScriptInterface {
                private AdministratorApplication $app;
                private DatabaseInterface $db;

                public function __construct(AdministratorApplication $app, DatabaseInterface $db)
                {
                    $this->app = $app;
                    $this->db  = $db;
                }

                public function install(InstallerAdapter $parent): bool
                {
                    return true;
                }

                public function update(InstallerAdapter $parent): bool
                {
                    $parent->loadLanguage(JPATH_ADMINISTRATOR . '/languages');

                    // Reset update source from "next" to "default"
                    try {
                        $dbChanged = $this->resetUpdateSource();
                    } catch (\Throwable $e) {
                        $this->app->enqueueMessage(Text::_('COM_JOOMLAUPDATE_SCRIPT_CHANGE_UPDATE_SOURCE_FAILED'), 'warning');

                        $dbChanged = false;
                    }

                    // Show message if update source changed
                    if ($dbChanged) {
                        $this->app->enqueueMessage(Text::_('COM_JOOMLAUPDATE_SCRIPT_CHANGE_UPDATE_SOURCE_OK'), 'notice');
                    }

                    return true;
                }

                public function uninstall(InstallerAdapter $parent): bool
                {
                    return true;
                }

                public function preflight(string $type, InstallerAdapter $parent): bool
                {
                    return true;
                }

                public function postflight(string $type, InstallerAdapter $parent): bool
                {
                    return true;
                }

                /**
                 * Reset update source from "next" to "default"
                 *
                 * @return  boolean  true if update source is reset, false if not
                 *
                 * @since   __DEPLOY_VERSION__
                 * @throws  \RuntimeException
                 */
                private function resetUpdateSource()
                {
                    // Get current update source
                    $params = ComponentHelper::getParams('com_joomlaupdate');

                    // Do nothing if not "next"
                    if ($params->get('updatesource', 'default') !== 'next') {
                        return false;
                    }

                    $params->set('updatesource', 'default');

                    $params = $params->toString();
                    $query  = $this->db->getQuery(true)
                        ->update($this->db->quoteName('#__extensions'))
                        ->set($this->db->quoteName('params') . ' = :params')
                        ->where($this->db->quoteName('type') . ' = ' . $this->db->quote('component'))
                        ->where($this->db->quoteName('element') . ' = ' . $this->db->quote('com_joomlaupdate'))
                        ->bind(':params', $params);

                    $this->db->setQuery($query);
                    $this->db->execute();

                    return true;
                }
            }
        );
    }
};
