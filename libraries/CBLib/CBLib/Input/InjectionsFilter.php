<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 20.06.13 13:36 $
* @package CBLib\Input
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CBLib\Input;

use CBLib\Application\Application;
use CBLib\DependencyInjection\Container;

defined('CBLIB') or die();

/**
 * CBLib\Input\InjectionsFilter Class implementation
 *
 * @author: Daniel Morris
 * Original contributors: Gianpaolo Racca, Ghislain Picard, Marco Wandschneider, Chris Tobin and Andrew Eddie, v.1.2.2_php4/php5.
 * Beat from joomlapolis.com: tightened multi-level arrays: see //BB below
 * @copyright: Daniel Morris (dan@rootcube.com)
 * @license: GNU General Public License (GPL) (v2 at time of publishing and usage in CB)
 */
class InjectionsFilter {
	protected static $instances     =   array();
	public $tagsArray;			// default = empty array
	public $attrArray;			// default = empty array

	public $tagsMethod;			// default = 0
	public $attrMethod;			// default = 0

	public $xssAuto;			// default = 1
	public $tagBlacklist	=	array(
									'applet',
									'body',
									'bgsound',
									'base',
									'basefont',
									'canvas',
									'embed',
									'frame',
									'frameset',
									'head',
									'html',
									'id',
									'iframe',
									'ilayer',
									'layer',
									'link',
									'meta',
									'name',
									'object',
									'script',
									'style',
									'title',
									'xml'
								);
	public $attrBlacklist	=	array('action', 'background', 'codebase', 'dynsrc', 'lowsrc');  // also will strip ALL event handlers

	/**
	 * Constructor. Only first parameter is required.
	 *
	 * @param  array  $tagsArray   List of user-defined tags
	 * @param  array  $attrArray   List of user-defined attributes
	 * @param  int    $tagsMethod  0= allow just user-defined,    1= allow all but user-defined
	 * @param  int    $attrMethod  0= allow just user-defined,    1= allow all but user-defined
	 * @param  int    $xssAuto     0= only auto clean essentials, 1= allow clean blacklisted tags/attr
	 */
	protected function __construct( $tagsArray = null, $attrArray = null, $tagsMethod = 0, $attrMethod = 0, $xssAuto = 1 ) {
		if ( $tagsArray === null ) {
			$tagsArray		=	array();
		}
		if ( $attrArray === null ) {
			$attrArray		=	array();
		}
		// make sure user defined arrays are in lowercase
		$this->tagsArray	=	array_map( 'strtolower', (array) $tagsArray );
		$this->attrArray	=	array_map( 'strtolower', (array) $attrArray );
		$this->tagsMethod	=	$tagsMethod;
		$this->attrMethod	=	$attrMethod;
		$this->xssAuto		=	$xssAuto;
	}

	/**
	 * Returns an input filter object, only creating it if it doesn't already exist.
	 *
	 * @param   array    $tagsArray   List of user-defined tags
	 * @param   array    $attrArray   List of user-defined attributes
	 * @param   integer  $tagsMethod  WhiteList method = 0, BlackList method = 1
	 * @param   integer  $attrMethod  WhiteList method = 0, BlackList method = 1
	 * @param   integer  $xssAuto     Only auto clean essentials = 0, Allow clean blacklisted tags/attr = 1
	 *
	 * @return  InjectionsFilter  The JFilterInput object.
	 *
	 * @since   11.1
	 */
	public static function &getInstance($tagsArray = array(), $attrArray = array(), $tagsMethod = 0, $attrMethod = 0, $xssAuto = 1)
	{
		$sig = serialize(array($tagsArray, $attrArray, $tagsMethod, $attrMethod, $xssAuto));

		if (empty(static::$instances[$sig]))
		{
			static::$instances[$sig] = new static($tagsArray, $attrArray, $tagsMethod, $attrMethod, $xssAuto);
		}

		return static::$instances[$sig];
	}

