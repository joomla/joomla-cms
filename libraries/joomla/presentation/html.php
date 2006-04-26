<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
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
 * @subpackage	Presentation
 * @since		1.0
 */
class mosHTML {
	/**
	 * Write a <a></a> element
	 *
	 * @param string 	The relative URL to use for the href attribute
	 * @param string	The target attribute to use
	 * @param array		An associative array of attributes to add
	 * @param integer	Set the SSL functionality
	 * @since 1.5
	 */

	function Link($url, $text, $attribs = null, $ssl = 0) {
		global $mainframe;

		$href = josURL(ampReplace($url), $ssl, $mainframe->getCfg('sef'));

		if (is_array($attribs)) {
            $attribs = mosHTML::_implode_assoc('=', ' ', $attribs);
		 }

		return '<a href="'.$href.'" '.$attribs.'>'.$text.'</a>';
	}

	/**
	 * Write a <img></amg> element
	 *
	 * @param string 	The relative URL to use for the src attribute
	 * @param string	The target attribute to use
	 * @param array		An associative array of attributes to add
	 * @since 1.5
	 */
	function Image($url, $alt, $attribs = null) {
		global $mainframe;

		$src = substr( $url, 0, 4 ) != 'http' ? $mainframe->getCfg('live_site') . $url : $url;

		 if (is_array($attribs)) {
            $attribs = mosHTML::_implode_assoc('=', ' ', $attribs);
		 }

		return '<img src="'.$src.'" alt="'.$alt.'" '.$attribs.' />';

	}

	/**
	 * Write a <script></script> element
	 *
	 * @param string 	The relative URL to use for the src attribute
	 * @param string	The target attribute to use
	 * @param array		An associative array of attributes to add
	 * @since 1.5
	 */
	function Script($url, $attribs = null) {
		global $mainframe;

		$src = $mainframe->getCfg('live_site') . $url;

		 if (is_array($attribs)) {
            $attribs = mosHTML::_implode_assoc('=', ' ', $attribs);
		 }

		return '<script type="text/javascript" src="'.$src.'" '.$attribs.'></script>';
	}

	/**
	 * Write a <iframe></iframe> element
	 *
	 * @param string 	The relative URL to use for the src attribute
	 * @param string	The target attribute to use
	 * @param array		An associative array of attributes to add
	 * @param integer	Set the SSL functionality
	 * @since 1.5
	 */
	function Iframe($url, $name, $attribs = null, $ssl = 0)	{
		global $mainframe;

		$src = josURL(ampReplace($url), $ssl, $mainframe->getCfg('sef'));

		 if (is_array($attribs)) {
            $attribs = mosHTML::_implode_assoc('=', ' ', $attribs);
		 }

		return '<iframe src="'.$src.'" '.$attribs.' />';

	}

	function makeOption( $value, $text='', $value_name='value', $text_name='text' ) {
		$obj = new stdClass;
		$obj->$value_name = $value;
		$obj->$text_name = trim( $text ) ? $text : $value;
		return $obj;
	}

	function writableCell( $folder, $relative=1, $text='', $visible=1 ) {
		$writeable 		= '<b><font color="green">'. JText::_( 'Writeable' ) .'</font></b>';
		$unwriteable 	= '<b><font color="red">'. JText::_( 'Unwriteable' ) .'</font></b>';

		echo '<tr>';
		echo '<td class="item">';
		echo $text;
		if ( $visible ) {
			echo $folder . '/';
		}
		echo '</td>';
		echo '<td >';
		if ( $relative ) {
			echo is_writable( "../$folder" ) 	? $writeable : $unwriteable;
		} else {
			echo is_writable( "$folder" ) 		? $writeable : $unwriteable;
		}
		echo '</td>';
		echo '</tr>';
	}

	/**
	* Generates an HTML select list
	* @param array An array of objects
	* @param string The value of the HTML name attribute
	* @param string Additional HTML attributes for the <select> tag
	* @param string The name of the object variable for the option value
	* @param string The name of the object variable for the option text
	* @param mixed The key that is selected
	* @returns string HTML for the select list
	*/
	function selectList( &$arr, $tag_name, $tag_attribs, $key, $text, $selected=NULL, $idtag=false, $flag=false ) {
		reset( $arr );

        $id = $tag_name;
		if ( $idtag ) {
			$id = $idtag;
		}

		$html = '<select name="'. $tag_name .'" id="'. $id .'" '. $tag_attribs .'>';
		for ($i=0, $n=count( $arr ); $i < $n; $i++ ) {
			if( is_array( $arr[$i] ) ) {
				$k 		= $arr[$i][$key];
				$t	 	= $arr[$i][$text];
				$id 	= ( isset( $arr[$i]['id'] ) ? $arr[$i]['id'] : null );
			} else {
				$k 		= $arr[$i]->$key;
				$t	 	= $arr[$i]->$text;
				$id 	= ( isset( $arr[$i]->id ) ? $arr[$i]->id : null );
			}
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
			if($flag) $t = JText::_( $t );

			$html .= '<option value="'. $k .'" '. $extra .'>' . $t . '</option>';
		}
		$html .= '</select>';

		return $html;
	}

	/**
	* Writes a select list of integers
	* @param int The start integer
	* @param int The end integer
	* @param int The increment
	* @param string The value of the HTML name attribute
	* @param string Additional HTML attributes for the <select> tag
	* @param mixed The key that is selected
	* @param string The printf format to be applied to the number
	* @returns string HTML for the select list
	*/
	function integerSelectList( $start, $end, $inc, $tag_name, $tag_attribs, $selected, $format="" ) {
		$start 	= intval( $start );
		$end 	= intval( $end );
		$inc 	= intval( $inc );
		$arr 	= array();

		for ($i=$start; $i <= $end; $i+=$inc) {
			$fi = $format ? sprintf( "$format", $i ) : "$i";
			$arr[] = mosHTML::makeOption( $fi, $fi );
		}

		return mosHTML::selectList( $arr, $tag_name, $tag_attribs, 'value', 'text', $selected );
	}

	/**
	* Writes a select list of month names based on Language settings
	* @param string The value of the HTML name attribute
	* @param string Additional HTML attributes for the <select> tag
	* @param mixed The key that is selected
	* @returns string HTML for the select list values
	*/
	function monthSelectList( $tag_name, $tag_attribs, $selected ) {
		$arr = array(
			mosHTML::makeOption( '01', JText::_( 'JAN' ) ),
			mosHTML::makeOption( '02', JText::_( 'FEB' ) ),
			mosHTML::makeOption( '03', JText::_( 'MAR' ) ),
			mosHTML::makeOption( '04', JText::_( 'APR' ) ),
			mosHTML::makeOption( '05', JText::_( 'MAY' ) ),
			mosHTML::makeOption( '06', JText::_( 'JUN' ) ),
			mosHTML::makeOption( '07', JText::_( 'JUL' ) ),
			mosHTML::makeOption( '08', JText::_( 'AUG' ) ),
			mosHTML::makeOption( '09', JText::_( 'SEP' ) ),
			mosHTML::makeOption( '10', JText::_( 'OCT' ) ),
			mosHTML::makeOption( '11', JText::_( 'NOV' ) ),
			mosHTML::makeOption( '12', JText::_( 'DEC' ) )
		);

		return mosHTML::selectList( $arr, $tag_name, $tag_attribs, 'value', 'text', $selected );
	}

