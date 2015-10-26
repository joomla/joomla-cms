<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php echo JText::sprintf('HI_CUSTOMER',@$data->name)."\n"."\n"; ?>
<?php echo JText::sprintf('THANK_YOU_FOR_REGISTERING',HIKASHOP_LIVE)."\n";?>
<?php if($data->active){
	echo JText::sprintf('ACCOUNT_MUST_BE_ACTIVATED')."\n"."\n"; ?>
	<?php echo $data->activation_url."\n"."\n";
}?>
<?php
$password = false;
if(HIKASHOP_J16){
	jimport('joomla.application.component.helper');
	$usersConfig = JComponentHelper::getParams( 'com_users' );
	if ($usersConfig->get('sendpassword')) {
		$password = true;
	}
}else{
	$password = true;
}
?>
<?php if($password)	echo JText::sprintf('YOU_CAN_LOG_IN_WITH')."\n";?>
<?php echo JText::sprintf('HIKA_USERNAME').' : '.$data->username."\n";?>
<?php if($password)	echo JText::sprintf('HIKA_PASSWORD').' : '.$data->password."\n"."\n"; ?>
<?php if(!empty($data->user_partner_activated)){
	echo JText::sprintf('THANK_YOU_FOR_BECOMING_OUR_PARTNER',$data->user_id,$data->partner_url)."\n"."\n";
}?>
<?php echo str_replace('<br/>',"\n",JText::sprintf('BEST_REGARDS_CUSTOMER',$mail->from_name));?>
