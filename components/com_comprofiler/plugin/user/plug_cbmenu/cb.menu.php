<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

use CBLib\Application\Application;
use CBLib\Language\CBTxt;

global $_PLUGINS;
$_PLUGINS->registerFunction( 'onPrepareMenus', 'prepareMenu','getMenuTab' );
$_PLUGINS->registerFunction( 'onPrepareMenus', 'prepareStatus','getStatusTab' );

class cbMenu
{
	protected $id			= null;
	protected $name			= null;
	protected $link			= null;
	protected $target		= null;
	protected $imgHTML		= null;
	protected $alt			= null;
	protected $tooltip		= null;
	protected $keystroke	= null;
	protected $class		= null;
	protected $topName		= null;
	protected $menuid		= null;

	/**
	 * @param int $id
	 * @param string $caption
	 * @param string $url
	 * @param null|string $target
	 * @param null|string $imgHTML
	 * @param null|string $alt
	 * @param null|string $tooltip
	 * @param null|string $keystroke
	 * @param null|string $class
	 * @param null|string $topCaption
	 * @param null|string $menuid
	 */
	public function setMenu( $id, $caption, $url, $target = null, $imgHTML = null, $alt = null, $tooltip = null, $keystroke = null, $class = null, $topCaption = null, $menuid = null )
	{
		$this->id			= $id;
		$this->name			= $caption;
		$this->link			= $url;
		$this->target		= $target;
		$this->imgHTML		= $imgHTML;
		$this->alt			= $alt;
		$this->tooltip		= $tooltip;
		$this->keystroke	= $keystroke;
		$this->class		= $class;
		$this->topName		= $topCaption;
		$this->menuid		= $menuid;
	}

	/**
	 * @param int $level
	 * @param int $idCounter
	 * @param null|string $key
	 */
	public function displayMenuItem( $level, $idCounter, $key = null )
	{
	}
}

class cbMenuHandler
{
	protected $items					=	array();
	protected $idCounter				=	9001;
	protected $oVarName					=	'name';
	protected $oVarLink					=	'link';
	protected $oVarDisplayClassName		=	'cbMenu';
	protected $oVarDisplayMethodName	=	'displayMenuItem';
	protected $class					=	null;
	protected $htmlBegin				=	null;
	protected $htmlEnd					=	null;
	protected $htmlDown					=	null;
	protected $htmlUp					=	null;
	protected $htmlLeaf					=	null;
	protected $htmlText					=	null;
	protected $htmlSeparator			=	null;
	public $js							=	null;
	public $jQuery						=	null;

	/**
	 * array( 'KEY' => 'Translation', ... ) for top-level menu entries
	 * @var array
	 */
	private static $translations	=	array();

	public function __construct( )
	{
		$this->htmlBegin		=	'<div class="%s">';

		$this->htmlEnd			=	'</div>';

		$this->htmlDown			=	array();

		$this->htmlDown[]		=	'<div class="cbMenuLevel1" id="cbMenuId%2$s">'
								.		'<div class="MenuLevel1txt">%1$s</div>';

		$this->htmlDown[]		=	'<div class="cbMenuLevel2" id="cbMenuId%2$s">'
								.		'<div class="MenuLevel2txt">%1$s</div>';

		$this->htmlDown[]		=	'<div class="cbMenuLevel3" id="cbMenuId%2$s">'
								.		'<div class="MenuLevel3txt">%1$s</div>';

		$this->htmlUp			=	array();

		$this->htmlUp[]			=	'</div>';

		$this->htmlUp[]			=	'</div>';

		$this->htmlUp[]			=	'</div>';

		$this->htmlLeaf			=	array();

		$this->htmlLeaf[]		=	'<div class="cbMenuSingleText">%s</div>';

		$this->htmlLeaf[]		=	'<div class="cbMenuLeaf1">' . '<a href="%s">%s</a>' . '</div>';

		$this->htmlLeaf[]		=	'<div class="cbMenuLeaf2">' . '<a href="%s">%s</a>' . '</div>';

		$this->htmlLeaf[]		=	'<div class="cbMenuLeaf3">' . '<a href="%s">%s</a>' . '</div>';

		$this->htmlLeaf[]		=	'<div class="cbMenuLeaf4">' . '<a href="%s">%s</a>' . '</div>';

		$this->htmlText			=	array();

		$this->htmlText[]		=	null;

		$this->htmlText[]		=	'<div class="cbMenuLeaf1">%s</div>';

		$this->htmlText[]		=	'<div class="cbMenuLeaf2">%s</div>';

		$this->htmlText[]		=	'<div class="cbMenuLeaf3">%s</div>';

		$this->htmlText[]		=	'<div class="cbMenuLeaf4">%s</div>';

		$this->htmlSeparator	=	array();

		$this->htmlSeparator[]	=	null;

		$this->htmlSeparator[]	=	'%s<span class="cbMenuSeparator1"><hr /></span>';

		$this->htmlSeparator[]	=	'%s<span class="cbMenuSeparator2"><hr /></span>';

		$this->htmlSeparator[]	=	'%s<span class="cbMenuSeparator3"><hr /></span>';

		$this->htmlSeparator[]	=	'%s<span class="cbMenuSeparator4"><hr /></span>';
	}

	/**
	 * @param array $array
	 */
	public function addArrayItem( $array )
	{
		$this->items	=	$this->multimerge( $this->items, $array );
	}

	/**
	 * @param array $arrayPos
	 * @param string $caption
	 * @param null|string $url
	 * @param null|string $target
	 * @param null|string $img
	 * @param null|string $alt
	 * @param null|string $tooltip
	 * @param null|string $keystroke
	 * @param null|string $menuid
	 */
	public function addObjectItem( $arrayPos, $caption, $url = null, $target = null, $img = null, $alt = null, $tooltip = null, $keystroke = null, /** @noinspection PhpUnusedParameterInspection */ $menuid = null )
	{
		if ( $target == '_self' ) {
			$target		=	null;
		}

		$a				=	&$arrayPos;
		$k				=	null;
		$topK			=	null;

		while ( is_array( $a ) ) {
			$topK		=	$k;
			$k			=	key( $a );
			$a			=	&$a[key( $a )];
		}

		if ( $topK === null ) {
			$topK		=	$k;
		}

		/** @var cbMenu $itm */
		$itm			=	new $this->oVarDisplayClassName();

		$itm->setMenu( $this->idCounter++, $caption, $url, $target, $img, $alt, $tooltip, $keystroke, $this->class, $topK, $k );

		$a				=	$itm;

		$this->items	=	$this->multimerge( $this->items, $arrayPos );
	}

	/**
	 * @param array $arrayPos
	 */
	public function addSeparator( $arrayPos )
	{
		$a				=	&$arrayPos;

		while ( is_array( $a ) ) {
			$a			=	&$a[key( $a )];
		}

		$a				=	null;

		$this->items	=	$this->multimerge( $this->items, $arrayPos );
	}

	/**
	 * @param  int          $idCounterStart
	 * @param  null|string  $menuClass
	 * @param  null|string  $callBackFunc
	 * @return string
	 */
	public function displayMenu( &$idCounterStart, $menuClass = null, $callBackFunc = null )
	{
		if ( $menuClass === null ) {
			$menuClass		=	$this->oVarDisplayClassName;
		}

		if ( $callBackFunc === null ) {
			$callBackFunc	=	array( $this, 'callBack' );
		}
		$params				=	array( 'level' => 0, 'idCounter' => $idCounterStart, 'nbMainMenus' => count( $this->items ) );

		if ( is_array( $this->items ) && ( count( $this->items ) > 0 ) ) {
			$return			=	call_user_func_array( $callBackFunc, array( &$params, 'begin', $menuClass, $this->items ) )
							.	$this->_displayMenu( $callBackFunc, $params, null, $this->items )
							.	call_user_func_array( $callBackFunc, array( &$params, 'end', null, $this->items ) );
		} else {
			$return			=	null;
		}

		$idCounterStart		=	$params['idCounter'];

		return $return;
	}

