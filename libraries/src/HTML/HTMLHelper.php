<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\HTML;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Environment\Browser;
use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Uri\Uri;
use Joomla\Utilities\ArrayHelper;

\JLoader::import('joomla.environment.browser');
\JLoader::import('joomla.filesystem.file');
\JLoader::import('joomla.filesystem.path');

/**
 * Utility class for all HTML drawing classes
 *
 * @since  1.5
 */
abstract class HTMLHelper
{
	/**
	 * Option values related to the generation of HTML output. Recognized
	 * options are:
	 *     fmtDepth, integer. The current indent depth.
	 *     fmtEol, string. The end of line string, default is linefeed.
	 *     fmtIndent, string. The string to use for indentation, default is
	 *     tab.
	 *
	 * @var    array
	 * @since  1.5
	 */
	public static $formatOptions = array('format.depth' => 0, 'format.eol' => "\n", 'format.indent' => "\t");

	/**
	 * An array to hold included paths
	 *
	 * @var    string[]
	 * @since  1.5
	 */
	protected static $includePaths = array();

	/**
	 * An array to hold method references
	 *
	 * @var    callable[]
	 * @since  1.6
	 */
	protected static $registry = array();

	/**
	 * Method to extract a key
	 *
	 * @param   string  $key  The name of helper method to load, (prefix).(class).function
	 *                        prefix and class are optional and can be used to load custom html helpers.
	 *
	 * @return  array  Contains lowercase key, prefix, file, function.
	 *
	 * @since   1.6
	 */
	protected static function extract($key)
	{
		$key = preg_replace('#[^A-Z0-9_\.]#i', '', $key);

		// Check to see whether we need to load a helper file
		$parts = explode('.', $key);

		$prefix = count($parts) === 3 ? array_shift($parts) : 'JHtml';
		$file   = count($parts) === 2 ? array_shift($parts) : '';
		$func   = array_shift($parts);

		return array(strtolower($prefix . '.' . $file . '.' . $func), $prefix, $file, $func);
	}

	/**
	 * Class loader method
	 *
	 * Additional arguments may be supplied and are passed to the sub-class.
	 * Additional include paths are also able to be specified for third-party use
	 *
	 * @param   string  $key  The name of helper method to load, (prefix).(class).function
	 *                        prefix and class are optional and can be used to load custom
	 *                        html helpers.
	 *
	 * @return  mixed  Result of HTMLHelper::call($function, $args)
	 *
	 * @since   1.5
	 * @throws  \InvalidArgumentException
	 */
	public static function _($key)
	{
		list($key, $prefix, $file, $func) = static::extract($key);

		if (array_key_exists($key, static::$registry))
		{
			$function = static::$registry[$key];
			$args     = func_get_args();

			// Remove function name from arguments
			array_shift($args);

			return static::call($function, $args);
		}

		$className = $prefix . ucfirst($file);

		if (!class_exists($className))
		{
			$path = \JPath::find(static::$includePaths, strtolower($file) . '.php');

			if (!$path)
			{
				throw new \InvalidArgumentException(sprintf('%s %s not found.', $prefix, $file), 500);
			}

			\JLoader::register($className, $path);

			if (!class_exists($className))
			{
				throw new \InvalidArgumentException(sprintf('%s not found.', $className), 500);
			}
		}

		$toCall = array($className, $func);

		if (!is_callable($toCall))
		{
			throw new \InvalidArgumentException(sprintf('%s::%s not found.', $className, $func), 500);
		}

		static::register($key, $toCall);
		$args = func_get_args();

		// Remove function name from arguments
		array_shift($args);

		return static::call($toCall, $args);
	}

	/**
	 * Registers a function to be called with a specific key
	 *
	 * @param   string  $key       The name of the key
	 * @param   string  $function  Function or method
	 *
	 * @return  boolean  True if the function is callable
	 *
	 * @since   1.6
	 */
	public static function register($key, $function)
	{
		list($key) = static::extract($key);

		if (is_callable($function))
		{
			static::$registry[$key] = $function;

			return true;
		}

		return false;
	}

	/**
	 * Removes a key for a method from registry.
	 *
	 * @param   string  $key  The name of the key
	 *
	 * @return  boolean  True if a set key is unset
	 *
	 * @since   1.6
	 */
	public static function unregister($key)
	{
		list($key) = static::extract($key);

		if (isset(static::$registry[$key]))
		{
			unset(static::$registry[$key]);

			return true;
		}

		return false;
	}

