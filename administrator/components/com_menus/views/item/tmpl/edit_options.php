<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_menus
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.html.pane');
$pane = &JPane::getInstance('sliders', array('allowAllClose' => true));

echo $pane->startPane('content-pane');
	$fieldSets = $this->paramsform->getFieldsets();
	foreach ($fieldSets as $name => $fieldSet) :
		$label = isset($fieldSet['label']) ? $fieldSet['label'] : 'Config_'.$name;
		echo $pane->startPanel(JText::_($label), 'publishing-details');
		if (isset($fieldSet['description'])) :
			echo '<p class="tip" style="float:right;">'.JText::_($fieldSet['description']).'</p>';
		endif;
?>
<table class="admintable">
	<tbody>
		<?php
		foreach ($this->paramsform->getFields($name) as $field):
		?>
		<tr>
			<td width="185" class="key">
				<?php echo $field->label; ?>
			</td>
			<td>
				<?php echo $field->input; ?>
			</td>
		</tr>
		<?php
		endforeach;
		?>
	</tbody>
</table>
<br class="clr" />
<?php
		echo $pane->endPanel();
	endforeach;
echo $pane->endPane();
?>