	/**
	 * @param string $callBackFunc
	 * @param array $params
	 * @param string $key
	 * @param mixed $value
	 * @return string
	 */
	public function _displayMenu( $callBackFunc, &$params, $key, $value )
	{
		$return					=	null;

		if ( is_array( $value ) ) {
			foreach ( $value as $k => $v ) {
				if ( is_array( $v ) ) {
					$translatedTitle	=	$this->languageTranslate( $k );
					$return		.=	call_user_func_array( $callBackFunc, array( &$params, 'down', $k, $translatedTitle ) );
				}

				$return			.=	$this->_displayMenu( $callBackFunc, $params, $k, $v );

				if ( is_array( $v ) ) {
					$return		.=	call_user_func_array( $callBackFunc, array( &$params, 'up', $k, $v ) );
				}
			}

			reset( $value );
		} else {
			$return				.=	call_user_func_array( $callBackFunc, array( &$params, 'leaf', $key, $value ) );
		}

		return $return;
	}

	/**
	 * Adds a $translated string for a $key
	 *
	 * @param  string  $key         Key
	 * @param  string  $translated  Translated string
	 * @return void
	 */
	public function addTranslation( $key, $translated )
	{
		self::$translations[$key]	=	$translated;
	}

	/**
	 * Gets a translated string for $string, if translation is defined for the $string key
	 * (that is only for top-level menu items)
	 *
	 * @param  string  $string  String or key
	 * @return string           String
	 */
	public function languageTranslate( $string )
	{
		if ( isset( self::$translations[$string] ) ) {
			return self::$translations[$string];
		}

		return $string;
	}

	/**
	 * @param array $params
	 * @param string $action
	 * @param string $key
	 * @param mixed $val
	 * @return null|string
	 */
	public function callBack( &$params, $action, $key, $val )
	{
		$return									=	null;
		$levelNow								=	$params['level'];

		switch ( $action ) {
			case 'begin':
				$return							.=	sprintf( $this->htmlBegin, $key );		// key is $menuClass in this particular case

				$params['level']				+=	1;
				break;
			case 'end':
				$params['level']				-=	1;

				$return							.=	sprintf( $this->htmlEnd, $params['nbMainMenus'] );
				break;
			case 'down':
				$return							.=	sprintf( $this->htmlDown[$params['level']-1], $val, $params['idCounter']++, $key );

				$params['level']				+=	1;
				break;
			case 'up':
				$params['level']				-=	1;

				$return							.=	$this->htmlUp[$params['level']-1];
				break;
			case 'leaf':
				if ( $val === null ) {
					$return						.=	sprintf( $this->htmlSeparator[$params['level']], $this->languageTranslate( $key ), $params['idCounter'] );
				} elseif ( $val === "" ) {
					$return						.=	sprintf( $this->htmlText[$params['level']], $this->languageTranslate( $key ), $params['idCounter'] );
				} else {
					if ( is_object( $val ) ) {
						if ( method_exists( $val, $this->oVarDisplayMethodName ) ) {
							$displayMethodName	=	$this->oVarDisplayMethodName;

							$return				.=	$val->$displayMethodName( $params['level'], $params['idCounter'], $key );
						} else {
							$l					=	$this->oVarLink;
							$n					=	$this->oVarName;

							$return				.=	sprintf( $this->htmlLeaf[$params['level']], $val->$l, $val->$n, $params['idCounter'] );
						}
					} else {
						$return					.=	sprintf( $this->htmlLeaf[$params['level']], $val, $this->languageTranslate( $key ), $params['idCounter'] );
					}
				}

				if ( $params['level'] == 1 ) {
					$params['idCounter']++;
				}
				break;
			default:
				break;
		}

		return $return;
	}

	/**
	 * @param string $property
	 * @param mixed $value
	 */
	public function set( $property, $value )
	{
		$this->$property	=	$value;
	}

	/**
	 * @param array $array1
	 * @param array $array2
	 * @return array
	 */
	protected function multimerge( $array1, $array2 )
	{
		if ( is_array( $array2 ) && count( $array2 ) ) {
			foreach ( $array2 as $k => $v ) {
				if ( is_array( $v ) && count( $v ) && isset( $array1[$k] ) ) {
					$array1[$k]	=	$this->multimerge( $array1[$k], $v );
				} else {
					$array1[$k]	=	$v;
				}
			}
		} else {
			$array1				=	$array2;
		}

		return $array1;
	}
}

class cbMenuBest extends cbMenu
{
	/**
	 * @param int $level
	 * @param int $idCounter
	 * @param string $key
	 * @return null|string
	 */
	public function displayMenuItem( $level, $idCounter, $key = null )
	{
		$return					=	null;

		switch ( $level ) {
			case 0:
				$return			.=	'<div class="cbMenuSingleText">' . $this->name . '</div>';
				break;
			case 1:
				$return			.=	'<li id="menu' . $idCounter . '" class="cbMenu cbTooltip dropdown" data-cbtooltip-tooltip-target="#ssmenu%3$d" data-cbtooltip-menu="true" data-cbtooltip-classes="qtip-nostyle" data-cbtooltip-adjust-y="0" data-cbtooltip-open-classes="open">';

				if ( substr( ltrim( $this->link ), 0, 1 ) == '<' ) {
					$return		.=	$this->link;
				} else {
					$return		.=	'<a href="' . $this->link . '"';

					if ( isset( $this->class ) && $this->class ) {
						$return	.=	' class="' . $this->class . '"';
					}

					if ( isset( $this->target ) && $this->target ) {
						$return	.=	' target="' . $this->target . '"';
					}

					if ( isset( $this->tooltip ) && $this->tooltip ) {
						$return	.=	' title="' . $this->tooltip . '"';
					}

					$return		.=	'>';

					if ( isset( $this->imgHTML ) && $this->imgHTML ) {
						$return	.=	$this->imgHTML;
					}

					$return		.=	$this->name . '</a>';
				}

				$return			.=	'</li>';
				break;
			case 2:
				$return			.=	'<li class="cbMenuLeaf2 cbMenu' . $key . '">';

				if ( substr( ltrim( $this->link ), 0, 1 ) == '<' ) {
					$return		.=	$this->link;
				} else {
					$return		.=	'<a href="' . $this->link . '"';

					if ( isset( $this->class ) && $this->class ) {
						$return	.=	' class="' . $this->class . '"';
					}

					if ( isset( $this->target ) && $this->target ) {
						$return	.=	' target="' . $this->target . '"';
					}

					if ( isset( $this->tooltip ) && $this->tooltip ) {
						$return	.=	' title="' . $this->tooltip . '"';
					}

					$return		.=	'>';

					if ( isset( $this->imgHTML ) && $this->imgHTML ) {
						$return	.=	$this->imgHTML;
					}

					$return		.=	$this->name . '</a>';
				}

				$return			.=	'</li>';
				break;
			default:
		}

		return $return;
	}
}