	/**
	 * Test if the key is registered.
	 *
	 * @param   string  $key  The name of the key
	 *
	 * @return  boolean  True if the key is registered.
	 *
	 * @since   1.6
	 */
	public static function isRegistered($key)
	{
		list($key) = static::extract($key);

		return isset(static::$registry[$key]);
	}

	/**
	 * Function caller method
	 *
	 * @param   callable  $function  Function or method to call
	 * @param   array     $args      Arguments to be passed to function
	 *
	 * @return  mixed   Function result or false on error.
	 *
	 * @link    https://www.php.net/manual/en/function.call-user-func-array.php
	 * @since   1.6
	 * @throws  \InvalidArgumentException
	 */
	protected static function call($function, $args)
	{
		if (!is_callable($function))
		{
			throw new \InvalidArgumentException('Function not supported', 500);
		}

		// PHP 5.3 workaround
		$temp = array();

		foreach ($args as &$arg)
		{
			$temp[] = &$arg;
		}

		return call_user_func_array($function, $temp);
	}

	/**
	 * Write a `<a>` element
	 *
	 * @param   string        $url      The relative URL to use for the href attribute
	 * @param   string        $text     The target attribute to use
	 * @param   array|string  $attribs  Attributes to be added to the `<a>` element
	 *
	 * @return  string
	 *
	 * @since   1.5
	 */
	public static function link($url, $text, $attribs = null)
	{
		if (is_array($attribs))
		{
			$attribs = ArrayHelper::toString($attribs);
		}

		return '<a href="' . $url . '" ' . $attribs . '>' . $text . '</a>';
	}

	/**
	 * Write a `<iframe>` element
	 *
	 * @param   string        $url       The relative URL to use for the src attribute.
	 * @param   string        $name      The target attribute to use.
	 * @param   array|string  $attribs   Attributes to be added to the `<iframe>` element
	 * @param   string        $noFrames  The message to display if the iframe tag is not supported.
	 *
	 * @return  string
	 *
	 * @since   1.5
	 */
	public static function iframe($url, $name, $attribs = null, $noFrames = '')
	{
		if (is_array($attribs))
		{
			$attribs = ArrayHelper::toString($attribs);
		}

		return '<iframe src="' . $url . '" ' . $attribs . ' name="' . $name . '">' . $noFrames . '</iframe>';
	}

	/**
	 * Include version with MD5SUM file in path.
	 *
	 * @param   string  $path  Folder name to search into (images, css, js, ...).
	 *
	 * @return  string  Query string to add.
	 *
	 * @since   3.7.0
	 *
	 * @deprecated   4.0  Usage of MD5SUM files is deprecated, use version instead.
	 */
	protected static function getMd5Version($path)
	{
		$md5 = dirname($path) . '/MD5SUM';

		if (file_exists($md5))
		{
			Log::add('Usage of MD5SUM files is deprecated, use version instead.', Log::WARNING, 'deprecated');

			return '?' . file_get_contents($md5);
		}

		return '';
	}

