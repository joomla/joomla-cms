<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Application\Application;
use CBLib\Registry\GetterInterface;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

class modCBLoginHelper {

	static function getType() {
		global $_CB_framework;

		return ( $_CB_framework->myId() > 0 ) ? 'logout' : 'login';
	}

	static function getReturnURL( $params, $type ) {
		global $cbSpecialReturnAfterLogin, $cbSpecialReturnAfterLogout;

		static $returnUrl			=	null;

		if ( ! isset( $returnUrl ) ) {
			$returnUrl				=	Application::Input()->get( 'get/return', '', GetterInterface::BASE64 );

			if ( $returnUrl ) {
				$returnUrl			=	base64_decode( $returnUrl );

				if ( ! JUri::isInternal( $returnUrl ) ) {
					// The URL isn't internal to the site; reset it to index to be safe:
					$returnUrl		=	'index.php';
				}
			} else {
				$isHttps			=	( isset( $_SERVER['HTTPS'] ) && ( ! empty( $_SERVER['HTTPS'] ) ) && ( $_SERVER['HTTPS'] != 'off' ) );
				$returnUrl			=	'http' . ( $isHttps ? 's' : '' ) . '://' . $_SERVER['HTTP_HOST'];

				if ( ( ! empty( $_SERVER['PHP_SELF'] ) ) && ( ! empty( $_SERVER['REQUEST_URI'] ) ) ) {
					$returnUrl		.=	$_SERVER['REQUEST_URI'];
				} else {
					$returnUrl		.=	$_SERVER['SCRIPT_NAME'];

					if ( isset( $_SERVER['QUERY_STRING'] ) && ( ! empty( $_SERVER['QUERY_STRING'] ) ) ) {
						$returnUrl	.=	'?' . $_SERVER['QUERY_STRING'];
					}
				}
			}

			$returnUrl				=	cbUnHtmlspecialchars( preg_replace( '/[\\\"\\\'][\\s]*javascript:(.*)[\\\"\\\']/', '""', preg_replace( '/eval\((.*)\)/', '', htmlspecialchars( urldecode( $returnUrl ) ) ) ) );

			if ( preg_match( '/index.php\?option=com_comprofiler&task=confirm&confirmCode=|index.php\?option=com_comprofiler&view=confirm&confirmCode=|index.php\?option=com_comprofiler&task=login|index.php\?option=com_comprofiler&view=login/', $returnUrl ) ) {
				$returnUrl			=	'index.php';
			}
		}

		$secureForm					=	(int) $params->get( 'https_post', 0 );

		if ( $type == 'login' ) {
			$loginReturnUrl 		=	$params->get( 'login', $returnUrl );

			if ( isset( $cbSpecialReturnAfterLogin ) ) {
				$loginReturnUrl		=	$cbSpecialReturnAfterLogin;
			}

			$url					=	cbSef( $loginReturnUrl, true, 'html', $secureForm );
		} elseif ( $type == 'logout' ) {
			$logoutReturnUrl 		=	$params->get( 'logout', 'index.php' );

			if ( $logoutReturnUrl == '#' ) {
				$logoutReturnUrl	=	$returnUrl;
			}

			if ( isset( $cbSpecialReturnAfterLogout ) ) {
				$logoutReturnUrl	=	$cbSpecialReturnAfterLogout;
			}

			$url					=	cbSef( $logoutReturnUrl, true, 'html', $secureForm );
		} else {
			$url					=	$returnUrl;
		}

		return base64_encode( $url );
	}

	static function getPlugins( $params, $type, $location = 'beforeButton', $tag = 'div', $horizontal = 0, $prefixHtml = '', $suffixHtml = '' ) {
		global $_PLUGINS;

		if ( ! $location ) {
			$location									=	'beforeButton';
		}

		if ( ! $tag ) {
			$tag										=	'div';
		}

		static $cache									=	array();

		if ( $type == 'logout' ) {
			$pluginClassPrefix							=	'cbLogoutForm';
			$pluginsTrigger								=	'onAfterLogoutForm';
		} else {
			$pluginClassPrefix							=	'cbLoginForm';
			$pluginsTrigger								=	'onAfterLoginForm';
		}

		$cacheId										=	( $pluginsTrigger . $horizontal );

		if ( (int) $params->get( 'cb_plugins', 1 ) && ( ! isset( $cache[$cacheId] ) ) ) {
			$pluginDisplays								=	array();
			$classSuffix								=	$params->get( 'moduleclass_sfx' );
			$usernameInputLength						=	(int) $params->get( 'name_length', 14 );
			$passwordInputLength						=	(int) $params->get( 'pass_length', 14 );

			$pluginsResults								=	$_PLUGINS->trigger( $pluginsTrigger, array( $usernameInputLength, $passwordInputLength, $horizontal, $classSuffix, &$params ) );

			if ( count( $pluginsResults ) > 0 ) foreach ( $pluginsResults as $pR ) {
				if ( is_array( $pR ) ) foreach ( $pR as $pK => $pV ) {
					if ( $pV != '' ) {
						$pluginDisplays[$pK][]			=	$pV;
					}
				} elseif ( $pR != '' ) {
					$pluginDisplays['beforeButton'][]	=	$pR;
				}
			}

			$cache[$cacheId]							=	$pluginDisplays;
		}

		$return											=	null;

		if ( isset( $cache[$cacheId][$location] ) ) {
			$return										.=	$prefixHtml;

			foreach ( $cache[$cacheId][$location] as $pV ) {
				$return									.=	( $tag ? '<' . htmlspecialchars( $tag ) . ' class="' . $pluginClassPrefix . ucfirst( htmlspecialchars( $location ) ) . '">' : null )
														.		$pV
														.	( $tag ? '</' . htmlspecialchars( $tag ) . '>' : null );
			}

			$return										.=	$suffixHtml;
		}

		return $return;
	}

	static function getTwoFactorMethods() {
		global $_CB_framework;

		if ( checkJversion( '3.2+' ) ) {
			require_once ( $_CB_framework->getCfg( 'absolute_path' ) . '/administrator/components/com_users/helpers/users.php' );

			return UsersHelper::getTwoFactorMethods();
		} else {
			return array();
		}
	}
}