class cbBarMenuHandler extends cbMenuHandler
{
	public function __construct( )
	{
		$this->jQuery			=	"$( '.cbMenuNavBar' ).on( 'click', '.navbar-toggle', function() {"
								.		"if ( ! $( this ).hasClass( 'dropdown-toggle' ) ) {"
								.			"var navbar = $( this ).closest( '.cbMenuNavBar' ).find( '.navbar-collapse' );"
								.			"var toggle = $( this ).closest( '.cbMenuNavBar' ).find( '.navbar-toggle' );"
								.			"if ( toggle.hasClass( 'collapsed' ) ) {"
								.				"navbar.addClass( 'in' );"
								.				"toggle.removeClass( 'collapsed' );"
								.			"} else {"
								.				"navbar.removeClass( 'in' );"
								.				"toggle.addClass( 'collapsed' );"
								.			"}"
								.		"}"
								.	"}).find( '.cbScroller' ).cbscroller({"
								.		"height: false"
								.	"});";

		$this->htmlBegin		=	'<div class="cbMenuNavBar navbar navbar-default">'
								.		'<div class="container-fluid">'
								.			'<div class="navbar-header">'
								.				'<button type="button" class="cbMenuNavBarToggle navbar-toggle collapsed">'
								.					'<span class="icon-bar"></span>'
								.					'<span class="icon-bar"></span>'
								.					'<span class="icon-bar"></span>'
								.				'</button>'
								.			'</div>'
								.			'<div class="collapse navbar-collapse cbScroller">'
								.				'<div class="cbScrollerLeft hidden">'
								.					'<button type="button" class="btn btn-xs btn-default"><span class="fa fa-angle-left"></span></button>'
								.				'</div>'
								.				'<ul class="cbMenuNav nav navbar-nav cbScrollerContent">';

		$this->htmlEnd			=				'</ul>'
								.				'<div class="cbScrollerRight hidden">'
								.					'<button type="button" class="btn btn-xs btn-default"><span class="fa fa-angle-right"></span></button>'
								.				'</div>'
								.			'</div>'
								.		'</div>'
								.	'</div>';

		$this->htmlDown			=	array();

		$this->htmlDown[]		=	'<li id="menu%2$d" class="cbMenu cbMenu%3$s cbTooltip dropdown" data-cbtooltip-tooltip-target="#ssmenu%2$d" data-cbtooltip-menu="true" data-cbtooltip-classes="qtip-nostyle cbMenuDropdown" data-cbtooltip-adjust-y="0" data-cbtooltip-open-classes="open active">'
								.		'<a href="javascript: void( 0 );" class="dropdown-toggle">%1$s <b class="fa fa-caret-down"></b></a>'
								.			'<ul id="ssmenu%2$d" class="cbSubMenu dropdown-menu">';

		$this->htmlDown[]		=	'<li id="menu%2$d" class="cbMenuL2 cbMenu%3$s cbTooltip dropdown" data-cbtooltip-tooltip-target="#ssmenu%2$d" data-cbtooltip-menu="true" data-cbtooltip-classes="qtip-nostyle cbMenuDropdown" data-cbtooltip-adjust-y="0" data-cbtooltip-open-classes="open active">'
								.		'<a href="javascript: void( 0 );" class="dropdown-toggle">%1$s <b class="fa fa-caret-down"></b></a>'
								.			'<ul id="ssmenu%2$d" class="cbSubMenuL2 dropdown-menu">';

		$this->htmlDown[]		=	'<li id="menu%2$d" class="cbMenuL3 cbMenu%3$s cbTooltip dropdown" data-cbtooltip-tooltip-target="#ssmenu%2$d" data-cbtooltip-menu="true" data-cbtooltip-classes="qtip-nostyle cbMenuDropdown" data-cbtooltip-adjust-y="0" data-cbtooltip-open-classes="open active">'
								.		'<a href="javascript: void( 0 );" class="dropdown-toggle">%1$s <b class="fa fa-caret-down"></b></a>'
								.			'<ul id="ssmenu%2$d" class="cbSubMenuL3 dropdown-menu">';

		$this->htmlUp			=	array();

		$this->htmlUp[]			=		'</ul>'
								.	'</li>';

		$this->htmlUp[]			=		'</ul>'
								.	'</li>';

		$this->htmlUp[]			=		'</ul>'
								.	'</li>';

		$this->htmlLeaf			=	array();

		$this->htmlLeaf[]		=	'<div class="cbMenuSingleText">%s</div>';

		$this->htmlLeaf[]		=	'<p id="menu%3$d" class="cbMenu cbTooltip dropdown" data-cbtooltip-tooltip-target="#ssmenu%3$d" data-cbtooltip-menu="true" data-cbtooltip-classes="qtip-nostyle" data-cbtooltip-adjust-y="0" data-cbtooltip-open-classes="open">'
								.		'<a href="%1$s">%2$s <b class="fa fa-caret-down"></b></a>'
								.	'</p>';

		$this->htmlLeaf[]		=	'<li class="cbMenuLeaf2 cbMenu%3$s">' . '<a href="%s">%s</a>' . '</li>';

		$this->htmlLeaf[]		=	'<div class="cbMenuLeaf3">' . '<a href="%s">%s</a>' . '</div>';

		$this->htmlLeaf[]		=	'<div class="cbMenuLeaf4">' . '<a href="%s">%s</a>' . '</div>';

		$this->htmlText			=	array();

		$this->htmlText[]		=	'<div class="cbMenuLeaf1">%s</div>';

		$this->htmlText[]		=	'<li class="cbMenuLeaf2 cbMenu_%3$s"><a href="javascript: void( 0 );">%s</a></li>';

		$this->htmlText[]		=	'<div class="cbMenuLeaf3">%s</div>';

		$this->htmlText[]		=	'<div class="cbMenuLeaf4">%s</div>';

		$this->htmlSeparator	=	array();

		$this->htmlSeparator[]	=	'%s<span class="cbMenuSeparator1"><hr /></span>';

		$this->htmlSeparator[]	=	'<li class="divider"></li>';

		$this->htmlSeparator[]	=	'%s<span class="cbMenuSeparator3"><hr /></span>';

		$this->htmlSeparator[]	=	'%s<span class="cbMenuSeparator4"><hr /></span>';

		$this->set( 'oVarDisplayClassName', 'cbMenuBest' );
	}
}

class cbMenuTabList extends cbMenu
{
	/**
	 * @param  int          $level
	 * @param  int          $idCounter
	 * @param  string       $key
	 * @return null|string
	 */
	public function displayMenuItem( $level, $idCounter, $key = null )
	{
		global $cbMenuTabListLastTopName;

		$return									=	null;

		switch ( $level ) {
			case 0:
			case 1:
				break;
			default:
				$return							.=	'<tr class="sectiontableentry' . ( $idCounter & 1 ? 1 : 2 ).' cbStatList' . $idCounter . '">'
												.		'<td class="titleCell">';

				if ( ( ! isset( $cbMenuTabListLastTopName ) ) || ( $this->topName != $cbMenuTabListLastTopName ) ) {
					$cbMenuTabListLastTopName	=	$this->topName;

					$return						.=			$this->topName;
				} else {
					$return						.=			'&nbsp;';
				}

				$return							.=		'</td>'
												.		'<td class="fieldCell">';

				if ( substr( ltrim( $this->link ), 0, 1 ) == '<' ) {
					$return						.=	$this->link;
				} else {
					if ( isset( $this->link ) && $this->link ) {
						$return					.=	'<a href="' . $this->link . '"';

						if ( isset( $this->target ) && $this->target ) {
							$return				.=	' target="' . $this->target . '"';
						}
					} else {
						$return					.=	'<span';
					}

					$return						.=	' class="cbMenuItem' . ( $this->menuid ? ' cbMenu' . $this->menuid : null ) . ( ( isset( $this->class ) && $this->class ) ? ' ' . $this->class : null ) . '"';

					if ( isset( $this->tooltip ) && $this->tooltip ) {
						$return					.=	' title="' . $this->tooltip . '"';
					}

					$return						.=	'>';

					if ( isset( $this->imgHTML ) && $this->imgHTML ) {
						$return					.=	$this->imgHTML;
					}

					$return						.=	$this->name;

					if ( isset( $this->link ) && $this->link ) {
						$return					.=	'</a>';
					} else {
						$return					.=	'</span>';
					}
				}

				$return							.=		'</td>'
												.	'</tr>';
				break;
		}

		return $return;
	}
}

class cbListMenuHandler extends cbMenuHandler
{
	public function __construct( )
	{
		$this->htmlBegin		=	null;
		$this->htmlEnd			=	null;
		$this->htmlDown			=	array( null, null, null );
		$this->htmlUp			=	array( null, null, null );
		$this->htmlLeaf			=	array( null, null, null, null );
		$this->htmlText			=	array( null, null, null, null );

		$this->htmlSeparator	=	array();

		$this->htmlSeparator[]	=	null;

		$this->htmlSeparator[]	=	'<tr class="sectiontableentry1">'
								.		'<td colspan="2"><hr /></td>'
								.	'</tr>';

		$this->htmlSeparator[]	=	'<tr>'
								.		'<td colspan="2"><hr /></td>'
								.	'</tr>';

		$this->htmlSeparator[]	=	null;

		$this->set( 'oVarDisplayClassName', 'cbMenuTabList' );
	}
}

