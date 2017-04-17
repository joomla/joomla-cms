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
<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=characteristic" method="post"  name="adminForm" id="adminForm" enctype="multipart/form-data">
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
<div id="page-characteristic">
	<table style="width:100%">
		<tr>
			<td valign="top" width="35%">
<?php } else { ?>
<div id="page-characteristic" class="row-fluid">
	<div class="span4">
<?php } ?>
				<?php echo $this->loadTemplate('item');?>
				<table width="100%" class="admintable table">
					<tbody>
						<tr>
							<td class="key">
									<?php echo JText::_( 'HIKA_ALIAS' ); ?>
							</td>
							<td>
								<input type="text" id="characteristic_alias" name="data[characteristic][characteristic_alias]" value="<?php echo $this->escape(@$this->element->characteristic_alias); ?>" />
							</td>
						</tr>
						<?php $config = hikashop_config();
						if(in_array($config->get('characteristic_display'),array('dropdown','radio'))){ ?>
							<tr>
								<td class="key">
										<?php echo JText::_( 'CHARACTERISTICS_DISPLAY' ); ?>
								</td>
								<td>
									<?php
										$characteristicdisplayType = hikashop_get('type.characteristicdisplay');
										echo $characteristicdisplayType->display('data[characteristic][characteristic_display_method]',@$this->element->characteristic_display_method,'characteristic');
									?>
								</td>
							</tr>
						<?php } ?>
					</tbody>
				</table>
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
			</td>
			<td valign="top" width="65%">
<?php } else { ?>
	</div>
	<div class="span8">
<?php } ?>
				<fieldset class="adminform" id="htmlfieldset">
					<legend><?php echo JText::_( 'VALUES' ); ?></legend>
					<?php
						$this->setLayout('form_value');
						echo $this->loadTemplate();
					?>
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
	<input type="hidden" name="cid[]" value="<?php echo @$this->element->characteristic_id; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="characteristic" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
