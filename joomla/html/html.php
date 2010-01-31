<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	HTML
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

JHtml::addIncludePath(JPATH_LIBRARIES.DS.'joomla'.DS.'html'.DS.'html');

/**
 * Utility class for all HTML drawing classes
 *
 * @static
 * @package 	Joomla.Framework
 * @subpackage	HTML
 * @since		1.5
 */
abstract class JHtml
{
	/**
	 * Option values related to the generation of HTML output. Recognized
	 * options are:
     * <ul><li>fmtDepth, integer. The current indent depth.
     * </li><li>fmtEol, string. The end of line string, default is linefeed.
     * </li><li>fmtIndent, string. The string to use for indentation, default is
     * tab.
     * </ul>
	 *
	 * @var array
	 */
	static $formatOptions = array(
        'format.depth' => 0,
        'format.eol' => "\n",
        'format.indent' => "\t"
 );

	private static $includePaths = array();

	/**
	 * An array to hold method references
	 *
	 * @var array
	 */
	private static $registry = array();

	/**
	 * Class loader method
	 *
	 * Additional arguments may be supplied and are passed to the sub-class.
	 * Additional include paths are also able to be specified for third-party use
	 *
	 * @param	string	The name of helper method to load, (prefix).(class).function
	 *					prefix and class are optional and can be used to load custom
	 *					html helpers.
	 */
	public static function _($type)
	{
		$type = preg_replace('#[^A-Z0-9_\.]#i', '', $type);

		// Check to see if we need to load a helper file
		$parts = explode('.', $type);

		$prefix = (count($parts) == 3 ? array_shift($parts) : 'JHtml');
		$file 	= (count($parts) == 2 ? array_shift($parts) : '');
		$func 	= array_shift($parts);

		$key = strtolower($prefix.'.'.$file.'.'.$func);

		if (array_key_exists($key, self::$registry))
		{
			$function = self::$registry[$key];
			$args = func_get_args();
			// remove function name from arguments
			array_shift($args);
			return JHtml::call($function, $args);
		}

		$className = $prefix.ucfirst($file);

		if (!class_exists($className))
		{
			jimport('joomla.filesystem.path');
			if ($path = JPath::find(JHtml::$includePaths, strtolower($file).'.php'))
			{
				require_once $path;

				if (!class_exists($className))
				{
					JError::raiseError(500, $className.'::' .$func. ' not found in file.');
					return false;
				}
			}
			else
			{
				JError::raiseError(500, $prefix.$file . ' not supported. File not found.');
				return false;
			}
		}

		$toCall = array($className, $func);
		if (is_callable($toCall))
		{
			JHtml::register($key, $toCall);
			$args = func_get_args();
			// remove function name from arguments
			array_shift($args);
			return JHtml::call($toCall, $args);
		}
		else
		{
			JError::raiseError(500, $className.'::'.$func.' not supported.');
			return false;
		}
	}

	/**
	 * Registers a function to be called with a specific key
	 *
	 * @param	string	The name of the key
	 * @param	string	Function or method
	 */
	public static function register($key, $function)
	{
		$parts = explode('.', $key);

		$prefix = (count($parts) == 3 ? array_shift($parts) : 'JHtml');
		$file 	= (count($parts) == 2 ? array_shift($parts) : '');
		$func 	= array_shift($parts);

		$key = strtolower($prefix.'.'.$file.'.'.$func);

		if (is_callable($function))
		{
			self::$registry[$key] = $function;
			return true;
		}

		return false;
	}

	/**
	 * Removes a key for a method from registry.
	 *
	 * @param	string	The name of the key
	 */
	public static function unregister($key)
	{
		$key = strtolower($key);
		if (isset(self::$registry[$key])) {
			unset(self::$registry[$key]);
			return true;
		}

		return false;
	}

	/**
	 * Function caller method
	 *
	 * @param	string 	Function or method to call
	 * @param	array	Arguments to be passed to function
	 */
	private static function call($function, $args)
	{
		if (is_callable($function))
		{
			// PHP 5.3 workaround
			$temp	= array();
			foreach ($args AS &$arg) {
				$temp[] = &$arg;
			}
			return call_user_func_array($function, $temp);
		}
		else {
			JError::raiseError(500, 'Function not supported.');
			return false;
		}
	}

	public static function core($debug = null)
	{
		// If no debugging value is set, use the configuration setting
		if ($debug === null) {
			$debug = JFactory::getConfig()->getValue('config.debug');
		}

		// TODO NOTE: Here we are checking for Konqueror - If they fix their issue with compressed, we will need to update this
		$konkcheck		= strpos(strtolower($_SERVER['HTTP_USER_AGENT']), "konqueror");
		$uncompressed	= ($debug || $konkcheck) ? '-uncompressed' : '';

		$document = &JFactory::getDocument();
		$document->addScript(JURI::root(true).'/media/system/js/core'.$uncompressed.'.js');
	}

