<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Application\Application;
use CBLib\Xml\SimpleXMLElement;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

class modCBAdminHelper {

	/**
	 * @param array $menus
	 * @param bool $disabled
	 * @return null|string
	 */
	public static function getTable( $menus, $disabled = false ) {
		$return					=	null;

		if ( $menus ) {
			$return				.=	'<div class="cbMenuTable">';

			foreach ( $menus as $menu ) {
				if ( isset( $menu['menu'] ) ) {
					$items		=	$menu['menu'];
				} else {
					$items		=	array();
				}

				if ( isset( $menu['component'] ) ) {
					$return		.=		'<table class="table table-hover">'
								.			modCBAdminHelper::getTabItems( $menu['component'], $items, $disabled )
								.		'</table>';
				}
			}

			$return				.=	'</div>';
		}

		return $return;
	}

	/**
	 * @param  array  $component  The parent menu node
	 * @param  array  $items      The menu items to output
	 * @param  bool   $disabled   If the menu item should be disabled (grayed)
	 * @param  int    $depth      The depth of the menu items
	 * @return null|string
	 */
	private static function getTabItems( $component, $items, $disabled = false, $depth = 0 ) {
		$parentTitleCleanHtml			=	( isset( $component['title'] ) ? $component['title'] : null );
		$parentAccess					=	( isset( $component['access'] ) ? $component['access'] : null );
		$return							=	null;

		if ( $parentTitleCleanHtml && modCBAdminHelper::checkAccess( $parentAccess ) ) {
			if ( ! $disabled ) {
				if ( ! $depth ) {
					$return				.=	'<thead>'
										.		'<tr>'
										.			'<th>' . $parentTitleCleanHtml . '</th>'
										.		'</tr>'
										.	'</thead>';
				}

				if ( $items ) {
					if ( ! $depth ) {
						$return			.=	'<tbody>';
					}

					foreach ( $items as $item ) {
						$title			=	( isset( $item['title'] ) ? $item['title'] : null );
						$link			=	( isset( $item['link'] ) ? $item['link'] : null );
						$access			=	( isset( $item['access'] ) ? $item['access'] : null );
						$target			=	( isset( $item['target'] ) ? $item['target'] : null );
						$subMenu		=	( isset( $item['submenu'] ) ? $item['submenu'] : array() );

						if ( $title && $link && modCBAdminHelper::checkAccess( $access ) ) {
							$return		.=		'<tr>'
										.			'<td>'
										.				( $depth ? str_repeat( '<span class="fa fa-angle-right text-muted"></span> ', $depth ) : null )
										.				'<a href="' . htmlspecialchars( $link ) . '"' . ( $target ? ' target="' . htmlspecialchars( $target ) . '"' : null ) . '>' . $title . '</a>'
										.			'</td>'
										.		'</tr>';

							if ( $subMenu ) {
								$return	.=		( $subMenu ? modCBAdminHelper::getTabItems( $item, $subMenu, false, ( $depth + 1 ) ) : null );
							}
						}
					}

					if ( ! $depth ) {
						$return			.=	'</tbody>';
					}
				}
			} elseif ( ! $depth ) {
				$return					.=	'<thead>'
										.		'<tr class="active text-muted">'
										.			'<th>' . $parentTitleCleanHtml . '</th>'
										.		'</tr>'
										.	'</thead>';
			}
		}

		return $return;
	}

	/**
	 * @param array $menus
	 * @param bool $disabled
	 * @return null|string
	 */
	public static function getMenu( $menus, $disabled = false ) {
		$return					=	null;

		if ( $menus ) {
			$return				=	'<ul id="menu"' . ( checkJversion( '3.0+' ) ? ' class="nav' . ( $disabled ? ' disabled' : null ) . '"' : null ) . '>';

			foreach ( $menus as $menu ) {
				if ( isset( $menu['menu'] ) ) {
					$items		=	$menu['menu'];
				} else {
					$items		=	array();
				}

				if ( isset( $menu['component'] ) ) {
					$return		.=		modCBAdminHelper::getMenuItems( $menu['component'], $items, $disabled );
				}
			}

			$return				.=	'</ul>';
		}

		return $return;
	}