	/**
	 * Main Filtering method
	 *
	 * @param   string|array  $source
	 * @param   string        $method       'html' or 'text' only
	 * @param   array|null    $allowedTags  if $method is 'html' this is the list of allowed tags. if NULL: uses the Application Config setting.
	 * @return  string|array
	 *
	 * @throws \InvalidArgumentException
	 */
	public static function filter( $source, $method, array $allowedTags = null )
	{
		if ( $method == 'html' ) {
			$filter     =   static::getInstance( array(), array(), 1, 1 );

			if ( $allowedTags === null ) {
				$allowedTags		=	Application::Config()->get( 'html_filter_allowed_tags' );
			}

			if ( $allowedTags ) {
				$filter->tagBlacklist	=	array_diff( $filter->tagBlacklist, explode( ' ', $allowedTags ) );
			}

			return $filter->process( $source );
		}

		if ( $method == 'text' ) {
			$filter     =   static::getInstance();
			return $filter->process( $source );
		}

		throw new \InvalidArgumentException( __CLASS__ . '::' . __FUNCTION__ . ': Unexpected filter method.' );
	}

	/**
	 * Method to be called by another php script. Processes for XSS and specified bad code.
	 *
	 * @param  mixed  $source  Input string/array-of-string to be 'cleaned'
	 * @return mixed  $source  'Cleaned' version of input parameter
	 */
	public function process( $source ) {
		if ( is_array( $source ) ) {
			// clean all elements in this array
			foreach ( $source as $key => $value ) {
				// filter element for XSS and other 'bad' code etc.
				//BB: if (is_string($value)) $source[$key] = $this->remove($this->decode($value));
				$source[$key]		=	$this->process( $value );		//BB changed line before with this line to take in account multi-level arrays
			}
			return $source;
		} elseif ( is_string( $source ) && ( $source !== '' ) ) {
			// clean this string: Filter source for XSS and other 'bad' code etc:
			return $this->remove( $this->decode( $source ) );
		} else {
			// Not non-empty string or array: return parameter as given:
			return $source;
		}
	}

	/**
	 * Internal method to iteratively remove all unwanted tags and attributes
	 *
	 * @param String $source - input string to be 'cleaned'
	 * @return String $source - 'cleaned' version of input parameter
	 */
	protected function remove( $source ) {
		// provides nested-tag protection
		while ($source != $this->filterTags( $source ) ) {
			$source					=	$this->filterTags( $source );
		}
		return $source;
	}

