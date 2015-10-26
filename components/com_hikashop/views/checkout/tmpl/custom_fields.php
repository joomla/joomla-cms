<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
$showfields = array(
	'my_special_field1' => 0, // my_special_field1 display only on step 0
);
$type = $this->type;
foreach($this->extraFields[$type] as $fieldName => $oneExtraField) {
	if(isset($showfields[$fieldName]) && $this->step != $showfields[$fieldName]) {
		echo '<tr style="display:none;"><td><input type="hidden" name="data['.$type.']['.$fieldName.']" value="'.$this->escape($this->$type->$fieldName).'"/></td></tr>';
		continue;
	}
?>
	<tr class="hikashop_checkout_<?php echo $fieldName;?>_line" id="hikashop_<?php echo $type.'_'.$oneExtraField->field_namekey; ?>">
		<td class="key">
			<?php echo $this->fieldsClass->getFieldName($oneExtraField);?>
		</td>
		<td>
<?php
	$onWhat='onchange';
	if($oneExtraField->field_type=='radio')
		$onWhat='onclick';

	echo $this->fieldsClass->display(
			$oneExtraField,
			$this->$type->$fieldName,
			'data['.$type.']['.$fieldName.']',
			false,
			' '.$onWhat.'="hikashopToggleFields(this.value,\''.$fieldName.'\',\''.$type.'\',0);"'
		);
?>
		</td>
	</tr>
<?php
}
