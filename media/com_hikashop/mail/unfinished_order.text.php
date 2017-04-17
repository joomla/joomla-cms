<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php echo JText::sprintf('HI_CUSTOMER',@$data->name);?>


<?php echo JText::sprintf('THANK_YOU_FOR_REGISTERING',HIKASHOP_LIVE);?>
<?php if($data->active){
	echo JText::sprintf('ACCOUNT_MUST_BE_ACTIVATED'); ?>


	<?php echo $data->activation_url;
}?>


<?php echo JText::sprintf('YOU_CAN_LOG_IN_WITH');?>

<?php echo JText::sprintf('HIKA_USERNAME').' : '.$data->username;?>

<?php echo JText::sprintf('HIKA_PASSWORD').' : '.$data->password;?>

<?php if(!empty($data->user_partner_activated)){
	echo JText::sprintf('THANK_YOU_FOR_BECOMING_OUR_PARTNER',$data->id,$data->partner_url);
}?>

<?php echo str_replace('<br/>',"\n",JText::sprintf('BEST_REGARDS_CUSTOMER',$mail->from_name));?>
