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
defined('_JEXEC') or die('Restricted access');
?><?php
class plgHikashoppaymentCeca extends JPlugin
{
	var $accepted_currencies = array(
		'EUR'
	);
	var $debugData = array();
	function onPaymentDisplay(&$order,&$methods,&$usable_methods){
		if(!empty($methods)){
			foreach($methods as $method){
			if($method->payment_type!='ceca' || !$method->enabled){
				continue;
			}

			if(!empty($method->payment_zone_namekey)){
				$zoneClass=hikashop_get('class.zone');
					$zones = $zoneClass->getOrderZones($order);
				if(!in_array($method->payment_zone_namekey,$zones)){
					return true;
				}
			}


			$currencyClass = hikashop_get('class.currency');
			$null=null;
			$currency_id = intval(@$order->total->prices[0]->price_currency_id);
			$currency = $currencyClass->getCurrencies($currency_id,$null);
			if(!empty($currency) && !in_array(@$currency[$currency_id]->currency_code,$this->accepted_currencies)){
				return true;
			}
			$usable_methods[$method->ordering]=$method;
			}
		}
		return true;
	}

	function onPaymentSave(&$cart,&$rates,&$payment_id){
		$usable = array();
		$this->onPaymentDisplay($cart,$rates,$usable);
		$payment_id = (int) $payment_id;
		foreach($usable as $usable_method){
			if($usable_method->payment_id==$payment_id){
				return $usable_method;
			}
		}

		return false;
	}

	function onAfterOrderConfirm(&$order,&$methods,$method_id){
		$method =& $methods[$method_id];
		$tax_total = '';
		$discount_total = '';
		$currencyClass = hikashop_get('class.currency');
		$currencies=null;
		$currencies = $currencyClass->getCurrencies($order->order_currency_id,$currencies);
		$currency=$currencies[$order->order_currency_id];
		$user = hikashop_loadUser(true);



		$app = JFactory::getApplication();
		$cart = hikashop_get('class.cart');

		if(!HIKASHOP_J30)
			JHTML::_('behavior.mootools');
		else
			JHTML::_('behavior.framework');
		$app =& JFactory::getApplication();
		$name = $method->payment_type.'_end.php';
			$path = JPATH_THEMES.DS.$app->getTemplate().DS.'hikashoppayment'.DS.$name;
			if(!file_exists($path)){
				if(version_compare(JVERSION,'1.6','<')){
					$path = JPATH_PLUGINS .DS.'hikashoppayment'.DS.$name;
				}else{
					$path = JPATH_PLUGINS .DS.'hikashoppayment'.DS.$method->payment_type.DS.$name;
				}
				if(!file_exists($path)){
					return true;
				}
			}

		$vars["Num_operacion"]=$order->order_id;
		$vars["Importe"] = round($order->cart->full_total->prices[0]->price_value_with_tax, (int)$currency->currency_locale['int_frac_digits']) * 100;
		$vars["MerchantID"]=$method->payment_params->merchant_id;
		$vars["AcquirerBIN"]=$method->payment_params->acquirer_bin;
		$vars["TerminalID"]=$method->payment_params->terminal_id;
		$vars["ClaveEncryp"]=$method->payment_params->clave_encryp;

		$lang = JFactory::getLanguage();
		$locale=strtoupper(substr($lang->get('tag'),0,2));
		if(!in_array($locale,array('EN', 'DE', 'ES', 'FR', 'IT', 'NL', 'PT'))) $locale = 'ES';
		$vars["Idioma"]=$locale;

		$vars["URL_OK"] = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=ceca&done=ok&order_id='.$order->order_id.'&lang='.strtolower($locale);
		$vars["URL_NOK"] = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=ceca&done=nok&order_id='.$order->order_id.'&lang='.strtolower($locale);



		require($path);

		$this->removeCart = true;

		return true;
	}

