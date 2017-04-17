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
?><div class="hikashop_ceca_end" id="hikashop_ceca_end">
	<span id="hikashop_ceca_end_message" class="hikashop_ceca_end_message">
		<?php echo JText::sprintf('PLEASE_WAIT_BEFORE_REDIRECTION_TO_X',$method->payment_name).'<br/>'. JText::_('CLICK_ON_BUTTON_IF_NOT_REDIRECTED');?>
	</span>
	<span id="hikashop_ceca_end_spinner" class="hikashop_ceca_end_spinner">
		<img src="<?php echo HIKASHOP_IMAGES.'spinner.gif';?>" />
	</span>
	<br/>

	<?php if($method->payment_params->debug){

		$urldestino="http://tpv.ceca.es:8000/cgi-bin/tpv";
	}
	else{
		$urldestino="https://pgw.ceca.es/cgi-bin/tpv";
	}
	?>

	<form id="hikashop_ceca_form" name="hikashop_ceca_form" action="<?php echo $urldestino;?>" method="post">
		<div id="hikashop_ceca_end_image" class="hikashop_ceca_end_image">
			<input id="hikashop_ceca_button" type="submit" class="btn btn-primary" value="<?php echo JText::_('PAY_NOW');?>" name="" alt="<?php echo JText::_('PAY_NOW');?>" />
		</div>
		<?php

			$Clave=$vars["ClaveEncryp"];
			$MerchantID=$vars["MerchantID"];
			$AcquirerBIN=$vars["AcquirerBIN"];
			$TerminalID=$vars["TerminalID"];
			$Num_operacion=$vars["Num_operacion"];
			$Importe=$vars["Importe"];
			$TipoMoneda="978";
			$Exponente="2";
			$Referencia="";
			$Cifrado="SHA1";
			$url_OK=$vars["URL_OK"];
			$url_NOK=$vars["URL_NOK"];
			$Firma = sha1($Clave.$MerchantID.$AcquirerBIN.$TerminalID.$Num_operacion.$Importe.$TipoMoneda.$Exponente.$Referencia.$Cifrado.$url_OK.$url_NOK);


			switch ($vars["Idioma"]) {
				case 'ES':
				    $Idioma = 1;
				    break;
				case 'EN':
				    $Idioma = 6;
				    break;
				case 'DE':
				    $Idioma = 8;
				    break;
				case 'FR':
				    $Idioma = 7;
				    break;
				case 'IT':
				    $Idioma = 10;
				    break;
				case 'NL':
				    $Idioma = 14;
				    break;
				case 'PT':
				    $Idioma = 9;
				    break;
				default :
				    $Idioma = 1;
			}




			$fp = fopen("enviado.txt","a");
			fwrite($fp, "MerchantID: $MerchantID \t " . PHP_EOL);
			fwrite($fp, "AcquirerBIN: $AcquirerBIN \t " . PHP_EOL);
			fwrite($fp, "TerminalID: $TerminalID \t " . PHP_EOL);
			fwrite($fp, "Num_operacion: $Num_operacion \t " . PHP_EOL);
			fwrite($fp, "Importe: $Importe \t " . PHP_EOL);
			fwrite($fp, "TipoMoneda: $TipoMoneda \t " . PHP_EOL);
			fwrite($fp, "Exponente: $Exponente \t " . PHP_EOL);
			fwrite($fp, "Referencia: $Referencia \t $texto" . PHP_EOL);
			fwrite($fp, "Firma: $Firma \t " . PHP_EOL);

			fclose($fp);


		?>


		<input type="hidden" name="MerchantID" value="<?php echo $vars["MerchantID"]; ?>">
		<input type="hidden" name="AcquirerBIN" value="<?php echo $vars["AcquirerBIN"]; ?>">
		<input name="TerminalID" type="hidden" value="<?php echo $vars["TerminalID"]; ?>">
		<input type="hidden" name="URL_OK" value="<?php echo $vars["URL_OK"]; ?>">
		<input type="hidden" name="URL_NOK" value="<?php echo $vars["URL_NOK"]; ?>">
		<input type="hidden" name="Firma" value="<?php echo $Firma; ?>">
		<input type="hidden" name="Cifrado" value="SHA1" />
		<input type="hidden" name="Num_operacion" value="<?php echo $vars["Num_operacion"]; ?>">
		<input type="hidden" name="Importe" value="<?php echo $vars["Importe"]; ?>">
		<input type="hidden" name="TipoMoneda" value="978">
		<input type="hidden" name="Exponente" value="2">
		<input type="hidden" name="Pago_soportado" value=SSL>
		<input type="hidden" name="Idioma" value="<?php echo $Idioma; ?>">

		<?php

			$doc =& JFactory::getDocument();
			$doc->addScriptDeclaration("window.hikashop.ready( function() {document.getElementById('hikashop_ceca_form').submit();});");
			JRequest::setVar('noform',1);
		?>
	</form>
</div>
