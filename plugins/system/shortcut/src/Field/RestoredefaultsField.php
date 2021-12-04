<?php
/**
 * @package     Joomla.Plugins
 * @subpackage  System.shortcut
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Shortcut\Field;

\defined('_JEXEC') or die;

/**
 * Unique ID Field class for the Shortcut Plugin.
 *
 * @since  3.5
 */
class RestoredefaultsField extends AbstractShortcutField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.5
	 */
	protected $type = 'Restoredefaults';

	/**
	 * Name of the layout being used to render the field
	 *
	 * @var    string
	 * @since  3.5
	 */
	protected $layout = 'field.restoredefaults';
}
