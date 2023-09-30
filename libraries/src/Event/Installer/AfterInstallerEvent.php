<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Installer;

use Joomla\CMS\Installer\Installer as ExtensionInstaller;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for Installer events
 *
 * @since  5.0.0
 */
class AfterInstallerEvent extends InstallerEvent
{
    /**
     * The argument names, in order expected by legacy plugins.
     *
     * @var array
     *
     * @since  5.0.0
     * @deprecated 5.0 will be removed in 6.0
     */
    protected $legacyArgumentsOrder = ['subject', 'package', 'installer', 'installerResult', 'message'];

    /**
     * Constructor.
     *
     * @param   string  $name       The event name.
     * @param   array   $arguments  The event arguments.
     *
     * @throws  \BadMethodCallException
     *
     * @since   5.0.0
     */
    public function __construct($name, array $arguments = [])
    {
        parent::__construct($name, $arguments);

        if (!\array_key_exists('installer', $this->arguments)) {
            throw new \BadMethodCallException("Argument 'installer' of event {$name} is required but has not been provided");
        }

        if (!\array_key_exists('installerResult', $this->arguments)) {
            throw new \BadMethodCallException("Argument 'installerResult' of event {$name} is required but has not been provided");
        }

        if (!\array_key_exists('message', $this->arguments)) {
            throw new \BadMethodCallException("Argument 'message' of event {$name} is required but has not been provided");
        }

        // For backward compatibility make sure the values is referenced
        // @todo: Remove in Joomla 6
        // @deprecated: Passing argument by reference is deprecated, and will not work in Joomla 6
        if (key($arguments) === 0) {
            $this->arguments['installerResult'] = &$arguments[3];
            $this->arguments['message']         = &$arguments[4];
        } elseif (\array_key_exists('installerResult', $arguments)) {
            $this->arguments['installerResult'] = &$arguments['installerResult'];
            $this->arguments['message']         = &$arguments['message'];
        }
    }

    /**
     * Setter for the installer argument.
     *
     * @param   ExtensionInstaller  $value  The value to set
     *
     * @return  ExtensionInstaller
     *
     * @since  5.0.0
     */
    protected function onSetInstaller(ExtensionInstaller $value): ExtensionInstaller
    {
        return $value;
    }

    /**
     * Setter for the installerResult argument.
     *
     * @param   bool  $value  The value to set
     *
     * @return  bool
     *
     * @since  5.0.0
     */
    protected function onSetInstallerResult(bool $value): bool
    {
        return $value;
    }

    /**
     * Setter for the message argument.
     *
     * @param   string  $value  The value to set
     *
     * @return  string
     *
     * @since  5.0.0
     */
    protected function onSetMessage(string $value): string
    {
        return $value;
    }

    /**
     * Getter for the installer.
     *
     * @return  ExtensionInstaller
     *
     * @since  5.0.0
     */
    public function getInstaller(): ExtensionInstaller
    {
        return $this->arguments['installer'];
    }

    /**
     * Getter for the installer result.
     *
     * @return  bool
     *
     * @since  5.0.0
     */
    public function getInstallerResult(): bool
    {
        return $this->arguments['installerResult'];
    }

    /**
     * Getter for the message.
     *
     * @return  string
     *
     * @since  5.0.0
     */
    public function getMessage(): string
    {
        return $this->arguments['message'];
    }

    /**
     * Update the installerResult.
     *
     * @param   bool  $value  The value to set
     *
     * @return  static
     *
     * @since  5.0.0
     */
    public function updateInstallerResult(bool $value): static
    {
        $this->arguments['installerResult'] = $this->onSetInstallerResult($value);

        return $this;
    }

    /**
     * Update the message.
     *
     * @param   string  $value  The value to set
     *
     * @return  static
     *
     * @since  5.0.0
     */
    public function updateMessage(string $value): static
    {
        $this->arguments['message'] = $this->onSetMessage($value);

        return $this;
    }
}
