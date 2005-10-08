<?php
/*************************************************************************************
 * geshi.php
 * ---------
 * Author: Nigel McNie (oracle.shinoda@gmail.com)
 * Copyright: (c) 2004 Nigel McNie
 * Release Version: 1.0.4
 * CVS Revision Version: $Revision$
 * Date Started: 2004/05/20
 * Last Modified: $Date: 2005-09-12 07:24:33 -0500 (Mon, 12 Sep 2005) $
 *
 * The GeSHi class for Generic Syntax Highlighting. Please refer to the documentation
 * at http://qbnz.com/highlighter/documentation.php for more information about how to
 * use this class.
 *
 * For changes, release notes, TODOs etc, see the relevant files in the docs/ directory
 *
 *************************************************************************************
 *
 *	 This file is part of GeSHi.
 *
 *   GeSHi is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   GeSHi is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with GeSHi; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 ************************************************************************************/


//
// GeSHi Constants
// You should use these constant names in your programs instead of
// their values - you never know when a value may change in a future
// version
//

// For the future (though this may never be realised)
define('GESHI_OUTPUT_HTML', 0);

// Shouldn't be used by your program
define('GESHI_COMMENTS', 0);

// Error detection - use these to analyse faults
define('GESHI_ERROR_NO_INPUT', 1);
define('GESHI_ERROR_NO_SUCH_LANG', 2);
// Human error messages - added in 1.0.2
$_GESHI_ERRORS = array(
	GESHI_ERROR_NO_INPUT => 'No source code inputted',
	GESHI_ERROR_NO_SUCH_LANG => 'GeSHi could not find the language {LANGUAGE} (using path {PATH})'
);

// Line numbers - use with enable_line_numbers()
define('GESHI_NO_LINE_NUMBERS', 0);
define('GESHI_NORMAL_LINE_NUMBERS', 1);
define('GESHI_FANCY_LINE_NUMBERS', 2);

// Strict mode - shouldn't be used by your scripts
define('GESHI_NEVER', 0);
define('GESHI_MAYBE', 1);
define('GESHI_ALWAYS', 2);

// Container HTML type - use these (added in 1.0.1)
define('GESHI_HEADER_DIV', 1);
define('GESHI_HEADER_PRE', 2);

// Capatalisation constants - use these (added in 1.0.1)
define('GESHI_CAPS_NO_CHANGE', 0);
define('GESHI_CAPS_UPPER', 1);
define('GESHI_CAPS_LOWER', 2);

// Link style constants - use these (added in 1.0.2)
define('GESHI_LINK', 0);
define('GESHI_HOVER', 1);
define('GESHI_ACTIVE', 2);
define('GESHI_VISITED', 3);

// Important string starter/finisher - use these (added in 1.0.2).
// Note that if you change these, they should be as-is: i.e., don't
// write them as if they had been run through htmlentities()
define('GESHI_START_IMPORTANT', '<BEGIN GeSHi>');
define('GESHI_END_IMPORTANT', '<END GeSHi>');

// Advanced regexp handling - don't use these (added in 1.0.2)
define('GESHI_SEARCH', 0);
define('GESHI_REPLACE', 1);
define('GESHI_MODIFIERS', 2);
define('GESHI_BEFORE', 3);
define('GESHI_AFTER', 4);

// Begin Class GeSHi
class GeSHi
{
	//
	// Data Fields
	//

	// Basic fields
	var $source = '';					 // The source code to highlight
	var $language = '';				   // The language to use when highlighting
	var $language_data = array();		 // The data for the language used
	var $language_path = 'geshi/';		// The path to the language files
	var $error = false;				   // The error message associated with an error
	var $strict_mode = false;			 // Whether highlighting is strict or not
	var $use_classes = false;			 // Whether to use classes
	var $header_type = GESHI_HEADER_PRE;  // The type of header to use
	var $lexic_permissions = array();	 // Array of permissions for which lexics should be highlighted
	// Added in 1.0.2 basic fields
	var $time = 0;						// The time it took to parse the code
	var $header_content = '';			 // The content of the header block
	var $footer_content = '';			 // The content of the footer block
	var $header_content_style = '';	   // The style of the header block
	var $footer_content_style = '';	   // The style of the footer block
	var $link_styles = array();		   // The styles for hyperlinks in the code
	var $enable_important_blocks = true;  // Whether important blocks should be recognised or not
	var $important_styles = 'font-weight: bold; color: red;'; // Styles for important parts of the code
	var $add_ids = false;				 // Whether css IDs should be added to the code
	var $highlight_extra_lines = array(); // Lines that should be highlighted extra
	var $highlight_extra_lines_style = 'color: #cc0; background-color: #ffc;';// Styles of extra-highlighted lines
	var $line_numbers_start = 1;		  // Number at which line numbers should start at

	// Style fields
	var $overall_style = '';			  // The overall style for this code block
	// The style for the actual code
	var $code_style = 'font-family: \'Courier New\', Courier, monospace; font-weight: normal;';
	var $overall_class = '';			  // The overall class for this code block
	var $overall_id = '';				 // The overall id for this code block
	// Line number styles
	var $line_style1 = 'font-family: \'Courier New\', Courier, monospace; color: black; font-weight: normal; font-style: normal;';
	var $line_style2 = 'font-weight: bold;';
	var $line_numbers = GESHI_NO_LINE_NUMBERS; // Flag for how line numbers are displayed
	var $line_nth_row = 0;				// The "nth" value for fancy line highlighting

	// Misc
	var $tab_width = 8;				   // A value for the size of tab stops.
	var $max_tabs = 20;				   // Maximum number of spaces per tab
	var $min_tabs = 0;					// Minimum  "   "	"	"	"
	var $link_target = '';				// default target for keyword links
	var $encoding = '';				   // The encoding to use for htmlentities() calls

	// Deprecated/unused
	var $output_format = GESHI_OUTPUT_HTML;


	/**
	 * constructor: GeSHi
	 * ------------------
	 * Creates a new GeSHi object, with source and language
	 */
	function GeSHi ($source, $language, $path = 'geshi/')
	{
		$this->source = $source;
		// Security, just in case :)
		$language = preg_replace('#[^a-zA-Z0-9\-\_]#', '', $language);
		$this->language = strtolower($language);
		$this->language_path = ( substr($path, strlen($path) - 1, 1) == '/' ) ? $path : $path . '/';
		$this->load_language();
	}


	//
	// Error methods
	//

	/**
	 * method: error
	 * -------------
	 * Returns an error message associated with the last GeSHi operation,
	 * or false if no error has occured
	 */
	function error()
	{
		global $_GESHI_ERRORS;
		if ( $this->error != 0 )
		{
			$msg = $_GESHI_ERRORS[$this->error];
			$debug_tpl_vars = array(
				'{LANGUAGE}' => $this->language,
				'{PATH}' => $this->language_path
			);
			foreach ( $debug_tpl_vars as $tpl => $var )
			{
				$msg = str_replace($tpl, $var, $msg);
			}
			return "<br /><strong>GeSHi Error:</strong> $msg (code $this->error)<br />";
		}
		return false;
	}


	//
	// Getters
	//

	/**
	 * get_language_name()
	 * ---------------
	 * Gets a human-readable language name (thanks to Simon Patterson
	 * for the idea :))
	 */
	function get_language_name()
	{
		if ( $this->error == GESHI_ERROR_NO_SUCH_LANG )
		{
			return $this->language_data['LANG_NAME'] . ' (Unknown Language)';
		}
		return $this->language_data['LANG_NAME'];
	}


	//
	// Setters
	//

	/**
	 * method: set_source
	 * ------------------
	 * Sets the source code for this object
	 */
	function set_source ( $source )
	{
		$this->source = $source;
	}


	/**
	 * method: set_language
	 * --------------------
	 * Sets the language for this object
	 */
	function set_language ( $language )
	{
		$language = preg_replace('#[^a-zA-Z0-9\-_]#', '', $language);
		$this->language = strtolower($language);
		// Load the language for parsing
		$this->load_language();
	}


	/**
	 * method: set_language_path
	 * -------------------------
	 * Sets the path to the directory containing the language files. NOTE
	 * that this path is relative to the directory of the script that included
	 * geshi.php, NOT geshi.php itself.
	 */
	function set_language_path ( $path )
	{
		$this->language_path = ( substr($path, strlen($path) - 1, 1) == '/' ) ? $path : $path . '/';
	}


	/**
	 * method: set_header_type
	 * -----------------------
	 * Sets the type of header to be used. If GESHI_HEADER_DIV is used,
	 * the code is surrounded in a <div>. This means more source code but
	 * more control over tab width and line-wrapping. GESHI_HEADER_PRE
	 * means that a <pre> is used - less source, but less control. Default
	 * is GESHI_HEADER_PRE
	 */
	function set_header_type ( $type )
	{
		$this->header_type = $type;
	}


	/**
	 * method: set_overall_style
	 * -------------------------
	 * Sets the styles for the code that will be outputted
	 * when this object is parsed. The style should be a
	 * string of valid stylesheet declarations
	 */
	function set_overall_style ( $style, $preserve_defaults = false )
	{
		if ( $preserve_defaults )
		{
			$this->overall_style .= $style;
		}
		else
		{
			$this->overall_style = $style;
		}
	}


