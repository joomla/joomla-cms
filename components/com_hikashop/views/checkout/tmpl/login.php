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
if(!$this->identified){
	$this->nextButton = false;
	$params = null; $js = null;
	$classLogin = "hikashop_hidden_checkout";
	$classRegistration = "";
	$defaultSelection = '2';
	$span='';

	$display_login = $this->config->get('display_login',1);

	$sr = explode(',', $this->simplified_registration);
	$this->registration = array(0=>false,1=>false,2=>false,3=>false);
	foreach($sr as $r) {
		$this->registration[ (int)$r ] = true;
	}
	$registration_count = count($sr);

	if($display_login){
		$classLogin = '';
		$classRegistration = 'hikashop_hidden_checkout';
		$defaultSelection = 'login';
	}

	if($this->display_method == 0) {
?>
	<!-- CLASSIC DISPLAY-->
<?php
		if($display_login) {
 			if($this->registration[2]){
 				echo '<h1>'.JText::_('LOGIN_OR_GUEST').'</h1>';
			} else {
				echo '<h1>'.JText::_('LOGIN_OR_REGISTER_ACCOUNT').'</h1>';
			}
		}
?>
	<div id="hikashop_checkout_login" class="hikashop_checkout_login row-fluid">
	<?php
		if($display_login) {
	?>
		<div id="hikashop_checkout_login_left_part" class="hikashop_checkout_login_left_part span4">
			<fieldset class="input">
				<h2><?php echo JText::_('HIKA_LOGIN');?></h2>
				<?php echo $this->loadTemplate('form');	?>
			</fieldset>
		</div>
	<?php } ?>
		<div id="hikashop_checkout_login_right_part" class="hikashop_checkout_login_right_part span8">
			<fieldset class="input">
				<h2><?php
					if($this->registration[2]) {
						echo JText::_('GUEST');
					} else {
						echo JText::_('HIKA_REGISTRATION');
					}
				?></h2>
<?php
			$usersConfig = JComponentHelper::getParams( 'com_users' );
			$allowRegistration = $usersConfig->get('allowUserRegistration');
			if($allowRegistration || $this->registration[2]){
				echo hikashop_getLayout('user','registration',$params,$js);
			}else{
				echo JText::_('REGISTRATION_NOT_ALLOWED');
			}
?>
			</fieldset>
		</div>
	</div>
	<input type="hidden" id="login_view_action" name="login_view_action" value="" />
	<div style="clear:both"></div><br/>
<?php

	}else{

		if($display_login && ($this->registration[0] || $this->registration[1] || $this->registration[3]) && $this->registration[2])
			echo '<h1>'.JText::_('LOGIN_OR_REGISTER_ACCOUNT_OR_GUEST').'</h1>';
		else if($display_login && ($this->registration[0] || $this->registration[1] || $this->registration[3]))
			echo '<h1>'.JText::_('LOGIN_OR_REGISTER_ACCOUNT').'</h1>';
		else if($display_login && $this->registration[2])
			echo '<h1>'.JText::_('LOGIN_OR_GUEST').'</h1>';
		else if(!$display_login && ($this->registration[0] || $this->registration[1] || $this->registration[3]) && $this->registration[2])
			echo '<h1>'.JText::_('REGISTER_ACCOUNT_OR_GUEST').'</h1>';

?>
	<!-- THIS IS THE SWITCHER DISPLAY, RADIO BUTTON ON THE LEFT, FORMS ON THE RIGHT-->
	<div id="hikashop_checkout_login" class="hikashop_checkout_login row-fluid">
<?php
		if($this->display_method == 1 && (($display_login && $registration_count > 0) || $registration_count > 1)) {
			$span='span8';
?>
		<div id="hikashop_checkout_login_left_part" class="hikashop_checkout_login_left_part span4">
			<fieldset class="input">
				<h2><?php echo JText::_('IDENTIFICATION');?></h2>
<?php
	$values = array();
	$v = null;
	if($display_login) {
		$v = JHTML::_('select.option', 'login', JText::_('HIKA_LOGIN').'<br/>');
		$v->class = 'hikabtn-checkout-login';
		$values[] = $v;
	}
	if($this->registration[0]){
		$v = JHTML::_('select.option', 0, JText::_('HIKA_REGISTRATION').'<br/>');
		$v->class = 'hikabtn-checkout-registration';
		$values[] = $v;
	}
	if($this->registration[1]){
		$v = JHTML::_('select.option', 1, JText::_('HIKA_REGISTRATION').'<br/>');
		$v->class = 'hikabtn-checkout-simplified';
		$values[] = $v;
	}
	if($this->registration[3]){
		$v = JHTML::_('select.option', 3, JText::_('HIKA_REGISTRATION').'<br/>');
		$v->class = 'hikabtn-checkout-simplified-pwd';
		$values[] = $v;
	}
	if($this->registration[2]){
		$v = JHTML::_('select.option', 2, JText::_('GUEST').'<br/>');
		$v->class = 'hikabtn-checkout-guest';
		$values[] = $v;
	}
	$defaultSelection = $this->config->get('default_registration_view','login');
	if(empty($defaultSelection)){
		$defaultSelection = 'login';
	}

	$js = "
	window.hikashop.ready( function(){
		var currentRegistrationSelection = window.document.getElementById('data_register_registration_method".$defaultSelection."');
		if(!currentRegistrationSelection) currentRegistrationSelection = window.document.getElementById('data[register][registration_method]".$defaultSelection."');
		displayRegistration(currentRegistrationSelection);
	});";
	$doc = JFactory::getDocument();
	$doc->addScriptDeclaration("\n<!--\n".$js."\n//-->\n");
	echo JHTML::_('hikaselect.radiolist',  $values, 'data[register][registration_method]', ' onchange="displayRegistration(this)"', 'value', 'text', $defaultSelection, false, false, true );
?>
			</fieldset>
		</div>
		<?php } ?>
		<div id="hikashop_checkout_login_right_part" class="hikashop_checkout_login_right_part <?php echo $span; ?>">
			<div id="hikashop_checkout_registration" class="<?php echo $classRegistration; ?>">
				<fieldset class="input">
					<h2 id="hika_registration_type"><?php
						if($this->registration[2] && $registration_count == 1) {
					 		echo JText::_('GUEST');
						} else {
							echo JText::_('HIKA_REGISTRATION');
						}
					?></h2>
<?php
	$usersConfig = JComponentHelper::getParams( 'com_users' );
	$allowRegistration = $usersConfig->get('allowUserRegistration');
	if ($allowRegistration || $this->registration[2]) {
		echo hikashop_getLayout('user','registration',$params,$js);
	} else {
		echo JText::_('REGISTRATION_NOT_ALLOWED');
	}
?>
				</fieldset>
			</div>
<?php if($display_login){ ?>
			<div id="hikashop_checkout_login_form" class=" <?php echo $classLogin; ?>">
				<fieldset class="input">
					<h2><?php echo JText::_('HIKA_LOGIN');?></h2>
					<?php echo $this->loadTemplate('form');	?>
				</fieldset>
			</div>
<?php } ?>
		</div>
	</div>
	<input type="hidden" id="login_view_action" name="login_view_action" value="" />
	<div style="clear:both"></div><br/>
<?php }
}?>
