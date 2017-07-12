<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
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
			?>
			<div>
				<div class="controls"><?php echo $field->input; ?></div>
			</div>
			<?php
		}
	}
	?>
</fieldset>