	/**
	 * Write a <a></a> element
	 *
	 * @access	public
	 * @param	string 	The relative URL to use for the href attribute
	 * @param	string	The target attribute to use
	 * @param	array	An associative array of attributes to add
	 * @since	1.5
	 */
	public static function link($url, $text, $attribs = null)
	{
		if (is_array($attribs)) {
			$attribs = JArrayHelper::toString($attribs);
		}

		return '<a href="'.$url.'" '.$attribs.'>'.$text.'</a>';
	}

	/**
	 * Write a <img></img> element
	 *
	 * @access	public
	 * @param	string 	The relative or absolute URL to use for the src attribute
	 * @param	string	The target attribute to use
	 * @param	array	An associative array of attributes to add
	 * @param	boolean	If set to true, it tries to find an override for the file in the template
	 * @since	1.5
	 */
	public static function image($url, $alt, $attribs = null, $relative = false, $path_only = false)
	{
		if (is_array($attribs)) {
			$attribs = JArrayHelper::toString($attribs);
		}

		if($relative)
		{
			$app = JFactory::getApplication();
			$cur_template = $app->getTemplate();
			if (file_exists(JPATH_THEMES .'/'. $cur_template .'/images/'. $url)) {
				$url = JURI::base(true).'/templates/'. $cur_template .'/images/'. $url;
			} else {
				$url = JURI::root(true).'/media/images/'.$url;
			}
			if($path_only)
			{
				return $url;
			}
		} elseif (strpos($url, 'http') !== 0) {
			$url = JURI::root(true).'/'.$url;
		}

		return '<img src="'.$url.'" alt="'.$alt.'" '.$attribs.' />';
	}

	/**
	 * Write a <iframe></iframe> element
	 *
	 * @access	public
	 * @param	string 	The relative URL to use for the src attribute
	 * @param	string	The target attribute to use
	 * @param	array	An associative array of attributes to add
	 * @param	string	The message to display if the iframe tag is not supported
	 * @since	1.5
	 */
	public static function iframe($url, $name, $attribs = null, $noFrames = '')
	{
		if (is_array($attribs)) {
			$attribs = JArrayHelper::toString($attribs);
		}

		return '<iframe src="'.$url.'" '.$attribs.' name="'.$name.'">'.$noFrames.'</iframe>';
	}

	/**
	 * Write a <script></script> element
	 *
	 * @access	public
	 * @param	string 	The name of the script file
	 * * @param	string 	The relative or absolute path of the script file
	 * @param	boolean If true, the mootools library will be loaded
	 * @since	1.5
	 */
	public static function script($filename, $path = 'media/system/js/', $framework = false)
	{
		JHtml::core();

		// Include mootools framework
		if ($framework) {
			JHtml::_('behavior.framework');
		}

		if (strpos($path, 'http') !== 0) {
			$path =  JURI::root(true).'/'.$path;
		};

		$document = &JFactory::getDocument();
		$document->addScript($path.$filename);
		return;
	}

    /**
     * Set format related options.
     *
     * Updates the formatOptions array with all valid values in the passed
     * array. See {@see JHtml::$formatOptions} for details.
     *
     * @param array Option key/value pairs.
     */
    public static function setFormatOptions($options)
	{
        foreach ($options as $key => $val) {
            if (isset(self::$formatOptions[$key])) {
                self::$formatOptions[$key] = $val;
            }
        }
    }

	/**
	 * Write a <link rel="stylesheet" style="text/css" /> element
	 *
	 * @access	public
	 * @param	string 	The relative URL to use for the href attribute
	 * @since	1.5
	 */
	public static function stylesheet($filename, $path = 'media/system/css/', $attribs = array())
	{
		if (strpos($path, 'http') !== 0) {
			$path = JURI::root(true).'/'.$path;
		};

		$document = &JFactory::getDocument();
		$document->addStylesheet($path.$filename, 'text/css', null, $attribs);
		return;
	}

