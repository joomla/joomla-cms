<?php

/**
 * @package    Joomla.Administrator
 * @subpackage com_users
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Administrator\DataShape;

use InvalidArgumentException;

/**
 * @property  string $pre_message         Custom HTML to display above the MFA form
 * @property  string $field_type          How to render the MFA code field. "input" or "custom".
 * @property  string $input_type          The type attribute for the HTML input box. Typically "text" or "password".
 * @property  string $placeholder         Placeholder text for the HTML input box. Leave empty if you don't need it.
 * @property  string $label               Label to show above the HTML input box. Leave empty if you don't need it.
 * @property  string $html                Custom HTML. Only used when field_type = custom.
 * @property  string $post_message        Custom HTML to display below the MFA form
 * @property  bool   $hide_submit         Should I hide the default Submit button?
 * @property  bool   $allowEntryBatching  Is this method validating against all configured authenticators of this type?
 * @property  string $help_url            URL for help content
 *
 * @since 4.2.0
 */
class CaptiveRenderOptions extends DataShapeObject
{
    /**
     * Display a standard HTML5 input field. Use the input_type, placeholder and label properties to set it up.
     *
     * @since 4.2.0
     */
    public const FIELD_INPUT = 'input';

    /**
     * Display a custom HTML document. Use the html property to set it up.
     *
     * @since 4.2.0
     */
    public const FIELD_CUSTOM = 'custom';

    /**
     * Custom HTML to display above the MFA form
     *
     * @var   string
     * @since 4.2.0
     */
    protected $pre_message = '';

    /**
     * How to render the MFA code field. "input" (HTML input element) or "custom" (custom HTML)
     *
     * @var   string
     * @since 4.2.0
     */
    protected $field_type = 'input';

    /**
     * The type attribute for the HTML input box. Typically "text" or "password". Use any HTML5 input type.
     *
     * @var   string
     * @since 4.2.0
     */
    protected $input_type = '';

    /**
     * Attributes other than type and id which will be added to the HTML input box.
     *
     * @var    array
     * @@since 4.2.0
     */
    protected $input_attributes = [];

    /**
     * Placeholder text for the HTML input box. Leave empty if you don't need it.
     *
     * @var   string
     * @since 4.2.0
     */
    protected $placeholder = '';

    /**
     * Label to show above the HTML input box. Leave empty if you don't need it.
     *
     * @var   string
     * @since 4.2.0
     */
    protected $label = '';

    /**
     * Custom HTML. Only used when field_type = custom.
     *
     * @var   string
     * @since 4.2.0
     */
    protected $html = '';

    /**
     * Custom HTML to display below the MFA form
     *
     * @var   string
     * @since 4.2.0
     */
    protected $post_message = '';

    /**
     * Should I hide the default Submit button?
     *
     * @var   boolean
     * @since 4.2.0
     */
    protected $hide_submit = false;

    /**
     * Additional CSS classes for the submit button (apply the MFA setup)
     *
     * @var   string
     * @since 4.2.0
     */
    protected $submit_class = '';

    /**
     * Icon class to use for the submit button
     *
     * @var    string
     * @since 4.2.0
     */
    protected $submit_icon = 'icon icon-rightarrow icon-arrow-right';

    /**
     * Language key to use for the text on the submit button
     *
     * @var    string
     * @since 4.2.0
     */
    protected $submit_text = 'COM_USERS_MFA_VALIDATE';

    /**
     * Is this MFA method validating against all configured authenticators of the same type?
     *
     * @var   boolean
     * @since 4.2.0
     */
    protected $allowEntryBatching = true;

    /**
     * URL for help content
     *
     * @var   string
     * @since 4.2.0
     */
    protected $help_url = '';

    /**
     * Setter for the field_type property
     *
     * @param   string  $value  One of self::FIELD_INPUT, self::FIELD_CUSTOM
     *
     * @since   4.2.0
     * @throws  InvalidArgumentException
     */
    // phpcs:ignore
    protected function setField_type(string $value)
    {
        if (!in_array($value, [self::FIELD_INPUT, self::FIELD_CUSTOM])) {
            throw new InvalidArgumentException('Invalid value for property field_type.');
        }

        $this->field_type = $value;
    }

    /**
     * Setter for the input_attributes property.
     *
     * @param   array  $value  The value to set
     *
     * @return  void
     * @@since  4.2.0
     */
    // phpcs:ignore
    protected function setInput_attributes(array $value)
    {
        $forbiddenAttributes = ['id', 'type', 'name', 'value'];

        foreach ($forbiddenAttributes as $key) {
            if (isset($value[$key])) {
                unset($value[$key]);
            }
        }

        $this->input_attributes = $value;
    }
}