	/**
	 * method: set_overall_class
	 * -------------------------
	 * Sets the overall classname for this block of code. This
	 * class can then be used in a stylesheet to style this object's
	 * output
	 */
	function set_overall_class ( $class )
	{
		$this->overall_class = $class;
	}


	/**
	 * method: set_overall_id
	 * ----------------------
	 * Sets the overall id for this block of code. This id can then
	 * be used in a stylesheet to style this object's output
	 */
	function set_overall_id ( $id )
	{
		$this->overall_id = $id;
	}


	/**
	 * method: enable_classes
	 * ----------------------
	 * Sets whether CSS classes should be used to highlight the source. Default
	 * is off, calling this method with no arguments will turn it on
	 */
	function enable_classes ( $flag = true )
	{
		$this->use_classes = ( $flag ) ? true : false;
	}


	/**
	 * method: set_code_style
	 * ----------------------
	 * Sets the style for the actual code. This should be a string
	 * containing valid stylesheet declarations. If $preserve_defaults is
	 * true, then styles are merged with the default styles, with the
	 * user defined styles having priority
	 *
	 * NOTE: Use this method to override any style changes you made to
	 * the line numbers if you are using line numbers, else the line of
	 * code will have the same style as the line number! Consult the
	 * GeSHi documentation for more information about this.
	 */
	function set_code_style ( $style, $preserve_defaults )
	{
		if ( $preserve_defaults )
		{
			$this->code_style .= $style;
		}
		else
		{
			$this->code_style = $style;
		}
	}


	/**
	 * method: set_line_style
	 * ----------------------
	 * Sets the styles for the line numbers. This should be a string
	 * containing valid stylesheet declarations. If $preserve_defaults is
	 * true, then styles are merged with the default styles, with the
	 * user defined styles having priority
	 */
	function set_line_style ( $style1, $style2 = '', $preserve_defaults = false )
	{
		if ( is_bool($style2) )
		{
			$preserve_defaults = $style2;
			$style2 = '';
		}
		if ( $preserve_defaults )
		{
			$this->line_style1 .= $style1;
			$this->line_style2 .= $style2;
		}
		else
		{
			$this->line_style1 = $style1;
			$this->line_style2 = $style2;
		}
	}


	/**
	 * method: enable_line_numbers
	 * ---------------------------
	 * Sets whether line numbers should be displayed. GESHI_NO_LINE_NUMBERS = not displayed,
	 * GESHI_NORMAL_LINE_NUMBERS = displayed, GESHI_FANCY_LINE_NUMBERS = every nth line a
	 * different class. Default is for no line numbers to be used
	 */
	function enable_line_numbers ( $flag, $nth_row = 5 )
	{
		$this->line_numbers = $flag;
		$this->line_nth_row = $nth_row;
	}


	/**
	 * method: set_keyword_group_style
	 * -------------------------------
	 * Sets the style for a keyword group. If $preserve_defaults is
	 * true, then styles are merged with the default styles, with the
	 * user defined styles having priority
	 */
	function set_keyword_group_style ( $key, $style, $preserve_defaults = false )
	{
		if ( $preserve_defaults )
		{
			$this->language_data['STYLES']['KEYWORDS'][$key] .= $style;
		}
		else
		{
			$this->language_data['STYLES']['KEYWORDS'][$key] = $style;
		}
	}


	/**
	 * method: set_keyword_group_highlighting
	 * --------------------------------------
	 * Turns highlighting on/off for a keyword group
	 */
	function set_keyword_group_highlighting ( $key, $flag = true )
	{
		$this->lexic_permissions['KEYWORDS'][$key] = ( $flag ) ? true : false;
	}


	/**
	 * method: set_comments_style
	 * --------------------------
	 * Sets the styles for comment groups.  If $preserve_defaults is
	 * true, then styles are merged with the default styles, with the
	 * user defined styles having priority
	 */
	function set_comments_style ( $key, $style, $preserve_defaults = false )
	{
		if ( $preserve_defaults )
		{
			$this->language_data['STYLES']['COMMENTS'][$key] .= $style;
		}
		else
		{
			$this->language_data['STYLES']['COMMENTS'][$key] = $style;
		}
	}


	/**
	 * method: set_comments_highlighting
	 * ---------------------------------
	 * Turns highlighting on/off for comment groups
	 */
	function set_comments_highlighting ( $key, $flag = true )
	{
		$this->lexic_permissions['COMMENTS'][$key] = ( $flag ) ? true : false;
	}


	/**
	 * method: set_escape_characters_style
	 * -----------------------------------
	 * Sets the styles for escaped characters. If $preserve_defaults is
	 * true, then styles are merged with the default styles, with the
	 * user defined styles having priority
	 */
	function set_escape_characters_style ( $style, $preserve_defaults = false )
	{
		if ( $preserve_defaults )
		{
			$this->language_data['STYLES']['ESCAPE_CHAR'][0] .= $style;
		}
		else
		{
			$this->language_data['STYLES']['ESCAPE_CHAR'][0] = $style;
		}
	}


	/**
	 * method: set_escape_characters_highlighting
	 * ------------------------------------------
	 * Turns highlighting on/off for escaped characters
	 */
	function set_escape_characters_highlighting ( $flag = true )
	{
		$this->lexic_permissions['ESCAPE_CHAR'] = ( $flag ) ? true : false;
	}


	/**
	 * method: set_brackets_style
	 * --------------------------
	 * Sets the styles for brackets. If $preserve_defaults is
	 * true, then styles are merged with the default styles, with the
	 * user defined styles having priority
	 *
	 * This method is DEPRECATED: use set_symbols_style instead.
	 * This method will be removed in 1.2.X
	 */
	function set_brackets_style ( $style, $preserve_defaults = false )
	{
		if ( $preserve_defaults )
		{
			$this->language_data['STYLES']['BRACKETS'][0] .= $style;
		}
		else
		{
			$this->language_data['STYLES']['BRACKETS'][0] = $style;
		}
	}


	/**
	 * method: set_brackets_highlighting
	 * ---------------------------------
	 * Turns highlighting on/off for brackets
	 *
	 * This method is DEPRECATED: use set_symbols_highlighting instead.
	 * This method will be remove in 1.2.X
	 */
	function set_brackets_highlighting ( $flag )
	{
		$this->lexic_permissions['BRACKETS'] = ( $flag ) ? true : false;
	}


	/**
	 * method: set_symbols_style
	 * --------------------------
	 * Sets the styles for symbols. If $preserve_defaults is
	 * true, then styles are merged with the default styles, with the
	 * user defined styles having priority
	 */
	function set_symbols_style ( $style, $preserve_defaults = false )
	{
		if ( $preserve_defaults )
		{
			$this->language_data['STYLES']['SYMBOLS'][0] .= $style;
		}
		else
		{
			$this->language_data['STYLES']['SYMBOLS'][0] = $style;
		}
		// For backward compatibility
		$this->set_brackets_style ( $style, $preserve_defaults );
	}


	/**
	 * method: set_symbols_highlighting
	 * ---------------------------------
	 * Turns highlighting on/off for symbols
	 */
	function set_symbols_highlighting ( $flag )
	{
		$this->lexic_permissions['SYMBOLS'] = ( $flag ) ? true : false;
		// For backward compatibility
		$this->set_brackets_highlighting ( $flag );
	}


	/**
	 * method: set_strings_style
	 * -------------------------
	 * Sets the styles for strings. If $preserve_defaults is
	 * true, then styles are merged with the default styles, with the
	 * user defined styles having priority
	 */
	function set_strings_style ( $style, $preserve_defaults = false )
	{
		if ( $preserve_defaults )
		{
			$this->language_data['STYLES']['STRINGS'][0] .= $style;
		}
		else
		{
			$this->language_data['STYLES']['STRINGS'][0] = $style;
		}
	}


	/**
	 * method: set_strings_highlighting
	 * --------------------------------
	 * Turns highlighting on/off for strings
	 */
	function set_strings_highlighting ( $flag )
	{
		$this->lexic_permissions['STRINGS'] = ( $flag ) ? true : false;
	}


	/**
	 * method: set_numbers_style
	 * -------------------------
	 * Sets the styles for numbers. If $preserve_defaults is
	 * true, then styles are merged with the default styles, with the
	 * user defined styles having priority
	 */
	function set_numbers_style ( $style, $preserve_defaults = false )
	{
		if ( $preserve_defaults )
		{
			$this->language_data['STYLES']['NUMBERS'][0] .= $style;
		}
		else
		{
			$this->language_data['STYLES']['NUMBERS'][0] = $style;
		}
	}


	/**
	 * method: set_numbers_highlighting
	 * --------------------------------
	 * Turns highlighting on/off for numbers
	 */
	function set_numbers_highlighting ( $flag )
	{
		$this->lexic_permissions['NUMBERS'] = ( $flag ) ? true : false;
	}


	/**
	 * method: set_methods_style
	 * -------------------------
	 * Sets the styles for methods. $key is a number that references the
	 * appropriate "object splitter" - see the language file for the language
	 * you are highlighting to get this number. If $preserve_defaults is
	 * true, then styles are merged with the default styles, with the
	 * user defined styles having priority
	 */
	function set_methods_style ( $key, $style, $preserve_defaults = false )
	{
		if ( $preserve_defaults )
		{
			$this->language_data['STYLES']['METHODS'][$key] .= $style;
		}
		else
		{
			$this->language_data['STYLES']['METHODS'][$key] = $style;
		}
	}


