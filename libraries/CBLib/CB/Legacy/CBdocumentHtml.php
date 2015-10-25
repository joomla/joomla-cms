<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 6/16/14 6:37 PM $
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/


defined('CBLIB') or die();

/**
 * CBdocumentHtml Class implementation
 * CB HTML document class
 * Use only $_CB_framework->document to access its public functions
 *
 */
class CBdocumentHtml
{
	/**
	 * Output type
	 * @var string
	 */
	protected $_output				=	'html';

	/**
	 * HTML head elements to output
	 * (needs to be public for backwards compatibility)
	 * @var array
	 */
	public $_head					=	array();

	/**
	 * CMS Document
	 * @var JDocument
	 */
	protected $_cmsDoc				=	null;

	/**
	 * Are head elements already outputed ?
	 * @var boolean
	 */
	protected $_headsOutputed		=	true;

	/**
	 * LTR/TRL direction: 'ltr' or null
	 * @var string
	 */
	protected $_direction			=	null;

	/**
	 * Constructor
	 * @access private
	 *
	 * @param  callable        $getDocFunction
	 */
	public function __construct( $getDocFunction )
	{
		if ( $getDocFunction ) {
			$this->_cmsDoc			=	call_user_func_array( $getDocFunction, array() );
		}

		$this->_renderingInit();
	}

	/**
	 * Removes live_site from the URL making it relative
	 *
	 * @param  string  $url  The URL to make relative
	 * @return string
	 */
	public function makeUrlRelative( $url )
	{
		global $_CB_framework;

		$liveSite	=	$_CB_framework->getCfg( 'live_site' );

		if ( cbStartOfStringMatch( $url, $liveSite ) ) {
			$url	=	substr( $url, strlen( $liveSite ) );
		}

		return $url;
	}

	/**
	 * Sets or alters a meta tag.
	 *
	 * @param  string  $name        MUST BE LOWERCASE: Name or http-equiv tag: 'generator', 'description', ...
	 * @param  string  $content     Content tag value
	 * @param  boolean $http_equiv  META type "http-equiv" defaults to null
	 * @return void
	 */
	public function addHeadMetaData( $name, $content, $http_equiv = false )
	{
		if ( $this->_tryCmsDoc( 'setMetaData', array( $name, $content, $http_equiv ) ) ) {
			return;
		}

		if ( $http_equiv ) {
			$metaTag	=	array( 'http-equiv' => $name, 'content' => $content );
		} else {
			$metaTag	=	array( 'name' => $name, 'content' => $content );
		}

		$this->_head['metaTags'][$http_equiv][$name]	=	$metaTag;

		$this->_renderCheckOutput();
	}

	/**
	 * Adds <link $relType="$relation" href="$url" associativeImplode($attribs) />
	 *
	 * @param  string  $url          Href URL to the linked style sheet
	 * @param  string  $relation     Relation to link
	 * @param  string  $relType      'rel' (default) for forward, or 'rev' for reverse relation
	 * @param  array   $attributes   Additional attributes ( 'attrName' => 'attrValue' )
	 * @return void
	 */
	public function addHeadLinkCustom( $url, $relation, $relType = 'rel', $attributes = null )
	{
		static $i	=	0;

		if ( $attributes === null ) {
			$attributes	=	array();
		}

		if ( $this->_tryCmsDoc( 'addHeadLink', array( $url, $relation, $relType, $attributes ) ) ) {
			return;
		}

		$this->_head['linksCustom']['link'][$i]		=	array( $relType => $relation, 'href' => $url );

		if ( count( $attributes ) > 0 ) {
			$this->_head['linksCustom']['link'][$i]	=	array_merge( $this->_head['linksCustom']['link'][$i], $attributes );
		}

		$i			+=	1;

		$this->_renderCheckOutput();
	}

