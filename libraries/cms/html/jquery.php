<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * Utility class for jQuery JavaScript behaviors
 *
 * @since  3.0
 */
abstract class JHtmlJquery
{
	/**
	 * Array containing information for loaded files
	 *
	 * @var    array
	 * @since  3.0
	 */
	protected static $loaded = array();

	/**
	 * Method to load the jQuery JavaScript framework into the document head
	 *
	 * If debugging mode is on an uncompressed version of jQuery is included for easier debugging.
	 *
	 * @param   boolean  $noConflict  True to load jQuery in noConflict mode [optional]
	 * @param   mixed    $debug       Is debugging mode on? [optional]
	 * @param   boolean  $migrate     True to enable the jQuery Migrate plugin
	 *
	 * @return  void
	 *
	 * @since   3.0
	 *
	 * @deprecated 5.0  Use Joomla\CMS\WebAsset\WebAssetManager::enableAsset();
	 */
	public static function framework($noConflict = true, $debug = null, $migrate = false)
	{
		$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
		$wa->enableAsset('jquery');

		// Check if we are loading in noConflict
		if ($noConflict)
		{
			$wa->enableAsset('jquery-noconflict');
		}

		// Check if we are loading Migrate
		if ($migrate)
		{
			$wa->enableAsset('jquery-migrate');
		}

		return;
	}

	/**
	 * Method to load the jQuery UI JavaScript framework into the document head
	 *
	 * If debugging mode is on an uncompressed version of jQuery UI is included for easier debugging.
	 *
	 * @param   array  $components  The jQuery UI components to load [optional]
	 * @param   mixed  $debug       Is debugging mode on? [optional]
	 *
	 * @return  void
	 *
	 * @since   3.0
	 *
	 * @deprecated 5.0  Use Joomla\CMS\WebAsset\WebAssetManager::enableAsset();
	 */
	public static function ui(array $components = array('core'), $debug = null)
	{
		// Set an array containing the supported jQuery UI components handled by this method
		$supported = array('core', 'sortable');

		$wa = Factory::getApplication()->getDocument()->getWebAssetManager();

		foreach ($components as $component)
		{
			if (in_array($component, $supported))
			{
				$wa->enableAsset('jquery.ui.' . $component);
			}
		}

		return;
	}

	/**
	 * Auto set CSRF token to ajaxSetup so all jQuery ajax call will contains CSRF token.
	 *
	 * @param   string  $name  The CSRF meta tag name.
	 *
	 * @return  void
	 *
	 * @throws  \InvalidArgumentException
	 *
	 * @since   3.8.0
	 */
	public static function token($name = 'csrf.token')
	{
		// Only load once
		if (!empty(static::$loaded[__METHOD__][$name]))
		{
			return;
		}

		static::framework();
		HTMLHelper::_('form.csrf', $name);

		$doc = Factory::getDocument();

		$doc->addScriptDeclaration(
<<<JS
;(function ($) {
	$.ajaxSetup({
		headers: {
			'X-CSRF-Token': Joomla.getOptions('$name')
		}
	});
})(jQuery);
JS
		);

		static::$loaded[__METHOD__][$name] = true;
	}
}
