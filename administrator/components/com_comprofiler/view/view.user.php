<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Language\CBTxt;
use CB\Database\Table\UserTable;

// ensure this file is being included by a parent file
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

class CBView_user
{
	/**
	 * Outputs legacy user edit display
	 *
	 * @deprecated 2.0
	 *
	 * @param UserTable  $user
	 * @param string     $option
	 * @param int        $newCBuser
	 * @param array      $postdata
	 */
	public function edituser( $user, /** @noinspection PhpUnusedParameterInspection */ $option, $newCBuser, &$postdata )
	{
		global $_CB_framework, $_CB_Backend_Title, $_PLUGINS;

		$results				=	$_PLUGINS->trigger( 'onBeforeUserProfileEditDisplay', array( &$user, 2 ) );

		if ( $_PLUGINS->is_errors() ) {
			cbRedirect( $_CB_framework->backendViewUrl( 'showusers' ), $_PLUGINS->getErrorMSG(), 'error' );
		}

		_CBsecureAboveForm( 'edituser' );

		cbimport( 'cb.validator' );
		outputCbTemplate( 2 );
		initToolTip( 2 );
		outputCbJs( 2 );

		$tabs					=	new cbTabs( ( ( ( $_CB_framework->getUi() == 2 ) && ( ! isset( $_REQUEST['tab'] ) ) ) ? 1 : 0 ), 2 ); // use cookies in backend to remember selected tab.
		$tabcontent				=	$tabs->getEditTabs( $user, $postdata, 'htmledit', 'divs' );

		$_CB_Backend_Title		=	array( 0 => array( 'fa fa-user', ( $user->id ? CBTxt::T( 'COMMUNITY_BUILDER_EDIT_USER_USERNAME', 'Community Builder: Edit User [[username]]', array( '[username]' => $user->username ) ) : CBTxt::T( 'Community Builder: New User' ) ) ) );

		cbValidator::loadValidation();

		if ( is_array( $results ) ) {
			echo implode( '', $results );
		}

		$return					=	'<form action="' . $_CB_framework->backendUrl( 'index.php' ) . '" method="post" name="adminForm" id="cbcheckedadminForm" enctype="multipart/form-data" autocomplete="off" class="cb_form form-auto cbValidation">'
								.		$tabcontent
								.		'<input type="hidden" name="id" value="' . (int) $user->id . '" />'
								.		'<input type="hidden" name="newCBuser" value="' . (int) $newCBuser . '" />'
								.		'<input type="hidden" name="option" value="com_comprofiler" />'
								.		'<input type="hidden" name="view" value="save" />'
								.		cbGetSpoofInputTag( 'user' )
								.		'<div class="cbIconsBottom">'
								.			getFieldIcons( 2, true, true, '', '', true )
								.		'</div>'
								.	'</form>';

		echo $return;
	}
}
