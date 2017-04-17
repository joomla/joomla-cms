<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><table class="admintable table" width="280px" style="margin:auto">
	<tr>
		<td class="key">
			<?php echo JText::_( 'HIKA_NAME' ); ?>
		</td>
		<td>
			<input id="characteristic_value" type="text" name="<?php echo $this->characteristic_value_input;?>" value="<?php echo $this->escape(@$this->element->characteristic_value); ?>" />
			<?php if(isset($this->characteristic_value_published)){
						$publishedid = 'published-'.$this->characteristic_value_id;
				?>
				<span id="<?php echo $publishedid; ?>" class="spanloading"><?php echo $this->toggle->toggle($publishedid,(int) $this->characteristic_value_published,'translation') ?></span>
			<?php } ?>
		</td>
	</tr>
</table>
