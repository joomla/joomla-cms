<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Installer;

use Joomla\CMS\Event\AbstractImmutableEvent;
use Joomla\CMS\MVC\Model\BaseModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for Installer events
 *
 * @since  5.0.0
 */
abstract class InstallerEvent extends AbstractImmutableEvent
{
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

        if (!\array_key_exists('subject', $this->arguments)) {
            throw new \BadMethodCallException("Argument 'subject' of event {$name} is required but has not been provided");
        }

        if (!\array_key_exists('package', $this->arguments)) {
            throw new \BadMethodCallException("Argument 'package' of event {$name} is required but has not been provided");
        }

        if (key($arguments) === 0) {
            $this->arguments['package'] = $arguments[1];
        } elseif (\array_key_exists('package', $arguments)) {
            $this->arguments['package'] = $arguments['package'];
        }
    }

    /**
     * Setter for the subject argument.
     *
     * @param   BaseModel  $value  The value to set
     *
     * @return  BaseModel
     *
     * @since  5.0.0
     */
    protected function onSetSubject(BaseModel $value): BaseModel
    {
        return $value;
    }

    /**
     * Setter for the package argument.
     *
     * @param   ?array  $value  The value to set
     *
     * @return  ?array
     *
     * @since  5.0.0
     */
    protected function onSetPackage(?array $value): ?array
    {
        return $value;
    }

    /**
     * Getter for the model.
     *
     * @return  BaseModel
     *
     * @since  5.0.0
     */
    public function getModel(): BaseModel
    {
        return $this->arguments['subject'];
    }

    /**
     * Getter for the package.
     *
     * @return  ?array
     *
     * @since  5.0.0
     */
    public function getPackage(): ?array
    {
        return $this->arguments['package'] ?? null;
    }

    /**
     * Update the package.
     *
     * @param   ?array  $value  The value to set
     *
     * @return  static
     *
     * @since  5.0.0
     */
    public function updatePackage(?array $value): static
    {
        $this->arguments['package'] = $this->onSetPackage($value);

        return $this;
    }
}
