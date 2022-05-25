<?php
/**
 * @package     Joomla.Plugins
 * @subpackage  System.shortcut
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Shortcut\Field;

use Joomla\CMS\Form\FormField;

\defined('_JEXEC') or die;

/**
 * Unique ID Field class for the Shortcut Plugin.
 *
 * @since  3.5
 */
class KeyselectField extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $type = 'Keyselect';

	/**
	 * Name of the layout being used to render the field
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $layout = 'joomla.form.field.keyselect';

	/**
	 * Allow to override renderer include paths in child fields
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getLayoutPaths()
	{
		return array_merge(parent::getLayoutPaths(), [JPATH_PLUGINS . '/system/shortcut/layouts']);
	}
}
