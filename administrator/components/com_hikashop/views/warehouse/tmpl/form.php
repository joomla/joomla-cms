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
<form action="<?php echo hikashop_completeLink('warehouse'); ?>" method="post" name="adminForm" id="adminForm">
	<table class="admintable table" style="width:100%">
		<tr>
			<td class="key"><?php
				echo JText::_( 'HIKA_NAME' );
			?></td>
			<td>
				<input id="warehouse_name" type="text" size="40" name="data[warehouse][warehouse_name]" value="<?php echo $this->escape(@$this->element->warehouse_name); ?>" />
			</td>
		</tr>
		<tr>
			<td class="key"><?php
				echo JText::_( 'HIKA_PUBLISHED' );
			?></td>
			<td>
				<?php echo JHTML::_('hikaselect.booleanlist', "data[warehouse][warehouse_published]" , '',@$this->element->warehouse_published);?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_( 'HIKA_DESCRIPTION' ); ?>
			</td>
			<td width="80%"></td>
		</tr>
		<tr>
			<td colspan="2" width="100%"><?php
				$this->editor->content = @$this->element->warehouse_description;
				echo $this->editor->display();
			?></td>
		</tr>
	</table>
	<input type="hidden" name="cid[]" value="<?php echo @$this->element->warehouse_id; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="warehouse" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
