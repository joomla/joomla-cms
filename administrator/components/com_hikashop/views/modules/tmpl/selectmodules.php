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
		<button class="btn" type="button" onclick="submitbutton('savemodules');"><img src="<?php echo HIKASHOP_IMAGES; ?>save.png"/><?php echo JText::_('OK'); ?></button>
	</div>
</fieldset>
<div class="iframedoc" id="iframedoc"></div>
<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=modules" method="post"  name="adminForm" id="adminForm">
	<table id="hikashop_modules_selection_listing" class="adminlist table table-striped table-hover" cellpadding="1">
		<thead>
			<tr>
				<th class="title titlenum">
					<?php echo JText::_( 'HIKA_NUM' );?>
				</th>
				<th class="title">
					<?php echo JText::_('HIKA_NAME'); ?>
				</th>
				<th class="title">
					<?php echo JText::_('HIKA_TYPE'); ?>
				</th>
				<th class="title titletoggle">
					<?php echo JText::_('HIKA_ORDER'); ?>
				</th>
				<th class="title titletoggle">
					<?php echo JText::_('HIKA_PUBLISHED'); ?>
				</th>
				<th class="title">
					<?php echo JText::_( 'ID' ); ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$k = 0;
				for($i = 0,$a = count($this->rows);$i<$a;$i++){
					$row =& $this->rows[$i];
					if(version_compare(JVERSION,'1.6','<')){
						$link=JRoute::_('index.php?option=com_modules&client=0&task=edit&cid='.$row->id);
					}else{
						$link=JRoute::_('index.php?option=com_modules&task=module.edit&id='.$row->id);
					}
			?>
				<tr class="<?php echo "row$k"; ?>">
					<td>
						<?php echo $i+1; ?>
					</td>
					<td>
						<a target="_blank" href="<?php echo $link; ?>">
						<?php echo $row->title; ?>
						</a>
					</td>
					<td>
						<?php echo $row->module; ?>
					</td>
					<td align="center" class="order">
						<input type="text" name="data[module][ordering][<?php echo $row->id; ?>]" value="<?php echo (int)@$row->module_ordering; ?>" size="3" />
					</td>
					<td align="center" nowrap>
						<?php echo JHTML::_('hikaselect.booleanlist', 'data[module][used]['.$row->id.']' , '',@$row->module_used); ?>
					</td>
					<td width="1%" align="center">
						<?php echo $row->id; ?>
					</td>
				</tr>
			<?php
					$k = 1-$k;
				}
			?>
		</tbody>
	</table>
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="selectmodules" />
	<input type="hidden" name="control" value="<?php echo JRequest::getCmd('control','');?>" />
	<input type="hidden" name="name" value="<?php echo JRequest::getCmd('name','');?>" />
	<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
