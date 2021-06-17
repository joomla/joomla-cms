<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_fields
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

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

foreach ($fields as $field)
{
	// If the value is empty do nothing
	if (!isset($field->value) || trim($field->value) === '')
	{
		continue;
	}

	$class = $field->params->get('render_class');
	$layout = $field->params->get('layout', 'render');
	$content = FieldsHelper::render($context, 'field.' . $layout, array('field' => $field));

	// If the content is empty do nothing
	if (trim($content) === '')
	{
		continue;
	}

	$output[] = '<li class="field-entry ' . $class . '">' . $content . '</li>';
}

if (empty($output))
{
	return;
}
?>
<ul class="fields-container">
	<?php echo implode("\n", $output); ?>
</ul>
