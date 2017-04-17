<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
if(JPluginHelper::isEnabled('authentication', 'openid')) {
	$lang = &JFactory::getLanguage();
	$lang->load( 'plg_authentication_openid', JPATH_ADMINISTRATOR );
	$langScript = 	'var JLanguage = {};'.
		' JLanguage.WHAT_IS_OPENID = \''.JText::_( 'WHAT_IS_OPENID' ).'\';'.
		' JLanguage.LOGIN_WITH_OPENID = \''.JText::_( 'LOGIN_WITH_OPENID' ).'\';'.
		' JLanguage.NORMAL_LOGIN = \''.JText::_( 'NORMAL_LOGIN' ).'\';'.
		' var comlogin = 1;';
	$document = &JFactory::getDocument();
	$document->addScriptDeclaration( $langScript );
	JHTML::_('script', 'openid.js');
}
if(!HIKASHOP_RESPONSIVE) {
?>

	<p id="com-form-login-username">
		<label for="username"><?php echo JText::_('HIKA_USERNAME') ?></label><br />
		<input name="username" id="username" type="text" class="inputbox" alt="username" size="18" />
	</p>
	<p id="com-form-login-password">
		<label for="passwd"><?php echo JText::_('HIKA_PASSWORD') ?></label><br />
		<input type="password" id="passwd" name="passwd" class="inputbox" size="18" alt="password" />
	</p>
	<?php if(JPluginHelper::isEnabled('system', 'remember')) : ?>
	<p id="com-form-login-remember">
		<label for="remember"><?php echo JText::_('HIKA_REMEMBER_ME') ?></label>
		<input type="checkbox" id="remember" name="remember" value="yes" alt="Remember Me" />
	</p>
	<?php endif; ?>
	<?php
		echo $this->cart->displayButton(JText::_('HIKA_LOGIN'),'login',@$this->params,'',' hikashopSubmitForm(\'hikashop_checkout_form\', \'login\'); return false;');
		$button = $this->config->get('button_style','normal');
	 	if ($button=='css')
			echo '<input type="submit" style="position: absolute; left: -9999px; width: 1px; height: 1px;"/></input>';
	?>
<?php
if(!HIKASHOP_J16){
	$reset = 'index.php?option=com_user&view=reset';
	$remind = 'index.php?option=com_user&view=remind';
}else{
	$reset = 'index.php?option=com_users&view=reset';
	$remind = 'index.php?option=com_users&view=remind';
}
?>
<ul>
	<li>
		<a href="<?php echo JRoute::_( $reset ); ?>">
		<?php echo JText::_('HIKA_FORGOT_YOUR_PASSWORD'); ?></a>
	</li>
	<li>
		<a href="<?php echo JRoute::_( $remind ); ?>">
		<?php echo JText::_('HIKA_FORGOT_YOUR_USERNAME'); ?></a>
	</li>
</ul>
<?php } else { ?>
<div class="userdata form-inline">
	<div id="form-login-username" class="control-group">
		<div class="controls">
			<div class="input-prepend input-append">
				<span class="add-on">
					<i class="icon-user tip" title="<?php echo JText::_('HIKA_USERNAME'); ?>"></i>
					<label for="modlgn-username" class="element-invisible"><?php echo JText::_('HIKA_USERNAME'); ?></label>
				</span>
				<input id="modlgn-username" type="text" name="username" class="input-small" tabindex="1" size="18" placeholder="<?php echo JText::_('HIKA_USERNAME'); ?>" />
				<a href="<?php echo JRoute::_('index.php?option=com_users&view=remind');?>" class="btn hasTooltip" title="<?php echo JText::_('HIKA_FORGOT_YOUR_USERNAME'); ?>"><i class="icon-question-sign"></i></a>
			</div>
		</div>
	</div>
	<div id="form-login-password" class="control-group">
		<div class="controls">
			<div class="input-prepend input-append">
				<span class="add-on">
					<i class="icon-lock tip" title="<?php echo JText::_('HIKA_PASSWORD') ?>"></i>
					<label for="modlgn-passwd" class="element-invisible"><?php echo JText::_('HIKA_PASSWORD') ?></label>
				</span>
				<input id="modlgn-passwd" type="password" name="passwd" class="input-small" tabindex="2" size="18" placeholder="<?php echo JText::_('HIKA_PASSWORD') ?>" />
				<a href="<?php echo JRoute::_('index.php?option=com_users&view=reset');?>" class="btn hasTooltip" title="<?php echo JText::_('HIKA_FORGOT_YOUR_PASSWORD'); ?>"><i class="icon-question-sign"></i></a>
			</div>
		</div>
	</div>
<?php if(JPluginHelper::isEnabled('system', 'remember')) { ?>
	<div id="form-login-remember" class="control-group checkbox">
		<label for="modlgn-remember" class="control-label"><?php echo JText::_('HIKA_REMEMBER_ME') ?></label>
		<input id="modlgn-remember" type="checkbox" name="remember" value="yes"/>
	</div>
<?php } ?>
	<div id="form-login-submit" class="control-group">
		<div class="controls">
			<?php echo $this->cart->displayButton(JText::_('HIKA_LOGIN'), 'login', @$this->params, '',' var b = document.getElementById(\'login_view_action\'); if(b) { b.value = \'login\'; } document.hikashop_checkout_form.submit(); return false;','', 0, 1, 'btn btn-primary'); ?>
		</div>
	</div>
</div>
<?php } ?>
