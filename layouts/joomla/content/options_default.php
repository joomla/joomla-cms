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

<fieldset class="<?php echo !empty($displayData->formclass) ? $displayData->formclass : ''; ?>">
	<legend><?php echo $displayData->name; ?></legend>

	<?php if (!empty($displayData->description)) : ?>
		<p><?php echo $displayData->description; ?></p>
	<?php endif; ?>

	<?php
	$fieldsnames = explode(',', $displayData->fieldsname);

	foreach ($fieldsnames as $fieldname)
	{
		foreach ($displayData->form->getFieldset($fieldname) as $field)
		{
			$datashowon = '';

			if ($showonstring = $displayData->form->getFieldAttribute($field->fieldname, 'showon'))
			{
				JHtml::_('jquery.framework');
				JHtml::_('script', 'jui/cms.js', array('version' => 'auto', 'relative' => true));

				$showonarr = array();

				foreach (preg_split('%\[AND\]|\[OR\]%', $showonstring) as $showonfield)
				{
					$showon   = explode(':', $showonfield, 2);
					$showonarr[] = array(
						'field'  => $displayData->form->getFormControl() . '[' . $displayData->form->getFieldAttribute($showon[0], 'name') . ']',
						'values' => explode(',', $showon[1]),
						'op'     => preg_match('%\[(AND|OR)\]' . $showonfield . '%', $showonstring, $matches) ? $matches[1] : ''
					);
				}

				$datashowon = ' data-showon=\'' . json_encode($showonarr) . '\'';
			}
			?>
			<div class="control-group"<?php echo $datashowon; ?>>
				<?php if (!isset($displayData->showlabel) || $displayData->showlabel) : ?>
					<div class="control-label"><?php echo $field->label; ?></div>
				<?php endif; ?>

				<div class="controls"><?php echo $field->input; ?></div>
			</div>
			<?php
		}
	}
	?>
</fieldset>
