<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="iframedoc" id="iframedoc"></div>
<form action="<?php echo hikashop_completeLink('vote'); ?>" method="post" name="adminForm" id="adminForm">
<?php if(HIKASHOP_BACK_RESPONSIVE) { ?>
	<div class="row-fluid">
		<div class="span8">
			<div class="input-prepend input-append">
				<span class="add-on"><i class="icon-filter"></i></span>
				<input type="text" name="search" id="search" value="<?php echo $this->escape($this->pageInfo->search);?>" class="text_area" />
				<button class="btn" onclick="this.form.limitstart.value=0;this.form.submit();"><i class="icon-search"></i></button>
				<button class="btn" onclick="this.form.limitstart.value=0;document.getElementById('search').value='';this.form.submit();"><i class="icon-remove"></i></button>
			</div>
		</div>
		<div class="span4">
<?php } else { ?>
	<table>
		<tr>
			<td width="100%">
				<?php echo JText::_('FILTER'); ?>:
				<input type="text" name="search" id="search" value="<?php echo $this->escape($this->pageInfo->search);?>" class="text_area" />
				<button class="btn" onclick="this.form.limitstart.value=0;this.form.submit();"><?php echo JText::_('GO'); ?></button>
				<button class="btn" onclick="this.form.limitstart.value=0;document.getElementById('search').value='';this.form.submit();"><?php echo JText::_('RESET'); ?></button>
			</td>
			<td nowrap="nowrap">
<?php }


