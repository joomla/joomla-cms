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
	<?php
		echo $this->popup->display(
			'<img src="'.HIKASHOP_IMAGES.'add.png"/>'.JText::_('ADD'),
			'ADD',
			hikashop_completeLink("product&task=selectfile&legacy=1&product_id=".@$this->element->product_id,true),
			'file_add_button',
			860, 480, '', '', 'button'
		);
	?>
</div>
<br/>
				<table id="hikashop_product_file_table" class="adminlist table table-striped table-hover" cellpadding="1">
					<thead>
						<tr>
							<th class="title">
								<?php echo JText::_('HIKA_EDIT'); ?>
							</th>
							<th class="title">
								<?php echo JText::_('FILENAME'); ?>
							</th>
							<th class="title">
								<?php echo JText::_('HIKA_NAME'); ?>
							</th>
							<th class="title">
								<?php echo JText::_('DOWNLOADS'); ?>
							</th>
							<th class="title">
								<?php echo JText::_('FREE_DOWNLOAD'); ?>
							</th>
							<th class="title">
								<?php echo JText::_('HIKA_DELETE'); ?>
							</th>
							<th class="title">
								<?php echo JText::_( 'ID' ); ?>
							</th>
						</tr>
					</thead>
					<tbody id="file_listing">
						<?php
							if(!empty($this->element->files)){
								$k = 0;
								foreach($this->element->files as $row){
									$id=rand();
									if(substr($row->file_path,0,1) == '@')
										continue;
									if(!isset($row->file_limit) || $row->file_limit == 0)
										$row->file_limit = '<em>'.$this->config->get('download_number_limit').'</em>';
							?>
								<tr class="<?php echo "row$k"; ?>" id="file_<?php echo $row->file_id.'_'.$id;?>">
									<td width="1%" align="center">
										<?php
											echo $this->popup->display(
												'<img src="'.HIKASHOP_IMAGES.'edit.png"/>',
												'HIKA_EDIT',
												hikashop_completeLink("product&task=selectfile&legacy=1&cid=".$row->file_id."&product_id=".@$this->element->product_id.'&id='.$id,true ),
												'file_edit_button'.$row->file_id,
												860, 480, '', '', 'link'
											);
										?>
									</td>
									<td>
										<?php echo $row->file_path; ?>
									</td>
									<td>
										<?php echo $row->file_name; ?>
									</td>
									<td width="1%" align="right">
										<?php
											echo (int)@$row->download_number . ' / ';
											if(!is_numeric($row->file_limit) || $row->file_limit > 0)
												echo $row->file_limit;
											else
												echo JText::_('UNLIMITED');
											if(@$row->download_number){
												echo ' <a href="'.hikashop_completeLink('file&task=resetdownload&file_id='.$row->file_id.'&'.hikashop_getFormToken().'=1&return='.urlencode(base64_encode(hikashop_completeLink('product&task=edit&cid='.@$this->element->product_id,false,true)))).'"><img src="'.HIKASHOP_IMAGES.'delete.png" alt="'.JText::_('HIKA_DELETE').'" /></a>';
											}
										?>
									</td>
									<td width="1%" align="center">
										<input type="checkbox" disabled="disabled" <?php echo !empty($row->file_free_download) ? 'checked="checked"' : ''; ?> />
									</td>
									<td width="1%" align="center">
										<a href="#" onclick="return deleteRow('file_div_<?php echo $row->file_id.'_'.$id;?>','file[<?php echo $row->file_id;?>][<?php echo $id;?>]','file_<?php echo $row->file_id.'_'.$id;?>');"><img src="<?php echo HIKASHOP_IMAGES; ?>delete.png"/></a>
									</td>
									<td width="1%" align="center">
										<?php echo $row->file_id; ?>
										<div id="file_div_<?php echo $row->file_id.'_'.$id;?>">
											<input type="hidden" name="file[<?php echo $row->file_id;?>]" id="file[<?php echo $row->file_id;?>][<?php echo $id;?>]" value="<?php echo $row->file_id;?>"/>
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
