<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 6/17/14 11:08 PM $
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Language\CBTxt;

defined('CBLIB') or die();

/**
 * moscomprofilerHTML Class implementation
 * 
 */
abstract class moscomprofilerHTML
{
	/**
	 * Creates an option for a list, a multi-checkbox field, or a radio field
	 *
	 * @param  string       $value      Value of the selection
	 * @param  null|string  $text       Text of the selection
	 * @param  string       $valueName  Name of the value variable
	 * @param  string       $textName   Name of the text variable
	 * @param  null|string  $id         Id of the option
	 * @param  null|string  $class      Class of the option
	 * @param  null|string  $extra      Extra html attributes of the option
	 * @return stdClass                 Selection object
	 */
	public static function makeOption( $value, $text = null, $valueName = 'value', $textName = 'text', $id = null, $class = null, $extra = null )
	{
		$option					=	new stdClass;
		$option->$valueName		=	(string) $value;
		$option->$textName		=	( trim( $text ) ? $text : $value );
		$option->id				=	trim( $id );
		$option->class			=	trim( $class );
		$option->extra			=	trim( $extra );

		return $option;
	}

	/**
	 * Creates an <optgroup> for a list
	 *
	 * @param  null|string  $text       Text of the selection (if NULL: end of optgroup)
	 * @param  string       $valueName  Name of the value variable
	 * @param  string       $textName   Name of the text variable
	 * @param  null|string  $id         Id of the option group
	 * @param  null|string  $class      Class of the option group
	 * @param  null|string  $extra      Extra html attributes of the option group
	 * @return stdClass                 Selection object
	 */
	public static function makeOptGroup( $text = '', $valueName = 'value', $textName = 'text', $id = null, $class = null, $extra = null )
	{
		$option					=	new stdClass;
		$option->$valueName		=	( $text !== null ? array( 'optgroup' ) : array( '/optgroup' ) );
		$option->$textName		=	trim( $text );
		$option->id				=	trim( $id );
		$option->class			=	trim( $class );
		$option->extra			=	trim( $extra );

		return $option;
	}

	/**
	 * Creates a radio input array from $arr options list
	 *
	 * @param  array        $arr
	 * @param  string       $tagName
	 * @param  null|string  $tagAttributes
	 * @param  int|string   $key
	 * @param  string       $text
	 * @param  string       $selected
	 * @param  int          $required
	 * @param  null|array   $classes
	 * @param  boolean      $translate
	 * @return array
	 */
	public static function radioListArr( $arr, $tagName, $tagAttributes = null, $key = 'value', $text = 'text', $selected = null, $required = 0, $classes = null, $translate = true )
	{
		reset( $arr );

		$idName					=	moscomprofilerHTML::htmlId( $tagName );

		$html					=	array();

		if ( $classes === null ) {
			$classes			=	array();
		}

		if ( $required ) {
			$classes[]			=	'required';
		}

		foreach ( $arr as $i => $option ) {
			$k					=	$option->$key;
			$t					=	$option->$text;

			if ( isset( $option->id ) && ( trim( $option->id ) != '' ) ) {
				$id				=	$option->id;
			} else {
				$id				=	$idName . '__cbf' . $i;
			}

			$extra				=	' id="' . htmlspecialchars( $id ) . '"';

			if ( is_array( $selected ) ) {
				foreach ( $selected as $obj ) {
					if ( is_object( $obj ) ) {
						$k2		=	$obj->$key;
					} else {
						$k2		=	$obj;
					}

					if ( (string) $k === (string) $k2 ) {
						$extra	.=	' checked="checked"';
						break;
					}
				}
			} else {
				$extra			.=	( (string) $k === (string) $selected ? ' checked="checked"' : null );
			}

			if ( isset( $option->class ) && ( trim( $option->class ) != '' ) ) {
				$classes[]		=	htmlspecialchars( $option->class );
			}

			if ( count( $classes ) > 0 ) {
				$tagAttributes	.=	' class="' . implode( ' ', $classes ) . '"';
			}

			$extra				.=	( trim( $tagAttributes ) ? ' ' . $tagAttributes : null )
								.	( isset( $option->extra ) && $option->extra ? ' ' . $option->extra : null );

			$html[]				=	'<label for="' . htmlspecialchars( $id ) . '" class="radio-inline">'
				.		'<input type="radio" name="' . htmlspecialchars( $tagName ) . '" value="' . htmlspecialchars( $k ) . '"' . $extra . ' />'
				.		' ' . ( $translate ? CBTxt::Th( $t ) : $t ) . ' '
				.	'</label>';
		}

		return $html;
	}