	/**
	 * @param  array  $component  The parent menu node
	 * @param  array  $items      The menu items to output
	 * @param  bool   $disabled   If the menu item should be disabled (grayed)
	 * @param  int    $depth      The depth of the menu items
	 * @return null|string
	 */
	private static function getMenuItems( $component, $items = array(), $disabled = false, $depth = 0 ) {
		$parentTitleCleanHtml			=	( isset( $component['title'] ) ? $component['title'] : null );
		$parentAccess					=	( isset( $component['access'] ) ? $component['access'] : null );
		$return							=	null;

		if ( $parentTitleCleanHtml && modCBAdminHelper::checkAccess( $parentAccess ) ) {
			if ( ! $disabled ) {
				if ( ! $depth ) {
					$return				.=	'<li class="' . ( checkJversion( '3.0+' ) ? 'dropdown' : 'node' ) . '">'
										.		'<a' . ( checkJversion( '3.0+' ) ? ' class="dropdown-toggle" data-toggle="dropdown"' : null ) . ' href="#">'
										.			$parentTitleCleanHtml
										.			( checkJversion( '3.0+' ) ? ' <span class="caret"></span>' : null )
										.		'</a>';
				}

				if ( $items ) {
					$menu				=	null;

					foreach ( $items as $item ) {
						$title			=	( isset( $item['title'] ) ? $item['title'] : null );
						$link			=	( isset( $item['link'] ) ? $item['link'] : null );
						$access			=	( isset( $item['access'] ) ? $item['access'] : null );
						$icon			=	( isset( $item['icon'] ) ? $item['icon'] : null );
						$target			=	( isset( $item['target'] ) ? $item['target'] : null );
						$subMenu		=	( isset( $item['submenu'] ) ? $item['submenu'] : array() );

						if ( $title && $link && modCBAdminHelper::checkAccess( $access ) ) {
							$subMenu	=	( $subMenu ? modCBAdminHelper::getMenuItems( $item, $subMenu, false, ( $depth + 1 ) ) : null );

							$menu		.=			'<li' . ( $subMenu ? ' class="' . ( checkJversion( '3.0+' ) ? 'dropdown-submenu' : 'node' ) . '"' : null ) . '>'
										.				'<a' . ( $icon ? ' class="' . ( checkJversion( '3.0+' ) ? 'menu-' : 'icon-16-' ) . htmlspecialchars( $icon ) . '"' : null ) . ' href="' . htmlspecialchars( $link ) . '"' . ( $target ? ' target="' . htmlspecialchars( $target ) . '"' : null ) . '>'
										.					'<span>' . $title . '</span>'
										.				'</a>'
										.				$subMenu
										.			'</li>';
						} elseif ( $title == 'spacer' ) {
							$menu		.=			'<li class="' . ( checkJversion( '3.0+' ) ? 'divider' : 'separator' ) . '"><span></span></li>';
						}
					}

					if ( $menu ) {
						$return			.=		'<ul' . ( checkJversion( '3.0+' ) ? ' class="dropdown-menu"' : null ) . '>'
										.			$menu
										.		'</ul>';
					}
				}

				if ( ! $depth ) {
					$return				.=	'</li>';
				}
			} elseif ( ! $depth ) {
				$return					.=	'<li class="disabled">'
										.		'<a href="#">' . $parentTitleCleanHtml . '</a>'
										.	'</li>';
			}
		}

		return $return;
	}