	/**
	 * Internal method to strip a string of certain tags
	 *
	 * @param  string  $source  Input string to be 'cleaned'
	 * @return string  $source  'cleaned' version of input parameter
	 */
	protected function filterTags( $source ) {
		$source						=	$this->_fixIllegalCharsInAttributeValues( $source );
		// filter pass setup
		$preTag						=	NULL;
		$postTag					=	$source;
		// find initial tag's position
		$tagOpen_start				=	strpos( $source, '<' );
		// interate through string until no tags left
		while ( $tagOpen_start !== false ) {
			// process tag interatively
			$preTag					.=	substr( $postTag, 0, $tagOpen_start );
			$postTag				=	substr( $postTag, $tagOpen_start );
			$fromTagOpen			=	substr( $postTag, 1 );
			// end of tag
			$tagOpen_end			=	strpos( $fromTagOpen, '>' );
			$nextOpenTag			=	( strlen( $postTag ) > $tagOpen_start ) ? strpos( $postTag, '<', $tagOpen_start + 1 ) : false;
			if ( ( $nextOpenTag !== false ) && ( $nextOpenTag < $tagOpen_end ) ) {
				$postTag			=	substr( $postTag, 0, $tagOpen_start ) . substr( $postTag, $tagOpen_start + 1 );
				$tagOpen_start		=	strpos( $postTag, '<' );
				continue;
			}
			if ( $tagOpen_end === false ) {
				$postTag			=	substr( $postTag, $tagOpen_start + 1 );	//+
				$tagOpen_start		=	strpos( $postTag, '<' );				//+
				continue;														//+
			}
			// next start of tag (for nested tag assessment)
			$tagOpen_nested			=	strpos( $fromTagOpen, '<' );
			if ( ( $tagOpen_nested !== false ) && ( $tagOpen_nested < $tagOpen_end ) ) {
				$preTag				.=	substr( $postTag, 0, ( $tagOpen_nested+1 ) );
				$postTag			=	substr( $postTag, ( $tagOpen_nested+1 ) );
				$tagOpen_start		=	strpos( $postTag, '<' );
				continue;
			}
			$currentTag				=	substr( $fromTagOpen, 0, $tagOpen_end );
			$tagLength				=	strlen( $currentTag );
			// iterate through tag finding attribute pairs - setup
			$tagLeft				=	$currentTag;
			$attrSet				=	array();
			$currentSpace			=	strpos( $tagLeft, ' ' );
			if ( substr( $currentTag, 0, 1 ) == '/' ) {
				// is end tag:
				$isCloseTag			=	TRUE;
				list($tagName)		=	explode( ' ', $currentTag );
				$tagName			=	substr( $tagName, 1 );
			} else {
				// is start tag:
				$isCloseTag			=	FALSE;
				list($tagName)		=	explode( ' ', $currentTag );
			}
			// excludes all "non-regular" tagnames OR no tagname OR remove if xssauto is on and tag is blacklisted:
			if ( ( !preg_match( "/^[a-z][a-z0-9]*$/i" ,$tagName ) ) || ( ! $tagName ) || ( ( in_array( strtolower( $tagName ), $this->tagBlacklist ) ) && ( $this->xssAuto) ) ) {
				$postTag			=	substr( $postTag, ( $tagLength + 2 ) );
				$tagOpen_start		=	strpos( $postTag, '<' );
				// don't append this tag
				continue;
			}
			// this while is needed to support attribute values with spaces in!
			while ( $currentSpace !== FALSE ) {
				$attr				=	'';
				$fromSpace			=	substr( $tagLeft, ( $currentSpace+1 ) );
				$nextEqual			=	strpos( $fromSpace, '=' );
				$nextSpace			=	strpos( $fromSpace, ' ' );
				$openQuotes			=	strpos( $fromSpace, '"' );
				$closeQuotes		=	strpos( substr( $fromSpace, ( $openQuotes + 1 ) ), '"' ) + $openQuotes + 1;
				// Find position of = and ":
				if ( preg_match( '/\s*=\s*\"/', $fromSpace, $matches, PREG_OFFSET_CAPTURE ) ) {
					$startAtt		=	$matches[0][0];
					$startAttPosition	=	$matches[0][1];
					$closeQuotes	=	strpos( substr( $fromSpace, ( $startAttPosition + strlen( $startAtt ) ) ), '"' ) + $startAttPosition + strlen( $startAtt );
					$nextEqual		=	$startAttPosition + strpos( $startAtt, '=' );
					$openQuotes		=	$startAttPosition + strpos( $startAtt, '"' );
					$nextSpace		=	strpos( substr( $fromSpace, $closeQuotes ), ' ' ) + $closeQuotes;
				}
				// Attribute needs = sign:
				if ( ( $fromSpace != '/' ) && ( ( $nextEqual && $nextSpace && $nextSpace < $nextEqual ) || ! $nextEqual ) ) {
					if ( ! $nextEqual ) {
						$attribEnd	=	strpos( $fromSpace, '/' ) - 1;
					} else {
						$attribEnd	=	$nextSpace - 1;
					}
					// If there is an ending, use this, if not, do not worry.
					if ( $attribEnd > 0 ) {
						$fromSpace	=	substr( $fromSpace, $attribEnd + 1 );
					}
				}

				// another equals exists
				if ( strpos( $fromSpace, '=' ) !== FALSE ) {
					// opening and closing quotes exists
					if ( ( $openQuotes !== FALSE ) && ( strpos( substr( $fromSpace, ( $openQuotes + 1 ) ), '"' ) !== FALSE ) ) {
						$attr		=	substr( $fromSpace, 0, ( $closeQuotes + 1 ) );
					} else {
						// one or neither exist
						$attr		=	substr( $fromSpace, 0, $nextSpace );
					}
					// no more equals exist
				} elseif ( $fromSpace != '/' ) {
					$attr			=	substr( $fromSpace, 0, $nextSpace );
				}
				// last attr pair
				if ( !$attr && $fromSpace != '/' ) {
					$attr			=	$fromSpace;
				}
				// add to attribute pairs array
				$attrSet[]			=	$attr;
				// next inc
				$tagLeft			=	substr( $fromSpace, strlen( $attr ) );
				$currentSpace		=	strpos( $tagLeft, ' ');
			}
			// appears in array specified by user
			$tagFound				=	in_array( strtolower( $tagName ), $this->tagsArray );
			// remove this tag on condition
			if ( ( ! $tagFound && $this->tagsMethod ) || ( $tagFound && ! $this->tagsMethod ) ) {
				// reconstruct tag with allowed attributes
				if ( ! $isCloseTag ) {
					$attrSet		=	$this->filterAttr( $attrSet );
					$preTag			.=	'<' . $tagName;
					for ($i = 0, $cnt = count( $attrSet ); $i < $cnt; $i++ ) {
						$preTag		.=	' ' . $attrSet[$i];
					}
					// reformat single tags to XHTML
					if ( strpos( $fromTagOpen, "</" . $tagName ) ) {
						$preTag		.=	'>';
					}
					else {
						$preTag		.=	' />';
					}
					// just the tagname
				} else {
					$preTag			.=	'</' . $tagName . '>';
				}
			}
			// find next tag's start
			$postTag				=	substr( $postTag, ( $tagLength + 2 ) );
			$tagOpen_start			=	strpos( $postTag, '<' );
		}
		// append any code after end of tags
		if ( $postTag != '<' ) {		//+
			$preTag					.=	$postTag;
		}
		return $preTag;
	}

