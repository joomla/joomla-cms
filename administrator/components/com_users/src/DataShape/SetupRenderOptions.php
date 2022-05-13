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
 * Data shape for Method Setup Render Options
 *
 * @property string $default_title Default title if you are setting up this TFA Method for the first time
 * @property string $pre_message   Custom HTML to display above the TFA setup form
 * @property string $table_heading Heading for displayed tabular data. Typically used to display a list of fixed TFA
 *                                 codes, TOTP setup parameters etc
 * @property array  $tabular_data  Any tabular data to display (label => custom HTML). See above
 * @property array  $hidden_data   Hidden fields to include in the form (name => value)
 * @property string $field_type    How to render the TFA setup code field. "input" (HTML input element) or "custom"
 *                                 (custom HTML)
 * @property string $input_type    The type attribute for the HTML input box. Typically "text" or "password". Use any
 *                                 HTML5 input type.
 * @property string $input_value   Pre-filled value for the HTML input box. Typically used for fixed codes, the fixed
 *                                 YubiKey ID etc.
 * @property string $placeholder   Placeholder text for the HTML input box. Leave empty if you don't need it.
 * @property string $label         Label to show above the HTML input box. Leave empty if you don't need it.
 * @property string $html          Custom HTML. Only used when field_type = custom.
 * @property bool   $show_submit   Should I show the submit button (apply the TFA setup)?
 * @property string $submit_class  Additional CSS classes for the submit button (apply the TFA setup)
 * @property string $post_message  Custom HTML to display below the TFA setup form
 * @property string $help_url      A URL with help content for this Method to display to the user
 *
 * @since       __DEPLOY_VERSION__
 */
class SetupRenderOptions extends DataShapeObject
{
	/**
	 * Display a standard HTML5 input field. Use the input_type, placeholder and label properties to set it up.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public const FIELD_INPUT = 'input';

	/**
	 * Display a custom HTML document. Use the html property to set it up.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public const FIELD_CUSTOM = 'custom';

	/**
	 * Default title if you are setting up this TFA Method for the first time
	 *
	 * @var   string
	 * @since __DEPLOY_VERSION__
	 */
	// phpcs:ignore
	protected $default_title = '';

	/**
	 * Custom HTML to display above the TFA setup form parameters etc
	 *
	 * @var   string
	 * @since __DEPLOY_VERSION__
	 */
	// phpcs:ignore
	protected $pre_message = '';

	/**
	 * Heading for displayed tabular data. Typically used to display a list of fixed TFA codes, TOTP setup
	 *
	 * @var   string
	 * @since __DEPLOY_VERSION__
	 */
	// phpcs:ignore
	protected $table_heading = '';

	/**
	 * Any tabular data to display (label => custom HTML). See above
	 *
	 * @var   array
	 * @since __DEPLOY_VERSION__
	 */
	// phpcs:ignore
	protected $tabular_data = [];

	/**
	 * Hidden fields to include in the form (name => value)
	 *
	 * @var   array
	 * @since __DEPLOY_VERSION__
	 */
	// phpcs:ignore
	protected $hidden_data = [];

	/**
	 * How to render the TFA setup code field. "input" (HTML input element) or "custom" (custom HTML)
	 *
	 * @var   string
	 * @since __DEPLOY_VERSION__
	 */
	// phpcs:ignore
	protected $field_type = 'input';

	/**
	 * The type attribute for the HTML input box. Typically "text" or "password". Use any HTML5 input type.
	 *
	 * @var   string
	 * @since __DEPLOY_VERSION__
	 */
	// phpcs:ignore
	protected $input_type = 'text';

	/**
	 * Pre-filled value for the HTML input box. Typically used for fixed codes, the fixed YubiKey ID etc.
	 *
	 * @var   string
	 * @since __DEPLOY_VERSION__
	 */
	// phpcs:ignore
	protected $input_value = '';

	/**
	 * Placeholder text for the HTML input box. Leave empty if you don't need it.
	 *
	 * @var   string
	 * @since __DEPLOY_VERSION__
	 */
	protected $placeholder = '';

	/**
	 * Label to show above the HTML input box. Leave empty if you don't need it.
	 *
	 * @var   string
	 * @since __DEPLOY_VERSION__
	 */
	protected $label = '';

	/**
	 * Custom HTML. Only used when field_type = custom.
	 *
	 * @var   string
	 * @since __DEPLOY_VERSION__
	 */
	protected $html = '';

	/**
	 * Should I show the submit button (apply the TFA setup)?
	 *
	 * @var   boolean
	 * @since __DEPLOY_VERSION__
	 */
	// phpcs:ignore
	protected $show_submit = true;

	/**
	 * Additional CSS classes for the submit button (apply the TFA setup)
	 *
	 * @var   string
	 * @since __DEPLOY_VERSION__
	 */
	// phpcs:ignore
	protected $submit_class = '';

	/**
	 * Custom HTML to display below the TFA setup form
	 *
	 * @var   string
	 * @since __DEPLOY_VERSION__
	 */
	// phpcs:ignore
	protected $post_message = '';

	/**
	 * A URL with help content for this Method to display to the user
	 *
	 * @var   string
	 * @since __DEPLOY_VERSION__
	 */
	// phpcs:ignore
	protected $help_url = '';

	/**
	 * Setter for the field_type property
	 *
	 * @param   string  $value  One of self::FIELD_INPUT, self::FIELD_CUSTOM
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  InvalidArgumentException
	 */
	// phpcs:ignore
	protected function setField_type($value)
	{
		if (!in_array($value, [self::FIELD_INPUT, self::FIELD_CUSTOM]))
		{
			throw new InvalidArgumentException('Invalid value for property field_type.');
		}
	}
}
