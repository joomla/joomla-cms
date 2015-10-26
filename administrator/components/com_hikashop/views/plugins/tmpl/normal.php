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
$type = $this->plugin_type;
$upType = strtoupper($type);
$plugin_name = $type.'_name';
$plugin_published = $type.'_published';
$plugin_name_input =$plugin_name.'_input';
$plugin_description = $type.'_description';
$plugin_name_published = $plugin_name.'_published';
$plugin_name_id = $plugin_name.'_id';
$plugin_description_published = $plugin_description.'_published';
$plugin_description_id = $plugin_description.'_id';
?>
<table class="admintable" style="width:100%">
	<tr>
		<td class="key"><?php
			echo JText::_( 'HIKA_NAME' );
		?></td>
		<td>
			<input id="hikashop_plugin_name_field" type="text" name="<?php echo $this->$plugin_name_input; ?>" value="<?php echo $this->escape(@$this->element->$plugin_name); ?>" />
<?php if(isset($this->$plugin_name_published)) {
	$publishedid = 'published-'.$this->$plugin_name_id;
?>
			<span id="<?php echo $publishedid; ?>" class="spanloading"><?php echo $this->toggle->toggle($publishedid,(int) $this->$plugin_name_published,'translation') ?></span>
<?php } ?>
		</td>
	</tr>
	<tr>
		<td class="key"  colspan="2" width="100%">
			<span style="float:left"><?php echo JText::_('HIKA_DESCRIPTION'); ?></span>
<?php if(isset($this->$plugin_description_published)){
	$publishedid = 'published-'.$this->$plugin_description_id;
?>
			<span id="<?php echo $publishedid; ?>" class="spanloading"><?php echo $this->toggle->toggle($publishedid,(int) $this->$plugin_description_published,'translation') ?></span>
<?php } ?>
			<br/>
<?php
	$this->editor->content = @$this->element->$plugin_description;
	echo $this->editor->display();
?>
		</td>
	</tr>
</table>