	/**
	 * Compute the files to be included
	 *
	 * @param   string   $folder         Folder name to search in (i.e. images, css, js).
	 * @param   string   $file           Path to file.
	 * @param   boolean  $relative       Flag if the path to the file is relative to the /media folder (and searches in template).
	 * @param   boolean  $detectBrowser  Flag if the browser should be detected to include specific browser files.
	 * @param   boolean  $detectDebug    Flag if debug mode is enabled to include uncompressed files if debug is on.
	 *
	 * @return  array    files to be included.
	 *
	 * @see     JBrowser
	 * @since   1.6
	 */
	protected static function includeRelativeFiles($folder, $file, $relative, $detectBrowser, $detectDebug)
	{
		// If http is present in filename just return it as an array
		if (strpos($file, 'http') === 0 || strpos($file, '//') === 0)
		{
			return array($file);
		}

		// Extract extension and strip the file
		$strip = \JFile::stripExt($file);
		$ext   = \JFile::getExt($file);

		// Prepare array of files
		$includes = array();

		// Detect browser and compute potential files
		if ($detectBrowser)
		{
			$navigator = Browser::getInstance();
			$browser   = $navigator->getBrowser();
			$major     = $navigator->getMajor();
			$minor     = $navigator->getMinor();

			// Try to include files named filename.ext, filename_browser.ext, filename_browser_major.ext, filename_browser_major_minor.ext
			// where major and minor are the browser version names
			$potential = array(
				$strip,
				$strip . '_' . $browser,
				$strip . '_' . $browser . '_' . $major,
				$strip . '_' . $browser . '_' . $major . '_' . $minor,
			);
		}
		else
		{
			$potential = array($strip);
		}

		// If relative search in template directory or media directory
		if ($relative)
		{
			// Get the template
			$template = Factory::getApplication()->getTemplate();

			// For each potential files
			foreach ($potential as $strip)
			{
				$files = array();

				// Detect debug mode
				if ($detectDebug && Factory::getConfig()->get('debug'))
				{
					/*
					 * Detect if we received a file in the format name.min.ext
					 * If so, strip the .min part out, otherwise append -uncompressed
					 */
					if (strlen($strip) > 4 && preg_match('#\.min$#', $strip))
					{
						$files[] = preg_replace('#\.min$#', '.', $strip) . $ext;
					}
					else
					{
						$files[] = $strip . '-uncompressed.' . $ext;
					}
				}

				$files[] = $strip . '.' . $ext;

				/*
				 * Loop on 1 or 2 files and break on first found.
				 * Add the content of the MD5SUM file located in the same folder to URL to ensure cache browser refresh
				 * This MD5SUM file must represent the signature of the folder content
				 */
				foreach ($files as $file)
				{
					// If the file is in the template folder
					$path = JPATH_THEMES . "/$template/$folder/$file";

					if (file_exists($path))
					{
						$includes[] = Uri::base(true) . "/templates/$template/$folder/$file" . static::getMd5Version($path);

						break;
					}
					else
					{
						// If the file contains any /: it can be in a media extension subfolder
						if (strpos($file, '/'))
						{
							// Divide the file extracting the extension as the first part before /
							list($extension, $file) = explode('/', $file, 2);

							// If the file yet contains any /: it can be a plugin
							if (strpos($file, '/'))
							{
								// Divide the file extracting the element as the first part before /
								list($element, $file) = explode('/', $file, 2);

								// Try to deal with plugins group in the media folder
								$path = JPATH_ROOT . "/media/$extension/$element/$folder/$file";

								if (file_exists($path))
								{
									$includes[] = Uri::root(true) . "/media/$extension/$element/$folder/$file" . static::getMd5Version($path);

									break;
								}

								// Try to deal with classical file in a media subfolder called element
								$path = JPATH_ROOT . "/media/$extension/$folder/$element/$file";

								if (file_exists($path))
								{
									$includes[] = Uri::root(true) . "/media/$extension/$folder/$element/$file" . static::getMd5Version($path);

									break;
								}

								// Try to deal with system files in the template folder
								$path = JPATH_THEMES . "/$template/$folder/system/$element/$file";

								if (file_exists($path))
								{
									$includes[] = Uri::root(true) . "/templates/$template/$folder/system/$element/$file" . static::getMd5Version($path);

									break;
								}

								// Try to deal with system files in the media folder
								$path = JPATH_ROOT . "/media/system/$folder/$element/$file";

								if (file_exists($path))
								{
									$includes[] = Uri::root(true) . "/media/system/$folder/$element/$file" . static::getMd5Version($path);

									break;
								}
							}
							else
							{
								// Try to deals in the extension media folder
								$path = JPATH_ROOT . "/media/$extension/$folder/$file";

								if (file_exists($path))
								{
									$includes[] = Uri::root(true) . "/media/$extension/$folder/$file" . static::getMd5Version($path);

									break;
								}

								// Try to deal with system files in the template folder
								$path = JPATH_THEMES . "/$template/$folder/system/$file";

								if (file_exists($path))
								{
									$includes[] = Uri::root(true) . "/templates/$template/$folder/system/$file" . static::getMd5Version($path);

									break;
								}

								// Try to deal with system files in the media folder
								$path = JPATH_ROOT . "/media/system/$folder/$file";

								if (file_exists($path))
								{
									$includes[] = Uri::root(true) . "/media/system/$folder/$file" . static::getMd5Version($path);

									break;
								}
							}
						}
						// Try to deal with system files in the media folder
						else
						{
							$path = JPATH_ROOT . "/media/system/$folder/$file";

							if (file_exists($path))
							{
								$includes[] = Uri::root(true) . "/media/system/$folder/$file" . static::getMd5Version($path);

								break;
							}
						}
					}
				}
			}
		}
		// If not relative and http is not present in filename
		else
		{
			foreach ($potential as $strip)
			{
				$files = array();

				// Detect debug mode
				if ($detectDebug && Factory::getConfig()->get('debug'))
				{
					/*
					 * Detect if we received a file in the format name.min.ext
					 * If so, strip the .min part out, otherwise append -uncompressed
					 */
					if (strlen($strip) > 4 && preg_match('#\.min$#', $strip))
					{
						$files[] = preg_replace('#\.min$#', '.', $strip) . $ext;
					}
					else
					{
						$files[] = $strip . '-uncompressed.' . $ext;
					}
				}

				$files[] = $strip . '.' . $ext;

				/*
				 * Loop on 1 or 2 files and break on first found.
				 * Add the content of the MD5SUM file located in the same folder to URL to ensure cache browser refresh
				 * This MD5SUM file must represent the signature of the folder content
				 */
				foreach ($files as $file)
				{
					$path = JPATH_ROOT . "/$file";

					if (file_exists($path))
					{
						$includes[] = Uri::root(true) . "/$file" . static::getMd5Version($path);

						break;
					}
				}
			}
		}

		return $includes;
	}