	/**
	* Generates an HTML select list from a tree based query list
	* @param array Source array with id and parent fields
	* @param array The id of the current list item
	* @param array Target array.  May be an empty array.
	* @param array An array of objects
	* @param string The value of the HTML name attribute
	* @param string Additional HTML attributes for the <select> tag
	* @param string The name of the object variable for the option value
	* @param string The name of the object variable for the option text
	* @param mixed The key that is selected
	* @returns string HTML for the select list
	*/
	function treeSelectList( &$src_list, $src_id, $tgt_list, $tag_name, $tag_attribs, $key, $text, $selected ) {

		// establish the hierarchy of the menu
		$children = array();
		// first pass - collect children
		foreach ($src_list as $v ) {
			$pt = $v->parent;
			$list = @$children[$pt] ? $children[$pt] : array();
			array_push( $list, $v );
			$children[$pt] = $list;
		}
		// second pass - get an indent list of the items
		$ilist = mosTreeRecurse( 0, '', array(), $children );

		// assemble menu items to the array
		$this_treename = '';
		foreach ($ilist as $item) {
			if ($this_treename) {
				if ($item->id != $src_id && strpos( $item->treename, $this_treename ) === false) {
					$tgt_list[] = mosHTML::makeOption( $item->id, $item->treename );
				}
			} else {
				if ($item->id != $src_id) {
					$tgt_list[] = mosHTML::makeOption( $item->id, $item->treename );
				} else {
					$this_treename = "$item->treename/";
				}
			}
		}
		// build the html select list
		return mosHTML::selectList( $tgt_list, $tag_name, $tag_attribs, $key, $text, $selected );
	}

	/**
	* Writes a yes/no select list
	* @param string The value of the HTML name attribute
	* @param string Additional HTML attributes for the <select> tag
	* @param mixed The key that is selected
	* @returns string HTML for the select list values
	*/
	function yesnoSelectList( $tag_name, $tag_attribs, $selected, $yes='yes', $no='no' ) {
		$arr = array(
			mosHTML::makeOption( 0, JText::_( $no ) ),
			mosHTML::makeOption( 1, JText::_( $yes ) ),
		);

		return mosHTML::selectList( $arr, $tag_name, $tag_attribs, 'value', 'text', $selected );
	}

