<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\HTML\Helpers;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * Utility class for jQuery JavaScript behaviors
 *
 * @since  3.0
 */
abstract class Jquery
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
	 * @deprecated 5.0  Use Joomla\CMS\WebAsset\WebAssetManager::useAsset();
	 */
	public static function framework($noConflict = true, $debug = null, $migrate = false)
	{
		$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
		$wa->useScript('jquery');

		// Check if we are loading in noConflict
		if ($noConflict)
		{
			$wa->useScript('jquery-noconflict');
		}

		// Check if we are loading Migrate
		if ($migrate)
		{
			$wa->useScript('jquery-migrate');
		}
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