	/**
	 * Write a `<img>` element
	 *
	 * @param   string        $file        The relative or absolute URL to use for the src attribute.
	 * @param   string        $alt         The alt text.
	 * @param   array|string  $attribs     Attributes to be added to the `<img>` element
	 * @param   boolean       $relative    Flag if the path to the file is relative to the /media folder (and searches in template).
	 * @param   integer       $returnPath  Defines the return value for the method:
	 *                                     -1: Returns a `<img>` tag without looking for relative files
	 *                                     0: Returns a `<img>` tag while searching for relative files
	 *                                     1: Returns the file path to the image while searching for relative files
	 *
	 * @return  string|null  HTML markup for the image, relative path to the image, or null if path is to be returned but image is not found
	 *
	 * @since   1.5
	 */
	public static function image($file, $alt, $attribs = null, $relative = false, $returnPath = 0)
	{
		$returnPath = (int) $returnPath;

		if ($returnPath !== -1)
		{
			$includes = static::includeRelativeFiles('images', $file, $relative, false, false);
			$file = count($includes) ? $includes[0] : null;
		}

		// If only path is required
		if ($returnPath === 1)
		{
			return $file;
		}

		return '<img src="' . $file . '" alt="' . $alt . '" ' . trim((is_array($attribs) ? ArrayHelper::toString($attribs) : $attribs) . ' /') . '>';
	}

	/**
	 * Write a `<link>` element to load a CSS file
	 *
	 * @param   string  $file     Path to file
	 * @param   array   $options  Array of options. Example: array('version' => 'auto', 'conditional' => 'lt IE 9')
	 * @param   array   $attribs  Array of attributes. Example: array('id' => 'scriptid', 'async' => 'async', 'data-test' => 1)
	 *
	 * @return  array|string|null  nothing if $returnPath is false, null, path or array of path if specific CSS browser files were detected
	 *
	 * @see     Browser
	 * @since   1.5
	 * @deprecated 4.0  The (file, attribs, relative, pathOnly, detectBrowser, detectDebug) method signature is deprecated,
	 *                  use (file, options, attributes) instead.
	 */
	public static function stylesheet($file, $options = array(), $attribs = array())
	{
		// B/C before 3.7.0
		if (!is_array($attribs))
		{
			Log::add('The stylesheet method signature used has changed, use (file, options, attributes) instead.', Log::WARNING, 'deprecated');

			$argList = func_get_args();
			$options = array();

			// Old parameters.
			$attribs                  = isset($argList[1]) ? $argList[1] : array();
			$options['relative']      = isset($argList[2]) ? $argList[2] : false;
			$options['pathOnly']      = isset($argList[3]) ? $argList[3] : false;
			$options['detectBrowser'] = isset($argList[4]) ? $argList[4] : true;
			$options['detectDebug']   = isset($argList[5]) ? $argList[5] : true;
		}
		else
		{
			$options['relative']      = isset($options['relative']) ? $options['relative'] : false;
			$options['pathOnly']      = isset($options['pathOnly']) ? $options['pathOnly'] : false;
			$options['detectBrowser'] = isset($options['detectBrowser']) ? $options['detectBrowser'] : true;
			$options['detectDebug']   = isset($options['detectDebug']) ? $options['detectDebug'] : true;
		}

		$includes = static::includeRelativeFiles('css', $file, $options['relative'], $options['detectBrowser'], $options['detectDebug']);

		// If only path is required
		if ($options['pathOnly'])
		{
			if (count($includes) === 0)
			{
				return;
			}

			if (count($includes) === 1)
			{
				return $includes[0];
			}

			return $includes;
		}

		// If inclusion is required
		$document = Factory::getDocument();

		foreach ($includes as $include)
		{
			// If there is already a version hash in the script reference (by using deprecated MD5SUM).
			if ($pos = strpos($include, '?') !== false)
			{
				$options['version'] = substr($include, $pos + 1);
			}

			$document->addStyleSheet($include, $options, $attribs);
		}
	}

