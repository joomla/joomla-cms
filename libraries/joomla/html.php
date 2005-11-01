<?php

/**
* @version $Id:  $
* @package Joomla 
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
* Utility class for all HTML drawing classes
* @package Joomla
* @since 1.0
*/
class mosHTML {
	function makeOption( $value, $text='', $value_name='value', $text_name='text' ) {
		$obj = new stdClass;
		$obj->$value_name = $value;
		$obj->$text_name = trim( $text ) ? $text : $value;
		return $obj;
	}

  function writableCell( $folder ) {
	global $_LANG;

  	echo '<tr>';
  	echo '<td class="item">' . $folder . '/</td>';
  	echo '<td >';
  	echo is_writable( "../$folder" ) ? '<b><font color="green">'. $_LANG->_( 'Writeable' ) .'</font></b>' : '<b><font color="red">'. $_LANG->_( 'Unwriteable' ) .'</font></b>' . '</td>';
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
	function selectList( &$arr, $tag_name, $tag_attribs, $key, $text, $selected=NULL, $idtag='' ) {
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
			$extra .= $id ? ' id="' . $arr[$i]->id . '"' : '';
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
		global $_LANG;

		$arr = array(
			mosHTML::makeOption( '01', $_LANG->_( 'JAN' ) ),
			mosHTML::makeOption( '02', $_LANG->_( 'FEB' ) ),
			mosHTML::makeOption( '03', $_LANG->_( 'MAR' ) ),
			mosHTML::makeOption( '04', $_LANG->_( 'APR' ) ),
			mosHTML::makeOption( '05', $_LANG->_( 'MAY' ) ),
			mosHTML::makeOption( '06', $_LANG->_( 'JUN' ) ),
			mosHTML::makeOption( '07', $_LANG->_( 'JUL' ) ),
			mosHTML::makeOption( '08', $_LANG->_( 'AUG' ) ),
			mosHTML::makeOption( '09', $_LANG->_( 'SEP' ) ),
			mosHTML::makeOption( '10', $_LANG->_( 'OCT' ) ),
			mosHTML::makeOption( '11', $_LANG->_( 'NOV' ) ),
			mosHTML::makeOption( '12', $_LANG->_( 'DEC' ) )			
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
		global $_LANG;

		$arr = array(
			mosHTML::makeOption( '0', $_LANG->_( $no ) ),
			mosHTML::makeOption( '1', $_LANG->_( $yes ) ),
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
	function radioList( &$arr, $tag_name, $tag_attribs, $selected=null, $key='value', $text='text' ) {
		reset( $arr );
		$html = "";
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
			$html .= "\n\t<input type=\"radio\" name=\"$tag_name\" id=\"$tag_name$k\" value=\"".$k."\"$extra $tag_attribs />";
			$html .= "\n\t<label for=\"$tag_name$k\">$t</label>";
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
	function yesnoRadioList( $tag_name, $tag_attribs, $selected, $yes='yes', $no='no' ) {
		global $_LANG;

		$arr = array(
			mosHTML::makeOption( '0', $_LANG->_( $no ) ),
			mosHTML::makeOption( '1', $_LANG->_( $yes ) )
		);
		return mosHTML::radioList( $arr, $tag_name, $tag_attribs, $selected );
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

	function sortIcon( $base_href, $field, $state='none' ) {
		global $mosConfig_live_site;
		global $_LANG;

		$alts = array(
			'none' 	=> $_LANG->_( 'No Sorting' ),
			'asc' 	=> $_LANG->_( 'Sort Ascending' ),
			'desc' 	=> $_LANG->_( 'Sort Descending' ),
		);
		$next_state = 'asc';
		if ($state == 'asc') {
			$next_state = 'desc';
		} else if ($state == 'desc') {
			$next_state = 'none';
		}

		$html = "<a href=\"$base_href&field=$field&order=$next_state\">"
		. "<img src=\"$mosConfig_live_site/images/M_images/sort_$state.png\" width=\"12\" height=\"12\" border=\"0\" alt=\"{$alts[$next_state]}\" />"
		. "</a>";
		return $html;
	}

	/**
	* Writes Close Button
	*/
	function CloseButton ( &$params, $hide_js=NULL ) {
		global $_LANG;

		// displays close button in Pop-up window
		if ( $params->get( 'popup' ) && !$hide_js ) {
			?>
			<div align="center" style="margin-top: 30px; margin-bottom: 30px;">
			<a href='javascript:window.close();'>
			<span class="small">
			<?php echo $_LANG->_( 'Close Window' );?>
			</span>
			</a>
			</div>
			<?php
		}
	}

	/**
	* Writes Back Button
	*/
	function BackButton ( &$params, $hide_js=NULL ) {
		global $_LANG;

		// Back Button
		if ( $params->get( 'back_button' ) && !$params->get( 'popup' ) && !$hide_js) {
			?>
			<div class="back_button">
			<a href='javascript:history.go(-1)'>
			<?php echo $_LANG->_( 'BACK' ); ?>
			</a>
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
		global $mosConfig_live_site, $mosConfig_absolute_path, $cur_template, $Itemid;
		global $_LANG;

    	if ( $params->get( 'print' )  && !$hide_js ) {
			// use default settings if none declared
			if ( !$status ) {
				$status = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no';
			}

			// checks template image directory for image, if non found default are loaded
			if ( $params->get( 'icons' ) ) {
				$image = mosAdminMenus::ImageCheck( 'printButton.png', '/images/M_images/', NULL, NULL, $_LANG->_( 'Print' ), $_LANG->_( 'Print' ) );
			} else {
				$image = $_LANG->_( 'ICON_SEP' ) .'&nbsp;'. $_LANG->_( 'Print' ) .'&nbsp;'. $_LANG->_( 'ICON_SEP' );
			}

			if ( $params->get( 'popup' ) && !$hide_js ) {
				// Print Preview button - used when viewing page
				?>
				<td align="right" width="100%" class="buttonheading">
				<a href="#" onclick="javascript:window.print(); return false" title="<?php echo $_LANG->_( 'Print' );?>">
				<?php echo $image;?>
				</a>
				</td>
				<?php
			} else {
				// Print Button - used in pop-up window
				?>
				<td align="right" width="100%" class="buttonheading">
				<a href="#" onclick="window.open('<?php echo $link; ?>','win2','<?php echo $status; ?>');" title="<?php echo $_LANG->_( 'Print' );?>">
				<?php echo $image;?>
				</a>
				</td>
				<?php
			}
		}
	}

	/**
	* simple Javascript Cloaking
	* email cloacking
 	* by default replaces an email with a mailto link with email cloacked
	*/
	function emailCloaking( $mail, $mailto=1, $text='', $email=1 ) {
		global $_LANG;

		// convert text
		$mail 		= mosHTML::encoding_converter( $mail );
		// split email by @ symbol
		$mail		= explode( '@', $mail );
		$mail_parts	= explode( '.', $mail[1] );
		// random number
		$rand	= rand( 1, 100000 );

		$replacement 	= "\n<script language='JavaScript' type='text/javascript'> \n";
		$replacement 	.= "<!-- \n";
		$replacement 	.= "var prefix = '&#109;a' + 'i&#108;' + '&#116;o'; \n";
		$replacement 	.= "var path = 'hr' + 'ef' + '='; \n";
		$replacement 	.= "var addy". $rand ." = '". @$mail[0] ."' + '&#64;'; \n";
		$replacement 	.= "addy". $rand ." = addy". $rand ." + '". implode( "' + '&#46;' + '", $mail_parts ) ."'; \n";
		if ( $mailto ) {
			// special handling when mail text is different from mail addy
			if ( $text ) {
				if ( $email ) {
					// convert text
					$text 	= mosHTML::encoding_converter( $text );
					// split email by @ symbol
					$text 	= explode( '@', $text );
					$text_parts	= explode( '.', $text[1] );
					$replacement 	.= "var addy_text". $rand ." = '". @$text[0] ."' + '&#64;' + '". implode( "' + '&#46;' + '", @$text_parts ) ."'; \n";
				} else {
					$text 	= mosHTML::encoding_converter( $text );
					$replacement 	.= "var addy_text". $rand ." = '". $text ."';\n";
				}
				$replacement 	.= "document.write( '<a ' + path + '\'' + prefix + ':' + addy". $rand ." + '\'>' ); \n";
				$replacement 	.= "document.write( addy_text". $rand ." ); \n";
				$replacement 	.= "document.write( '<\/a>' ); \n";
			} else {
				$replacement 	.= "document.write( '<a ' + path + '\'' + prefix + ':' + addy". $rand ." + '\'>' ); \n";
				$replacement 	.= "document.write( addy". $rand ." ); \n";
				$replacement 	.= "document.write( '<\/a>' ); \n";
			}
		} else {
			$replacement 	.= "document.write( addy". $rand ." ); \n";
		}
		$replacement 	.= "//--> \n";
		$replacement 	.= "</script>";
		$replacement 	.= "<noscript> \n";
		$replacement 	.= $_LANG->_( 'CLOAKING' );
		$replacement 	.= "\n</noscript>";

		return $replacement;
	}

	function encoding_converter( $text ) {
		// replace vowels with character encoding
		$text 	= str_replace( 'a', '&#97;', $text );
		$text 	= str_replace( 'e', '&#101;', $text );
		$text 	= str_replace( 'i', '&#105;', $text );
		$text 	= str_replace( 'o', '&#111;', $text );
		$text	= str_replace( 'u', '&#117;', $text );

		return $text;
	}
}

class mosCommonHTML {

	function ContentLegend( ) {
		global $_LANG;
		?>
		<table cellspacing="0" cellpadding="4" border="0" align="center">
		<tr align="center">
			<td>
			<img src="images/publish_y.png" width="12" height="12" border="0" alt="<?php echo $_LANG->_( 'Pending' ); ?>" />
			</td>
			<td>
			<?php echo $_LANG->_( 'Published, but is' ); ?> <u><?php echo $_LANG->_( 'Pending' ); ?></u> |
			</td>
			<td>
			<img src="images/publish_g.png" width="12" height="12" border="0" alt="<?php echo $_LANG->_( 'Visible' ); ?>" />
			</td>
			<td>
			<?php echo $_LANG->_( 'Published and is' ); ?> <u><?php echo $_LANG->_( 'Current' ); ?></u> |
			</td>
			<td>
			<img src="images/publish_r.png" width="12" height="12" border="0" alt="<?php echo $_LANG->_( 'Finished' ); ?>" />
			</td>
			<td>
			<?php echo $_LANG->_( 'Published, but has' ); ?> <u><?php echo $_LANG->_( 'Expired' ); ?></u> |
			</td>
			<td>
			<img src="images/publish_x.png" width="12" height="12" border="0" alt="<?php echo $_LANG->_( 'Finished' ); ?>" />
			</td>
			<td>
			<?php echo $_LANG->_( 'Not Published' ); ?>
			</td>
		</tr>
		<tr>
			<td colspan="8" align="center">
			<?php echo $_LANG->_( 'Click on icon to toggle state.' ); ?>
			</td>
		</tr>
		</table>
		<?php
	}

	function menuLinksContent( &$menus ) {
		global $_LANG;
		?>
		<script language="javascript" type="text/javascript">
		function go2( pressbutton, menu, id ) {
			var form = document.adminForm;

			if (pressbutton == 'go2menu') {
				form.menu.value = menu;
				submitform( pressbutton );
				return;
			}

			if (pressbutton == 'go2menuitem') {
				form.menu.value 	= menu;
				form.menuid.value 	= id;
				submitform( pressbutton );
				return;
			}
		}
		</script>
		<?php
		foreach( $menus as $menu ) {
			?>
			<tr>
				<td colspan="2">
				<hr />
				</td>
			</tr>
			<tr>
				<td width="90px" valign="top">
				<?php echo $_LANG->_( 'Menu' ); ?>
				</td>
				<td>
				<a href="javascript:go2( 'go2menu', '<?php echo $menu->menutype; ?>' );" title="<?php echo $_LANG->_( 'Go to Menu' ); ?>">
				<?php echo $menu->menutype; ?>
				</a>
				</td>
			</tr>
			<tr>
				<td width="90px" valign="top">
				<?php echo $_LANG->_( 'Link Name' ); ?>
				</td>
				<td>
				<strong>
				<a href="javascript:go2( 'go2menuitem', '<?php echo $menu->menutype; ?>', '<?php echo $menu->id; ?>' );" title="<?php echo $_LANG->_( 'Go to Menu Item' ); ?>">
				<?php echo $menu->name; ?>
				</a>
				</strong>
				</td>
			</tr>
			<tr>
				<td width="90px" valign="top">
				<?php echo $_LANG->_( 'State' ); ?>
				</td>
				<td>
				<?php
				switch ( $menu->published ) {
					case -2:
						echo '<font color="red">'. $_LANG->_( 'Trashed' ) .'</font>';
						break;
					case 0:
						echo $_LANG->_( 'UnPublished' );
						break;
					case 1:
					default:
						echo '<font color="green">'. $_LANG->_( 'Published' ) .'</font>';
						break;
				}
				?>
				</td>
			</tr>
			<?php
		}
		?>
		<input type="hidden" name="menu" value="" />
		<input type="hidden" name="menuid" value="" />
		<?php
	}

	function menuLinksSecCat( &$menus ) {
		global $_LANG;
		?>
		<script language="javascript" type="text/javascript">
		function go2( pressbutton, menu, id ) {
			var form = document.adminForm;

			if (pressbutton == 'go2menu') {
				form.menu.value = menu;
				submitform( pressbutton );
				return;
			}

			if (pressbutton == 'go2menuitem') {
				form.menu.value 	= menu;
				form.menuid.value 	= id;
				submitform( pressbutton );
				return;
			}
		}
		</script>
		<?php
		foreach( $menus as $menu ) {
			?>
			<tr>
				<td colspan="2">
				<hr/>
				</td>
			</tr>
			<tr>
				<td width="90px" valign="top">
				<?php echo $_LANG->_( 'Menu' ); ?>
				</td>
				<td>
				<a href="javascript:go2( 'go2menu', '<?php echo $menu->menutype; ?>' );" title="<?php echo $_LANG->_( 'Go to Menu' ); ?>">
				<?php echo $menu->menutype; ?>
				</a>
				</td>
			</tr>
			<tr>
				<td width="90px" valign="top">
				<?php echo $_LANG->_( 'Type' ); ?>
				</td>
				<td>
				<?php echo $menu->type; ?>
				</td>
			</tr>
			<tr>
				<td width="90px" valign="top">
				<?php echo $_LANG->_( 'Item Name' ); ?>
				</td>
				<td>
				<strong>
				<a href="javascript:go2( 'go2menuitem', '<?php echo $menu->menutype; ?>', '<?php echo $menu->id; ?>' );" title="<?php echo $_LANG->_( 'Go to Menu Item' ); ?>">
				<?php echo $menu->name; ?>
				</a>
				</strong>
				</td>
			</tr>
			<tr>
				<td width="90px" valign="top">
				<?php echo $_LANG->_( 'State' ); ?>
				</td>
				<td>
				<?php
				switch ( $menu->published ) {
					case -2:
						echo '<font color="red">'. $_LANG->_( 'Trashed' ) .'</font>';
						break;
					case 0:
						echo $_LANG->_( 'UnPublished' );
						break;
					case 1:
					default:
						echo '<font color="green">'. $_LANG->_( 'Published' ) .'</font>';
						break;
				}
				?>
				</td>
			</tr>
			<?php
		}
		?>
		<input type="hidden" name="menu" value="" />
		<input type="hidden" name="menuid" value="" />
		<?php
	}

	function checkedOut( &$row, $overlib=1 ) {
		global $_LANG;

		$hover = '';
		if ( $overlib ) {
			$date 				= mosFormatDate( $row->checked_out_time, '%A, %d %B %Y' );
			$time				= mosFormatDate( $row->checked_out_time, '%H:%M' );
			$checked_out_text 	= '<table>';
			$checked_out_text 	.= '<tr><td>'. $row->editor .'</td></tr>';
			$checked_out_text 	.= '<tr><td>'. $date .'</td></tr>';
			$checked_out_text 	.= '<tr><td>'. $time .'</td></tr>';
			$checked_out_text 	.= '</table>';
			$hover = 'onMouseOver="return overlib(\''. $checked_out_text .'\', CAPTION, \''. $_LANG->_( 'Checked Out' ) .'\', BELOW, RIGHT);" onMouseOut="return nd();"';
		}
		$checked	 		= '<img src="images/checked_out.png" '. $hover .'/>';

		return $checked;
	}

	/*
	* Loads all necessary files for JS Overlib tooltips
	*/
	function loadOverlib() {
		global  $mosConfig_live_site, $mainframe;

		if ( !$mainframe->get( 'loadOverlib' ) ) {
		// check if this function is already loaded
			?>
			<script language="javascript" type="text/javascript" src="<?php echo $mosConfig_live_site;?>/includes/js/overlib_mini.js"></script>
			<script language="javascript" type="text/javascript" src="<?php echo $mosConfig_live_site;?>/includes/js/overlib_hideform_mini.js"></script>
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
		global  $mosConfig_live_site;
		global $_LANG;
		?>
		<link rel="stylesheet" type="text/css" media="all" href="<?php echo $mosConfig_live_site;?>/includes/js/calendar/calendar-mos.css" title="<?php echo $_LANG->_( 'green' ); ?>" />
		<!-- import the calendar script -->
		<script type="text/javascript" src="<?php echo $mosConfig_live_site;?>/includes/js/calendar/calendar_mini.js"></script>
		<!-- import the language module -->
		<script type="text/javascript" src="<?php echo $mosConfig_live_site;?>/includes/js/calendar/lang/calendar-en.js"></script>
		<?php
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
		<a href="javascript: void(0);" onclick="return listItemTask(\'cb'. $i .'\',\''. $task_access .'\')" '. $color_access .'>
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

	function PublishedProcessing( &$row, $i ) {
		global $_LANG;

		$img 	= $row->published ? 'publish_g.png' : 'publish_x.png';
		$task 	= $row->published ? 'unpublish' : 'publish';
		$alt 	= $row->published ? $_LANG->_( 'Published' ) : $_LANG->_( 'Unpublished' );
		$action	= $row->published ? 'Unpublish Item' : 'Publish item';

		$href = '
		<a href="javascript: void(0);" onclick="return listItemTask(\'cb'. $i .'\',\''. $task .'\')" title="'. $action .'">
		<img src="images/'. $img .'" border="0" alt="'. $alt .'" />
		</a>'
		;

		return $href;
	}
}

/**
* Tab Creation handler
* @package Joomla
* @author Phil Taylor
* @since 1.0
*/
class mosTabs {
	/** @var int Use cookies */
	var $useCookies = 0;

	/**
	* Constructor
	* Includes files needed for displaying tabs and sets cookie options
	* @param int useCookies, if set to 1 cookie will hold last used tab between page refreshes
	* @param boolean xhtml [DEPRECATED]
	*/
	function mosTabs( $useCookies, $xhtml=NULL ) {
		global $mosConfig_live_site, $_LANG, $mainframe;
		
		$css = $_LANG->rtl() ? 'tabpane_rtl.css' : 'tabpane.css';
		
		$mainframe->addCustomHeadTag( '<link rel="stylesheet" type="text/css" media="screen, projection" href="'.$mosConfig_live_site.'/includes/js/tabs/'.$css.'" id="luna-tab-style-sheet" />' );		
		$mainframe->addCustomHeadTag( '<script type="text/javascript" src="'.$mosConfig_live_site.'/includes/js/tabs/tabpane_mini.js"></script>' );		

		$this->useCookies = $useCookies;
	}

	/**
	* creates a tab pane and creates JS obj
	* @param string The Tab Pane Name
	*/
	function startPane($id){
		echo "<div class=\"tab-page\" id=\"".$id."\">";
		echo "<script type=\"text/javascript\">\n";
		echo "	var tabPane1 = new WebFXTabPane( document.getElementById( \"".$id."\" ), ".$this->useCookies." )\n";
		echo "</script>\n";
	}

	/**
	* Ends Tab Pane
	*/
	function endPane() {
		echo "</div>";
	}

	/*
	* Creates a tab with title text and starts that tabs page
	* @param tabText - This is what is displayed on the tab
	* @param paneid - This is the parent pane to build this tab on
	*/
	function startTab( $tabText, $paneid ) {
		echo "<div class=\"tab-page\" id=\"".$paneid."\">";
		echo "<h2 class=\"tab\">".$tabText."</h2>";
		echo "<script type=\"text/javascript\">\n";
		echo "  tabPane1.addTabPage( document.getElementById( \"".$paneid."\" ) );";
		echo "</script>";
	}

	/*
	* Ends a tab page
	*/
	function endTab() {
		echo "</div>";
	}
}

/**
* Common HTML Output Files
* @package Joomla
* @since 1.0
*/
class mosAdminMenus {
	/**
	* build the select list for Menu Ordering
	*/
	function Ordering( &$row, $id ) {
		global $database;
		global $_LANG;

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
			$ordering = '<input type="hidden" name="ordering" value="'. $row->ordering .'" />'. $_LANG->_( 'CMN_NEW_ITEM_LAST' );
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
		$access = mosHTML::selectList( $groups, 'access', 'class="inputbox" size="3"', 'value', 'text', intval( $row->access ) );

		return $access;
	}

	/**
	* build the select list for parent item
	*/
	function Parent( &$row ) {
		global $database;
		global $_LANG;

		// get a list of the menu items
		$query = "SELECT m.*"
		. "\n FROM #__menu m"
		. "\n WHERE menutype = '$row->menutype'"
		. "\n AND published <> -2"
		. "\n ORDER BY ordering"
		;
		$database->setQuery( $query );
		$mitems = $database->loadObjectList();

		// establish the hierarchy of the menu
		$children = array();
		// first pass - collect children
		foreach ( $mitems as $v ) {
			$pt = $v->parent;
			$list = @$children[$pt] ? $children[$pt] : array();
			array_push( $list, $v );
			$children[$pt] = $list;
		}
		// second pass - get an indent list of the items
		$list = mosTreeRecurse( 0, '', array(), $children, 9999, 0, 0 );

		// assemble menu items to the array
		$mitems = array();
		$mitems[] = mosHTML::makeOption( '0', $_LANG->_( 'Top' ) );
		$this_treename = '';
		foreach ( $list as $item ) {
			if ( $this_treename ) {
				if ( $item->id != $row->id && strpos( $item->treename, $this_treename ) === false) {
					$mitems[] = mosHTML::makeOption( $item->id, $item->treename );
				}
			} else {
				if ( $item->id != $row->id ) {
					$mitems[] = mosHTML::makeOption( $item->id, $item->treename );
				} else {
					$this_treename = "$item->treename/";
				}
			}
		}
		$parent = mosHTML::selectList( $mitems, 'parent', 'class="inputbox" size="1"', 'value', 'text', $row->parent );
		return $parent;
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
		global $_LANG;

		$click[] = mosHTML::makeOption( '0', $_LANG->_( 'Parent Window With Browser Navigation' ) );
		$click[] = mosHTML::makeOption( '1', $_LANG->_( 'New Window With Browser Navigation' ) );
		$click[] = mosHTML::makeOption( '2', $_LANG->_( 'New Window Without Browser Navigation' ) );
		$target = mosHTML::selectList( $click, 'browserNav', 'class="inputbox" size="4"', 'value', 'text', intval( $row->browserNav ) );
		return $target;
	}

	/**
	* build the multiple select list for Menu Links/Pages
	*/
	function MenuLinks( &$lookup, $all=NULL, $none=NULL, $unassigned=1 ) {
		global $database;
		global $_LANG;

		// get a list of the menu items
		$query = "SELECT m.*"
		. "\n FROM #__menu AS m"
//		. "\n WHERE type != 'url'"
//		. "\n AND type != 'separator'"
// Change adds Itemid support for Link - Urls without `index.php` or `Itemid=` in their url
		. "\n WHERE m.type != 'separator'"
		. "\n AND NOT ("
			. "\n ( m.type = 'url' )" 
			. "\n AND ( m.link LIKE '%index.php%' )"
			. "\n AND ( m.link LIKE '%Itemid=%' )"
		. "\n )"
		. "\n AND m.published = 1"
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
					if ( strlen($text) > $text_count) {
						$text_count = strlen($text);
					}
				}
			}
		}
		$list = $list_temp;

		$mitems = array();
		if ( $all ) {
			// prepare an array with 'all' as the first item
			$mitems[] = mosHTML::makeOption( 0, $_LANG->_( 'All' ) );
			// adds space, in select box which is not saved
			$mitems[] = mosHTML::makeOption( -999, '----' );
		}
		if ( $none ) {
			// prepare an array with 'all' as the first item
			$mitems[] = mosHTML::makeOption( -999, $_LANG->_( 'None' ) );
			// adds space, in select box which is not saved
			$mitems[] = mosHTML::makeOption( -999, '----' );
		}
		if ( $none ) {
			// prepare an array with 'all' as the first item
			$mitems[] = mosHTML::makeOption( 99999999, $_LANG->_( 'Unassigned' ) );
			// adds space, in select box which is not saved
			$mitems[] = mosHTML::makeOption( -999, '----' );
		}
		// append the rest of the menu items to the array
		foreach ($list as $item) {
			$mitems[] = mosHTML::makeOption( $item->value, $item->text );
		}
		$pages = mosHTML::selectList( $mitems, 'selections[]', 'class="inputbox" size="26" multiple="multiple"', 'value', 'text', $lookup );
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
		if ( $id ) {
			foreach ( $rows as $row ) {
				if ( $row->value == $menu->componentid ) {
					$category = $row->text;
				}
			}
			$category .= '<input type="hidden" name="componentid" value="'. $menu->componentid .'" />';
			$category .= '<input type="hidden" name="link" value="'. $menu->link .'" />';
		} else {
			$category = mosHTML::selectList( $rows, 'componentid', 'class="inputbox" size="10"'. $javascript, 'value', 'text' );
			$category .= '<input type="hidden" name="link" value="" />';
		}
		return $category;
	}

	/**
	* build the select list to choose a section
	*/
	function Section( &$menu, $id, $all=0 ) {
		global $database;
		global $_LANG;

		$query = "SELECT s.id AS `value`, s.id AS `id`, s.title AS `text`"
		. "\n FROM #__sections AS s"
		. "\n WHERE s.scope = 'content'"
		. "\n ORDER BY s.name"
		;
		$database->setQuery( $query );
		if ( $all ) {
			$rows[] = mosHTML::makeOption( 0, '- '. $_LANG->_( 'All Sections' ) .' -' );
			$rows = array_merge( $rows, $database->loadObjectList() );
		} else {
			$rows = $database->loadObjectList();
		}

		if ( $id ) {
			foreach ( $rows as $row ) {
				if ( $row->value == $menu->componentid ) {
					$section = $row->text;
				}
			}
			$section .= '<input type="hidden" name="componentid" value="'. $menu->componentid .'" />';
			$section .= '<input type="hidden" name="link" value="'. $menu->link .'" />';
		} else {
			$section = mosHTML::selectList( $rows, 'componentid', 'class="inputbox" size="10"', 'value', 'text' );
			$section .= '<input type="hidden" name="link" value="" />';
		}
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

		if ( $id ) {
			// existing component, just show name
			foreach ( $rows as $row ) {
				if ( $row->value == $menu->componentid ) {
					$component = $row->text;
				}
			}
			$component .= '<input type="hidden" name="componentid" value="'. $menu->componentid .'" />';
		} else {
			$component = mosHTML::selectList( $rows, 'componentid', 'class="inputbox" size="10"', 'value', 'text' );
		}
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
				$component = $row->text;
			}
		}

		return $component;
	}

	/**
	* build the select list to choose an image
	*/
	function Images( $name, &$active, $javascript=NULL, $directory=NULL ) {
		global $mosConfig_absolute_path;
		global $_LANG;

		if ( !$javascript ) {
			$javascript = "onchange=\"javascript:if (document.forms[0].image.options[selectedIndex].value!='') {document.imagelib.src='../images/stories/' + document.forms[0].image.options[selectedIndex].value} else {document.imagelib.src='../images/blank.png'}\"";
		}
		if ( !$directory ) {
			$directory = '/images/stories';
		}

		$imageFiles = mosReadDirectory( $mosConfig_absolute_path . $directory );
		$images = array(  mosHTML::makeOption( '', '- '. $_LANG->_( 'Select Image' ) .' -' ) );
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
		global $_LANG;

		if ( $neworder ) {
			$text = $_LANG->_( 'descNewItemsFirst' );
		} else {
			$text = $_LANG->_( 'descNewItemsLast' );
		}

		if ( $id ) {
			$order = mosGetOrderingList( $query );
			$ordering = mosHTML::selectList( $order, 'ordering', 'class="inputbox" size="1"', 'value', 'text', intval( $row->ordering ) );
		} else {
			$ordering = '<input type="hidden" name="ordering" value="'. $row->ordering .'" />'. $text;
		}
		return $ordering;
	}

	/**
	* Select list of active users
	*/
	function UserSelect( $name, $active, $nouser=0, $javascript=NULL, $order='name', $reg=1 ) {
		global $database, $my;
		global $_LANG;

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
			$users[] = mosHTML::makeOption( '0', '- '. $_LANG->_( 'No User' ) .' -' );
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
	function Positions( $name, $active=NULL, $javascript=NULL, $none=1, $center=1, $left=1, $right=1 ) {
		global $_LANG;

		if ( $none ) {
			$pos[] = mosHTML::makeOption( '', $_LANG->_( 'None' ) );
		}
		if ( $center ) {
			$pos[] = mosHTML::makeOption( 'center', $_LANG->_( 'Center' ) );
		}
		if ( $left ) {
			$pos[] = mosHTML::makeOption( 'left', $_LANG->_( 'Left' ) );
		}
		if ( $right ) {
			$pos[] = mosHTML::makeOption( 'right', $_LANG->_( 'Right' ) );
		}

		$positions = mosHTML::selectList( $pos, $name, 'class="inputbox" size="1"'. $javascript, 'value', 'text', $active );

		return $positions;
	}

	/**
	* Select list of active categories for components
	*/
	function ComponentCategory( $name, $section, $active=NULL, $javascript=NULL, $order='ordering', $size=1, $sel_cat=1 ) {
		global $database;
		global $_LANG;

		$query = "SELECT id AS value, name AS text"
		. "\n FROM #__categories"
		. "\n WHERE section = '$section'"
		. "\n AND published = 1"
		. "\n ORDER BY $order"
		;
		$database->setQuery( $query );
		if ( $sel_cat ) {
			$categories[] = mosHTML::makeOption( '0', '- '. $_LANG->_( 'Select a Category' ) .' -' );
			$categories = array_merge( $categories, $database->loadObjectList() );
		} else {
			$categories = $database->loadObjectList();
		}

		if ( count( $categories ) < 1 ) {
			mosRedirect( 'index2.php?option=com_categories&section='. $section, $_LANG->_( 'You must create a category first.' ) );
		}

		$category = mosHTML::selectList( $categories, $name, 'class="inputbox" size="'. $size .'" '. $javascript, 'value', 'text', $active );

		return $category;
	}

	/**
	* Select list of active sections
	*/
	function SelectSection( $name, $active=NULL, $javascript=NULL, $order='ordering' ) {
		global $database;
		global $_LANG;

		$categories[] = mosHTML::makeOption( '0', '- '. $_LANG->_( 'Select a Section' ) .' -' );
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
		for( $i = 0; $i < $total; $i++ ) {
			$params = mosParseParams( $menus[$i]->params );
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
	function ImageCheck( $file, $directory='/images/M_images/', $param=NULL, $param_directory='/images/M_images/', $alt=NULL, $name='image', $type=1, $align='middle' ) {
		global $mosConfig_absolute_path, $mosConfig_live_site, $mainframe;
		$cur_template = $mainframe->getTemplate();

		if ( $param ) {
			$image = $mosConfig_live_site. $param_directory . $param;
			if ( $type ) {
				$image = '<img src="'. $image .'" align="'. $align .'" alt="'. $alt .'" name="'. $name .'" border="0" />';
			}
		} else if ( $param == -1 ) {
			$image = '';
		} else {
			if ( file_exists( $mosConfig_absolute_path .'/templates/'. $cur_template .'/images/'. $file ) ) {
				$image = $mosConfig_live_site .'/templates/'. $cur_template .'/images/'. $file;
			} else {
				// outputs only path to image
				$image = $mosConfig_live_site. $directory . $file;
			}

			// outputs actual html <img> tag
			if ( $type ) {
				$image = '<img src="'. $image .'" alt="'. $alt .'" align="'. $align .'" name="'. $name .'" border="0" />';
			}
		}

		return $image;
	}

	/**
	* Checks to see if an image exists in the current templates image directory
 	* if it does it loads this image.  Otherwise the default image is loaded.
	* Also can be used in conjunction with the menulist param to create the chosen image
	* load the default or use no image
	*/
	function ImageCheckAdmin( $file, $directory='/administrator/images/', $param=NULL, $param_directory='/administrator/images/', $alt=NULL, $name=NULL, $type=1, $align='middle' ) {
		global $mosConfig_absolute_path, $mosConfig_live_site, $mainframe;
		$cur_template = $mainframe->getTemplate();

		if ( $param ) {
			$image = $mosConfig_live_site. $param_directory . $param;
			if ( $type ) {
				$image = '<img src="'. $image .'" align="'. $align .'" alt="'. $alt .'" name="'. $name .'" border="0" />';
			}
		} else if ( $param == -1 ) {
			$image = '';
		} else {
			if ( file_exists( $mosConfig_absolute_path .'/administrator/templates/'. $cur_template .'/images/'. $file ) ) {
				$image = $mosConfig_live_site .'/administrator/templates/'. $cur_template .'/images/'. $file;
			} else {
				$image = $mosConfig_live_site. $directory . $file;
			}

			// outputs actual html <img> tag
			if ( $type ) {
				$image = '<img src="'. $image .'" alt="'. $alt .'" align="'. $align .'" name="'. $name .'" border="0" />';
			}
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
			$modParams 	= mosParseParams( $modMenu->params );
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
		global $mosConfig_absolute_path;

		$path = $mosConfig_absolute_path .'/administrator/components/com_menus/'. $item .'/';
		include_once( $path . $item .'.class.php' );
		include_once( $path . $item .'.menu.html.php' );
	}
}

/**
 * Utility class for helping with patTemplate
 * @package Joomla
 * @since 1.0
 */
class patHTML {
	/**
	 * Converts a named array to an array or named rows suitable to option lists
	 * @param array The source array[key] = value
	 * @param mixed A value or array of selected values
	 * @param string The name for the value field
	 * @param string The name for selected attribute (use 'checked' for radio of box lists)
	 */
	function selectArray( &$source, $selected=null, $valueName='value', $selectedAttr='selected' ) {
		if (!is_array( $selected )) {
			$selected = array( $selected );
		}
		foreach ($source as $i => $row) {
			if (is_object( $row )) {
				$source[$i]->selected = in_array( $row->$valueName, $selected ) ? $selectedAttr . '="true"' : '';
			} else {
				$source[$i]['selected'] = in_array( $row[$valueName], $selected ) ? $selectedAttr . '="true"' : '';
			}
		}
	}

	/**
	 * Converts a named array to an array or named rows suitable to checkbox or radio lists
	 * @param array The source array[key] = value
	 * @param mixed A value or array of selected values
	 * @param string The name for the value field
	 */
	function checkArray( &$source, $selected=null, $valueName='value' ) {
		patHTML::selectArray( $source, $selected, $valueName, 'checked' );
	}

	/**
	 * @param mixed The value for the option
	 * @param string The text for the option
	 * @param string The name of the value parameter (default is value)
	 * @param string The name of the text parameter (default is text)
	 */
	function makeOption( $value, $text, $valueName='value', $textName='text' ) {
		return array(
			$valueName => $value,
			$textName => $text
		);
	}

	/**
	 * Writes a radio pair
	 * @param object Template object
	 * @param string The template name
	 * @param string The field name
	 * @param int The value of the field
	 * @param array Array of options
	 * @param string Optional template variable name
	 */
	function radioSet( &$tmpl, $template, $name, $value, $a, $varname=null ) {
		patHTML::checkArray( $a, $value );

		$tmpl->addVar( 'radio-set', 'name', $name );
		$tmpl->addRows( 'radio-set', $a );
		$tmpl->parseIntoVar( 'radio-set', $template, is_null( $varname ) ? $name : $varname );
	}

	/**
	 * Writes a radio pair
	 * @param object Template object
	 * @param string The template name
	 * @param string The field name
	 * @param int The value of the field
	 * @param string Optional template variable name
	 */
	function yesNoRadio( &$tmpl, $template, $name, $value, $varname=null ) {
		global $_LANG;
		$a = array(
			patHTML::makeOption( 0, $_LANG->_( $noText ) ),
			patHTML::makeOption( 1, $_LANG->_( $yesText ) )
		);
		patHTML::radioSet( $tmpl, $template, $name, $value, $a, $varname );
	}
}

?>