	/**
	 * method: set_methods_highlighting
	 * --------------------------------
	 * Turns highlighting on/off for methods
	 */
	function set_methods_highlighting ( $flag )
	{
		$this->lexic_permissions['METHODS'] = ( $flag ) ? true : false;
	}


	/**
	 * method: set_regexps_style
	 * -------------------------
	 * Sets the styles for regexps. If $preserve_defaults is
	 * true, then styles are merged with the default styles, with the
	 * user defined styles having priority
	 */
	function set_regexps_style ( $key, $style, $preserve_defaults = false )
	{
		if ( $preserve_defaults )
		{
			$this->language_data['STYLES']['REGEXPS'][$key] .= $style;
		}
		else
		{
			$this->language_data['STYLES']['REGEXPS'][$key] = $style;
		}
	}


	/**
	 * method: set_regexps_highlighting
	 * --------------------------------
	 * Turns highlighting on/off for regexps
	 */
	function set_regexps_highlighting ( $key, $flag )
	{
		$this->lexic_permissions['REGEXPS'][$key] = ( $flag ) ? true : false;
	}


	/**
	 * method: set_case_sensitivity
	 * ----------------------------
	 * Sets whether a set of keywords are checked for in a case sensitive manner
	 */
	function set_case_sensitivity ( $key, $case )
	{
		$this->language_data['CASE_SENSITIVE'][$key] = ( $case ) ? true : false;
	}


	/**
	 * method: set_case_keywords
	 * -------------------------
	 * Sets the case that keywords should use when found. Use the constants:
	 *   GESHI_CAPS_NO_CHANGE: leave keywords as-is
	 *   GESHI_CAPS_UPPER: convert all keywords to uppercase where found
	 *   GESHI_CAPS_LOWER: convert all keywords to lowercase where found
	 * Method added in 1.0.1
	 */
	function set_case_keywords ( $case )
	{
		$this->language_data['CASE_KEYWORDS'] = $case;
	}


	/**
	 * method: set_tab_width
	 * ---------------------
	 * Sets how many spaces a tab is substituted for
	 * This method will probably be re-engineered later to allow customisability
	 * in the maximum and minimum number of tabs without mutulating data fields.
	 */
	function set_tab_width ( $width )
	{
		if ( $width > $this->max_tabs ) $width = $this->max_tabs;
		if ( $width < $this->min_tabs ) $width = $this->min_tabs;
		$this->tab_width = $width;
	}


	/**
	 * method: enable_strict_mode
	 * --------------------------
	 * Enables/disables strict highlighting. Default is off, calling this
	 * method without parameters will turn it on. See documentation
	 * for more details on strict mode and where to use it
	 */
	function enable_strict_mode ( $mode = true )
	{
		$this->strict_mode = ( $mode ) ? true : false;
		// Turn on strict mode no matter what if language should always
		// be in strict mode
		if ( $this->language_data['STRICT_MODE_APPLIES'] == GESHI_ALWAYS )
		{
			$this->strict_mode = true;
		}
		// Turn off strict mode no matter what if language should never
		// be in strict mode
		elseif ( $this->language_data['STRICT_MODE_APPLIES'] == GESHI_NEVER )
		{
			$this->strict_mode = false;
		}
	}


	/**
	 * method: disable_highlighting
	 * ----------------------------
	 * Disables all highlighting
	 */
	function disable_highlighting ()
	{
		foreach ( $this->language_data['KEYWORDS'] as $key => $words )
		{
			$this->lexic_permissions['KEYWORDS'][$key] = false;
		}
		foreach ( $this->language_data['COMMENT_SINGLE'] as $key => $comment )
		{
			$this->lexic_permissions['COMMENTS'][$key] = false;
		}
		// Multiline comments
		$this->lexic_permissions['COMMENTS']['MULTI'] = false;
		// Escape characters
		$this->lexic_permissions['ESCAPE_CHAR'] = false;
		// Brackets
		$this->lexic_permissions['BRACKETS'] = false;
		// Strings
		$this->lexic_permissions['STRINGS'] = false;
		// Numbers
		$this->lexic_permissions['NUMBERS'] = false;
		// Methods
		$this->lexic_permissions['METHODS'] = false;
		// Symbols
		$this->lexic_permissions['SYMBOLS'] = false;
		// Script
		$this->lexic_permissions['SCRIPT'] = false;
		// Regexps
		foreach ( $this->language_data['REGEXPS'] as $key => $regexp )
		{
			$this->lexic_permissions['REGEXPS'][$key] = false;
		}
		// Context blocks
		$this->enable_important_blocks = false;
	}


	/**
	 * method: enable_highlighting
	 * ---------------------------
	 * Enables all highlighting
	 */
	function enable_highlighting ()
	{
		foreach ( $this->language_data['KEYWORDS'] as $key => $words )
		{
			$this->lexic_permissions['KEYWORDS'][$key] = true;
		}
		foreach ( $this->language_data['COMMENT_SINGLE'] as $key => $comment )
		{
			$this->lexic_permissions['COMMENTS'][$key] = true;
		}
		// Multiline comments
		$this->lexic_permissions['COMMENTS']['MULTI'] = true;
		// Escape characters
		$this->lexic_permissions['ESCAPE_CHAR'] = true;
		// Brackets
		$this->lexic_permissions['BRACKETS'] = true;
		// Strings
		$this->lexic_permissions['STRINGS'] = true;
		// Numbers
		$this->lexic_permissions['NUMBERS'] = true;
		// Methods
		$this->lexic_permissions['METHODS'] = true;
		// Symbols
		$this->lexic_permissions['SYMBOLS'] = true;
		// Script
		$this->lexic_permissions['SCRIPT'] = true;
		// Regexps
		foreach ( $this->language_data['REGEXPS'] as $key => $regexp )
		{
			$this->lexic_permissions['REGEXPS'][$key] = true;
		}
		// Context blocks
		$this->enable_important_blocks = true;
	}


	/**
	 * method: add_keyword
	 * -------------------
	 * Adds a keyword to a keyword group for highlighting
	 */
	function add_keyword( $key, $word )
	{
		$this->language_data['KEYWORDS'][$key][] = $word;
	}


	/**
	 * method: remove_keyword
	 * ----------------------
	 * Removes a keyword from a keyword group
	 */
	function remove_keyword ( $key, $word )
	{
		$this->language_data['KEYWORDS'][$key] = array_diff($this->language_data['KEYWORDS'][$key], array($word));
	}


	/**
	 * method: add_keyword_group
	 * -------------------------
	 * Creates a new keyword group
	 */
	function add_keyword_group ( $key, $styles, $case_sensitive = true, $words = array() )
	{
		if ( !is_array($words) )
		{
			$words = array($words);
		}
		$this->language_data['KEYWORDS'][$key] = $words;
		$this->lexic_permissions['KEYWORDS'][$key] = true;
		$this->language_data['CASE_SENSITIVE'][$key] = $case_sensitive;
		$this->language_data['STYLES']['KEYWORDS'][$key] = $styles;
	}


	/**
	 * method: remove_keyword_group
	 * ----------------------------
	 * Removes a keyword group
	 */
	function remove_keyword_group ( $key )
	{
		unset($this->language_data['KEYWORDS'][$key]);
		unset($this->lexic_permissions['KEYWORDS'][$key]);
		unset($this->language_data['CASE_SENSITIVE'][$key]);
		unset($this->language_data['STYLES']['KEYWORDS'][$key]);
	}


	/**
	 * method: set_header_content
	 * --------------------------
	 * Sets the content of the header block
	 */
	function set_header_content ( $content )
	{
		$this->header_content = $content;
	}


	/**
	 * method: set_footer_content
	 * --------------------------
	 * Sets the content of the footer block
	 */
	function set_footer_content ( $content )
	{
		$this->footer_content = $content;
	}


	/**
	 * method: set_header_content_style
	 * --------------------------------
	 * Sets the style for the header content
	 */
	function set_header_content_style ( $style )
	{
		$this->header_content_style = $style;
	}


	/**
	 * method: set_footer_content_style
	 * --------------------------------
	 * Sets the style for the footer content
	 */
	function set_footer_content_style ( $style )
	{
		$this->footer_content_style = $style;
	}


	/**
	 * method: set_url_for_keyword_group
	 * ---------------------------------
	 * Sets the base URL to be used for keywords
	 */
	function set_url_for_keyword_group ( $group, $url )
	{
		$this->language_data['URLS'][$group] = $url;
	}


	/**
	 * method: set_link_styles
	 * -----------------------
	 * Sets styles for links in code
	 */
	function set_link_styles ( $type, $styles )
	{
		$this->link_styles[$type] = $styles;
	}


	/**
	* method: set_link_target
	* -----------------------
	* Sets the target for links in code
	*/
	function set_link_target ( $target )
	{
		if ( empty( $target ) )
		{
			$this->link_target = '';
		}
		else
		{
			$this->link_target = ' target="' . $target . '" ';
		}
	}


	/**
	 * method: set_important_styles
	 * ----------------------------
	 * Sets styles for important parts of the code
	 */
	function set_important_styles ( $styles )
	{
		$this->important_styles = $styles;
	}