	/**
	 * Write a `<script>` element to load a JavaScript file
	 *
	 * @param   string  $file     Path to file.
	 * @param   array   $options  Array of options. Example: array('version' => 'auto', 'conditional' => 'lt IE 9')
	 * @param   array   $attribs  Array of attributes. Example: array('id' => 'scriptid', 'async' => 'async', 'data-test' => 1)
	 *
	 * @return  array|string|null  Nothing if $returnPath is false, null, path or array of path if specific JavaScript browser files were detected
	 *
	 * @see     HTMLHelper::stylesheet()
	 * @since   1.5
	 * @deprecated 4.0  The (file, framework, relative, pathOnly, detectBrowser, detectDebug) method signature is deprecated,
	 *                  use (file, options, attributes) instead.
	 */
	public static function script($file, $options = array(), $attribs = array())
	{
		// B/C before 3.7.0
		if (!is_array($options))
		{
			Log::add('The script method signature used has changed, use (file, options, attributes) instead.', Log::WARNING, 'deprecated');

			$argList = func_get_args();
			$options = array();
			$attribs = array();

			// Old parameters.
			$options['framework']     = isset($argList[1]) ? $argList[1] : false;
			$options['relative']      = isset($argList[2]) ? $argList[2] : false;
			$options['pathOnly']      = isset($argList[3]) ? $argList[3] : false;
			$options['detectBrowser'] = isset($argList[4]) ? $argList[4] : true;
			$options['detectDebug']   = isset($argList[5]) ? $argList[5] : true;
		}
		else
		{
			$options['framework']     = isset($options['framework']) ? $options['framework'] : false;
			$options['relative']      = isset($options['relative']) ? $options['relative'] : false;
			$options['pathOnly']      = isset($options['pathOnly']) ? $options['pathOnly'] : false;
			$options['detectBrowser'] = isset($options['detectBrowser']) ? $options['detectBrowser'] : true;
			$options['detectDebug']   = isset($options['detectDebug']) ? $options['detectDebug'] : true;
		}

		// Include MooTools framework
		if ($options['framework'])
		{
			static::_('behavior.framework');
		}

		$includes = static::includeRelativeFiles('js', $file, $options['relative'], $options['detectBrowser'], $options['detectDebug']);

		// If only path is required
		if ($options['pathOnly'])
		{
			if (count($includes) === 0)
			{
				return;
			}

			if (count($includes) === 1)
			{
				return $includes[0];
			}

			return $includes;
		}

		// If inclusion is required
		$document = Factory::getDocument();

		foreach ($includes as $include)
		{
			// If there is already a version hash in the script reference (by using deprecated MD5SUM).
			if ($pos = strpos($include, '?') !== false)
			{
				$options['version'] = substr($include, $pos + 1);
			}

			$document->addScript($include, $options, $attribs);
		}
	}

	/**
	 * Set format related options.
	 *
	 * Updates the formatOptions array with all valid values in the passed array.
	 *
	 * @param   array  $options  Option key/value pairs.
	 *
	 * @return  void
	 *
	 * @see     HTMLHelper::$formatOptions
	 * @since   1.5
	 */
	public static function setFormatOptions($options)
	{
		foreach ($options as $key => $val)
		{
			if (isset(static::$formatOptions[$key]))
			{
				static::$formatOptions[$key] = $val;
			}
		}
	}

