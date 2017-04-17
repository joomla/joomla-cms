<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 6/20/14 1:56 PM $
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

defined('CBLIB') or die();

/**
 * CBCookie Class implementation
 * Easy cookies settings handling special cases
 */
class CBCookie
{
	/**
	 * PHP setcookie but smarter and more secure:
	 * //TBD: add domain info in cookie-name
	 *
	 * @param  string  $name
	 * @param  string  $value
	 * @param  int     $expire
	 * @param  string  $path
	 * @param  string  $domain
	 * @param  boolean $secure
	 * @param  boolean $httpOnly
	 * @return boolean
	 */
	public static function setcookie( $name, $value = '', $expire = 0, $path = null, $domain = null, $secure = false,  $httpOnly = false )
	{
		global $_CB_framework, $_SERVER;

		static $PrivacyHeaderSent		=	false;

		if ( ! $PrivacyHeaderSent ) {
			header('P3P: CP="NOI ADM DEV PSAi COM NAV OUR OTRo STP IND DEM"');		// needed for IE6 to accept this cookie in higher security setting.
			$PrivacyHeaderSent			=	true;
		}

		$sp								=	session_get_cookie_params();

		$liveSiteIsDefined				=	false;
		$matches						=	null;

		if ( ( $domain === null ) || ( $path === null ) ) {
			if ( $_CB_framework ) {
				$liveSiteIsDefined		=	( preg_match( '#^https?://([^/]+)(.*)#i', $_CB_framework->getCfg( 'live_site' ), $matches ) );
			}

		}
		if ( $domain === null ) {
			// handles www and non-www domains: e.g. live_site = 'site.com' but on 'www.site.com' (or the other way around):
			// in that case, cookie-domain needs to be '.site.com':
			if ( $liveSiteIsDefined ) {
				// remove port part as setcookie does not want/need that:
				$pageDomain				=	preg_replace( '/:[0-9]+$/', '', $_SERVER['HTTP_HOST'] );
				$liveDomain				=	preg_replace( '/:[0-9]+$/', '', $matches[1] );
				if ( $liveDomain === $pageDomain ) {
					$domain				=	$liveDomain;
				} else {
					$live_len			=	strlen( $liveDomain );
					$page_len			=	strlen( $pageDomain );
					if ( ( $live_len < $page_len )
						&& ( $liveDomain === substr( $pageDomain, $page_len - $live_len ) )
						&& ( substr( $pageDomain, $page_len - $live_len - 1, 1 ) === '.' ) )
					{
						// ends of domains match, but live_site domain is shorter (e.g. no 'www.'):
						$domain			=	'.' . $liveDomain;		// '.' in front needed for 2-3 dots security-rule of browsers ( '.site.com' )
					} elseif ( ( $live_len > $page_len )
						&& ( $pageDomain === substr( $liveDomain, $live_len - $page_len ) )
						&& ( substr( $liveDomain, $live_len - $page_len - 1, 1 ) === '.' ) )
					{
						$domain			=	'.' . $pageDomain;
					}
				}
			}

			if ( $domain === null ) {
				$domain					=	$sp['domain'];
			}
		}

		if ( substr_count( $domain, '.' ) < 2 ) {
			$domain						=	null;
		}

		if ( $path === null ) {
			$directory_len				=	strlen( $matches[2] );

			if ( $liveSiteIsDefined && ( $directory_len > 1 ) ) {
				// get the query string:
				if ( ! empty( $_SERVER['PHP_SELF'] ) && ! empty( $_SERVER['REQUEST_URI'] ) ) {
					$queryString		=	urldecode( $_SERVER['REQUEST_URI'] );	// Apache
				} else {
					$queryString		=	urldecode( $_SERVER['SCRIPT_NAME'] );	// IIS
					// that part is not needed in this case:
					//	if (isset($_SERVER['QUERY_STRING']) && ! empty($_SERVER['QUERY_STRING'])) {
					//		$return	.=	'?' . $_SERVER['QUERY_STRING'];
					//	}
				}

				if ( substr( $queryString, 0, $directory_len ) === $matches[2] ) {
					$path				=	$matches[2];
				}
			}

			if ( $path === null ) {
				$path					=	'/';		// $sp['path']
			}
		}

		if ( $secure === null ) {
			$secure					=	$sp['secure'];
		}

		return setcookie( $name, $value, $expire, $path, $domain, $secure, $httpOnly );
	}

	/**
	 * gets cookie set by cbSetcookie ! WARNING: always unescaped
	 * //TBD: add domain info in cookie-name
	 *
	 * @param  string            $name
	 * @param  string|array      $defaultValue
	 * @return string|array|null
	 */
	public static function getcookie( $name, $defaultValue = null )
	{
		global $_COOKIE;

		return cbStripslashes( cbGetParam( $_COOKIE, $name, $defaultValue ) );
	}
}
