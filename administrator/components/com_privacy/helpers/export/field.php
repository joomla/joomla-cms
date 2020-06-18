<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   © 2018 Open Source Matters, Inc. <https://www.joomla.org/contribute-to-joomla.html>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Data object representing a field within an item.
 *
 * @since  3.9.0
 */
class PrivacyExportField
{
	/**
	 * The name of this field
	 *
	 * @var    string
	 * @since  3.9.0
	 */
	public $name;

	/**
	 * The field's value
	 *
	 * @var    mixed
	 * @since  3.9.0
	 */
	public $value;
}