	function onPaymentNotification(&$statuses){
		$pluginsClass = hikashop_get('class.plugins');
		$elements = $pluginsClass->getMethods('payment','ceca');

		if(empty($elements)) return false;

		$element = reset($elements);

		$done = JRequest::getString("done");
		$requestOrderId = JRequest::getString("order_id");
		$fp = fopen("recibido.txt","a");

		if ($done == "ok")
		{
			$msg = JText::_('ORDER_IS_COMPLETE').'<br/>'.
			JText::_('THANK_YOU_FOR_PURCHASE');

			return $msg;



		}
		elseif ($done == "nok")
		{
			$msg = JText::_('TRANSACTION_PROCESSING_ERROR').'<br/>';
			return $msg;
		}
		else
		{
			if ($element->payment_params->comunicacion_online_ok)
			{
				$vars = array();
				$data = array();
				$filter = JFilterInput::getInstance();



				fwrite($fp, "VARIABLES QUE RECIBO DEL POST \t " . PHP_EOL);
				foreach($_POST as $key => $value){
					$key = $filter->clean($key);
					$value = JRequest::getString($key);
					$vars[$key]=$value;
					fwrite($fp, "Clave y valor: $key \t $value" . PHP_EOL);
				}




				$Clave=$element->payment_params->clave_encryp;
				$MerchantID=$element->payment_params->merchant_id;
				$AcquirerBIN=$element->payment_params->acquirer_bin;
				$TerminalID=$element->payment_params->terminal_id;
				$Num_operacion=$vars["Num_operacion"];
				$Importe=$vars['Importe'];
				$TipoMoneda=$vars["TipoMoneda"];
				$Exponente=$vars["Exponente"];
				$Referencia=$vars["Referencia"];

				$Firma = sha1($Clave.$MerchantID.$AcquirerBIN.$TerminalID.$Num_operacion.$Importe.$TipoMoneda.$Exponente.$Referencia);


					fwrite($fp, "VARIABLES QUE USO PARA FIRMAR \t " . PHP_EOL);
					fwrite($fp, "Clave: $Clave \t " . PHP_EOL);
					fwrite($fp, "MerchantID: $MerchantID \t " . PHP_EOL);
					fwrite($fp, "AcquirerBIN: $AcquirerBIN \t " . PHP_EOL);
					fwrite($fp, "TerminalID: $TerminalID \t " . PHP_EOL);
					fwrite($fp, "Num_operacion: $Num_operacion \t " . PHP_EOL);
					fwrite($fp, "Importe: $Importe \t " . PHP_EOL);
					fwrite($fp, "TipoMoneda: $TipoMoneda \t " . PHP_EOL);
					fwrite($fp, "Exponente: $Exponente \t " . PHP_EOL);
					fwrite($fp, "Referencia: $Referencia \t $texto" . PHP_EOL);
					fwrite($fp, "Firma: $Firma \t " . PHP_EOL);


				$validaFirma = false;
				if ($Firma==$vars["Firma"])
				{



					fwrite($fp, "ES BUENA $Firma \t ". PHP_EOL);
					$validaFirma = true;



				}
				else
				{
					fwrite($fp, "ES MALA: $Firma \t " . PHP_EOL);
					$validaFirma = false;


				}

				$validaPrecio = false;
				$orderClass = hikashop_get('class.order');
				$dbOrder = $orderClass->get((int)@$vars['Num_operacion']);

				$currencyClass = hikashop_get('class.currency');
				$currencies=null;
				$currencies = $currencyClass->getCurrencies($dbOrder->order_currency_id,$currencies);
				$currency=$currencies[$dbOrder->order_currency_id];


				$ImporteEntrada=(int)@$vars['Importe'];
				$ImporteBD = round($dbOrder->order_full_price, (int)$currency->currency_locale['int_frac_digits']) * 100;




				if ($ImporteEntrada == $ImporteBD){
					$validaPrecio = true;

				}
				else
				{
					$validaPrecio = false;

				}


				if ($validaFirma && $validaPrecio)
				{

					$order = new stdClass();
					$order->order_id = @$dbOrder->order_id;
					if(!empty($dbOrder)){
						$order->old_status->order_status=$dbOrder->order_status;

						fwrite($fp, "CAMBIANDO ESTADOS \t " . PHP_EOL);
						fwrite($fp, "ORDER ID: $order->order_id \t " . PHP_EOL);

					}



					$order->history->history_reason=JText::sprintf('AUTOMATIC_PAYMENT_NOTIFICATION');
					$order->history->history_notified=0;
					$order->history->history_amount=@$vars['Importe'];
					$order->history->history_payment_id = $element->payment_id;
					$order->history->history_payment_method =$element->payment_type;
					$order->history->history_data = ob_get_clean();
					$order->history->history_type = 'payment';

					$order->order_status = $element->payment_params->verified_status;

					fwrite($fp, "Verificado: $order->order_status \t " . PHP_EOL);



					$orderClass->save($order);

				}


				if ($element->payment_params->respuesta_requerida)
				{
					$respuesta= '$*$OKY$*$';
					return $respuesta;


				}



				fclose($fp);
			}
		}



		return true;
	}

	function onPaymentConfiguration(&$element){

		$this->ceca = JRequest::getCmd('name','ceca');
		if(empty($element)){
			$element = new stdClass();
				$element->payment_name='CECA';
				$element->payment_description='Puede pagar con tarjeta con este método de pago';
				$element->payment_images='MasterCard,VISA,Credit_card,American_Express';
				$element->payment_type=$this->ceca;
				$element->payment_params= new stdClass();
				$list=null;

				$element->payment_params->verified_status='confirmed';
				$element = array($element);

			}
			$lang = &JFactory::getLanguage();
		$locale=strtoupper(substr($lang->get('tag'),0,2));
		$key = key($element);
		$element[$key]->payment_params->status_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=ceca&lang='.strtolower($locale);
		$this->toolbar = array(
			'save',
			'apply',
			'cancel',
			'|',
			array('name' => 'pophelp', 'target' =>'payment-bluepaid-form')
		);

		hikashop_setTitle('Ceca','plugin','plugins&plugin_type=payment&task=edit&name='.$this->ceca);
		$app = JFactory::getApplication();
		$app->setUserState( HIKASHOP_COMPONENT.'.payment_plugin_type', $this->ceca);
		$this->address = hikashop_get('type.address');
		$this->category = hikashop_get('type.categorysub');
		$this->category->type = 'status';

	}

	function onPaymentConfigurationSave(&$element){

		return true;
	}




}
