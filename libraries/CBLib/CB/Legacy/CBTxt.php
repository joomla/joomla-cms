<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 6/17/14 1:01 AM $
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Application\Application;

defined('CBLIB') or die();

/**
 * Legacy \CBTxt Class implementation
 * @deprecated 2.0 use \CBLib\Language\CBTxt
 * @see \CBLib\Language\CBTxt
 */
class CBTxt extends \CBLib\Language\CBTxt
{
	/**
	 * @deprecated 2.0 Use T() as it is always UTF8
	 *
	 * @param $english
	 * @return string
	 */
	static function Tutf8( $english )
	{
		return parent::T( $english );
	}

	/**
	 * Parse the string through static::T.
	 * That is, for a particular string find the corresponding translation.
	 * Variable subsitution is performed for the $args parameter.
	 * @since 1.3
	 * @deprecated 2.0 Use T() instead
	 *
	 * @param string   $english  the string to translate
	 * @param array    $args     a strtr-formatted array of string substitutions
	 * @return string
	 */
	public static function P( $english, $args = array() )
	{
		if ( $args === null ) {
			$args		=	array();
		}
		return parent::T( $english, null, $args );
	}

	/**
	 * Parse the string through static::Th.
	 * That is, for a particular string find the corresponding translation.
	 * Variable subsitution is performed for the $args parameter.
	 * @since 1.3
	 * @deprecated 2.0 Use T() instead
	 *
	 * @param string   $english  the string to translate
	 * @param array    $args     a strtr-formatted array of string substitutions
	 * @return string
	 */
	public static function Ph( $english, $args = array() )
	{
		if ( $args === null ) {
			$args		=	array();
		}
		return parent::Th( $english, null, $args );
	}

	/**
	 * Parse the string through static::Tutf8.
	 * That is, for a particular string find the corresponding translation.
	 * Variable subsitution is performed for the $args parameter.
	 * @since 1.3
	 * @deprecated 2.0 Use T() instead
	 *
	 * @param string   $english  the string to translate
	 * @param array    $args     a strtr-formatted array of string substitutions
	 * @return string
	 */
	public static function Putf8( $english, $args = array() )
	{
		return parent::T( $english, null, $args );
	}

	/**
	 * Converts UTF-8 string to CMS charset
	 * @deprecated 2.0 : Not needed anymore: CMS charset is always UTF-8
	 *
	 * @param  string  $string
	 * @return string
	 */
	public static function utf8ToISO( $string )
	{
		return $string;
	}

	/**
	 * Equivalent of html_entity_decode( $string ) using ENT_COMPAT and the charset of the system
	 * @since 1.2.2
	 *
	 * @param  string  $string
	 * @return string
	 */
	public static function html_entity_decode( $string )
	{
		return html_entity_decode( $string, ENT_COMPAT, 'UTF-8' );
	}

	/**
	 * html_entity_decode for all php versions
	 * @deprecated 2.0 Use native php function html_entity_decode( $string, $quotes, $charset );
	 *             (keep in 2.0 as CBSubs GPL 3.0.0 used it)
	 *
	 * @param  string  $string
	 * @param  int     $quotes
	 * @param  string  $charset
	 * @return string
	 */
	public static function _unhtmlentities( $string, $quotes = ENT_COMPAT, $charset = 'ISO-8859-1' )
	{
		return html_entity_decode( $string, $quotes, $charset );
	}

	/**
	 * Prepares the HTML $htmlText with triggering CMS Content Plugins
	 * @since 1.9
	 * @deprecated 2.0: Use Application::Cms()->prepareHtmlContentPlugins( $htmlText )
	 * @see CmsInterface::prepareHtmlContentPlugins()
	 * TODO: Seems not used outside CB: Remove for 2.1
	 *
	 * @param  string   $htmlText
	 * @return string
	 */
	public static function prepareHtmlContentPlugins( $htmlText )
	{
		return Application::Cms()->prepareHtmlContentPlugins( $htmlText );
	}

	/**
	 * Translates, prepares the HTML $htmlText with triggering CMS Content Plugins, replaces CB substitutions and extra HTML and non-HTML substitutions
	 * @deprecated 2.0: Use CBuser::replaceUserVars
	 * @see CBuser::replaceUserVars
	 *
	 * @param  string      $mainText
	 * @param  int         $user_id
	 * @param  boolean     $html
	 * @param  boolean     $translateMainText
	 * @param  boolean     $prepareHtmlContentPlugins
	 * @param  array|null  $extraHtmlStrings
	 * @param  array|null  $extraNonHtmlStrings
	 * @return string
	 */
	public static function replaceUserVars( $mainText, $user_id, $html, $translateMainText = true,
											$prepareHtmlContentPlugins = false,
											$extraHtmlStrings = null, $extraNonHtmlStrings = null )
	{
		if ( $translateMainText ) {
			$mainText		=	$html ? parent::Th( $mainText ) : parent::T( $mainText );
		}

		if ( $prepareHtmlContentPlugins ) {
			$mainText		=	Application::Cms()->prepareHtmlContentPlugins( $mainText );

			if ( ! $html ) {
				$mainText	=	strip_tags( $mainText );
			}
		}

		$cbUser				=	CBuser::getInstance( (int) $user_id );

		if ( ! $cbUser ) {
			$cbUser			=	CBuser::getInstance( null );
		}

		$mainText			=	$cbUser->replaceUserVars( $mainText, true, false, $extraNonHtmlStrings, false );

		if ( $extraHtmlStrings ) {
			foreach ( $extraHtmlStrings as $k => $v ) {
				$mainText	=	str_replace( "[$k]", $html ? $v : strip_tags( $v ), $mainText );
			}
		}

		return $mainText;
	}
}
