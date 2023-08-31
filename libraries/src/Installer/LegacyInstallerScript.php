<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installer;

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\Exception\DatabaseNotFoundException;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Legacy installer script which delegates the methods to the internal instance when possible.
 *
 * @since  4.2.0
 */
class LegacyInstallerScript implements InstallerScriptInterface, DatabaseAwareInterface
{
    use DatabaseAwareTrait;

    /**
     * @var    \stdClass
     * @since  4.2.0
     */
    private $installerScript;

    /**
     * @param   \stdClass  $installerScript  The script instance
     */
    public function __construct($installerScript)
    {
        $this->installerScript = $installerScript;
    }

    /**
     * Function called after the extension is installed.
     *
     * @param   InstallerAdapter  $adapter  The adapter calling this method
     *
     * @return  boolean  True on success
     *
     * @since   4.2.0
     */
    public function install(InstallerAdapter $adapter): bool
    {
        return $this->callOnScript('install', [$adapter]);
    }

    /**
     * Function called after the extension is updated.
     *
     * @param   InstallerAdapter  $adapter  The adapter calling this method
     *
     * @return  boolean  True on success
     *
     * @since   4.2.0
     */
    public function update(InstallerAdapter $adapter): bool
    {
        return $this->callOnScript('update', [$adapter]);
    }

    /**
     * Function called after the extension is uninstalled.
     *
     * @param   InstallerAdapter  $adapter  The adapter calling this method
     *
     * @return  boolean  True on success
     *
     * @since   4.2.0
     */
    public function uninstall(InstallerAdapter $adapter): bool
    {
        return $this->callOnScript('uninstall', [$adapter]);
    }

    /**
     * Function called before extension installation/update/removal procedure commences.
     *
     * @param   string            $type     The type of change (install or discover_install, update, uninstall)
     * @param   InstallerAdapter  $adapter  The adapter calling this method
     *
     * @return  boolean  True on success
     *
     * @since   4.2.0
     */
    public function preflight(string $type, InstallerAdapter $adapter): bool
    {
        return $this->callOnScript('preflight', [$type, $adapter]);
    }

    /**
     * Function called after extension installation/update/removal procedure commences.
     *
     * @param   string            $type     The type of change (install or discover_install, update, uninstall)
     * @param   InstallerAdapter  $adapter  The adapter calling this method
     *
     * @return  boolean  True on success
     *
     * @since   4.2.0
     */
    public function postflight(string $type, InstallerAdapter $adapter): bool
    {
        return $this->callOnScript('postflight', [$type, $adapter]);
    }

    /**
     * Sets the variable to the internal script.
     *
     * @param   string $name   The name of the variable
     * @param   mixed  $value  The value of the variable
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function __set(string $name, $value)
    {
        $this->installerScript->$name = $value;
    }

    /**
     * Returns the variable from the internal script.
     *
     * @param   string $name  The name of the variable
     *
     * @return  mixed
     *
     * @since   4.2.0
     */
    public function __get(string $name)
    {
        return $this->installerScript->$name;
    }

    /**
     * Calls the function with the given name on the internal script with
     * the given name and arguments.
     *
     * @param   string $name       The name of the function
     * @param   array  $arguments  The arguments
     *
     * @return  mixed
     *
     * @since   4.2.0
     */
    public function __call(string $name, array $arguments)
    {
        return call_user_func_array([$this->installerScript, $name], $arguments);
    }

    /**
     * Calls the function with the given name on the internal script with
     * some condition checking.
     *
     * @param   string $name       The name of the function
     * @param   array  $arguments  The arguments
     *
     * @return  bool
     *
     * @since   4.2.0
     */
    private function callOnScript(string $name, array $arguments): bool
    {
        if (!method_exists($this->installerScript, $name)) {
            return true;
        }

        if ($this->installerScript instanceof DatabaseAwareInterface) {
            try {
                $this->installerScript->setDatabase($this->getDatabase());
            } catch (DatabaseNotFoundException $e) {
                @trigger_error(sprintf('Database must be set, this will not be caught anymore in 6.0 in %s.', __METHOD__), E_USER_DEPRECATED);
                $this->installerScript->setDatabase(Factory::getContainer()->get(DatabaseInterface::class));
            }
        }

        $return = $this->__call($name, $arguments);

        // When function doesn't have a return value, assume it succeeded
        if ($return === null) {
            return true;
        }

        return (bool) $return;
    }
}