	/**
	* Generates an HTML radio list
	* @param array An array of objects
	* @param string The value of the HTML name attribute
	* @param string Additional HTML attributes for the <select> tag
	* @param mixed The key that is selected
	* @param string The name of the object variable for the option value
	* @param string The name of the object variable for the option text
	* @returns string HTML for the select list
	*/
	function radioList( &$arr, $tag_name, $tag_attribs, $selected=null, $key='value', $text='text', $idtag=false ) {
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
	* Writes a yes/no radio list
	* @param string The value of the HTML name attribute
	* @param string Additional HTML attributes for the <select> tag
	* @param mixed The key that is selected
	* @returns string HTML for the radio list
	*/
	function yesnoRadioList( $tag_name, $tag_attribs, $selected, $yes='yes', $no='no', $id=false ) {

		$arr = array(
			mosHTML::makeOption( '0', JText::_( $no ) ),
			mosHTML::makeOption( '1', JText::_( $yes ) )
		);
		return mosHTML::radioList( $arr, $tag_name, $tag_attribs, $selected, 'value', 'text', $id );
	}

	/**
	* @param int The row index
	* @param int The record id
	* @param boolean
	* @param string The name of the form element
	* @return string
	*/
	function idBox( $rowNum, $recId, $checkedOut=false, $name='cid' ) {
		if ( $checkedOut ) {
			return '';
		} else {
			return '<input type="checkbox" id="cb'.$rowNum.'" name="'.$name.'[]" value="'.$recId.'" onclick="isChecked(this.checked);" />';
		}
	}

	function sortIcon( $text, $base_href, $field, $state='none' ) {
		$alts = array(
			'none' 	=> JText::_( 'No Sorting' ),
			'asc' 	=> JText::_( 'Sort Ascending' ),
			'desc' 	=> JText::_( 'Sort Descending' ),
		);

		$next_state = 'asc';
		if ($state == 'asc') {
			$next_state = 'desc';
		} else if ($state == 'desc') {
			$next_state = 'none';
		}

		if ($state == 'none') {
			$img = '';
		} else {
			$img = "<img src=\"images/sort_$state.png\" width=\"12\" height=\"12\" border=\"0\" alt=\"{$alts[$next_state]}\" />";
		}

		$html = "<a href=\"$base_href&field=$field&order=$next_state\">"
		. JText::_( $text )
		. '&nbsp;&nbsp;'
		. $img
		. "</a>";

		return $html;
	}

	/**
	 * Utility function to provide Warning Icons
	 *
	 * @package Joomla.Framework
	 * @param string Warning text
	 * @param string Box title
	 * @returns HTML code for Warning
	 * @since 1.5
	 */
	function WarningIcon($warning, $title='Joomla Warning')	{
		global $mainframe;

		$title 		= JText::_( 'Joomla Warning' );
		$mouseover 	= 'return overlib(\''. $warning .'\', CAPTION, \''. $title .'\', BELOW, RIGHT);';
		$url        = $mainframe->isAdmin() ? $mainframe->getSiteURL() : $mainframe->getBaseURL();

		$tip 		 = '<!--'. $title .'-->';
		$tip 		.= '<a onmouseover="'. $mouseover .'" onmouseout="return nd();">';
		$tip 		.= '<img src="'.$url.'includes/js/ThemeOffice/warning.png" border="0"  alt="" /></a>';

		return $tip;
	}

	/**
	* Writes Close Button
	*/
	function CloseButton ( &$params, $hide_js=NULL ) {

		// displays close button in Pop-up window
		if ( $params->get( 'popup' ) && !$hide_js ) {
			?>
			<div align="center" style="margin-top: 30px; margin-bottom: 30px;">
				<script type="text/javascript">
					document.write('<a href="#" onclick="javascript:window.close();"><span class="small"><?php echo JText::_( 'Close Window' );?></span></a>');
				</script>
				<?php
				if ( $_SERVER['HTTP_REFERER'] != "") {
					echo '<noscript>';
					echo '<a href="'. $_SERVER['HTTP_REFERER'] .'"><span class="small">'. JText::_( 'BACK' ) .'</span></a>';
					echo '</noscript>';
				}
				?>
			</div>
			<?php
		}
	}

	/**
	* Writes Back Button - Deprecated in 1.5
	* TODO : move to legacy classes
	*/
	function BackButton ( &$params, $hide_js=NULL ) {

		// Back Button
		if ( $params->get( 'back_button' ) && !$params->get( 'popup' ) && !$hide_js) {
			?>
			<div class="back_button">
				<a href='javascript:history.go(-1)'>
					<?php echo JText::_( 'BACK' ); ?></a>
			</div>
			<?php
		}
	}

	/**
	* Cleans text of all formating and scripting code
	*/
	function cleanText ( &$text ) {
		$text = preg_replace( "'<script[^>]*>.*?</script>'si", '', $text );
		$text = preg_replace( '/<a\s+.*?href="([^"]+)"[^>]*>([^<]+)<\/a>/is', '\2 (\1)', $text );
		$text = preg_replace( '/<!--.+?-->/', '', $text );
		$text = preg_replace( '/{.+?}/', '', $text );
		$text = preg_replace( '/&nbsp;/', ' ', $text );
		$text = preg_replace( '/&amp;/', ' ', $text );
		$text = preg_replace( '/&quot;/', ' ', $text );
		$text = strip_tags( $text );
		$text = htmlspecialchars( $text );
		return $text;
	}

	/**
	* Writes Print icon
	*/
	function PrintIcon( &$row, &$params, $hide_js, $link, $status=NULL ) {

    	if ( $params->get( 'print' )  && !$hide_js ) {
			// use default settings if none declared
			if ( !$status ) {
				$status = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no';
			}

			// checks template image directory for image, if non found default are loaded
			if ( $params->get( 'icons' ) ) {
				$image = mosAdminMenus::ImageCheck( 'printButton.png', '/images/M_images/', NULL, NULL, JText::_( 'Print' ), JText::_( 'Print' ) );
			} else {
				$image = JText::_( 'ICON_SEP' ) .'&nbsp;'. JText::_( 'Print' ) .'&nbsp;'. JText::_( 'ICON_SEP' );
			}

			if ( $params->get( 'popup' ) && !$hide_js ) {
				// Print Preview button - used when viewing page
				?>
				<script type="text/javascript">
					document.write('<td align="right" width="100%" class="buttonheading">');
					document.write('<a href="#" onclick="javascript:window.print(); return false" title="<?php echo JText::_( 'Print' );?>">');
					document.write('<?php echo $image;?>');
					document.write('</a>');
					document.write('</td>');
				</script>
				<?php
			} else {
				// Print Button - used in pop-up window
				?>
				<td align="right" width="100%" class="buttonheading">
				<a href="<?php echo $link; ?>" onclick="window.open('<?php echo $link; ?>','win2','<?php echo $status; ?>'); return false;" title="<?php echo JText::_( 'Print' );?>">
				<?php echo $image;?>
				</a>
				</td>
				<?php
			}
		}
	}

	/**
	 * Write a page header block
	 *
	 * @param string 	The relative URL to use for the src attribute
	 * @param string	The target attribute to use
	 * @param array		An associative array of attributes to add
	 * @param integer	Set the SSL functionality
	 * @since 1.5
	 */
	function Header($text, $iconClass)
	{
		//strip the extension
		$iconClass	= preg_replace('#\.[^.]*$#', '', $iconClass);
		$html		= null;

		$html	.= "<div class=\"header icon-48-$iconClass\">\n";
		$html	.= "$text\n";
		$html	.= "</div>\n";

		return $html;
	}

	/**
	* simple Javascript Cloaking
	* email cloacking
 	* by default replaces an email with a mailto link with email cloacked
	*/
	function emailCloaking( $mail, $mailto=1, $text='', $email=1 ) {

		// convert text
		$mail 			= mosHTML::_encoding_converter( $mail );
		// split email by @ symbol
		$mail			= explode( '@', $mail );
		$mail_parts		= explode( '.', $mail[1] );
		// random number
		$rand			= rand( 1, 100000 );

		$replacement 	= "\n <script language='JavaScript' type='text/javascript'>";
		$replacement 	.= "\n <!--";
		$replacement 	.= "\n var prefix = '&#109;a' + 'i&#108;' + '&#116;o';";
		$replacement 	.= "\n var path = 'hr' + 'ef' + '=';";
		$replacement 	.= "\n var addy". $rand ." = '". @$mail[0] ."' + '&#64;';";
		$replacement 	.= "\n addy". $rand ." = addy". $rand ." + '". implode( "' + '&#46;' + '", $mail_parts ) ."';";
		
		if ( $mailto ) {
			// special handling when mail text is different from mail addy
			if ( $text ) {
				if ( $email ) {
					// convert text
					$text 			= mosHTML::_encoding_converter( $text );
					// split email by @ symbol
					$text 			= explode( '@', $text );
					$text_parts		= explode( '.', $text[1] );
					$replacement 	.= "\n var addy_text". $rand ." = '". @$text[0] ."' + '&#64;' + '". implode( "' + '&#46;' + '", @$text_parts ) ."';";
				} else {
					$replacement 	.= "\n var addy_text". $rand ." = '". $text ."';";
				}
				$replacement 	.= "\n document.write( '<a ' + path + '\'' + prefix + ':' + addy". $rand ." + '\'>' );";
				$replacement 	.= "\n document.write( addy_text". $rand ." );";
				$replacement 	.= "\n document.write( '<\/a>' );";
			} else {
				$replacement 	.= "\n document.write( '<a ' + path + '\'' + prefix + ':' + addy". $rand ." + '\'>' );";
				$replacement 	.= "\n document.write( addy". $rand ." );";
				$replacement 	.= "\n document.write( '<\/a>' );";
			}
		} else {
			$replacement 	.= "\n document.write( addy". $rand ." );";
		}
		$replacement 	.= "\n //-->";
		$replacement 	.= '\n </script>';
		
		// XHTML compliance `No Javascript` text handling
		$replacement 	.= "\n <script language='JavaScript' type='text/javascript'>";
		$replacement 	.= "\n <!--";
		$replacement 	.= "\n document.write( '<span style=\"display: none;\">' );";
		$replacement 	.= "\n //-->";
		$replacement 	.= "\n </script>";
		$replacement 	.= JText::_( 'CLOAKING' );
		$replacement 	.= "\n <script language='JavaScript' type='text/javascript'>";
		$replacement 	.= "\n <!--";
		$replacement 	.= "\n document.write( '</' );";
		$replacement 	.= "\n document.write( 'span>' );";
		$replacement 	.= "\n //-->";
		$replacement 	.= "\n </script>";

		return $replacement;
	}

	function _encoding_converter( $text ) {
		// replace vowels with character encoding
		$text 	= str_replace( 'a', '&#97;', $text );
		$text 	= str_replace( 'e', '&#101;', $text );
		$text 	= str_replace( 'i', '&#105;', $text );
		$text 	= str_replace( 'o', '&#111;', $text );
		$text	= str_replace( 'u', '&#117;', $text );

		return $text;
	}

	function _implode_assoc($inner_glue = "=", $outer_glue = "\n", $array = null, $keepOuterKey = false)
    {
        $output = array();

        foreach($array as $key => $item)
        if (is_array ($item)) {
            if ($keepOuterKey)
                $output[] = $key;
            // This is value is an array, go and do it again!
            $output[] = implode_assoc($inner_glue, $outer_glue, $item, $keepOuterKey);
        } else
            $output[] = $key . $inner_glue . $item;

        return implode($outer_glue, $output);
    }
}

/**
 * Utility class for drawing common HTML elements
 *
 * @static
 * @package 	Joomla.Framework
 * @subpackage	Presentation
 * @since		1.0
 */
class mosCommonHTML {

	function ContentLegend( ) {
		?>
		<table cellspacing="0" cellpadding="4" border="0" align="center">
		<tr align="center">
			<td>
			<img src="images/publish_y.png" width="12" height="12" border="0" alt="<?php echo JText::_( 'Pending' ); ?>" />
			</td>
			<td>
			<?php echo JText::_( 'Published, but is' ); ?> <u><?php echo JText::_( 'Pending' ); ?></u> |
			</td>
			<td>
			<img src="images/publish_g.png" width="12" height="12" border="0" alt="<?php echo JText::_( 'Visible' ); ?>" />
			</td>
			<td>
			<?php echo JText::_( 'Published and is' ); ?> <u><?php echo JText::_( 'Current' ); ?></u> |
			</td>
			<td>
			<img src="images/publish_r.png" width="12" height="12" border="0" alt="<?php echo JText::_( 'Finished' ); ?>" />
			</td>
			<td>
			<?php echo JText::_( 'Published, but has' ); ?> <u><?php echo JText::_( 'Expired' ); ?></u> |
			</td>
			<td>
			<img src="images/publish_x.png" width="12" height="12" border="0" alt="<?php echo JText::_( 'Finished' ); ?>" />
			</td>
			<td>
			<?php echo JText::_( 'Not Published' ); ?>
			</td>
		</tr>
		<tr>
			<td colspan="8" align="center">
			<?php echo JText::_( 'Click on icon to toggle state.' ); ?>
			</td>
		</tr>
		</table>
		<?php
	}