	/**
	 * Returns a radio input list
	 *
	 * @param  array        $arr
	 * @param  string       $tagName
	 * @param  null|string  $tagAttributes
	 * @param  int|string   $key
	 * @param  string       $text
	 * @param  string       $selected
	 * @param  int          $required
	 * @param  null|array   $classes
	 * @param  boolean      $translate
	 * @return string
	 */
	public static function radioList( $arr, $tagName, $tagAttributes = null, $key = 'value', $text = 'text', $selected = null, $required = 0, $classes = null, $translate = true )
	{
		return "\n\t" . implode( "\n\t ", moscomprofilerHTML::radioListArr( $arr, $tagName, $tagAttributes, $key, $text, $selected, $required, $classes, $translate ) ) . "\n";
	}

	/**
	 * Returns a radio input list formatted as a table
	 *
	 * @param  array        $arr
	 * @param  string       $tagName
	 * @param  null|string  $tagAttributes
	 * @param  int|string   $key
	 * @param  string       $text
	 * @param  string       $selected
	 * @param  int          $cols
	 * @param  int          $rows
	 * @param  int          $size
	 * @param  int          $required
	 * @param  null|array   $classes
	 * @param  null|string  $cellAttributes
	 * @param  boolean      $translate
	 * @return string
	 */
	public static function radioListTable( $arr, $tagName, $tagAttributes = null, $key = 'value', $text = 'text', $selected = null, $cols = 0, $rows = 1, $size = 0, $required = 0, $classes = null, $cellAttributes = null, $translate = true )
	{
		$cellsHtml	=	moscomprofilerHTML::radioListArr( $arr, $tagName, $tagAttributes, $key, $text, $selected, $required, $classes, $translate );

		return moscomprofilerHTML::list2Table( $cellsHtml, $cols, $rows, $size, $cellAttributes );
	}

	/**
	 * Returns a select input
	 *
	 * @param  array             $arr
	 * @param  string            $tagName
	 * @param  null|string       $tagAttributes
	 * @param  int|string        $key
	 * @param  string            $text
	 * @param  null|string        $selected
	 * @param  int               $required
	 * @param  boolean           $htmlspecialcharText
	 * @param  null|boolean|int  $addBlank
	 * @param  boolean           $translate
	 * @return string
	 */
	public static function selectList( $arr, $tagName, $tagAttributes = null, $key = 'value', $text = 'text', $selected = null, $required = 0, $htmlspecialcharText = true, $addBlank = null, $translate = true )
	{
		reset( $arr );

		$idName						=	moscomprofilerHTML::htmlId( $tagName );

		$html						=	"\n" . '<select name="' . htmlspecialchars( $tagName ) . '" id="' . htmlspecialchars( $idName ) . '"' . ( trim( $tagAttributes ) ? ' ' . $tagAttributes : null ) . '>';

		if ( $addBlank === null ) {
			$addBlank				=	( ( ( ! $required ) || ( is_array( $selected ) ? ( count( $selected ) == 0 ) : ( $selected == '' ) ) ) && ( ! ( isset( $arr[0] ) && ( $arr[0]->$key == '' ) ) ) );
		}

		if ( $addBlank ) {
			$html					.=	"\n\t" . '<option value=""></option>';
		}

		foreach ( $arr as $i => $option ) {
			$t						=	( $translate ? CBTxt::T( $option->$text ) : $option->$text );

			if ( isset( $option->id ) && ( trim( $option->id ) != '' ) ) {
				$id					=	$option->id;
			} else {
				$id					=	$idName . '__cbf' . $i;
			}

			$extra					=	' id="' . htmlspecialchars( $id ) . '"';

			if ( isset( $option->class ) && ( trim( $option->class ) != '' ) ) {
				$extra				.=	' class="' . htmlspecialchars( $option->class ) . '"';
			}

			if ( is_array( $option->$key ) ) {
				$a					=	$option->$key;

				if ( $a[0] == 'optgroup' ) {
					$html			.=	"\n" . '<optgroup label="' . htmlspecialchars( $t ) . '"' . $extra . '>';
				} else {
					$html			.=	"\n" . '</optgroup>';
				}
			} else {
				$k					=	$option->$key;

				if ( is_array( $selected ) ) {
					foreach ( $selected as $obj ) {
						if ( is_object( $obj ) ) {
							$k2		=	$obj->$key;
						} else {
							$k2		=	$obj;
						}

						if ( (string) $k === (string) $k2 ) {
							$extra	.=	' selected="selected"';
							break;
						}
					}
				} else {
					if ( (string) $k === (string) $selected ) {
						$extra		.=	' selected="selected"';
					}
				}

				$extra				.=	( isset( $option->extra ) && $option->extra ? ' ' . $option->extra : null );

				$html				.=	"\n\t" . '<option value="' . htmlspecialchars( $k ) . '"' . $extra . '>'
					.	( $htmlspecialcharText ? htmlspecialchars( $t ) : $t )
					.	'</option>';
			}
		}

		$html						.=	"\n" . '</select>' . "\n";

		return $html;
	}

