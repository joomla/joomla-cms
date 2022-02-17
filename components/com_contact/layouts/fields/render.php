<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
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

// Check if we have mail context in first element
$isMail = (reset($fields)->context == 'com_contact.mail');

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
	if ((!isset($field->value) || trim($field->value) === '') && !$isMail)
	{
		continue;
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
			$groupTitles[$field->group_id] = htmlentities($field->group_title, ENT_QUOTES | ENT_IGNORE, 'UTF-8');
		}
	}

	$groupFields[$field->group_id][] = $content;
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
		if ($isMail)
		{
			$output[] = "\r\n" . $groupTitles[$group_id] . "\r\n";
		}
		else
		{
			$output[] = '<dd class="contact-field-group group-' . $group_id . '">';
			$output[] = '<span id="group-' . $group_id . '">' . $groupTitles[$group_id] . '</span>';
			$output[] = '<dl class="fields-container" aria-labelledby="group-' . $group_id . '">';
		}
	}

	foreach ($group_fields as $field)
	{
		$output[] = $field;
	}

	if ($groupTitles[$group_id] && !$isMail)
	{
		$output[] = '</dl>';
		$output[] = '</dd>';
	}
}

if (empty($output))
{
	return;
}
?>
<?php if (!$isMail) : ?>
	<dl class="fields-container contact-fields dl-horizontal">
<?php endif; ?>
<?php echo implode("\n", $output); ?>
<?php if (!$isMail) : ?>
	</dl>
<?php endif; ?>
