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
<form action="<?php echo hikashop_completeLink('taxation');?>" method="post"  name="adminForm" id="adminForm">
	<center>
	<table class="admintable table">
		<tr>
			<td class="key">
					<?php echo JText::_( 'TAXATION_CATEGORY' ); ?>
			</td>
			<td>
				<?php echo $this->category->display( "data[taxation][category_namekey]" , @$this->element->category_namekey ); ?>
			</td>
		</tr>
		<tr>
			<td class="key">
					<?php echo JText::_( 'RATE' ); ?>
			</td>
			<td>
				<?php echo $this->ratesType->display( "data[taxation][tax_namekey]" , @$this->element->tax_namekey ); ?>
			</td>
		</tr>
		<tr>
			<td class="key">
					<?php echo JText::_( 'CUMULATIVE_TAX' ); ?>
			</td>
			<td>
				<?php echo JHTML::_('hikaselect.booleanlist', "data[taxation][taxation_cumulative]" , '',@$this->element->taxation_cumulative	); ?>
			</td>
		</tr>
		<tr>
			<td class="key">
					<?php echo JText::_( 'POST_CODE' ); ?>
			</td>
			<td>
				<input type="text" name="data[taxation][taxation_post_code]" value="<?php echo @$this->element->taxation_post_code; ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
					<?php echo JText::_( 'ZONE' ); ?>
			</td>
			<td><?php
				echo $this->nameboxType->display(
					'data[taxation][zone_namekey]',
					explode(',',@$this->element->zone_namekey),
					hikashopNameboxType::NAMEBOX_MULTIPLE,
					'zone',
					array(
						'delete' => true,
						'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
						'zone_types' => array('country' => 'COUNTRY', 'tax' => 'TAXES'),
					)
				);
				?>
			</td>
		</tr>
		<tr>
			<td class="key">
					<?php echo JText::_( 'CUSTOMER_TYPE' ); ?>
			</td>
			<td>
				<?php echo $this->taxType->display( "data[taxation][taxation_type][]" , explode(',',trim(@$this->element->taxation_type,',')), true, 'multiple="multiple" size="3"' ); ?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_( 'START_DATE' ); ?>
			</td>
			<td>
				<?php echo JHTML::_('calendar', hikashop_getDate((@$this->element->taxation_date_start?@$this->element->taxation_date_start:''),'%Y-%m-%d %H:%M'), 'data[taxation][taxation_date_start]','taxation_date_start','%Y-%m-%d %H:%M',array('size'=>'20')); ?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_( 'END_DATE' ); ?>
			</td>
			<td>
				<?php echo JHTML::_('calendar', hikashop_getDate((@$this->element->taxation_date_end?@$this->element->taxation_date_end:''),'%Y-%m-%d %H:%M'), 'data[taxation][taxation_date_end]','taxation_date_end','%Y-%m-%d %H:%M',array('size'=>'20')); ?>
			</td>
		</tr>
		<tr>
			<td class="key">
					<?php echo JText::_( 'INTERNAL_CODE' ); ?>
			</td>
			<td>
				<input type="text" name="data[taxation][taxation_internal_code]" value="<?php echo @$this->element->taxation_internal_code; ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
					<?php echo JText::_( 'TAX_NOTE' ); ?>
			</td>
			<td>
								<textarea rows="3" cols="60" id="jform_params_taxation_note" name="data[taxation][taxation_note]" title="<?php echo JText::_('TAX_NOTE_TTIPS'); ?>"><?php echo @$this->element->taxation_note; ?></textarea>
			</td>
		</tr>
<?php
						if(file_exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites'.DS.'helpers'.DS.'utils.php')){
							include_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites'.DS.'helpers'.DS.'utils.php');
							if ( class_exists( 'MultisitesHelperUtils') && method_exists( 'MultisitesHelperUtils', 'getComboSiteIDs')) {
								$comboSiteIDs = MultisitesHelperUtils::getComboSiteIDs( @$this->element->taxation_site_id, 'data[taxation][taxation_site_id]', JText::_( 'SELECT_A_SITE'));
								if( !empty( $comboSiteIDs)){ ?>
								<tr>
									<td class="key">
											<?php echo JText::_( 'SITE_ID' ); ?>
									</td>
									<td>
										<?php echo $comboSiteIDs; ?>
									</td>
								</tr>
								<?php
								}
							}
						}
?>

<?php
if(!empty($this->extra_blocks['taxation'])) {
	foreach($this->extra_blocks['taxation'] as $r) {
		if(is_string($r))
			echo $r;
		if(is_object($r)) $r = (array)$r;
		if(is_array($r)) {
			if(!isset($r['name']) && isset($r[0]))
				$r['name'] = $r[0];
			if(!isset($r['value']) && isset($r[1]))
				$r['value'] = $r[1];
?>
		<tr>
			<td class="key"><?php echo JText::_(@$r['name']); ?></td>
			<td><?php echo @$r['value']; ?></td>
		</tr>
<?php
		}
	}
}
?>
		<tr>
			<td colspan="2">
				<fieldset class="adminform">
					<legend><?php echo JText::_('ACCESS_LEVEL'); ?></legend>
					<?php
					if(hikashop_level(2)){
						$acltype = hikashop_get('type.acl');
						echo $acltype->display('taxation_access',@$this->element->taxation_access,'taxation');
					}else{
						echo hikashop_getUpgradeLink('business');
					} ?>
				</fieldset>
			</td>
		</tr>
		<tr>
			<td class="key">
					<?php echo JText::_( 'HIKA_PUBLISHED' ); ?>
			</td>
			<td>
				<?php echo JHTML::_('hikaselect.booleanlist', "data[taxation][taxation_published]" , '',@$this->element->taxation_published	); ?>
			</td>
		</tr>
	</table>
	</center>
	<div class="clr"></div>

	<input type="hidden" name="taxation_id" value="<?php echo @$this->element->taxation_id; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT;?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="<?php echo JRequest::getString('ctrl');?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