	/**
	 * Adds <link type="$type" rel="stylesheet" href="$url" media="$media" />
	 *
	 * @param  string  $url         Href URL to the linked style sheet (either full url, or if starting with '/', live_site will be prepended)
	 * @param  boolean $minVersion  If a minified version ".min.css" exists, will use that one when not debugging
	 * @param  string  $media       Media type for stylesheet
	 * @param  array   $attributes  Additional attributes ( 'attrName' => 'attrValue' )
	 * @param  string  $type        MUST BE LOWERCASE: Mime type ('text/css' by default)
	 * @return void
	 */
	public function addHeadStyleSheet( $url, $minVersion = false, $media = null, $attributes = null, $type = 'text/css' )
	{
		global $_CB_framework;

		if ( $attributes === null ) {
			$attributes			=	array();
		}

		// Remove live_site so we can add it properly below with versioning:
		$url					=	$this->makeUrlRelative( $url );

		if ( $url[0] == '/' ) {
			if ( substr( $url, -4, 4 ) == '.css' ) {

				$exists			=	false;

				if ( $minVersion && ! $_CB_framework->getCfg( 'debug' ) ) {
					$urlMin		=	str_replace( '.css', '.min.css', $url );
					$file		=	$_CB_framework->getCfg( 'absolute_path' ) . $urlMin;
					if ( file_exists( $file ) ) {
						$exists	=	true;
						$url	=	$this->addVersionFileUrl( $urlMin, $file );
					}
				}

				if ( ! $exists ) {
					$file		=	$_CB_framework->getCfg( 'absolute_path' ) . $url;
					if ( file_exists( $file ) ) {
						$url	=	$this->addVersionFileUrl( $url, $file );
					}
				}
			}
			if ( $_CB_framework->getUi() == 2 ) {
				$url			=	'..' . $url;		// relative paths in backend
			} else {
				$url			=	$_CB_framework->getCfg( 'live_site' ) . $url;
			}
		}

		if ( $this->_tryCmsDoc( 'addStyleSheet', array( $url, $type, $media, $attributes ) ) ) {
			return;
		}

		$this->_head['stylesheets'][$url]		=	array( 'type' => $type, 'rel' => 'stylesheet', 'href' => $url );

		if ( $media ) {
			$this->_head['stylesheets'][$url]['media']		=	$media;
		}

		if ( count( $attributes ) > 0 ) {
			$this->_head['stylesheets'][$url]	=	array_merge( $this->_head['stylesheets'][$url], $attributes );
		}

		$this->_renderCheckOutput();
	}

	/**
	 * Adds <style type="$type">$content</style>
	 *
	 * @param	string  $content   Style declarations
	 * @param	string  $type		Type of stylesheet (defaults to 'text/css')
	 * @return   void
	 */
	public function addHeadStyleInline( $content, $type = 'text/css' )
	{
		if ( $this->_tryCmsDoc( 'addStyleDeclaration', array( $content, $type ) ) ) {
			return;
		}

		$this->_head['styles'][$type][]	=	$content;

		$this->_renderCheckOutput();
	}

	/**
	 * Appends version to $url '?v=16CHARSMD5' depending on $file date and of this file too
	 *
	 * @param  string  $url   URL
	 * @param  string  $file  Existing filename with path
	 * @return string         $url with appended "?v=...."
	 */
	public function addVersionFileUrl( $url, $file )
	{
		global $_CB_framework, $ueConfig;

		return $url . '?v=' . substr( md5( $ueConfig['version'] . filemtime( $file ) . filemtime( __FILE__ ) . $_CB_framework->getCfg( 'live_site' ) ), 0, 16 );
	}

	/**
	 * Adds <script type="$type" src="$url"></script>
	 *
	 * @param  string        $url           Src of script (either full url, or if starting with '/', live_site will be prepended) DO htmlspecialchars BEFORE calling if needed (&->&amp;)
	 * @param  boolean       $minVersion    Minified version exist, named .min.js
	 * @param  string        $preScript     Script that must be just before the file inclusion
	 * @param  string        $postScript    Script that must be just after the file
	 * @param  string        $preCustom     Any html code just before the scripts incl. pre
	 * @param  string        $postCustom    Any html code just after the scripts incl. post
	 * @param  string|array  $type          String: type="$type" : MUST BE LOWERCASE: Type of script ('text/javascript' by default), Array: e.g. array( 'type' => 'text/javascript', 'charset' => 'utf-8' )
	 */
	public function addHeadScriptUrl( $url, $minVersion = false, $preScript = null, $postScript = null, $preCustom = null, $postCustom = null, $type = 'text/javascript' )
	{
		global $_CB_framework;

		// Remove live_site so we can add it properly below with versioning:
		$url				=	$this->makeUrlRelative( $url );

		if ( $minVersion && ! $_CB_framework->getCfg( 'debug' ) ) {
			$url			=	str_replace( '.js', '.min.js', $url );
		}

		if ( $url[0] == '/' ) {
			if ( substr( $url, -3 ) == '.js' ) {
				$file		=	$_CB_framework->getCfg( 'absolute_path' ) . $url;
				if ( file_exists( $file ) ) {
					$url	=	$this->addVersionFileUrl( $url, $file );
				}
			}
			if ( $_CB_framework->getUi() == 2 ) {
				$url	=	'..' . $url;		// relative paths in backend
			} else {
				$url	=	$_CB_framework->getCfg( 'live_site' ) . $url;
			}
		}

//		if ( ! $this->_tryCmsDoc( 'addScript', array( $url, $type ) ) ) {							// The core ones are broken as they do not keep the strict ordering of scripts
		$this->_head['scriptsUrl'][$url]		=	array( 'pre' => $preScript, 'post' => $postScript, 'preC' => $preCustom, 'postC' => $postCustom, 'type' => $type );

		$this->_renderCheckOutput();
//		}
	}

