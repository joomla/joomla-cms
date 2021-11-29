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

$output = [];
$list_type = [];
$show_title = [];
$title_tag = [];
$title_class = [];

foreach ($fields as $field)
{
	// If the value is empty do nothing
	if (!isset($field->value) || trim($field->value) === '')
	{
		continue;
	}

	$class = $field->name . ' ' . $field->params->get('render_class');
	$layout = $field->params->get('layout', 'render');
	$content = FieldsHelper::render($context, 'field.' . $layout, array('field' => $field));

	// If the content is empty do nothing
	if (trim($content) === '')
	{
		continue;
	}

	// check gparams for backward compatibility
	$gparams = json_decode($field->group_params);

	if (isset($field->group_title) && isset($gparams->render_tag))
	{
		switch ($gparams->render_tag)
		{
			case 'dl':
				$tag = 'dd';
				break;
			case 'p':
			case 'div':
				$tag = $gparams->render_tag;
				break;
			default:
				$tag = 'li';
		}
		$list_type[$field->group_title] = $gparams->render_tag;
		$list_type_class[$field->group_title] = $gparams->render_class;
		$show_title[$field->group_title] = $gparams->show_title;
		$title_tag[$field->group_title] = $gparams->title_tag;
		$title_class[$field->group_title] = $gparams->title_class;
		$output[$field->group_title][] = '<' . $tag  . ' class="field-entry ' . $class . '">' . $content . '</' . $tag  . '>';
	}
	else
	{
		$list_type['none'] = 'ul';
		$list_type_class['none'] = 'fields-container';
		$show_title['none'] = 0;
		$title_tag['none'] = '';
		$title_class['none'] = '';
		$output['none'][] = '<li class="field-entry ' . $class . '">' . $content . '</li>';
	}
}

if (empty($output))
{
	return;
}

foreach ($list_type as $title => $list_type)
{
	if ($show_title[$title] && $list_type != 'dl')
	{
		$class = empty($title_class[$title]) ? '' : ' class="' . $title_class[$title] . '"';
		echo '<' . $title_tag[$title] . $class . '>' . $title . '</' . $title_tag[$title] . '>' . "\n";
	}

	if (!($list_type === 'p' || $list_type === 'div'))
	{
		$class = empty($list_type_class[$title]) ? '' : ' class="' . $list_type_class[$title]  . '"';
		echo '<' . $list_type . $class . '>';
	}

	if ($list_type === 'dl')
	{
		$class = empty($list_type_class[$title]) ? '' : ' class="' . $list_type_class[$title] . '"';
		echo '<dt'. $class . '>' . $title . '</dt>' . "\n";
	}
	echo implode("\n", $output[$title]);

	if (!($list_type === 'p' || $list_type === 'div'))
	{
		echo '</' . $list_type . '>' . "\n";
	}
}