	/**
	 * Returns formatted date according to a given format and time zone.
	 *
	 * @param   string   $input      String in a format accepted by date(), defaults to "now".
	 * @param   string   $format     The date format specification string (see {@link PHP_MANUAL#date}).
	 * @param   mixed    $tz         Time zone to be used for the date.  Special cases: boolean true for user
	 *                               setting, boolean false for server setting.
	 * @param   boolean  $gregorian  True to use Gregorian calendar.
	 *
	 * @return  string    A date translated by the given format and time zone.
	 *
	 * @see     strftime
	 * @since   1.5
	 */
	public static function date($input = 'now', $format = null, $tz = true, $gregorian = false)
	{
		// UTC date converted to user time zone.
		if ($tz === true)
		{
			// Get a date object based on UTC.
			$date = Factory::getDate($input, 'UTC');

			// Set the correct time zone based on the user configuration.
			$date->setTimezone(Factory::getUser()->getTimezone());
		}
		// UTC date converted to server time zone.
		elseif ($tz === false)
		{
			// Get a date object based on UTC.
			$date = Factory::getDate($input, 'UTC');

			// Set the correct time zone based on the server configuration.
			$date->setTimezone(new \DateTimeZone(Factory::getConfig()->get('offset')));
		}
		// No date conversion.
		elseif ($tz === null)
		{
			$date = Factory::getDate($input);
		}
		// UTC date converted to given time zone.
		else
		{
			// Get a date object based on UTC.
			$date = Factory::getDate($input, 'UTC');

			// Set the correct time zone based on the server configuration.
			$date->setTimezone(new \DateTimeZone($tz));
		}

		// If no format is given use the default locale based format.
		if (!$format)
		{
			$format = \JText::_('DATE_FORMAT_LC1');
		}
		// $format is an existing language key
		elseif (Factory::getLanguage()->hasKey($format))
		{
			$format = \JText::_($format);
		}

		if ($gregorian)
		{
			return $date->format($format, true);
		}

		return $date->calendar($format, true);
	}

	/**
	 * Creates a tooltip with an image as button
	 *
	 * @param   string  $tooltip  The tip string.
	 * @param   mixed   $title    The title of the tooltip or an associative array with keys contained in
	 *                            {'title','image','text','href','alt'} and values corresponding to parameters of the same name.
	 * @param   string  $image    The image for the tip, if no text is provided.
	 * @param   string  $text     The text for the tip.
	 * @param   string  $href     A URL that will be used to create the link.
	 * @param   string  $alt      The alt attribute for img tag.
	 * @param   string  $class    CSS class for the tool tip.
	 *
	 * @return  string
	 *
	 * @since   1.5
	 */
	public static function tooltip($tooltip, $title = '', $image = 'tooltip.png', $text = '', $href = '', $alt = 'Tooltip', $class = 'hasTooltip')
	{
		if (is_array($title))
		{
			foreach (array('image', 'text', 'href', 'alt', 'class') as $param)
			{
				if (isset($title[$param]))
				{
					$$param = $title[$param];
				}
			}

			if (isset($title['title']))
			{
				$title = $title['title'];
			}
			else
			{
				$title = '';
			}
		}

		if (!$text)
		{
			$alt = htmlspecialchars($alt, ENT_COMPAT, 'UTF-8');
			$text = static::image($image, $alt, null, true);
		}

		if ($href)
		{
			$tip = '<a href="' . $href . '">' . $text . '</a>';
		}
		else
		{
			$tip = $text;
		}

		if ($class === 'hasTip')
		{
			// Still using MooTools tooltips!
			$tooltip = htmlspecialchars($tooltip, ENT_COMPAT, 'UTF-8');

			if ($title)
			{
				$title = htmlspecialchars($title, ENT_COMPAT, 'UTF-8');
				$tooltip = $title . '::' . $tooltip;
			}
		}
		else
		{
			$tooltip = self::tooltipText($title, $tooltip, 0);
		}

		return '<span class="' . $class . '" title="' . $tooltip . '">' . $tip . '</span>';
	}