	/**
	 * Re-enables the update site if disabled or creates it if missing
	 */
	public static function enableUpdateSite()
	{
		global $_CB_database;

		$query			=	'SELECT ' . $_CB_database->NameQuote( 'extension_id' )
						.	"\n FROM " . $_CB_database->NameQuote( '#__extensions' )
						.	"\n WHERE " . $_CB_database->NameQuote( 'element' ) . ' = ' . $_CB_database->Quote( 'comprofiler' );
		$_CB_database->setQuery( $query );
		$extensionId	=	$_CB_database->loadResult();

		$query			=	'SELECT ' . $_CB_database->NameQuote( 'update_site_id' )
						.	"\n FROM " . $_CB_database->NameQuote( '#__update_sites_extensions' )
						.	"\n WHERE " . $_CB_database->NameQuote( 'extension_id' ) . ' = ' . (int) $extensionId;
		$_CB_database->setQuery( $query );
		$updateSiteId	=	$_CB_database->loadResult();

		if ( $updateSiteId ) {
			$query		=	'SELECT ' . $_CB_database->NameQuote( 'enabled' )
						.	"\n FROM " . $_CB_database->NameQuote( '#__update_sites' )
						.	"\n WHERE " . $_CB_database->NameQuote( 'update_site_id' ) . ' = ' . (int) $updateSiteId;
			$_CB_database->setQuery( $query );
			$isEnabled	=	$_CB_database->loadResult();

			if ( ! $isEnabled ) {
				$query	=	'UPDATE ' . $_CB_database->NameQuote( '#__update_sites' )
						.	"\n SET " . $_CB_database->NameQuote( 'enabled' ) . " = 1"
						.	"\n WHERE " . $_CB_database->NameQuote( 'update_site_id' ) . " = " . (int) $updateSiteId;
				$_CB_database->setQuery( $query );
				$_CB_database->query();
			}
		} else {
			$query		=	'INSERT INTO ' . $_CB_database->NameQuote( '#__update_sites' )
						.	"\n ("
						.		$_CB_database->NameQuote( 'name' )
						.		', ' . $_CB_database->NameQuote( 'type' )
						.		', ' . $_CB_database->NameQuote( 'location' )
						.		', ' . $_CB_database->NameQuote( 'enabled' )
						.	')'
						.	"\n VALUES ("
						.		$_CB_database->Quote( 'Community Builder Package Update Site' )
						.		', ' . $_CB_database->Quote( 'collection' )
						.		', ' . $_CB_database->Quote( 'http://update.joomlapolis.net/versions/pkg-communitybuilder-list.xml' )
						.		', 1'
						.	')';
			$_CB_database->setQuery( $query );
			$_CB_database->query();

			$query		=	'INSERT INTO ' . $_CB_database->NameQuote( '#__update_sites_extensions' )
						.	"\n ("
						.		$_CB_database->NameQuote( 'update_site_id' )
						.		', ' . $_CB_database->NameQuote( 'extension_id' )
						.	')'
						.	"\n VALUES ("
						.		(int) $_CB_database->insertid()
						.		', ' . (int) $extensionId
						.	')';
			$_CB_database->setQuery( $query );
			$_CB_database->query();
		}
	}

	/**
	 * @param array $access
	 * @return bool
	 */
	private static function checkAccess( $access ) {
		if ( $access ) {
			$actions			=	( isset( $access[0] ) ? $access[0] : null );

			if ( $actions ) {
				$assetName		=	( isset( $access[1] ) ? ( $access[1] == 'root' ? null : $access[1] ) : 'com_comprofiler' );

				if ( ! is_array( $actions ) ) {
					$actions	=	array( $actions );
				}

				foreach( $actions as $action ) {
					if ( Application::MyUser()->isAuthorizedToPerformActionOnAsset( $action, $assetName ) ) {
						return true;
					}
				}
			}
		} else {
			return true;
		}

		return false;
	}

