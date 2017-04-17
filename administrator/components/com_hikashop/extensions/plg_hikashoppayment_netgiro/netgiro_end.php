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

?>

<div class="hikashop_netgiro_end" id="hikashop_netgiro_end">
	<!-- <span id="hikashop_netgiro_end_message" class="hikashop_netgiro_end_message">
		<?php echo JText::sprintf('PLEASE_WAIT_BEFORE_REDIRECTION_TO_X',$this->payment_name).'<br/>'. JText::_('CLICK_ON_BUTTON_IF_NOT_REDIRECTED');?>
	</span>
	<span id="hikashop_netgiro_end_spinner" class="hikashop_netgiro_end_spinner">
		<img src="<?php echo HIKASHOP_IMAGES .'spinner.gif'; ?>" />
	</span> -->

	<div id="netgiro-branding-container"></div>
	<br/>

	<form id="hikashop_netgiro_form" name="hikashop_netgiro_form" action="<?php echo $this->netGiropaymentUrl; ?>" method="post">

		<div id="hikashop_netgiro_end_image" class="hikashop_netgiro_end_image">
			<input id="hikashop_netgiro_button" type="submit" class="btn btn-primary" value="<?php echo JText::_('PAY_NOW');?>" name="" alt="<?php echo JText::_('PAY_NOW');?>" />
		</div>
		<?php
			foreach($this->vars as $name => $value ) {
				$value = htmlspecialchars((string)$value);
				echo "<input type='hidden' name='$name' value='$value' />";
			}
			JRequest::setVar('noform',1); 
		?>
	</form>
</div>

<!-- Client Side Scripts -->
<?php
	if (!version_compare(JVERSION, '3', 'ge') && !JFactory::getApplication()->get('jquery', false)) {
		JFactory::getApplication()->set('jquery',true);
	} 
	else {
		JHtml::_('jquery.framework');
	}

	$document = JFactory::getDocument();
	$document->addScript('plugins/hikashoppayment/netgiro/netgiro.api.js');
?>

<script type="text/javascript">

	var $j = jQuery.noConflict(),
		optIndex = 1,
		isPaymentOptIncluded,
		chosenOption;

	netgiro.branding.options = {
		showP1: <?php echo $this->paymentOptions->showP1; ?>,
		showP2: <?php echo $this->paymentOptions->showP2; ?>,		
		showP3: <?php echo $this->paymentOptions->showP3; ?>,	
	};

	netgiro.branding.init('<?php echo $this->appId; ?>'); 

	$j.each( netgiro.branding.options , function( key, showPaymentOpt ) {
		if(showPaymentOpt) {
			var containerId = "#netgiro-branding-p" + optIndex;
			$j(containerId).prepend('<div style="float: left; vertical-align: middle; padding: 30px 0px 30px 20px;">' +
										'<input type="radio" name="chosenOption" value="'+ optIndex +'">' +
									'</div>');
		}
		optIndex++;
	});

	isPaymentOptIncluded = optIndex !== 1 ? true : false;

	if(isPaymentOptIncluded) {
		$j("input[name='chosenOption']").first().attr('checked','checked');
	}

	$j("#hikashop_netgiro_button").click(function(){
		if(isPaymentOptIncluded) {
			chosenOption = $j("input[name='chosenOption']:checked").val();

			$j('#hikashop_netgiro_form').append('<input type="hidden" name="PaymentOption" value="'+ chosenOption +'">');
		}
	});
</script>
