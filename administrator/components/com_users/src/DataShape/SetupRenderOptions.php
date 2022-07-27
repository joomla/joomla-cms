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
use Joomla\Database\ParameterType;

/**
 * Data shape for Method Setup Render Options
 *
 * @property string $default_title Default title if you are setting up this MFA Method for the first time
 * @property string $pre_message   Custom HTML to display above the MFA setup form
 * @property string $table_heading Heading for displayed tabular data. Typically used to display a list of fixed MFA
 *                                 codes, TOTP setup parameters etc
 * @property array  $tabular_data  Any tabular data to display (label => custom HTML). See above
 * @property array  $hidden_data   Hidden fields to include in the form (name => value)
 * @property string $field_type    How to render the MFA setup code field. "input" (HTML input element) or "custom"
 *                                 (custom HTML)
 * @property string $input_type    The type attribute for the HTML input box. Typically "text" or "password". Use any
 *                                 HTML5 input type.
 * @property string $input_value   Pre-filled value for the HTML input box. Typically used for fixed codes, the fixed
 *                                 YubiKey ID etc.
 * @property string $placeholder   Placeholder text for the HTML input box. Leave empty if you don't need it.
 * @property string $label         Label to show above the HTML input box. Leave empty if you don't need it.
 * @property string $html          Custom HTML. Only used when field_type = custom.
 * @property bool   $show_submit   Should I show the submit button (apply the MFA setup)?
 * @property string $submit_class  Additional CSS classes for the submit button (apply the MFA setup)
 * @property string $post_message  Custom HTML to display below the MFA setup form
 * @property string $help_url      A URL with help content for this Method to display to the user
 *
 * @since       4.2.0
 */
class SetupRenderOptions extends DataShapeObject
{
    /**
     * Display a standard HTML5 input field. Use the input_type, placeholder and label properties to set it up.
     *
     * @since  4.2.0
     */
    public const FIELD_INPUT = 'input';

    /**
     * Display a custom HTML document. Use the html property to set it up.
     *
     * @since  4.2.0
     */
    public const FIELD_CUSTOM = 'custom';

    /**
     * Default title if you are setting up this MFA Method for the first time
     *
     * @var   string
     * @since 4.2.0
     */
    protected $default_title = '';

    /**
     * Custom HTML to display above the MFA setup form parameters etc
     *
     * @var   string
     * @since 4.2.0
     */
    protected $pre_message = '';

    /**
     * Heading for displayed tabular data. Typically used to display a list of fixed MFA codes, TOTP setup
     *
     * @var   string
     * @since 4.2.0
     */
    protected $table_heading = '';

    /**
     * Any tabular data to display (label => custom HTML). See above
     *
     * @var   array
     * @since 4.2.0
     */
    protected $tabular_data = [];

    /**
     * Hidden fields to include in the form (name => value)
     *
     * @var   array
     * @since 4.2.0
     */
    protected $hidden_data = [];

    /**
     * How to render the MFA setup code field. "input" (HTML input element) or "custom" (custom HTML)
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
    protected $input_type = 'text';

    /**
     * Attributes other than type and id which will be added to the HTML input box.
     *
     * @var    array
     * @@since 4.2.0
     */
    protected $input_attributes = [];

    /**
     * Pre-filled value for the HTML input box. Typically used for fixed codes, the fixed YubiKey ID etc.
     *
     * @var   string
     * @since 4.2.0
     */
    protected $input_value = '';

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
     * Should I show the submit button (apply the MFA setup)?
     *
     * @var   boolean
     * @since 4.2.0
     */
    protected $show_submit = true;

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
    protected $submit_icon = 'icon icon-ok';

    /**
     * Language key to use for the text on the submit button
     *
     * @var    string
     * @since 4.2.0
     */
    protected $submit_text = 'JSAVE';

    /**
     * Custom HTML to display below the MFA setup form
     *
     * @var   string
     * @since 4.2.0
     */
    protected $post_message = '';

    /**
     * A URL with help content for this Method to display to the user
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
    protected function setField_type($value)
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
     * @since  4.2.0
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
