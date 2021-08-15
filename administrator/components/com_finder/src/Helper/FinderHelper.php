<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Finder\Administrator\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Extension\ExtensionHelper;

/**
 * Helper class for Finder.
 *
 * @since  2.5
 */
class FinderHelper
{
	/**
	 * The extension name.
	 *
	 * @var    string
	 * @since  2.5
	 */
	public static $extension = 'com_finder';

	/**
	 * Gets the finder system plugin extension id.
	 *
	 * @return  integer  The finder system plugin extension id.
	 *
	 * @since   3.6.0
	 */
	public static function getFinderPluginId()
	{
		$pluginRecord = ExtensionHelper::getExtensionRecord('finder', 'plugin', null, 'content');

		return $pluginRecord !== null ? $pluginRecord->extension_id : 0;
	}
}
