<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_modules
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.html.pane');
$pane = &JPane::getInstance('sliders');

	$fieldSets = $this->paramsform->getFieldsets();
	foreach ($fieldSets as $name => $fieldSet) :
		if (isset($fieldSet['hidden']) && $fieldSet['hidden'] == true || $name == 'request') :
			continue;
		endif;
		$label = isset($fieldSet['label']) ? $fieldSet['label'] : 'Config_'.$name;
		echo $pane->startPanel(JText::_($label), 'publishing-details');
			if (isset($fieldSet['description'])) :
				echo '<p class="tip">'.JText::_($fieldSet['description']).'</p>';
			endif;
			?>
		<fieldset class="panelform">
			<?php foreach ($this->paramsform->getFields($name) as $field) : ?>
				<?php echo $field->label; ?>
				<?php echo $field->input; ?>
			<?php endforeach; ?>
		</fieldset>
<?php
	echo $pane->endPanel();
	endforeach;
?>