	/**
	 * Converts a double colon separated string or 2 separate strings to a string ready for bootstrap tooltips
	 *
	 * @param   string   $title      The title of the tooltip (or combined '::' separated string).
	 * @param   string   $content    The content to tooltip.
	 * @param   boolean  $translate  If true will pass texts through JText.
	 * @param   boolean  $escape     If true will pass texts through htmlspecialchars.
	 *
	 * @return  string  The tooltip string
	 *
	 * @since   3.1.2
	 */
	public static function tooltipText($title = '', $content = '', $translate = true, $escape = true)
	{
		// Initialise return value.
		$result = '';

		// Don't process empty strings
		if ($content !== '' || $title !== '')
		{
			// Split title into title and content if the title contains '::' (old Mootools format).
			if ($content === '' && !(strpos($title, '::') === false))
			{
				list($title, $content) = explode('::', $title, 2);
			}

			// Pass texts through JText if required.
			if ($translate)
			{
				$title = \JText::_($title);
				$content = \JText::_($content);
			}

			// Use only the content if no title is given.
			if ($title === '')
			{
				$result = $content;
			}
			// Use only the title, if title and text are the same.
			elseif ($title === $content)
			{
				$result = '<strong>' . $title . '</strong>';
			}
			// Use a formatted string combining the title and content.
			elseif ($content !== '')
			{
				$result = '<strong>' . $title . '</strong><br />' . $content;
			}
			else
			{
				$result = $title;
			}

			// Escape everything, if required.
			if ($escape)
			{
				$result = htmlspecialchars($result);
			}
		}

		return $result;
	}

	/**
	 * Displays a calendar control field
	 *
	 * @param   string  $value    The date value
	 * @param   string  $name     The name of the text field
	 * @param   string  $id       The id of the text field
	 * @param   string  $format   The date format
	 * @param   mixed   $attribs  Additional HTML attributes
	 *                            The array can have the following keys:
	 *                            readonly      Sets the readonly parameter for the input tag
	 *                            disabled      Sets the disabled parameter for the input tag
	 *                            autofocus     Sets the autofocus parameter for the input tag
	 *                            autocomplete  Sets the autocomplete parameter for the input tag
	 *                            filter        Sets the filter for the input tag
	 *
	 * @return  string  HTML markup for a calendar field
	 *
	 * @since   1.5
	 *
	 */
	public static function calendar($value, $name, $id, $format = '%Y-%m-%d', $attribs = array())
	{
		$tag       = Factory::getLanguage()->getTag();
		$calendar  = Factory::getLanguage()->getCalendar();
		$direction = strtolower(Factory::getDocument()->getDirection());

		// Get the appropriate file for the current language date helper
		$helperPath = 'system/fields/calendar-locales/date/gregorian/date-helper.min.js';

		if (!empty($calendar) && is_dir(JPATH_ROOT . '/media/system/js/fields/calendar-locales/date/' . strtolower($calendar)))
		{
			$helperPath = 'system/fields/calendar-locales/date/' . strtolower($calendar) . '/date-helper.min.js';
		}

		// Get the appropriate locale file for the current language
		$localesPath = 'system/fields/calendar-locales/en.js';

		if (is_file(JPATH_ROOT . '/media/system/js/fields/calendar-locales/' . strtolower($tag) . '.js'))
		{
			$localesPath = 'system/fields/calendar-locales/' . strtolower($tag) . '.js';
		}
		elseif (is_file(JPATH_ROOT . '/media/system/js/fields/calendar-locales/' . $tag . '.js'))
		{
			$localesPath = 'system/fields/calendar-locales/' . $tag . '.js';
		}
		elseif (is_file(JPATH_ROOT . '/media/system/js/fields/calendar-locales/' . strtolower(substr($tag, 0, -3)) . '.js'))
		{
			$localesPath = 'system/fields/calendar-locales/' . strtolower(substr($tag, 0, -3)) . '.js';
		}

		$readonly     = isset($attribs['readonly']) && $attribs['readonly'] === 'readonly';
		$disabled     = isset($attribs['disabled']) && $attribs['disabled'] === 'disabled';
		$autocomplete = isset($attribs['autocomplete']) && $attribs['autocomplete'] === '';
		$autofocus    = isset($attribs['autofocus']) && $attribs['autofocus'] === '';
		$required     = isset($attribs['required']) && $attribs['required'] === '';
		$filter       = isset($attribs['filter']) && $attribs['filter'] === '';
		$todayBtn     = isset($attribs['todayBtn']) ? $attribs['todayBtn'] : true;
		$weekNumbers  = isset($attribs['weekNumbers']) ? $attribs['weekNumbers'] : true;
		$showTime     = isset($attribs['showTime']) ? $attribs['showTime'] : false;
		$fillTable    = isset($attribs['fillTable']) ? $attribs['fillTable'] : true;
		$timeFormat   = isset($attribs['timeFormat']) ? $attribs['timeFormat'] : 24;
		$singleHeader = isset($attribs['singleHeader']) ? $attribs['singleHeader'] : false;
		$hint         = isset($attribs['placeholder']) ? $attribs['placeholder'] : '';
		$class        = isset($attribs['class']) ? $attribs['class'] : '';
		$onchange     = isset($attribs['onChange']) ? $attribs['onChange'] : '';
		$minYear      = isset($attribs['minYear']) ? $attribs['minYear'] : null;
		$maxYear      = isset($attribs['maxYear']) ? $attribs['maxYear'] : null;

		$showTime     = ($showTime) ? "1" : "0";
		$todayBtn     = ($todayBtn) ? "1" : "0";
		$weekNumbers  = ($weekNumbers) ? "1" : "0";
		$fillTable    = ($fillTable) ? "1" : "0";
		$singleHeader = ($singleHeader) ? "1" : "0";

		// Format value when not nulldate ('0000-00-00 00:00:00'), otherwise blank it as it would result in 1970-01-01.
		if ($value && $value !== Factory::getDbo()->getNullDate() && strtotime($value) !== false)
		{
			$tz = date_default_timezone_get();
			date_default_timezone_set('UTC');
			$inputvalue = strftime($format, strtotime($value));
			date_default_timezone_set($tz);
		}
		else
		{
			$inputvalue = '';
		}

		$data = array(
			'id'           => $id,
			'name'         => $name,
			'class'        => $class,
			'value'        => $inputvalue,
			'format'       => $format,
			'filter'       => $filter,
			'required'     => $required,
			'readonly'     => $readonly,
			'disabled'     => $disabled,
			'hint'         => $hint,
			'autofocus'    => $autofocus,
			'autocomplete' => $autocomplete,
			'todaybutton'  => $todayBtn,
			'weeknumbers'  => $weekNumbers,
			'showtime'     => $showTime,
			'filltable'    => $fillTable,
			'timeformat'   => $timeFormat,
			'singleheader' => $singleHeader,
			'tag'          => $tag,
			'helperPath'   => $helperPath,
			'localesPath'  => $localesPath,
			'direction'    => $direction,
			'onchange'     => $onchange,
			'minYear'      => $minYear,
			'maxYear'      => $maxYear,
		);

		return LayoutHelper::render('joomla.form.field.calendar', $data, null, null);
	}

