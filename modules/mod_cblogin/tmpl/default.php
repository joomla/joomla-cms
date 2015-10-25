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
<form action="<?php echo $_CB_framework->viewUrl( 'login', true, null, 'html', $secureForm ); ?>" method="post" id="login-form" class="cbLoginForm">
	<input type="hidden" name="option" value="com_comprofiler" />
	<input type="hidden" name="view" value="login" />
	<input type="hidden" name="op2" value="login" />
	<input type="hidden" name="return" value="B:<?php echo $loginReturnUrl; ?>" />
	<input type="hidden" name="message" value="<?php echo (int) $params->get( 'login_message', 0 ); ?>" />
	<input type="hidden" name="loginfrom" value="<?php echo htmlspecialchars( ( defined( '_UE_LOGIN_FROM' ) ? _UE_LOGIN_FROM : 'loginmodule' ) ); ?>" />
	<?php echo cbGetSpoofInputTag( 'login' ); ?>
	<?php echo modCBLoginHelper::getPlugins( $params, $type, 'start' ); ?>
	<?php if ( $preLogintText ) { ?>
		<div class="pretext">
			<p><?php echo $preLogintText; ?></p>
		</div>
	<?php } ?>
	<?php echo modCBLoginHelper::getPlugins( $params, $type, 'almostStart' ); ?>
	<?php if ( $loginMethod != 4 ) { ?>
		<fieldset class="userdata">
			<p id="form-login-username">
				<?php if ( in_array( $showUsernameLabel, array( 1, 2, 3, 5 ) ) ) { ?>
					<?php if ( in_array( $showUsernameLabel, array( 2, 5 ) ) ) { ?>
						<span class="<?php echo htmlspecialchars( $templateClass ); ?>">
							<span class="cbModuleUsernameIcon fa fa-user" title="<?php echo htmlspecialchars( $userNameText ); ?>"></span>
						</span>
					<?php } else { ?>
						<label for="modlgn-username">
							<?php if ( $showUsernameLabel == 3 ) { ?>
								<span class="<?php echo htmlspecialchars( $templateClass ); ?>">
									<span class="cbModuleUsernameIcon fa fa-user" title="<?php echo htmlspecialchars( $userNameText ); ?>"></span>
								</span>
							<?php } ?>
							<?php if ( in_array( $showUsernameLabel, array( 1, 3 ) ) ) { ?>
								<?php echo htmlspecialchars( $userNameText ); ?>
							<?php } ?>
						</label>
					<?php } ?>
				<?php } ?>
				<input id="modlgn-username" type="text" name="username" class="inputbox"  size="<?php echo $usernameInputLength; ?>"<?php echo ( in_array( $showUsernameLabel, array( 4, 5 ) ) ? ' placeholder="' . htmlspecialchars( $userNameText ) . '"' : null ); ?> />
			</p>
			<p id="form-login-password">
				<?php if ( in_array( $showPasswordLabel, array( 1, 2, 3, 5 ) ) ) { ?>
					<?php if ( in_array( $showPasswordLabel, array( 2, 5 ) ) ) { ?>
						<span class="<?php echo htmlspecialchars( $templateClass ); ?>">
							<span class="cbModulePasswordIcon fa fa-lock" title="<?php echo htmlspecialchars( CBTxt::T( 'Password' ) ); ?>"></span>
						</span>
					<?php } else { ?>
						<label for="modlgn-passwd">
							<?php if ( $showPasswordLabel == 3 ) { ?>
								<span class="<?php echo htmlspecialchars( $templateClass ); ?>">
									<span class="cbModulePasswordIcon fa fa-lock" title="<?php echo htmlspecialchars( CBTxt::T( 'Password' ) ); ?>"></span>
								</span>
							<?php } ?>
							<?php if ( in_array( $showPasswordLabel, array( 1, 3 ) ) ) { ?>
								<?php echo htmlspecialchars( CBTxt::T( 'Password' ) ); ?>
							<?php } ?>
						</label>
					<?php } ?>
				<?php } ?>
				<input id="modlgn-passwd" type="password" name="passwd" class="inputbox" size="<?php echo $passwordInputLength; ?>"<?php echo ( in_array( $showPasswordLabel, array( 4, 5 ) ) ? ' placeholder="' . htmlspecialchars( CBTxt::T( 'Password' ) ) . '"' : null ); ?>  />
			</p>
			<?php if ( count( $twoFactorMethods ) > 1 ) { ?>
				<p id="form-login-secretkey">
					<?php if ( in_array( $showSecretKeyLabel, array( 1, 2, 3, 5 ) ) ) { ?>
						<?php if ( in_array( $showSecretKeyLabel, array( 2, 5 ) ) ) { ?>
							<span class="<?php echo htmlspecialchars( $templateClass ); ?>">
								<span class="cbModuleSecretKeyIcon fa fa-star" title="<?php echo htmlspecialchars( CBTxt::T( 'Secret Key' ) ); ?>"></span>
							</span>
						<?php } else { ?>
							<label for="modlgn-secretkey">
								<?php if ( $showSecretKeyLabel == 3 ) { ?>
									<span class="<?php echo htmlspecialchars( $templateClass ); ?>">
										<span class="cbModuleSecretKeyIcon fa fa-star" title="<?php echo htmlspecialchars( CBTxt::T( 'Secret Key' ) ); ?>"></span>
									</span>
								<?php } ?>
								<?php if ( in_array( $showSecretKeyLabel, array( 1, 3 ) ) ) { ?>
									<?php echo htmlspecialchars( CBTxt::T( 'Secret Key' ) ); ?>
								<?php } ?>
							</label>
						<?php } ?>
					<?php } ?>
					<input id="modlgn-secretkey" type="text" name="secretkey" class="inputbox" size="<?php echo $secretKeyInputLength; ?>"<?php echo ( in_array( $showSecretKeyLabel, array( 4, 5 ) ) ? ' placeholder="' . htmlspecialchars( CBTxt::T( 'Secret Key' ) ) . '"' : null ); ?>  />
				</p>
			<?php } ?>
			<?php if ( in_array( $showRememberMe, array( 1, 3 ) ) ) { ?>
				<p id="form-login-remember">
					<label for="modlgn-remember"><?php echo htmlspecialchars( CBTxt::T( 'Remember Me' ) ); ?></label>
					<input id="modlgn-remember" type="checkbox" name="remember" class="inputbox" value="yes"<?php echo ( $showRememberMe == 3 ? ' checked="checked"' : null ); ?> />
				</p>
			<?php } elseif ( $showRememberMe == 2 ) { ?>
				<input id="modlgn-remember" type="hidden" name="remember" class="inputbox" value="yes" />
			<?php } ?>
			<?php echo modCBLoginHelper::getPlugins( $params, $type, 'beforeButton', 'p' ); ?>
			<button type="submit" name="Submit" class="button"<?php echo $buttonStyle; ?>>
				<?php if ( in_array( $showButton, array( 1, 2, 3 ) ) ) { ?>
					<span class="<?php echo htmlspecialchars( $templateClass ); ?>">
						<span class="cbModuleLoginIcon fa fa-sign-in" title="<?php echo htmlspecialchars( CBTxt::T( 'Log in' ) ); ?>"></span>
					</span>
				<?php } ?>
				<?php if ( in_array( $showButton, array( 0, 1, 4 ) ) ) { ?>
					<?php echo htmlspecialchars( CBTxt::T( 'Log in' ) ); ?>
				<?php } ?>
			</button>
			<?php echo modCBLoginHelper::getPlugins( $params, $type, 'afterButton', 'p' ); ?>
		</fieldset>
	<?php } else { ?>
		<?php echo modCBLoginHelper::getPlugins( $params, $type, 'beforeButton', 'p' ); ?>
		<?php echo modCBLoginHelper::getPlugins( $params, $type, 'afterButton', 'p' ); ?>
	<?php } ?>
	<?php if ( $showForgotLogin || $showRegister ) { ?>
		<ul id="form-login-links">
			<?php if ( $showForgotLogin ) { ?>
				<li id="form-login-forgot">
					<a href="<?php echo $_CB_framework->viewUrl( 'lostpassword', true, null, 'html', $secureForm ); ?>">
						<?php if ( in_array( $showForgotLogin, array( 2, 3 ) ) ) { ?>
							<span class="<?php echo htmlspecialchars( $templateClass ); ?>">
								<span class="cbModuleForgotLoginIcon fa fa-unlock-alt" title="<?php echo htmlspecialchars( CBTxt::T( 'Forgot Login?' ) ); ?>"></span>
							</span>
						<?php } ?>
						<?php if ( in_array( $showForgotLogin, array( 1, 3 ) ) ) { ?>
							<?php echo CBTxt::T( 'Forgot Login?' ); ?>
						<?php } ?>
					</a>
				</li>
			<?php } ?>
			<?php if ( $showRegister ) { ?>
				<li id="form-login-register">
					<a href="<?php echo $_CB_framework->viewUrl( 'registers', true, null, 'html', $secureForm ); ?>">
						<?php if ( in_array( $params->get( 'show_newaccount', 1 ), array( 2, 3 ) ) ) { ?>
							<span class="<?php echo htmlspecialchars( $templateClass ); ?>">
								<span class="cbModuleRegisterIcon fa fa-edit" title="<?php echo htmlspecialchars( CBTxt::T( 'UE_REGISTER', 'Sign up' ) ); ?>"></span>
							</span>
						<?php } ?>
						<?php if ( in_array( $params->get( 'show_newaccount', 1 ), array( 1, 3 ) ) ) { ?>
							<?php echo CBTxt::T( 'UE_REGISTER', 'Sign up' ); ?>
						<?php } ?>
					</a>
				</li>
			<?php } ?>
		</ul>
	<?php } ?>
	<?php echo modCBLoginHelper::getPlugins( $params, $type, 'almostEnd' ); ?>
	<?php if ( $postLoginText ) { ?>
		<div class="posttext">
			<p><?php echo $postLoginText; ?></p>
		</div>
	<?php } ?>
	<?php echo modCBLoginHelper::getPlugins( $params, $type, 'end' ); ?>
</form>
<?php echo modCBLoginHelper::getPlugins( $params, $type, 'afterForm' ); ?>