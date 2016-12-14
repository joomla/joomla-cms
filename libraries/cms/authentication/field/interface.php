<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Authentication
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Interface to custom login form field objects. The login modules MUST use these objects, returned by
 * JAuthenticationHelper::getUserLoginFormFields, to display these extra fields.
 *
 * @since  __DEPLOY_VERSION__
 */
interface JAuthenticationFieldInterface
{
	/**
	 * Returns the login form field type: "field" for custom (input) fields which are rendered inline the login form;
	 * "button" for action buttons rendered in the submit button group of the form; "link" for a link rendered together
	 * with the "Forgot your password?" etc links.
	 *
	 * Please remember that when the user is already logged in you do NOT get to display any custom fields. Whatever
	 * processing you need to do on logout must happen server-side, typically in your "user" plugin.
	 *
	 * @return  string  field|button|link
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getType();

	/**
	 * For the "field" type, returns the icon CSS class to use when the login form is set up to only display icons, not
	 * text, for the fields. For the "button" type, returns the icon CSS class to use in the button. Ignored for the
	 * "link" type (just return an empty string).
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getIcon();

	/**
	 * Returns the label. For the "field" type this is the label text. For the "button" type this is the text displayed
	 * on the button. For the "link" type this is the link text.
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getLabel();

	/**
	 * Gets the input / action for this field. For the "field" type this is the input element's HTML (ideally rendered
	 * with an overridable JLayout); for the "button" type this is the link the button points to (use
	 * "javascript:something(); return false;" to call a JavaScript function instead); for the "link" type this is the
	 * link target, i.e. the URL to visit when the link is clicked.
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getInput();
}
