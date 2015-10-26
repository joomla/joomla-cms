<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><script type="text/javascript">
function ups_addRow() {
	var count = parseInt(document.getElementById('count_warehouse').value);
	document.getElementById('count_warehouse').value = count + 1;
	var theTable = document.getElementById('warehouse_listing');
	var oldRow = document.getElementById('warehouse_##');
	var rowData = oldRow.cloneNode(true);
	rowData.id = rowData.id.replace(/##/g,count);
	theTable.appendChild(rowData);
	for(var c = 0,m=oldRow.cells.length;c<m;c++){
		rowData.cells[c].innerHTML = rowData.cells[c].innerHTML.replace(/##/g,count);
	}
<?php if(HIKASHOP_BACK_RESPONSIVE) { ?>
	try {
		jQuery('#warehouse_'+count+'_units_input').removeClass("chzn-done").chosen();
		jQuery('#warehouse_'+count+'_currency_input').removeClass("chzn-done").chosen();
		jQuery('#warehouse_'+count+'_country_input').removeClass("chzn-done").chosen();
	}catch(e){}
<?php } ?>
	return false;
}
</script>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][access_code]"><?php
				echo JText::_( 'UPS_ACCESS_CODE' );
			?></label>
		</td>
		<td>
			<input type="text" name="data[shipping][shipping_params][access_code]" value="<?php echo @$this->element->shipping_params->access_code; ?>" />
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][user_id]"><?php
				echo JText::_( 'UPS_USER_ID' );
			?></label>
		</td>
		<td>
			<input type="text" name="data[shipping][shipping_params][user_id]" value="<?php echo @$this->element->shipping_params->user_id; ?>" />
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][password]"><?php
				echo JText::_( 'HIKA_PASSWORD' );
			?></label>
		</td>
		<td>
			<input type="text" name="data[shipping][shipping_params][password]" value="<?php echo @$this->element->shipping_params->password; ?>" />
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][shipper_number]"><?php
				echo JText::_( 'SHIPPER_NUMBER' );
			?></label>
		</td>
		<td>
			<input type="text" name="data[shipping][shipping_params][shipper_number]" value="<?php echo @$this->element->shipping_params->shipper_number; ?>" />
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][pickup_type]"><?php
				echo JText::_( 'PICKUP_TYPE' );
			?></label>
		</td>
		<td><?php
			$arr = array(
				JHTML::_('select.option', '01', JText::_('Daily Pickup') ),
				JHTML::_('select.option', '03', JText::_('Customer Counter') ),
				JHTML::_('select.option', '06', JText::_('One Time Pickup') ),
				JHTML::_('select.option', '07', JText::_('On Call Air') ),
				JHTML::_('select.option', '19', JText::_('Letter Center') ),
				JHTML::_('select.option', '20', JText::_('Air Service Center') ),
			);
			echo JHTML::_('hikaselect.genericlist', $arr, "data[shipping][shipping_params][pickup_type]", 'class="inputbox" size="1"', 'value', 'text', @$this->element->shipping_params->pickup_type);
		?></td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][destination_type]"><?php
				echo JText::_( 'DESTINATION_TYPE' );
			?></label>
		</td>
		<td><?php
			$arr = array(
				JHTML::_('select.option', 'auto', JText::_('AUTO_DETERMINATION') ),
				JHTML::_('select.option', 'res', JText::_('RESIDENTIAL_ADDRESS') ),
				JHTML::_('select.option', 'com', JText::_('COMMERCIAL_ADDRESS') ),
			);
			echo JHTML::_('hikaselect.genericlist', $arr, "data[shipping][shipping_params][destination_type]", 'class="inputbox" size="1"', 'value', 'text', @$this->element->shipping_params->destination_type);
		?></td>
	</tr>
</table>
</fieldset>
<fieldset>
	<legend><?php echo JText::_( 'WAREHOUSE' ); ?></legend>
	<div style="text-align:right;">
		<button class="btn" type="button" onclick="return ups_addRow();">
			<img src="<?php echo HIKASHOP_IMAGES; ?>add.png"/><?php echo JText::_('ADD');?>
		</button>
	</div>
	<table class="adminlist table table-striped" cellpadding="1" width="100%" id="warehouse_listing_table">
		<thead>
			<tr>
				<th class="title"><?php echo JText::_( 'HIKA_NAME' ); ?></th>
				<th class="title"><?php echo JText::_( 'POST_CODE' ); ?></th>
				<th class="title"><?php echo JText::_( 'STATEPROVINCE_CODE' ); ?></th>
				<th class="title"><?php echo JText::_( 'CITY' ); ?></th>
				<th class="title"><?php echo JText::_( 'COUNTRY' ); ?></th>
				<th class="title"><?php echo JText::_( 'ZONE' ); ?></th>
				<th class="title"><?php echo JText::_( 'UNITS' ); ?></th>
				<th class="title"><?php echo JText::_( 'CURRENCY' ); ?></th>
				<th class="title"><?php echo JText::_( 'HIKA_DELETE' ); ?></th>
			</tr>
		</thead>
		<tbody id="warehouse_listing">
