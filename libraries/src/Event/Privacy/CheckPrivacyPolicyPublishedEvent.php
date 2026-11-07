<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Privacy;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for Privacy events.
 * Example:
 *     new CheckPrivacyPolicyPublishedEvent('onEventName', ['subject' => $policyInfo]);
 *
 * @since  5.0.0
 */
class CheckPrivacyPolicyPublishedEvent extends PrivacyEvent
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

        if (key($arguments) === 0) {
            $this->arguments['subject'] = $arguments[0];
        } elseif (\array_key_exists('subject', $arguments)) {
            $this->arguments['subject'] = $arguments['subject'];
        }
    }

    /**
     * Setter for the subject argument.
     *
     * @param   array  $value  The value to set
     *
     * @return  array
     *
     * @since  5.0.0
     */
    protected function onSetSubject(array $value): array
    {
        if (!\array_key_exists('published', $value) || !\array_key_exists('articlePublished', $value) || !\array_key_exists('editLink', $value)) {
            throw new \UnexpectedValueException("Argument 'subject' of event {$this->name} is not of the expected type");
        }

        return $value;
    }

    /**
     * Getter for the policy check.
     *
     * @return  array
     *
     * @since  5.0.0
     */
    public function getPolicyInfo(): array
    {
        return $this->arguments['subject'];
    }

    /**
     * Update the PolicyInfo.
     *
     * @param   object[]  $value  The value to set
     *
     * @return  static
     *
     * @since  5.0.0
     */
    public function updatePolicyInfo(array $value): static
    {
        $this->arguments['subject'] = $this->onSetSubject($value);

        return $this;
    }
}