class cbMenuDivsList extends cbMenu
{
	/**
	 * @param int $level
	 * @param int $idCounter
	 * @param string $key
	 * @return null|string
	 */
	public function displayMenuItem( $level, $idCounter, $key = null )
	{
		$return									=	null;

		switch ( $level ) {
			case 0:
			case 1:
				break;
			default:
				$return							.=	'<tr class="sectiontableentry' . ( $idCounter & 1 ? 1 : 2 ).' cbStatList' . $idCounter . '">'
												.		'<td class="fieldCell" colspan="2">';

				if ( substr( ltrim( $this->link ), 0, 1 ) == '<' ) {
					$return						.=			$this->link;
				} else {
					if ( isset( $this->link ) && $this->link ) {
						$return					.=	'<a href="' . $this->link . '"';

						if ( isset( $this->target ) && $this->target ) {
							$return				.=	' target="' . $this->target . '"';
						}
					} else {
						$return					.=	'<span';
					}

					$return						.=	' class="cbMenuItem' . ( $this->menuid ? ' cbMenu' . $this->menuid : null ) . ( ( isset( $this->class ) && $this->class ) ? ' ' . $this->class : null ) . '"';

					if ( isset( $this->tooltip ) && $this->tooltip ) {
						$return					.=	' title="' . $this->tooltip . '"';
					}

					$return						.=	'>';

					if ( isset( $this->imgHTML ) && $this->imgHTML ) {
						$return					.=	$this->imgHTML;
					}

					$return						.=	$this->name;

					if ( isset( $this->link ) && $this->link ) {
						$return					.=	'</a>';
					} else {
						$return					.=	'</span>';
					}
				}

				$return							.=		'</td>'
												.	'</tr>';
				break;
		}

		return $return;
	}
}

class cbMenuHandlerUL extends cbMenuHandler
{
	public function __construct( )
	{
		$this->htmlBegin		=	'<ul class="%s list-group">';

		$this->htmlEnd			=	'</ul>';

		$this->htmlDown			=	array();

		$this->htmlDown[]		=	null;

		$this->htmlDown[]		=	'<li class="cbMenuLevel2 list-group-item" id="cbMenuId%2$s">'
								.		'<ul class="MenuLevel2txt list-group">';

		$this->htmlDown[]		=	'<li class="cbMenuLevel3 list-group-item" id="cbMenuId%2$s">'
								.		'<ul class="MenuLevel3txt list-group">';

		$this->htmlUp			=	array();

		$this->htmlUp[]			=	null;

		$this->htmlUp[]			=		'</ul>'
								.	'</li>';

		$this->htmlUp[]			=		'</ul>'
								.	'</li>';

		$this->htmlLeaf			=	array();

		$this->htmlLeaf[]		=	'<li class="cbMenuSingleText list-group-item">%s</li>';

		$this->htmlLeaf[]		=	'<li class="cbMenuLeaf1 list-group-item">' . '<a href="%s">%s</a>' . '</li>';

		$this->htmlLeaf[]		=	'<li class="cbMenuLeaf2 list-group-item">' . '<a href="%s">%s</a>' . '</li>';

		$this->htmlLeaf[]		=	'<li class="cbMenuLeaf3 list-group-item">' . '<a href="%s">%s</a>' . '</li>';

		$this->htmlLeaf[]		=	'<li class="cbMenuLeaf4 list-group-item">' . '<a href="%s">%s</a>' . '</li>';

		$this->htmlText			=	array();

		$this->htmlText[]		=	null;

		$this->htmlText[]		=	'<li class="cbMenuLeaf1 list-group-item">%s</li>';

		$this->htmlText[]		=	'<li class="cbMenuLeaf2 list-group-item">%s</li>';

		$this->htmlText[]		=	'<li class="cbMenuLeaf3 list-group-item">%s</li>';

		$this->htmlText[]		=	'<li class="cbMenuLeaf4 list-group-item">%s</li>';

		$this->htmlSeparator	=	array();

		$this->htmlSeparator[]	=	null;

		$this->htmlSeparator[]	=	'%s<li class="cbMenuSeparator1 list-group-item"><hr /></li>';

		$this->htmlSeparator[]	=	'%s<li class="cbMenuSeparator2 list-group-item"><hr /></li>';

		$this->htmlSeparator[]	=	'%s<li class="cbMenuSeparator3 list-group-item"><hr /></li>';

		$this->htmlSeparator[]	=	'%s<li class="cbMenuSeparator4 list-group-item"><hr /></li>';

		$this->set( 'oVarDisplayClassName', 'cbMenuULlist' );
	}
}

class cbMenuULlist extends cbMenu
{
	/**
	 * @param  int          $level
	 * @param  int          $idCounter
	 * @param  string       $key
	 * @return null|string
	 */
	public function displayMenuItem( $level, $idCounter, $key = null )
	{
		$return						=	null;

		$attributes					=	' class="list-group-item cbMenuItem cbMenuEogr' . ( $idCounter & 1 ? 2 : 1 ) . ( $this->menuid ? ' cbMenu' . $this->menuid : null ) . ( ( isset( $this->class ) && $this->class ) ? ' ' . $this->class : null ) . '"' . ( ( isset( $this->tooltip ) && $this->tooltip ) ? ' title="' . $this->tooltip . '"' : null );

		switch ( $level ) {
			case 0:
				$return				.=	'<li' . $attributes . '>' . $this->name . '</li>';
				break;
			default:
				$return				.=	'<li' . $attributes . '>';

				if ( substr( ltrim( $this->link ), 0, 1 ) == '<' ) {
					$return			.=		$this->link;
				} else {
					$return			.=	'<a href="' . $this->link . '"';

					if ( isset( $this->target ) && $this->target ) {
						$return		.=	' target="' . $this->target . '"';
					}

					$return 		.= '>';

					if ( isset( $this->imgHTML ) && $this->imgHTML ) {
						$return		.=	$this->imgHTML;
					}

					$return 		.=	$this->name . '</a>';
				}

				$return 			.=	'</li>';
				break;
		}

		return $return;
	}
}

class cbMenuBar extends cbBarMenuHandler
{
	public function outputScripts( )
	{
		initToolTip( 1 );
	}
}

class cbMenuList extends cbListMenuHandler
{
	public function outputScripts( )
	{
	}
}

class cbMenuDivs extends cbListMenuHandler
{
	public function outputScripts( )
	{
	}
}

class cbMenuUL extends cbMenuHandlerUL
{
	public function outputScripts( )
	{
	}
}

class getMenuTab extends cbTabHandler
{
	/**
	 * @var cbBarMenuHandler
	 */
	protected $menuBar;
	protected $ui;
	protected $cbMyIsModerator;
	protected $cbUserIsModerator;

	public function __construct( )
	{
		parent::__construct();
	}

	/**
	 * @param  \CB\Database\Table\UserTable  $user
	 */
	public function prepareMenu( $user )
	{
		global $_CB_framework;

		$this->ui					=	$_CB_framework->getUi();
		$this->cbUserIsModerator	=	Application::User( (int) $user->id )->isGlobalModerator();
		$this->cbMyIsModerator		=	Application::MyUser()->isModeratorFor( Application::User( (int) $user->id ) );

		$params						=	$this->params;

		switch ( $params->get( 'menuFormat', 'menuBar' ) ) {
			case 'menuList':
			case 'no':
				$this->menuBar		=	new cbMenuList( 1 );
				break;
			case 'menuUL':
				$this->menuBar		=	new cbMenuUL( 1 );
				break;
			case 'menuDivs':
				$this->menuBar		=	new cbMenuDivs( 1 );
				break;
			case 'menuBar':
			default:
				$this->menuBar		=	new cbMenuBar( 1 );
				break;
		}

		$this->menuBar->outputScripts( 1 );
	}

