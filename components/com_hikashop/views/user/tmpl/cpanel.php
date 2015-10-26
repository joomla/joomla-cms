<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="hikashop_cpanel_main" id="hikashop_cpanel_main">
	<div class="hikashop_cpanel_title" id="hikashop_cpanel_title">
		<fieldset>
			<div class="header hikashop_header_title"><h1><?php echo JText::_('CUSTOMER_ACCOUNT');?></h1></div>
		</fieldset>
	</div>
	<div class="hikashopcpanel" id="hikashopcpanel">
<?php
	foreach($this->buttons as $oneButton) {
		$url = hikashop_level($oneButton['level']) ? 'onclick="document.location.href=\''.$oneButton['link'].'\';"' : '';
?>
		<div <?php echo $url; ?> class="icon hikashop_cpanel_icon_div icon hikashop_cpanel_icon_div_<?php echo $oneButton['image'];?>">
			<a href="<?php echo hikashop_level($oneButton['level']) ? $oneButton['link'] : '#'; ?>">
				<table class="hikashop_cpanel_icon_table">
					<tr>
						<td class="hikashop_cpanel_icon_image">
							<span class="hikashop_cpanel_icon_image_span icon-48-<?php echo $oneButton['image']; ?>" title="<?php echo $oneButton['text']; ?>"> </span>
							<span class="hikashop_cpanel_button_text"><?php echo $oneButton['text']; ?></span>
						</td>
						<td>
							<div class="hikashop_cpanel_button_description">
								<?php echo $oneButton['description']; ?>
							</div>
						</td>
					</tr>
				</table>
			</a>
		</div>
<?php
	}
?>
	</div>
</div>
<div class="clear_both"></div>