	/**
	 * Adds <script type="$type">$content</script>
	 *
	 * @param  string  $content  Script
	 * @param  string  $type     MUST BE LOWERCASE: Mime type ('text/javascript' by default)
	 */
	public function addHeadScriptDeclaration( $content, $type = 'text/javascript' )
	{
//		if ( ! $this->_tryCmsDoc( 'addScriptDeclaration', array( $content, $type ) ) ) {			// The core ones are broken as they do not keep the strict ordering of scripts
		$this->_head['scripts'][$type][]		=	$content;

		$this->_renderCheckOutput();
//		}
	}

	/**
	 * Adds custom $html into <head> portion
	 *
	 * @param  string  $html
	 */
	public function addHeadCustomHtml( $html )
	{
//		if ( ! $this->_tryCmsDoc( 'addCustomTag', array( $html ) ) ) {							// The core ones are broken as they do not keep the strict ordering of scripts
		$this->_head['custom'][]				=	$html;

		$this->_renderCheckOutput();
//		}
	}

	/**
	 * Sets the page title
	 *
	 * @param  string  $title
	 * @return void
	 */
	public function setPageTitle( $title )
	{
		if ( $this->_cmsDoc ) {
			$this->_cmsDoc->setTitle( $title );
		}
	}

	/**
	 * Returns direction 'ltr' or 'rtl' for global document
	 *
	 * @return   string  'ltr' for left-to-right or 'rtl' for right-to-left texts globally on the page
	 */
	public function getDirection( )
	{
		if ( $this->_direction === null ) {
			if ( $this->_cmsDoc ) {
				$this->_direction	=	$this->_cmsDoc->getDirection();
			} else {
				$this->_direction	=	'ltr';
			}
		}
		return $this->_direction;
	}

	/**
	 * Sets direction 'ltr' or 'rtl' for global document
	 *
	 * @param  string  $textDirection  'ltr' for left-to-right or 'rtl' for right-to-left texts globally on the page
	 */
	public function setDirection( $textDirection = 'ltr' )
	{
		if ( $this->_cmsDoc ) {
			$this->_cmsDoc->setDirection( $textDirection );
		}
		$this->_direction			=	$textDirection;
	}

	/**
	 * Tries to add head tags to CMS document.
	 * @access private
	 *
	 * @param  string   $type
	 * @param  array    $params
	 * @return boolean           Returns true for success and false if it couldn't use.
	 */
	protected function _tryCmsDoc( $type, $params )
	{
		if ( $this->_cmsDoc ) {
			call_user_func_array( array( $this->_cmsDoc, $type ), $params );

			return true;
		}

		if ( $this->_cmsDoc === false ) {
			// no html headers to output: do as if outputed so they get ignored:
			return true;
		}

		return false;
	}

	/**
	 * Resets heads to not be yet outputed
	 *
	 * @return void
	 */
	public function outputToHeadCollectionStart( )
	{
		$this->_headsOutputed		=	false;
	}

	/**
	 * Outputs the headers to the CMS handler or echos them if it can't
	 * @access private
	 *
	 * @return string|null   string for header to be echoed worst case, null if it could echo
	 */
	public function outputToHead( )
	{
		$customHead					=	$this->_renderHead();

		if ( $this->_tryCmsDoc( 'addCustomTag', array( $customHead ) ) ) {
			$this->_headsOutputed	=	true;

			return null;
		}

		return $customHead . "\n";
	}

	/**
	 * Tells if head is already outputed
	 * @since 2.0
	 *
	 * @return boolean  True: head is outputed
	 */
	public function isHeadOutputed( )
	{
		return $this->_headsOutputed;
	}

	/**
	 * Renders to echo the outputs to head
	 *
	 * @return void
	 */
	protected function _renderCheckOutput( )
	{
		if ( $this->_headsOutputed && ( $this->_cmsDoc !== false ) ) {
//			$customHead			=	$this->_renderHead();
//			echo $customHead;		// better late than never...
			echo $this->outputToHead();
		}
	}

	/**
	 * Reinit renderings
	 *
	 * @return void
	 */
	protected function _renderingInit( )
	{
		$this->_head				=	array(
												'metaTags'	  => array(),
												'linksCustom' => array(),
												'stylesheets' => array(),
												'styles'	  => array(),
												'scriptsUrl'  => array(),
												'scripts'	  => array(),
												'custom'	  => array()
											 );
	}