	/**
	 * @param  \CB\Database\Table\TabTable   $tab       the tab database entry
	 * @param  \CB\Database\Table\UserTable  $user      the user being displayed
	 * @param  int                           $ui        1 for front-end, 2 for back-end
	 * @return boolean
	 */
	public function getMenuAndStatus( $tab, $user, $ui )
	{
		global $_CB_framework, $_CB_database, $ueConfig, $_REQUEST, $_POST;

		$params				=	$this->params;

		$userId				=	( $user->id && ( $_CB_framework->myId() == $user->id ) ? null : $user->id );

		$firstMenuName		= $params->get( 'firstMenuName', '' ); // CBTxt::T( '_UE_MENU_CB', 'Community' )
		$firstSubMenuName	= $params->get( 'firstSubMenuName', '' ); // CBTxt::T( '_UE_MENU_ABOUT_CB', 'About Community Builder...' )
		$firstSubMenuHref	= $params->get( 'firstSubMenuHref', '' );
		$secondSubMenuName	= $params->get( 'secondSubMenuName', '' );
		$secondSubMenuHref	= $params->get( 'secondSubMenuHref', '' );

		// ----- CUSTOM MENU -----
		if ( $firstMenuName != '' ) {
			$customMenu					=	array();
			$customMenu['arrayPos']		=	$firstMenuName;
			$customMenu['position']		=	'menuBar';
			$customMenu['caption']		=	CBTxt::T( $firstMenuName );

			$this->addMenu( $customMenu );

			if ( $firstSubMenuName != '' ) {
				// Custom 1:
				$first					=	array();
				$first['arrayPos']		=	array( $firstMenuName => array( '_UE_FIRST' => null ) );
				$first['position']		=	'menuBar';
				$first['caption']		=	CBTxt::T( $firstSubMenuName );
				$first['url']			=	( $firstSubMenuHref == '' ? "javascript: void( 0 );" : cbSef( $firstSubMenuHref ) );
				$first['target']		=	'';
				$first['img']			=	'';
				$first['tooltip']		=	'';

				$this->addMenu( $first );

				if ( $secondSubMenuName != '' ) {
					// Custom 2:
					$second				=	array();
					$second['arrayPos']	=	array( $firstMenuName => array( '_UE_SECOND' => null ) );
					$second['position']	=	'menuBar';
					$second['caption']	=	CBTxt::T( $secondSubMenuName );
					$second['url']		=	( $secondSubMenuHref == '' ? "javascript: void( 0 );" : cbSef( $secondSubMenuHref ) );
					$second['target']	=	'';
					$second['img']		=	'';
					$second['tooltip']	=	'';

					$this->addMenu( $second );
				}
			}
		}

		// ----- VIEW MENU -----
		$viewMenu						=	array();
		$viewMenu['arrayPos']			=	'_UE_MENU_VIEW';
		$viewMenu['position']			=	'menuBar';
		$viewMenu['caption']			=	CBTxt::T( '_UE_MENU_VIEW', 'View' );
		$this->addMenu( $viewMenu );

		if ( $_CB_framework->myId() > 0 ) {
			if ( ( $_CB_framework->displayedUser() === null ) || ( ( $_CB_framework->myId() != $user->id ) && ( $_CB_framework->displayedUser() !== null ) ) ) {
				// View My Profile:
				$myProfile				=	array();
				$myProfile['arrayPos']	=	array( '_UE_MENU_VIEW' => array( '_UE_MENU_VIEWMYPROFILE' => null ) );
				$myProfile['position']	=	'menuBar';
				$myProfile['caption']	=	CBTxt::T( 'UE_MENU_VIEWMYPROFILE', 'View Your Profile' );
				$myProfile['url']		=	$_CB_framework->userProfileUrl();
				$myProfile['target']	=	'';
				$myProfile['img']		=	'<span class="fa fa-home"></span> ';
				$myProfile['tooltip']	=	CBTxt::T( 'UE_MENU_VIEWMYPROFILE_DESC', 'View your own profile' );

				$this->addMenu( $myProfile );
			}
		}

		// ----- EDIT MENU -----
		$editMenu						=	array();
		$editMenu['arrayPos']			=	'_UE_MENU_EDIT';
		$editMenu['position']			=	'menuBar';
		$editMenu['caption']			=	CBTxt::T( '_UE_MENU_EDIT', 'Edit' );
		$this->addMenu( $editMenu );

		if ( ! cbCheckIfUserCanPerformUserTask( $user->id, 'allowModeratorsUserEdit') ) {
			if ( $user->id == $_CB_framework->myId() ) {
				$menuTexts	=	array(	'_UE_UPDATEPROFILE'				=>	CBTxt::T( 'UE_UPDATEPROFILE', 'Update Your Profile' ),
										'_UE_MENU_UPDATEPROFILE_DESC'	=>	CBTxt::T( 'UE_MENU_UPDATEPROFILE_DESC', 'Change your profile settings' )
									);
			} else {
				$menuTexts	=	array(	'_UE_UPDATEPROFILE'				=>	CBTxt::T( 'UE_MOD_MENU_UPDATEPROFILE', 'Update user profile' ),
										'_UE_MENU_UPDATEPROFILE_DESC'	=>	CBTxt::T( 'UE_MOD_MENU_UPDATEPROFILE_DESC', 'Change profile settings of this user profile' )
									);
			}

			// Update Profile:
			$updateProfile				=	array();
			$updateProfile['arrayPos']	=	array( '_UE_MENU_EDIT' => array( '_UE_UPDATEPROFILE' => null ) );
			$updateProfile['position']	=	'menuBar';
			$updateProfile['caption']	=	$menuTexts['_UE_UPDATEPROFILE'];
			$updateProfile['url']		=	$_CB_framework->userProfileEditUrl( $userId );
			$updateProfile['target']	=	'';
			$updateProfile['img']		=	'<span class="fa fa-edit"></span> ';
			$updateProfile['tooltip']	=	$menuTexts['_UE_MENU_UPDATEPROFILE_DESC'];

			$this->addMenu( $updateProfile );
		}

		// ----- MESSAGES MENU -----
		$messagesMenu					=	array();
		$messagesMenu['arrayPos']		=	'_UE_MENU_MESSAGES';
		$messagesMenu['position']		=	'menuBar';
		$messagesMenu['caption']		=	CBTxt::T( '_UE_MENU_MESSAGES', 'Messages' );
		$this->addMenu( $messagesMenu );

		if ( ( $_CB_framework->myId() != $user->id ) && ( $_CB_framework->myId() > 0 ) ) {
			global $_CB_PMS;

			$resultArray						=	$_CB_PMS->getPMSlinks( $user->id, $_CB_framework->myId(), '', '', 1 );

			if ( count( $resultArray ) > 0 ) foreach ( $resultArray as $res ) {
				if ( is_array( $res ) ) {
					// Send Private Message:
					$sendMessage				=	array();
					$sendMessage['arrayPos']	=	array( '_UE_MENU_MESSAGES' => array( '_UE_PM_USER' => null ) );
					$sendMessage['position']	=	'menuBar';
					$sendMessage['caption']		=	$res['caption']; // Already translated in CB Menu
					$sendMessage['url']			=	cbSef( $res['url'] );
					$sendMessage['target']		=	'';
					$sendMessage['img']			=	'<span class="fa fa-comment"></span> ';
					$sendMessage['tooltip']		=	$res['tooltip']; // Already translated in CB Menu

					$this->addMenu( $sendMessage );
				}
			}
		}

		if ( ( $ueConfig['allow_email_display'] != 4 ) && ( $_CB_framework->myId() != $user->id ) && ( $_CB_framework->myId() > 0 ) ) {
			switch ( $ueConfig['allow_email_display'] ) {
				case 1: // Display Email only
					$caption			=	moscomprofilerHTML::emailCloaking( htmlspecialchars( $user->email ), 0 );
					$url				=	"javascript: void( 0 );;";
					$desc				=	CBTxt::T( 'UE_MENU_USEREMAIL_DESC', 'Email address of this user' );
					break;
				case 2: // Display Email with link
					$caption			=	moscomprofilerHTML::emailCloaking( htmlspecialchars( $user->email ), 1 );
					$url				=	"javascript: void( 0 );;";
					$desc				=	CBTxt::T( 'UE_MENU_SENDUSEREMAIL_DESC', 'Send an Email to this user' );
					break;
				case 3: // Display Email-to text with link to web-form
				default:
					$caption			=	CBTxt::T( 'UE_MENU_SENDUSEREMAIL', 'Send Email to User' );
					$url				=	$_CB_framework->viewUrl( 'emailuser', true, array( 'uid' => $userId ) );
					$desc				=	CBTxt::T( 'UE_MENU_SENDUSEREMAIL_DESC', 'Send an Email to this user' );
					break;
			}

			// Send Email:
			$sendEmail					=	array();
			$sendEmail['arrayPos']		=	array( '_UE_MENU_MESSAGES' => array( '_UE_MENU_SENDUSEREMAIL' => null ) );
			$sendEmail['position']		=	'menuBar';
			$sendEmail['caption']		=	$caption;
			$sendEmail['url']			=	$url;
			$sendEmail['target']		=	'';
			$sendEmail['img']			=	'<span class="fa fa-envelope"></span> ';
			$sendEmail['tooltip']		=	$desc;

			$this->addMenu( $sendEmail );
		}

		// ----- CONNECTIONS MENU -----
		$connectionsMenu							=	array();
		$connectionsMenu['arrayPos']				=	'_UE_MENU_CONNECTIONS';
		$connectionsMenu['position']				=	'menuBar';
		$connectionsMenu['caption']					=	CBTxt::T( '_UE_MENU_CONNECTIONS', 'Connections' );
		$this->addMenu( $connectionsMenu );

		if ( $ueConfig['allowConnections'] && ( $_CB_framework->myId() > 0 ) ) {
			// Manage My Connections:
			$manageConnections						=	array();
			$manageConnections['arrayPos']			=	array( '_UE_MENU_CONNECTIONS' => array( '_UE_MENU_MANAGEMYCONNECTIONS' => null ) );
			$manageConnections['position']			=	'menuBar';
			$manageConnections['caption']			=	CBTxt::T( 'UE_MENU_MANAGEMYCONNECTIONS', 'Manage Your Connections' );
			$manageConnections['url']				=	$_CB_framework->viewUrl( 'manageconnections' );
			$manageConnections['target']			=	'';
			$manageConnections['img']				=	'<span class="fa fa-users"></span> ';
			$manageConnections['tooltip']			=	CBTxt::T( 'UE_MENU_MANAGEMYCONNECTIONS_DESC', 'Manage your existing connections and pending connections actions' );

			$this->addMenu( $manageConnections );

			if ( $_CB_framework->myId() != $user->id ) {
				$cbConnection						=	new cbConnection( (int) $_CB_framework->myId() );
				$cbUser								=&	CBuser::getInstance( (int) $user->id, false );

				$connClass							=	null;
				$connLink							=	null;
				$connDesc							=	null;
				$connMsg							=	null;
				$connImg							=	null;

				$isConnection						=	$cbConnection->isConnected( (int) $user->id );

				if ( $isConnection ) {
					$isApproved						=	$cbConnection->isConnectionApproved( (int) $user->id );
					$isAccepted						=	$cbConnection->isConnectionAccepted( (int) $user->id );
				} else {
					$isApproved						=	false;
					$isAccepted						=	false;
				}

				if ( ! $isConnection ) {
					$connUrl						=	$_CB_framework->viewUrl( 'addconnection', true, array( 'connectionid' => (int) $user->id ) );

					if ( $ueConfig['useMutualConnections'] == 1 ) {
						$connClass					=	'UE_ADDCONNECTIONREQUEST';
						$connMsg					=	CBTxt::T( 'UE_ADDCONNECTIONREQUEST', 'Request Connection' );
						$connDesc					=	CBTxt::T( 'UE_ADDCONNECTIONREQUEST_DESC', 'Request a Connection to that user' );
					} else {
						$connClass					=	'UE_ADDCONNECTION';
						$connMsg					=	CBTxt::T( 'UE_ADDCONNECTION', 'Add Connection' );
						$connDesc					=	CBTxt::T( 'UE_ADDCONNECTION_DESC', 'Add a Connection to that user' );
					}

					if ( $ueConfig['conNotifyType'] != 0 ) {
						cbValidator::loadValidation();

						$tooltipTitle				=	sprintf( CBTxt::T( 'UE_CONNECTTO', 'Connect to %s' ), $cbUser->getField( 'formatname', null, 'html', 'none', 'profile', 0, true ) );

						$connectionInvitationMsg	=	CBTxt::T( 'UE_CONNECTIONINVITATIONMSG', 'Personalize your invitation to connect by adding a message that will be included with your connection.' );

						$tooltip					=	null;

						if ( $connectionInvitationMsg ) {
							$tooltip				.=	'<div class="form-group cb_form_line clearfix">'
													.		$connectionInvitationMsg
													.	'</div>';
						}

						$tooltip					.=	'<form action="' . $connUrl . '" method="post" id="connOverForm" name="connOverForm" class="cb_form cbValidation">'
													.		'<div class="form-group cb_form_line clearfix">'
													.			'<label for="message" class="control-label">' . CBTxt::T( 'UE_MESSAGE', 'Message' ) . '</label>'
													.			'<div class="cb_field">'
													.				'<textarea cols="40" rows="8" name="message" class="form-control"></textarea>'
													.			'</div>'
													.		'</div>'
													.		'<div class="form-group cb_form_line clearfix">'
													.			'<input type="submit" class="btn btn-primary cbConnReqSubmit" value="' . htmlspecialchars( CBTxt::Th( 'UE_SENDCONNECTIONREQUEST', 'Request Connection' ) ) . '"' . cbValidator::getSubmitBtnHtmlAttributes() . ' />'
													.			' <input type="button" id="cbConnReqCancel" class="btn btn-default cbConnReqCancel cbTooltipClose" value="' . htmlspecialchars( CBTxt::Th( 'UE_CANCELCONNECTIONREQUEST', 'Cancel' ) ) . '" />'
													.		'</div>'
													.	'</form>';

						$connLink					=	cbTooltip( $ui, $tooltip, $tooltipTitle, 400, null, '<span class="fa fa-heart"></span> ' . CBTxt::Th( $connMsg ), 'javascript: void( 0 );', 'data-hascbtooltip="true" data-cbtooltip-modal="true"' );
					} else {
						$connLink					=	$connUrl;
						$connImg					=	'<span class="fa fa-heart"></span> ';
					}
				} else {
					if ( $isAccepted ) {
						$connUrl					=	$_CB_framework->viewUrl( 'removeconnection', true, array( 'connectionid' => (int) $user->id ) );

						if ( $isApproved ) {
							$connClass				=	'UE_REMOVECONNECTION';
							$connMsg				=	CBTxt::T( 'UE_REMOVECONNECTION', 'Remove Connection' );
							$connDesc				=	CBTxt::T( 'UE_REMOVECONNECTION_DESC', 'Remove Connection to that user' );
						} else {
							$connClass				=	'UE_REVOKECONNECTIONREQUEST';
							$connMsg				=	CBTxt::T( 'UE_REVOKECONNECTIONREQUEST', 'Revoke Connection Request' );
							$connDesc				=	CBTxt::T( 'UE_REVOKECONNECTIONREQUEST_DESC', 'Cancel the Connection Request to that user' );
						}

						$js							=	"if ( typeof confirmSubmit != 'function' ) {"
													.		"function confirmSubmit() {"
													.			"if ( confirm( '" . addslashes( CBTxt::T( 'UE_CONFIRMREMOVECONNECTION', 'Are you sure you want to remove this connection?' ) ) . "' ) ) {"
													.				"return true;"
													.			"} else {"
													.				"return false;"
													.			"}"
													.		"};"
													.	"}";

						$_CB_framework->document->addHeadScriptDeclaration( $js );

						$connLink					=	$connUrl . '" onclick="return confirmSubmit();';
						$connImg					=	'<span class="fa fa-heart-o"></span> ';
					} else {
						$connClass					=	null;
						$connMsg					=	null;
					}
				}

				if ( $connMsg ) {
					// Request/Add/Remove/Revoke Connection:
					$connectionRequest				=	array();
					$connectionRequest['arrayPos']	=	array( '_UE_MENU_CONNECTIONS' => array( ( $connClass ) => null ) );
					$connectionRequest['position']	=	'menuBar';
					$connectionRequest['caption']	=	$connMsg;
					$connectionRequest['url']		=	$connLink;
					$connectionRequest['target']	=	'';
					$connectionRequest['img']		=	$connImg;
					$connectionRequest['tooltip']	=	$connDesc;

					$this->addMenu( $connectionRequest );
				}
			}

		}

		// ----- MODERATE MENU -----
		$moderateMenu								=	array();
		$moderateMenu['arrayPos']					=	'_UE_MENU_MODERATE';
		$moderateMenu['position']					=	'menuBar';
		$moderateMenu['caption']					=	CBTxt::T( '_UE_MENU_MODERATE', 'Moderate' );
		$this->addMenu( $moderateMenu );

		if ( $_CB_framework->myId() == $user->id ) {
			if ( ( $user->banned == 1 ) && ( $this->cbUserIsModerator == 0 ) && ( $ueConfig['allowUserBanning'] == 1 ) ) {
				// Request Unban:
				$requestUnban						=	array();
				$requestUnban['arrayPos']			=	array( '_UE_MENU_MODERATE' => array( '_UE_REQUESTUNBANPROFILE' => null ) );
				$requestUnban['position']			=	'menuBar';
				$requestUnban['caption']			=	CBTxt::T( 'UE_REQUESTUNBANPROFILE', 'Submit Unban Request' );
				$requestUnban['url']				=	$_CB_framework->viewUrl( 'banprofile', true, array( 'act' => 2, 'reportform' => 1, 'uid' => (int) $user->id ) );
				$requestUnban['target']				=	'';
				$requestUnban['img']				=	'<span class="fa fa-envelope"></span> ';
				$requestUnban['tooltip']			=	CBTxt::T( 'UE_MENU_REQUESTUNBANPROFILE_DESC', 'Submit a request to the site moderator to unban your profile' );

				$this->addMenu( $requestUnban );
			}
		} else {
			if ( ( $ueConfig['allowUserReports'] == 1 ) && ( $this->cbUserIsModerator == 0 ) && ( $_CB_framework->myId() > 0 ) ) {
				// Report User:
				$reportUser							=	array();
				$reportUser['arrayPos']				=	array( '_UE_MENU_MODERATE' => array( '_UE_REPORTUSER' => null ) );
				$reportUser['position']				=	'menuBar';
				$reportUser['caption']				=	CBTxt::T( 'UE_REPORTUSER', 'Report User' );
				$reportUser['url']					=	$_CB_framework->viewUrl( 'reportuser', true, array( 'uid' => (int) $user->id ) );
				$reportUser['target']				=	'';
				$reportUser['img']					=	'<span class="fa fa-bullhorn"></span> ';
				$reportUser['tooltip']				=	CBTxt::T( 'UE_MENU_REPORTUSER_DESC', 'Report this user to the site moderator so that he can take appropriate action' );

				$this->addMenu( $reportUser );
			}

			if ( ( $this->cbMyIsModerator == 1 ) && ( $this->cbUserIsModerator == 0 ) ) {
				$query								=	'SELECT COUNT(*)'
													.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_userreports' )
													.	"\n WHERE " . $_CB_database->NameQuote( 'reporteduser' ) . " = " . (int) $user->id
													.	"\n AND " . $_CB_database->NameQuote( 'reportedstatus' ) . " = 0";
				$_CB_database->setQuery( $query );
				$pendingReports						=	$_CB_database->loadResult();

				$query								=	'SELECT COUNT(*)'
													.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_userreports' )
													.	"\n WHERE " . $_CB_database->NameQuote( 'reporteduser' ) . " = " . (int) $user->id;
				$_CB_database->setQuery( $query );
				$processedReports					=	$_CB_database->loadResult();

				if ( $ueConfig['allowUserBanning'] == 1 ) {
					if ( $user->banned != 0 ) {
						// Unban Profile:
						$unbanUser					=	array();
						$unbanUser['arrayPos']		=	array( '_UE_MENU_MODERATE' => array( '_UE_UNBANPROFILE' => null ) );
						$unbanUser['position']		=	'menuBar';
						$unbanUser['caption']		=	CBTxt::T( 'UE_UNBANPROFILE', 'Unban Profile' );
						$unbanUser['url']			=	$_CB_framework->viewUrl( 'banprofile', true, array( 'act' => 0, 'reportform' => 0, 'uid' => (int) $user->id ) );
						$unbanUser['target']		=	'';
						$unbanUser['img']			=	'<span class="fa fa-check-circle-o"></span> ';
						$unbanUser['tooltip']		=	CBTxt::T( 'UE_MENU_UNBANPROFILE_DESC', 'As Site Moderator: Unban this profile, making it visible to other users' );

						$this->addMenu( $unbanUser );
					} else {
						// Ban Profile:
						$banUser					=	array();
						$banUser['arrayPos']		=	array( '_UE_MENU_MODERATE' => array( '_UE_BANPROFILE' => null ) );
						$banUser['position']		=	'menuBar';
						$banUser['caption']			=	CBTxt::T( 'UE_BANPROFILE', 'Ban Profile' );
						$banUser['url']				=	$_CB_framework->viewUrl( 'banprofile', true, array( 'act' => 1, 'uid' => (int) $user->id ) );
						$banUser['target']			=	'';
						$banUser['img']				=	'<span class="fa fa-ban"></span> ';
						$banUser['tooltip']			=	CBTxt::T( 'UE_MENU_BANPROFILE_DESC', 'As Site Moderator: Ban this profile, making it invisible to other users' );

						$this->addMenu( $banUser );
					}

					if ( $user->bannedby ) {
						// Ban History:
						$banHistory					=	array();
						$banHistory['arrayPos']		=	array( '_UE_MENU_MODERATE' => array( '_UE_MENU_BANPROFILE_HISTORY' => null ) );
						$banHistory['position']		=	'menuBar';
						$banHistory['caption']		=	CBTxt::T( 'UE_MENU_BANPROFILE_HISTORY', 'Ban history' );
						$banHistory['url']			=	$_CB_framework->viewUrl( 'moderatebans', true, array( 'act' => 2, 'uid' => (int) $user->id ) );
						$banHistory['target']		=	'';
						$banHistory['img']			=	'<span class="fa fa-book"></span> ';
						$banHistory['tooltip']		=	CBTxt::T( 'UE_MENU_BANPROFILE_HISTORY_DESC', 'As Site Moderator: See ban history of this profile' );

						$this->addMenu( $banHistory );
					}
				}

				if ( ( $ueConfig['allowUserReports'] == 1 ) && ( $pendingReports > 0 ) ) {
					// View Pending Reports:
					$userReports					=	array();
					$userReports['arrayPos']		=	array( '_UE_MENU_MODERATE' => array( '_UE_VIEWUSERREPORTS' => null ) );
					$userReports['position']		=	'menuBar';
					$userReports['caption']			=	CBTxt::T( 'UE_VIEWUSERREPORTS', 'View User Reports' );
					$userReports['url']				=	$_CB_framework->viewUrl( 'viewreports', true, array( 'uid' => (int) $user->id ) );
					$userReports['target']			=	'';
					$userReports['img']				=	'<span class="fa fa-warning"></span> ';
					$userReports['tooltip']			=	CBTxt::T( 'UE_MENU_VIEWUSERREPORTS_DESC', 'As Site Moderator: View User Reports for this user' );

					$this->addMenu( $userReports );
				} elseif ( ( $ueConfig['allowUserReports'] == 1 ) && ( $processedReports > 0 ) ) {
					// View Processed Reports:
					$userReports					=	array();
					$userReports['arrayPos']		=	array( '_UE_MENU_MODERATE' => array( '_UE_VIEWUSERREPORTS' => null ) );
					$userReports['position']		=	'menuBar';
					$userReports['caption']			=	CBTxt::T( 'UE_MOD_MENU_VIEWOLDUSERREPORTS', 'View processed user reports' );
					$userReports['url']				=	$_CB_framework->viewUrl( 'viewreports', true, array( 'act' => 1, 'uid' => (int) $user->id ) );
					$userReports['target']			=	'';
					$userReports['img']				=	'<span class="fa fa-warning"></span> ';
					$userReports['tooltip']			=	CBTxt::T( 'UE_MOD_MENU_VIEWOLDUSERREPORTS_DESC', 'As site moderator: View processed user reports for this user' );

					$this->addMenu( $userReports );
				}
			}
		}
	}

