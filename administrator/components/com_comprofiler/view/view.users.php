<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CB\Database\Table\UserTable;
use CBLib\Language\CBTxt;

// ensure this file is being included by a parent file
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

class CBView_users {

	/**
	 * Outputs legacy plugin rows for legacy user management views
	 *
	 * @param  array $pluginRows
	 * @return null|string
	 * @deprecated 2.0
	 */
	public function _pluginRows( $pluginRows ) {
		$return				=	null;

		foreach ( $pluginRows as $pluginOutput ) {
			if ( is_array( $pluginOutput ) ) foreach ( $pluginOutput as $title => $content ) {
				$return		.=	'<div class="form-group cb_form_line clearfix">'
							.		'<label class="control-label col-sm-3">' . $title . '</label>'
							.		'<div class="cb_field col-sm-9">'
							.			'<div>' . $content . '</div>'
							.		'</div>'
							.	'</div>';
			}
		}

		return $return;
	}

	/**
	 * Outputs legacy mass mailer display
	 *
	 * @deprecated 2.0
	 *
	 * @param UserTable[]  $rows
	 * @param string       $emailSubject
	 * @param string       $emailBody
	 * @param string       $emailAttach
	 * @param string       $emailFromName
	 * @param string       $emailFromAddr
	 * @param string       $emailReplyName
	 * @param string       $emailReplyAddr
	 * @param int          $emailsPerBatch
	 * @param int          $emailsBatch
	 * @param int          $emailPause
	 * @param bool         $simulationMode
	 * @param array        $pluginRows
	 */
	public function emailUsers( $rows, $emailSubject, $emailBody, $emailAttach, $emailFromName, $emailFromAddr, $emailReplyName, $emailReplyAddr, $emailsPerBatch, $emailsBatch, $emailPause, $simulationMode, $pluginRows ) {
		global $_CB_framework, $_CB_Backend_Title;

		_CBsecureAboveForm( 'showUsers' );

		cbimport( 'cb.validator' );
		outputCbTemplate( 2 );
		outputCbJs( 2 );

		$_CB_Backend_Title		=	array( 0 => array( 'fa fa-envelope-o', CBTxt::T( 'Community Builder: Mass Mailer' ) ) );

		$editorSave				=	$_CB_framework->saveCmsEditorJS( 'emailbody' );

		if ( $editorSave ) {
	 		$js					=	"$( '.cbEmailUsersForm' ).submit( function() { "
								.		$editorSave
								.	"});";
		} else {
			$js					=	null;
		}

		cbValidator::outputValidatorJs( $js );

		$emailsList				=	array();

		foreach ( array_slice( $rows, 0, 100 ) as $row ) {
			$emailsList[]		=	htmlspecialchars( $row->name ) . ' &lt;' . htmlspecialchars( $row->email ) . '&gt;';
		}

		$return					=	'<form action="' . $_CB_framework->backendUrl( 'index.php' ) . '" method="post" name="adminForm" class="cb_form form-auto cbEmailUsersForm">'
								.		'<div class="form-group cb_form_line clearfix">'
								.			'<label class="control-label col-sm-3">' . CBTxt::Th( 'SEND_EMAIL_TO_TOTAL_USERS', 'Send Email to [total] users', array( '[total]' => (int) count( $rows ) ) ) . '</label>'
								.			'<div class="cb_field col-sm-9">'
								.				'<div>' . implode( ', ', $emailsList ) . ( count( $rows ) > 100 ? ' <strong>' . CBTxt::Th( 'AND_COUNT_MORE_USERS', 'and [count] more users.', array( '[count]' => (int) ( count( $rows ) - 100 ) ) ) . '</strong>' : null ) . '</div>'
								.			'</div>'
								.		'</div>'
								.		'<div class="form-group cb_form_line clearfix">'
								.			'<label class="control-label col-sm-3">' . CBTxt::Th( 'MASS_MAILER_SIMULATION_MODE_LABEL', 'Simulation Mode' ) . '</label>'
								.			'<div class="cb_field col-sm-9">'
								.				'<div>'
								.					'<input type="checkbox" name="simulationmode" id="simulationmode"' . ( $simulationMode ? ' checked="checked"' : null ) . ' /> <label for="simulationmode">' . CBTxt::T( 'Do not send emails, just show me how it works' ) . '</label>'
								.					getFieldIcons( 2, false, false, CBTxt::T( 'MASS_MAILER_SIMULATION_MODE_TOOLTIP', 'Check this box to simulate email sending in a dry run mode. No emails are actually sent.' ), CBTxt::T( 'MASS_MAILER_SIMULATION_MODE_LABEL', 'Simulation Mode' ), false, 4 )
								.				'</div>'
								.			'</div>'
								.		'</div>'
								.		'<div class="form-group cb_form_line clearfix">'
								.			'<label class="control-label col-sm-3">' . CBTxt::Th( 'MASS_MAILER_SUBJECT_LABEL', 'Email Subject' ) . '</label>'
								.			'<div class="cb_field col-sm-9">'
								.				'<div>'
								.					'<input type="text" name="emailsubject" value="' . htmlspecialchars( $emailSubject ) . '" class="form-control required" size="60" />'
								.					getFieldIcons( 2, false, false, CBTxt::T( 'MASS_MAILER_SUBJECT_TOOLTIP', 'Type in the subject of the mass mailing (CB field substitutions are supported).' ), CBTxt::T( 'MASS_MAILER_SUBJECT_LABEL', 'Email Subject' ), false, 4 )
								.				'</div>'
								.			'</div>'
								.		'</div>'
								.		'<div class="form-group cb_form_line clearfix">'
								.			'<label class="control-label col-sm-3">' . CBTxt::Th( 'MASS_MAILER_MESSAGE_LABEL', 'Email Message' ) . '</label>'
								.			'<div class="cb_field col-sm-9">'
								.				'<div>'
								.					$_CB_framework->displayCmsEditor( 'emailbody', $emailBody, 600, 200, 50, 7 )
								.					getFieldIcons( 2, false, false, CBTxt::T( 'MASS_MAILER_MESSAGE_TOOLTIP', 'Type in the main message body of your mass mailing (HTML editor and CB field substitutions are supported).' ), CBTxt::T( 'MASS_MAILER_MESSAGE_LABEL', 'Email Message' ), false, 4 )
								.				'</div>'
								.			'</div>'
								.		'</div>'
								.		'<div class="form-group cb_form_line clearfix">'
								.			'<label class="control-label col-sm-3">' . CBTxt::Th( 'MASS_MAILER_ATTACHMENTS_LABEL', 'Email Attachments' ) . '</label>'
								.			'<div class="cb_field col-sm-9">'
								.				'<div>'
								.					'<input type="text" name="emailattach" value="' . htmlspecialchars( $emailAttach ) . '" class="form-control" size="80" />'
								.					getFieldIcons( 2, false, false, CBTxt::T( 'MASS_MAILER_ATTACHMENTS_TOOLTIP', 'Absolute server path to file that should be attached to each email. Multiple files can be specified using a comma separator.' ), CBTxt::T( 'MASS_MAILER_ATTACHMENTS_LABEL', 'Email Attachments' ), false, 4 )
								.				'</div>'
								.			'</div>'
								.		'</div>'
								.		'<div class="form-group cb_form_line clearfix">'
								.			'<label class="control-label col-sm-3">' . CBTxt::Th( 'Substitutions for Subject, Message, and Attachments' ) . '</label>'
								.			'<div class="cb_field col-sm-9">'
								.				'<div>' . CBTxt::T( 'You can use all CB substitutions as in most parts: e.g.: [cb:if team="winners"] Congratulations [cb:userfield field="name" /], you are in the winning team! [/cb:if]' ) . '</div>'
								.			'</div>'
								.		'</div>'
								.		$this->_pluginRows( $pluginRows )
								.		'<div class="form-group cb_form_line clearfix">'
								.			'<label class="control-label col-sm-3">' . CBTxt::Th( 'MASS_MAILER_FROM_NAME_LABEL', 'From Name' ) . '</label>'
								.			'<div class="cb_field col-sm-9">'
								.				'<div>'
								.					'<input type="text" name="emailfromname" value="' . htmlspecialchars( $emailFromName ) . '" class="form-control" size="30" />'
								.					getFieldIcons( 2, false, false, CBTxt::T( 'MASS_MAILER_FROM_NAME_TOOLTIP', 'The name to be used in the From field of email. If left empty the CB and Joomla configuration defaults will be used.' ), CBTxt::T( 'MASS_MAILER_FROM_NAME_LABEL', 'From Name' ), false, 4 )
								.				'</div>'
								.			'</div>'
								.		'</div>'
								.		'<div class="form-group cb_form_line clearfix">'
								.			'<label class="control-label col-sm-3">' . CBTxt::Th( 'MASS_MAILER_FROM_ADDRESS_LABEL', 'From Email Address' ) . '</label>'
								.			'<div class="cb_field col-sm-9">'
								.				'<div>'
								.					'<input type="text" name="emailfromaddr" value="' . htmlspecialchars( $emailFromAddr ) . '" class="form-control" size="40" />'
								.					getFieldIcons( 2, false, false, CBTxt::T( 'MASS_MAILER_FROM_ADDRESS_TOOLTIP', 'The email address to be user in the From field of email. If left empty the CB and Joomla settings will be used.' ), CBTxt::T( 'MASS_MAILER_FROM_ADDRESS_LABEL', 'From Email Address' ), false, 4 )
								.				'</div>'
								.			'</div>'
								.		'</div>'
								.		'<div class="form-group cb_form_line clearfix">'
								.			'<label class="control-label col-sm-3">' . CBTxt::Th( 'MASS_MAILER_REPLY_TO_NAME_LABEL', 'Reply-To Name' ) . '</label>'
								.			'<div class="cb_field col-sm-9">'
								.				'<div>'
								.					'<input type="text" name="emailreplyname" value="' . htmlspecialchars( $emailReplyName ) . '" class="form-control" size="30" />'
								.					getFieldIcons( 2, false, false, CBTxt::T( 'MASS_MAILER_REPLY_TO_NAME_TOOLTIP', 'The Reply-To Name value to be used in the From field of email. If left empty the CB and Joomla settings will be used.' ), CBTxt::T( 'MASS_MAILER_REPLY_TO_NAME_LABEL', 'Reply-To Name' ), false, 4 )
								.				'</div>'
								.			'</div>'
								.		'</div>'
								.		'<div class="form-group cb_form_line clearfix">'
								.			'<label class="control-label col-sm-3">' . CBTxt::Th( 'MASS_MAILER_REPLY_TO_ADDRESS_LABEL', 'Reply-To Email Address' ) . '</label>'
								.			'<div class="cb_field col-sm-9">'
								.				'<div>'
								.					'<input type="text" name="emailreplyaddr" value="' . htmlspecialchars( $emailReplyAddr ) . '" class="form-control" size="40" />'
								.					getFieldIcons( 2, false, false, CBTxt::T( 'MASS_MAILER_REPLY_TO_ADDRESS_TOOLTIP', 'The Reply-To Email address to be used in the email.' ), CBTxt::T( 'MASS_MAILER_REPLY_TO_ADDRESS_LABEL', 'Reply-To Email Address' ), false, 4 )
								.				'</div>'
								.			'</div>'
								.		'</div>'
								.		'<div class="form-group cb_form_line clearfix">'
								.			'<label class="control-label col-sm-3">' . CBTxt::Th( 'MASS_MAILER_EMAILS_PER_BATCH_LABEL', 'Emails per batch' ) . '</label>'
								.			'<div class="cb_field col-sm-9">'
								.				'<div>'
								.					'<input type="text" name="emailsperbatch" value="' . htmlspecialchars( $emailsPerBatch ) . '" class="form-control required digits" size="12" />'
								.					getFieldIcons( 2, false, false, CBTxt::T( 'MASS_MAILER_EMAILS_PER_BATCH_TOOLTIP', 'The number of emails to be sent in each batch (default 50).' ), CBTxt::T( 'MASS_MAILER_EMAILS_PER_BATCH_LABEL', 'Emails per batch' ), false, 4 )
								.				'</div>'
								.			'</div>'
								.		'</div>'
								.		'<div class="form-group cb_form_line clearfix">'
								.			'<label class="control-label col-sm-3">' . CBTxt::Th( 'MASS_MAILER_SECONDS_BETWEEN_BATCHES_LABEL', 'Seconds of pause between batches' ) . '</label>'
								.			'<div class="cb_field col-sm-9">'
								.				'<div>'
								.					'<input type="text" name="emailpause" value="' . htmlspecialchars( $emailPause ) . '" class="form-control required digits" size="12" />'
								.					getFieldIcons( 2, false, false, CBTxt::T( 'MASS_MAILER_SECONDS_BETWEEN_BATCHES_TOOLTIP', 'The number of seconds to pause between batch sending (default is 30 sec).' ), CBTxt::T( 'MASS_MAILER_SECONDS_BETWEEN_BATCHES_LABEL', 'Seconds of pause between batches' ), false, 4 )
								.				'</div>'
								.			'</div>'
								.		'</div>'
								.		'<input type="hidden" name="option" value="com_comprofiler" />'
								.		'<input type="hidden" name="view" value="emailusers" />'
								.		'<input type="hidden" name="boxchecked" value="0" />';

		foreach ( $rows as $row ) {
			$return				.=		'<input type="hidden" name="cid[]" value="' . (int) $row->id . '">';
		}

		$return					.=		cbGetSpoofInputTag( 'user' )
								.	'</form>';

		echo $return;
	}

