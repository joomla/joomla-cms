<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><fieldset>
	<div class="toolbar" id="toolbar" style="float: right;">
		<?php if(!in_array($this->type,array('discount','shipping','payment','config','tax'))){?>
			<button class="btn" type="button" onclick="submitbutton('newchild');"><img src="<?php echo HIKASHOP_IMAGES; ?>new.png"/><?php echo JText::_('HIKA_NEW'); ?></button>
		<?php }?>
		<button class="btn" type="button" onclick="if(document.adminForm.boxchecked.value==0){alert('<?php echo JText::_( 'PLEASE_SELECT_SOMETHING',true ); ?>');}else{submitbutton('addchild');}"><img src="<?php echo HIKASHOP_IMAGES; ?>add.png"/><?php echo JText::_('OK'); ?></button>
	</div>
</fieldset>
<div class="iframedoc" id="iframedoc"></div>
<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=zone&amp;tmpl=component" method="post"  name="adminForm" id="adminForm">
	<table>
		<tr>
			<td width="100%">
				<?php echo JText::_( 'FILTER' ); ?>:
				<input type="text" name="search" id="search" value="<?php echo $this->escape($this->pageInfo->search);?>" class="text_area" onchange="document.adminForm.submit();" />
				<button class="btn" onclick="this.form.submit();"><?php echo JText::_( 'GO' ); ?></button>
				<button class="btn" onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'RESET' ); ?></button>
			</td>
			<td nowrap="nowrap">
				<?php echo $this->filters->country; ?>
				<?php echo $this->filters->type; ?>
			</td>
		</tr>
	</table>
	<table id="hikashop_zone_selection_listing" class="adminlist table table-striped table-hover" cellpadding="1">
		<thead>
			<tr>
				<th class="title titlenum">
					<?php echo JText::_( 'HIKA_NUM' );?>
				</th>
				<th class="title titlebox">
					<input type="checkbox" name="toggle" value="" onclick="hikashop.checkAll(this);" />
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('ZONE_NAME_ENGLISH'), 'a.zone_name_english', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value,'selectchildlisting' ); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('HIKA_NAME'), 'a.zone_name', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value,'selectchildlisting' ); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('ZONE_CODE_2'), 'a.zone_code_2', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value,'selectchildlisting' ); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('ZONE_CODE_3'), 'a.zone_code_3', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value,'selectchildlisting' ); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('ZONE_TYPE'), 'a.zone_type', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ,'selectchildlisting'); ?>
				</th>
				<th class="title titletoggle">
					<?php echo JHTML::_('grid.sort',   JText::_('HIKA_PUBLISHED'), 'a.zone_published', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value,'selectchildlisting' ); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort',   JText::_( 'ID' ), 'a.zone_id', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value,'selectchildlisting' ); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="10">
					<?php echo $this->pagination->getListFooter(); ?>
					<?php echo $this->pagination->getResultsCounter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
			<?php
				$k = 0;
				for($i = 0,$a = count($this->rows);$i<$a;$i++){
					$row =& $this->rows[$i];
					$publishedid = 'zone_published-'.$row->zone_id;
			?>
				<tr class="<?php echo "row$k"; ?>">
					<td align="center">
					<?php echo $this->pagination->getRowOffset($i); ?>
					</td>
					<td align="center">
						<?php echo JHTML::_('grid.id', $i, $row->zone_id ); ?>
					</td>
					<td>
						<?php if(in_array($this->type,array('discount','shipping','payment','config','tax'))){?>
							<a href="<?php echo hikashop_completeLink('zone&task=addchild&cid='.$row->zone_id.'&type='.$this->type.'&subtype='.$this->subtype.'&map='.$this->map,true); ?>">
						<?php }?>
							<?php echo $row->zone_name_english; ?>
						<?php if(in_array($this->type,array('discount','shipping','payment','config','tax'))){?>
							</a>
						<?php }?>
					</td>
					<td>
						<?php echo $row->zone_name; ?>
					</td>
					<td align="center">
						<?php echo $row->zone_code_2; ?>
					</td>
					<td align="center">
						<?php echo $row->zone_code_3; ?>
					</td>
					<td align="center">
						<?php echo $row->zone_type; ?>
					</td>
					<td align="center">
						<span id="<?php echo $publishedid ?>" class="spanloading"><?php echo $this->toggleClass->toggle($publishedid,(int) $row->zone_published,'zone') ?></span>
					</td>
					<td width="1%" align="center">
						<?php echo $row->zone_id; ?>
					</td>
				</tr>
			<?php
					$k = 1-$k;
				}
			?>
		</tbody>
	</table>
	<?php if(in_array($this->type,array('discount','shipping','payment','config','tax'))){?>
		<input type="hidden" name="type" value="<?php echo $this->type;?>" />
		<input type="hidden" name="subtype" value="<?php echo $this->subtype;?>" />
		<input type="hidden" name="map" value="<?php echo $this->map;?>" />
		<input type="hidden" name="column" value="<?php echo $this->column;?>" />
	<?php }?>
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="<?php echo JRequest::getCmd('task'); ?>" />
	<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>" />
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="main_namekey" value="<?php echo JRequest::getCmd('main_namekey'); ?>" />
	<input type="hidden" name="main_id" value="<?php echo JRequest::getInt('main_id'); ?>" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