	/**
	 * method: enable_important_blocks
	 * -------------------------------
	 * Sets whether context-important blocks are highlighted
	 */
	function enable_important_blocks ( $flag )
	{
		$this->enable_important_blocks = ( $flag ) ? true : false;
	}


	/**
	 * method: enable_ids
	 * ------------------
	 * Whether CSS IDs should be added to each line
	 */
	function enable_ids ( $flag = true )
	{
		$this->add_ids = ( $flag ) ? true : false;
	}


	/**
	 * method: highlight_lines_extra
	 * -----------------------------
	 * Specifies which lines to highlight extra
	 */
	function highlight_lines_extra ( $lines )
	{
		if ( is_array($lines) )
		{
			foreach ( $lines as $line )
			{
				$this->highlight_extra_lines[intval($line)] = intval($line);
			}
		}
		else
		{
			$this->highlight_extra_lines[intval($lines)] = intval($lines);
		}
	}


	/**
	 * method: set_highlight_lines_extra_style
	 * ---------------------------------------
	 * Sets the style for extra-highlighted lines
	 */
	function set_highlight_lines_extra_style ( $styles )
	{
		$this->highlight_extra_lines_style = $styles;
	}


	/**
	 * method: start_line_numbers_at
	 * -----------------------------
	 * Sets what number line numbers should start at. Should
	 * be a positive integer, and will be converted to one.
	 */
	function start_line_numbers_at ( $number )
	{
		$this->line_numbers_start = abs(intval($number));
	}


	/**
	 * method: set_encoding
	 * --------------------
	 * Sets the encoding used for htmlentities(), for international
	 * support.
	 */
	function set_encoding ( $encoding )
	{
		$this->encoding = $encoding;
	}


	/**
	 * method: parse_code()
	 * --------------------
	 * Returns the code in $this->source, highlighted and surrounded by the
	 * nessecary HTML. This should only be called ONCE, cos it's SLOW!
	 * If you want to highlight the same source multiple times, you're better
	 * off doing a whole lot of str_replaces to replace the <span>s
	 */
	function parse_code()
	{
		// Start the timer
		$start_time = microtime();

		// Firstly, if there is an error, we won't highlight
		// FUTURE: maybe an option to try to force highlighting anyway?
		if ( $this->error )
		{
			$result = $this->header();
			if ( $this->header_type != GESHI_HEADER_PRE )
			{
				$result .= $this->indent(htmlentities($this->source, ENT_COMPAT, $this->encoding));
			}
			else
			{
				$result .= htmlentities($this->source, ENT_COMPAT, $this->encoding);
			}
			// Stop Timing
			$this->set_time($start_time, microtime());
			return $result . $this->footer();
		}

		// Add spaces for regular expression matching and line numbers
		$code = ' ' . $this->source . ' ';
		// Replace all newlines to a common form.
		$code = str_replace("\r\n", "\n", $code);
		$code = str_replace("\r", "\n", $code);

		// Initialise various stuff
		$length = strlen($code);
		$STRING_OPEN = '';
		$CLOSE_STRING = false;
		$ESCAPE_CHAR_OPEN = false;
		$COMMENT_MATCHED = false;
		// Turn highlighting on if strict mode doesn't apply to this language
		$HIGHLIGHTING_ON = ( !$this->strict_mode ) ? true : '';
		// Whether to highlight inside a block of code
		$HIGHLIGHT_INSIDE_STRICT = false;
		$stuff_to_parse = '';
		$result = '';

		// "Important" selections are handled like multiline comments
		if ( $this->enable_important_blocks )
		{
			$this->language_data['COMMENT_MULTI'][GESHI_START_IMPORTANT] = GESHI_END_IMPORTANT;
		}


		if ( $this->strict_mode )
		{
			// Break the source into bits. Each bit will be a portion of the code
			// within script delimiters - for example, HTML between < and >
			$parts = array(0 => array(0 => ''));
			$k = 0;
			for ( $i = 0; $i < $length; $i++ )
			{
				$char = substr($code, $i, 1);
				if ( !$HIGHLIGHTING_ON )
				{
					foreach ( $this->language_data['SCRIPT_DELIMITERS'] as $key => $delimiters )
					{
						foreach ( $delimiters as $open => $close )
						{
							// Get the next little bit for this opening string
							$check = substr($code, $i, strlen($open));
							// If it matches...
							if ( $check == $open )
							{
								// We start a new block with the highlightable
								// code in it
								$HIGHLIGHTING_ON = $open;
								$i += strlen($open) - 1;
								++$k;
								$char = $open;
								$parts[$k][0] = $char;

								// No point going around again...
								break(2);
							}
						}
					}
				}
				else
				{
					foreach ( $this->language_data['SCRIPT_DELIMITERS'] as $key => $delimiters )
					{
						foreach ( $delimiters as $open => $close )
						{
							if ( $open == $HIGHLIGHTING_ON )
							{
								// Found the closing tag
								break(2);
							}
						}
					}
					// We check code from our current position BACKWARDS. This is so
					// the ending string for highlighting can be included in the block
					$check = substr($code, $i - strlen($close) + 1, strlen($close));
					if ( $check == $close )
					{
						$HIGHLIGHTING_ON = '';
						// Add the string to the rest of the string for this part
						$parts[$k][1] = ( isset($parts[$k][1]) ) ? $parts[$k][1] . $char : $char;
						++$k;
						$parts[$k][0] = '';
						$char = '';
					}
				}
				$parts[$k][1] = ( isset($parts[$k][1]) ) ? $parts[$k][1] . $char : $char;
			}
			$HIGHLIGHTING_ON = '';
		}
		else
		{
			// Not strict mode - simply dump the source into
			// the array at index 1 (the first highlightable block)
			$parts = array(
				1 => array(
					0 => '',
					1 => $code
				)
			);
		}

		// Now we go through each part. We know that even-indexed parts are
		// code that shouldn't be highlighted, and odd-indexed parts should
		// be highlighted
		foreach ( $parts as $key => $data )
		{
			$part = $data[1];
			// If this block should be highlighted...
			if ( $key % 2 )
			{
				if ( $this->strict_mode )
				{
					// Find the class key for this block of code
					foreach ( $this->language_data['SCRIPT_DELIMITERS'] as $script_key => $script_data )
					{
						foreach ( $script_data as $open => $close )
						{
							if ( $data[0] == $open )
							{
								break(2);
							}
						}
					}

					if ( $this->language_data['STYLES']['SCRIPT'][$script_key] != '' && $this->lexic_permissions['SCRIPT'] )
					{
						// Add a span element around the source to
						// highlight the overall source block
						if ( !$this->use_classes && $this->language_data['STYLES']['SCRIPT'][$script_key] != '' )
						{
							$attributes = ' style="' . $this->language_data['STYLES']['SCRIPT'][$script_key] . '"';
						}
						else
						{
							$attributes = ' class="sc' . $script_key . '"';
						}
						$result .= "<span$attributes>";
					}
				}

				if ( !$this->strict_mode || $this->language_data['HIGHLIGHT_STRICT_BLOCK'][$script_key] )
				{
					// Now, highlight the code in this block. This code
					// is really the engine of GeSHi (along with the method
					// parse_non_string_part).
					$length = strlen($part);
					for ( $i = 0; $i < $length; $i++ )
					{
						// Get the next char
						$char = substr($part, $i, 1);
						// Is this char the newline and line numbers being used?
						if ( ($this->line_numbers != GESHI_NO_LINE_NUMBERS || count($this->highlight_extra_lines) > 0) && $char == "\n" )
						{
							// If so, is there a string open? If there is, we should end it before
							// the newline and begin it again (so when <li>s are put in the source
							// remains XHTML compliant)
							// NOTE TO SELF: This opens up possibility of config files specifying
							// that languages can/cannot have multiline strings???
							if ( $STRING_OPEN )
							{
								if ( !$this->use_classes )
								{
									$attributes = ' style="' . $this->language_data['STYLES']['STRINGS'][0] . '"';
								}
								else
								{
									$attributes = ' class="st0"';
								}
								$char = '</span>' . $char . "<span$attributes>";
							}
						}
						// Is this a match of a string delimiter?
						elseif ( $char == $STRING_OPEN )
						{
							if ( ($this->lexic_permissions['ESCAPE_CHAR'] && $ESCAPE_CHAR_OPEN) || ($this->lexic_permissions['STRINGS'] && !$ESCAPE_CHAR_OPEN) )
							{
								$char .= '</span>';
							}
							if ( !$ESCAPE_CHAR_OPEN )
							{
								$STRING_OPEN = '';
								$CLOSE_STRING = true;
							}
							$ESCAPE_CHAR_OPEN = false;
						}
						// Is this the start of a new string?
						elseif ( in_array( $char, $this->language_data['QUOTEMARKS'] ) && ($STRING_OPEN == '') && $this->lexic_permissions['STRINGS'] )
						{
							$STRING_OPEN = $char;
							if ( !$this->use_classes )
							{
								$attributes = ' style="' . $this->language_data['STYLES']['STRINGS'][0] . '"';
							}
							else
							{
								$attributes = ' class="st0"';
							}
							$char = "<span$attributes>" . $char;

							$result .= $this->parse_non_string_part( $stuff_to_parse );
							$stuff_to_parse = '';
						}
						// Is this an escape char?
						elseif ( ($char == $this->language_data['ESCAPE_CHAR']) && ($STRING_OPEN != '') )
						{
							if ( !$ESCAPE_CHAR_OPEN )
							{
								$ESCAPE_CHAR_OPEN = true;
								if ( $this->lexic_permissions['ESCAPE_CHAR'] )
								{
									if ( !$this->use_classes )
									{
										$attributes = ' style="' . $this->language_data['STYLES']['ESCAPE_CHAR'][0] . '"';
									}
									else
									{
										$attributes = ' class="es0"';
									}
									$char = "<span$attributes>" . $char;
								}
							}
							else
							{
								$ESCAPE_CHAR_OPEN = false;
								if ( $this->lexic_permissions['ESCAPE_CHAR'] )
								{
									$char .= '</span>';
								}
							}
						}
						elseif ( $ESCAPE_CHAR_OPEN )
						{
							if ( $this->lexic_permissions['ESCAPE_CHAR'] )
							{
								$char .= '</span>';
							}
							$ESCAPE_CHAR_OPEN = false;
							$test_str = $char;
						}
						elseif ( $STRING_OPEN == '' )
						{
							// Is this a multiline comment?
							foreach ( $this->language_data['COMMENT_MULTI'] as $open => $close )
							{
								$com_len = strlen($open);
								$test_str = substr( $part, $i, $com_len );
								$test_str_match = $test_str;
								if ( $open == $test_str )
								{
									$COMMENT_MATCHED = true;
									if ( $this->lexic_permissions['COMMENTS']['MULTI'] || $test_str == GESHI_START_IMPORTANT )
									{
										if ( $test_str != GESHI_START_IMPORTANT )
										{
											if ( !$this->use_classes )
											{
												$attributes = ' style="' . $this->language_data['STYLES']['COMMENTS']['MULTI'] . '"';
											}
											else
											{
												$attributes = ' class="coMULTI"';
											}
											$test_str = "<span$attributes>" . htmlentities($test_str, ENT_COMPAT, $this->encoding);
										}
										else
										{
											if ( !$this->use_classes )
											{
												$attributes = ' style="' . $this->important_styles . '"';
											}
											else
											{
												$attributes = ' class="imp"';
											}
											// We don't include the start of the comment if it's an
											// "important" part
											$test_str = "<span$attributes>";
										}
									}
									else
									{
										$test_str = htmlentities($test_str, ENT_COMPAT, $this->encoding);
									}

									$close_pos = strpos( $part, $close, $i + strlen($close) );

									if ( $close_pos === false )
									{
										$close_pos = strlen($part);
									}

									// Short-cut through all the multiline code
									$rest_of_comment = htmlentities(substr($part, $i + $com_len, $close_pos - $i), ENT_COMPAT, $this->encoding);
									if ( ($this->lexic_permissions['COMMENTS']['MULTI'] || $test_str_match == GESHI_START_IMPORTANT) && ($this->line_numbers != GESHI_NO_LINE_NUMBERS || count($this->highlight_extra_lines) > 0) )
									{
										// strreplace to put close span and open span around multiline newlines
										$test_str .= str_replace("\n", "</span>\n<span$attributes>", $rest_of_comment);
									}
									else
									{
										$test_str .= $rest_of_comment;
									}

									if ( $this->lexic_permissions['COMMENTS']['MULTI'] || $test_str_match == GESHI_START_IMPORTANT )
									{
										$test_str .= '</span>';
									}
									$i = $close_pos + $com_len - 1;
									// parse the rest
									$result .= $this->parse_non_string_part( $stuff_to_parse );
									$stuff_to_parse = '';
									break;
								}
							}
							// If we haven't matched a multiline comment, try single-line comments
							if ( !$COMMENT_MATCHED )
							{
								foreach ( $this->language_data['COMMENT_SINGLE'] as $comment_key => $comment_mark )
								{
									$com_len = strlen($comment_mark);
									$test_str = substr( $part, $i, $com_len );
									if ( $this->language_data['CASE_SENSITIVE'][GESHI_COMMENTS] )
									{
										$match = ( $comment_mark == $test_str );
									}
									else
									{
										$match = ( strtolower($comment_mark) == strtolower($test_str) );
									}
									if ( $match )
									{
										$COMMENT_MATCHED = true;
										if ( $this->lexic_permissions['COMMENTS'][$comment_key] )
										{
											if ( !$this->use_classes )
											{
												$attributes = ' style="' . $this->language_data['STYLES']['COMMENTS'][$comment_key] . '"';
											}
											else
											{
												$attributes = ' class="co' . $comment_key . '"';
											}
											$test_str = "<span$attributes>" . htmlentities($this->change_case($test_str), ENT_COMPAT, $this->encoding);
										}
										else
										{
											$test_str = htmlentities($test_str, ENT_COMPAT, $this->encoding);
										}
										$close_pos = strpos( $part, "\n", $i );
										if ( $close_pos === false )
										{
											$close_pos = strlen($part);
										}
										$test_str .= htmlentities(substr($part, $i + $com_len, $close_pos - $i - $com_len), ENT_COMPAT, $this->encoding);
										if ( $this->lexic_permissions['COMMENTS'][$comment_key] )
										{
											$test_str .= "</span>";
										}
										$test_str .= "\n";
										$i = $close_pos;
										// parse the rest
										$result .= $this->parse_non_string_part( $stuff_to_parse );
										$stuff_to_parse = '';
										break;
									}
								}
							}
						}
						// Otherwise, convert it to HTML form
						elseif ( $STRING_OPEN != '' )
						{
							$char = htmlentities($char, ENT_COMPAT, $this->encoding);
						}
						// Where are we adding this char?
						if ( !$COMMENT_MATCHED )
						{
							if ( ($STRING_OPEN == '') && !$CLOSE_STRING )
							{
								$stuff_to_parse .= $char;
							}
							else
							{
								$result .= $char;
								$CLOSE_STRING = false;
							}
						}
						else
						{
							$result .= $test_str;
							$COMMENT_MATCHED = false;
						}
					}
					// Parse the last bit
					$result .= $this->parse_non_string_part( $stuff_to_parse );
					$stuff_to_parse = '';
				}
				else
				{
					$result .= htmlentities($part, ENT_COMPAT, $this->encoding);
				}
				// Close the <span> that surrounds the block
				if ( $this->strict_mode && $this->lexic_permissions['SCRIPT'] )
				{
					$result .= '</span>';
				}
			}
			// Else not a block to highlight
			else
			{
				$result .= htmlentities($part, ENT_COMPAT, $this->encoding);
			}
		}

		// Parse the last stuff (redundant?)
		$result .= $this->parse_non_string_part( $stuff_to_parse );

		// Lop off the very first and last spaces
		$result = substr($result, 1, strlen($result) - 1);

		// Are we still in a string?
		if ( $STRING_OPEN )
		{
			$result .= '</span>';
		}

		// We're finished: stop timing
		$this->set_time($start_time, microtime());

		return $this->finalise($result);
	}

