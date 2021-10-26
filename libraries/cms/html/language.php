<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;

/**
 * Utility class for internationalization.
 *
 * @since  __DEPLOY_VERSION__
 */
abstract class JHtmlLanguage
{
	/**
	 * Wrap inline text or markup into an HTML inline element with a "dir" attribute
	 * and optionally a "lang" attribute if the direction of the text or markup shall
	 * be different to the document's direction.
	 *
	 * @param   string  $value      Text or markup to be shown in the desired direction
	 * @param   string  $direction  Desired direction (auto, ltr or rtl)
	 * @param   string  $element    HTML inline element to wrap the text or markup into
	 * @param   string  $language   Optional lang attribute for element, empty string if not used
	 *
	 * @return  string  HTML markup for the desired direction
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 */
	public static function inlineBidirectional($value, $direction = 'auto', $element = 'span', $language = '')
	{
		if (strtolower(Factory::getDocument()->getDirection()) === $direction)
		{
			return $value;
		}

		if ($language)
		{
			return '<' . $element . ' dir="' . $direction . '" lang="' . $language . '">' . $value . '</' . $element . '>';
		}

		return '<' . $element . ' dir="' . $direction . '">' . $value . '</' . $element . '>';
	}
}
