<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div style="float:right">
	<button class="btn" type="button" onclick="submitbutton('managevariant');">
		<img src="<?php echo HIKASHOP_IMAGES; ?>edit.png"/><?php echo JText::_('MANAGE_VARIANTS');?>
	</button>
	<?php
		echo $this->popup->display(
			'<img src="'.HIKASHOP_IMAGES.'add.png"/>'.JText::_('ADD'),
			'ADD',
			hikashop_completeLink("characteristic&task=selectcharacteristic",true ),
			'characteristic_add_button',
			860, 480, '', '', 'button'
		);
	?>
</div>
<br/>
<table class="adminlist table table-striped table-hover" cellpadding="1" width="100%">
	<thead>
		<tr>
			<th class="title">
				<?php echo JText::_('HIKA_NAME'); ?>
			</th>
			<th class="title">
				<?php echo JText::_('DEFAULT_VALUE'); ?>
			</th>
			<th class="title">
				<?php echo JText::_('HIKA_ORDER'); ?>
			</th>
			<th class="title titletoggle">
				<?php echo JText::_('HIKA_DELETE'); ?>
			</th>
			<th class="title">
				<?php echo JText::_('ID'); ?>
			</th>
		</tr>
	</thead>
	<tbody id="characteristic_listing">
		<?php
			if(!empty($this->element->characteristics)){
				$k = 0;
				for($i = 0,$a = count($this->element->characteristics);$i<$a;$i++){
					$row =& $this->element->characteristics[$i];
					$id = rand();
					?>
					<tr id="characteristic_<?php echo $row->characteristic_id.'_'.$id;?>">
						<td>
							<?php echo $row->characteristic_value; ?>
						</td>
						<td>
							<div id="characteristic_default_div_<?php echo $row->characteristic_id.'_'.$id;?>">
							<?php echo $this->characteristicHelper->display('characteristic_default['.$row->characteristic_id.']',@$row->default_id,@$row->values,'characteristic_default_'.$row->characteristic_id.'_'.$id); ?>
							</div>
						</td>
						<td class="order">
							<div id="characteristic_ordering_div_<?php echo $row->characteristic_id.'_'.$id;?>">
								<input type="text" size="3" name="characteristic_ordering[<?php echo $row->characteristic_id;?>]" id="characteristic_ordering[<?php echo $row->characteristic_id;?>][<?php echo $id?>]" value="<?php echo intval(@$row->ordering);?>"/>
							</div>
						</td>
						<td align="center">
							<a href="#" onclick="return deleteRow('characteristic_div_<?php echo $row->characteristic_id.'_'.$id;?>','characteristic[<?php echo $row->characteristic_id;?>][<?php echo $id?>]','characteristic_<?php echo $row->characteristic_id.'_'.$id;?>','characteristic_default_div_<?php echo $row->characteristic_id.'_'.$id;?>','characteristic_default_<?php echo $row->characteristic_id.'_'.$id;?>','characteristic_ordering_div_<?php echo $row->characteristic_id.'_'.$id;?>','characteristic_ordering[<?php echo $row->characteristic_id;?>][<?php echo $id?>]');">
								<img src="<?php echo HIKASHOP_IMAGES; ?>delete.png"/>
							</a>
						</td>
						<td width="1%" align="center">
							<?php echo $row->characteristic_id; ?>
							<div id="characteristic_div_<?php echo $row->characteristic_id.'_'.$id;?>">
								<input type="hidden" name="characteristic[<?php echo $row->characteristic_id;?>]" id="characteristic[<?php echo $row->characteristic_id;?>][<?php echo $id?>]" value="<?php echo $row->characteristic_id;?>"/>
							</div>
						</td>
					</tr>
					<?php
					$k = 1-$k;
				}
			}
		?>
	</tbody>
</table>
