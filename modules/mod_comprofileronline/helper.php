<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

class modCBOnlineHelper {

	static function getPlugins( $params, $location = 'afterUsers', $tag = 'div', $horizontal = 0, $prefixHtml = '', $suffixHtml = '' ) {
		global $_PLUGINS;

		$mode											=	(int) $params->get( 'mode', 1 );

		if ( ! $location ) {
			$location									=	'afterUsers';
		}

		if ( ! $tag ) {
			$tag										=	'div';
		}

		static $cache									=	array();

		$cacheId										=	( $mode . $horizontal );

		if ( (int) $params->get( 'cb_plugins', 1 ) && ( ! isset( $cache[$cacheId] ) ) ) {
			$pluginDisplays								=	array();
			$classSuffix								=	$params->get( 'moduleclass_sfx' );

			$pluginsResults								=	$_PLUGINS->trigger( 'onAfterOnlineModule', array( $mode, $horizontal, $classSuffix, &$params ) );

			if ( count( $pluginsResults ) > 0 ) foreach ( $pluginsResults as $pR ) {
				if ( is_array( $pR ) ) foreach ( $pR as $pK => $pV ) {
					if ( $pV != '' ) {
						$pluginDisplays[$pK][]			=	$pV;
					}
				} elseif ( $pR != '' ) {
					$pluginDisplays['afterUsers'][]		=	$pR;
				}
			}

			$cache[$cacheId]							=	$pluginDisplays;
		}

		$return											=	null;

		if ( isset( $cache[$cacheId][$location] ) ) {
			$return										.=	$prefixHtml;

			foreach ( $cache[$cacheId][$location] as $pV ) {
				$return									.=	( $tag ? '<' . htmlspecialchars( $tag ) . ' class="cbOnlineModule' . ucfirst( htmlspecialchars( $location ) ) . '">' : null )
														.		$pV
														.	( $tag ? '</' . htmlspecialchars( $tag ) . '>' : null );
			}

			$return										.=	$suffixHtml;
		}

		return $return;
	}
}
