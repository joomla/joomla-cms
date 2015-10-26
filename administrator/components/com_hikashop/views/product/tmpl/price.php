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
function addPriceRow(){
<?php if(HIKASHOP_BACK_RESPONSIVE) { ?>
	if(jQuery && jQuery('#priceprice_currency_id___chzn'))
		jQuery('#priceprice_currency_id___chzn').remove();
<?php } ?>
	var d = document, count = parseInt(d.getElementById('count_price').value);
	d.getElementById('count_price').value=count+1;
	var theTable = d.getElementById('price_listing'), oldRow = d.getElementById('price_##'), rowData = oldRow.cloneNode(true);
	rowData.id = rowData.id.replace(/##/g,count);
	theTable.appendChild(rowData);
	for (var c = 0,m=oldRow.cells.length;c<m;c++){
		rowData.cells[c].innerHTML = rowData.cells[c].innerHTML.replace(/##/g,count);
	}
<?php if(HIKASHOP_BACK_RESPONSIVE) { ?>
	try {
		jQuery('#priceprice_currency_id'+count).removeClass("chzn-done").chosen();
	}catch(e){}
<?php } ?>
	return false;
}
</script>
<div style="float:right">
	<button class="btn" type="button" onclick="return addPriceRow();">
		<img src="<?php echo HIKASHOP_IMAGES; ?>add.png"/><?php echo JText::_('ADD');?>
	</button>
</div>
<br/>
<?php
$site_id_title = '';
$site_id_value = '';
if(file_exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites'.DS.'helpers'.DS.'utils.php')){
	include_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites'.DS.'helpers'.DS.'utils.php');
	if ( class_exists( 'MultisitesHelperUtils') && method_exists( 'MultisitesHelperUtils', 'getComboSiteIDs')) {
		$comboSiteIDs = str_replace('class="inputbox"','class="inputbox chzn-done"',MultisitesHelperUtils::getComboSiteIDs( '', 'price[price_site_id][##]', JText::_( 'SELECT_A_SITE')));
		if( !empty( $comboSiteIDs)){
			$site_id_title = '<th class="title">'. JText::_( 'SITE_ID' ).'</th>';
			$site_id_value = '<td>'. $comboSiteIDs.'</td>';
		}
	}
}
$hideTaxedPriceColumn = '';
if($this->config->get('floating_tax_prices',0)){
	$hideTaxedPriceColumn = ' style="display:none;"';
}
?>
<table id="hikashop_product_price_table" class="adminlist table table-striped table-hover" cellpadding="1" width="100%" id="price_listing_table">
	<thead>
		<tr>
			<th class="title">
				<?php echo JText::_( 'PRICE' ); ?>
			</th>
			<th class="title"<?php echo $hideTaxedPriceColumn; ?>>
				<?php echo JText::_( 'PRICE_WITH_TAX' ); ?>
			</th>
			<th class="title">
				<?php echo JText::_( 'CURRENCY' ); ?>
			</th>
			<th class="title">
				<?php echo JText::_( 'MINIMUM_QUANTITY' ); ?>
			</th>
			<?php if(hikashop_level(2)){ ?>
			<th class="title">
				<?php echo JText::_( 'ACCESS_LEVEL' ); ?>
			</th>
			<?php }
			echo $site_id_title; ?>
			<th class="title">
				<?php echo JText::_('ID'); ?>
			</th>
			<th class="title">
			</th>
		</tr>
	</thead>
	<tbody id="price_listing">
	<?php
		$a = @count($this->element->prices);
		if($a){
			for($i = 0;$i<$a;$i++){
				$row =& $this->element->prices[$i];
					if(empty($row->price_min_quantity)){
						$row->price_min_quantity = 1;
					}
				?>
				<tr class="row0" id="price_<?php echo $i;?>">
					<td>
						<input size="10" type="text" id="price[price_value][<?php echo $i;?>]" name="price[price_value][<?php echo $i;?>]" value="<?php echo @$row->price_value; ?>" onchange="updatePrice('price_with_tax_<?php echo $i;?>',this.value,this.form['data[product][product_tax_id]'].value,0);" />
					</td>
					<td<?php echo $hideTaxedPriceColumn; ?>>
						<input size="10" type="text" id="price_with_tax_<?php echo $i;?>" name="price_with_tax_<?php echo $i;?>" value="<?php echo @$row->price_value_with_tax; ?>" onchange="updatePrice('price[price_value][<?php echo $i;?>]',this.value,this.form['data[product][product_tax_id]'].value,1);"/>
					</td>
					<td>
						<?php echo @$this->currency->display('price[price_currency_id]['.$i.']',@$row->price_currency_id); ?>
					</td>
					<td>
						<input size="3" type="text" id="price[price_min_quantity][<?php echo $i;?>]" name="price[price_min_quantity][<?php echo $i;?>]" value="<?php echo @$row->price_min_quantity; ?>" />
					</td>
					<?php if(hikashop_level(2)){ ?>
					<td>
						<?php if(!empty($row->price_id)){ ?>
						<?php
							echo $this->popup->display(
								'<img src="'. HIKASHOP_IMAGES.'icons/icon-16-levels.png" title="'.JText::_('ACCESS_LEVEL').'" />',
								'ACCESS_LEVEL',
								'\''.hikashop_completeLink('product&task=priceaccess&id='.$i,true).'&access=\'+document.getElementById(\'price_access_'.$i.'\').value',
								'price_'.$i.'_acl',
								760, 480, '', '', 'link',true
							);
						?>
						<input type="hidden" id="price_access_<?php echo $i;?>" name="price[price_access][<?php echo $i;?>]" value="<?php echo @$row->price_access; ?>" />
						<?php }else{echo '--';}?>
					</td>
					<?php }
					if(!empty($site_id_value)){
						echo '<td>'.str_replace('class="inputbox"','class="inputbox chzn-done"',MultisitesHelperUtils::getComboSiteIDs( @$row->price_site_id, 'price[price_site_id]['.$i.']', JText::_( 'SELECT_A_SITE'))).'</td>';
					}; ?>
					<td>
						<?php
						if(!empty($row->price_id)){
							echo $row->price_id. '<input type="hidden" id="price[price_id]['.$i.']" name="price[price_id]['.$i.']" value="'.$row->price_id.'" />';
						}else{
							echo '--';
						} ?>
					</td>
					<td>
						<a href="#" onclick="hikashop.deleteRow(this); return false;"><img src="<?php echo HIKASHOP_IMAGES; ?>delete2.png" alt="<?php echo JText::_('HIKA_DELETE'); ?>"></a>
					</td>
				</tr>
			<?php
			}
		}

		?>
	</tbody>
</table>
<?php
if(!in_array($this->element->product_type,array('main','template'))){ ?>
	<table class="admintable table" width="100%">
		<tr>
			<td class="key">
				<?php echo JText::_( 'MAIN_PRICE_OVERRIDE' ); ?>
			</td>
			<td>
				<input type="text" name="data[product][product_price_percentage]" value="<?php echo $this->escape(@$this->element->product_price_percentage); ?>" />%
			</td>
		</tr>
	</table>
<?php } ?>
<input type="hidden" name="count_price" value="<?php echo $a;?>" id="count_price" />
<div style="display:none">
	<table class="adminlist table table-striped" cellpadding="1" width="100%" id="price_listing_table_row">
		<tr class="row0" id="price_##">
			<td>
				<input size="10" type="text" id="price[price_value][##]" name="price[price_value][##]" value="0" onchange="updatePrice('price_with_tax_##',this.value,this.form['data[product][product_tax_id]'].value,0);" />
			</td>
			<td<?php echo $hideTaxedPriceColumn; ?>>
				<input size="10" type="text" id="price_with_tax_##" name="price_with_tax_##" value="0" onchange="updatePrice('price[price_value][##]',this.value,this.form['data[product][product_tax_id]'].value,1);"/>
			</td>
			<td>
				<?php echo @$this->currency->display('price[price_currency_id][##]',0); ?>
			</td>
			<td>
				<input type="text" size="3" id="price[price_min_quantity][##]" name="price[price_min_quantity][##]" value="1" />
			</td>
			<?php if(hikashop_level(2)){ ?>
			<td>
				--
			</td>
			<?php }
			echo $site_id_value; ?>
			<td>
				--
			</td>
			<td>
				<a href="#" onclick="hikashop.deleteRow(this); return false;"><img src="<?php echo HIKASHOP_IMAGES; ?>delete2.png" alt="<?php echo JText::_('HIKA_DELETE'); ?>"></a>
			</td>
		</tr>
	</table>
</div>