	/**
	 * method: indent
	 * --------------
	 * Swaps out spaces and tabs for HTML indentation. Not needed if
	 * the code is in a pre block...
	 */
	function indent ( $result )
	{
		$result = str_replace('  ', '&nbsp; ', $result);
		$result = str_replace('  ', ' &nbsp;', $result);
		$result = str_replace("\n ", "\n&nbsp;", $result);
		$result = str_replace("\t", $this->get_tab_replacement(), $result);
		if ( $this->line_numbers == GESHI_NO_LINE_NUMBERS )
		{
			$result = nl2br($result);
		}
		return $result;
	}

	/**
	 * method: change_case
	 * -------------------
	 * Changes the case of a keyword for those languages where a change is asked for
	 */
	function change_case ( $instr )
	{
		if ( $this->language_data['CASE_KEYWORDS'] == GESHI_CAPS_UPPER )
		{
			return strtoupper($instr);
		}
		elseif ( $this->language_data['CASE_KEYWORDS'] == GESHI_CAPS_LOWER )
		{
			return strtolower($instr);
		}
		return $instr;
	}


	/**
	 * method: add_url_to_keyword
	 * --------------------------
	 * Adds a url to a keyword where needed.
	 * Added in 1.0.2
	 */
	function add_url_to_keyword ( $keyword, $group, $start_or_end )
	{
		if ( isset($this->language_data['URLS'][$group]) && $this->language_data['URLS'][$group] != '' && substr($keyword, 0, 5) != '&lt;/' )
		{
			// There is a base group for this keyword

			if ( $start_or_end == 'BEGIN' )
			{
				// HTML workaround... not good form (tm) but should work for 1.0.X
				$keyword = ( substr($keyword, 0, 4) == '&lt;' ) ? substr($keyword, 4) : $keyword;
				$keyword = ( substr($keyword, -4) == '&gt;' ) ? substr($keyword, 0, strlen($keyword) - 4) : $keyword;
				if ( $keyword != '' )
				{
					$keyword = ( $this->language_data['CASE_SENSITIVE'][$group] ) ? $keyword : strtolower($keyword);
					return '<|UR1|"' . str_replace(array('{FNAME}', '.'), array(htmlentities($keyword, ENT_COMPAT, $this->encoding), '<DOT>'), $this->language_data['URLS'][$group]) . '">';
				}
				return '';
			}
			else
			{
				return '</a>';
			}
		}
	}