	function menuLinksContent( &$menus ) {
		foreach( $menus as $menu ) {
			?>
			<tr>
				<td colspan="2">
					<hr />
				</td>
			</tr>
			<tr>
				<td width="90" valign="top">
					<?php echo JText::_( 'Menu' ); ?>
				</td>
				<td>
					<a href="javascript:go2('go2menu','<?php echo $menu->menutype; ?>');" title="<?php echo JText::_( 'Go to Menu' ); ?>">
						<?php echo $menu->menutype; ?></a>
				</td>
			</tr>
			<tr>
				<td width="90" valign="top">
				<?php echo JText::_( 'Link Name' ); ?>
				</td>
				<td>
					<strong>
					<a href="javascript:go2('go2menuitem','<?php echo $menu->menutype; ?>','<?php echo $menu->id; ?>');" title="<?php echo JText::_( 'Go to Menu Item' ); ?>">
						<?php echo $menu->name; ?></a>
					</strong>
				</td>
			</tr>
			<tr>
				<td width="90" valign="top">
					<?php echo JText::_( 'State' ); ?>
				</td>
				<td>
					<?php
					switch ( $menu->published ) {
						case -2:
							echo '<font color="red">'. JText::_( 'Trashed' ) .'</font>';
							break;
						case 0:
							echo JText::_( 'UnPublished' );
							break;
						case 1:
						default:
							echo '<font color="green">'. JText::_( 'Published' ) .'</font>';
							break;
					}
					?>
				</td>
			</tr>
			<?php
		}
		?>
		<tr>
			<td colspan="2">
				<input type="hidden" name="menu" value="" />
				<input type="hidden" name="menuid" value="" />
			</td>
		</tr>
		<?php
	}

	function menuLinksSecCat( &$menus ) {
		foreach( $menus as $menu ) {
			?>
			<tr>
				<td colspan="2">
					<hr />
				</td>
			</tr>
			<tr>
				<td width="90" valign="top">
					<?php echo JText::_( 'Menu' ); ?>
				</td>
				<td>
					<a href="javascript:go2('go2menu','<?php echo $menu->menutype; ?>');" title="<?php echo JText::_( 'Go to Menu' ); ?>">
						<?php echo $menu->menutype; ?></a>
				</td>
			</tr>
			<tr>
				<td width="90" valign="top">
					<?php echo JText::_( 'Type' ); ?>
				</td>
				<td>
					<?php echo $menu->type; ?>
				</td>
			</tr>
			<tr>
				<td width="90" valign="top">
					<?php echo JText::_( 'Item Name' ); ?>
				</td>
				<td>
					<strong>
					<a href="javascript:go2('go2menuitem','<?php echo $menu->menutype; ?>','<?php echo $menu->id; ?>');" title="<?php echo JText::_( 'Go to Menu Item' ); ?>">
						<?php echo $menu->name; ?></a>
					</strong>
				</td>
			</tr>
			<tr>
				<td width="90" valign="top">
					<?php echo JText::_( 'State' ); ?>
				</td>
				<td>
					<?php
					switch ( $menu->published ) {
						case -2:
							echo '<font color="red">'. JText::_( 'Trashed' ) .'</font>';
							break;
						case 0:
							echo JText::_( 'UnPublished' );
							break;
						case 1:
						default:
							echo '<font color="green">'. JText::_( 'Published' ) .'</font>';
							break;
					}
					?>
				</td>
			</tr>
			<?php
		}
		?>
		<tr>
			<td colspan="2">
				<input type="hidden" name="menu" value="" />
				<input type="hidden" name="menuid" value="" />
			</td>
		</tr>
		<?php
	}

	function checkedOut( &$row, $overlib=1 ) {

		$hover = '';
		if ( $overlib ) {

			$text = addslashes(htmlspecialchars($row->editor));

			$date 				= mosFormatDate( $row->checked_out_time, '%A, %d %B %Y' );
			$time				= mosFormatDate( $row->checked_out_time, '%H:%M' );
			$checked_out_text 	= '<table>';
			$checked_out_text 	.= '<tr><td>'. $text .'</td></tr>';
			$checked_out_text 	.= '<tr><td>'. $date .'</td></tr>';
			$checked_out_text 	.= '<tr><td>'. $time .'</td></tr>';
			$checked_out_text 	.= '</table>';

			$hover = 'onMouseOver="return overlib(\''. $checked_out_text .'\', CAPTION, \''. JText::_( 'Checked Out' ) .'\', BELOW, RIGHT);" onMouseOut="return nd();"';
		}
		$checked	 		= '<img src="images/checked_out.png" '. $hover .'/>';

		return $checked;
	}

	/*
	* Loads all necessary files for JS Overlib tooltips
	*/
	function loadOverlib() {
		global $mainframe;

		$url = $mainframe->isAdmin() ? $mainframe->getSiteURL() : $mainframe->getBaseURL();

		if ( !$mainframe->get( 'loadOverlib' ) ) {
		// check if this function is already loaded
			$doc =& $mainframe->getDocument();
			$doc->addScript($url.'includes/js/overlib_mini.js');
			$doc->addScript($url.'includes/js/overlib_hideform_mini.js');
			?>
			<div id="overDiv" style="position:absolute; visibility:hidden; z-index:10000;"></div>
			<?php
			// change state so it isnt loaded a second time
			$mainframe->set( 'loadOverlib', true );
		}
	}

	/*
	* Loads all necessary files for JS Calendar
	*/
	function loadCalendar() {
		global $mainframe;

		$doc =& $mainframe->getDocument();
		$url = $mainframe->isAdmin() ? $mainframe->getSiteURL() : $mainframe->getBaseURL();

		$doc->addStyleSheet( $url. 'includes/js/calendar/calendar-mos.css', 'text/css', null, array(' title' => '"'. JText::_( 'green' ) .'"',' media' => '"all"' ));
		$doc->addScript( $url. 'includes/js/calendar/calendar_mini.js' );
		$doc->addScript( $url. 'includes/js/calendar/lang/calendar-en.js' );
	}

	function AccessProcessing( &$row, $i ) {
		if ( !$row->access ) {
			$color_access = 'style="color: green;"';
			$task_access = 'accessregistered';
		} else if ( $row->access == 1 ) {
			$color_access = 'style="color: red;"';
			$task_access = 'accessspecial';
		} else {
			$color_access = 'style="color: black;"';
			$task_access = 'accesspublic';
		}

		$href = '
		<a href="javascript:void(0);" onclick="return listItemTask(\'cb'. $i .'\',\''. $task_access .'\')" '. $color_access .'>
		'. $row->groupname .'
		</a>'
		;

		return $href;
	}

	function CheckedOutProcessing( &$row, $i ) {
		global $my;

		if ( $row->checked_out ) {
			$checked = mosCommonHTML::checkedOut( $row );
		} else {
			$checked = mosHTML::idBox( $i, $row->id, ($row->checked_out && $row->checked_out != $my->id ) );
		}

		return $checked;
	}

