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

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;

/**
 * Base field for the Shortcut Plugin.
 *
 * @since  3.5
 */
abstract class AbstractShortcutField extends FormField
{
	/**
	 * Get the layouts paths
	 *
	 * @return  array
	 *
	 * @since   3.5
	 */
	protected function getLayoutPaths()
	{
		$template = Factory::getApplication()->getTemplate();

		return array(
			JPATH_ADMINISTRATOR . '/templates/' . $template . '/html/layouts/plugins/system/shortcut',
			JPATH_PLUGINS . '/system/shortcut/layouts',
			JPATH_SITE . '/layouts',
		);
	}
}