	/**
	 * Internal method to strip a tag of certain attributes
	 *
	 * @param  array  $attrSet
	 * @return array  $newSet
	 */
	protected function filterAttr( $attrSet ) {
		$newSet						=	array();
		// process attributes
		for ( $i = 0, $cnt = count( $attrSet ); $i < $cnt; $i++ ) {
			// skip blank spaces in tag
			if ( ! $attrSet[$i] ) {
				continue;
			}
			// split into attr name and value
			$attrSubSet				=	explode( '=', trim( $attrSet[$i] ), 2 );		//+ ',2'
			list($attrSubSet[0])	=	explode( ' ', $attrSubSet[0] );
			// removes all "non-regular" attr names AND also attr blacklisted
			if (( ! preg_match( '/^[a-zA-Z]*$/', $attrSubSet[0] ) ) || (($this->xssAuto) && ((in_array(strtolower($attrSubSet[0]), $this->attrBlacklist)) || (substr($attrSubSet[0], 0, 2) == 'on'))) || ! isset($attrSubSet[1] ) ) {		//BB replaced eregi by pregmatch added last ! isset($attrSubSet[1]
				continue;
			}
			// xss attr value filtering
			if ( $attrSubSet[1] ) {
				// strips unicode, hex, etc
				$attrSubSet[1]		=	str_replace( '&#', '', trim( $attrSubSet[1] ) );
				// strip normal newline within attr value
				$attrSubSet[1]		=	preg_replace( '/[\n\r]+/', '', $attrSubSet[1] );
				// strip double quotes
				$attrSubSet[1]		=	str_replace( '"', '', $attrSubSet[1] );
				// [requested feature] convert single quotes from either side to doubles (Single quotes shouldn't be used to pad attr value)
				if ( ( substr($attrSubSet[1], 0, 1) == "'" ) && ( substr( $attrSubSet[1], ( strlen( $attrSubSet[1] ) - 1 ), 1 ) == "'" ) ) {
					$attrSubSet[1]	=	substr( $attrSubSet[1], 1, ( strlen( $attrSubSet[1] ) - 2 ) );
				}
				// strip slashes
				$attrSubSet[1]		=	stripslashes( $attrSubSet[1] );
			}
			// auto strip attr's with "javascript:
			$attrSubSet1Lower		=	strtolower( $attrSubSet[1] );
			if (	( (strpos( $attrSubSet1Lower, 'expression') !== false ) &&	( strtolower( $attrSubSet[0] ) == 'style') ) ||
				( strpos( $attrSubSet1Lower, 'javascript:') !== false ) ||
				( strpos( $attrSubSet1Lower, 'behaviour:') !== false ) ||
				( strpos( $attrSubSet1Lower, 'vbscript:') !== false ) ||
				( strpos( $attrSubSet1Lower, 'mocha:') !== false ) ||
				( strpos( $attrSubSet1Lower, 'livescript:') !== false )
			) {
				continue;
			}

			// if matches user defined array
			$attrFound				=	in_array( strtolower( $attrSubSet[0] ), $this->attrArray );
			// keep this attr on condition
			if ( ( ! $attrFound && $this->attrMethod ) || ( $attrFound && !$this->attrMethod ) ) {
				if ( empty( $attrSubSet[1] ) === false ) {
					// attr has value:
					$newSet[]		=	$attrSubSet[0] . '="' . $attrSubSet[1] . '"';
				}
				elseif ( $attrSubSet[1] === '0' ) {
					// attr has decimal zero as value:
					$newSet[]		=	$attrSubSet[0] . '="0"';
				} else {
					// leave empty attributes empty:
					$newSet[]		=	$attrSubSet[0] . '=""';
				}
			}
		}
		return $newSet;
	}