	/**
	 * @param int $duration
	 * @return string
	 */
	public static function getLatestCBVersion( $duration = 12 ) {
		global $_CB_framework, $ueConfig;

		cbimport( 'cb.snoopy' );

		$cache							=	$_CB_framework->getCfg( 'absolute_path' ) . '/cache/cblatestversion.xml';
		$xml							=	null;

		if ( file_exists( $cache ) ) {
			if ( ( ! $duration ) || ( intval( ( $_CB_framework->now() - filemtime( $cache ) ) / 3600 ) > $duration ) ) {
				$request				=	true;
			} else {
				$xml					=	new SimpleXMLElement( trim( file_get_contents( $cache ) ) );

				$request				=	false;
			}
		} else {
			$request					=	true;
		}

		if ( $request ) {
			$s							=	new CBSnoopy();
			$s->read_timeout			=	30;
			$s->referer					=	$_CB_framework->getCfg( 'live_site' );

			@$s->fetch( 'http://www.joomlapolis.com/versions/comprofilerversion.php?currentversion=' . urlencode( $ueConfig['version'] ) );

			if ( (int) $s->status == 200 ) {
				try {
					$results			=	$s->results;
					$infoPosition		=	strpos( $results, ':' );

					if ( $infoPosition === false ) {
						$version		=	$results;
						$info			=	null;
					} else {
						$version		=	substr( $results, 0, $infoPosition );
						$info			=	substr( $results, $infoPosition + 1 );
					}

					$xml				=	new SimpleXMLElement( '<cbversion version="' . htmlspecialchars( $version ) . '" info="' . htmlspecialchars( $info ) . '"></cbversion>' );

					$xml->saveXML( $cache );
				} catch ( Exception $e ) {}
			}
		}

		if ( $xml ) {
			return $xml->attributes( 'version' );
		} else {
			return null;
		}
	}

	/**
	 * @param string $url
	 * @param string $file
	 * @param int $duration
	 * @return SimpleXMLElement|null
	 */
	public static function getFeedXML( $url, $file, $duration = 12 ) {
		global $_CB_framework;

		cbimport( 'cb.snoopy' );

		$cache					=	$_CB_framework->getCfg( 'absolute_path' ) . '/cache/' . $file;
		$xml					=	null;

		if ( file_exists( $cache ) ) {
			if ( ( ! $duration ) || ( intval( ( $_CB_framework->now() - filemtime( $cache ) ) / 3600 ) > $duration ) ) {
				$request		=	true;
			} else {
				$xml			=	new SimpleXMLElement( trim( file_get_contents( $cache ) ) );

				$request		=	false;
			}
		} else {
			$request			=	true;
		}

		if ( $request ) {
			$s					=	new CBSnoopy();
			$s->read_timeout	=	30;
			$s->referer			=	$_CB_framework->getCfg( 'live_site' );

			@$s->fetch( $url );

			if ( (int) $s->status == 200 ) {
				try {
					$xml		=	new SimpleXMLElement( $s->results );

					$xml->saveXML( $cache );
				} catch ( Exception $e ) {}
			}
		}

		return $xml;
	}

	/**
	 * @param string $text
	 * @param null|int $length
	 * @return mixed|string
	 */
	static public function shortDescription( $text, $length = null ) {
		$text		=	stripslashes( strip_tags( $text ) );

		if ( $length && ( strlen( $text ) > $length ) ) {
			$text	=	preg_replace( '/(\.\.\.\s*){2,}/', '... ', trim( substr( $text, 0, $length ) ) . '...' );
		}

		$text		=	trim( $text );

		return $text;
	}

	/**
	 * @param string $text
	 * @return mixed
	 */
	static public function longDescription( $text ) {
		if ( preg_match_all( '/<a[^>]+>/i', $text, $links ) ) {
			foreach ( $links as $link ) {
				$text	=	str_replace( $link, str_replace( '<a', '<a target="_blank"', preg_replace( '/target="\w+"/i', '', $link ) ), $text );
			}
		}

		$text			=	preg_replace( '%src="[^"]+/(//www[^"]+)"%i', 'src="$1"', $text );

		return $text;
	}

	/**
	 * @param string $text
	 * @return null|string
	 */
	static public function descriptionIcon( $text ) {
		$logo		=	null;

		if ( preg_match( '/<img[^>]+>/i', $text, $image ) ) {
			if ( preg_match( '/src="([^"]+)"/i', $image[0], $src ) ) {
				$logo	=	'<div class="cbFeedItemLogoImg" style="background-image: url(' . htmlspecialchars( $src[1] ) . ')"></div>';
			}
		}

		if ( ! $logo ) {
			$logo		=	'<div class="cbFeedItemLogoImg cbFeedItemLogoImgEmpty"></div>';
		}

		return $logo;
	}
}
