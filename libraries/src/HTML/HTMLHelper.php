<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\HTML;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Environment\Browser;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Utilities\ArrayHelper;

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
	 * @deprecated  5.0
	 */
	protected static $includePaths = array();

	/**
	 * An array to hold method references
	 *
	 * @var    callable[]
	 * @since  1.6
	 * @deprecated  5.0
	 */
	protected static $registry = array();

	/**
	 * The service registry for custom and overridden JHtml helpers
	 *
	 * @var    Registry
	 * @since  4.0.0
	 */
	protected static $serviceRegistry;

	/**
	 * Method to extract a key
	 *
	 * @param   string  $key  The name of helper method to load, (prefix).(class).function
	 *                        prefix and class are optional and can be used to load custom html helpers.
	 *
	 * @return  array  Contains lowercase key, prefix, file, function.
	 *
	 * @since       1.6
	 * @deprecated  5.0 Use the service registry instead
	 */
	protected static function extract($key)
	{
		$key = preg_replace('#[^A-Z0-9_\.]#i', '', $key);

		// Check to see whether we need to load a helper file
		$parts = explode('.', $key);

		if (\count($parts) === 3)
		{
			@trigger_error(
				'Support for a three segment service key is deprecated and will be removed in Joomla 5.0, use the service registry instead',
				E_USER_DEPRECATED
			);
		}

		$prefix = \count($parts) === 3 ? array_shift($parts) : 'JHtml';
		$file   = \count($parts) === 2 ? array_shift($parts) : '';
		$func   = array_shift($parts);

		return array(strtolower($prefix . '.' . $file . '.' . $func), $prefix, $file, $func);
	}

	/**
	 * Class loader method
	 *
	 * Additional arguments may be supplied and are passed to the sub-class.
	 * Additional include paths are also able to be specified for third-party use
	 *
	 * @param   string  $key         The name of helper method to load, (prefix).(class).function
	 *                               prefix and class are optional and can be used to load custom
	 *                               html helpers.
	 * @param   array   $methodArgs  The arguments to pass forward to the method being called
	 *
	 * @return  mixed  Result of HTMLHelper::call($function, $args)
	 *
	 * @since   1.5
	 * @throws  \InvalidArgumentException
	 */
	final public static function _(string $key, ...$methodArgs)
	{
		list($key, $prefix, $file, $func) = static::extract($key);

		if (\array_key_exists($key, static::$registry))
		{
			$function = static::$registry[$key];

			return static::call($function, $methodArgs);
		}

		/*
		 * Support fetching services from the registry if a custom class prefix was not given (a three segment key),
		 * the service comes from a class other than this one, and a service has been registered for the file.
		 */
		if ($prefix === 'JHtml' && $file !== '' && static::getServiceRegistry()->hasService($file))
		{
			$service = static::getServiceRegistry()->getService($file);

			$toCall = array($service, $func);

			if (!\is_callable($toCall))
			{
				throw new \InvalidArgumentException(sprintf('%s::%s not found.', $file, $func), 500);
			}

			static::register($key, $toCall);

			return static::call($toCall, $methodArgs);
		}

		$className = $prefix . ucfirst($file);

		if (!class_exists($className))
		{
			$path = Path::find(static::$includePaths, strtolower($file) . '.php');

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

		// If calling a method from this class, do not allow access to internal methods
		if ($className === __CLASS__)
		{
			if (!((new \ReflectionMethod($className, $func))->isPublic()))
			{
				throw new \InvalidArgumentException('Access to internal class methods is not allowed.');
			}
		}

		$toCall = array($className, $func);

		if (!\is_callable($toCall))
		{
			throw new \InvalidArgumentException(sprintf('%s::%s not found.', $className, $func), 500);
		}

		static::register($key, $toCall);

		return static::call($toCall, $methodArgs);
	}

	/**
	 * Registers a function to be called with a specific key
	 *
	 * @param   string    $key       The name of the key
	 * @param   callable  $function  Function or method
	 *
	 * @return  boolean  True if the function is callable
	 *
	 * @since       1.6
	 * @deprecated  5.0 Use the service registry instead
	 */
	public static function register($key, callable $function)
	{
		@trigger_error(
			'Support for registering functions is deprecated and will be removed in Joomla 5.0, use the service registry instead',
			E_USER_DEPRECATED
		);

		list($key) = static::extract($key);

		static::$registry[$key] = $function;

		return true;
	}

	/**
	 * Removes a key for a method from registry.
	 *
	 * @param   string  $key  The name of the key
	 *
	 * @return  boolean  True if a set key is unset
	 *
	 * @since       1.6
	 * @deprecated  5.0 Use the service registry instead
	 */
	public static function unregister($key)
	{
		@trigger_error(
			'Support for registering functions is deprecated and will be removed in Joomla 5.0, use the service registry instead',
			E_USER_DEPRECATED
		);

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
	 * Retrieves the service registry.
	 *
	 * @return  Registry
	 *
	 * @since   4.0.0
	 */
	public static function getServiceRegistry(): Registry
	{
		if (!static::$serviceRegistry)
		{
			static::$serviceRegistry = Factory::getContainer()->get(Registry::class);
		}

		return static::$serviceRegistry;
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
	protected static function call(callable $function, $args)
	{
		// PHP 5.3 workaround
		$temp = array();

		foreach ($args as &$arg)
		{
			$temp[] = &$arg;
		}

		return \call_user_func_array($function, $temp);
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
		if (\is_array($attribs))
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
		if (\is_array($attribs))
		{
			$attribs = ArrayHelper::toString($attribs);
		}

		return '<iframe src="' . $url . '" ' . $attribs . ' name="' . $name . '">' . $noFrames . '</iframe>';
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
	 * @see     Browser
	 * @since   1.6
	 */
	protected static function includeRelativeFiles($folder, $file, $relative, $detectBrowser, $detectDebug)
	{
		// Set debug flag
		$debugMode = false;

		// Detect debug mode
		if ($detectDebug && JDEBUG)
		{
			$debugMode = true;
		}

		// If http is present in filename
		if (strpos($file, 'http') === 0 || strpos($file, '//') === 0)
		{
			$includes = [$file];
		}
		else
		{
			// Extract extension and strip the file
			$strip = File::stripExt($file);
			$ext   = File::getExt($file);

			// Prepare array of files
			$includes = [];

			// Detect browser and compute potential files
			if ($detectBrowser)
			{
				$navigator = Browser::getInstance();
				$browser   = $navigator->getBrowser();
				$major     = $navigator->getMajor();
				$minor     = $navigator->getMinor();
				$minExt    = '';

				if (\strlen($strip) > 4 && preg_match('#\.min$#', $strip))
				{
					$minExt    = '.min';
					$strip = preg_replace('#\.min$#', '', $strip);
				}

				// Try to include files named filename.ext, filename_browser.ext, filename_browser_major.ext, filename_browser_major_minor.ext
				// where major and minor are the browser version names
				$potential = [
					$strip . $minExt,
					$strip . '_' . $browser . $minExt,
					$strip . '_' . $browser . '_' . $major . $minExt,
					$strip . '_' . $browser . '_' . $major . '_' . $minor . $minExt,
				];
			}
			else
			{
				$potential = [$strip];
			}

			// If relative search in template directory or media directory
			if ($relative)
			{
				$app        = Factory::getApplication();
				$template   = $app->getTemplate(true);
				$templaPath = JPATH_THEMES;

				if ($template->inheritable || !empty($template->parent))
				{
					$client     = $app->isClient('administrator') === true ? 'administrator' : 'site';
					$templaPath = JPATH_ROOT . "/media/templates/$client";
				}

				// For each potential files
				foreach ($potential as $strip)
				{
					$files = [];
					$files[] = $strip . '.' . $ext;

					/**
					 * Loop on 1 or 2 files and break on first found.
					 * Add the content of the MD5SUM file located in the same folder to url to ensure cache browser refresh
					 * This MD5SUM file must represent the signature of the folder content
					 */
					foreach ($files as $file)
					{
						if (!empty($template->parent))
						{
							$found = static::addFileToBuffer("$templaPath/$template->template/$folder/$file", $ext, $debugMode);

							if (empty($found))
							{
								$found = static::addFileToBuffer("$templaPath/$template->parent/$folder/$file", $ext, $debugMode);
							}
						}
						else
						{
							$found = static::addFileToBuffer("$templaPath/$template->template/$folder/$file", $ext, $debugMode);
						}

						if (!empty($found))
						{
							$includes[] = $found;

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
									$found = static::addFileToBuffer(JPATH_ROOT . "/media/$extension/$element/$folder/$file", $ext, $debugMode);

									if (!empty($found))
									{
										$includes[] = $found;

										break;
									}

									// Try to deal with classical file in a media subfolder called element
									$found = static::addFileToBuffer(JPATH_ROOT . "/media/$extension/$folder/$element/$file", $ext, $debugMode);

									if (!empty($found))
									{
										$includes[] = $found;

										break;
									}

									// Try to deal with system files in the template folder
									if (!empty($template->parent))
									{
										$found = static::addFileToBuffer("$templaPath/$template->template/$folder/system/$element/$file", $ext, $debugMode);

										if (!empty($found))
										{
											$includes[] = $found;

											break;
										}

										$found = static::addFileToBuffer("$templaPath/$template->parent/$folder/system/$element/$file", $ext, $debugMode);

										if (!empty($found))
										{
											$includes[] = $found;

											break;
										}
									}
									else
									{
										// Try to deal with system files in the media folder
										$found = static::addFileToBuffer(JPATH_ROOT . "/media/system/$folder/$element/$file", $ext, $debugMode);

										if (!empty($found))
										{
											$includes[] = $found;

											break;
										}
									}
								}
								else
								{
									// Try to deal with files in the extension's media folder
									$found = static::addFileToBuffer(JPATH_ROOT . "/media/$extension/$folder/$file", $ext, $debugMode);

									if (!empty($found))
									{
										$includes[] = $found;

										break;
									}

									// Try to deal with system files in the template folder
									if (!empty($template->parent))
									{
										$found = static::addFileToBuffer("$templaPath/$template->template/$folder/system/$file", $ext, $debugMode);

										if (!empty($found))
										{
											$includes[] = $found;

											break;
										}

										$found = static::addFileToBuffer("$templaPath/$template->parent/$folder/system/$file", $ext, $debugMode);

										if (!empty($found))
										{
											$includes[] = $found;

											break;
										}
									}
									else
									{
										// Try to deal with system files in the template folder
										$found = static::addFileToBuffer("$templaPath/$template->template/$folder/system/$file", $ext, $debugMode);

										if (!empty($found))
										{
											$includes[] = $found;

											break;
										}
									}

									// Try to deal with system files in the media folder
									$found = static::addFileToBuffer(JPATH_ROOT . "/media/system/$folder/$file", $ext, $debugMode);

									if (!empty($found))
									{
										$includes[] = $found;

										break;
									}
								}
							}
							else
							{
								// Try to deal with system files in the media folder
								$found = static::addFileToBuffer(JPATH_ROOT . "/media/system/$folder/$file", $ext, $debugMode);

								if (!empty($found))
								{
									$includes[] = $found;

									break;
								}
							}
						}
					}
				}
			}
			else
			{
				// If not relative and http is not present in filename
				foreach ($potential as $strip)
				{
					$files = [];

					$files[] = $strip . '.' . $ext;

					/**
					 * Loop on 1 or 2 files and break on first found.
					 * Add the content of the MD5SUM file located in the same folder to url to ensure cache browser refresh
					 * This MD5SUM file must represent the signature of the folder content
					 */
					foreach ($files as $file)
					{
						$path = JPATH_ROOT . "/$file";

						$found = static::addFileToBuffer($path, $ext, $debugMode);

						if (!empty($found))
						{
							$includes[] = $found;

							break;
						}
					}
				}
			}
		}

		return $includes;
	}

	/**
	 * Gets a URL, cleans the Joomla specific params and returns an object
	 *
	 * @param    string  $url  The relative or absolute URL to use for the src attribute.
	 *
	 * @return   object
	 * @example  {
	 *             url: 'string',
	 *             attributes: [
	 *               width:  integer,
	 *               height: integer,
	 *             ]
	 *           }
	 *
	 * @since    4.0.0
	 */
	public static function cleanImageURL($url)
	{
		$obj = new \stdClass;

		$obj->attributes = [
			'width'  => 0,
			'height' => 0,
		];

		if ($url === null)
		{
			$url = '';
		}

		if (!strpos($url, '?'))
		{
			$obj->url = $url;

			return $obj;
		}

		$mediaUri = new Uri($url);

		// Old image URL format
		if ($mediaUri->hasVar('joomla_image_height'))
		{
			$height = (int) $mediaUri->getVar('joomla_image_height');
			$width  = (int) $mediaUri->getVar('joomla_image_width');

			$mediaUri->delVar('joomla_image_height');
			$mediaUri->delVar('joomla_image_width');
		}
		else
		{
			// New Image URL format
			$fragmentUri = new Uri($mediaUri->getFragment());
			$width       = (int) $fragmentUri->getVar('width', 0);
			$height      = (int) $fragmentUri->getVar('height', 0);
		}

		if ($width > 0)
		{
			$obj->attributes['width'] = $width;
		}

		if ($height > 0)
		{
			$obj->attributes['height'] = $height;
		}

		$mediaUri->setFragment('');
		$obj->url = $mediaUri->toString();

		return $obj;
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
		// Ensure is an integer
		$returnPath = (int) $returnPath;

		// The path of the file
		$path = $file;

		// The arguments of the file path
		$arguments = '';

		// Get the arguments positions
		$pos1 = strpos($file, '?');
		$pos2 = strpos($file, '#');

		// Check if there are arguments
		if ($pos1 !== false || $pos2 !== false)
		{
			// Get the path only
			$path = substr($file, 0, min($pos1, $pos2));

			// Determine the arguments is mostly the part behind the #
			$arguments = str_replace($path, '', $file);
		}

		// Get the relative file name when requested
		if ($returnPath !== -1)
		{
			// Search for relative file names
			$includes = static::includeRelativeFiles('images', $path, $relative, false, false);

			// Grab the first found path and if none exists default to null
			$path = \count($includes) ? $includes[0] : null;
		}

		// Compile the file name
		$file = ($path === null ? null : $path . $arguments);

		// If only the file is required, return here
		if ($returnPath === 1)
		{
			return $file;
		}

		// Ensure we have a valid default for concatenating
		if ($attribs === null || $attribs === false)
		{
			$attribs = [];
		}

		// When it is a string, we need convert it to an array
		if (is_string($attribs))
		{
			$attributes = [];

			// Go through each argument
			foreach (explode(' ', $attribs) as $attribute)
			{
				// When an argument without a value, default to an empty string
				if (strpos($attribute, '=') === false)
				{
					$attributes[$attribute] = '';
					continue;
				}

				// Set the attribute
				list($key, $value) = explode('=', $attribute);
				$attributes[$key]  = trim($value, '"');
			}

			// Add the attributes from the string to the original attributes
			$attribs = $attributes;
		}

		// Fill the attributes with the file and alt text
		$attribs['src'] = $file;
		$attribs['alt'] = $alt;

		// Render the layout with the attributes
		return LayoutHelper::render('joomla.html.image', $attribs);
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
	 * @see   Browser
	 * @since 1.5
	 */
	public static function stylesheet($file, $options = array(), $attribs = array())
	{
		$options['relative']      = $options['relative'] ?? false;
		$options['pathOnly']      = $options['pathOnly'] ?? false;
		$options['detectBrowser'] = $options['detectBrowser'] ?? false;
		$options['detectDebug']   = $options['detectDebug'] ?? true;

		$includes = static::includeRelativeFiles('css', $file, $options['relative'], $options['detectBrowser'], $options['detectDebug']);

		// If only path is required
		if ($options['pathOnly'])
		{
			if (\count($includes) === 0)
			{
				return;
			}

			if (\count($includes) === 1)
			{
				return $includes[0];
			}

			return $includes;
		}

		// If inclusion is required
		$document = Factory::getApplication()->getDocument();

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
	 * @see   HTMLHelper::stylesheet()
	 * @since 1.5
	 */
	public static function script($file, $options = array(), $attribs = array())
	{
		$options['relative']      = $options['relative'] ?? false;
		$options['pathOnly']      = $options['pathOnly'] ?? false;
		$options['detectBrowser'] = $options['detectBrowser'] ?? false;
		$options['detectDebug']   = $options['detectDebug'] ?? true;

		$includes = static::includeRelativeFiles('js', $file, $options['relative'], $options['detectBrowser'], $options['detectDebug']);

		// If only path is required
		if ($options['pathOnly'])
		{
			if (\count($includes) === 0)
			{
				return;
			}

			if (\count($includes) === 1)
			{
				return $includes[0];
			}

			return $includes;
		}

		// If inclusion is required
		$document = Factory::getApplication()->getDocument();

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
		$app = Factory::getApplication();

		// UTC date converted to user time zone.
		if ($tz === true)
		{
			// Get a date object based on UTC.
			$date = Factory::getDate($input, 'UTC');

			// Set the correct time zone based on the user configuration.
			$date->setTimezone($app->getIdentity()->getTimezone());
		}
		// UTC date converted to server time zone.
		elseif ($tz === false)
		{
			// Get a date object based on UTC.
			$date = Factory::getDate($input, 'UTC');

			// Set the correct time zone based on the server configuration.
			$date->setTimezone(new \DateTimeZone($app->get('offset')));
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
			$format = Text::_('DATE_FORMAT_LC1');
		}
		// $format is an existing language key
		elseif (Factory::getLanguage()->hasKey($format))
		{
			$format = Text::_($format);
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
		if (\is_array($title))
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
	 * @param   boolean  $translate  If true will pass texts through Text.
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

			// Pass texts through Text if required.
			if ($translate)
			{
				$title = Text::_($title);
				$content = Text::_($content);
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
				$result = '<strong>' . $title . '</strong><br>' . $content;
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
		$app       = Factory::getApplication();
		$lang      = $app->getLanguage();
		$tag       = $lang->getTag();
		$calendar  = $lang->getCalendar();
		$direction = strtolower($app->getDocument()->getDirection());

		// Get the appropriate file for the current language date helper
		$helperPath = 'system/fields/calendar-locales/date/gregorian/date-helper.min.js';

		if ($calendar && is_dir(JPATH_ROOT . '/media/system/js/fields/calendar-locales/date/' . strtolower($calendar)))
		{
			$helperPath = 'system/fields/calendar-locales/date/' . strtolower($calendar) . '/date-helper.min.js';
		}

		$readonly     = isset($attribs['readonly']) && $attribs['readonly'] === 'readonly';
		$disabled     = isset($attribs['disabled']) && $attribs['disabled'] === 'disabled';
		$autocomplete = isset($attribs['autocomplete']) && $attribs['autocomplete'] === '';
		$autofocus    = isset($attribs['autofocus']) && $attribs['autofocus'] === '';
		$required     = isset($attribs['required']) && $attribs['required'] === '';
		$filter       = isset($attribs['filter']) && $attribs['filter'] === '';
		$todayBtn     = $attribs['todayBtn'] ?? true;
		$weekNumbers  = $attribs['weekNumbers'] ?? true;
		$showTime     = $attribs['showTime'] ?? false;
		$fillTable    = $attribs['fillTable'] ?? true;
		$timeFormat   = $attribs['timeFormat'] ?? 24;
		$singleHeader = $attribs['singleHeader'] ?? false;
		$hint         = $attribs['placeholder'] ?? '';
		$class        = $attribs['class'] ?? '';
		$onchange     = $attribs['onChange'] ?? '';
		$minYear      = $attribs['minYear'] ?? null;
		$maxYear      = $attribs['maxYear'] ?? null;

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
			'id'             => $id,
			'name'           => $name,
			'class'          => $class,
			'value'          => $inputvalue,
			'format'         => $format,
			'filter'         => $filter,
			'required'       => $required,
			'readonly'       => $readonly,
			'disabled'       => $disabled,
			'hint'           => $hint,
			'autofocus'      => $autofocus,
			'autocomplete'   => $autocomplete,
			'todaybutton'    => $todayBtn,
			'weeknumbers'    => $weekNumbers,
			'showtime'       => $showTime,
			'filltable'      => $fillTable,
			'timeformat'     => $timeFormat,
			'singleheader'   => $singleHeader,
			'tag'            => $tag,
			'helperPath'     => $helperPath,
			'direction'      => $direction,
			'onchange'       => $onchange,
			'minYear'        => $minYear,
			'maxYear'        => $maxYear,
			'dataAttribute'  => '',
			'dataAttributes' => '',
			'calendar'       => $calendar,
			'firstday'       => $lang->getFirstDay(),
			'weekend'        => explode(',', $lang->getWeekEnd()),
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
	 * @since       1.5
	 * @deprecated  5.0 Use the service registry instead
	 */
	public static function addIncludePath($path = '')
	{
		@trigger_error(
			'Support for registering lookup paths is deprecated and will be removed in Joomla 5.0, use the service registry instead',
			E_USER_DEPRECATED
		);

		// Loop through the path directories
		foreach ((array) $path as $dir)
		{
			if (!empty($dir) && !\in_array($dir, static::$includePaths))
			{
				array_unshift(static::$includePaths, Path::clean($dir));
			}
		}

		return static::$includePaths;
	}

	/**
	 * Method that searches if file exists in given path and returns the relative path. If a minified version exists it will be preferred.
	 *
	 * @param   string   $path       The actual path of the file
	 * @param   string   $ext        The extension of the file
	 * @param   boolean  $debugMode  Signifies if debug is enabled
	 *
	 * @return  string  The relative path of the file
	 *
	 * @since   4.0.0
	 */
	protected static function addFileToBuffer($path = '', $ext = '', $debugMode = false)
	{
		$position = strrpos($path, '.min.');

		// We are handling a name.min.ext file:
		if ($position !== false)
		{
			$minifiedPath    = $path;
			$nonMinifiedPath = substr_replace($path, '', $position, 4);

			if ($debugMode)
			{
				return self::checkFileOrder($minifiedPath, $nonMinifiedPath);
			}

			return self::checkFileOrder($nonMinifiedPath, $minifiedPath);
		}

		$minifiedPath = pathinfo($path, PATHINFO_DIRNAME) . '/' . pathinfo($path, PATHINFO_FILENAME) . '.min.' . $ext;

		if ($debugMode)
		{
			return self::checkFileOrder($minifiedPath, $path);
		}

		return self::checkFileOrder($path, $minifiedPath);
	}

	/**
	 * Method that takes a file path and converts it to a relative path
	 *
	 * @param   string  $path  The actual path of the file
	 *
	 * @return  string  The relative path of the file
	 *
	 * @since   4.0.0
	 */
	protected static function convertToRelativePath($path)
	{
		$relativeFilePath = Uri::root(true) . str_replace(JPATH_ROOT, '', $path);

		// On windows devices we need to replace "\" with "/" otherwise some browsers will not load the asset
		return str_replace(DIRECTORY_SEPARATOR, '/', $relativeFilePath);
	}

	/**
	 * Method that takes two paths and checks if the files exist with different order
	 *
	 * @param   string  $first   the path of the minified file
	 * @param   string  $second  the path of the non minified file
	 *
	 * @return  string
	 *
	 * @since  4.0.0
	 */
	private static function checkFileOrder($first, $second)
	{
		if (is_file($second))
		{
			return static::convertToRelativePath($second);
		}

		if (is_file($first))
		{
			return static::convertToRelativePath($first);
		}

		return '';
	}
}
