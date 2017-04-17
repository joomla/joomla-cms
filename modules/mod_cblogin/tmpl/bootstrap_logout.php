<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

JHtml::_( 'behavior.keepalive' );

?>
<?php echo modCBLoginHelper::getPlugins( $params, $type, 'beforeForm' ); ?>
<form action="<?php echo $_CB_framework->viewUrl( 'logout', true, null, 'html', $secureForm ); ?>" method="post" id="login-form" class="form-vertical cbLogoutForm">
	<input type="hidden" name="option" value="com_comprofiler" />
	<input type="hidden" name="view" value="logout" />
	<input type="hidden" name="op2" value="logout" />
	<input type="hidden" name="return" value="B:<?php echo $logoutReturnUrl; ?>" />
	<input type="hidden" name="message" value="<?php echo (int) $params->get( 'logout_message', 0 ); ?>" />
	<?php echo cbGetSpoofInputTag( 'logout' ); ?>
	<?php echo modCBLoginHelper::getPlugins( $params, $type, 'start' ); ?>
	<?php if ( $preLogoutText ) { ?>
		<div class="pretext">
			<p><?php echo $preLogoutText; ?></p>
		</div>
	<?php } ?>
	<?php echo modCBLoginHelper::getPlugins( $params, $type, 'almostStart' ); ?>
	<?php if ( (int) $params->get( 'greeting', 1 ) ) { ?>
		<div class="login-greeting">
			<p><?php echo $greetingText; ?></p>
		</div>
	<?php } ?>
	<?php if ( (int) $params->get( 'show_avatar', 1 ) ) { ?>
		<div class="login-avatar">
			<p><?php echo $cbUser->getField( 'avatar', null, 'html', 'none', 'list', 0, true ); ?></p>
		</div>
	<?php } ?>
	<?php echo modCBLoginHelper::getPlugins( $params, $type, 'beforeButton', 'p' ); ?>
	<div class="logout-button">
		<button type="submit" name="Submit" class="btn btn-primary"<?php echo $buttonStyle; ?>>
			<?php if ( in_array( $showButton, array( 1, 2, 3 ) ) ) { ?>
				<span class="<?php echo htmlspecialchars( $templateClass ); ?>">
					<span class="cbModuleLogoutIcon fa fa-sign-out" title="<?php echo htmlspecialchars( CBTxt::T( 'Log out' ) ); ?>"></span>
				</span>
			<?php } ?>
			<?php if ( in_array( $showButton, array( 0, 1, 4 ) ) ) { ?>
				<?php echo htmlspecialchars( CBTxt::T( 'Log out' ) ); ?>
			<?php } ?>
		</button>
	</div>
	<?php echo modCBLoginHelper::getPlugins( $params, $type, 'afterButton', 'p' ); ?>
	<?php if ( $profileViewText || $profileEditText || $showPrivateMessages || $showConnectionRequests ) { ?>
		<p>
			<ul class="unstyled logout-links">
				<?php if ( $showPrivateMessages ) { ?>
					<li class="logout-private-messages">
						<a href="<?php echo $privateMessageURL; ?>">
							<?php if ( $params->get( 'show_pms_icon', 0 ) ) { ?>
								<span class="<?php echo htmlspecialchars( $templateClass ); ?>">
									<span class="cbModulePMIcon fa fa-envelope" title="<?php echo htmlspecialchars( CBTxt::T( 'Private Messages' ) ); ?>"></span>
								</span>
							<?php } ?>
							<?php if ( $newMessageCount ) { ?>
								<?php echo ( $newMessageCount == 1 ? CBTxt::T( 'YOU_HAVE_COUNT_NEW_PRIVATE_MESSAGE', 'You have [count] new private message.', array( '[count]' => $newMessageCount ) ) : CBTxt::T( 'YOU_HAVE_COUNT_NEW_PRIVATE_MESSAGES', 'You have [count] new private messages.', array( '[count]' => $newMessageCount ) ) ); ?>
							<?php } else { ?>
								<?php echo CBTxt::T( 'You have no new private messages.' ); ?>
							<?php } ?>
						</a>
					</li>
				<?php } ?>
				<?php if ( $showConnectionRequests ) { ?>
					<li class="logout-connection-requests">
						<a href="<?php echo $_CB_framework->viewUrl( 'manageconnections' ); ?>">
							<?php if ( $params->get( 'show_connection_notifications_icon', 0 ) ) { ?>
								<span class="<?php echo htmlspecialchars( $templateClass ); ?>">
									<span class="cbModuleConnectionsIcon fa fa-users" title="<?php echo htmlspecialchars( CBTxt::T( 'Connections' ) ); ?>"></span>
								</span>
							<?php } ?>
							<?php if ( $newConnectionRequests ) { ?>
								<?php echo ( $newConnectionRequests == 1 ? CBTxt::T( 'YOU_HAVE_COUNT_NEW_CONNECTION_REQUEST', 'You have [count] new connection request.', array( '[count]' => $newConnectionRequests ) ) : CBTxt::T( 'YOU_HAVE_COUNT_NEW_CONNECTION_REQUESTS', 'You have [count] new connection requests.', array( '[count]' => $newConnectionRequests ) ) ); ?>
							<?php } else { ?>
								<?php echo CBTxt::T( 'You have no new connection requests.' ); ?>
							<?php } ?>
						</a>
					</li>
				<?php } ?>
				<?php if ( $profileViewText ) { ?>
					<li class="logout-profile">
						<a href="<?php echo $_CB_framework->userProfileUrl(); ?>">
							<?php if ( $params->get( 'icon_show_profile', 0 ) ) { ?>
								<span class="<?php echo htmlspecialchars( $templateClass ); ?>">
									<span class="cbModuleProfileViewIcon fa fa-user" title="<?php echo htmlspecialchars( $profileViewText ); ?>"></span>
								</span>
							<?php } ?>
							<?php echo $profileViewText; ?>
						</a>
					</li>
				<?php } ?>
				<?php if ( $profileEditText ) { ?>
					<li class="logout-profile-edit">
						<a href="<?php echo $_CB_framework->userProfileEditUrl(); ?>">
							<?php if ( $params->get( 'icon_edit_profile', 0 ) ) { ?>
								<span class="<?php echo htmlspecialchars( $templateClass ); ?>">
									<span class="cbModuleProfileEditIcon fa fa-pencil" title="<?php echo htmlspecialchars( $profileEditText ); ?>"></span>
								</span>
							<?php } ?>
							<?php echo $profileEditText; ?>
						</a>
					</li>
				<?php } ?>
			</ul>
		</p>
	<?php } ?>
	<?php echo modCBLoginHelper::getPlugins( $params, $type, 'almostEnd' ); ?>
	<?php if ( $postLogoutText ) { ?>
		<div class="posttext">
			<p><?php echo $postLogoutText; ?></p>
		</div>
	<?php } ?>
	<?php echo modCBLoginHelper::getPlugins( $params, $type, 'end' ); ?>
</form>
<?php echo modCBLoginHelper::getPlugins( $params, $type, 'afterForm' ); ?>