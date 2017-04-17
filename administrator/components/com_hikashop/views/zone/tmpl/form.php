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
<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=zone" method="post"  name="adminForm" id="adminForm" enctype="multipart/form-data">
	<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
	<div id="hikashop_zone_form">
		<table style="width:100%" class="table">
			<tr>
				<td valign="top" width="350">
	<?php } else { ?>
	<div id="hikashop_zone_form" class="row-fluid">
		<div class="span4 hikaspanleft">
	<?php } ?>
					<fieldset class="adminform" id="htmlfieldset">
						<legend><?php echo JText::_( 'ZONE_INFORMATION' ); ?></legend>
						<?php
						$this->setLayout('information');
						echo $this->loadTemplate();
						?>
					</fieldset>
	<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
				</td>
				<td valign="top">
	<?php } else { ?>
		</div>
		<div class="span8">
	<?php } ?>
					<fieldset class="adminform" id="htmlfieldset">
						<legend><?php echo JText::_( 'SUBZONES' ); ?></legend>
						<?php if(empty($this->element->zone_namekey)){
							echo JText::_( 'SUBZONES_CHOOSER_DISABLED' );
						}else{
							$this->setLayout('childlisting');
							echo $this->loadTemplate();
						} ?>
					</fieldset>
	<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
				</td>
			</tr>
		</table>
	</div>
	<?php } else { ?>
		</div>
	</div>
	<?php } ?>
	<div class="clr"></div>
	<input type="hidden" name="cid[]" value="<?php echo @$this->element->zone_id; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="zone" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