	/**
	 * Generates the HTML to display the user profile tab
	 *
	 * @param  \CB\Database\Table\TabTable   $tab       the tab database entry
	 * @param  \CB\Database\Table\UserTable  $user      the user being displayed
	 * @param  int                           $ui        1 for front-end, 2 for back-end
	 * @return string|boolean                           Either string HTML for tab content, or false if ErrorMSG generated
	 */
	public function getDisplayTab( $tab, $user, $ui )
	{
		global $_CB_framework, $_PLUGINS, $_CB_OneTwoRowsStyleToggle;

		$params									=	$this->params;

		if ( ! $this->menuBar ) {
			$this->prepareMenu( $user );
		}

		$pm										=	$_PLUGINS->getMenus();

		for ( $i = 0, $pmc = count( $pm ); $i < $pmc; $i++ ) {
			if ( $pm[$i]['position'] == 'menuBar' ) {

				if ( is_string( $pm[$i]['arrayPos'] ) ) {
					// Top-level menu translation definition:
					$this->menuBar->addTranslation( $pm[$i]['arrayPos'], $pm[$i]['caption'] );
					continue;
				}

				$pmUrl							=	( isset( $pm[$i]['url'] ) ? $pm[$i]['url'] : null );
				$pmTarget						=	( isset( $pm[$i]['target'] ) ? $pm[$i]['target'] : null );
				$pmImg							=	( isset( $pm[$i]['img'] ) ? $pm[$i]['img'] : null );
				$pmAlt							=	( isset( $pm[$i]['alt'] ) ? $pm[$i]['alt'] : null );
				$pmTooltip						=	( isset( $pm[$i]['tooltip'] ) ? $pm[$i]['tooltip'] : null );
				$pmKeystroke					=	( isset( $pm[$i]['keystroke'] ) ? $pm[$i]['keystroke'] : null );

				$this->menuBar->addObjectItem( $pm[$i]['arrayPos'], $pm[$i]['caption'], $pmUrl, $pmTarget, $pmImg, $pmAlt, $pmTooltip, $pmKeystroke );
			}
		}

		static $JS_LOADED						=	0;

		if ( ! $JS_LOADED++ ) {
			if ( $this->menuBar->js ) {
				$_CB_framework->document->addHeadScriptDeclaration( $this->menuBar->js );
			}

			if ( $this->menuBar->jQuery ) {
				$_CB_framework->outputCbJQuery( $this->menuBar->jQuery, 'cbscroller' );
			}
		}

		switch ( $params->get( 'menuFormat', 'menuBar' ) ) {
			case 'no':
				$return							=	$this->_writeTabDescription( $tab, $user, 'cbUserMenuDescription' );
				break;
			case 'menuUL':
				$return							=	$this->_writeTabDescription( $tab, $user, 'cbUserMenuDescription' );

				$idCounter						=	$_CB_OneTwoRowsStyleToggle;
				$tableContent					=	$this->menuBar->displayMenu( $idCounter );

				if ( $tableContent != '' ) {
					$_CB_OneTwoRowsStyleToggle	=	( $idCounter & 1 ? 2 : 1 );

					$return						.=	'<div class="cbMenuList">'
												.		$tableContent
												.	'</div>';
				}
				break;
			case 'menuList':
			case 'menuDivs':
				$return							=	$this->_writeTabDescription( $tab, $user, 'cbUserMenuDescription' );

				$idCounter						=	$_CB_OneTwoRowsStyleToggle;
				$tableContent					=	$this->menuBar->displayMenu( $idCounter );

				if ( $tableContent != '' ) {
					$_CB_OneTwoRowsStyleToggle	=	( $idCounter & 1 ? 2 : 1);

					$return						.=	'<table class="cbStatusList table table-hover">'
												.		$tableContent
												.	'</table>';
				}
				break;
			case 'menuBar':
			default:
				$idCounter						=	1;

				$return							=	$this->menuBar->displayMenu( $idCounter )
												.	$this->_writeTabDescription( $tab, $user, 'cbUserMenuDescription' );
				break;
		}

		return $return;
	}
}