	function PublishedProcessing( &$row, $i, $imgY='tick.png', $imgX='publish_x.png' ) {

		$img 	= $row->published ? $imgY : $imgX;
		$task 	= $row->published ? 'unpublish' : 'publish';
		$alt 	= $row->published ? JText::_( 'Published' ) : JText::_( 'Unpublished' );
		$action	= $row->published ? 'Unpublish Item' : 'Publish item';

		$href = '
		<a href="javascript:void(0);" onclick="return listItemTask(\'cb'. $i .'\',\''. $task .'\')" title="'. $action .'">
		<img src="images/'. $img .'" border="0" alt="'. $alt .'" />
		</a>'
		;

		return $href;
	}
	function selectState( $filter_state=NULL, $published='Published', $unpublished='Unpublished' )	{
		$state[] = mosHTML::makeOption( '', '- '. JText::_( 'Select State' ) .' -' );
		$state[] = mosHTML::makeOption( 'P', JText::_( $published ) );
		$state[] = mosHTML::makeOption( 'U', JText::_( $unpublished ) );

		return mosHTML::selectList( $state, 'filter_state', 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', $filter_state );
	}

	function saveorderButton( $rows, $image='filesave.png' ) {
		$image = mosAdminMenus::ImageCheckAdmin( $image, '/images/', NULL, NULL, JText::_( 'Save Order' ), '', 1 );
		?>
		<a href="javascript:saveorder(<?php echo count( $rows )-1; ?>)">
			<?php echo $image; ?></a>
		<?php
	}

	function tableOrdering( $text, $ordering, &$lists, $task=NULL ) {
		?>
		<a href="javascript:tableOrdering('<?php echo $ordering; ?>','<?php echo $lists['order_Dir']; ?>','<?php echo $task; ?>');" title="<?php echo JText::_( 'Order by' ); ?> <?php echo JText::_( $text ); ?>">
			<?php echo JText::_( $text ); ?>
			<?php mosCommonHTML::tableOrdering_img( $ordering, $lists ); ?></a>
		<?php
	}
	function tableOrdering_img( $current, &$lists ) {
		if ( $current == $lists['order']) {
			if ( $lists['order_Dir'] == 'ASC' ) {
				$image = 'sort_desc.png';
			} else {
				$image = 'sort_asc.png';
			}
			echo mosAdminMenus::ImageCheckAdmin( $image, '/images/', NULL, NULL, '', '', 1 );
		}
	}
}

/**
 * Utility class for drawing admin menu HTML elements
 *
 * @static
 * @package 	Joomla.Framework
 * @subpackage	Presentation
 * @since		1.0
 */
class mosAdminMenus {
	/**
	* build the select list for Menu Ordering
	*/
	function Ordering( &$row, $id ) {
		global $database;

		if ( $id ) {
			$query = "SELECT ordering AS value, name AS text"
			. "\n FROM #__menu"
			. "\n WHERE menutype = '$row->menutype'"
			. "\n AND parent = $row->parent"
			. "\n AND published != -2"
			. "\n ORDER BY ordering"
			;
			$order = mosGetOrderingList( $query );
			$ordering = mosHTML::selectList( $order, 'ordering', 'class="inputbox" size="1"', 'value', 'text', intval( $row->ordering ) );
		} else {
			$ordering = '<input type="hidden" name="ordering" value="'. $row->ordering .'" />'. JText::_( 'DESCNEWITEMSLAST' );
		}
		return $ordering;
	}

	/**
	* build the select list for access level
	*/
	function Access( &$row ) {
		global $database;

		$query = "SELECT id AS value, name AS text"
		. "\n FROM #__groups"
		. "\n ORDER BY id"
		;
		$database->setQuery( $query );
		$groups = $database->loadObjectList();
		$access = mosHTML::selectList( $groups, 'access', 'class="inputbox" size="3"', 'value', 'text', intval( $row->access ), '', 1 );

		return $access;
	}

	/**
	* build the select list for parent item
	*/
	function Parent( &$row ) {
		global $database;

		$id = '';
		if ( $row->id ) {
			$id = "\n AND id != $row->id";
		}

		// get a list of the menu items
		// excluding the current menu item and its child elements
		$query = "SELECT m.*"
		. "\n FROM #__menu m"
		. "\n WHERE menutype = '$row->menutype'"
		. "\n AND published != -2"
		. $id
		. "\n ORDER BY parent, ordering"
		;
		$database->setQuery( $query );
		$mitems = $database->loadObjectList();

		// establish the hierarchy of the menu
		$children = array();

		if ( $mitems ) {
			// first pass - collect children
			foreach ( $mitems as $v ) {
				$pt 	= $v->parent;
				$list 	= @$children[$pt] ? $children[$pt] : array();
				array_push( $list, $v );
				$children[$pt] = $list;
			}
		}

		// second pass - get an indent list of the items
		$list = mosTreeRecurse( 0, '', array(), $children, 9999, 0, 0 );

		// assemble menu items to the array
		$mitems 	= array();
		$mitems[] 	= mosHTML::makeOption( '0', JText::_( 'Top' ) );

		foreach ( $list as $item ) {
			$mitems[] = mosHTML::makeOption( $item->id, '&nbsp;&nbsp;&nbsp;'. $item->treename );
		}

		$output = mosHTML::selectList( $mitems, 'parent', 'class="inputbox" size="10"', 'value', 'text', $row->parent );

		return $output;
	}

	/**
	* build a radio button option for published state
	*/
	function Published( &$row ) {
		$published = mosHTML::yesnoRadioList( 'published', 'class="inputbox"', $row->published );
		return $published;
	}

	/**
	* build the link/url of a menu item
	*/
	function Link( &$row, $id, $link=NULL ) {
		if ( $id ) {
			if ( $link ) {
				$link = $row->link;
			} else {
				$link = $row->link .'&amp;Itemid='. $row->id;
			}
		} else {
			$link = NULL;
		}
		return $link;
	}

	/**
	* build the select list for target window
	*/
	function Target( &$row ) {
		$click[] = mosHTML::makeOption( '0', JText::_( 'Parent Window With Browser Navigation' ) );
		$click[] = mosHTML::makeOption( '1', JText::_( 'New Window With Browser Navigation' ) );
		$click[] = mosHTML::makeOption( '2', JText::_( 'New Window Without Browser Navigation' ) );
		$target = mosHTML::selectList( $click, 'browserNav', 'class="inputbox" size="4"', 'value', 'text', intval( $row->browserNav ) );
		return $target;
	}

	function MenuOutputTop( &$lists, &$menu, $text=NULL, $tip=NULL ) {
		?>
		<tr>
			<th colspan="2">
			<?php echo JText::_( 'Details' ); ?>
			</th>
		</tr>
		<tr>
			<td width="20%" align="right">
			<?php echo JText::_( 'Menu Type' ); ?>:
			</td>
			<td width="80%">
			<?php echo JText::_( $text ); ?>
			</td>
		</tr>
		<tr>
			<td valign="top" align="right">
			<?php echo JText::_( 'Published' ); ?>:
			</td>
			<td>
			<?php echo $lists['published']; ?>
			</td>
		</tr>
		<tr>
			<td align="right">
			<?php echo JText::_( 'Name' ); ?>:
			</td>
			<td>
			<input class="inputbox" type="text" name="name" size="50" maxlength="100" value="<?php echo $menu->name; ?>" />
			<?php
			if ( !$menu->id && $tip ) {
				echo mosToolTip( JText::_( 'TIPIFLEAVEBLANKCAT' ) );
			}
			?>
			</td>
		</tr>
		<?php
	}

