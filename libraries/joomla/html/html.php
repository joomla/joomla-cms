<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	HTML
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

/**
 * Utility class for all HTML drawing classes
 *
 * @static
 * @package 	Joomla.Framework
 * @subpackage	HTML
 * @since		1.5
 */
class JHTML
{
	/**
	 * Class loader method
	 *
	 * Additional arguments may be supplied and are passed to the sub-class.
	 * Additional include paths are also able to be specified for third-party use
	 *
	 * @param	string	The type of helper method to load
	 */
	function _( $type )
	{
		//Initialise variables
		$file = '';
		$func = $type;

		// Check to see if we need to load a helper file
		if(substr_count($type, '.'))
		{
			$parts = explode('.', $type);
			$file		= preg_replace( '#[^A-Z0-9_]#i', '', $parts[0] );
			$func		= preg_replace( '#[^A-Z0-9_]#i', '', $parts[1] );
		}

		$className	= 'JHTML'.ucfirst($file);

		if (!class_exists( $className ))
		{
			jimport('joomla.filesystem.path');
			if ($path = JPath::find(JHTML::addIncludePath(), strtolower($file).'.php'))
			{
				require_once $path;

				if (!class_exists( $className ))
				{
					JError::raiseWarning( 0, 'JHTML '. $className.'::' .$func. ' not found in file.' );
					return false;
				}
			}
			else
			{
				JError::raiseWarning( 0, 'JHTML ' . $file . ' not supported. File not found.' );
				return false;
			}
		}

		if (is_callable( array( $className, $func ) ))
		{
			$args = func_get_args();
			array_shift( $args );
			return call_user_func_array( array( $className, $func ), $args );
		}
		else
		{
			JError::raiseWarning( 0, $className.'::'.$func.' not supported.' );
			return false;
		}
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
	function link($url, $text, $attribs = null)
	{
		if (is_array( $attribs )) {
			$attribs = JArrayHelper::toString( $attribs );
		}

		return '<a href="'.$url.'" '.$attribs.'>'.$text.'</a>';
	}

	/**
	 * Write a <img></amg> element
	 *
	 * @access	public
	 * @param	string 	The relative URL to use for the src attribute
	 * @param	string	The target attribute to use
	 * @param	array	An associative array of attributes to add
	 * @since	1.5
	 */
	function image($url, $alt, $attribs = null)
	{
		global $mainframe;

		if (is_array($attribs)) {
			$attribs = JArrayHelper::toString( $attribs );
		}

		return '<img src="'.JURI::base().$url.'" alt="'.$alt.'" '.$attribs.' />';
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
	function iframe( $url, $name, $attribs = null, $noFrames = '' )
	{
		if (is_array( $attribs )) {
			$attribs = JArrayHelper::toString( $attribs );
		}

		return '<iframe src="'.$url.'" '.$attribs.' name="'.$name.'">'.$noFrames.'</iframe>';
	}

	/**
	 * Write a <script></script> element
	 *
	 * @access	public
	 * @param	string 	The name of the script file
	 * * @param	string 	The relative path of the script file
	 * @param	boolean If true, the mootools library will be loaded
	 * @since	1.5
	 */
	function script($filename, $path = 'media/system/js/', $mootools = true)
	{
		global $mainframe;

		// Include mootools framework
		if($mootools) {
			JHTML::_('behavior.mootools');
		}

		$base = $mainframe->isAdmin() ? $mainframe->getSiteURL() : JURI::base();

		$document = &JFactory::getDocument();
		$document->addScript( $base.$path.$filename.'.js' );
		return;
	}

	/**
	 * Write a <link rel="stylesheet" style="text/css" /> element
	 *
	 * @access	public
	 * @param	string 	The relative URL to use for the href attribute
	 * @since	1.5
	 */
	function stylesheet($filename, $path = '/media/system/css/', $attribs = array())
	{
		global $mainframe;
		$base = $mainframe->isAdmin() ? $mainframe->getSiteURL() : JURI::base();

		$document = &JFactory::getDocument();
		$document->addStylesheet( $base.$path.$filename.'.css', 'text/css', null, $attribs );
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
	function date($date, $format = null, $offset = NULL)
	{
		if ( ! $format ) {
			$format = JText::_('DATE_FORMAT_LC1');
		}

		jimport('joomla.utilities.date');

		if(is_null($offset))
		{
			$config =& JFactory::getConfig();
			$offset = $config->getValue('config.offset');
		}
		$instance = new JDate($date);
		$instance->setOffset($offset);

		return $instance->toFormat($format);
	}

	/**
	 * Creates a tooltip with an image as button
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @param	string
	 * @param	string
	 * @param	string
	 * @param	boolean
	 * @return	string
	 * @since	1.5
	 */
	function tooltip($tooltip, $title='', $image='tooltip.png', $text='', $href='', $link=1)
	{
		global $mainframe;

		$tooltip	= addslashes(htmlspecialchars($tooltip));
		$title		= addslashes(htmlspecialchars($title));

		$url = $mainframe->isAdmin() ? $mainframe->getSiteURL() : JURI::base();

		if ( !$text ) {
			$image 	= $url . 'includes/js/ThemeOffice/'. $image;
			$text 	= '<img src="'. $image .'" border="0" alt="'. JText::_( 'Tooltip' ) .'"/>';
		} else {
			$text 	= JText::_( $text, true );
		}

		if($title) {
			$title = $title.'::';
		}

		$style = 'style="text-decoration: none; color: #333;"';

		if ( $href ) {
			$href = JRoute::_( $href );
			$style = '';
		}
		if ( $link ) {
			$tip = '<span class="editlinktip hasTip" title="'.$title.$tooltip.'" '. $style .'><a href="'. $href .'">'. $text .'</a></span>';
		} else {
			$tip = '<span class="editlinktip hasTip" title="'.$title.$tooltip.'" '. $style .'>'. $text .'</span>';
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
	function calendar($value, $name, $id, $format = 'y-mm-dd', $attribs = null)
	{
		JHTML::_('behavior.calendar'); //load the calendar behavior

		if (is_array($attribs)) {
			$attribs = JArrayHelper::toString( $attribs );
		}

		return '<input type="text" name="'.$name.'" id="'.$id.'" value="'.htmlspecialchars($value).'" '.$attribs.' />'.
				 '<a href="#" onclick="return showCalendar(\''.$id.'\', \''.$format.'\');"><img class="calendar" src="images/blank.png" alt="calendar" /></a>';
	}

	/**
	 * Add a directory where JHTML should search for helpers. You may
	 * either pass a string or an array of directories.
	 *
	 * @access	public
	 * @param	string	A path to search.
	 * @return	array	An array with directory elements
	 * @since	1.5
	 */
	function addIncludePath( $path='' )
	{
		static $paths;

		if (!isset($paths)) {
			$paths = array( JPATH_LIBRARIES.DS.'joomla'.DS.'html'.DS.'html' );
		}

		// force path to array
		settype($path, 'array');

		// loop through the path directories
		foreach ($path as $dir)
		{
			if (!empty($dir) && !in_array($dir, $paths)) {
				array_unshift($paths, JPath::clean( $dir ));
			}
		}

		return $paths;
	}
}