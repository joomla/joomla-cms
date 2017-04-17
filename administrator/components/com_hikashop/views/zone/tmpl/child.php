<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><tr id="zone_namekey-<?php echo $this->row->zone_namekey; ?>" class="row<?php echo $this->k; ?>">
	<td align="center">
		<?php echo @$this->row->zone_name_english; ?>
	</td>
	<td align="center">
		<?php echo @$this->row->zone_name; ?>
	</td>
	<td align="center">
		<?php echo @$this->row->zone_code_2; ?>
	</td>
	<td align="center">
		<?php echo @$this->row->zone_code_3; ?>
	</td>
	<td align="center">
		<?php echo @$this->row->zone_type; ?>
	</td>
	<td align="center">
		<span class="spanloading">
			<?php echo $this->toggleClass->delete("zone_namekey-".$this->row->zone_namekey,$this->main_namekey.'-'.$this->row->zone_namekey,'zone',true) ?>
		</span>
	</td>
	<td align="center">
		<?php echo @$this->row->zone_id; ?>
	</td>
</tr>
