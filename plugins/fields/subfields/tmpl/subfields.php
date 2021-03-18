<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Subfields
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;

defined('_JEXEC') or die;

if (!$context || empty($field->subfields_rows))
{
	return;
}

$result = '';

// Iterate over each row that we have
foreach ($field->subfields_rows as $subfields_row)
{
	// Placeholder array to generate this rows output
	$row_output = array();

	// Iterate over each sub field inside of that row
	foreach ($subfields_row as $subfield)
	{
		$class   = trim($subfield->params->get('render_class', ''));
		$layout  = trim($subfield->params->get('layout', 'render'));
		$content = trim(
			FieldsHelper::render(
				$context,
				'field.' . $layout, // normally just 'field.render'
				array('field' => $subfield)
			)
		);

		// Skip empty output
		if ($content === '')
		{
			continue 1;
		}

		// Generate the output for this sub field and row
		$row_output[] = '<span class="field-entry' . ($class ? (' ' . $class) : '') . '">' . $content . '</span>';
	}

	// Skip empty rows
	if (count($row_output) == 0)
	{
		continue 1;
	}

	$result .= '<li>' . implode(', ', $row_output) . '</li>';
}
?>

<?php if (trim($result) != '') : ?>
	<ul class="fields-container">
		<?php echo $result; ?>
	</ul>
<?php endif; ?>