	/**
	 * Sends legacy mass mailer
	 *
	 * @deprecated 2.0
	 *
	 * @param  UserTable[]  $rows
	 * @param  string       $emailSubject
	 * @param  string       $emailBody
	 * @param  string       $emailAttach
	 * @param  string       $emailFromName
	 * @param  string       $emailFromAddr
	 * @param  string       $emailReplyName
	 * @param  string       $emailReplyAddr
	 * @param  int          $emailsPerBatch
	 * @param  int          $emailsBatch
	 * @param  int          $emailPause
	 * @param  bool         $simulationMode
	 * @param  array        $pluginRows
	 * @return void
	 */
	public function startEmailUsers( $rows, $emailSubject, $emailBody, $emailAttach, $emailFromName, $emailFromAddr, $emailReplyName, $emailReplyAddr, $emailsPerBatch, $emailsBatch, $emailPause, $simulationMode, $pluginRows ) {
		global $_CB_framework, $_CB_Backend_Title;

		_CBsecureAboveForm( 'showUsers' );

		outputCbTemplate( 2 );
		outputCbJs( 2 );

		$_CB_Backend_Title			=	array( 0 => array( 'fa fa-envelope-o', CBTxt::T( 'Community Builder: Sending Mass Mailer' ) ) );

		$userIds					=	array();

		foreach ( $rows as $row ) {
			$userIds[]				=	(int) $row->id;
		}

		$cbSpoofField				=	cbSpoofField();
		$cbSpoofString				=	cbSpoofString( null, 'cbadmingui' );
		$regAntiSpamFieldName		=	cbGetRegAntiSpamFieldName();
		$regAntiSpamValues			=	cbGetRegAntiSpams();

		cbGetRegAntiSpamInputTag( $regAntiSpamValues );

		$maximumBatches				=	( count( $rows ) / $emailsPerBatch );

		if ( $maximumBatches < 1 ) {
			$maximumBatches			=	1;
		}

		$progressPerBatch			=	round( 100 / $maximumBatches );
		$delayInMilliseconds		=	( $emailPause ? 0 : ( $emailPause * 1000 ) );

		$js							=	"var cbbatchemail = function( batch, emailsbatch, emailsperbatch ) {"
									.		"$.ajax({"
									.			"type: 'POST',"
									.			"url: '" . addslashes( $_CB_framework->backendViewUrl( 'ajaxemailusers', false, array(), 'raw' ) ) . "',"
									.			"dataType: 'json',"
									.			"data: {"
									.				"emailsubject: '" . addslashes( $emailSubject ) . "',"
									.				"emailbody: '" . addslashes( rawurlencode( $emailBody ) ) . "',"
									.				"emailattach: '" . addslashes( $emailAttach ) . "',"
									.				"emailfromname: '" . addslashes( $emailFromName ) . "',"
									.				"emailfromaddr: '" . addslashes( $emailFromAddr ) . "',"
									.				"emailreplyname: '" . addslashes( $emailReplyName ) . "',"
									.				"emailreplyaddr: '" . addslashes( $emailReplyAddr ) . "',"
									.				"emailsperbatch: emailsperbatch,"
									.				"emailsbatch: emailsbatch,"
									.				"emailpause: '" . addslashes( $emailPause ) . "',"
									.				"simulationmode: '" . addslashes( $simulationMode ) . "',"
									.				"cid: " . json_encode( $userIds ) . ","
									.				$cbSpoofField . ": '" . addslashes( $cbSpoofString ) . "',"
									.				$regAntiSpamFieldName . ": '" . addslashes( $regAntiSpamValues[0] ) . "'"
									.			"},"
									.			"success: function( data, textStatus, jqXHR ) {"
									.				"if ( data.result == 1 ) {" // Success (Loop)
									.					"var progress = ( " . (int) $progressPerBatch . " * batch ) + '%';"
									.					"$( '#cbProgressIndicatorBar > .progress-bar' ).css({ width: progress });"
									.					"$( '#cbProgressIndicatorBar > .progress-bar > span' ).html( progress );"
									.					"$( '#cbProgressIndicator' ).html( data.htmlcontent );"
									.					"setTimeout( cbbatchemail( ( batch + 1 ), ( emailsbatch + emailsperbatch ), emailsperbatch ), " . (int) $delayInMilliseconds . " );"
									.				"} else if ( data.result == 2 ) {" // Success (Done)
									.					"$( '#cbProgressIndicatorBar' ).removeClass( 'progress-striped active' );"
									.					"$( '#cbProgressIndicatorBar > .progress-bar' ).css({ width: '100%' });"
									.					"$( '#cbProgressIndicatorBar > .progress-bar' ).addClass( 'progress-bar-success' );"
									.					"$( '#cbProgressIndicatorBar > .progress-bar > span' ).html( '100%' );"
									.					"$( '#cbProgressIndicator' ).html( data.htmlcontent );"
									.				"} else {" // Failed
									.					"$( '#cbProgressIndicatorBar' ).removeClass( 'progress-striped active' );"
									.					"$( '#cbProgressIndicatorBar > .progress-bar' ).css({ width: '100%' });"
									.					"$( '#cbProgressIndicatorBar > .progress-bar' ).addClass( 'progress-bar-danger' );"
									.					"$( '#cbProgressIndicatorBar > .progress-bar > span' ).html( '" . addslashes( CBTxt::T( 'Email failed to send' ) ) . "' );"
									.					"$( '#cbProgressIndicator' ).html( data.htmlcontent );"
									.				"}"
									.			"},"
									.			"error: function( jqXHR, textStatus, errorThrown ) {"
									.				"$( '#cbProgressIndicatorBar' ).removeClass( 'progress-striped active' );"
									.				"$( '#cbProgressIndicatorBar > .progress-bar' ).css({ width: '100%' });"
									.				"$( '#cbProgressIndicatorBar > .progress-bar' ).addClass( 'progress-bar-danger' );"
									.				"$( '#cbProgressIndicatorBar > .progress-bar > span' ).html( '" . addslashes( CBTxt::T( 'Email failed to send' ) ) . "' );"
									.				"$( '#cbProgressIndicator' ).html( errorThrown );"
									.			"}"
									.		"});"
									.	"};"
									.	"cbbatchemail( 1, " . (int) $emailsBatch . ", " . (int) $emailsPerBatch . " );";

		$_CB_framework->outputCbJQuery( $js );

		$return						=	'<form action="' . $_CB_framework->backendUrl( 'index.php' ) . '" method="post" id="cbmailbatchform" name="adminForm" class="cb_form form-auto cbEmailUsersBatchForm">';

		if ( $simulationMode ) {
			$return					.=		'<div class="form-group cb_form_line clearfix">'
									.			'<label class="control-label col-sm-3">' . CBTxt::T( 'MASS_MAILER_SIMULATION_MODE_LABEL', 'Simulation Mode' ) . '</label>'
									.			'<div class="cb_field col-sm-9">'
									.				'<div><input type="checkbox" name="simulationmode" id="simulationmode" checked="checked" disabled="disabled" /> <label for="simulationmode">' . CBTxt::T( 'Do not send emails, just show me how it works' ) . '</label></div>'
									.			'</div>'
									.		'</div>';
		}

		$return						.=		$this->_pluginRows( $pluginRows )
									.		'<div class="form-group cb_form_line clearfix">'
									.			'<label class="control-label col-sm-3">' . CBTxt::T( 'SEND_EMAIL_TO_TOTAL_USERS', 'Send Email to [total] users', array( '[total]' => (int) count( $rows ) ) ) . '</label>'
									.			'<div class="cb_field col-sm-9">'
									.				'<div>'
									.					'<div id="cbProgressIndicatorBar" class="progress progress-striped active">'
									.						'<div class="progress-bar" style="width: 0%;">'
									.							'<span></span>'
									.						'</div>'
									.					'</div>'
									.					'<div id="cbProgressIndicator"></div>'
									.				'</div>'
									.			'</div>'
									.		'</div>'
									.		$this->_pluginRows( $pluginRows );

		if ( ! $simulationMode ) {
			$return					.=		'<input type="hidden" name="simulationmode" value="' . htmlspecialchars( $simulationMode ) . '" />';
		}

		$return						.=		'<input type="hidden" name="option" value="com_comprofiler" />'
									.		'<input type="hidden" name="view" value="ajaxemailusers" />'
									.		'<input type="hidden" name="boxchecked" value="0" />';

		foreach ( $rows as $row ) {
			$return					.=		'<input type="hidden" name="cid[]" value="' . (int) $row->id . '">';
		}

		$return						.=		cbGetSpoofInputTag( 'user' )
									.	'</form>';

		echo $return;
	}