	/**
	 * Returns a YES/NO select input
	 *
	 * @param  string       $tagName
	 * @param  null|string  $tagAttributes
	 * @param  string       $selected
	 * @param  string       $yes
	 * @param  string       $no
	 * @param  boolean      $translate
	 * @return string
	 */
	public static function yesnoSelectList( $tagName, $tagAttributes = null, $selected = null, $yes = null, $no = null, $translate = true )
	{
		if ( $yes === null ) {
			// For strings-grabbing script: CBTxt::T( 'YESNO_SELECTLIST_YES YES', 'Yes' );
			// We add a field-name-specific translation key in front: YESNO_SELECTLIST_YES_FIELD_NAME-OF-FORM-INPUT-ELEMENT as most specific language string here:
			$yes	=	CBTxt::T( 'YESNO_SELECTLIST_YES_FIELD_' . strtoupper( $tagName ) . ' YESNO_SELECTLIST_YES YES', 'Yes' );
		}
		elseif ( $translate ) {
			$yes	=	CBTxt::T( $yes );
		}

		if ( $no === null ) {
			// For strings-grabbing script: CBTxt::T( 'YESNO_SELECTLIST_NO NO', 'No' );
			// We add a field-name-specific translation key in front: YESNO_SELECTLIST_NO_FIELD_NAME-OF-FORM-INPUT-ELEMENT as most specific language string here:
			$no		=	CBTxt::T( 'YESNO_SELECTLIST_NO_FIELD_' . strtoupper( $tagName ) . ' YESNO_SELECTLIST_NO NO', 'No' );
		}
		elseif ( $translate ) {
			$no	=	CBTxt::T( $no );
		}

		$arr	=	array(	moscomprofilerHTML::makeOption( '0', $no ),
			moscomprofilerHTML::makeOption( '1', $yes )
		);

		return moscomprofilerHTML::selectList( $arr, $tagName, $tagAttributes, 'value', 'text', (string) $selected, 2, true, false, false );
	}

	/**
	 * Creates a checkbox input array from $arr options list
	 *
	 * @param  array        $arr
	 * @param  string       $tagName
	 * @param  null|string  $tagAttributes
	 * @param  int|string   $key
	 * @param  string       $text
	 * @param  string       $selected
	 * @param  int          $required
	 * @param  null|array   $classes
	 * @param  boolean      $translate
	 * @return array
	 */
	public static function checkboxListArr( $arr, $tagName, $tagAttributes = null, $key = 'value', $text = 'text', $selected = null, $required = 0, $classes = null, $translate = true )
	{
		reset( $arr );

		$idName					=	moscomprofilerHTML::htmlId( $tagName );

		$html					=	array();

		if ( $classes === null ) {
			$classes			=	array();
		}

		if ( $required ) {
			$classes[]			=	'required';
		}

		foreach ( $arr as $i => $option ) {
			$k					=	$option->$key;
			$t					=	$option->$text;

			if ( isset( $option->id ) && ( trim( $option->id ) != '' ) ) {
				$id				=	$option->id;
			} else {
				$id				=	$idName . '__cbf' . $i;
			}

			$extra				=	' id="' . htmlspecialchars( $id ) . '"';

			if ( is_array( $selected ) ) {
				foreach ( $selected as $obj ) {
					if ( is_object( $obj ) ) {
						$k2		=	$obj->$key;
					} else {
						$k2		=	$obj;
					}

					if ( (string) $k === (string) $k2 ) {
						$extra	.=	' checked="checked"';
						break;
					}
				}
			} else {
				$extra			.=	( (string) $k === (string) $selected ? ' checked="checked"' : null );
			}

			if ( isset( $option->class ) && ( trim( $option->class ) != '' ) ) {
				$classes[]		=	htmlspecialchars( $option->class );
			}

			if ( count( $classes ) > 0 ) {
				$tagAttributes	.=	' class="' . implode( ' ', $classes ) . '"';
			}

			$extra				.=	( trim( $tagAttributes ) ? ' ' . $tagAttributes : null )
								.	( isset( $option->extra ) && $option->extra ? ' ' . $option->extra : null );

			$html[]				=	'<label for="' . htmlspecialchars( $id ) . '" class="checkbox-inline">'
				.		'<input type="checkbox" name="' . htmlspecialchars( $tagName ) . '" value="' . htmlspecialchars( $k ) . '"' . $extra . ' />'
				.		' ' . ( $translate ? CBTxt::Th( $t ) : $t ) . ' '
				.	'</label>';
		}

		return $html;
	}

