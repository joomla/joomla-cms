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
class plgHikashoppaymentCreditcard extends hikashopPaymentPlugin
{
	var $multiple = true;
	var $name = 'creditcard';
	var $pluginConfig = array(
		'order_status' => array('ORDER_STATUS', 'orderstatus'),
		'status_notif_email' => array('ORDER_STATUS_NOTIFICATION', 'boolean','0'),
		'ask_ccv' => array('CARD_VALIDATION_CODE', 'boolean','1'),
		'ask_owner' => array('CREDIT_CARD_OWNER', 'boolean','0'),
		'ask_cctype' => array('CARD_TYPE', 'big-textarea'),
		'information' => array('CREDITCARD_INFORMATION', 'big-textarea')
	);


	function needCC(&$method) {
		if(@$_GET['option']=='com_hikashop'&&@$_GET['ctrl']=='order'&&@$_GET['task']=='pay') return false;
		$method->ask_cc=true;
		$method->ask_ccv = @$method->payment_params->ask_ccv;
		$method->ask_owner = @$method->payment_params->ask_owner;
		$method->ask_cctype = @$method->payment_params->ask_cctype;
		if(!empty($method->ask_cctype)){
			$types = explode(',',$method->ask_cctype);
			$method->ask_cctype = array();
			foreach($types as $type){
				$method->ask_cctype[$type]=$type;
			}
		}
		return true;
	}


	function onBeforeOrderCreate(&$order,&$do){
		if(parent::onBeforeOrderCreate($order, $do) === true)
			return true;

		$this->ccLoad();

		if($order->order_payment_method=='creditcard'){
			$order->credit_card_info = $this;

			$obj = new stdClass();
			$obj->cc_number=substr($this->cc_number,0,8);
			$obj->cc_month=$this->cc_month;
			$obj->cc_year=$this->cc_year;
			$obj->cc_type=@$this->cc_type;

			$history = new stdClass();
			$history->type = 'credit card';
			$history->notified = 0;
			$history->data = base64_encode(serialize($obj));

			$this->modifyOrder($order,$this->payment_params->order_status,$history,false);
		}
	}

	function onHistoryDisplay(&$histories){
		foreach($histories as $k => $history){
			if($history->history_payment_method == $this->name && !empty($history->history_data)){
				$data = unserialize(base64_decode($history->history_data));
				$string='';
				if(!empty($data->cc_type)){
					$string.= JText::_('CARD_TYPE').': '.$data->cc_type.'<br />';
				}
				$string.= JText::_('DATE').': '.$data->cc_month.'/'.$data->cc_year.'<br />';
				$string.= JText::_('BEGINNING_OF_CREDIT_CARD_NUMBER').': '.$data->cc_number.'<br />';
				$string.='<a href="'.hikashop_completeLink('order&task=remove_history_data&history_id='.$history->history_id).'"><img src="'.HIKASHOP_IMAGES.'delete.png" /></a>';
				$histories[$k]->history_data = $string;
				static $done = false;
				if(!$done){
					$done = true;
					$app = JFactory::getApplication();
					$app->enqueueMessage(JText::_('CREDITCARD_WARNING'));
				}
			}
		}
	}

	function onAfterOrderConfirm(&$order,&$methods,$method_id){

		$method =& $methods[$method_id];

		if($order->order_status != $method->payment_params->order_status)
			$this->modifyOrder($order->order_id, $method->payment_params->order_status, @$method->payment_params->status_notif_email, false);

		$this->removeCart = true;

		$this->information = $method->payment_params->information;
		if(preg_match('#^[a-z0-9_]*$#i',$this->information)){
			$this->information = JText::_($this->information);
		}

		return $this->showPage('end');

	}

	function getPaymentDefaultValues(&$element) {
		$element->payment_name='Credit card';
		$element->payment_description='You can pay by credit card.';
		$element->payment_images='MasterCard,VISA,Credit_card,American_Express';

		$element->payment_params->information='We will now process the credit card transaction and contact you when completed.';
		$element->payment_params->order_status='created';
		$element->payment_params->ask_ccv = true;
		$element->payment_params->ask_owner = false;
		$element->payment_params->ask_cctype = '';
	}
}