	/**
	 * method: parse_non_string_part
	 * -----------------------------
	 * Takes a string that has no strings or comments in it, and highlights
	 * stuff like keywords, numbers and methods.
	 */
	function parse_non_string_part ( &$stuff_to_parse )
	{
		$stuff_to_parse = ' ' . quotemeta(htmlentities($stuff_to_parse, ENT_COMPAT, $this->encoding));
		// These vars will disappear in the future
		$func = '$this->change_case';
		$func2 = '$this->add_url_to_keyword';


		//
		// Regular expressions
		//
		foreach ( $this->language_data['REGEXPS'] as $key => $regexp )
		{
			if ( $this->lexic_permissions['REGEXPS'][$key] )
			{
				if ( is_array($regexp) )
				{
					$stuff_to_parse = preg_replace( "#" . $regexp[GESHI_SEARCH] . "#{$regexp[GESHI_MODIFIERS]}", "{$regexp[GESHI_BEFORE]}<|!REG3XP$key!>{$regexp[GESHI_REPLACE]}|>{$regexp[GESHI_AFTER]}", $stuff_to_parse);
				}
				else
				{
					$stuff_to_parse = preg_replace( "#(" . $regexp . ")#", "<|!REG3XP$key!>\\1|>", $stuff_to_parse);
				}
			}
		}

		//
		// Highlight numbers. This regexp sucks... anyone with a regexp that WORKS
		// here wins a cookie if they send it to me. At the moment there's two doing
		// almost exactly the same thing, except the second one prevents a number
		// being highlighted twice (eg <span...><span...>5</span></span>)
		// Put /NUM!/ in for the styles, which gets replaced at the end.
		//
		if ( $this->lexic_permissions['NUMBERS'] && preg_match('#[0-9]#', $stuff_to_parse ) )
		{
			$stuff_to_parse = preg_replace('#([^a-zA-Z0-9\#])([0-9]+)([^a-zA-Z0-9])#', "\\1<|/NUM!/>\\2|>\\3", $stuff_to_parse);
			$stuff_to_parse = preg_replace('#([^a-zA-Z0-9\#>])([0-9]+)([^a-zA-Z0-9])#', "\\1<|/NUM!/>\\2|>\\3", $stuff_to_parse);
		}

		// Highlight keywords
		// if there is a couple of alpha symbols there *might* be a keyword
		if ( preg_match('#[a-zA-Z]{2,}#', $stuff_to_parse) )
		{
			foreach ( $this->language_data['KEYWORDS'] as $k => $keywordset )
			{
				if ( $this->lexic_permissions['KEYWORDS'][$k] )
				{
					foreach ( $keywordset as $keyword )
					{
						$keyword = quotemeta($keyword);
						//
						// This replacement checks the word is on it's own (except if brackets etc
						// are next to it), then highlights it. We don't put the color=" for the span
						// in just yet - otherwise languages with the keywords "color" or "or" have
						// a fit.
						//
						if ( false !== stristr($stuff_to_parse, $keyword ) )
						{
							$stuff_to_parse .= ' ';
							// Might make a more unique string for putting the number in soon
							// Basically, we don't put the styles in yet because then the styles themselves will
							// get highlighted if the language has a CSS keyword in it (like CSS, for example ;))
							$styles = "/$k/";
							$keyword = quotemeta($keyword);
							if ( $this->language_data['CASE_SENSITIVE'][$k] )
							{
								$stuff_to_parse = preg_replace("#([^a-zA-Z0-9\$_\|\.\#;>])($keyword)([^a-zA-Z0-9_<\|%\-&])#e", "'\\1' . $func2('\\2', '$k', 'BEGIN') . '<|$styles>' . $func('\\2') . '|>' . $func2('\\2', '$k', 'END') . '\\3'", $stuff_to_parse);
							}
							else
							{
								// Change the case of the word.
								$stuff_to_parse = preg_replace("#([^a-zA-Z0-9\$_\|\.\#;>])($keyword)([^a-zA-Z0-9_<\|%\-&])#ie", "'\\1' . $func2('\\2', '$k', 'BEGIN') . '<|$styles>' . $func('\\2') . '|>' . $func2('\\2', '$k', 'END') . '\\3'", $stuff_to_parse);
							}
							$stuff_to_parse = substr($stuff_to_parse, 0, strlen($stuff_to_parse) - 1);
						}
					}
				}
			}
		}

		//
		// Now that's all done, replace /[number]/ with the correct styles
		//
		foreach ( $this->language_data['KEYWORDS'] as $k => $kws )
		{
			if ( !$this->use_classes )
			{
				$attributes = ' style="' . $this->language_data['STYLES']['KEYWORDS'][$k] . '"';
			}
			else
			{
				$attributes = ' class="kw' . $k . '"';
			}
			$stuff_to_parse = str_replace("/$k/", $attributes, $stuff_to_parse);
		}

		// Put number styles in
		if ( !$this->use_classes && $this->lexic_permissions['NUMBERS'] )
		{
			$attributes = ' style="' . $this->language_data['STYLES']['NUMBERS'][0] . '"';
		}
		else
		{
			$attributes = ' class="nu0"';
		}
		$stuff_to_parse = str_replace('/NUM!/', $attributes, $stuff_to_parse);

		//
		// Highlight methods and fields in objects
		//
		if ( $this->lexic_permissions['METHODS'] && $this->language_data['OOLANG'] )
		{
			foreach ( $this->language_data['OBJECT_SPLITTERS'] as $key => $splitter )
			{
				if ( false !== stristr($stuff_to_parse, $this->language_data['OBJECT_SPLITTERS'][$key]) )
				{
					if ( !$this->use_classes )
					{
						$attributes = ' style="' . $this->language_data['STYLES']['METHODS'][$key] . '"';
					}
					else
					{
						$attributes = ' class="me' . $key . '"';
					}
					$stuff_to_parse = preg_replace("#(" . quotemeta($this->language_data['OBJECT_SPLITTERS'][$key]) . "[\s]*)([a-zA-Z\*\(][a-zA-Z0-9_\*]*)#", "\\1<|$attributes>\\2|>", $stuff_to_parse);
				}
			}
		}

		//
		// Highlight brackets. Yes, I've tried adding a semi-colon to this list.
		// You try it, and see what happens ;)
		// TODO: Fix lexic permissions not converting entities if shouldn't
		// be highlighting regardless
		//
		if ( $this->lexic_permissions['BRACKETS'] )
		{
			$code_entities_match = array('[', ']', '(', ')', '{', '}');
			if ( !$this->use_classes )
			{
				$code_entities_replace = array(
					'<| style="' . $this->language_data['STYLES']['BRACKETS'][0] . '">&#91;|>',
					'<| style="' . $this->language_data['STYLES']['BRACKETS'][0] . '">&#93;|>',
					'<| style="' . $this->language_data['STYLES']['BRACKETS'][0] . '">&#40;|>',
					'<| style="' . $this->language_data['STYLES']['BRACKETS'][0] . '">&#41;|>',
					'<| style="' . $this->language_data['STYLES']['BRACKETS'][0] . '">&#123;|>',
					'<| style="' . $this->language_data['STYLES']['BRACKETS'][0] . '">&#125;|>',
				);
			}
			else
			{
				$code_entities_replace = array(
					'<| class="br0">&#91;|>',
					'<| class="br0">&#93;|>',
					'<| class="br0">&#40;|>',
					'<| class="br0">&#41;|>',
					'<| class="br0">&#123;|>',
					'<| class="br0">&#125;|>',
				);
			}
			$stuff_to_parse = str_replace( $code_entities_match,  $code_entities_replace, $stuff_to_parse );
		}

		//
		// Add class/style for regexps
		//
		foreach ( $this->language_data['REGEXPS'] as $key => $regexp )
		{
			if ( $this->lexic_permissions['REGEXPS'][$key] )
			{
				if ( !$this->use_classes )
				{
					$attributes = ' style="' . $this->language_data['STYLES']['REGEXPS'][$key] . '"';
				}
				else
				{
					$attributes = ' class="re' . $key . '"';
				}
				$stuff_to_parse = str_replace("!REG3XP$key!", "$attributes", $stuff_to_parse);
			}
		}

		// Replace <DOT> with . for urls
		$stuff_to_parse = str_replace('<DOT>', '.', $stuff_to_parse);
		// Replace <|UR1| with <a href= for urls also
		if ( isset($this->link_styles[GESHI_LINK]) )
		{
			if ( $this->use_classes )
			{
				$stuff_to_parse = str_replace('<|UR1|', '<a' . $this->link_target . ' href=', $stuff_to_parse);
			}
			else
			{
				$stuff_to_parse = str_replace('<|UR1|', '<a' . $this->link_target . ' style="' . $this->link_styles[GESHI_LINK] . '" href=', $stuff_to_parse);
			}
		}
		else
		{
			$stuff_to_parse = str_replace('<|UR1|', '<a' . $this->link_target . ' href=', $stuff_to_parse);
		}

		//
		// NOW we add the span thingy ;)
		//

		$stuff_to_parse = str_replace('<|', '<span', $stuff_to_parse);
		$stuff_to_parse = str_replace ( '|>', '</span>', $stuff_to_parse );

		return substr(stripslashes($stuff_to_parse), 1);
	}