	/**
	 * Outputs legacy ajax results of mass mailer
	 *
	 * @param string  $usernames
	 * @param string  $emailSubject
	 * @param string  $emailBody
	 * @param string  $emailAttach
	 * @param string  $emailFromName
	 * @param string  $emailFromAddr
	 * @param string  $emailReplyName
	 * @param string  $emailReplyAddr
	 * @param int     $limitstart
	 * @param int     $limit
	 * @param int     $total
	 * @param string  $errors
	 * @deprecated 2.0
	 */
	public function ajaxResults( $usernames, $emailSubject, $emailBody, $emailAttach, $emailFromName, $emailFromAddr, $emailReplyName, $emailReplyAddr, $limitstart, $limit, $total, $errors ) {
		global $_CB_framework;

		$return				=	null;

		if ( $errors == 0 ) {
			$return			.=	'<div class="page-header">'
							.		'<h3>' . CBTxt::T( 'SENT_EMAIL_TO_COUNT_OF_TOTAL_USERS', 'Sent email to [count] of [total] users', array( '[count]' => min( $total, $limitstart + $limit ), '[total]' => $total ) ) . '</h3>'
							.	'</div>'
							.	CBTxt::T( 'JUST_SENT_COUNT_EMAILS_TO_FOLLOWING_USERS_USERNAMES', 'Just sent [count] emails to following users: [usernames]', array( '[count]' => min( $limit, $total - $limitstart ), '[usernames]' => $usernames ) );
		} else {
			$return			.=	'<div class="page-header">'
							.		'<h3>' . CBTxt::T( 'COULD_NOT_EMAIL_TO_ERRORS_OF_COUNT_USERS_OUT_OF_TOTAL_EMAILS_TO_SEND', 'Could not send email to [errors] of [count] users out of [total] emails to send', array( '[count]' => min( $total, $limitstart + $limit ), '[total]' => $total, '[errors]' => $errors ) ) . '</h3>'
							.	'</div>'
							.	CBTxt::T( 'JUST_SENT_COUNT_EMAILS_TO_FOLLOWING_USERS_USERNAMES', 'Just sent [count] emails to following users: [usernames]', array( '[count]' => ( min( $limit, $total - $limitstart ) - $errors ), '[usernames]' => $usernames ) );
		}

		if ( ( $total - ( $limitstart + $limit ) ) > 0 ) {
			$return			.=	'<div class="page-header">'
							.		'<h3>' . CBTxt::T( 'STILL_COUNT_EMAILS_REMAINING_TO_SEND', 'Still [count] emails remaining to send', array( '[count]' => ( $total - ( $limitstart + $limit ) ) ) ) . '</h3>'
							.	'</div>';
		} else {
			if ( $errors > 0 ) {
				$return		.=	'<div class="page-header">'
							.		'<h3>' . CBTxt::T( 'ERRORS_EMAILS_COULD_NOT_BE_SENT_DUE_TO_A_SENDING_ERROR', '[errors] emails could not be sent due to a sending error', array( '[errors]' => $errors ) ) . '</h3>'
							.	'</div>';
			} elseif ( $total == 1 ) {
				$return		.=	'<div class="page-header">'
							.		'<h3>' . CBTxt::T( 'Your email has been sent' ) . '</h3>'
							.	'</div>';
			} else {
				$return		.=	'<div class="page-header">'
							.		'<h3>' . CBTxt::T( 'ALL_TOTAL_EMAILS_HAVE_BEEN_SENT', 'All [total] emails have been sent', array( '[total]' => $total ) ) . '</h3>'
							.	'</div>';
			}
		}

		if ( ! ( $total - ( $limitstart + $limit ) > 0 ) ) {
			$return			.=	'<div class="form-group cb_form_line clearfix">'
							.		'<label class="control-label col-sm-3">' . CBTxt::T( 'Email Subject' ) . '</label>'
							.		'<div class="cb_field col-sm-9">'
							.			'<div>' . htmlspecialchars( $emailSubject ) . '</div>'
							.		'</div>'
							.	'</div>'
							.	'<div class="form-group cb_form_line clearfix">'
							.		'<label class="control-label col-sm-3">' . CBTxt::T( 'Email Message' ) . '</label>'
							.		'<div class="cb_field col-sm-9">'
							.			'<div>' . htmlspecialchars( $emailBody ) . '</div>'
							.		'</div>'
							.	'</div>'
							.	'<div class="form-group cb_form_line clearfix">'
							.		'<label class="control-label col-sm-3">' . CBTxt::T( 'Email Attachments' ) . '</label>'
							.		'<div class="cb_field col-sm-9">'
							.			'<div>' . htmlspecialchars( $emailAttach ) . '</div>'
							.		'</div>'
							.	'</div>'
							.	'<div class="form-group cb_form_line clearfix">'
							.		'<label class="control-label col-sm-3">' . CBTxt::T( 'From Name' ) . '</label>'
							.		'<div class="cb_field col-sm-9">'
							.			'<div>' . htmlspecialchars( $emailFromName ) . '</div>'
							.		'</div>'
							.	'</div>'
							.	'<div class="form-group cb_form_line clearfix">'
							.		'<label class="control-label col-sm-3">' . CBTxt::T( 'From Email Address' ) . '</label>'
							.		'<div class="cb_field col-sm-9">'
							.			'<div>' . htmlspecialchars( $emailFromAddr ) . '</div>'
							.		'</div>'
							.	'</div>'
							.	'<div class="form-group cb_form_line clearfix">'
							.		'<label class="control-label col-sm-3">' . CBTxt::T( 'Reply-To Name' ) . '</label>'
							.		'<div class="cb_field col-sm-9">'
							.			'<div>' . htmlspecialchars( $emailReplyName ) . '</div>'
							.		'</div>'
							.	'</div>'
							.	'<div class="form-group cb_form_line clearfix">'
							.		'<label class="control-label col-sm-3">' . CBTxt::T( 'Reply-To Email Address' ) . '</label>'
							.		'<div class="cb_field col-sm-9">'
							.			'<div>' . htmlspecialchars( $emailReplyAddr ) . '</div>'
							.		'</div>'
							.	'</div>'
							.	'<h3><a href="' . $_CB_framework->backendViewUrl( 'showusers' ) . '">' . CBTxt::T( 'Click here to go back to User Management' ) . '</a></h3>';
		}

		echo $return;
	}
}