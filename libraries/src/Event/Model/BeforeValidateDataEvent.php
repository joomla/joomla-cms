<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Model;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for Model Form event.
 * Example:
 *  new BeforeValidateDataEvent('onEventName', ['subject' => $form, 'data' => $data]);
 *
 * @since  5.0.0
 */
class BeforeValidateDataEvent extends FormEvent
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

        // For backward compatibility make sure the content is referenced
        // @todo: Remove in Joomla 6
        // @deprecated: Passing argument by reference is deprecated, and will not work in Joomla 6
        if (key($arguments) === 0) {
            $this->arguments['data'] = &$arguments[1];
        } elseif (\array_key_exists('data', $arguments)) {
            $this->arguments['data'] = &$arguments['data'];
        }
    }

    /**
     * Update the data.
     *
     * @param   object|array  $value  The value to set
     *
     * @return  static
     *
     * @since  5.0.0
     */
    public function updateData(object|array $value): static
    {
        $this->arguments['data'] = $this->onSetData($value);

        return $this;
    }
}