	function MenuOutputBottom( &$lists, &$menu ) {
		?>
		<tr>
			<td align="right">
			<?php echo JText::_( 'Url' ); ?>:
			</td>
			<td>
			<?php echo ampReplace($lists['link']); ?>
			</td>
		</tr>
		<tr>
			<td valign="top" align="right">
			<?php echo JText::_( 'Ordering' ); ?>:
			</td>
			<td>
			<?php echo $lists['ordering']; ?>
			</td>
		</tr>
		<tr>
			<td valign="top" align="right">
			<?php echo JText::_( 'Access Level' ); ?>:
			</td>
			<td>
			<?php echo $lists['access']; ?>
			</td>
		</tr>
		<tr>
			<td align="right" valign="top">
			<?php echo JText::_( 'Parent Item' ); ?>:
			</td>
			<td>
			<?php echo $lists['parent']; ?>
			</td>
		</tr>
		<?php
	}

	function MenuOutputParams( &$params, $menu, $tip=NULL ) {
		?>
		<td width="40%">
			<table class="adminform">
			<tr>
				<th>
				<?php echo JText::_( 'Parameters' ); ?>
				</th>
			</tr>
			<tr>
				<td>
				<?php
				if ($tip) {
					if ($menu->id) {
						echo $params->render();
					} else {
						?>
						<strong>
						<?php echo JText::_( 'TIPPARAMLISTMENUITEM' ); ?>
						</strong>
						<?php
					}
				} else {
					echo $params->render();
				}
				?>
				</td>
			</tr>
			</table>
		</td>
		<?php
	}

	/**
	* build the multiple select list for Menu Links/Pages
	*/
	function MenuLinks( &$lookup, $all=NULL, $none=NULL, $unassigned=1 ) {
		global $database;

		// get a list of the menu items
		$query = "SELECT m.*"
		. "\n FROM #__menu AS m"
		. "\n WHERE m.published = 1"
		//. "\n AND m.type != 'separator'"
		. "\n AND NOT ("
			. "\n ( m.type = 'url' )"
			. "\n AND ( m.link LIKE '%index.php%' )"
			. "\n AND ( m.link LIKE '%Itemid=%' )"
		. "\n )"
		. "\n ORDER BY m.menutype, m.parent, m.ordering"
		;
		$database->setQuery( $query );
		$mitems = $database->loadObjectList();
		$mitems_temp = $mitems;

		// establish the hierarchy of the menu
		$children = array();
		// first pass - collect children
		foreach ( $mitems as $v ) {
			$id = $v->id;
			$pt = $v->parent;
			$list = @$children[$pt] ? $children[$pt] : array();
			array_push( $list, $v );
			$children[$pt] = $list;
		}
		// second pass - get an indent list of the items
		$list = mosTreeRecurse( intval( $mitems[0]->parent ), '', array(), $children, 9999, 0, 0 );

		// Code that adds menu name to Display of Page(s)
		$text_count = "0";
		$mitems_spacer = $mitems_temp[0]->menutype;
		foreach ($list as $list_a) {
			foreach ($mitems_temp as $mitems_a) {
				if ($mitems_a->id == $list_a->id) {
					// Code that inserts the blank line that seperates different menus
					if ($mitems_a->menutype <> $mitems_spacer) {
						$list_temp[] = mosHTML::makeOption( -999, '----' );
						$mitems_spacer = $mitems_a->menutype;
					}
					$text = $mitems_a->menutype." | ".$list_a->treename;
					$list_temp[] = mosHTML::makeOption( $list_a->id, $text );
					if ( JString::strlen($text) > $text_count) {
						$text_count = JString::strlen($text);
					}
				}
			}
		}
		$list = $list_temp;

		$mitems = array();
		if ( $all ) {
			// prepare an array with 'all' as the first item
			$mitems[] = mosHTML::makeOption( 0, JText::_( 'All' ) );
			// adds space, in select box which is not saved
			$mitems[] = mosHTML::makeOption( -999, '----' );
		}
		if ( $none ) {
			// prepare an array with 'all' as the first item
			$mitems[] = mosHTML::makeOption( -999, JText::_( 'None' ) );
			// adds space, in select box which is not saved
			$mitems[] = mosHTML::makeOption( -999, '----' );
		}
		if ( $none ) {
			// prepare an array with 'all' as the first item
			$mitems[] = mosHTML::makeOption( 99999999, JText::_( 'Unassigned' ) );
			// adds space, in select box which is not saved
			$mitems[] = mosHTML::makeOption( -999, '----' );
		}
		// append the rest of the menu items to the array
		foreach ($list as $item) {
			$mitems[] = mosHTML::makeOption( $item->value, $item->text );
		}
		$pages = mosHTML::selectList( $mitems, 'selections[]', 'class="inputbox" size="26" multiple="multiple"', 'value', 'text', $lookup, 'selections' );
		return $pages;
	}


	/**
	* build the select list to choose a category
	*/
	function Category( &$menu, $id, $javascript='' ) {
		global $database;

		$query = "SELECT c.id AS `value`, c.section AS `id`, CONCAT_WS( ' / ', s.title, c.title) AS `text`"
		. "\n FROM #__sections AS s"
		. "\n INNER JOIN #__categories AS c ON c.section = s.id"
		. "\n WHERE s.scope = 'content'"
		. "\n ORDER BY s.name, c.name"
		;
		$database->setQuery( $query );
		$rows = $database->loadObjectList();
		$category = '';

		$category .= mosHTML::selectList( $rows, 'componentid', 'class="inputbox" size="10"'. $javascript, 'value', 'text', $menu->componentid );
		$category .= '<input type="hidden" name="link" value="" />';

		return $category;
	}

	/**
	* build the select list to choose a section
	*/
	function Section( &$menu, $id, $all=0 ) {
		global $database;

		$query = "SELECT s.id AS `value`, s.id AS `id`, s.title AS `text`"
		. "\n FROM #__sections AS s"
		. "\n WHERE s.scope = 'content'"
		. "\n ORDER BY s.name"
		;
		$database->setQuery( $query );
		if ( $all ) {
			$rows[] = mosHTML::makeOption( 0, '- '. JText::_( 'All Sections' ) .' -' );
			$rows = array_merge( $rows, $database->loadObjectList() );
		} else {
			$rows = $database->loadObjectList();
		}

		$section = mosHTML::selectList( $rows, 'componentid', 'class="inputbox" size="10"', 'value', 'text', $menu->componentid );
		$section .= '<input type="hidden" name="link" value="" />';

		return $section;
	}

	/**
	* build the select list to choose a component
	*/
	function Component( &$menu, $id ) {
		global $database;

		$query = "SELECT c.id AS value, c.name AS text, c.link"
		. "\n FROM #__components AS c"
		. "\n WHERE c.link <> ''"
		. "\n ORDER BY c.name"
		;
		$database->setQuery( $query );
		$rows = $database->loadObjectList( );

		$component = mosHTML::selectList( $rows, 'componentid', 'class="inputbox" size="10"', 'value', 'text', $menu->componentid, '', 1 );

		return $component;
	}

