<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Calendar
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::import('components.com_fields.libraries.fieldsplugin', JPATH_ADMINISTRATOR);

/**
 * Fields Calendar Plugin
 *
 * @since  3.7.0
 */
class PlgFieldsCalendar extends FieldsPlugin
{
	/**
	 * Transforms the field into an XML element and appends it as child on the given parent. This
	 * is the default implementation of a field. Form fields which do support to be transformed into
	 * an XML Element mut implemet the JFormDomfieldinterface.
	 *
	 * @param   stdClass    $field   The field.
	 * @param   DOMElement  $parent  The field node parent.
	 * @param   JForm       $form    The form.
	 *
	 * @return  DOMElement
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onCustomFieldsPrepareDom($field, DOMElement $parent, JForm $form)
	{
		$fieldNode = parent::onCustomFieldsPrepareDom($field, $parent, $form);

		if (!$fieldNode)
		{
			return $fieldNode;
		}

		// Set filter to user UTC
		$fieldNode->setAttribute('filter', 'USER_UTC');

		return $fieldNode;
	}

	/**
	 * Convert date/time format between `date()` and `strftime()`.
	 *
	 * Timezone conversion is done for Unix. Windows users must exchange %z and %Z.
	 *
	 * Unsupported date formats : S, n, t, L, B, G, u, e, I, P, Z, c, r
	 * Unsupported strftime formats : %U, %W, %C, %g, %r, %R, %T, %X, %c, %D, %F, %x
	 *
	 * Example:
	 * Convert `%A, %B %e, %Y, %l:%M %P` to `l, F j, Y, g:i a`, and vice versa for "Saturday, March 10, 2001, 5:16 pm".
	 *
	 * @param   string  $format  The format to parse.
	 * @param   string  $syntax  The format's syntax. Either 'strftime' for `strtime()` or 'date' for `date()`.
	 *
	 * @return  bool|string  Returns a string formatted according $syntax using the given $format or `false`.
	 *
	 * @link http://php.net/manual/en/function.strftime.php#96424
	 * @link https://gist.github.com/mcaskill/02636e5970be1bb22270
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function changeFormat($format, $syntax = 'strftime')
	{
		// Syntax from http://php.net/manual/en/function.strftime.php
		$strfSyntax = array(
			// Day - no strf eq : S (created one called %O)
			'%O', '%d', '%a', '%e', '%A', '%u', '%w', '%j',
			// Week - no date eq : %U, %W
			'%V',
			// Month - no strf eq : n, t
			'%B', '%m', '%b', '%-m',
			// Year - no strf eq : L; no date eq : %C, %g
			'%G', '%Y', '%y',
			// Time - no strf eq : B, G, u; no date eq : %r, %R, %T, %X
			'%P', '%p', '%l', '%I', '%H', '%M', '%S',
			// Timezone - no strf eq : e, I, P, Z
			'%z', '%Z',
			// Full Date / Time - no strf eq : c, r; no date eq : %c, %D, %F, %x
			'%s'
		);

		// Syntax from http://php.net/manual/en/function.date.php
		$dateSyntax = array(
			'S', 'd', 'D', 'j', 'l', 'N', 'w', 'z',
			'W',
			'F', 'm', 'M', 'n',
			'o', 'Y', 'y',
			'a', 'A', 'g', 'h', 'H', 'i', 's',
			'O', 'T',
			'U'
		);

		// Strftime to date
		$from = $strfSyntax;
		$to   = $dateSyntax;

		if ($syntax == 'date')
		{
			// Date to strftime
			$from = $dateSyntax;
			$to   = $strfSyntax;
		}

		$pattern = array_map(
			function ($s) {
				return '/(?<!\\\\|\%)' . $s . '/';
			},
			$from
		);

		return preg_replace($pattern, $to, $format);
	}
}
