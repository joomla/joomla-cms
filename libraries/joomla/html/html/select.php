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
 * Utility class for creating HTML select lists
 *
 * @static
 * @package 	Joomla.Framework
 * @subpackage	HTML
 * @since		1.5
 */
class JHTMLSelect
{
	function Option( $value, $text='', $value_name='value', $text_name='text' )
	{
		$obj = new stdClass;
		$obj->$value_name = $value;
		$obj->$text_name = trim( $text ) ? $text : $value;
		return $obj;
	}
	
	/**
	 * Generates just the option tags for an HTML select list
	 *
	 * @param	array	An array of objects
	 * @param	string	The name of the object variable for the option value
	 * @param	string	The name of the object variable for the option text
	 * @param	mixed	The key that is selected (accepts an array or a string)
	 * @returns	string	HTML for the select list
	 */
	function Options( $arr, $key, $text, $selected=null, $flag=false )
	{
		$html = '';

		while(current($arr) !== FALSE) {
			$element =& $arr[key($arr)]; // since current doesn't return a reference, need to do this

			$isArray = is_array( $element );
			if ($isArray) {
				$k 		= $element[$key];
				$t	 	= $element[$text];
				$id 	= ( isset( $element['id'] ) ? $element['id'] : null );
			} else {
				$k 		= $element->$key;
				$t	 	= $element->$text;
				$id 	= ( isset( $element->id ) ? $element->id : null );
			}

			// This is real dirty, open to suggestions,
			// barring doing a propper object to handle it
			if ($k === '<OPTGROUP>') {
				$html .= '<optgroup label="' . $t . '">';
			} else if ($k === '</OPTGROUP>') {
				$html .= '</optgroup>';
			} else {
				//if no string after hypen - take hypen out
				$splitText = explode( " - ", $t, 2 );
				$t = $splitText[0];
				if(isset($splitText[1])){ $t .= " - ". $splitText[1]; }

				$extra = '';
				//$extra .= $id ? ' id="' . $arr[$i]->id . '"' : '';
				if (is_array( $selected )) {
					foreach ($selected as $obj) {
						$k2 = $obj->$key;
						if ($k == $k2) {
							$extra .= ' selected="selected"';
							break;
						}
					}
				} else {
					$extra .= ( $k == $selected ? ' selected="selected"' : '' );
				}
				//if flag translate text
				if ($flag) {
					$t = JText::_( $t );
				}

				$html .= '<option value="'. $k .'" '. $extra .'>' . $t . '</option>';
			}
			next($arr);
		}

		return $html;
	}
	
	/**
	 * Generates an HTML select list
	 *
	 * @param	array	An array of objects
	 * @param	string	The value of the HTML name attribute
	 * @param	string	Additional HTML attributes for the <select> tag
	 * @param	string	The name of the object variable for the option value
	 * @param	string	The name of the object variable for the option text
	 * @param	mixed	The key that is selected (accepts an array or a string)
	 * @returns	string	HTML for the select list
	 */
	function GenericList( $arr, $tag_name, $tag_attribs, $key, $text, $selected=NULL, $idtag=false, $flag=false )
	{
		// check if array
		if ( is_array( $arr ) ) {
			reset( $arr );
		}

		$id = $tag_name;
		if ( $idtag ) {
			$id = $idtag;
		}
		$id = str_replace('[','',$id);
		$id = str_replace(']','',$id);

		$html = '<select name="'. $tag_name .'" id="'. $id .'" '. $tag_attribs .'>';
//		for ($i=0, $n=count( $arr ); $i < $n; $i++ ) {
		$html .= JHTMLSelect::Options( $arr, $key, $text, $selected, $flag );
		$html .= '</select>';

		return $html;
	}

	

	/**
	* Generates a select list of integers
	*
	* @param int The start integer
	* @param int The end integer
	* @param int The increment
	* @param string The value of the HTML name attribute
	* @param string Additional HTML attributes for the <select> tag
	* @param mixed The key that is selected
	* @param string The printf format to be applied to the number
	* @returns string HTML for the select list
	*/
	function IntegerList( $start, $end, $inc, $tag_name, $tag_attribs, $selected, $format="" )
	{
		$start 	= intval( $start );
		$end 	= intval( $end );
		$inc 	= intval( $inc );
		$arr 	= array();

		for ($i=$start; $i <= $end; $i+=$inc) {
			$fi = $format ? sprintf( "$format", $i ) : "$i";
			$arr[] = JHTML::_('select.option',  $fi, $fi );
		}

		return JHTML::_('select.genericlist',   $arr, $tag_name, $tag_attribs, 'value', 'text', $selected );
	}

	/**
	* Generates an HTML radio list
	*
	* @param array An array of objects
	* @param string The value of the HTML name attribute
	* @param string Additional HTML attributes for the <select> tag
	* @param mixed The key that is selected
	* @param string The name of the object variable for the option value
	* @param string The name of the object variable for the option text
	* @returns string HTML for the select list
	*/
	function RadioList( $arr, $tag_name, $tag_attribs, $selected=null, $key='value', $text='text', $idtag=false )
	{
		reset( $arr );
		$html = '';

		$id_text = $tag_name;
		if ( $idtag ) {
			$id_text = $idtag;
		}

		for ($i=0, $n=count( $arr ); $i < $n; $i++ ) {
			$k = $arr[$i]->$key;
			$t = $arr[$i]->$text;
			$id = ( isset($arr[$i]->id) ? @$arr[$i]->id : null);

			$extra = '';
			$extra .= $id ? " id=\"" . $arr[$i]->id . "\"" : '';
			if (is_array( $selected )) {
				foreach ($selected as $obj) {
					$k2 = $obj->$key;
					if ($k == $k2) {
						$extra .= " selected=\"selected\"";
						break;
					}
				}
			} else {
				$extra .= ($k == $selected ? " checked=\"checked\"" : '');
			}
			$html .= "\n\t<input type=\"radio\" name=\"$tag_name\" id=\"$id_text$k\" value=\"".$k."\"$extra $tag_attribs />";
			$html .= "\n\t<label for=\"$id_text$k\">$t</label>";
		}
		$html .= "\n";
		return $html;
	}

	/**
	* Generates a yes/no radio list
	*
	* @param string The value of the HTML name attribute
	* @param string Additional HTML attributes for the <select> tag
	* @param mixed The key that is selected
	* @returns string HTML for the radio list
	*/
	function BooleanList( $tag_name, $tag_attribs, $selected, $yes='yes', $no='no', $id=false ) {

		$arr = array(
			JHTML::_('select.option',  '0', JText::_( $no ) ),
			JHTML::_('select.option',  '1', JText::_( $yes ) )
		);
		return JHTML::_('select.radiolist',  $arr, $tag_name, $tag_attribs, (int) $selected, 'value', 'text', $id );
	}
}