	/**
	* build the select list to choose a component
	*/
	function ComponentName( &$menu, $id ) {
		global $database;

		$query = "SELECT c.id AS value, c.name AS text, c.link"
		. "\n FROM #__components AS c"
		. "\n WHERE c.link <> ''"
		. "\n ORDER BY c.name"
		;
		$database->setQuery( $query );
		$rows = $database->loadObjectList( );

		$component = 'Component';
		foreach ( $rows as $row ) {
			if ( $row->value == $menu->componentid ) {
				$component = JText::_( $row->text );
			}
		}

		return $component;
	}

	/**
	* build the select list to choose an image
	*/
	function Images( $name, &$active, $javascript=NULL, $directory=NULL ) {
		if ( !$directory ) {
			$directory = '/images/stories';
		}

		if ( !$javascript ) {
			$javascript = "onchange=\"javascript:if (document.forms[0]." . $name . ".options[selectedIndex].value!='') {document.imagelib.src='..$directory' + document.forms[0]." . $name . ".options[selectedIndex].value} else {document.imagelib.src='../images/blank.png'}\"";
		}

		$imageFiles = mosReadDirectory( JPATH_SITE . $directory );
		$images 	= array(  mosHTML::makeOption( '', '- '. JText::_( 'Select Image' ) .' -' ) );
		foreach ( $imageFiles as $file ) {
			if ( eregi( "bmp|gif|jpg|png", $file ) ) {
				$images[] = mosHTML::makeOption( $file );
			}
		}
		$images = mosHTML::selectList( $images, $name, 'class="inputbox" size="1" '. $javascript, 'value', 'text', $active );

		return $images;
	}

	/**
	* build the select list for Ordering of a specified Table
	*/
	function SpecificOrdering( &$row, $id, $query, $neworder=0 ) {
		global $database;

		if ( $id ) {
			$order = mosGetOrderingList( $query );
			$ordering = mosHTML::selectList( $order, 'ordering', 'class="inputbox" size="1"', 'value', 'text', intval( $row->ordering ) );
		} else {
    		if ( $neworder ) {
    			$text = JText::_( 'descNewItemsFirst' );
    		} else {
    			$text = JText::_( 'descNewItemsLast' );
    		}
			$ordering = '<input type="hidden" name="ordering" value="'. $row->ordering .'" />'. $text;
		}
		return $ordering;
	}

	/**
	* Select list of active users
	*/
	function UserSelect( $name, $active, $nouser=0, $javascript=NULL, $order='name', $reg=1 ) {
		global $database, $my;

		$and = '';
		if ( $reg ) {
		// does not include registered users in the list
			$and = "\n AND gid > 18";
		}

		$query = "SELECT id AS value, name AS text"
		. "\n FROM #__users"
		. "\n WHERE block = 0"
		. $and
		. "\n ORDER BY $order"
		;
		$database->setQuery( $query );
		if ( $nouser ) {
			$users[] = mosHTML::makeOption( '0', '- '. JText::_( 'No User' ) .' -' );
			$users = array_merge( $users, $database->loadObjectList() );
		} else {
			$users = $database->loadObjectList();
		}

		$users = mosHTML::selectList( $users, $name, 'class="inputbox" size="1" '. $javascript, 'value', 'text', $active );

		return $users;
	}

	/**
	* Select list of positions - generally used for location of images
	*/
	function Positions( $name, $active=NULL, $javascript=NULL, $none=1, $center=1, $left=1, $right=1, $id=false ) {

		if ( $none ) {
			$pos[] = mosHTML::makeOption( '', JText::_( 'None' ) );
		}
		if ( $center ) {
			$pos[] = mosHTML::makeOption( 'center', JText::_( 'Center' ) );
		}
		if ( $left ) {
			$pos[] = mosHTML::makeOption( 'left', JText::_( 'Left' ) );
		}
		if ( $right ) {
			$pos[] = mosHTML::makeOption( 'right', JText::_( 'Right' ) );
		}

		$positions = mosHTML::selectList( $pos, $name, 'class="inputbox" size="1"'. $javascript, 'value', 'text', $active, $id );

		return $positions;
	}

	/**
	* Select list of active categories for components
	*/
	function ComponentCategory( $name, $section, $active=NULL, $javascript=NULL, $order='ordering', $size=1, $sel_cat=1 ) {
		global $database;

		$query = "SELECT id AS value, name AS text"
		. "\n FROM #__categories"
		. "\n WHERE section = '$section'"
		. "\n AND published = 1"
		. "\n ORDER BY $order"
		;
		$database->setQuery( $query );
		if ( $sel_cat ) {
			$categories[] = mosHTML::makeOption( '0', '- '. JText::_( 'Select a Category' ) .' -' );
			$categories = array_merge( $categories, $database->loadObjectList() );
		} else {
			$categories = $database->loadObjectList();
		}

		if ( count( $categories ) < 1 ) {
			josRedirect( 'index2.php?option=com_categories&section='. $section, JText::_( 'You must create a category first.' ) );
		}

		$category = mosHTML::selectList( $categories, $name, 'class="inputbox" size="'. $size .'" '. $javascript, 'value', 'text', $active );

		return $category;
	}

	/**
	* Select list of active sections
	*/
	function SelectSection( $name, $active=NULL, $javascript=NULL, $order='ordering' ) {
		global $database;

		$categories[] = mosHTML::makeOption( '-1', '- '. JText::_( 'Select Section' ) .' -' );
		$categories[] = mosHTML::makeOption( '0', JText::_( 'Uncategorized' ) );
		$query = "SELECT id AS value, title AS text"
		. "\n FROM #__sections"
		. "\n WHERE published = 1"
		. "\n ORDER BY $order"
		;
		$database->setQuery( $query );
		$sections = array_merge( $categories, $database->loadObjectList() );

		$category = mosHTML::selectList( $sections, $name, 'class="inputbox" size="1" '. $javascript, 'value', 'text', $active );

		return $category;
	}

	/**
	* Select list of menu items for a specific menu
	*/
	function Links2Menu( $type, $and ) {
		global $database;

		$query = "SELECT *"
		. "\n FROM #__menu"
		. "\n WHERE type = '$type'"
		. "\n AND published = 1"
		. $and
		;
		$database->setQuery( $query );
		$menus = $database->loadObjectList();

		return $menus;
	}

	/**
	* Select list of menus
	*/
	function MenuSelect( $name='menuselect', $javascript=NULL ) {
		global $database;

		$query = "SELECT params"
		. "\n FROM #__modules"
		. "\n WHERE module = 'mod_mainmenu'"
		;
		$database->setQuery( $query );
		$menus = $database->loadObjectList();
		$total = count( $menus );
		$menuselect = array();
		for( $i = 0; $i < $total; $i++ ) {
			$registry = new JRegistry();
			$registry->loadINI($menus[$i]->params);
			$params = $registry->toObject( );

			$menuselect[$i]->value 	= $params->menutype;
			$menuselect[$i]->text 	= $params->menutype;
		}
		// sort array of objects
		SortArrayObjects( $menuselect, 'text', 1 );

		$menus = mosHTML::selectList( $menuselect, $name, 'class="inputbox" size="10" '. $javascript, 'value', 'text' );

		return $menus;
	}

	/**
	* Internal function to recursive scan the media manager directories
	* @param string Path to scan
	* @param string root path of this folder
	* @param array  Value array of all existing folders
	* @param array  Value array of all existing images
	*/
	function ReadImages( $imagePath, $folderPath, &$folders, &$images ) {
		$imgFiles = mosReadDirectory( $imagePath );

		foreach ($imgFiles as $file) {
			$ff_ 	= $folderPath . $file .'/';
			$ff 	= $folderPath . $file;
			$i_f 	= $imagePath .'/'. $file;

			if ( is_dir( $i_f ) && $file <> 'CVS' && $file <> '.svn') {
				$folders[] = mosHTML::makeOption( $ff_ );
				mosAdminMenus::ReadImages( $i_f, $ff_, $folders, $images );
			} else if ( eregi( "bmp|gif|jpg|png", $file ) && is_file( $i_f ) ) {
				// leading / we don't need
				$imageFile = substr( $ff, 1 );
				$images[$folderPath][] = mosHTML::makeOption( $imageFile, $file );
			}
		}
	}