	/**
	 * Add a directory where HTMLHelper should search for helpers. You may
	 * either pass a string or an array of directories.
	 *
	 * @param   string  $path  A path to search.
	 *
	 * @return  array  An array with directory elements
	 *
	 * @since   1.5
	 */
	public static function addIncludePath($path = '')
	{
		// Loop through the path directories
		foreach ((array) $path as $dir)
		{
			if (!empty($dir) && !in_array($dir, static::$includePaths))
			{
				array_unshift(static::$includePaths, \JPath::clean($dir));
			}
		}

		return static::$includePaths;
	}

	/**
	 * Internal method to get a JavaScript object notation string from an array
	 *
	 * @param   array  $array  The array to convert to JavaScript object notation
	 *
	 * @return  string  JavaScript object notation representation of the array
	 *
	 * @since   3.0
	 * @deprecated  4.0 Use `json_encode()` or `Joomla\Registry\Registry::toString('json')` instead
	 */
	public static function getJSObject(array $array = array())
	{
		Log::add(
			__METHOD__ . " is deprecated. Use json_encode() or \\Joomla\\Registry\\Registry::toString('json') instead.",
			Log::WARNING,
			'deprecated'
		);

		$elements = array();

		foreach ($array as $k => $v)
		{
			// Don't encode either of these types
			if ($v === null || is_resource($v))
			{
				continue;
			}

			// Safely encode as a Javascript string
			$key = json_encode((string) $k);

			if (is_bool($v))
			{
				$elements[] = $key . ': ' . ($v ? 'true' : 'false');
			}
			elseif (is_numeric($v))
			{
				$elements[] = $key . ': ' . ($v + 0);
			}
			elseif (is_string($v))
			{
				if (strpos($v, '\\') === 0)
				{
					// Items such as functions and JSON objects are prefixed with \, strip the prefix and don't encode them
					$elements[] = $key . ': ' . substr($v, 1);
				}
				else
				{
					// The safest way to insert a string
					$elements[] = $key . ': ' . json_encode((string) $v);
				}
			}
			else
			{
				$elements[] = $key . ': ' . static::getJSObject(is_object($v) ? get_object_vars($v) : $v);
			}
		}

		return '{' . implode(',', $elements) . '}';
	}
}