class getStatusTab extends cbTabHandler
{
	/**
	 * @var cbBarMenuHandler
	 */
	protected $menuList;
	protected $ui;
	protected $cbMyIsModerator;
	protected $cbUserIsModerator;

	public function __construct( )
	{
		parent::__construct();
	}

	/**
	 * @param  \CB\Database\Table\UserTable  $user
	 */
	public function prepareStatus( $user )
	{
		global $_CB_framework;

		$this->ui					=	$_CB_framework->getUi();
		$this->cbUserIsModerator	=	Application::User( (int) $user->id )->isGlobalModerator();
		$this->cbMyIsModerator		=	Application::MyUser()->isModeratorFor( Application::User( (int) $user->id ) );

		$params						=	$this->params;

		switch ( $params->get( 'statusFormat', 'menuList' ) ) {
			case 'menuBar':
				$this->menuList		=	new cbMenuBar( 1 );
				break;
			case 'menuUL':
				$this->menuList		=	new cbMenuUL( 1 );
				break;
			case 'menuDivs':
				$this->menuList		=	new cbMenuDivs( 1 );
				break;
			case 'menuList':
			default:
				$this->menuList		=	new cbMenuList( 1 );
				break;
		}

		$this->menuList->outputScripts( 1 );
	}

	/**
	 * @param  \CB\Database\Table\TabTable   $tab       the tab database entry
	 * @param  \CB\Database\Table\UserTable  $user      the user being displayed
	 * @param  int                           $ui        1 for front-end, 2 for back-end
	 * @return boolean
	 */
	public function getMenuAndStatus( $tab, $user, $ui )
	{
		return true;
	}

