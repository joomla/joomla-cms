<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?>	<script type="text/javascript">
		function addRow(){
			var count = parseInt(document.getElementById('count_warehouse').value);
			document.getElementById('count_warehouse').value=count+1;
			var theTable = document.getElementById('warehouse_listing');
			var oldRow = document.getElementById('warehouse_##');
			var rowData = oldRow.cloneNode(true);
			rowData.id = rowData.id.replace(/##/g,count);
			theTable.appendChild(rowData);
			for (var c = 0,m=oldRow.cells.length;c<m;c++){
				rowData.cells[c].innerHTML = rowData.cells[c].innerHTML.replace(/##/g,count);
			}
<?php 	if(HIKASHOP_BACK_RESPONSIVE) { ?>
			try {
				jQuery('#warehouse_'+count+'_units_input').removeClass("chzn-done").chosen();
			}catch(e){}
<?php 	} ?>
			return false;
		}
	</script>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][merchant_ID]">
				<?php echo JText::_( 'ATOS_MERCHANT_ID' ); ?>
			</label>
		</td>
		<td>
			<input type="text" name="data[shipping][shipping_params][merchant_ID]" value="<?php echo @$this->element->shipping_params->merchant_ID; ?>" />
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][turnaround_time]">
				<?php echo JText::_( 'CANADAPOST_TURNAROUND' ); ?>

			</label>
		</td>
		<td>
			<input type="text" name="data[shipping][shipping_params][turnaround_time]" value="<?php echo @$this->element->shipping_params->turnaround_time; ?>" />
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<fieldset>
				<legend><?php echo JText::_( 'WAREHOUSE' ); ?></legend>
				<div style="text-align:right;">
					<button class="btn" type="button" onclick="return addRow();">
						<img src="<?php echo HIKASHOP_IMAGES; ?>add.png"/><?php echo JText::_('ADD');?>
					</button>
				</div>
				<table class="adminlist table table-striped" cellpadding="1" width="100%" id="warehouse_listing_table">
					<thead>
						<tr>
							<th class="title">
								<?php echo JText::_( 'HIKA_NAME' ); ?>
							</th>
							<th class="title">
								<?php echo JText::_( 'POST_CODE' ); ?>
							</th>
							<th class="title">
								<?php echo JText::_( 'ZONE' ); ?>
							</th>
							<th class="title">
								<?php echo JText::_( 'DELETE_ZONE' ); ?>
							</th>
							<th class="title">
								<?php echo JText::_( 'UNITS' ); ?>
							</th>
							<th class="title">
								<?php echo JText::_( 'HIKA_DELETE' ); ?>
							</th>
						</tr>
					</thead>
					<tbody id="warehouse_listing">
						<?php
						$a = @count($this->element->shipping_params->warehouse);
						if(!$a){ $a++; }
						for($i = 0;$i<$a;$i++){
							$row =@$this->element->shipping_params->warehouse[$i];
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

								<td align="center">
									<span id="warehouse_<?php echo $i;?>_zone">
										<?php if(!empty($row->zone_name)){ echo $row->zone_name;} ?>
										<input type="hidden" name="warehouse[<?php echo $i;?>][zone]" value="<?php echo @$row->zone ?>"/>
									</span>
									<a class="modal" rel="{handler: 'iframe', size: {x: 760, y: 480}}" href="<?php echo hikashop_completeLink("zone&task=selectchildlisting&type=shipping&subtype=warehouse_".$i."_zone&map=warehouse[".$i."][zone]&tmpl=component"); ?>" >
										<img src="<?php echo HIKASHOP_IMAGES; ?>edit.png"/>
									</a>
								</td>
								<td align="center">
									<a href="#" onclick="return deleteZone('warehouse_<?php echo $i;?>_zone');">
										<img src="../media/com_hikashop/images/delete.png"/>
									</a>
								</td>
								<td>
									<select id="warehouse_<?php echo $i;?>_units"  name="warehouse[<?php echo $i;?>][units]">
										<option <?php if(@$row->units=='lb')  echo "selected=\"selected\""; ?> value="lb">LB/IN</option>
										<option <?php if(@$row->units=='kg')  echo "selected=\"selected\""; ?> value="kg">KG/CM</option>
									</select>
								</td>
								<td align="center">
									<a href="#" onclick="return deleteRow('warehouse_<?php echo $i;?>_zip','warehouse_<?php echo $i;?>_zip_input','warehouse_<?php echo $i;?>');">
										<img src="../media/com_hikashop/images/delete.png"/>
									</a>
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
								<input size="10" type="text" id="warehouse_##_name" name="warehouse[##][name]" value="-"/>
							</td>
							<td>
								<div id="warehouse_##_zip">
								<input size="10" type="text" id="warehouse_##_zip_input" name="warehouse[##][zip]" value="-"/>
								</div>
							</td>
							<td align="center">
								<span id="warehouse_##_zone">
									<input type="hidden" name="warehouse[##][zone]" value=""/>
								</span>
								<a class="modal" rel="{handler: 'iframe', size: {x: 760, y: 480}}" href="<?php echo hikashop_completeLink("zone&task=selectchildlisting&type=shipping&subtype=warehouse_##_zone&map=warehouse[##][zone]&tmpl=component"); ?>" onclick="SqueezeBox.fromElement(this,{parse: 'rel'});return false;" >
									<img src="<?php echo HIKASHOP_IMAGES; ?>edit.png"/>
								</a>
							</td>
							<td align="center">
								<a href="#" onclick="return deleteZone('warehouse_##_zone');">
									<img src="../media/com_hikashop/images/delete.png"/>
								</a>
							</td>
							<td>
								<select class="chzn-done" id="warehouse_##_units_input" name="warehouse[##][units]">
									<option value="lb">LB/IN</option>
									<option value="kg">KG/CM</option>
								</select>
							</td>
							<td align="center">
								<a href="#" onclick="return deleteRow('warehouse_##_zip','warehouse_##_zip_input','warehouse_##');">
									<img src="../media/com_hikashop/images/delete.png"/>
								</a>
							</td>
						</tr>
					</table>
				</div>
			</fieldset>
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][services]">
				<?php echo JText::_( 'SHIPPING_SERVICES' ); ?>
			</label>
		</td>
		<td id="shipping_services_list">
			<?php
					echo '<a style="cursor: pointer;" onclick="checkAllBox(\'shipping_services_list\',\'check\');">'.JText::_('SELECT_ALL').'</a> / <a style="cursor: pointer;" onclick="checkAllBox(\'shipping_services_list\',\'uncheck\');">'.JText::_('UNSELECT_ALL').'</a><br/>';
					$i=-1; foreach($this->data['canadapost_methods'] as $method){
					$i++;
					$varName=$method['name'];
				?>
				<input name="data[shipping_methods][<?php echo $varName;?>][name]" type="checkbox" value="<?php echo $varName;?>" <?php echo (isset($this->element->shipping_params->methods[$varName])?'checked="checked"':''); ?>/><?php echo $method['name'].' ('.$method['countries'].')'; ?><br/>
			<?php	} ?>
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][group_package]">
				<?php echo JText::_( 'GROUP_PACKAGE' ); ?>
			</label>
		</td>
		<td>
			<?php echo JHTML::_('hikaselect.booleanlist', "data[shipping][shipping_params][group_package]" , '',@$this->element->shipping_params->group_package	); ?>
		</td>
	</tr>
	<tr>
	<td class="key">
			<label for="data[shipping][shipping_params][readyToShip]">
				<?php echo JText::_( 'CANADAPOST_READYTOSHIP' ); ?>
			</label>
		</td>
		<td>
			<?php echo JHTML::_('hikaselect.booleanlist', "data[shipping][shipping_params][readyToShip]" , '',@$this->element->shipping_params->readyToShip	); ?>
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][weight_approximation]">
				<?php echo JText::_( 'UPS_WEIGHT_APPROXIMATION' ); ?>
			</label>
		</td>
		<td>
			<input size="5" type="text" name="data[shipping][shipping_params][weight_approximation]" value="<?php echo @$this->element->shipping_params->weight_approximation; ?>" />%
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][dim_approximation]">
				<?php echo JText::_( 'DIMENSION_APPROXIMATION' ); ?>
			</label>
		</td>
		<td>
			<input size="5" type="text" name="data[shipping][shipping_params][dim_approximation]" value="<?php echo @$this->element->shipping_params->dim_approximation; ?>" />%
		</td>
	</tr>
