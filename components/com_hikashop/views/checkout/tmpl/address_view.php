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
$same_address = false;
if($this->shipping_address == $this->billing_address){
	$same_address = true;
}

if(!empty($this->addresses)) {
?>
<table class="table">
<?php
	$varname = $this->type.'_address';
	if(empty($this->$varname) || !isset($this->addresses[$this->$varname])){
		$first = reset($this->addresses);
		$this->$varname = $first->address_id;
		$app = JFactory::getApplication();
		$app->setUserState(HIKASHOP_COMPONENT.'.'.$varname,0 );
	}
	$done = false;
	$nb_addresses = count($this->addresses);
	$k = 0;
	foreach($this->addresses as $address){
		$this->address =& $address;
		$checked = '';
		$class = '';
		if(($this->$varname == $address->address_id) || (empty($address->address_id) && !$done)){
			$checked = 'checked="checked"';
			$done = true;
		}
		if($this->config->get('auto_submit_methods',1) && empty($checked)) {
			$checked.=' onclick="this.form.submit(); return false;"';
		}
?>
	<tr class="row<?php echo $k.$class; ?>">
<?php if($nb_addresses > 1) { ?>
		<td>
			<input id="hikashop_checkout_<?php echo $this->type;?>_address_radio_<?php echo $address->address_id;?>" class="hikashop_checkout_<?php echo $this->type;?>_address_radio" type="radio" name="hikashop_address_<?php echo $this->type;?>" value="<?php echo $address->address_id;?>" <?php echo $checked; ?> />
		</td>
<?php } ?>
		<td>
<?php if($nb_addresses == 1 ) { ?>
			<input id="hikashop_checkout_<?php echo $this->type;?>_address_radio_<?php echo $address->address_id;?>" type="hidden" name="hikashop_address_<?php echo $this->type;?>" value="<?php echo $address->address_id;?>" />
<?php } else { ?>
			<label for="hikashop_checkout_<?php echo $this->type;?>_address_radio_<?php echo $address->address_id;?>" style="cursor:pointer;">
<?php } ?>
			<span class="hikashop_checkout_<?php echo $this->type;?>_address_info">
<?php
		$addressClass = hikashop_get('class.address');
		echo $addressClass->displayAddress($this->fields,$address,'address');
?>
			</span>
<?php if($nb_addresses > 1) { ?>
			</label>
<?php } ?>
		</td>
		<td>
			<span class="hikashop_checkout_<?php echo $this->type;?>_address_buttons">
<?php if($nb_addresses>1){ ?>
					<a onclick="if(!confirm('<?php echo JText::_('HIKASHOP_CONFIRM_DELETE_ADDRESS', true); ?>')){return false;}else{return true;}" title="<?php echo JText::_('HIKA_DELETE'); ?>" class="hikashop_checkout_<?php echo $this->type;?>_address_delete" href="<?php echo hikashop_completeLink('checkout&step='.$this->step.'&redirect=checkout&task=deleteaddress&address_id='.$address->address_id.'&'.hikashop_getFormToken().'=1'.$this->url_itemid);?>"><img alt="<?php echo JText::_('HIKA_DELETE'); ?>" src="<?php echo HIKASHOP_IMAGES; ?>delete.png" border="0" /></a>
<?php } ?>
				<a id="hikashop_checkout_<?php echo $this->type;?>_address_edit_<?php echo $address->address_id; ?>" title="<?php echo JText::_('HIKA_EDIT'); ?>" class="hikashop_checkout_<?php echo $this->type;?>_address_edit" rel="{handler: 'iframe', size: {x: 450, y: 480}}" href="<?php echo hikashop_completeLink('address&task=edit&redirect=checkout&address_id='.$address->address_id.'&step='.$this->step.'&type='.$this->type.$this->url_itemid,true);?>" onclick="return hikashopEditAddress(this,<?php echo (int)$same_address?>,false);"><img alt="<?php echo JText::_('HIKA_EDIT'); ?>" src="<?php echo HIKASHOP_IMAGES; ?>edit.png" border="0" /></a>
			</span>
		</td>
	</tr>
<?php
		$k = 1-$k;
	}
?>
</table>
<?php
}else{
	if(!JRequest::getVar( HIKASHOP_COMPONENT.'.address_error')){
		JRequest::setVar( HIKASHOP_COMPONENT.'.address_error',1);
		$this->app->enqueueMessage( JText::_('CREATE_OR_SELECT_ADDRESS') );
	}
	$js='
window.hikashop.ready( function(){
	var link = document.getElementById("hikashop_checkout_'. $this->type.'_address_new_link");
	if(link) return hikashopEditAddress(link,'.(int)$same_address.',true);
});';
	$this->doc->addScriptDeclaration("\n<!--\n".$js."\n//-->\n");
}
?>
<span id="hikashop_checkout_<?php echo $this->type;?>_address_new" class="hikashop_checkout_<?php echo $this->type;?>_address_new">
<?php
	$html = $this->cart->displayButton(JText::_('HIKA_NEW'),'new',$this->params,'','var link = document.getElementById(\'hikashop_checkout_'. $this->type.'_address_new_link\'); if(link) return hikashopEditAddress(link,'.(int)$same_address.',true); return false;');
	if(strpos($html,'<a') !== false) echo $html;
?>
	<a id="hikashop_checkout_<?php echo $this->type;?>_address_new_link" rel="{handler: 'iframe', size: {x: 450, y: 480}}" href="<?php echo hikashop_completeLink('address&redirect=checkout&task=add&step='.$this->step.'&type='.$this->type.$this->url_itemid,true);?>" onclick="return hikashopEditAddress(this,<?php echo (int)$same_address; ?>,true);">
		<?php if(strpos($html,'<a')===false) echo $html; ?>
	</a>
</span>
