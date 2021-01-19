<?php
/**
 * Akeeba Frontend Framework (FEF)
 *
 * @package       fef
 * @copyright (c) 2017-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license       GNU General Public License version 3, or later
 */

// Protect from unauthorized access
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die();

@include_once(__DIR__ . '/version.php');

if (!defined('AKEEBAFEF_VERSION'))
{
	define('AKEEBAFEF_VERSION', 'dev');
	define('AKEEBAFEF_DATE', gmdate('Y-m-d'));
}

class AkeebaFEFHelper
{
	/**
	 * Media versioning tag
	 *
	 * @var  string
	 */
	public static $tag = null;

	/**
	 * Is FEF already loaded?
	 *
	 * @var  bool
	 */
	public static $loaded = false;

	/**
	 * Loads Akeeba Frontend Framework, both CSS and JS
	 *
	 * @param   bool  $withReset  Should I also load the CSS reset for the FEF container?
	 * @param   bool  $dark       Include Dark Mode CSS and JS?
	 *
	 * @return  void
	 */
	public static function load($withReset = true, $dark = false)
	{
		if (self::$loaded)
		{
			return;
		}

		self::$loaded = true;

		if ($withReset)
		{
			self::loadCSS('css/fef-reset.min.css');
		}

		self::loadCSS('fef/fef-joomla.min.css');

		self::loadScript('loader');
		self::loadScript('tabs');
		self::loadScript('dropdown');

		if ($dark)
		{
			self::loadCSS('fef/dark.min.css');
		}
	}

	public static function loadScript($name)
	{
		if (!self::$loaded)
		{
			return;
		}

		$defer = $name !== 'loader';

		$testFile = sprintf("%s/js/%s.min.js", __DIR__, $name);

		if (!@is_file($testFile))
		{
			return;
		}

		self::loadJS('fef/' . $name . '.min.js', $defer);
	}

	/**
	 * Load a JavaScript file using the Joomla! API.
	 *
	 * Special considerations:
	 *
	 * We always load the minified version of the file. Joomla! will automatically use the non-minified one if Debug
	 * Site is enabled.
	 *
	 * You can have browser-specific files, e.g. foo_firefox.min.js, foo_firefox_57.min.js etc. These are loaded
	 * automatically instead of the foo.js file as needed.
	 *
	 * This method goes through Joomla's script loader, thus allowing template media overrides. The media overrides are
	 * supposed to be in the templates/YOUR_TEMPLATE/js/fef folder for FEF.
	 *
	 * @param   string  $file  The Joomla!-coded path of the file, e.g. 'foo/bar.min.js' for the JavaScript file
	 *                         media/foo/js/bar.min.js
	 *
	 * @param   bool    $defer Should I load the script defered?
	 *
	 * @return void
	 */
	protected static function loadJS($file, $defer = true)
	{
		HTMLHelper::_('script', $file, [
			'version'       => self::getMediaVersion(),
			'relative'      => true,
			'detectDebug'   => true,
			'framework'     => false,
			'pathOnly'      => false,
			'detectBrowser' => true,
		], [
			'defer' => $defer,
			'async' => false,
		]);
	}

	/**
	 * Load a CSS file using the Joomla! API.
	 *
	 * Special considerations:
	 *
	 * We always as Joomla to load the minified version of a file. Joomla! will automatically use the non-minified one
	 * if Debug Site is enabled.
	 *
	 * You can have browser-specific files, e.g. foo_firefox.min.css, foo_firefox_57.min.css etc. These are loaded
	 * automatically instead of the foo.css file as needed.
	 *
	 * This method goes through Joomla's script loader, thus allowing template media overrides. The media overrides are
	 * supposed to be in the templates/YOUR_TEMPLATE/css/fef folder for FEF.
	 *
	 * @param   string  $file  The Joomla!-coded path of the file, e.g. 'foo/bar.min.css' for the JavaScript file
	 *                         media/foo/css/bar.min.css
	 *
	 * @return  void
	 */
	protected static function loadCSS($file)
	{
		/**
		 * IMPORTANT! The $attribs (final parameter) MUST ALWAYS be non-empty. Otherwise Joomla! 3.x bugs out.
		 */
		HTMLHelper::_('stylesheet', $file, [
			'version'       => self::getMediaVersion(),
			'relative'      => true,
			'detectDebug'   => true,
			'pathOnly'      => false,
			'detectBrowser' => true,
		], [
			'type' => 'text/css',
		]);

	}

	/**
	 * Get the media versioning tag. If it's not set, create one first.
	 *
	 * @return  string
	 */
	protected static function getMediaVersion()
	{
		if (empty(self::$tag))
		{
			self::$tag = md5(AKEEBAFEF_VERSION . AKEEBAFEF_DATE . self::getApplicationSecret());
		}

		return self::$tag;
	}

	/**
	 * Return the secret key for the Joomla! installation. Falls back to an MD5 of our file mod time.
	 *
	 * @return  string
	 */
	protected static function getApplicationSecret()
	{
		$secret = md5(filemtime(__FILE__));

		// Get the site's secret
		try
		{
			$app = Factory::getApplication();

			if (method_exists($app, 'get'))
			{
				return $app->get('secret', $secret);
			}
		}
		catch (Exception $e)
		{
		}

		return $secret;
	}
}