<?php
	$country=hikashop_get('type.country');
	$a = @count($this->element->shipping_params->warehouse);
	if(!$a){ $a++; }
	for($i = 0; $i < $a; $i++) {
		$row = @$this->element->shipping_params->warehouse[$i];
?>
			<tr class="row0" id="warehouse_<?php echo $i;?>">
				<td>
					<input size="10" type="text" id="warehouse_<?php echo $i;?>_name" name="warehouse[<?php echo $i;?>][name]" value="<?php echo @$row->name; ?>"/>
				</td>
				<td>
					<div id="warehouse_<?php echo $i;?>_zip">
					<input size="10" type="text" id="warehouse_<?php echo $i;?>_zip_input" name="warehouse[<?php echo $i;?>][zip]" value="<?php echo @$row->zip; ?>"/>
					</div>
				</td>
				<td>
					<div id="warehouse_<?php echo $i;?>_statecode">
					<input size="10" type="text" id="warehouse_<?php echo $i;?>_statecode_input" name="warehouse[<?php echo $i;?>][statecode]" value="<?php echo @$row->statecode; ?>"/>
					</div>
				</td>
				<td>
					<div id="warehouse_<?php echo $i;?>_city">
					<input size="10" type="text" id="warehouse_<?php echo $i;?>_city_input" name="warehouse[<?php echo $i;?>][city]" value="<?php echo @$row->city; ?>"/>
					</div>
				</td>
				<td>
					<?php $countryList=$country->display("warehouse[$i][country]", @$row->country, false , "style='width:100px;'"); echo $countryList; ?>
				</td>
				<td align="center">
					<span id="warehouse_<?php echo $i;?>_zone">
						<?php if(!empty($row->zone_name)){ echo $row->zone_name;} ?>
						<input type="hidden" name="warehouse[<?php echo $i;?>][zone]" value="<?php echo @$row->zone ?>"/>
					</span>
					<a class="modal" rel="{handler: 'iframe', size: {x: 760, y: 480}}" href="<?php echo hikashop_completeLink("zone&task=selectchildlisting&type=shipping&subtype=warehouse_".$i."_zone&map=warehouse[".$i."][zone]&tmpl=component"); ?>" ><img src="<?php echo HIKASHOP_IMAGES; ?>edit.png"/></a>
					<a href="#" onclick="return deleteZone('warehouse_<?php echo $i;?>_zone');"><img src="<?php echo HIKASHOP_IMAGES; ?>delete.png"/></a>
				</td>
				<td>
					<select id="warehouse_<?php echo $i;?>_units"  name="warehouse[<?php echo $i;?>][units]">
						<option <?php if(@$row->units=='lb')  echo "selected=\"selected\""; ?> value="lb">LB/IN</option>
						<option <?php if(@$row->units=='kg')  echo "selected=\"selected\""; ?> value="kg">KG/CM</option>
					</select>
				</td>
				<td><?php
					$currency=hikashop_get('type.currency');
					$currencyList=$currency->display("warehouse[$i][currency]", @$row->currency, 'id="warehouse_'.$i.'_currency"  name="warehouse['.$i.'][currency]"');
					echo $currencyList;
				?></td>
				<td align="center">
					<a href="#" onclick="return deleteRow('warehouse_<?php echo $i;?>_zip','warehouse_<?php echo $i;?>_zip_input','warehouse_<?php echo $i;?>');"><img src="<?php echo HIKASHOP_IMAGES; ?>delete.png"/></a>
				</td>
			</tr>
<?php
	}
