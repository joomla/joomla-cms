<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

?>
<fieldset class="<?php echo !empty($displayData->formclass) ? $displayData->formclass : 'form-horizontal'; ?>">
	<legend><?php echo $displayData->name; ?></legend>
	<?php if (!empty($displayData->description)) : ?>
		<p><?php echo $displayData->description; ?></p>
	<?php endif; ?>
<<<<<<< HEAD
	
	<?php $fieldsnames = explode(',', $displayData->fieldsname); ?>
	<?php foreach($fieldsnames as $fieldname) : ?>
		<?php foreach ($displayData->form->getFieldset($fieldname) as $field) : ?>
			<?php $classnames = 'control-group'; ?>
			<?php $rel = ''; ?>
			<?php $showon = $displayData->form->getFieldAttribute($field->fieldname, 'showon'); ?>
			<?php if (!empty($showon)) : ?>
				<?php JHtml::_('jquery.framework'); ?>
				<?php JHtml::_('script', 'jui/cms.js', false, true); ?>
				<?php $id = $displayData->form->getFormControl(); ?>
				<?php $showon = explode(':', $showon, 2); ?>
				<?php $classnames .= ' showon_' . implode(' showon_', explode(',', $showon[1])); ?>
				<?php $rel = ' rel="showon_' . $id . '['. $showon[0] . ']"'; ?>
=======
	<?php
	$fieldsnames = explode(',', $displayData->fieldsname);
	foreach($fieldsnames as $fieldname)
	{
		foreach ($displayData->form->getFieldset($fieldname) as $field)
		{
			$datashowon = '';
			if ($showonstring = $displayData->form->getFieldAttribute($field->fieldname, 'showon'))
			{
				JHtml::_('jquery.framework');
				JHtml::_('script', 'jui/cms.js', false, true);

				$showonarr = array();
				foreach (preg_split('%\[AND\]|\[OR\]%', $showonstring) as $showonfield)
				{
					$showon   = explode(':', $showonfield, 2);
					$showonarr[] = array(
						'field'  => $displayData->form->getFormControl() . '[' . $displayData->form->getFieldAttribute($showon[0], 'name') . ']',
						'values' => explode(',', $showon[1]),
						'op'     => (preg_match('%\[(AND|OR)\]' . $showonfield . '%', $showonstring, $matches)) ? $matches[1] : ''
					);
				}

				$datashowon = ' data-showon=\'' . json_encode($showonarr) . '\'';
			}
	?>
		<div class="control-group"<?php echo $datashowon; ?>>
			<?php if (!isset($displayData->showlabel) || $displayData->showlabel): ?>
				<div class="control-label"><?php echo $field->label; ?></div>
>>>>>>> db558e506f7b0b542d577d79bf19106450517b81
			<?php endif; ?>

			<div class="<?php echo $classnames; ?>"<?php echo $rel; ?>>
				<?php if (!isset($displayData->showlabel) || $displayData->showlabel): ?>
					<div class="control-label"><?php echo $field->label; ?></div>
				<?php endif; ?>
				<div class="controls"><?php echo $field->input; ?></div>
			</div>
		<?php endforeach; ?>
	<?php endforeach; ?>
</fieldset>