	/**
	 * method: set_time
	 * ----------------
	 * Sets the time taken to parse the code
	 */
	function set_time ( $start_time, $end_time )
	{
		$start = explode(' ', $start_time);
		$end = explode(' ', $end_time);
		$this->time = $end[0] + $end[1] - $start[0] - $start[1];
	}

	/**
	 * method: get_time
	 * ----------------
	 * Gets the time taken to parse the code
	 */
	function get_time ()
	{
		return $this->time;
	}

	/**
	 * method: load_language
	 * ---------------------
	 * Gets language information and stores it for later use
	 */
	function load_language ()
	{
		$file_name = $this->language_path . $this->language . '.php';
		if ( !is_readable($file_name))
		{
			$this->error = GESHI_ERROR_NO_SUCH_LANG;
			return;
		}
		require($file_name);
		// Perhaps some checking might be added here later to check that
		// $language data is a valid thing but maybe not
		$this->language_data = $language_data;
		// Set strict mode if should be set
		if ( $this->language_data['STRICT_MODE_APPLIES'] == GESHI_ALWAYS )
		{
			$this->strict_mode = true;
		}
		// Set permissions for all lexics to true
		// so they'll be highlighted by default
		$this->enable_highlighting();
		// Set default class for CSS
		$this->overall_class = $this->language;
	}

	/**
	 * method: get_tab_replacement
	 * ---------------------------
	 * Gets the replacement string for tabs in the source code. Useful for
	 * HTML highlighting, where tabs don't mean anything to a browser.
	 */
	function get_tab_replacement ()
	{
		$i = 0;
		$result = '';
		while ( $i < $this->tab_width )
		{
			$i++;
			if ( $i % 2 == 0 )
			{
				$result .= ' ';
			}
			else
			{
				$result .= '&nbsp;';
			}
		}
		return $result;
	}

	/**
	 * method: finalise
	 * ----------------
	 * Takes the parsed code and various options, and creates the HTML
	 * surrounding it to make it look nice.
	 */
	function finalise ( $parsed_code )
	{
		// Remove end parts of important declarations
		// This is BUGGY!! My fault for bad code: fix coming in 1.2
		if ( $this->enable_important_blocks && (strstr($parsed_code, htmlentities(GESHI_START_IMPORTANT, ENT_COMPAT, $this->encoding)) === false) )
		{
			$parsed_code = str_replace(htmlentities(GESHI_END_IMPORTANT, ENT_COMPAT, $this->encoding), '', $parsed_code);
		}

		// Add HTML whitespace stuff if we're using the <div> header
		if ( $this->header_type == GESHI_HEADER_DIV )
		{
			$parsed_code = $this->indent($parsed_code);
		}

		// If we're using line numbers, we insert <li>s and appropriate
		// markup to style them (otherwise we don't need to do anything)
		if ( $this->line_numbers != GESHI_NO_LINE_NUMBERS )
		{
			// If we're using the <pre> header, we shouldn't add newlines because
			// the <pre> will line-break them (and the <li>s already do this for us)
			$ls = ( $this->header_type != GESHI_HEADER_PRE ) ? "\n" : '';
			// Get code into lines
			$code = explode("\n", $parsed_code);
			// Set vars to defaults for following loop
			$parsed_code = '';
			$i = 0;
			// Foreach line...
			foreach ( $code as $line )
			{
				$line = ( $line ) ? $line : '&nbsp;';
				// If this is a "special line"...
				if ( $this->line_numbers == GESHI_FANCY_LINE_NUMBERS && $i % $this->line_nth_row == ($this->line_nth_row - 1) )
				{
					// Set the attributes to style the line
					if ( $this->use_classes )
					{
						$attr = ' class="li2"';
						$def_attr = ' class="de2"';
					}
					else
					{
						$attr = ' style="' . $this->line_style2 . '"';
						// This style "covers up" the special styles set for special lines
						// so that styles applied to special lines don't apply to the actual
						// code on that line
						$def_attr = ' style="' . $this->code_style . '"';
					}
					// Span or div?
					$start = "<div$def_attr>";
					$end = '</div>';
				}
				else
				{
					if ( $this->use_classes )
					{
						$def_attr = ' class="de1"';
					}
					else
					{
						$def_attr = ' style="' . $this->code_style . '"';
					}
					// Reset everything
					$attr = '';
					// Span or div?
					$start = "<div$def_attr>";
					$end = '</div>';
				}

				++$i;
				// Are we supposed to use ids? If so, add them
				if ( $this->add_ids )
				{
					$attr .= " id=\"{$this->overall_id}-{$i}\"";
				}
				if ( $this->use_classes && in_array($i, $this->highlight_extra_lines) )
				{
					$attr .= " class=\"ln-xtra\"";
				}
				if ( !$this->use_classes && in_array($i, $this->highlight_extra_lines) )
				{
					$attr .= " style=\"{$this->highlight_extra_lines_style}\"";
				}

				// Add in the line surrounded by appropriate list HTML
				$parsed_code .= "<li$attr>$start$line$end</li>$ls";
			}
		}
		else
		{
			// No line numbers, but still need to handle highlighting lines extra.
			// Have to use divs so the full width of the code is highlighted
			$code = explode("\n", $parsed_code);
			$parsed_code = '';
			$i = 0;
			foreach ( $code as $line )
			{
				// Make lines have at least one space in them if they're empty
				$line = ( $line ) ? $line : '&nbsp;';
				if ( in_array(++$i, $this->highlight_extra_lines) )
				{
					if ( $this->use_classes )
					{
						//$id = ( $this->overall_id != '' ) ? $this->overall_id . "-$i" : $this->overall_class . "-$i";
						$parsed_code .= '<div class="ln-xtra">';
					}
					else
					{
						$parsed_code .= "<div style=\"{$this->highlight_extra_lines_style}\">";
					}
					$parsed_code .= $line . "</div>\n";
				}
				else
				{
					$parsed_code .= $line . "\n";
				}
			}
		}

		return $this->header() . chop($parsed_code) . $this->footer();
	}


	/**
	 * method: header
	 * --------------
	 * Creates the header for the code block (with correct attributes)
	 */
	function header ()
	{
		// Get attributes needed
		$attributes = $this->get_attributes();

		if ( $this->use_classes )
		{
			$ol_attributes = '';
		}
		else
		{
			$ol_attributes = ' style="margin: 0;"';
		}

		if ( $this->line_numbers_start != 1 )
		{
			$ol_attributes .= ' start="' . $this->line_numbers_start . '"';
		}

		// Get the header HTML
		$header = $this->format_header_content();

		// Work out what to return and do it
		if ( $this->line_numbers != GESHI_NO_LINE_NUMBERS )
		{
			if ( $this->header_type == GESHI_HEADER_PRE )
			{
				return "<pre$attributes>$header<ol$ol_attributes>";
			}
			elseif ( $this->header_type == GESHI_HEADER_DIV )
			{
				return "<div$attributes>$header<ol$ol_attributes>";
			}
		}
		else
		{
			if ( $this->header_type == GESHI_HEADER_PRE )
			{
				return "<pre$attributes>$header";
			}
			elseif ( $this->header_type == GESHI_HEADER_DIV )
			{
				return "<div$attributes>$header";
			}
		}
	}


	/**
	 * method: format_header_content
	 * -----------------------------
	 * Returns the header content, formatted for output
	 */
	function format_header_content ()
	{
		$header = $this->header_content;
		if ( $header )
		{
			if ( $this->header_type == GESHI_HEADER_PRE )
			{
				$header = str_replace("\n", '', $header);
			}
			$header = $this->replace_keywords($header);

			if ( $this->use_classes )
			{
				$attr = ' class="head"';
			}
			else
			{
				$attr = " style=\"{$this->header_content_style}\"";
			}
			return "<div$attr>$header</div>";
		}
	}


	/**
	 * method: footer
	 * --------------
	 * Returns the footer for the code block. Ending newline removed in 1.0.2
	 */
	function footer ()
	{
		$footer_content = $this->format_footer_content();

		if ( $this->header_type == GESHI_HEADER_DIV )
		{
			if ( $this->line_numbers != GESHI_NO_LINE_NUMBERS )
			{
				return "</ol>$footer_content</div>";
			}
			return "$footer_content</div>";
		}
		else
		{
			if ( $this->line_numbers != GESHI_NO_LINE_NUMBERS )
			{
				return "</ol>$footer_content</pre>";
			}
			return "$footer_content</pre>";
		}
	}


	/**
	 * method: format_footer_content
	 * -----------------------------
	 * Returns the footer content, formatted for output
	 */
	function format_footer_content ()
	{
		$footer = $this->footer_content;
		if ( $footer )
		{
			if ( $this->header_type == GESHI_HEADER_PRE )
			{
				$footer = str_replace("\n", '', $footer);;
			}
			$footer = $this->replace_keywords($footer);

			if ( $this->use_classes )
			{
				$attr = ' class="foot"';
			}
			else
			{
				$attr = " style=\"{$this->footer_content_style}\">";
			}
			return "<div$attr>$footer</div>";
		}
	}


