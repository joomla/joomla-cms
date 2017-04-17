<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 11/28/13 7:09 PM $
* @package CBLib\AhaWow\View
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CBLib\AhaWow\View;

use CBTxt;
use cbValidator;

defined('CBLIB') or die();

/**
 * CBLib\AhaWow\View\ActionView Class implementation
 * 
 */
class ActionView {
	/**
	 * Draws a form posting to $getParams with CSS class $cssClass around $settings with $warnings at top, and $formHiddens hidden elements. Also add validation languages into head.
	 *
	 * @param  string        $settings
	 * @param  string        $warning
	 * @param  string[]      $formHiddens
	 * @param  string|array  $getParams
	 * @param  string|null   $cssClass
	 * @return string
	 */
	public function drawForm( $settings, $warning, $formHiddens, $getParams, $cssClass = null ) {
		$html				=	'';
		if ( $warning ) {
			$html			.=	'<div class="alert alert-danger">' . $warning . '</div>' . "\n";
		}
		if ( is_array( $getParams ) ) {
			$postUrl		=	'index.php';
			if ( $getParams && ( count( $getParams ) > 0 ) ) {
				foreach ( $getParams as $k => $v ) {
					$getParams[$k]	=	$k . '=' . htmlspecialchars( urlencode( $v ) );
				}
				$postUrl	.=	'?' . implode( '&', $getParams );
			}
		} else {
			$postUrl		=	$getParams;
		}
		if ( $formHiddens !== null ) {
			cbValidator::loadValidation();

			$html			.=	'<form enctype="multipart/form-data" action="' . cbSef( $postUrl ) . '" method="post" name="adminForm" id="cbAdminFormForm" class="cb_form form-auto cbValidation cbregfrontendform' . ( $cssClass ? ' ' . $cssClass : '' ) . '">' . "\n";
		}
		if ( $formHiddens !== null ) {
			foreach ( $formHiddens as $k => $v ) {
				$html		.=	"\t" . '<input type="hidden" name="' . htmlspecialchars( $k ) . '" value="' . htmlspecialchars( $v ) . '" />' . "\n";
			}
			$html			.=	cbGetSpoofInputTag( 'plugin' );
		}
		$html				.=	$settings;
		if ( $formHiddens !== null ) {
			$html			.=	"</form>\n";
		}

		return $html;
	}
}
 