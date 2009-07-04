<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	HTML
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
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
		//Initialise variables
		$prefix = 'JHtml';
		$file   = '';
		$func   = $type;

		// Check to see if we need to load a helper file
		$parts = explode('.', $type);

		switch (count($parts))
		{
			case 3 :
			{
				$prefix		= preg_replace('#[^A-Z0-9_]#i', '', $parts[0]);
				$file		= preg_replace('#[^A-Z0-9_]#i', '', $parts[1]);
				$func		= preg_replace('#[^A-Z0-9_]#i', '', $parts[2]);
			} break;

			case 2 :
			{
				$file		= preg_replace('#[^A-Z0-9_]#i', '', $parts[0]);
				$func		= preg_replace('#[^A-Z0-9_]#i', '', $parts[1]);
			} break;
		}

		$className	= $prefix.ucfirst($file);

		if (!class_exists($className))
		{
			jimport('joomla.filesystem.path');
			if ($path = JPath::find(JHtml::$includePaths, strtolower($file).'.php'))
			{
				require_once $path;

				if (!class_exists($className))
				{
					JError::raiseWarning(0, $className.'::' .$func. ' not found in file.');
					return false;
				}
			}
			else
			{
				JError::raiseWarning(0, $prefix.$file . ' not supported. File not found.');
				return false;
			}
		}

		if (is_callable(array($className, $func)))
		{
			$temp	= func_get_args();
			array_shift($temp);
			$args	= array();
			foreach ($temp AS &$arg) {
				$args[] = &$arg;
			}
			return call_user_func_array(array($className, $func), $args);
		}
		else
		{
			JError::raiseWarning(0, $className.'::'.$func.' not supported.');
			return false;
		}
	}

	function core($debug = null)
	{
		// If no debugging value is set, use the configuration setting
		if ($debug === null) {
			$debug = JFactory::getConfig()->getValue('config.debug');
		}

		// TODO NOTE: Here we are checking for Konqueror - If they fix thier issue with compressed, we will need to update this
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
	 * @since	1.5
	 */
	public static function image($url, $alt, $attribs = null)
	{
		if (is_array($attribs)) {
			$attribs = JArrayHelper::toString($attribs);
		}

		if (strpos($url, 'http') !== 0) {
			$url = JURI::root(true).'/'.$url;
		};

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
	 * Returns formated date according to current local and adds time offset
	 *
	 * @access	public
	 * @param	string	date in an US English date format
	 * @param	string	format optional format for strftime
	 * @returns	string	formated date
	 * @see		strftime
	 * @since	1.5
	 */
	public static function date($date, $format = null, $offset = null)
	{
		if (! $format) {
			$format = JText::_('DATE_FORMAT_LC1');
		}

		if (is_null($offset))
		{
			$config = &JFactory::getConfig();
			$offset = $config->getValue('config.offset');
		}
		$instance = &JFactory::getDate($date);
		$instance->setOffset($offset);

		return $instance->toFormat($format);
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
		$tooltip	= addslashes(htmlspecialchars($tooltip));
		$title		= addslashes(htmlspecialchars($title));

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
				 '<img class="calendar" src="'.JURI::root(true).'/templates/system/images/calendar.png" alt="calendar" id="'.$id.'_img" />';
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
