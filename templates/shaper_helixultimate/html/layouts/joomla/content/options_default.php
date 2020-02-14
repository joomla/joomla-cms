<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('JPATH_BASE') or die();

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Form\FormHelper;

?>

<fieldset class="<?php echo !empty($displayData->formclass) ? $displayData->formclass : ''; ?>">
	<legend><?php echo $displayData->name; ?></legend>
	<?php if (!empty($displayData->description)) : ?>
		<p><?php echo $displayData->description; ?></p>
	<?php endif; ?>
	<?php $fieldsnames = explode(',', $displayData->fieldsname); ?>
	<?php foreach ($fieldsnames as $fieldname) : ?>
    	<?php foreach ($displayData->form->getFieldset($fieldname) as $field) : ?>
        	<?php $datashowon = ''; ?>
        	<?php $groupClass = $field->type === 'Spacer' ? ' field-spacer' : ''; ?>
            <?php if ($field->showon) : ?>
                <?php HTMLHelper::_('jquery.framework'); ?>
                <?php HTMLHelper::_('script', 'system/cms.min.js', array('version' => 'auto', 'relative' => true)); ?>
                <?php $datashowon = ' data-showon=\'' . json_encode(FormHelper::parseShowOnConditions($field->showon, $field->formControl, $field->group)) . '\''; ?>
            <?php endif; ?>
            <div class="control-group<?php echo $groupClass; ?>"<?php echo $datashowon; ?>>
				<?php if (!isset($displayData->showlabel) || $displayData->showlabel) : ?>
					<div class="control-label"><?php echo $field->label; ?></div>
				<?php endif; ?>
				<div class="controls"><?php echo $field->input; ?></div>
			</div>
		<?php endforeach; ?>
	<?php endforeach; ?>
</fieldset>