if(HIKASHOP_BACK_RESPONSIVE) { ?>
		</div>
	</div>
<?php } else { ?>
			</td>
		</tr>
	</table>
<?php } ?>

	<?php $backend_listing_vote = JRequest::getVar('backend_listing_vote', 'both', 'default', 'string', 0); ?>
	<table id="hikashop_vote_listing" class="adminlist table table-striped table-hover" cellpadding="1">
		<thead>
			<tr>
				<th class="title titlenum">
					<?php echo JText::_( 'HIKA_NUM' );?>
				</th>
				<th class="title titlebox">
					<input type="checkbox" name="toggle" value="" onclick="hikashop.checkAll(this);" />
				</th>
				<th class="title_title_product_id">
					<?php echo JHTML::_('grid.sort', JText::_('HIKASHOP_ITEM'), 'a.vote_ref_id', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<?php
					$manyTypes = 0;
					if(defined('HIKAMARKET_COMPONENT')) {
						$manyTypes = 1;
					}
					if($manyTypes){ ?>
				<th class="title_title_type">
					<?php  echo JHTML::_('grid.sort', JText::_('HIKA_TYPE'), 'a.vote_type', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<?php } ?>
				<?php if($this->pageInfo->enabled == 2 || $this->pageInfo->enabled == 3){?>
				<th class="title_title_comment">
					<?php echo JHTML::_('grid.sort', JText::_('COMMENT'), 'a.vote_comment', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<?php
					}
					if($this->pageInfo->enabled == 1 || $this->pageInfo->enabled == 3){
				?>
				<th class="title_title_vote">
					<?php echo JHTML::_('grid.sort', JText::_('RATING'), 'a.vote_rating', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<?php } ?>
				<th class="title_title_username">
					<?php echo JHTML::_('grid.sort', JText::_('HIKA_USERNAME'), 'a.vote_pseudo', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title_title_ip">
					<?php echo JHTML::_('grid.sort', JText::_('HIKA_IP'), 'a.vote_ip', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title_title_email">
					<?php echo JHTML::_('grid.sort', JText::_('HIKA_EMAIL'), 'a.vote_email', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title titledate">
				<?php echo JHTML::_('grid.sort', JText::_( 'DATE' ), 'a.vote_date',$this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title titletoggle">
					<?php echo JHTML::_('grid.sort',   JText::_('HIKA_PUBLISHED'), 'a.vote_published', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort',   JText::_( 'ID' ), 'a.vote_id', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="11">
					<?php echo $this->pagination->getListFooter(); ?>
					<?php echo $this->pagination->getResultsCounter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
			<?php
				$k = 0;
				$a = count($this->rows);
				if($a){
					for($i = 0;$i<$a;$i++){
						$row =& $this->rows[$i];

						$publishedid = 'vote_published-'.$row->vote_id;
						$username = isset($row->username)?$row->username:'0';
						$email = isset($row->email)?$row->email:'0';
						$item_name = @$row->item_name;

						if(($backend_listing_vote == 'vote'  && $row->vote_rating!='0') || $backend_listing_vote == 'both' ||  ($backend_listing_vote == 'comment'  && $row->vote_comment!='')){

					?>
						<tr class="<?php echo "row$k"; ?>">
							<td align="center">
							<?php echo $this->pagination->getRowOffset($i); ?>
							</td>
							<td align="center">
								<?php echo JHTML::_('grid.id', $i, $row->vote_id ); ?>
							</td>
							<td>
								<?php
									if($this->pageInfo->manageProduct && $row->vote_type == 'product'){
										echo "<a href=".hikashop_completeLink('option=com_hikashop&ctrl=product&task=edit&cid[]='.$row->vote_ref_id,false,true).">$item_name</a>";
									}else{
										echo $item_name;
									}
								?>
							</td>
							<?php
								if($manyTypes){ 
							?>
							<td>
								<?php
									echo $row->vote_type;
								?>
							</td>
							<?php } ?>
							<?php if($this->pageInfo->enabled == 2 || $this->pageInfo->enabled == 3){?>
							<td>
								<?php
									if($row->vote_comment == ''){echo "empty";}
									elseif($this->manage){
										echo "<a href=".hikashop_completeLink('vote&task=edit&cid[]='.$row->vote_id,false,true).">"; echo JHTML::tooltip($row->vote_comment, JText::_('FULL_COMMENT'),'', $row->vote_comment_short); echo "</a>";
									}
									else{
										echo JHTML::tooltip($row->vote_comment, JText::_('FULL_COMMENT'),'', $row->vote_comment_short);
									}
								?>
								</a>
							</td>
							<?php
							}
							if($this->pageInfo->enabled == 1 || $this->pageInfo->enabled == 3){?>
							<td>
								<?php
									if($row->vote_rating == '0'){echo "empty";$row->vote_rating = "";}
									elseif($this->manage){echo "<a href=".hikashop_completeLink('vote&task=edit&cid[]='.$row->vote_id,false,true).">".$row->vote_rating."</a>";}
									else{ echo $row->vote_rating;}
								?>
							</td>
							<?php } ?>
							<td>
								<?php
								if(($row->vote_pseudo == '0' || $row->vote_pseudo == '')&& $username !='0' ){
									if($this->pageInfo->manageUser){
										echo "<a href=".hikashop_completeLink('option=com_hikashop&ctrl=user&task=edit&cid[]='.$row->vote_user_id,false,true).">$username</a>";
									}
									else{
										echo $username;
									}
								}
								else if($username == '0' && ($row->vote_pseudo == '0' || $row->vote_pseudo == '')){echo 'empty';}
								else{
									echo $row->vote_pseudo;
								}
								?>
							</td>
							<td>
								<?php echo $row->vote_ip; ?>
							</td>
							<td>
								<?php
								if(($row->vote_email == '0' || $row->vote_email == '') && $email !='0' ){
									if($this->pageInfo->manageUser){
										echo "<a href=".hikashop_completeLink('option=com_hikashop&ctrl=user&task=edit&cid[]='.$row->vote_user_id,false,true).">$email</a>";
									}
									else{
										echo $email;
									}
								}
								else if($email == 0 && ($row->vote_email == '0' || $row->vote_email == '')){echo 'empty';}
								else{
									echo $row->vote_email;
								} ?>
							</td>
							<td class="order">
								<?php  echo $date = date('d/m/Y h:m:s', $row->vote_date);  ?>
							</td>
							<td align="center">
								<?php if($this->manage){ ?>
									<span id="<?php echo $publishedid?>" class="spanloading"><?php echo $this->toggleClass->toggle($publishedid,(int) $row->vote_published,'vote') ?></span>
								<?php }else{ echo $this->toggleClass->display('activate',$row->vote_published); } ?>
							</td>
							<td width="1%" align="center">
								<?php echo $row->vote_id; ?>
							</td>
						</tr>
					<?php
							$k = 1-$k;
						}
					}
				}
			?>
		</tbody>
	</table>
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