	function GetImageFolders( &$folders, $path ) {
		$javascript 	= "onchange=\"changeDynaList( 'imagefiles', folderimages, document.adminForm.folders.options[document.adminForm.folders.selectedIndex].value, 0, 0);  previewImage( 'imagefiles', 'view_imagefiles', '$path/' );\"";
		$getfolders 	= mosHTML::selectList( $folders, 'folders', 'class="inputbox" size="1" '. $javascript, 'value', 'text', '/' );
		return $getfolders;
	}

	function GetImages( &$images, $path ) {
		if ( !isset($images['/'] ) ) {
			$images['/'][] = mosHTML::makeOption( '' );
		}

		//$javascript	= "onchange=\"previewImage( 'imagefiles', 'view_imagefiles', '$path/' )\" onfocus=\"previewImage( 'imagefiles', 'view_imagefiles', '$path/' )\"";
		$javascript	= "onchange=\"previewImage( 'imagefiles', 'view_imagefiles', '$path/' )\"";
		$getimages	= mosHTML::selectList( $images['/'], 'imagefiles', 'class="inputbox" size="10" multiple="multiple" '. $javascript , 'value', 'text', null );

		return $getimages;
	}

	function GetSavedImages( &$row, $path ) {
		$images2 = array();
		foreach( $row->images as $file ) {
			$temp = explode( '|', $file );
			if( strrchr($temp[0], '/') ) {
				$filename = substr( strrchr($temp[0], '/' ), 1 );
			} else {
				$filename = $temp[0];
			}
			$images2[] = mosHTML::makeOption( $file, $filename );
		}
		//$javascript	= "onchange=\"previewImage( 'imagelist', 'view_imagelist', '$path/' ); showImageProps( '$path/' ); \" onfocus=\"previewImage( 'imagelist', 'view_imagelist', '$path/' )\"";
		$javascript	= "onchange=\"previewImage( 'imagelist', 'view_imagelist', '$path/' ); showImageProps( '$path/' ); \"";
		$imagelist 	= mosHTML::selectList( $images2, 'imagelist', 'class="inputbox" size="10" '. $javascript, 'value', 'text' );

		return $imagelist;
	}

	/**
	* Checks to see if an image exists in the current templates image directory
 	* if it does it loads this image.  Otherwise the default image is loaded.
	* Also can be used in conjunction with the menulist param to create the chosen image
	* load the default or use no image
	*/
	function ImageCheck( $file, $directory='/images/M_images/', $param=NULL, $param_directory='/images/M_images/', $alt=NULL, $name='image', $type=1, $align='top' ) {
		global $mainframe;

		$cur_template = $mainframe->getTemplate();

		// strip html
		$alt	= html_entity_decode( $alt );

		if ( $param ) {
			$image = $param_directory . $param;
			if ( $type ) {
				$image = '<img src="'. $image .'" align="'. $align .'" alt="'. $alt .'" border="0" />';
			}
		} else if ( $param == -1 ) {
			$image = '';
		} else {
			if ( file_exists( JPATH_SITE .'/templates/'. $cur_template .'/images/'. $file ) ) {
				$image = 'templates/'. $cur_template .'/images/'. $file;
			} else {
				// outputs only path to image
				$image = $directory . $file;
			}
		}

		if (substr($image, 0, 1 ) == "/") {
			$image = substr_replace($image, '', 0, 1);
		}

		// outputs actual html <img> tag
		if ( $type ) {
			$image = '<img src="'. $image .'" alt="'. $alt .'" align="'. $align .'" border="0" />';
		}

		return $image;
	}

	/**
	* Checks to see if an image exists in the current templates image directory
 	* if it does it loads this image.  Otherwise the default image is loaded.
	* Also can be used in conjunction with the menulist param to create the chosen image
	* load the default or use no image
	*/
	function ImageCheckAdmin( $file, $directory='/images/', $param=NULL, $param_directory='/images/', $alt=NULL, $name=NULL, $type=1, $align='middle' )	{
		global $mainframe;

		$cur_template = $mainframe->getTemplate();

		// strip html
		$alt	= html_entity_decode( $alt );

		if ( $param ) {
			$image = $param_directory . $param;
		} else if ( $param == -1 ) {
			$image = '';
		} else {
			if ( file_exists( JPATH_ADMINISTRATOR .'/templates/'. $cur_template .'/images/'. $file ) ) {
				$image = 'templates/'. $cur_template .'/images/'. $file;
			} else {
				// compability with previous versions
				if ( substr($directory, 0, 14 )== "/administrator" ) {
					$image = substr($directory,15) . $file;
				} else {
					$image = $directory . $file;
				}
			}
		}

		if (substr($image, 0, 1 ) == "/") {
			$image = substr_replace($image, '', 0, 1);
		}

		// outputs actual html <img> tag
		if ( $type ) {
			$image = '<img src="'. $image .'" alt="'. $alt .'" align="'. $align .'" border="0" />';
		}

		return $image;
	}

	function menutypes() {
		global $database;

		$query = "SELECT params"
		. "\n FROM #__modules"
		. "\n WHERE module = 'mod_mainmenu'"
		. "\n ORDER BY title"
		;
		$database->setQuery( $query	);
		$modMenus = $database->loadObjectList();

		$query = "SELECT menutype"
		. "\n FROM #__menu"
		. "\n GROUP BY menutype"
		. "\n ORDER BY menutype"
		;
		$database->setQuery( $query	);
		$menuMenus = $database->loadObjectList();

		$menuTypes = '';
		foreach ( $modMenus as $modMenu ) {
			$check = 1;
			mosMakeHtmlSafe( $modMenu) ;

			$registry = new JRegistry();
			$registry->loadINI($modMenu->params);
			$modParams = $registry->toObject( );

			$menuType 	= @$modParams->menutype;
			if (!$menuType) {
				$menuType = 'mainmenu';
			}

			// stop duplicate menutype being shown
			if ( !is_array( $menuTypes) ) {
				// handling to create initial entry into array
				$menuTypes[] = $menuType;
			} else {
				$check = 1;
				foreach ( $menuTypes as $a ) {
					if ( $a == $menuType ) {
						$check = 0;
					}
				}
				if ( $check ) {
					$menuTypes[] = $menuType;
				}
			}

		}
		// add menutypes from jos_menu
		foreach ( $menuMenus as $menuMenu ) {
			$check = 1;
			foreach ( $menuTypes as $a ) {
				if ( $a == $menuMenu->menutype ) {
					$check = 0;
				}
			}
			if ( $check ) {
				$menuTypes[] = $menuMenu->menutype;
			}
		}

		// sorts menutypes
		asort( $menuTypes );

		return $menuTypes;
	}

	/*
	* loads files required for menu items
	*/
	function menuItem( $item ) {

		$path = JPATH_ADMINISTRATOR .'/components/com_menus/'. $item .'/';
		include_once( $path . $item .'.class.php' );
		include_once( $path . $item .'.menu.html.php' );
	}
}
?>
