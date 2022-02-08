<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_fields
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;

// Check if we have all the data
if (!array_key_exists('item', $displayData) || !array_key_exists('context', $displayData))
{
	return;
}

// Setting up for display
$item = $displayData['item'];

if (!$item)
{
	return;
}

$context = $displayData['context'];

if (!$context)
{
	return;
}

$parts     = explode('.', $context);
$component = $parts[0];
$fields    = null;

if (array_key_exists('fields', $displayData))
{
	$fields = $displayData['fields'];
}
else
{
	$fields = $item->jcfields ?: FieldsHelper::getFields($context, $item, true);
}

if (empty($fields))
{
	return;
}

$output = array();

// Organize the fields according to their group

$groupFields = array(
	0 => [],
);

$groupTitles = array(
	0 => '',
);

foreach ($fields as $field)
{
	// If the value is empty do nothing
	if (!isset($field->value) || trim($field->value) === '')
	{
		continue;
	}

	$class = $field->name;

	if ($field->params->get('render_class'))
	{
		$class .= ' ' . $field->params->get('render_class');
	}

	$layout = $field->params->get('layout', 'render');
	$content = FieldsHelper::render($context, 'field.' . $layout, array('field' => $field));

	// If the content is empty do nothing
	if (trim($content) === '')
	{
		continue;
	}

	if (!array_key_exists($field->group_id, $groupFields))
	{
		$groupFields[$field->group_id] = [];

		if (Factory::getLanguage()->hasKey($field->group_title))
		{
			$groupTitles[$field->group_id] = Text::_($field->group_title);
		}
		else
		{
			$groupTitles[$field->group_id] = $field->group_title;
		}
	}

	$groupFields[$field->group_id][] = '<li class="field-entry ' . $class . '">' . $content . '</li>';
}

// Loop through the groups

foreach ($groupFields as $group_id => $group_fields)
{
	if (!$group_fields)
	{
		continue;
	}

	if ($groupTitles[$group_id])
	{
		$output[] = '<li class="field-group group_' . $group_id . '">';
		$output[] = '<span id="group_' . $group_id . '">' . $groupTitles[$group_id] . '</span>';
		$output[] = '<ul aria-labelledby="group_' . $group_id . '">';
	}

	foreach ($group_fields as $field)
	{
		$output[] = $field;
	}

	if ($groupTitles[$group_id])
	{
		$output[] = '</ul>';
		$output[] = '</li>';
	}
}

if (empty($output))
{
	return;
}
?>
<ul class="fields-container">
	<?php echo implode("\n", $output); ?>
</ul>
