<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="leftmenu-container <?php if(HIKASHOP_BACK_RESPONSIVE) echo 'leftmenu-container-j30';?>">
	<div <?php if(!HIKASHOP_BACK_RESPONSIVE) echo 'class="config-menu"';?> id="menu_<?php echo $this->menuname; ?>">
		<a id="menu-scrolltop-<?php echo $this->menuname; ?>" href="#" onclick="window.scrollTo(0, 0);" class="menu-scrolltop" style="float: right; margin:12px 2px 0px 2px;">
			<span class="scrollTop_img" style="padding: 11px 18px;"></span>
		</a>
		<ul <?php if(HIKASHOP_BACK_RESPONSIVE) echo 'class="hika-navbar-ul" data-spy="affix" data-offset-top="60"';?>>
<?php
	foreach($this->menudata as $href => $name) {
?>			<li><a href="<?php echo $href; ?>"><?php echo $name; ?><i class="icon-chevron-right"></i></a><div style="clear:left;"></div></li>
<?php
	}
	if(HIKASHOP_BACK_RESPONSIVE){
		?>
		<li id="responsive_menu_scrolltop_li_<?php echo $this->menuname; ?>">
			<a style="text-align:center;" href="#" onclick="window.scrollTo(0, 0);">
				<span class="responsive_menu_scrolltop" style="padding: 6px 12px 18px 12px; "></span>
			</a>
			<div style="clear:left;"></div>
		</li>
		<?php
	}
?>
		</ul>
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
		<a id="menu-save-button-<?php echo $this->menuname; ?>" class="menu-save-button" onclick="window.hikashop.submitform('apply', 'adminForm'); return false;" href="#" style="float: right; margin:0px 4px 2px 0px;">
			<span class="menuSave_img" style="padding: 12px 16px;"> </span>
		</a>
<?php } ?>
	</div>
</div>