	/**
	 * Renders the portion going into the <head> if CMS doesn't support correct ordering
	 * @access private
	 *
	 * @return string    HTML for <head> or NULL if done by CMS
	 */
	protected function _renderHead( )
	{
		$html					=	null;

		if ( $this->_output == 'html' ) {

			if ( $this->_cmsDoc === null ) {
				// <base> is done outside

				// metaTags:
				foreach ( $this->_head['metaTags'] as $namContentArray ) {
					foreach ( $namContentArray as $metaTagAttrs ) {
						$html[]	=	$this->_renderTag( 'meta', $metaTagAttrs );
					}
				}
				// <title> is done outside

				// links, custom ones:
				foreach ( $this->_head['linksCustom'] as $tagName => $attributes ) {
					$html[]		=	$this->_renderTag( $tagName, $attributes );
				}

				// styleSheets first:
				foreach ( $this->_head['stylesheets'] as $styleSheet ) {
					$html[]		=	$this->_renderTag( 'link', $styleSheet );
				}

				// style inline:
				$html[]			=	$this->_renderInlineHelper( 'style', 'styles' );
			}

			// The core SCRIPT handlers are broken as they do not keep the strict ordering of scripts: so do it here as custom:
			// scriptsUrl:
			foreach ( $this->_head['scriptsUrl'] as $url => $tpp ) {
				$html[]			=	$tpp['preC']
					.	$this->_renderInlineScript( $tpp['pre'] )
					.	$this->_renderScriptUrlTag( $url, $tpp['type'] )
					.	$this->_renderInlineScript( $tpp['post'] )
					.	$tpp['postC']
				;
			}

			// scripts inline
			$html[]				=	$this->_renderInlineHelper( 'script', 'scripts' );
			;

			// if there are custom things:
			foreach ( $this->_head['custom'] as $custom ) {
				$html[]			=	$custom;
			}
		}

		// reset the headers, in case we get late callers from outside the component (modules):
		$this->_renderingInit();

		// finally transform to a string:
		return implode( "\n\t", $html );
	}

	/**
	 * Internal utility to render <$tag implode($attributes) />
	 * (NOT PART of CB API)
	 * @access private
	 *
	 * @param  string  $tag
	 * @param  array   $attributes
	 * @param  string  $tagClose    '/>' (default) or '>'
	 * @return string
	 */
	protected function _renderTag( $tag, $attributes, $tagClose = '/>' )
	{
		$html				=	'<' . $tag .' ';

		foreach ( $attributes as $attr => $val ) {
			$html			.= ' ' . $attr . '="' . $val . '"';
		}

		$html				.=	$tagClose;

		return $html;
	}

	/**
	 * Internal utility to render <script type="$type" src="$url"></script>
	 * (NOT PART of CB API)
	 * @access private
	 *
	 * @param  string  $url
	 * @param  array   $type
	 * @return string
	 */
	protected function _renderScriptUrlTag( $url, $type )
	{
		if ( is_string( $type ) ) {
			return '<script type="' . $type . '" src="' . $url . '"></script>';
		}

		$type['src']	=	$url;

		return $this->_renderTag( 'script', $type, '>' ) . '</script>';

	}

	/**
	 * Internal utility to render <$tag type="$type"><!-- implode($attributes) --></$tag>
	 * (NOT PART of CB API)
	 * @access private
	 *
	 * @param  string  $tag
	 * @param  string  $content
	 * @param  string  $type
	 * @return string
	 */
	protected function _renderInlineScript( $content, $tag = 'script', $type = 'text/javascript' )
	{
		if ( $content ) {
			return '<' . $tag . ' type="' . $type . '">'
			.	( $this->_output == 'html' ? "<!--\n" : "<![CDATA[\n" )
			.	$content
			.	( $this->_output == 'html' ? "\n-->" : "\n]]>" )
			.	'</' . $tag . '>'
				;
		}

		return null;
	}

	/**
	 * Internal utility to render an inline head portion (<style> or <script>)
	 * @access private
	 *
	 * @param  string  $tag  <$tag
	 * @param  string $head  index in $this->_head[$head] as array( $type => array( $contents ) )
	 * @return string        HTML
	 */
	protected function _renderInlineHelper( $tag, $head )
	{
		$html				=	null;

		foreach ( $this->_head[$head] as $type => $contentsArray ) {
			$html[]			=	$this->_renderInlineScript( implode( "\n\n", $contentsArray ), $tag, $type );
		}

		if ( $html !== null ) {
			return implode( "\n\t", $html );
		}

		return null;
	}
}