	/**
	 * Returns a checkbox input list
	 *
	 * @param  array        $arr
	 * @param  string       $tagName
	 * @param  null|string  $tagAttributes
	 * @param  int|string   $key
	 * @param  string       $text
	 * @param  string       $selected
	 * @param  int          $required
	 * @param  null|array   $classes
	 * @param  boolean      $translate
	 * @return string
	 */
	public static function checkboxList( $arr, $tagName, $tagAttributes = null, $key = 'value', $text = 'text', $selected = null, $required = 0, $classes = null, $translate = true )
	{
		return "\n\t" . implode( "\n\t", moscomprofilerHTML::checkboxListArr( $arr, $tagName, $tagAttributes, $key, $text, $selected, $required, $classes, $translate ) ) . "\n";
	}

	/**
	 * Returns a checkbox input list formatted as a table
	 *
	 * @param  array        $arr
	 * @param  string       $tagName
	 * @param  null|string  $tagAttributes
	 * @param  int|string   $key
	 * @param  string       $text
	 * @param  string       $selected
	 * @param  int          $cols
	 * @param  int          $rows
	 * @param  int          $size
	 * @param  int          $required
	 * @param  null|array   $classes
	 * @param  null|string  $cellAttributes
	 * @param  boolean      $translate
	 * @return string
	 */
	public static function checkboxListTable( $arr, $tagName, $tagAttributes = null, $key = 'value', $text = 'text', $selected = null, $cols = 0, $rows = 0, $size = 0, $required = 0, $classes = null, $cellAttributes = null, $translate = true )
	{
		$cellsHtml	=	moscomprofilerHTML::checkboxListArr( $arr, $tagName, $tagAttributes, $key, $text, $selected, $required, $classes, $translate );

		return moscomprofilerHTML::list2Table( $cellsHtml, $cols, $rows, $size, $cellAttributes );
	}

	/**
	 * transforms a list of $cellsHtml into an HTML table of $rows or of $cols
	 *
	 * @param  string[]  $cellsHtml       HTML content of cells
	 * @param  int       $cols            Columns
	 * @param  int       $rows            Rows
	 * @param  int       $size            Width of table in em
	 * @param  string    $cellAttributes  Attributes to add to cells <td HERE>
	 * @return string                     Formatted HTML table
	 */
	public static function list2Table ( $cellsHtml, $cols, $rows, $size, $cellAttributes = null )
	{
		$cells					=	count( $cellsHtml );

		$size					=	(int) ( ( $size - ( $size % 3 ) ) / 3  ) * 2;	// int div  3 * 2 width/heigh ratio
		if ( $size == 0 ) {
			$localStyle			=	'';
		} else {
			$localStyle			=	' style="width:' . $size . 'em;"';
		}
		$return					=	'';
		if ( $cells ) {
			if ( $rows ) {
				$return			=	"\n\t" . '<table class="cbMulti"' . $localStyle . '>';

				for ( $lineIdx = 0, $n = min( $rows, $cells ) ; $lineIdx < $n ; $lineIdx++ ) {
					$return		.=	"\n\t\t<tr>";
					for ( $i = $lineIdx ; $i < $cells ; $i += $rows ) {
						$return	.=	'<td' . ( trim( $cellAttributes ) ? ' ' . $cellAttributes : null ) . '>' . $cellsHtml[$i] . '</td>';
					}
					$return 	.=	"</tr>\n";
				}
				$return			.=	"\t</table>\n";
			} elseif ( $cols ) {
				$return			=	"\n\t" . '<table class="cbMulti"' . $localStyle . '>';
				$idx			=	0;
				while ( $cells ) {
					$return		.=	"\n\t\t<tr>";
					for ( $i = 0, $n = min( $cells, $cols ); $i < $n; $i++, $cells-- ) {
						$return .=	"<td" . ( trim( $cellAttributes ) ? ' ' . $cellAttributes : null ) . ">".$cellsHtml[$idx++]."</td>";
					}
					$return		.=	"</tr>\n";
				}
				$return			.=	"\t</table>\n";
			} else {
				$return			=	"\n\t" . '<span class="cbSnglCtrlLbl"' . ( trim( $cellAttributes ) ? ' ' . $cellAttributes : null ) . '>' . implode( '</span><span class="cbSnglCtrlLbl"' . $cellAttributes . '>', $cellsHtml ) . "</span>\n";
			}
		}
		return $return;
	}

