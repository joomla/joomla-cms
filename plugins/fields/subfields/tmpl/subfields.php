<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Subfields
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

if (!$context || empty($field->subfields_rows))
{
	return;
}
?>
<ul class="fields-container">
	<?php /* Iterate over each row that we have */ ?>
	<?php foreach ($field->subfields_rows as $subfields_row): ?>
		<li>
			<?php
			/* Placeholder array to generate this rows output */
			$row_output = array();

			/* Iterate over each sub field inside of that row */
			foreach ($subfields_row as $subfield)
			{
				$class   = $subfield->params->get('render_class', null);
				$layout  = $subfield->params->get('layout', 'render');
				$content = FieldsHelper::render($context, 'field.' . $layout, array('field' => $subfield));

				// Generate the output for this sub field and row
				$row_output[] = '<span class="field-entry' . ($class ? (' ' . $class) : '') . '">' . $content . '</span>';
			}

			// And output this rows output
			echo implode(', ', $row_output);
			?>
		</li>
	<?php endforeach; ?>
</ul>