	/**
	 * Try to convert to plaintext
	 *
	 * @param  string  $source
	 * @return string  $source
	 */
	public function decode( $source ) {
		// Entity decode
		$source	    		=	html_entity_decode($source, ENT_QUOTES, 'UTF-8');			//BB: changed from "ISO-8859-1"

		if ( version_compare( phpversion(), '5.4.0', '>=' ) ) {
			// convert decimal
			//BB	$source = preg_replace('/&#(\d+);/me',"chr(\\1)", $source);				// decimal notation
			$source			=	preg_replace_callback('/&#(\d+);/m', function ($m) {
				return utf8_encode(chr($m[1]));
			},
				$source);
			// convert hex
			//BB	$source = preg_replace('/&#x([a-f0-9]+);/mei',"chr(0x\\1)", $source);	// hex notation
			$source			=	preg_replace_callback('/&#x([a-f0-9]+);/mi', function ($m) {
				return utf8_encode(chr('0x'.$m[1]));
			},
				$source);
		} else {
			// convert decimal
			//BB	$source = preg_replace('/&#(\d+);/me',"chr(\\1)", $source);				// decimal notation
			$source			=	preg_replace('/&#(\d+);/me', "utf8_encode(chr(\\1))", $source);				// decimal notation
			// convert hex
			//BB	$source = preg_replace('/&#x([a-f0-9]+);/mei',"chr(0x\\1)", $source);	// hex notation
			$source			=	preg_replace('/&#x([a-f0-9]+);/mei',"utf8_encode(chr(0x\\1))", $source);	// hex notation
		}
		return $source;
	}
	/**
	 * Fixes illegal characters inside attributes
	 *
	 * @param  string  $source
	 * @return string  $source filtered
	 */
	protected function _fixIllegalCharsInAttributeValues( $source ) {
		$alreadyFiltered		=	'';
		$remainder				=	$source;
		$badChars				=	array( '<', '"', '>' );
		$escapedChars			=	array( '&lt;', '&quot;', '&gt;' );

		// Look for '="' , '"<space>' , '"/>', or '">':
		while ( preg_match( '#<[^>]*?=\s*?(\"|\')#s', $remainder, $matches, PREG_OFFSET_CAPTURE ) ) {
			// get the portion before the attribute value:
			$quotePosition		=	$matches[0][1];
			$nextBefore			=	$quotePosition + strlen( $matches[0][0] );
			// Check for ' or " and closing "/>, ">, "<space>, or " at the end of the string:
			$quote				=	substr( $matches[0][0], -1 );
			$pregMatch			=	( $quote == '"' ) ? '/(\"\s*\/\s*>|\"\s*>|\"\s+|\"$)/' : '/(\'\s*\/\s*>|\'\s*>|\'\s+|\'$)/';

			// get the portion after attribute value:
			if ( preg_match( $pregMatch, substr( $remainder, $nextBefore ), $matches, PREG_OFFSET_CAPTURE ) ) {
				// We have a closing quote:
				$nextAfter		=	$nextBefore + $matches[0][1];
			} else {
				// No closing quote:
				$nextAfter		=	strlen( $remainder );
			}
			$attributeValue		=	substr( $remainder, $nextBefore, $nextAfter - $nextBefore );

			// Escape bad chars:
			$attributeValue		=	str_replace( $badChars, $escapedChars, $attributeValue );
			$attributeValue		=	$this->_stripCSSExpressions( $attributeValue );
			$alreadyFiltered	.=	substr( $remainder, 0, $nextBefore ) . $attributeValue . $quote;
			$remainder			=	substr( $remainder, $nextAfter + 1 );
		}
		return $alreadyFiltered . $remainder;
	}
	/**
	 * Remove CSS: "<property>:expression(...)"
	 *
	 * @param  string  $source
	 * @return string  $source filtered
	 */
	protected function _stripCSSExpressions( $source ) {
		// Strip /*...*/ comments:
		$test					=	preg_replace( '#\/\*.*\*\/#U', '', $source );
		if ( stripos($test, ':expression' ) && preg_match_all( '#:expression\s*\(#i', $test, $matches ) ) {
			$source				=	str_ireplace( ':expression', '', $test );
		}
		return $source;
	}
}