	/**
	 * Returns a validating unique id attribute based on name attribute
	 *
	 * @param  string  $name
	 * @return string
	 */
	public static function htmlId( $name )
	{
		return str_replace( array( '[', ']' ), array( '__', '' ), $name );
	}

	/**
	 * Simple Javascript email cloaking
	 * By default replaces an email with a mailto link with email cloacked
	 *
	 * @param  string   $mail
	 * @param  int      $mailTo
	 * @param  string   $text
	 * @param  int      $email
	 * @param  boolean  $cloakText
	 * @return string
	 */
	public static function emailCloaking( $mail, $mailTo = 1, $text = '', $email = 1, $cloakText = true )
	{
		global $_CB_framework;

		static $spanId	=	null;

		if ( $spanId == null ) {
			$spanId		=	rand( 1, 100000 );
		} else {
			$spanId		+=	1;
		}

		// convert text
		$mail 			=	moscomprofilerHTML::encoding_converter( $mail );

		// split email by @ symbol
		$mail			=	explode( '@', $mail );

		if ( count( $mail ) > 1 ) {
			$mail_parts	= explode( '.', $mail[1] );
		} else {
			$mail_parts	=	array( '' );
		}

		// random number
		$rand			=	rand( 1, 100000 );

		$replacement	=	'<span id="cbMa' . $spanId . '" class="cbMailRepl">...</span>';

		$js				=	'	{'
			.	"\n		var prefix='&#109;a'+'i&#108;'+'&#116;o';"
			.	"\n		var path = 'hr'+ 'ef'+'=';"
			.	"\n		var addy". $rand ."= '". @$mail[0] ."'+ '&#64;' +'". implode( "' + '&#46;' + '", $mail_parts ) ."';"
		;

		if ( $mailTo ) {
			// special handling when mail text is different from mail addy
			if ( $text ) {
				if ( $cloakText ) {
					if ( $email ) {
						// convert text
						$text 			=	moscomprofilerHTML::encoding_converter( $text );
						// split email by @ symbol
						$text 			=	explode( '@', $text );
						$text_parts		=	explode( '.', $text[1] );
						$js			 	.=	"\n		var addy_text". $rand ." = '". @$text[0] ."' + '&#64;' + '". implode( "' + '&#46;' + '", @$text_parts ) ."';";
					} else {
						$text 	= moscomprofilerHTML::encoding_converter( $text );
						$js				.=	"\n		var addy_text". $rand ." = '". $text ."';";
					}
				} else {
					$js					.=	"\n		var addy_text". $rand ." = '". $text ."';";
				}
				$js				.=	"\n		$('#cbMa" . $spanId . "').html("
					.				"'<a ' + path + '\\'' + prefix + ':' + addy". $rand ." + '\\'>'"
					.				" + addy_text". $rand
					.				" + '</a>'"
					.			");"
				;
			} else {
				$js				.=	"\n		$('#cbMa" . $spanId . "').html("
					.				"'<a ' + path + '\\'' + prefix + ':' + addy". $rand ." + '\\'>'"
					.				" + addy". $rand
					.				" + '</a>'"
					.			");"
				;
			}
		} else {
			$js				.=	"\n		$('#cbMa" . $spanId . "').html(addy". $rand . ");";
		}
		$js						.=	"\n	}";
		$replacement 	.=	"<noscript> \n";
		$replacement 	.=	CBTxt::Th( 'UE_CLOAKED', 'This e-mail address is protected from spam bots, you must enable JavaScript in your web browser to view it' );
		$replacement 	.=	"\n</noscript> \n";

		$_CB_framework->outputCbJQuery( $js );
		return $replacement;
	}

	/**
	 * Utility function for function emailCloaking
	 *
	 * @param  string  $text
	 * @return string
	 */
	private static function encoding_converter( $text )
	{
		// replace vowels with character encoding
		$text 	= str_replace( 'a', '&#97;', $text );
		$text 	= str_replace( 'e', '&#101;', $text );
		$text 	= str_replace( 'i', '&#105;', $text );
		$text 	= str_replace( 'o', '&#111;', $text );
		$text	= str_replace( 'u', '&#117;', $text );
		return addslashes( $text );
	}
}
