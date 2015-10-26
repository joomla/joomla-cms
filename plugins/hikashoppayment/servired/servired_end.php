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

function ShowError () {
	echo "<html><head><title>Results</title></head><body><table width=100% height=50%><tr><td><p><h2><center>Compruebe que todos los datos del formulario son correctos!!</center></h2></p></td></tr></table></body></html>\n";
} // End of function ShowError

function ShowForm ($amount,$currency,$producto,$id_pedido,$methods,$method_id) {
	$method =& $methods[$method_id];
	$url_tpvv=$method->payment_params->url;
	$merchantName=$method->payment_params->merchantName;
	$clave=$method->payment_params->encriptionKey;
	$code=$method->payment_params->merchantId;
	$terminal=$method->payment_params->terminalId;
	$payment_methods = preg_replace('#[^TRDOC]#','',@$method->payment_params->payment_methods);
	$url_OK=HIKASHOP_LIVE."index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id=$id_pedido";
	$url_KO=HIKASHOP_LIVE."index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id=$id_pedido";
	$transactionType='0';
	$urlMerchant=HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=servired';
	$order= '00'.$id_pedido;

	$language = '0';
	$lang = JFactory::getLanguage();
	$locale=strtoupper(substr($lang->get('tag'),0,2));
	switch($locale){
		case 'es':
			$language = '001';
			break;
		case 'en':
			$language = '002';
			break;
		case 'ca':
			$language = '003';
			break;
		case 'fr':
			$language = '004';
			break;
		case 'de':
			$language = '005';
			break;
		case 'nl':
			$language = '006';
			break;
		case 'it':
			$language = '007';
			break;
		case 'sv':
			$language = '008';
			break;
		case 'pt':
			$language = '009';
			break;
		case 'pl':
			$language = '011';
			break;
		case 'gl':
			$language = '013';
			break;
		default: //default to english for other languages
			$language = '002';
			break;
	}

?>
<script language=JavaScript>
function calc() {
document.getElementById('compra').submit();
}
</script>

<form id="compra" name="compra" action="<?php echo $url_tpvv; ?>" method="post" target="_self">
<pre>
	<input type="hidden" name="Ds_Merchant_Amount" value="<?php echo $amount;?>">
	<input type="hidden" name="Ds_Merchant_Currency" value="<?php echo $currency;?>">
	<input type="hidden" name="Ds_Merchant_Order"  value="<?php echo $order;?>">
	<input type="hidden" name="Ds_Merchant_MerchantCode" value="<?php echo $code;?>">
	<input type="hidden" name="Ds_Merchant_Terminal" value="<?php echo $terminal;?>">
	<input type="hidden" name="Ds_Merchant_TransactionType" value="<?php echo $transactionType;?>">
	<input type="hidden" name="Ds_Merchant_MerchantURL" value="<?php echo $urlMerchant;?>">
	<input type="hidden" name="Ds_Merchant_UrlOK" value="<?php echo $url_OK;?>">
	<input type="hidden" name="Ds_Merchant_UrlKO" value="<?php echo $url_KO;?>">
	<input type="hidden" name="Ds_Merchant_PayMethods" value="<?php echo $payment_methods;?>">
	<input type="hidden" name="Ds_Merchant_ConsumerLanguage" value="<?php echo $language;?>">
<?php
$message = $amount.$order.$code.$currency.$transactionType.$urlMerchant.$clave;
$signature = strtoupper(sha1($message));

?>
	<input type="hidden" name="Ds_Merchant_MerchantSignature" value="<?php echo $signature;?>">
	<center>
		<span class="art-button-wrapper">
			<span class="art-button-l"></span>
			<span class="art-button-r"></span>
			<a class="button art-button" href="javascript:calc()"><img src='/tpvirtual.jpg' border="0" alt="Ir al TPV Virtual"></a>
		</span>
	</center>
</pre>
</form>
<?php
	$doc = JFactory::getDocument();
	$doc->addScriptDeclaration("window.hikashop.ready( function() {document.getElementById('compra').submit();});");
} # End of function ShowForm
?>

<div class="hikashop_servired_end" id="hikashop_servired_end">
	<span id="hikashop_servired_end_message" class="hikashop_servired_end_message">
		<?php echo JText::sprintf('PLEASE_WAIT_BEFORE_REDIRECTION_TO_X',$this->payment_name).'<br/>'. JText::_('CLICK_ON_BUTTON_IF_NOT_REDIRECTED');?>
	</span>
	<span id="hikashop_servired_end_spinner" class="hikashop_servired_end_spinner">
		<img src="<?php echo HIKASHOP_IMAGES.'spinner.gif';?>" />
	</span>
	<br/>
<?php
	if($this->currency->currency_code=='USD'){
		$currency = '840';
	}else{
		$currency = '978';
	}
	ShowForm($this->amount_total,$currency,'mi producto',$this->id_pedido,$this->methods,$this->method_id);
?>
</div>