?>
		</tbody>
	</table>
	<input type="hidden" name="count_warehouse" value="<?php echo $a;?>" id="count_warehouse" />
	<div style="display:none">
		<table class="adminlist table table-striped" cellpadding="1" width="100%" id="warehouse_listing_table_row">
			<tr class="row0" id="warehouse_##">
				<td>
					<input size="10" type="text" id="warehouse_##_name" name="warehouse[##][name]" value=""/>
				</td>
				<td>
					<div id="warehouse_##_zip">
					<input size="10" type="text" id="warehouse_##_zip_input" name="warehouse[##][zip]" value=""/>
					</div>
				</td>
				<td>
					<div id="warehouse_##_statecode">
					<input size="10" type="text" id="warehouse_##_statecode_input" name="warehouse[##][statecode]" value=""/>
					</div>
				</td>
				<td>
					<div id="warehouse_##_city">
					<input size="10" type="text" id="warehouse_##_city_input" name="warehouse[##][city]" value=""/>
					</div>
				</td>
				<td>
					<?php $countryList=$country->display("warehouse[##][country]", '', false , 'style="width:100px;" class="chzn-done"','warehouse_##_country_input'); echo $countryList; ?>
				</td>
				<td align="center">
					<span id="warehouse_##_zone">
						<input type="hidden" name="warehouse[##][zone]" value=""/>
					</span>
					<a class="modal" rel="{handler: 'iframe', size: {x: 760, y: 480}}" href="<?php echo hikashop_completeLink("zone&task=selectchildlisting&type=shipping&subtype=warehouse_##_zone&map=warehouse[##][zone]&tmpl=component"); ?>" onclick="SqueezeBox.fromElement(this,{parse: 'rel'});return false;" ><img src="<?php echo HIKASHOP_IMAGES; ?>edit.png"/></a>
					<a href="#" onclick="return deleteZone('warehouse_##_zone');"><img src="<?php echo HIKASHOP_IMAGES; ?>delete.png"/></a>
				</td>
				<td>
					<select class="chzn-done" id="warehouse_##_units_input" name="warehouse[##][units]">
						<option value="lb">LB/IN</option>
						<option value="kg">KG/CM</option>
					</select>
				</td>
				<td><?php
					$currency=hikashop_get('type.currency');
					$currencyList=$currency->display("warehouse[##][currency]", '', 'name="warehouse[##][curency]" class="chzn-done"','warehouse_##_currency_input');
					echo $currencyList;
				?></td>
				<td align="center">
					<a href="#" onclick="return deleteRow('warehouse_##_zip','warehouse_##_zip_input','warehouse_##');"><img src="<?php echo HIKASHOP_IMAGES; ?>delete.png"/></a>
				</td>
			</tr>
		</table>
	</div>
</fieldset>
<fieldset>
<table class="admintable table">
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][services]"><?php
				echo JText::_( 'SHIPPING_SERVICES' );
			?></label>
		</td>
		<td id="shipping_services_list"><?php
			echo '<a style="cursor: pointer;" onclick="checkAllBox(\'shipping_services_list\',\'check\');">'.JText::_('SELECT_ALL').'</a> / <a style="cursor: pointer;" onclick="checkAllBox(\'shipping_services_list\',\'uncheck\');">'.JText::_('UNSELECT_ALL').'</a><br/>';
			$i=-1;
			foreach($this->data['ups_methods'] as $method){
				$i++;
				$varName=strtolower($method['name']);
				$varName=str_replace(' ','_', $varName);
			?><input id="data_shipping_ups_<?php echo $varName;?>" name="data[shipping_methods][<?php echo $varName;?>][name]" type="checkbox" value="<?php echo $varName;?>" <?php echo (!empty($this->element->shipping_params->methods[$varName])?'checked="checked"':''); ?>/><label for="data_shipping_ups_<?php echo $varName;?>"><?php echo $method['name'].' ('.$method['countries'].')'; ?></label><br/>
<?php
			}
?>
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][negotiated_rate]"><?php
				echo JText::_('NEGOTIATED_RATE');
			?></label>
		</td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', "data[shipping][shipping_params][negotiated_rate]" , '', @$this->element->shipping_params->negotiated_rate);
		?></td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][group_package]"><?php
				echo JText::_('GROUP_PACKAGE');
			?></label>
		</td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', "data[shipping][shipping_params][group_package]" , '', @$this->element->shipping_params->group_package);
		?></td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][show_eta]"><?php
				echo JText::_('FEDEX_SHOW_ETA');
			?></label>
		</td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', "data[shipping][shipping_params][show_eta]" , '', @$this->element->shipping_params->show_eta);
		?></td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][include_price]"><?php
				echo JText::_('INCLUDE_PRICE');
			?></label>
		</td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', "data[shipping][shipping_params][include_price]" , '', @$this->element->shipping_params->include_price);
		?></td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][exclude_dimensions]"><?php
				echo JText::_('EXCLUDE_DIMENSIONS');
			?></label>
		</td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', "data[shipping][shipping_params][exclude_dimensions]" , '', @$this->element->shipping_params->exclude_dimensions);
		?></td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][weight_approximation]"><?php
				echo JText::_('UPS_WEIGHT_APPROXIMATION');
			?></label>
		</td>
		<td>
			<input size="5" type="text" name="data[shipping][shipping_params][weight_approximation]" value="<?php echo @$this->element->shipping_params->weight_approximation; ?>" />%
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][dim_approximation]"><?php
				echo JText::_('DIMENSION_APPROXIMATION');
			?></label>
		</td>
		<td>
			<input size="5" type="text" name="data[shipping][shipping_params][dim_approximation]" value="<?php echo @$this->element->shipping_params->dim_approximation; ?>" />%
		</td>
	</tr>
		<tr>
	<td class="key">
		<label for="data[shipping][shipping_params][debug]"><?php
			echo JText::_('DEBUG');
		?></label>
	</td>
	<td><?php
		echo JHTML::_('hikaselect.booleanlist', "data[shipping][shipping_params][debug]" , '', @$this->element->shipping_params->debug);
	?></td>
</tr>
</fieldset>
