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
	$js = '

	if(!hkjQuery)
		hkjQuery = jQuery.noConflict();

	stripe_plugin_is_init_correctly = false;
	try {
		Stripe.setPublishableKey("'.trim($this->payment_params->publishable_key).'");
		stripe_plugin_is_init_correctly = true;
	} catch(e) {
		if(e.message) alert(e.message);
		else alert(e);
	}
	hkjQuery(function($)
	{
		$("#payment-form").submit(function(e)
		{
			var $form = $(this);
			$form.find("button").prop("disabled", true);
			if(!stripe_plugin_is_init_correctly) {
				alert("Please configure the Stripe plugin before.");
				return false;
			}
			Stripe.createToken($form,
				function(status, response)
				{
					var $form = hkjQuery("#payment-form");
					if (response.error)
					{
						alert(response.error.message);
						$form.find(".payment-errors").text(response.error.message);
						$form.find("button").prop("disabled", false);
					}
					else
					{
						var token = response.id;
						$form.append(hkjQuery("<input type=\'hidden\' name=\'stripeToken\' />").val(token));
						$form.get(0).submit();
					}
				}
			);
			return false;
		});
	});
	';

	if (!HIKASHOP_PHP5) {
		$doc =& JFactory::getDocument();
	}else{
		$doc = JFactory::getDocument();
	}
	$imagepath = HIKASHOP_IMAGES.'payment/stripe.png';
	$doc->addScript('https://js.stripe.com/v2/');
	hikashop_loadJsLib('jquery');
	$doc->addScriptDeclaration($js);
?>

<fieldset><legend><?php echo JText::_('STRIPE_FORM').' : '; ?></legend>
<div id="stripeform" style="width:100%; margin:auto; background-color:#F5F5F5; border-radius:10px; border:1px solid #CCCCCC;">
	<div style="width:67px; height:67px; background-image:url(<?php echo $imagepath; ?>); margin:auto; margin-top:5px;"></div>
	<form action="<?php echo $this->notifyurl; ?>" method="POST" id="payment-form">
		<table width="70%" style="margin:auto; margin-bottom:10px;">
			<tbody style="display:block; padding:10px;">
			<tr style="margin-bottom:5px;">
				<td style="text-align:right"><label><?php echo JText::_('CREDIT_CARD_NUMBER').' : '; ?></label></td>
				<td><input style="text-align: center;" value="" autocomplete="off" type="text" size="20" data-stripe="number"></td>
			</tr>
			<tr style="margin-bottom:5px;">
				<td style="text-align:right"><label><?php
					echo JHTML::tooltip(JText::_('CVC_TOOLTIP_TEXT').' : ', JText::_('CVC_TOOLTIP_TITLE'), '', JText::_('CARD_VALIDATION_CODE'));
				?></label></td>
				<td><input style="text-align: center;" value="" type="text" maxlength="4" size="4" data-stripe="cvc"></td>
			</tr>
			<tr style="margin-bottom:5px;">
				<td style="text-align:right"><label><?php
					echo JText::_('EXPIRATION_DATE') . ' ('.JText::_('MM').'/'.JText::_('YY').JText::_('YY').') : ';
				?></label></td>
				<td><input style="text-align: center; width:40%;" maxlength="2" onfocus="this.value='';" value="MM" type="text" size="2" data-stripe="exp-month"> / <input style="text-align: center; width:40%;" maxlength="4" size="4" value="YYYY" onfocus="this.value='';" type="text" data-stripe="exp-year"></td>
			</tr>
			<tr>
				<td></td>
				<td colspan="2"><button type="submit" style="width:220px; height:28px; margin:auto; background: linear-gradient(rgb(69, 177, 232), rgb(48, 151, 222)) repeat scroll 0% 0% rgb(69, 177, 232); border-radius:4px; color:rgb(255,255,255);">Submit Payment</button></td>
			</tr>
			</tbody>
		</table>
	</form>
</div>
</fieldset>