	/**
	 * method: replace_keywords
	 * ----------------------
	 * Replaces certain keywords in the header and footer with
	 * certain configuration values
	 */
	function replace_keywords ( $instr )
	{
		$keywords = $replacements = array();

		$keywords[] = '<TIME>';
		$replacements[] = number_format($this->get_time(), 3);

		$keywords[] = '<LANGUAGE>';
		$replacements[] = $this->language;

		$keywords[] = '<VERSION>';
		$replacements[] = '1.0.4';

		return str_replace($keywords, $replacements, $instr);
	}

	/**
	 * method: get_attributes
	 * ----------------------
	 * Gets the CSS attributes for this code
	 */
	function get_attributes ()
	{
		$attributes = '';

		if ( $this->overall_class != '' && $this->use_classes )
		{
			$attributes .= " class=\"{$this->overall_class}\"";
		}
		if ( $this->overall_id != '' )
		{
			$attributes .= " id=\"{$this->overall_id}\"";
		}
		if ( $this->overall_style != '' && !$this->use_classes )
		{
			$attributes .= ' style="' . $this->overall_style . '"';
		}
		return $attributes;
	}


	/**
	 * method: get_stylesheet
	 * ----------------------
	 * Returns a stylesheet for the highlighted code. If $economy mode
	 * is true, we only return the stylesheet declarations that matter for
	 * this code block instead of the whole thing
	 */
	function get_stylesheet ( $economy_mode = true )
	{
		// If there's an error, chances are that the language file
		// won't have populated the language data file, so we can't
		// risk getting a stylesheet...
		if ( $this->error )
		{
			return '';
		}
		// First, work out what the selector should be. If there's an ID,
		// that should be used, the same for a class. Otherwise, a selector
		// of '' means that these styles will be applied anywhere
		$selector = ( $this->overall_id != '' ) ? "#{$this->overall_id} " : '';
		$selector = ( $selector == '' && $this->overall_class != '' ) ? ".{$this->overall_class} " : $selector;

		// Header of the stylesheet
		if ( !$economy_mode )
		{
			$stylesheet = "/**\n * GeSHi Dynamically Generated Stylesheet\n * --------------------------------------\n * Dynamically generated stylesheet for {$this->language}\n * CSS class: {$this->overall_class}, CSS id: {$this->overall_id}\n * GeSHi (c) Nigel McNie 2004 (http://qbnz.com/highlighter)\n */\n";
 		}
		else
		{
			$stylesheet = '/* GeSHi (c) Nigel McNie 2004 (http://qbnz.com/highlighter) */' . "\n";
		}

		// Set the <ol> to have no effect at all if there are line numbers
		// (<ol>s have margins that should be destroyed so all layout is
		// controlled by the set_overall_style method, which works on the
		// <pre> or <div> container). Additionally, set default styles for lines
		if ( !$economy_mode || $this->line_numbers != GESHI_NO_LINE_NUMBERS )
		{
			$stylesheet .= "$selector, {$selector}ol, {$selector}ol li {margin: 0;}\n";
			$stylesheet .= "$selector.de1, $selector.de2 {{$this->code_style}}\n";
		}

		// Add overall styles
		if ( !$economy_mode || $this->overall_style != '' )
		{
			$stylesheet .= "$selector {{$this->overall_style}}\n";
		}

		// Add styles for links
		foreach ( $this->link_styles as $key => $style )
		{
			if ( !$economy_mode || $key == GESHI_LINK && $style != '' )
			{
				$stylesheet .= "{$selector}a:link {{$style}}\n";
			}
			if ( !$economy_mode || $key == GESHI_HOVER && $style != '' )
			{
				$stylesheet .= "{$selector}a:hover {{$style}}\n";
			}
			if ( !$economy_mode || $key == GESHI_ACTIVE && $style != '' )
			{
				$stylesheet .= "{$selector}a:active {{$style}}\n";
			}
			if ( !$economy_mode || $key == GESHI_VISITED && $style != '' )
			{
				$stylesheet .= "{$selector}a:visited {{$style}}\n";
			}
		}

		// Header and footer
		if ( !$economy_mode || $this->header_content_style != '' )
		{
			$stylesheet .= "$selector.head {{$this->header_content_style}}\n";
		}
		if ( !$economy_mode || $this->footer_content_style != '' )
		{
			$stylesheet .= "$selector.foot {{$this->footer_content_style}}\n";
		}

		// Styles for important stuff
		if ( !$economy_mode || $this->important_styles != '' )
		{
			$stylesheet .= "$selector.imp {{$this->important_styles}}\n";
		}


		// Styles for lines being highlighted extra
		if ( !$economy_mode || count($this->highlight_extra_lines) )
		{
			/*foreach ( $this->highlight_extra_lines as $line )
			{
				$id = ( $this->overall_id != '' ) ? $this->overall_id . "-$line" : $this->overall_class . "-$line";
				$stylesheet .= "$selector#$id,";
			}*/
			$stylesheet .= "$selector.ln-xtra {{$this->highlight_extra_lines_style}}\n";
		}


		// Simple line number styles
		if ( !$economy_mode || ($this->line_numbers != GESHI_NO_LINE_NUMBERS && $this->line_style1 != '') )
		{
			$stylesheet .= "{$selector}li {{$this->line_style1}}\n";
		}

		// If there is a style set for fancy line numbers, echo it out
		if ( !$economy_mode || ($this->line_numbers == GESHI_FANCY_LINE_NUMBERS && $this->line_style2 != '') )
		{
			$stylesheet .= "{$selector}li.li2 {{$this->line_style2}}\n";
		}


		foreach ( $this->language_data['STYLES']['KEYWORDS'] as $group => $styles )
		{
			if ( !$economy_mode || !($economy_mode && (!$this->lexic_permissions['KEYWORDS'][$group] || $styles == '')) )
			{
				$stylesheet .= "$selector.kw$group {{$styles}}\n";
			}
		}
		foreach ( $this->language_data['STYLES']['COMMENTS'] as $group => $styles )
		{
			if ( !$economy_mode || !($economy_mode && $styles == '') && !($economy_mode && !$this->lexic_permissions['COMMENTS'][$group]) )
			{
				$stylesheet .= "$selector.co$group {{$styles}}\n";
			}
		}
		foreach ( $this->language_data['STYLES']['ESCAPE_CHAR'] as $group => $styles )
		{
			if ( !$economy_mode || !($economy_mode && $styles == '') && !($economy_mode && !$this->lexic_permissions['ESCAPE_CHAR']) )
			{
				$stylesheet .= "$selector.es$group {{$styles}}\n";
			}
		}
		foreach ( $this->language_data['STYLES']['SYMBOLS'] as $group => $styles )
		{
			if ( !$economy_mode || !($economy_mode && $styles == '') && !($economy_mode && !$this->lexic_permissions['BRACKETS']) )
			{
				$stylesheet .= "$selector.br$group {{$styles}}\n";
			}
		}
		foreach ( $this->language_data['STYLES']['STRINGS'] as $group => $styles )
		{
			if ( !$economy_mode || !($economy_mode && $styles == '') && !($economy_mode && !$this->lexic_permissions['STRINGS']) )
			{
				$stylesheet .= "$selector.st$group {{$styles}}\n";
			}
		}
		foreach ( $this->language_data['STYLES']['NUMBERS'] as $group => $styles )
		{
			if ( !$economy_mode || !($economy_mode && $styles == '') && !($economy_mode && !$this->lexic_permissions['NUMBERS']) )
			{
				$stylesheet .= "$selector.nu$group {{$styles}}\n";
			}
		}
		foreach ( $this->language_data['STYLES']['METHODS'] as $group => $styles )
		{
			if ( !$economy_mode || !($economy_mode && $styles == '') && !($economy_mode && !$this->lexic_permissions['METHODS']) )
			{
				$stylesheet .= "$selector.me$group {{$styles}}\n";
			}
		}
		foreach ( $this->language_data['STYLES']['SCRIPT'] as $group => $styles )
		{
			if ( !$economy_mode || !($economy_mode && $styles == '') /*&& !($economy_mode && !$this->lexic_permissions['SCRIPT'])*/ )
			{
				$stylesheet .= "$selector.sc$group {{$styles}}\n";
			}
		}
		foreach ( $this->language_data['STYLES']['REGEXPS'] as $group => $styles )
		{
			if ( !$economy_mode || !($economy_mode && $styles == '') && !($economy_mode && !$this->lexic_permissions['REGEXPS'][$group]) )
			{
				$stylesheet .= "$selector.re$group {{$styles}}\n";
			}
		}

		return $stylesheet;
	}

} // End Class GeSHi


if ( !function_exists('geshi_highlight') )
{
	/**
	* function: geshi_highlight
	* -------------------------
	* Easy way to highlight stuff. Behaves just like highlight_string
	*/
	function geshi_highlight ( $string, $language, $path, $return = false )
	{
		$geshi = new GeSHi($string, $language, $path);
		$geshi->set_header_type(GESHI_HEADER_DIV);
		if ( $return )
		{
			return str_replace('<div>', '<code>', str_replace('</div>', '</code>', $geshi->parse_code()));
		}
		echo str_replace('<div>', '<code>', str_replace('</div>', '</code>', $geshi->parse_code()));
		if ( $geshi->error() )
		{
			return false;
		}
		return true;
	}
}
?>