	/**
	 * Returns formated date according to a given format and time zone.
	 *
	 * @param	string	String in a format accepted by strtotime(), defaults to "now".
	 * @param	string	format optional format for strftime
	 * @param	mixed	Time zone to be used for the date.  Special cases: boolean true for user
	 * 					setting, boolean false for server setting.
	 * @return	string	A date translated by the given format and time zone.
	 * @see		strftime
	 * @since	1.5
	 */
	public static function date($input = 'now', $format = null, $tz = true)
	{
		// Get some system objects.
		$config = JFactory::getConfig();
		$user	= JFactory::getUser();

		// UTC date converted to user time zone.
		if ($tz === true)
		{
			// Get a date object based on UTC.
			$date = JFactory::getDate($input, 'UTC');

			// Set the correct time zone based on the user configuration.
			$date->setOffset($user->getParam('timezone', $config->getValue('config.offset')));
		}
		// UTC date converted to server time zone.
		elseif ($tz === false)
		{
			// Get a date object based on UTC.
			$date = JFactory::getDate($input, 'UTC');

			// Set the correct time zone based on the server configuration.
			$date->setOffset($config->getValue('config.offset'));
		}
		// No date conversion.
		elseif ($tz === null)
		{
			$date = JFactory::getDate($input);
		}
		// UTC date converted to given time zone.
		else
		{
			// Get a date object based on UTC.
			$date = JFactory::getDate($input, 'UTC');

			// Set the correct time zone based on the server configuration.
			$date->setOffset($tz);
		}

		// If no format is given use the default locale based format.
		if (!$format) {
			$format = JText::_('DATE_FORMAT_LC1');
		}

		return $date->toFormat($format);
	}

	/**
	 * Creates a tooltip with an image as button
	 *
	 * @access	public
	 * @param	string	$tooltip The tip string
	 * @param	string	$title The title of the tooltip
	 * @param	string	$image The image for the tip, if no text is provided
	 * @param	string	$text The text for the tip
	 * @param	string	$href An URL that will be used to create the link
	 * @param	boolean depreciated
	 * @return	string
	 * @since	1.5
	 */
	public static function tooltip(
		$tooltip, $title = '', $image = 'tooltip.png', $text = '', $href = '', $link = 1
	)
	{
		$tooltip	= addslashes(htmlspecialchars($tooltip, ENT_COMPAT, 'UTF-8'));
		$title		= addslashes(htmlspecialchars($title, ENT_COMPAT, 'UTF-8'));

		if (!$text) {
			$image 	= JURI::root(true).'/includes/js/ThemeOffice/'. $image;
			$text 	= '<img src="'. $image .'" border="0" alt="'. JText::_('Tooltip') .'"/>';
		} else {
			$text 	= JText::_($text, true);
		}

		if ($title) {
			$title .= '::';
		}

		$style = 'style="text-decoration: none; color: #333;"';

		$tip = '<span class="editlinktip hasTip" title="' . $title . $tooltip . '" '
			. $style . '>';
		if ($href) {
			$href = JRoute::_($href);
			$style = '';
			$tip .= '<a href="' . $href . '">' . $text . '</a></span>';
		} else {
			$tip .= $text . '</span>';
		}

		return $tip;
	}

	/**
	 * Displays a calendar control field
	 *
	 * @param	string	The date value
	 * @param	string	The name of the text field
	 * @param	string	The id of the text field
	 * @param	string	The date format
	 * @param	array	Additional html attributes
	 */
	public static function calendar($value, $name, $id, $format = '%Y-%m-%d', $attribs = null)
	{
		static $done;

		if ($done === null) {
			$done = array();
		}

		// Load the calendar behavior
		JHtml::_('behavior.calendar');

		if (is_array($attribs)) {
			$attribs = JArrayHelper::toString($attribs);
		}

		// Only display the triggers once for each control.
		if (!in_array($id, $done))
		{
			$document = &JFactory::getDocument();
			$document->addScriptDeclaration('window.addEvent(\'domready\', function() {Calendar.setup({
	        inputField     :    "'.$id.'",     // id of the input field
	        ifFormat       :    "'.$format.'",      // format of the input field
	        button         :    "'.$id.'_img",  // trigger for the calendar (button ID)
	        align          :    "Tl",           // alignment (defaults to "Bl")
	        singleClick    :    true
	    });});');
			$done[] = $id;
		}

		return '<input type="text" name="'.$name.'" id="'.$id.'" value="'.htmlspecialchars($value, ENT_COMPAT, 'UTF-8').'" '.$attribs.' />'.
				 JHTML::_('image', 'system/calendar.png', JText::_('calendar'), array( 'class' => 'calendar', 'id' => $id.'_img'), true);
	}

	/**
	 * Add a directory where JHtml should search for helpers. You may
	 * either pass a string or an array of directories.
	 *
	 * @access	public
	 * @param	string	A path to search.
	 * @return	array	An array with directory elements
	 * @since	1.5
	 */
	public static function addIncludePath($path = '')
	{
		// force path to array
		settype($path, 'array');

		// loop through the path directories
		foreach ($path as $dir)
		{
			if (!empty($dir) && !in_array($dir, JHtml::$includePaths)) {
				array_unshift(JHtml::$includePaths, JPath::clean($dir));
			}
		}

		return JHtml::$includePaths;
	}

}