	/**
	 * Generates the HTML to display the user profile tab
	 *
	 * @param  \CB\Database\Table\TabTable   $tab       the tab database entry
	 * @param  \CB\Database\Table\UserTable  $user      the user being displayed
	 * @param  int                           $ui        1 for front-end, 2 for back-end
	 * @return string|boolean                           Either string HTML for tab content, or false if ErrorMSG generated
	 */
	public function getDisplayTab( $tab, $user, $ui )
	{
		global $_CB_framework, $_PLUGINS, $_CB_OneTwoRowsStyleToggle;

		$params									=	$this->params;

		if ( ! $this->menuList ) {
			$this->prepareStatus( $user );
		}

		$pm										=	$_PLUGINS->getMenus();

		for ( $i = 0, $pmc = count( $pm ); $i < $pmc; $i++ ) {
			if ( $pm[$i]['position'] == 'menuList' ) {
				if ( is_string( $pm[$i]['arrayPos'] ) ) {
					$this->menuList->addTranslation( $pm[$i]['arrayPos'], $pm[$i]['caption'] );
					continue;
				}

				$pmUrl							=	( isset( $pm[$i]['url'] ) ? $pm[$i]['url'] : null );
				$pmTarget						=	( isset( $pm[$i]['target'] ) ? $pm[$i]['target'] : null );
				$pmImg							=	( isset( $pm[$i]['img'] ) ? $pm[$i]['img'] : null );
				$pmAlt							=	( isset( $pm[$i]['alt'] ) ? $pm[$i]['alt'] : null );
				$pmTooltip						=	( isset( $pm[$i]['tooltip'] ) ? $pm[$i]['tooltip'] : null );
				$pmKeystroke					=	( isset( $pm[$i]['keystroke'] ) ? $pm[$i]['keystroke'] : null );

				$this->menuList->addObjectItem( $pm[$i]['arrayPos'], $pm[$i]['caption'], $pmUrl, $pmTarget, $pmImg, $pmAlt, $pmTooltip, $pmKeystroke );
			}
		}

		static $JS_LOADED						=	0;

		if ( ! $JS_LOADED++ ) {
			if ( $this->menuList->js ) {
				$_CB_framework->document->addHeadScriptDeclaration( $this->menuList->js );
			}

			if ( $this->menuList->jQuery ) {
				$_CB_framework->outputCbJQuery( $this->menuList->jQuery );
			}
		}

		switch ( $params->get( 'statusFormat', 'menuList' ) ) {
			case 'no':
				$return							=	$this->_writeTabDescription( $tab, $user, 'cbUserStatusDescription' );
				break;
			case 'menuBar':
				$idCounter						=	1;

				$return							=	$this->menuList->displayMenu( $idCounter )
												.	$this->_writeTabDescription( $tab, $user, 'cbUserStatusDescription' );
				break;
			case 'menuUL':
				$return							=	$this->_writeTabDescription( $tab, $user, 'cbUserStatusDescription' );

				$idCounter						=	$_CB_OneTwoRowsStyleToggle;
				$tableContent					=	$this->menuList->displayMenu( $idCounter );

				if ( $tableContent != '' ) {
					$_CB_OneTwoRowsStyleToggle	=	( $idCounter & 1 ? 2 : 1 );

					$return						.=	'<div class="cbStatusList">'
												.		$tableContent
												.	'</div>';
				}
				break;
			case 'menuList':
			case 'menuDivs':
			default:
				$return							=	$this->_writeTabDescription( $tab, $user, 'cbUserStatusDescription' );

				$idCounter						=	$_CB_OneTwoRowsStyleToggle;
				$tableContent					=	$this->menuList->displayMenu( $idCounter );

				if ( $tableContent != '' ) {
					$_CB_OneTwoRowsStyleToggle	=	( $idCounter & 1 ? 2 : 1);

					$return						.=	'<table class="cbStatusList table table-hover">'
												.		$tableContent
												.	'</table>';
				}
				break;
		}

		return $return;
	}
}
